$ErrorActionPreference = 'Stop'
$Port = 8793
$project = Split-Path -Parent $PSScriptRoot
$local = Join-Path $project '.local'
$wp = Join-Path $local 'wordpress'
$theme = Join-Path $project 'theme/brow-boutique'
$url = "http://127.0.0.1:$Port"
New-Item -ItemType Directory -Force -Path $local | Out-Null
if (-not (Test-Path "$wp/wp-load.php")) {
    if (-not (Test-Path "$local/wordpress.zip")) {
        Invoke-WebRequest 'https://wordpress.org/latest.zip' -OutFile "$local/wordpress.zip"
    }
    Expand-Archive -Path "$local/wordpress.zip" -DestinationPath $local -Force
}
if (-not (Test-Path "$wp/wp-content/plugins/sqlite-database-integration/db.copy")) {
    if (-not (Test-Path "$local/sqlite.zip")) {
        Invoke-WebRequest 'https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip' -OutFile "$local/sqlite.zip"
    }
    Expand-Archive -Path "$local/sqlite.zip" -DestinationPath "$wp/wp-content/plugins" -Force
}
if (-not (Test-Path "$wp/wp-content/db.php")) {
    Copy-Item -LiteralPath "$wp/wp-content/plugins/sqlite-database-integration/db.copy" -Destination "$wp/wp-content/db.php"
}
if (-not (Test-Path "$wp/wp-content/themes/brow-boutique")) {
    New-Item -ItemType Junction -Path "$wp/wp-content/themes/brow-boutique" -Target $theme | Out-Null
}
$php = Get-Command php -ErrorAction Stop
$extDir = Join-Path (Split-Path -Parent $php.Source) 'ext'
$phpFlags = @('-d', "extension_dir=$extDir", '-d', 'extension=pdo_sqlite', '-d', 'extension=sqlite3', '-d', 'extension=mbstring', '-d', 'extension=fileinfo', '-d', 'extension=gd')
& $php.Source @phpFlags "$PSScriptRoot/seed-local.php" $wp $url
if ($LASTEXITCODE -ne 0) { throw 'WordPress installation failed.' }
& $php.Source @phpFlags -S "127.0.0.1:$Port" -t $wp
