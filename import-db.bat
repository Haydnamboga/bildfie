@echo off
REM ============================================================
REM  bildfie - create the local database and import your data
REM  Run this ONCE (double-click).
REM  Requirements:
REM    1. XAMPP Control Panel -> MySQL is STARTED.
REM    2. Your data file  remissio_bildfie.sql  is in THIS folder.
REM  If your XAMPP is not at C:\xampp, edit the MYSQL path below.
REM ============================================================
title bildfie - import database
cd /d "%~dp0"

set "MYSQL=C:\xampp\mysql\bin\mysql.exe"
if not exist "%MYSQL%" (
  echo Could not find MySQL at %MYSQL%
  echo Edit import-db.bat and set the correct path to your xampp\mysql\bin\mysql.exe
  pause
  exit /b
)

echo Creating local database "bildfie" ...
"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS bildfie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if errorlevel 1 (
  echo.
  echo  !! Could not reach MySQL. Open XAMPP Control Panel, click Start on MySQL,
  echo     then run this file again.
  pause
  exit /b
)

if not exist "%~dp0remissio_bildfie.sql" (
  echo.
  echo  The database was created, but no data file was found.
  echo  Put  remissio_bildfie.sql  in this folder and run this again,
  echo  or import it via  http://localhost/phpmyadmin  into the "bildfie" database.
  pause
  exit /b
)

echo Importing remissio_bildfie.sql  (this can take a few seconds) ...
"%MYSQL%" -u root bildfie < "%~dp0remissio_bildfie.sql"
echo.
echo  Done. Your local database is ready. You can close this window.
pause
