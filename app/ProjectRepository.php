<?php
final class ProjectRepository {
    public static function filters(array $input): array {
        $r = ['q' => mb_substr(trim((string) ($input['q'] ?? '')), 0, 150)];
        foreach (['area','subarea','edicao','curso','instituicao','ano','pagina'] as $k) $r[$k] = max(0, (int) ($input[$k] ?? 0));
        $r['favoritos'] = !empty($input['favoritos']); return $r;
    }
    public static function where(array $f, bool $ignoreArea = false): array {
        $w = ["t.situacao='publicado'"]; $p = [];
        foreach (['area','subarea','edicao','curso','instituicao'] as $key) {
            if ($ignoreArea && $key === 'area') continue;
            if (!empty($f[$key])) { $w[] = 't.' . $key . '_id=?'; $p[] = $f[$key]; }
        }
        if (!empty($f['ano'])) { $w[] = 'EXISTS(SELECT 1 FROM edicoes ed WHERE ed.id=t.edicao_id AND ed.ano=?)'; $p[] = $f['ano']; }
        if (!empty($f['favoritos'])) { $w[] = 'EXISTS(SELECT 1 FROM favoritos f WHERE f.trabalho_id=t.id AND f.visitante_hash=?)'; $p[] = self::visitor(); }
        if (!empty($f['q'])) {
          foreach (array_slice(preg_split('/\s+/u', $f['q'], -1, PREG_SPLIT_NO_EMPTY),0,12) as $term) {
            $like = '%' . strtr($term, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $w[] = "(t.titulo LIKE ? ESCAPE '!' OR EXISTS(SELECT 1 FROM trabalho_autores ta JOIN autores au ON au.id=ta.autor_id WHERE ta.trabalho_id=t.id AND au.nome LIKE ? ESCAPE '!') OR EXISTS(SELECT 1 FROM instituicoes i WHERE i.id=t.instituicao_id AND i.nome LIKE ? ESCAPE '!') OR EXISTS(SELECT 1 FROM trabalho_palavras tp JOIN palavras_chave pc ON pc.id=tp.palavra_id WHERE tp.trabalho_id=t.id AND pc.nome LIKE ? ESCAPE '!') OR EXISTS(SELECT 1 FROM cursos c WHERE c.id=t.curso_id AND c.nome LIKE ? ESCAPE '!') OR EXISTS(SELECT 1 FROM edicoes ed WHERE ed.id=t.edicao_id AND CAST(ed.ano AS CHAR) LIKE ? ESCAPE '!'))";
            for ($i=0;$i<6;$i++) $p[] = $like;
          }
        }
        return [implode(' AND ', $w), $p];
    }
    public static function visitor(): string { $_SESSION['visitor'] ??= bin2hex(random_bytes(32)); return hash('sha256', $_SESSION['visitor']); }
    public static function listing(array $f, bool $featured = false): array {
        [$w,$p] = self::where($f);
        if ($featured) $w .= ' AND t.destaque=1';
        $total = (int) Database::one('SELECT COUNT(*) n FROM trabalhos t WHERE ' . $w, $p)['n'];
        $page = max(1,min((int) ($f['pagina'] ?? 1), max(1,(int) ceil($total/12))));
        $limit = $featured ? 3 : 12; $offset = $featured ? 0 : ($page-1)*12;
        $rows = Database::all("SELECT t.*,a.nome area,e.nome edicao,e.ano,i.nome instituicao,c.nome curso,
          (SELECT GROUP_CONCAT(au.nome ORDER BY ta.posicao SEPARATOR ', ') FROM trabalho_autores ta JOIN autores au ON au.id=ta.autor_id WHERE ta.trabalho_id=t.id) autores,
          (SELECT ar.id FROM arquivos ar WHERE ar.trabalho_id=t.id AND ar.mime LIKE 'image/%' ORDER BY ar.id LIMIT 1) capa
          FROM trabalhos t JOIN areas a ON a.id=t.area_id JOIN edicoes e ON e.id=t.edicao_id JOIN instituicoes i ON i.id=t.instituicao_id JOIN cursos c ON c.id=t.curso_id WHERE $w ORDER BY t.destaque DESC,t.id DESC LIMIT $limit OFFSET $offset",$p);
        return ['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>max(1,(int) ceil($total/12))];
    }
    public static function counts(array $f): array {
        [$w,$p] = self::where($f);
        return Database::all('SELECT a.id,a.nome,COUNT(t.id) total FROM areas a LEFT JOIN trabalhos t ON t.area_id=a.id AND ' . $w . ' GROUP BY a.id,a.nome ORDER BY a.nome', $p);
    }
    public static function detail(int $id, bool $public = true): array {
        $t = Database::one("SELECT t.*,a.nome area,s.nome subarea,e.nome edicao,e.ano,i.nome instituicao,i.cidade,i.uf,c.nome curso,uc.nome unidade_curricular,o.nome responsavel,o.biografia responsavel_bio FROM trabalhos t JOIN areas a ON a.id=t.area_id LEFT JOIN subareas s ON s.id=t.subarea_id JOIN edicoes e ON e.id=t.edicao_id JOIN instituicoes i ON i.id=t.instituicao_id JOIN cursos c ON c.id=t.curso_id JOIN unidades_curriculares uc ON uc.id=t.unidade_curricular_id JOIN orientadores o ON o.id=t.responsavel_id WHERE t.id=?" . ($public ? " AND t.situacao='publicado'" : ''), [$id]);
        if (!$t) throw new HttpError(404,'Projeto não encontrado.');
        $t['autores'] = Database::all('SELECT a.*,i.nome instituicao FROM autores a JOIN instituicoes i ON i.id=a.instituicao_id JOIN trabalho_autores ta ON ta.autor_id=a.id WHERE ta.trabalho_id=? ORDER BY ta.posicao',[$id]);
        $t['colaboradores'] = Database::all('SELECT o.*,i.nome instituicao FROM orientadores o JOIN instituicoes i ON i.id=o.instituicao_id JOIN trabalho_colaboradores tc ON tc.orientador_id=o.id WHERE tc.trabalho_id=? ORDER BY tc.posicao',[$id]);
        $t['palavras'] = Database::all('SELECT p.* FROM palavras_chave p JOIN trabalho_palavras tp ON tp.palavra_id=p.id WHERE tp.trabalho_id=?',[$id]);
        $t['arquivos'] = Database::all('SELECT * FROM arquivos WHERE trabalho_id=? ORDER BY id',[$id]);
        $t['links'] = Database::all('SELECT * FROM links WHERE trabalho_id=? ORDER BY id',[$id]); return $t;
    }
    public static function lookups(): array {
        $r=[]; foreach (['areas','subareas','edicoes','instituicoes','cursos','unidades_curriculares','autores','orientadores','palavras_chave'] as $t) $r[$t] = Database::all('SELECT * FROM ' . $t . ' ORDER BY nome'); return $r;
    }
}
