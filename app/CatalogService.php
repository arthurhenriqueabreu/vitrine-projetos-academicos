<?php
final class CatalogService {
    public const ENTITIES = [
      'areas'=>['Áreas', ['nome'=>'Nome','descricao'=>'Descrição']],
      'subareas'=>['Subáreas',['nome'=>'Nome','area_id'=>'Área']],
      'instituicoes'=>['Instituições',['nome'=>'Nome','cidade'=>'Cidade','uf'=>'UF']],
      'cursos'=>['Cursos',['nome'=>'Nome','instituicao_id'=>'Instituição']],
      'unidades_curriculares'=>['Unidades curriculares',['nome'=>'Nome','curso_id'=>'Curso']],
      'autores'=>['Autores',['nome'=>'Nome','biografia'=>'Biografia pública','instituicao_id'=>'Instituição']],
      'orientadores'=>['Orientadores',['nome'=>'Nome','biografia'=>'Biografia pública','instituicao_id'=>'Instituição']],
      'edicoes'=>['Edições',['nome'=>'Nome','ano'=>'Ano','descricao'=>'Descrição']],
      'palavras_chave'=>['Palavras-chave',['nome'=>'Nome']],
      'usuarios'=>['Usuários',['nome'=>'Nome','email'=>'E-mail','perfil'=>'Perfil','ativo'=>'Ativo','senha'=>'Nova senha']]
    ];
    public const RELATIONS = ['area_id'=>'areas','instituicao_id'=>'instituicoes','curso_id'=>'cursos'];
    public static function validateEntity(string $entity): array {
        if (!isset(self::ENTITIES[$entity])) throw new HttpError(404,'Cadastro não encontrado.'); return self::ENTITIES[$entity];
    }
    public static function save(string $entity, array $in, array $user, ?int $id): void {
        [, $fields]=self::validateEntity($entity);
        if ($user['perfil']!=='administrador') throw new HttpError(403,'Cadastro exclusivo de administradores.');
        $d=[];
        foreach ($fields as $key=>$label) {
            if ($key==='senha') continue;
            $v=trim((string)($in[$key]??''));
            if (isset(self::RELATIONS[$key])) { $v=(int)$v; if ($v<1) throw new InvalidArgumentException('Selecione '.$label); }
            elseif ($key==='ano') { $v=(int)$v; if ($v<2000 || $v>2100) throw new InvalidArgumentException('Ano deve estar entre 2000 e 2100.'); }
            elseif ($key==='ativo') $v=empty($in[$key])?0:1;
            elseif ($key==='perfil') { if (!in_array($v,['administrador','editor'],true)) throw new InvalidArgumentException('Perfil inválido.'); }
            else { $max=in_array($key,['descricao','biografia'])?5000:($key==='nome'?80:190); if ((!in_array($key,['descricao','biografia']) && $v==='') || mb_strlen($v)>$max) throw new InvalidArgumentException($label.': obrigatório, até '.$max.' caracteres.'); }
            if ($key==='email' && !filter_var($v,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('E-mail inválido.');
            if ($key==='uf') { $v=strtoupper($v); if(!in_array($v,explode(' ','AC AL AP AM BA CE DF ES GO MA MT MS MG PA PB PR PE PI RJ RN RS RO RR SC SP SE TO'),true)) throw new InvalidArgumentException('UF inválida.'); }
            $d[$key]=$v;
        }
        if ($entity==='usuarios') {
            $pw=(string)($in['senha']??''); if (!$id || $pw!=='') { if(strlen($pw)<12 || strlen($pw)>72) throw new InvalidArgumentException('Senha: de 12 a 72 caracteres.'); $d['senha_hash']=password_hash($pw,PASSWORD_DEFAULT); }
        }
        $db=Database::connection();$db->beginTransaction();
        try {
            if ($entity==='usuarios') {
                $admins=Database::all("SELECT id FROM usuarios WHERE perfil='administrador' AND ativo=1 FOR UPDATE");
                if ($id===$user['id'] && (!$d['ativo'] || $d['perfil']!=='administrador')) throw new InvalidArgumentException('Não é possível desativar ou rebaixar sua própria conta.');
                if ($id && count($admins)===1 && (int)$admins[0]['id']===$id && (!$d['ativo'] || $d['perfil']!=='administrador')) throw new InvalidArgumentException('Mantenha ao menos um administrador ativo.');
            }
            if ($id) { if(!Database::one("SELECT id FROM $entity WHERE id=?",[$id])) throw new HttpError(404,'Registro não encontrado.');$set=implode(',',array_map(fn($k)=>"$k=?",array_keys($d))); Database::run("UPDATE $entity SET $set WHERE id=?",[...array_values($d),$id]); }
            else { Database::run("INSERT INTO $entity (".implode(',',array_keys($d)).') VALUES ('.implode(',',array_fill(0,count($d),'?')).')',array_values($d));$id=(int)$db->lastInsertId(); }
            ProjectService::audit('salvar',$entity,$id,(int)$user['id']);$db->commit();
        } catch(Throwable $ex) { $db->rollBack();throw $ex; }
    }
    public static function delete(string $entity,int $id,array $user): void {
        self::validateEntity($entity);if($user['perfil']!=='administrador') throw new HttpError(403,'Ação exclusiva de administradores.');
        if ($entity==='usuarios' && $id===$user['id']) throw new InvalidArgumentException('Não é possível excluir sua própria conta.');
        $db=Database::connection();$db->beginTransaction();
        try {
            if (!Database::one("SELECT id FROM $entity WHERE id=? FOR UPDATE",[$id])) throw new HttpError(404,'Registro não encontrado.');
            if ($entity==='usuarios') {
                $admins=Database::all("SELECT id FROM usuarios WHERE perfil='administrador' AND ativo=1 FOR UPDATE");
                if(count($admins)===1 && (int)$admins[0]['id']===$id) throw new InvalidArgumentException('Mantenha um administrador ativo.');
            }
            Database::run("DELETE FROM $entity WHERE id=?",[$id]);ProjectService::audit('excluir',$entity,$id,(int)$user['id']);$db->commit();
        } catch(Throwable $ex) { $db->rollBack(); throw $ex; }
    }
}
