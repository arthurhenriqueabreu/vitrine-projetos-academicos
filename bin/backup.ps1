param([string]$MySqlBin = 'C:\xampp\mysql\bin')
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$targetDir = Join-Path $projectRoot 'storage\backups'
New-Item -ItemType Directory -Force $targetDir | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$targetFile = Join-Path $targetDir "vitrine-$stamp.sql"
# O defaults-extra-file deve estar fora do repositório e ter ACL restrita.
if (!$env:MYSQL_BACKUP_CONFIG -or !(Test-Path -LiteralPath $env:MYSQL_BACKUP_CONFIG)) { throw 'Defina MYSQL_BACKUP_CONFIG com um arquivo [client] protegido contendo host, port, user e password.' }
if (!$env:DB_DATABASE -or $env:DB_DATABASE -notmatch '^[a-zA-Z0-9_]+$') { throw 'Defina DB_DATABASE.' }
& (Join-Path $MySqlBin 'mysqldump.exe') "--defaults-extra-file=$env:MYSQL_BACKUP_CONFIG" --single-transaction --routines --triggers --hex-blob --default-character-set=utf8mb4 "--result-file=$targetFile" $env:DB_DATABASE
if ($LASTEXITCODE -ne 0) { throw 'Backup falhou.' }
Compress-Archive -LiteralPath $targetFile,(Join-Path $projectRoot 'storage\uploads') -DestinationPath (Join-Path $targetDir "vitrine-$stamp.zip")
Get-FileHash -Algorithm SHA256 -LiteralPath $targetFile
Write-Output 'Backup do banco e uploads concluído. Guarde cópia protegida fora do servidor.'
