<?php
final class ProjectService {
    public static function ids($value, int $min, int $max, string $name): array {
        if (!is_array($value)) $value=[];
        foreach($value as $raw) if(!is_int($raw) && !(is_string($raw) && preg_match('/^[1-9][0-9]*$/D',$raw))) throw new InvalidArgumentException("$name: identificador inválido.");
        $ids=array_values(array_unique(array_map('intval',$value)));
        if (count($ids)<$min || count($ids)>$max || ($ids && min($ids)<1)) throw new InvalidArgumentException("$name: selecione de $min a $max registros distintos.");
        return $ids;
    }
    public static function audit(string $action, string $entity, int $id, int $uid): void { Database::run('INSERT INTO auditoria (usuario_id,acao,entidade,registro_id) VALUES (?,?,?,?)',[$uid,$action,$entity,$id]); }
    public static function save(array $in, array $user, ?int $id = null): int {
        if (!in_array($user['perfil'] ?? '', ['administrador','editor'], true)) throw new HttpError(403,'Perfil sem permissão para editar projetos.');
        $authors=self::ids($in['autores']??[],1,5,'Autores');
        $collabs=self::ids($in['colaboradores']??[],0,3,'Colaboradores');
        $keywords=self::ids($in['palavras']??[],0,30,'Palavras-chave');
        $data=[];
        foreach (['titulo'=>200,'resumo'=>15000,'objetivos'=>8000] as $k=>$max) { $v=trim((string)($in[$k]??'')); if ($v==='' || mb_strlen($v)>$max) throw new InvalidArgumentException("Preencha $k (máximo $max caracteres)."); $data[$k]=$v; }
        foreach (['edicao_id','area_id','instituicao_id','curso_id','unidade_curricular_id','responsavel_id'] as $k) { $data[$k]=(int)($in[$k]??0); if ($data[$k]<1) throw new InvalidArgumentException('Preencha todos os relacionamentos obrigatórios.'); }
        $data['subarea_id']=empty($in['subarea_id'])?null:(int)$in['subarea_id'];
        $data['situacao']=(string)($in['situacao']??'rascunho');
        if (!in_array($data['situacao'],['rascunho','publicado','arquivado'],true)) throw new InvalidArgumentException('Situação inválida.');
        $data['destaque']=empty($in['destaque'])?0:1;
        if (in_array($data['responsavel_id'],$collabs,true)) throw new InvalidArgumentException('Professor responsável não pode ser também colaborador.');
        $db=Database::connection(); $db->beginTransaction();
        try {
            if ($id) {
                $old=Database::one('SELECT id,versao FROM trabalhos WHERE id=? FOR UPDATE',[$id]);
                if (!$old) throw new HttpError(404,'Projeto não encontrado.');
                if ((int)($in['versao']??0)!==(int)$old['versao']) throw new InvalidArgumentException('O projeto foi alterado por outro usuário. Reabra o formulário.');
                $sets=implode(',',array_map(fn($k)=>"$k=?",array_keys($data)));
                Database::run("UPDATE trabalhos SET $sets,versao=versao+1 WHERE id=?",[...array_values($data),$id]);
            } else {
                $data['identificador']=bin2hex(random_bytes(16)); $data['criado_por']=$user['id'];
                Database::run('INSERT INTO trabalhos (' . implode(',',array_keys($data)) . ') VALUES (' . implode(',',array_fill(0,count($data),'?')) . ')',array_values($data)); $id=(int)$db->lastInsertId();
            }
            foreach (['trabalho_autores','trabalho_colaboradores','trabalho_palavras'] as $table) Database::run("DELETE FROM $table WHERE trabalho_id=?",[$id]);
            foreach ($authors as $n=>$a) Database::run('INSERT INTO trabalho_autores VALUES (?,?,?)',[$id,$a,$n+1]);
            foreach ($collabs as $n=>$o) Database::run('INSERT INTO trabalho_colaboradores VALUES (?,?,?)',[$id,$o,$n+1]);
            foreach ($keywords as $p) Database::run('INSERT INTO trabalho_palavras VALUES (?,?)',[$id,$p]);
            self::audit('salvar','trabalhos',$id,(int)$user['id']); $db->commit(); return $id;
        } catch (Throwable $ex) { $db->rollBack(); throw $ex; }
    }
    public static function delete(int $id, array $user): void {
        if ($user['perfil']!=='administrador') throw new HttpError(403,'Somente administradores podem excluir projetos.');
        $db=Database::connection(); $db->beginTransaction();
        try {
            if (!Database::one('SELECT id FROM trabalhos WHERE id=? FOR UPDATE',[$id])) throw new HttpError(404,'Projeto não encontrado.');
            $files=Database::all('SELECT nome_storage FROM arquivos WHERE trabalho_id=?',[$id]);
            Database::run('DELETE FROM trabalhos WHERE id=?',[$id]); self::audit('excluir','trabalhos',$id,(int)$user['id']); $db->commit();
            foreach ($files as $f) { $path=BASE.'/storage/uploads/'.basename($f['nome_storage']); if (is_file($path)) unlink($path); }
        } catch (Throwable $ex) { if($db->inTransaction()) $db->rollBack(); throw $ex; }
    }
}
