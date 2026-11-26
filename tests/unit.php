<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$_SESSION=[];
$results=[];
function check(string $name, callable $test): void { global $results;try { if($test()!==true)throw new RuntimeException('O cenário deve retornar exatamente true');$results[]=['teste'=>$name,'status'=>'aprovado']; } catch(Throwable $e) { $results[]=['teste'=>$name,'status'=>'reprovado','erro'=>$e->getMessage()]; } }
function rejects(callable $fn,string $type=InvalidArgumentException::class,?string $message=null,?int $status=null): bool {
    try{$fn();}catch(Throwable $e){return $e instanceof $type && ($message===null || str_contains($e->getMessage(),$message)) && ($status===null || ($e instanceof HttpError && $e->status===$status));}return false;
}
check('Escape de HTML e aspas',fn()=>e('<script>"&</script>')==='&lt;script&gt;&quot;&amp;&lt;/script&gt;');
check('Escape de UTF-8 inválido',fn()=>str_contains(e("\xFF"),'�'));
check('CSRF aleatório e estável na sessão',function(){ $first=csrf();if(!preg_match('/^[a-f0-9]{64}$/D',$first) || csrf()!==$first)return false;unset($_SESSION['csrf']);return csrf()!==$first; });
check('CSRF ausente é recusado',function(){$_POST=[];return rejects(fn()=>verifyCsrf(),HttpError::class,'Formulário expirado',419);});
check('CSRF incorreto é recusado',function(){$_POST=['csrf'=>'invalido'];return rejects(fn()=>verifyCsrf(),HttpError::class,'Formulário expirado',419);});
check('CSRF correto é aceito',function(){$_POST=['csrf'=>csrf()];verifyCsrf();return true;});
check('Mínimo de um autor',fn()=>rejects(fn()=>ProjectService::ids([],1,5,'Autores')));
check('Máximo de cinco autores',fn()=>rejects(fn()=>ProjectService::ids([1,2,3,4,5,6],1,5,'Autores')));
check('Cinco autores aceitos',fn()=>ProjectService::ids([1,2,3,4,5],1,5,'Autores')===[1,2,3,4,5]);
check('Autores duplicados são normalizados',fn()=>ProjectService::ids([1,1,2],1,5,'Autores')===[1,2]);
check('IDs negativos são recusados',fn()=>rejects(fn()=>ProjectService::ids([-1],1,5,'Autores')));
check('Máximo de três colaboradores',fn()=>rejects(fn()=>ProjectService::ids([1,2,3,4],0,3,'Colaboradores')));
check('Colaboradores opcionais',fn()=>ProjectService::ids([],0,3,'Colaboradores')===[]);
$admin=['id'=>1,'perfil'=>'administrador'];
$input=['titulo'=>'Título','resumo'=>'Resumo','objetivos'=>'Objetivos','autores'=>[1],'colaboradores'=>[2],'responsavel_id'=>1,'edicao_id'=>1,'area_id'=>1,'instituicao_id'=>1,'curso_id'=>1,'unidade_curricular_id'=>1];
check('Responsável não pode colaborar no mesmo trabalho',fn()=>rejects(fn()=>ProjectService::save(array_merge($input,['colaboradores'=>[1]]),$admin),InvalidArgumentException::class,'Professor responsável não pode'));
check('Título obrigatório',fn()=>rejects(fn()=>ProjectService::save(array_merge($input,['titulo'=>'']),$admin),InvalidArgumentException::class,'Preencha titulo'));
check('Associação obrigatória ao curso',fn()=>rejects(fn()=>ProjectService::save(array_merge($input,['curso_id'=>0]),$admin),InvalidArgumentException::class,'relacionamentos obrigatórios'));
check('Situação fora da lista é recusada',fn()=>rejects(fn()=>ProjectService::save(array_merge($input,['situacao'=>'oculto']),$admin),InvalidArgumentException::class,'Situação inválida'));
check('Perfil desconhecido não edita projetos',fn()=>rejects(fn()=>ProjectService::save($input,['id'=>1,'perfil'=>'visitante']),HttpError::class,'Perfil sem permissão',403));
check('Editor não exclui projetos',fn()=>rejects(fn()=>ProjectService::delete(1,['id'=>2,'perfil'=>'editor']),HttpError::class,'Somente administradores',403));
check('Editor não gerencia usuários',fn()=>rejects(fn()=>CatalogService::save('usuarios',[],['id'=>2,'perfil'=>'editor'],null),HttpError::class,'Cadastro exclusivo',403));
check('Cadastro arbitrário é recusado',fn()=>rejects(fn()=>CatalogService::validateEntity('usuarios;DROP TABLE trabalhos'),HttpError::class,'Cadastro não encontrado',404));
check('Perfil desconhecido não envia arquivos',fn()=>rejects(fn()=>UploadService::store([],1,'',[]),HttpError::class,'Perfil sem permissão',403));
check('SQL de busca não contém entrada interpolada',function(){[$sql,$params]=ProjectRepository::where(ProjectRepository::filters(['q'=>"' OR 1=1 --"]));return !str_contains($sql,"' OR 1=1 --") && count($params)>0 && str_contains($sql,"t.situacao='publicado'");});
check('Pesquisa escapa curingas LIKE',function(){[, $p]=ProjectRepository::where(ProjectRepository::filters(['q'=>'100%_!']));return $p[0]==='%100!%!_!!%';});
check('Pesquisa combina nomes de múltiplos autores',function(){[, $p]=ProjectRepository::where(ProjectRepository::filters(['q'=>'Clara Lucas']));return count($p)===12 && $p[0]==='%Clara%' && $p[6]==='%Lucas%';});
check('Filtros combinados têm parâmetros separados',function(){[$sql,$p]=ProjectRepository::where(ProjectRepository::filters(['area'=>1,'edicao'=>2,'ano'=>2026]));return $p===[1,2,2026] && str_contains($sql,'t.area_id=?') && str_contains($sql,'t.edicao_id=?');});
check('Limite do texto de busca',fn()=>mb_strlen(ProjectRepository::filters(['q'=>str_repeat('á',200)])['q'])===150);
check('Favoritos têm identificador de sessão estável',function(){ $first=ProjectRepository::visitor();if(strlen($first)!==64 || ProjectRepository::visitor()!==$first)return false;unset($_SESSION['visitor']);return ProjectRepository::visitor()!==$first; });
check('Vídeo YouTube é incorporado em domínio autorizado',fn()=>UploadService::embedUrl('https://youtu.be/abcdefghijk')==='https://www.youtube-nocookie.com/embed/abcdefghijk');
check('Vídeo Vimeo é incorporado em domínio autorizado',fn()=>UploadService::embedUrl('https://vimeo.com/12345')==='https://player.vimeo.com/video/12345');
check('Domínio semelhante ao YouTube não é incorporado',fn()=>UploadService::embedUrl('https://youtube.com.evil.example/watch?v=abcdefghijk')===null);
check('Canal sem vídeo não gera iframe inválido',fn()=>UploadService::embedUrl('https://www.youtube.com/@SenacSC')===null);
check('Cartão escapa conteúdo malicioso',function(){ $cards=[['id'=>1,'area_id'=>1,'capa'=>null,'ano'=>2026,'destaque'=>0,'area'=>'Teste','titulo'=>'<script>alert(1)</script>','resumo'=>'Resumo','autores'=>'<img src=x onerror=alert(1)>','curso'=>'ADS']];ob_start();require BASE.'/views/partials/cards.php';$html=ob_get_clean();return !str_contains($html,'<script>') && str_contains($html,'&lt;script&gt;') && !str_contains($html,'<img src=x');});
check('Layout mantém rota atual para menu ativo',function(){global $path;$path='vitrine';ob_start();view('error',['status'=>404,'message'=>'Teste','title'=>'Teste']);$html=ob_get_clean();return str_contains($html,'class="active"') && str_contains($html,'href="#conteudo"');});
$failed=count(array_filter($results,fn($r)=>$r['status']==='reprovado'));
echo json_encode(['suite'=>'unit','data'=>date(DATE_ATOM),'php'=>PHP_VERSION,'aprovados'=>count($results)-$failed,'reprovados'=>$failed,'resultados'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
exit($failed?1:0);
