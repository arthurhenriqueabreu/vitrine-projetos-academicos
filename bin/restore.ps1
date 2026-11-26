param([Parameter(Mandatory=$true)][string]$SqlFile,[string]$MySqlBin='C:\xampp\mysql\bin')
$ErrorActionPreference='Stop'
if (!$env:MYSQL_BACKUP_CONFIG -or !(Test-Path -LiteralPath $env:MYSQL_BACKUP_CONFIG)) { throw 'Defina MYSQL_BACKUP_CONFIG com arquivo de cliente protegido.' }
if (!$env:DB_DATABASE -or $env:DB_DATABASE -notmatch '^[a-zA-Z0-9_]+$') { throw 'Defina DB_DATABASE com o banco de destino vazio e isolado.' }
$absoluteSql=(Resolve-Path -LiteralPath $SqlFile).Path.Replace('\','/')
if ($absoluteSql -match '[\r\n";]') { throw 'Nome de arquivo não suportado.' }
& (Join-Path $MySqlBin 'mysql.exe') "--defaults-extra-file=$env:MYSQL_BACKUP_CONFIG" --default-character-set=utf8mb4 $env:DB_DATABASE -e "source $absoluteSql"
if ($LASTEXITCODE -ne 0) { throw 'Restauração falhou.' }
Write-Output 'SQL restaurado. Restaure uploads do mesmo conjunto e execute os testes de integridade.'
