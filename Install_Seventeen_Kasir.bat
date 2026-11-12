@echo off
setlocal
title Instalasi & Konfigurasi Shortcut Seventeen Kasir POS
echo ===================================================================
echo   INSTALASI APLIKASI KASIR SEVENTEEN PETSHOP (DESKTOP WINDOWS)
echo ===================================================================
echo.

set "TARGET_EXE=%~dp0dist\SeventeenKasir.exe"
set "TARGET_ICO=%~dp0dist\app.ico"
set "DESKTOP_PATH=%USERPROFILE%\Desktop\Seventeen Kasir POS.lnk"

if not exist "%TARGET_EXE%" (
    echo [ERROR] Berkas SeventeenKasir.exe tidak ditemukan di folder dist!
    pause
    exit /b 1
)

echo [1/3] Memeriksa keberadaan berkas aplikasi... [OK]
echo [2/3] Memasang Shortcut di Layar Desktop...
powershell -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%DESKTOP_PATH%'); $s.TargetPath = '%TARGET_EXE%'; $s.WorkingDirectory = '%~dp0dist'; $s.IconLocation = '%TARGET_ICO%,0'; $s.Description = 'Aplikasi Kasir Seventeen Petshop'; $s.Save()"
echo       Shortcut Desktop berhasil dipasang: %DESKTOP_PATH%

echo [3/3] Selesai!
echo.
echo ===================================================================
echo   Pemasangan selesai! Anda dapat membuka aplikasi sekarang.
echo ===================================================================
echo.
set /p START_NOW="Apakah ingin langsung menjalankan aplikasi sekarang? (Y/N): "
if /i "%START_NOW%"=="Y" (
    start "" "%TARGET_EXE%"
)
exit /b 0

