<?php
declare(strict_types=1);
// Instalação local explícita. Não altera bancos preexistentes.
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__).'/app/bootstrap.php';
try {
    $name=env('DB_DATABASE','vitrine_academica');
    if (!preg_match('/^[a-zA-Z0-9_]+$/D',$name)) throw new RuntimeException('Nome de banco inválido.');
    $db=new PDO('mysql:host='.env('DB_HOST','127.0.0.1').';port='.env('DB_PORT','3306').';charset=utf8mb4',env('INSTALL_DB_USER','root'),env('INSTALL_DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $exists=$db->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');$exists->execute([$name]);
    if ($exists->fetchColumn()) throw new RuntimeException('O banco já existe. Use bin/setup.php para um banco vazio configurado, ou escolha outro DB_DATABASE. Nenhum dado foi sobrescrito.');
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco criado. Configure DB_USERNAME/DB_PASSWORD com um usuário restrito antes de executar setup.php.\n";
} catch(Throwable $ex) { fwrite(STDERR,'Instalação não concluída: '.$ex->getMessage()."\n");exit(1); }
