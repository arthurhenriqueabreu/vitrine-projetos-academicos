param([string]$Php='C:\xampp\php\php.exe')
$ErrorActionPreference='Stop'
$projectRoot=Split-Path $PSScriptRoot -Parent
Set-Location -LiteralPath $projectRoot
if (!(Test-Path -LiteralPath '.env')) { throw 'Copie .env.example para .env e configure o MySQL conforme README.md.' }
& $Php -d extension=gd bin/diagnose.php
if ($LASTEXITCODE -ne 0) { throw 'Corrija o diagnóstico antes de iniciar.' }
& $Php -d extension=gd -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8080 -t public public/router.php
