<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
if(PHP_SAPI!=='cli')exit;
$ok=true;
foreach(['pdo_mysql','mbstring','fileinfo','gd'] as $ext) { $present=extension_loaded($ext);echo ($present?'OK ':'FALHA ').$ext."\n";$ok=$ok&&$present; }
foreach(['storage/uploads','storage/logs','storage/backups'] as $dir) {
    $probe=BASE.'/'.$dir.'/.probe-'.bin2hex(random_bytes(8));
    $present=@file_put_contents($probe,'probe')===5;
    if($present)unlink($probe);
    echo ($present?'OK ':'FALHA ').$dir." gravável (gravação real)\n";$ok=$ok&&$present;
}
try { $version=Database::one('SELECT VERSION() versao')['versao'];echo 'OK conexão PDO; servidor '.$version."\n";if(str_contains(strtolower($version),'mariadb'))echo "ATENÇÃO: MariaDB não comprova RNF17 em MySQL. Valide também em MySQL 8.\n"; }
catch(Throwable $ex) { echo 'FALHA conexão PDO: '.$ex->getMessage()."\n";$ok=false; }
exit($ok?0:1);
