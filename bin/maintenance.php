<?php
require dirname(__DIR__).'/app/bootstrap.php';
if(PHP_SAPI!=='cli')exit;
Database::run('DELETE FROM login_tentativas WHERE criado_em < DATE_SUB(NOW(),INTERVAL 1 DAY)');
Database::run('DELETE FROM favoritos WHERE criado_em < DATE_SUB(NOW(),INTERVAL 30 DAY)');
echo "Retenção aplicada. Auditoria: revisar política institucional antes de eliminar registros.\n";
