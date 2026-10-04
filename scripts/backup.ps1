# =====================================================
# SAUVEGARDE COMPLETE - Parc Informatique
# =====================================================

$date = Get-Date -Format "yyyy-MM-dd_HH-mm"
$projectRoot = "C:\wamp64\www\parc-informatique"
$backupDir = "$projectRoot\storage\backups"
$backupRoot = "$backupDir\sauvegarde_$date"
$mysqldump = "C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe"

Write-Host ""
Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host "  Sauvegarde Parc Informatique" -ForegroundColor Cyan
Write-Host "  Date : $date" -ForegroundColor Cyan
Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Creer les dossiers
Write-Host "[1/4] Creation des dossiers..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null
Write-Host "      OK" -ForegroundColor Green
Write-Host ""

# 2. Sauvegarder la BDD
Write-Host "[2/4] Export de la base de donnees..." -ForegroundColor Yellow
$sqlFile = "$backupRoot\parc_informatique.sql"

if (Test-Path $mysqldump) {
    & $mysqldump -u root parc_informatique > $sqlFile 2>$null
    if ((Test-Path $sqlFile) -and (Get-Item $sqlFile).Length -gt 0) {
        $size = [math]::Round((Get-Item $sqlFile).Length / 1KB, 2)
        Write-Host "      OK : $size Ko" -ForegroundColor Green
    } else {
        Write-Host "      ERREUR : export vide ou echoue" -ForegroundColor Red
    }
} else {
    Write-Host "      ERREUR : mysqldump introuvable" -ForegroundColor Red
}
Write-Host ""

# 3. Sauvegarder le code source
Write-Host "[3/4] Copie du code source..." -ForegroundColor Yellow
$codeDir = "$backupRoot\code"
New-Item -ItemType Directory -Path $codeDir -Force | Out-Null

$excludeDirs = @('vendor', 'storage', '.git', 'node_modules', '.idea', '.vscode')

Get-ChildItem -Path $projectRoot -Force | Where-Object {
    $_.Name -notin $excludeDirs
} | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination "$codeDir\$($_.Name)" -Recurse -Force
}

Write-Host "      OK" -ForegroundColor Green
Write-Host ""

# 4. Creer l'archive ZIP
Write-Host "[4/4] Creation de l'archive ZIP..." -ForegroundColor Yellow
$zipFile = "$backupDir\sauvegarde_$date.zip"

if (Test-Path $zipFile) { Remove-Item $zipFile -Force }

Compress-Archive -Path "$backupRoot\*" -DestinationPath $zipFile -Force

Remove-Item $backupRoot -Recurse -Force

$zipSize = [math]::Round((Get-Item $zipFile).Length / 1MB, 2)
Write-Host "      OK : $zipSize Mo" -ForegroundColor Green
Write-Host ""

# Resume
Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host "  SAUVEGARDE TERMINEE" -ForegroundColor Green
Write-Host "=====================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Fichier : $zipFile" -ForegroundColor White
Write-Host "Taille  : $zipSize Mo" -ForegroundColor White
Write-Host ""
Write-Host "[INFO] Copie ce fichier sur une cle USB ou un cloud." -ForegroundColor Yellow
Write-Host ""