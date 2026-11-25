@echo off
REM =====================================================================
REM DIRPOLES-4: Script de Carga de Seeds (Windows / XAMPP)
REM Ubicación: scripts/importar_seed_nuevas.bat
REM =====================================================================

chcp 65001 >nul
echo ======================================================================
echo    DIRPOLES-4 — IMPORTADOR DE SEEDS SINTÉTICOS (WINDOWS / XAMPP)
echo ======================================================================
echo.

set SCRIPT_DIR=%~dp0
set PROJECT_DIR=%SCRIPT_DIR%..
set SEEDS_DIR=%PROJECT_DIR%\docs\bd\seed_nuevas

REM Detectar mysql.exe en XAMPP o en PATH
set MYSQL_BIN=mysql
where mysql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        set MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe
    ) else (
        echo [ERROR] No se encontro mysql.exe en el PATH ni en C:\xampp\mysql\bin\
        pause
        exit /b 1
    )
)

echo [INFO] Usando binario MySQL: %MYSQL_BIN%
echo [INFO] Importando los 8 archivos de seed en dirpoles_business...
echo ----------------------------------------------------------------------

echo [1/8] Importando seed_beneficiarios2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_beneficiarios2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [2/8] Importando seed_citas2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_citas2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [3/8] Importando seed_psicologia2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_psicologia2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [4/8] Importando seed_medicina2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_medicina2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [5/8] Importando seed_orientacion2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_orientacion2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [6/8] Importando seed_discapacidad2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_discapacidad2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [7/8] Importando seed_trabajo_social2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_trabajo_social2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo [8/8] Importando seed_referencias2.sql...
"%MYSQL_BIN%" --default-character-set=utf8mb4 -u root dirpoles_business < "%SEEDS_DIR%\seed_referencias2.sql"
if %ERRORLEVEL% NEQ 0 goto error

echo ----------------------------------------------------------------------
echo [EXITO] Todos los archivos de seed fueron aplicados con exito.
echo.
pause
exit /b 0

:error
echo.
echo [ERROR] Ocurrio un fallo durante la importacion. Abortando.
pause
exit /b 1
