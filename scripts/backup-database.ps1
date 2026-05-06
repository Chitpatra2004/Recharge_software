param(
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path,
    [string]$MysqlDumpPath = 'D:\wamp64(1)\bin\mysql\mysql9.1.0\bin\mysqldump.exe',
    [int]$RetentionDays = 14
)

$ErrorActionPreference = 'Stop'

function Read-DotEnv {
    param([string]$Path)

    $values = @{}

    Get-Content -LiteralPath $Path | ForEach-Object {
        $line = $_.Trim()
        if ($line -eq '' -or $line.StartsWith('#') -or -not $line.Contains('=')) {
            return
        }

        $parts = $line.Split('=', 2)
        $key = $parts[0].Trim()
        $value = $parts[1].Trim()

        if (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'"))) {
            $value = $value.Substring(1, $value.Length - 2)
        }

        $values[$key] = $value
    }

    return $values
}

function Protect-File {
    param(
        [string]$InputPath,
        [string]$OutputPath,
        [string]$Base64Key
    )

    $keyMaterial = [Convert]::FromBase64String($Base64Key)
    if ($keyMaterial.Length -lt 32) {
        throw 'BACKUP_ENCRYPTION_KEY must decode to at least 32 bytes.'
    }

    $salt = New-Object byte[] 16
    $iv = New-Object byte[] 16
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    $rng.GetBytes($salt)
    $rng.GetBytes($iv)
    $rng.Dispose()

    $kdf = New-Object System.Security.Cryptography.Rfc2898DeriveBytes($keyMaterial, $salt, 200000, [System.Security.Cryptography.HashAlgorithmName]::SHA256)
    $aesKey = $kdf.GetBytes(32)
    $kdf.Dispose()

    $aes = [System.Security.Cryptography.Aes]::Create()
    $aes.Mode = [System.Security.Cryptography.CipherMode]::CBC
    $aes.Padding = [System.Security.Cryptography.PaddingMode]::PKCS7
    $aes.Key = $aesKey
    $aes.IV = $iv

    $out = [System.IO.File]::Create($OutputPath)
    $in = [System.IO.File]::OpenRead($InputPath)

    try {
        $magic = [System.Text.Encoding]::ASCII.GetBytes('CPDB1')
        $out.Write($magic, 0, $magic.Length)
        $out.Write($salt, 0, $salt.Length)
        $out.Write($iv, 0, $iv.Length)

        $encryptor = $aes.CreateEncryptor()
        $crypto = New-Object System.Security.Cryptography.CryptoStream($out, $encryptor, [System.Security.Cryptography.CryptoStreamMode]::Write)
        try {
            $in.CopyTo($crypto)
            $crypto.FlushFinalBlock()
        } finally {
            $crypto.Dispose()
            $encryptor.Dispose()
        }
    } finally {
        $in.Dispose()
        $out.Dispose()
        $aes.Dispose()
    }
}

$envPath = Join-Path $ProjectRoot '.env'
$config = Read-DotEnv -Path $envPath

foreach ($required in @('DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'BACKUP_ENCRYPTION_KEY')) {
    if (-not $config.ContainsKey($required) -or [string]::IsNullOrWhiteSpace($config[$required])) {
        throw "Missing required .env value: $required"
    }
}

if (-not (Test-Path -LiteralPath $MysqlDumpPath)) {
    throw "mysqldump.exe not found: $MysqlDumpPath"
}

$backupDir = Join-Path $ProjectRoot 'storage\app\private\backups\database'
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$tempSql = Join-Path $env:TEMP "coldpay-db-$stamp.sql"
$backupPath = Join-Path $backupDir "coldpay-db-$stamp.sql.enc"

$oldMysqlPwd = $env:MYSQL_PWD
$env:MYSQL_PWD = $config['DB_PASSWORD']

try {
    & $MysqlDumpPath `
        --host=$($config['DB_HOST']) `
        --port=$($config['DB_PORT']) `
        --user=$($config['DB_USERNAME']) `
        --single-transaction `
        --quick `
        --no-tablespaces `
        --routines `
        --triggers `
        --skip-events `
        --result-file=$tempSql `
        $config['DB_DATABASE']

    if ($LASTEXITCODE -ne 0) {
        throw "mysqldump failed with exit code $LASTEXITCODE"
    }

    Protect-File -InputPath $tempSql -OutputPath $backupPath -Base64Key $config['BACKUP_ENCRYPTION_KEY']
} finally {
    if ($null -eq $oldMysqlPwd) {
        Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
    } else {
        $env:MYSQL_PWD = $oldMysqlPwd
    }

    Remove-Item -LiteralPath $tempSql -Force -ErrorAction SilentlyContinue
}

Get-ChildItem -LiteralPath $backupDir -Filter '*.enc' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$RetentionDays) } |
    Remove-Item -Force

Write-Output "Encrypted database backup created: $backupPath"
