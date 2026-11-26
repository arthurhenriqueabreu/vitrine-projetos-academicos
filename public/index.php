<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$path=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
$basePath=trim(parse_url(env('APP_URL'),PHP_URL_PATH)??'','/');
if($basePath!=='' && str_starts_with($path,$basePath)) $path=trim(substr($path,strlen($basePath)),'/');
$method=$_SERVER['REQUEST_METHOD'];
try {
    if ($method==='POST') verifyCsrf();
    if ($path==='catalogo') {
        $f=ProjectRepository::filters($_GET);$f['favoritos']=false;$f['pagina']=1;
        $list=ProjectRepository::listing($f);$cards=$list['rows'];
        for($page=2;$page<=$list['pages'];$page++) { $f['pagina']=$page;$more=ProjectRepository::listing($f);array_push($cards,...$more['rows']); }
        view('print-catalog',['title'=>'Catálogo de projetos','cards'=>$cards]);
    } elseif ($path==='login') {
        if($method==='POST') {
            if(Auth::login(trim((string)($_POST['email']??'')),(string)($_POST['senha']??''))) redirect('admin');
            http_response_code(422);$error='E-mail ou senha inválidos.';
        }
        view('login',['title'=>'Acesso da equipe','error'=>$error??null]);
    } elseif($path==='logout' && $method==='POST') { Auth::logout();redirect(''); }
    elseif($path==='' || $path==='vitrine' || $path==='favoritos') {
        $f=ProjectRepository::filters($_GET);if($path==='favoritos')$f['favoritos']=true;
        $list=ProjectRepository::listing($f);$lookups=ProjectRepository::lookups();$counts=ProjectRepository::counts($f);
        $featured=$path===''?ProjectRepository::listing(ProjectRepository::filters([]),true):null;
        view($path===''?'home':'catalog',compact('f','list','lookups','counts','featured')+['title'=>$path==='favoritos'?'Meus favoritos':'Vitrine de projetos','favoritePage'=>$path==='favoritos']);
    } elseif($path==='edicoes') {
        $editions=Database::all("SELECT e.*,COUNT(t.id) total FROM edicoes e LEFT JOIN trabalhos t ON t.edicao_id=e.id AND t.situacao='publicado' GROUP BY e.id,e.nome,e.ano,e.descricao ORDER BY e.ano DESC,e.id DESC");
        view('editions',['title'=>'Histórico de edições','editions'=>$editions]);
    } elseif($path==='privacidade') view('privacy',['title'=>'Privacidade e acessibilidade']);
    elseif(preg_match('~^projeto/(\d+)$~',$path,$m)) {
        $project=ProjectRepository::detail((int)$m[1]);
        if(empty($_SESSION['viewed'][$project['id']])) {Database::run('UPDATE trabalhos SET visualizacoes=visualizacoes+1 WHERE id=?',[$project['id']]);$_SESSION['viewed'][$project['id']]=true;$project['visualizacoes']++;}
        $favorite=Database::one('SELECT trabalho_id FROM favoritos WHERE visitante_hash=? AND trabalho_id=?',[ProjectRepository::visitor(),$project['id']]);
        view('detail',['title'=>$project['titulo'],'project'=>$project,'favorite'=>$favorite]);
    } elseif(preg_match('~^favoritar/(\d+)$~',$path,$m) && $method==='POST') {
        $id=(int)$m[1];ProjectRepository::detail($id);$visitor=ProjectRepository::visitor();
        if(Database::one('SELECT trabalho_id FROM favoritos WHERE visitante_hash=? AND trabalho_id=?',[$visitor,$id])) Database::run('DELETE FROM favoritos WHERE visitante_hash=? AND trabalho_id=?',[$visitor,$id]);
        else Database::run('INSERT IGNORE INTO favoritos (visitante_hash,trabalho_id) VALUES (?,?)',[$visitor,$id]);
        redirect('projeto/'.$id);
    } elseif($path==='limpar-favoritos' && $method==='POST') { Database::run('DELETE FROM favoritos WHERE visitante_hash=?',[ProjectRepository::visitor()]);flash('Favoritos removidos.');redirect('favoritos'); }
    elseif(preg_match('~^arquivo/(\d+)$~',$path,$m)) {
        $file=Database::one('SELECT a.*,t.situacao FROM arquivos a JOIN trabalhos t ON t.id=a.trabalho_id WHERE a.id=?',[(int)$m[1]]);
        if(!$file || ($file['situacao']!=='publicado' && !Auth::user())) throw new HttpError(404,'Arquivo não encontrado.');
        $location=BASE.'/storage/uploads/'.basename($file['nome_storage']);if(!is_file($location))throw new HttpError(404,'Arquivo indisponível.');
        header('Content-Type: '.$file['mime']);header('Content-Length: '.filesize($location));
        header('Content-Disposition: '.(isset($_GET['download'])?'attachment':'inline').'; filename="'.preg_replace('/[^a-zA-Z0-9_.-]/','_',$file['nome_original']).'"');
        header("Content-Security-Policy: sandbox; default-src 'none'");header('Cache-Control: private, max-age=300');readfile($location);
    } elseif(str_starts_with($path,'admin')) {
        $user=Auth::requireUser();$lookups=ProjectRepository::lookups();
        if($path==='admin') {
            $stats=Database::one("SELECT COUNT(*) total,COALESCE(SUM(situacao='publicado'),0) publicados,COALESCE(SUM(situacao='rascunho'),0) rascunhos,COALESCE(SUM(situacao='arquivado'),0) arquivados,COALESCE(SUM(visualizacoes),0) visualizacoes FROM trabalhos");
            $recent=Database::all('SELECT t.id,t.titulo,t.situacao,t.atualizado_em,e.nome edicao FROM trabalhos t JOIN edicoes e ON e.id=t.edicao_id ORDER BY t.atualizado_em DESC LIMIT 8');
            view('admin/dashboard',compact('stats','recent','user')+['title'=>'Visão geral']);
        } elseif($path==='admin/projetos') {
            $q=mb_substr(trim((string)($_GET['q']??'')),0,150);
            $rows=Database::all('SELECT t.*,e.nome edicao FROM trabalhos t JOIN edicoes e ON e.id=t.edicao_id WHERE t.titulo LIKE ? ORDER BY t.id DESC',['%'.$q.'%']);
            view('admin/projects',compact('rows','q','user')+['title'=>'Gerenciar projetos']);
        } elseif($path==='admin/projetos/novo' || preg_match('~^admin/projetos/(\d+)$~',$path,$m)) {
            $id=isset($m[1])?(int)$m[1]:null;
            $project=$id?ProjectRepository::detail($id,false):[];
            if($method==='POST') {
                try { $id=ProjectService::save($_POST,$user,$id);flash('Projeto salvo com sucesso.');redirect('admin/projetos/'.$id); }
                catch(InvalidArgumentException|PDOException $ex) { $error=$ex instanceof PDOException?'Confira os relacionamentos selecionados. Curso, unidade curricular, instituição, área e subárea devem ser compatíveis.':$ex->getMessage();$project=array_merge($project,$_POST);$project['autores']=array_map(fn($v)=>['id'=>(int)$v],$_POST['autores']??[]);$project['colaboradores']=array_map(fn($v)=>['id'=>(int)$v],$_POST['colaboradores']??[]);$project['palavras']=array_map(fn($v)=>['id'=>(int)$v],$_POST['palavras']??[]);http_response_code(422); }
            }
            view('admin/project-form',compact('id','project','lookups','user')+['title'=>$id?'Editar projeto':'Novo projeto','error'=>$error??null]);
        } elseif(preg_match('~^admin/projetos/(\d+)/(excluir|upload|link|editar-link|descrever-arquivo|remover-arquivo|remover-link)$~',$path,$m) && $method==='POST') {
            $id=(int)$m[1];$action=$m[2];ProjectRepository::detail($id,false);
            try {
                if($action==='excluir') {ProjectService::delete($id,$user);flash('Projeto excluído.');redirect('admin/projetos');}
                if($action==='upload') UploadService::store($_FILES['arquivo']??[],$id,(string)($_POST['texto_alternativo']??''),$user);
                elseif($action==='link') UploadService::link($_POST,$id,$user);
                elseif($action==='editar-link') UploadService::link($_POST,$id,$user,(int)($_POST['material_id']??0));
                elseif($action==='descrever-arquivo') {
                    $mid=(int)($_POST['material_id']??0);$alt=trim((string)($_POST['texto_alternativo']??''));
                    if($alt==='' || mb_strlen($alt)>200)throw new InvalidArgumentException('Informe uma descrição de até 200 caracteres.');
                    if(!Database::one('SELECT id FROM arquivos WHERE id=? AND trabalho_id=?',[$mid,$id]))throw new HttpError(404,'Material não encontrado.');
                    Database::run('UPDATE arquivos SET texto_alternativo=? WHERE id=? AND trabalho_id=?',[$alt,$mid,$id]);
                    ProjectService::audit('descrever','arquivos',$mid,(int)$user['id']);
                }
                elseif($action==='remover-arquivo') {
                    $file=Database::one('SELECT * FROM arquivos WHERE id=? AND trabalho_id=?',[(int)($_POST['material_id']??0),$id]);if(!$file)throw new HttpError(404,'Material não encontrado.');
                    Database::run('DELETE FROM arquivos WHERE id=? AND trabalho_id=?',[$file['id'],$id]);$loc=BASE.'/storage/uploads/'.basename($file['nome_storage']);if(is_file($loc))unlink($loc);ProjectService::audit('excluir','arquivos',(int)$file['id'],(int)$user['id']);
                } elseif($action==='remover-link') { $mid=(int)($_POST['material_id']??0);if(!Database::run('DELETE FROM links WHERE id=? AND trabalho_id=?',[$mid,$id])->rowCount())throw new HttpError(404,'Link não encontrado.');ProjectService::audit('excluir','links',$mid,(int)$user['id']); }
                flash('Materiais atualizados.');
            }catch(InvalidArgumentException $ex){flash($ex->getMessage(),'error');}
            redirect('admin/projetos/'.$id.'#materiais');
        } elseif(preg_match('~^admin/cadastros/([a-z_]+)(?:/(\d+|novo)(?:/(excluir))?)?$~',$path,$m)) {
            $user=Auth::requireUser(true);$entity=$m[1];[$label,$fields]=CatalogService::validateEntity($entity);
            $id=isset($m[2]) && $m[2]!=='novo'?(int)$m[2]:null;$form=isset($m[2]);$record=$id?Database::one("SELECT * FROM $entity WHERE id=?",[$id]):[];
            if($id && !$record)throw new HttpError(404,'Registro não encontrado.');
            if($method==='POST') {
                try { if(isset($m[3]))CatalogService::delete($entity,(int)$id,$user);else CatalogService::save($entity,$_POST,$user,$id);flash('Cadastro atualizado.');redirect('admin/cadastros/'.$entity); }
                catch(InvalidArgumentException|PDOException $ex) { $error=$ex instanceof PDOException?'Operação não permitida: existe um registro com esse valor ou relacionamentos dependentes.':$ex->getMessage();$record=array_merge($record??[],$_POST);http_response_code(422); }
            }
            $rows=Database::all('SELECT '.($entity==='usuarios'?'id,nome,email,perfil,ativo':'*')." FROM $entity ORDER BY nome");
            view('admin/catalog',compact('entity','label','fields','id','form','record','rows','lookups','user')+['title'=>$label,'error'=>$error??null]);
        } elseif($path==='admin/auditoria') {
            Auth::requireUser(true);$rows=Database::all('SELECT a.*,u.nome usuario FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.id DESC LIMIT 200');view('admin/audit',compact('rows','user')+['title'=>'Registro de atividades']);
        } else throw new HttpError(404,'Página não encontrada.');
    } else throw new HttpError(404,'Página não encontrada.');
} catch(Throwable $ex) {
    $status=$ex instanceof HttpError?$ex->status:500;http_response_code($status);
    $message=$ex instanceof HttpError?$ex->getMessage():'Não foi possível concluir a solicitação. Tente novamente ou contate a equipe.';
    if($status===500)error_log((string)$ex);
    view('error',['title'=>'Solicitação não concluída','status'=>$status,'message'=>$message]);
}
