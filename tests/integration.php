<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
// Executar SOMENTE sobre demonstração criada em banco descartável terminado em _test.
if(!str_ends_with(env('DB_DATABASE'),'_test')) {fwrite(STDERR,"Recusado: DB_DATABASE deve terminar em _test.\n");exit(2);}
$results=[];$created=[];
function test(string $name,callable $fn):void {global $results;try{if($fn()!==true)throw new RuntimeException('O cenário deve retornar true');$results[]=['teste'=>$name,'status'=>'aprovado'];}catch(Throwable $e){$results[]=['teste'=>$name,'status'=>'reprovado','erro'=>$e->getMessage()];}}
function denied(callable $fn,string $class=HttpError::class,?int $status=404,?string $message=null):bool {
    try{$fn();return false;}catch(Throwable $e){return $e instanceof $class && ($status===null || ($e instanceof HttpError && $e->status===$status)) && ($message===null || str_contains($e->getMessage(),$message));}
}
function foreignKeyDenied(callable $fn,int $mysqlCode=1452):bool {
    try{$fn();return false;}catch(PDOException $e){return $e->getCode()==='23000' && (int)($e->errorInfo[1]??0)===$mysqlCode;}
}
try { $db=Database::connection();$version=Database::one('SELECT VERSION() v')['v']; }
catch(Throwable $e){echo json_encode(['suite'=>'integration','status'=>'bloqueado','motivo'=>$e->getMessage()],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);exit(2);}
$admin=Database::one("SELECT id,perfil FROM usuarios WHERE perfil='administrador' AND ativo=1 LIMIT 1");
$editor=Database::one("SELECT id,perfil FROM usuarios WHERE perfil='editor' AND ativo=1 LIMIT 1");
if(!$admin || !$editor)throw new RuntimeException('Execute setup.php --demo no banco de teste.');
$base=['titulo'=>'Integração '.bin2hex(random_bytes(4)),'resumo'=>'Resumo de teste','objetivos'=>'Objetivos de teste','autores'=>[1,2],'colaboradores'=>[2],'palavras'=>[1],'responsavel_id'=>1,'edicao_id'=>2,'area_id'=>1,'subarea_id'=>1,'instituicao_id'=>1,'curso_id'=>1,'unidade_curricular_id'=>1,'situacao'=>'publicado','destaque'=>1];
$id=null;
test('CA08 criar projeto persiste dados e relações',function()use($base,$admin,&$id,&$created){$id=ProjectService::save($base,$admin);$created[]=$id;$p=ProjectRepository::detail($id);return $p['titulo']===$base['titulo'] && count($p['autores'])===2 && count($p['colaboradores'])===1;});
if($id) {
 test('CA09 editar projeto',function()use($id,$base,$admin){$p=ProjectRepository::detail($id,false);ProjectService::save(array_merge($base,['titulo'=>'Título atualizado','versao'=>$p['versao']]),$admin,$id);return ProjectRepository::detail($id)['titulo']==='Título atualizado';});
 test('Edição simultânea antiga é rejeitada',fn()=>denied(fn()=>ProjectService::save(array_merge($base,['versao'=>1]),$admin,$id),InvalidArgumentException::class,null,'alterado por outro usuário'));
 test('CA10 rascunho sai da consulta pública',function()use($id,$base,$admin){$p=ProjectRepository::detail($id,false);ProjectService::save(array_merge($base,['situacao'=>'rascunho','versao'=>$p['versao']]),$admin,$id);return denied(fn()=>ProjectRepository::detail($id));});
 test('Editor publica projetos',function()use($id,$base,$editor){$p=ProjectRepository::detail($id,false);ProjectService::save(array_merge($base,['versao'=>$p['versao']]),$editor,$id);return ProjectRepository::detail($id)['situacao']==='publicado';});
 test('Editor não exclui projeto',fn()=>denied(fn()=>ProjectService::delete($id,$editor),HttpError::class,403) && ProjectRepository::detail($id)['id']==$id);
 test('Curso e instituição incompatíveis rejeitados pelo banco',fn()=>foreignKeyDenied(fn()=>ProjectService::save(array_merge($base,['instituicao_id'=>2]),$admin)));
 test('Subárea incompatível é recusada',fn()=>foreignKeyDenied(fn()=>ProjectService::save(array_merge($base,['subarea_id'=>3]),$admin)));
 test('Unidade curricular incompatível é recusada',fn()=>foreignKeyDenied(fn()=>ProjectService::save(array_merge($base,['unidade_curricular_id'=>3]),$admin)));
 test('Contadores correspondem ao filtro área/edição',function(){$f=ProjectRepository::filters(['area'=>1,'edicao'=>2]);$n=ProjectRepository::listing($f)['total'];return array_sum(array_column(ProjectRepository::counts($f),'total'))===$n;});
 test('Busca por nome de autor',fn()=>ProjectRepository::listing(ProjectRepository::filters(['q'=>'Clara Martins']))['total']>0);
 test('Busca por instituição',fn()=>ProjectRepository::listing(ProjectRepository::filters(['q'=>'Faculdade Senac Joinville']))['total']>0);
 test('Busca sem resultados',fn()=>ProjectRepository::listing(ProjectRepository::filters(['q'=>'zzzzinexistentezzzz']))['total']===0);
 test('Links javascript recusados',fn()=>denied(fn()=>UploadService::link(['url'=>'javascript:alert(1)','titulo'=>'X','tipo'=>'externo'],$id,$admin),InvalidArgumentException::class,null,'URL HTTP ou HTTPS'));
 test('Mass assignment não altera criado_por ou identificador',function()use($base,$admin,&$created){$x=ProjectService::save(array_merge($base,['criado_por'=>999999,'identificador'=>'forjado']),$admin);$created[]=$x;$r=ProjectRepository::detail($x);return $r['criado_por']==$admin['id'] && strlen($r['identificador'])===32 && $r['identificador']!=='forjado';});
 test('Exclusão remove relacionamentos em cascata',function()use($id,$admin){ProjectService::delete($id,$admin);return Database::one('SELECT COUNT(*) n FROM trabalho_autores WHERE trabalho_id=?',[$id])['n']==0 && denied(fn()=>ProjectRepository::detail($id,false));});
}
foreach(CatalogService::ENTITIES as $entity=>$_) {
 test('CRUD '.$entity,function()use($entity,$admin){
  $suffix=bin2hex(random_bytes(5));$in=['nome'=>'Teste '.$suffix,'descricao'=>'Descrição','biografia'=>'Biografia','cidade'=>'Joinville','uf'=>'SC','ano'=>2027,'area_id'=>1,'instituicao_id'=>1,'curso_id'=>1,'email'=>$suffix.'@teste.example','perfil'=>'editor','ativo'=>1,'senha'=>bin2hex(random_bytes(12))];
  CatalogService::save($entity,$in,$admin,null);$r=Database::one("SELECT id FROM $entity WHERE nome=?",[$in['nome']]);if(!$r)return false;
  $in['nome'].=' editado';CatalogService::save($entity,$in,$admin,(int)$r['id']);$changed=Database::one("SELECT nome FROM $entity WHERE id=?",[$r['id']])['nome']===$in['nome'];
  CatalogService::delete($entity,(int)$r['id'],$admin);return $changed && Database::one("SELECT id FROM $entity WHERE id=?",[$r['id']])===null;
 });
}
test('Instituição com projetos não pode ser excluída',fn()=>foreignKeyDenied(fn()=>CatalogService::delete('instituicoes',1,$admin),1451));
test('Administrador não exclui a própria conta',fn()=>denied(fn()=>CatalogService::delete('usuarios',(int)$admin['id'],$admin),InvalidArgumentException::class,null,'própria conta'));
foreach($created as $x)if(Database::one('SELECT id FROM trabalhos WHERE id=?',[$x]))ProjectService::delete($x,$admin);
$failed=count(array_filter($results,fn($r)=>$r['status']==='reprovado'));
echo json_encode(['suite'=>'integration','data'=>date(DATE_ATOM),'servidor'=>$version,'aprovados'=>count($results)-$failed,'reprovados'=>$failed,'resultados'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
exit($failed?1:0);
