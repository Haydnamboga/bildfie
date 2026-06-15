@echo off
REM ============================================================
REM  bildfie - start the site locally (double-click this file)
REM  Serves the project at http://localhost:8000
REM  (PHP's built-in server; .htaccess is ignored locally, which is fine.)
REM  If your XAMPP is not at C:\xampp, edit the PHP path below.
REM ============================================================
title bildfie - local server (close to stop)
cd /d "%~dp0"

set "PHP=C:\xampp\php\php.exe"
if not exist "%PHP%" (
  echo Could not find PHP at %PHP%
  echo Edit start-local.bat and set the correct path to your xampp\php\php.exe
  pause
  exit /b
)

echo ============================================
echo   bildfie running at  http://localhost:8000
echo   Keep this window open. Close it to stop.
echo ============================================
start "" http://localhost:8000
"%PHP%" -S localhost:8000
