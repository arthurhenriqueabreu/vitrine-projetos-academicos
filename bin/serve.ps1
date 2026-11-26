$projectRoot=Split-Path $PSScriptRoot -Parent
Set-Location -LiteralPath $projectRoot
& C:\xampp\php\php.exe -d extension=gd -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8080 -t public public/router.php
