<?php
require dirname(__DIR__).'/app/bootstrap.php';
if(PHP_SAPI!=='cli')exit;
[$script,$name,$email,$role]=array_pad($argv,4,'');
$pw=env('NEW_USER_PASSWORD');
try {
    $first=(int)Database::one('SELECT COUNT(*) n FROM usuarios')['n']===0;
    if($first) {
        if($role!=='administrador' || trim($name)==='' || mb_strlen($name)>80 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen($pw)<12 || strlen($pw)>72)throw new InvalidArgumentException('Primeira conta: informe nome, e-mail válido, perfil administrador e NEW_USER_PASSWORD com 12 a 72 caracteres.');
        $db=Database::connection();$db->beginTransaction();
        try {
            Database::run('INSERT INTO usuarios (nome,email,senha_hash,perfil) VALUES (?,?,?,?)',[$name,$email,password_hash($pw,PASSWORD_DEFAULT),'administrador']);
            $id=(int)$db->lastInsertId();ProjectService::audit('criar','usuarios',$id,$id);$db->commit();
        }catch(Throwable $ex){$db->rollBack();throw $ex;}
    }else {
        $actor=Database::one("SELECT id,perfil FROM usuarios WHERE id=? AND perfil='administrador' AND ativo=1",[(int)env('ACTING_ADMIN_ID')]);
        if(!$actor)throw new InvalidArgumentException('Defina ACTING_ADMIN_ID com um administrador ativo existente.');
        CatalogService::save('usuarios',['nome'=>$name,'email'=>$email,'perfil'=>$role,'ativo'=>1,'senha'=>$pw],$actor,null);
    }
    echo "Usuário criado.\n";
}catch(Throwable $e){fwrite(STDERR,'Falha: '.$e->getMessage()."\n");exit(1);}
