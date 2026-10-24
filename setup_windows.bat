@echo off
setlocal EnableExtensions EnableDelayedExpansion

title DIRPOLES-4 - Instalador y Configurador para Windows (XAMPP)

echo =====================================================================
echo          DIRPOLES-4 - Configuracion Automatica para Windows (XAMPP)
echo =====================================================================
echo.

set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"
set "SECURITY_SQL=docs\bd\dirpoles_security_actualizada.sql"
set "BUSINESS_SQL=docs\bd\dirpoles_business_actualizada.sql"
set "IMPORT_DATABASES=0"
if /I "%~1"=="--import-databases" set "IMPORT_DATABASES=1"

if not exist "%SECURITY_SQL%" (
    echo [ERROR] No se encontro %SECURITY_SQL%.
    exit /b 1
)
if not exist "%BUSINESS_SQL%" (
    echo [ERROR] No se encontro %BUSINESS_SQL%.
    exit /b 1
)

:: ---------------------------------------------------------------
:: 1. VARIABLES DE ENTORNO (.env)
:: ---------------------------------------------------------------
echo [1/6] Verificando variables de entorno (.env)...
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo   - Archivo .env creado a partir de .env.example
        echo   - Para XAMPP por defecto: DB_USER=root y DB_PASS vacio ya estan listos.
    ) else (
        echo   [ERROR] No se encontro .env.example
    )
) else (
    echo   - El archivo .env ya existe. Se conserva la configuracion.
)

:: ---------------------------------------------------------------
:: 2. CARPETAS DEL SISTEMA
:: ---------------------------------------------------------------
echo.
echo [2/6] Creando carpetas necesarias del sistema...
if not exist "logs" mkdir "logs"
if not exist "uploads" mkdir "uploads"
if not exist "app\Config\Keys" mkdir "app\Config\Keys"
echo   - Directorios logs/, uploads/ y app/Config/Keys/ listos.

:: ---------------------------------------------------------------
:: 3. GENERAR LLAVES CRIPTOGRAFICAS RSA (Login y JWT RS256)
:: ---------------------------------------------------------------
echo.
echo [3/6] Generando Llaves Criptograficas RSA (Login y JWT)...

set "PHP_BIN="
where php >nul 2>nul
if !errorlevel! equ 0 (
    set "PHP_BIN=php"
) else (
    if exist "C:\xampp\php\php.exe" (
        set "PHP_BIN=C:\xampp\php\php.exe"
    )
)
if not defined PHP_BIN (
    echo   [ERROR] No se encontro PHP CLI. Agrega PHP al PATH o verifica C:\xampp\php.
    exit /b 1
)
for %%E in (openssl pdo_mysql mbstring fileinfo) do (
    "%PHP_BIN%" -m | findstr /I /R /C:"^%%E$" >nul
    if !errorlevel! neq 0 (
        echo   [ERROR] Falta la extension PHP: %%E
        exit /b 1
    )
)
"%PHP_BIN%" "scripts\generar_llaves.php"
if !errorlevel! neq 0 (
    echo   [ERROR] No se pudieron generar las llaves RSA.
    exit /b 1
)
for %%K in (login_private login_public jwt_private jwt_public) do (
    if not exist "app\Config\Keys\%%K.pem" (
        echo   [ERROR] Falta la llave app\Config\Keys\%%K.pem
        exit /b 1
    )
)

:: ---------------------------------------------------------------
:: 4. DEPENDENCIAS COMPOSER (PHP 8.2 y 8.3)
:: ---------------------------------------------------------------
echo.
echo [4/6] Instalando dependencias con Composer...
where composer >nul 2>nul
if !errorlevel! equ 0 (
    echo   - Ejecutando: composer install --no-interaction
    composer install --no-interaction
    if !errorlevel! equ 0 (
        echo   - Dependencias instaladas correctamente.
    ) else (
        echo   - Reintentando instalacion ligera: composer install --no-dev --no-interaction
        composer install --no-dev --no-interaction
        if !errorlevel! neq 0 (
            echo   [ERROR] Composer no pudo instalar las dependencias.
            exit /b 1
        )
    )
) else (
    echo   [ERROR] Composer CLI no detectado en el PATH de Windows.
    echo   Instala Composer y vuelve a ejecutar setup_windows.bat.
    exit /b 1
)

:: ---------------------------------------------------------------
:: 5. IMPORTACION DE BASES DE DATOS
:: ---------------------------------------------------------------
echo.
echo [5/6] Importando Bases de Datos en MariaDB / MySQL...
set "MYSQL_BIN="
where mysql >nul 2>nul
if !errorlevel! equ 0 (
    set "MYSQL_BIN=mysql"
) else (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
    )
)

if defined MYSQL_BIN (
    if "!IMPORT_DATABASES!"=="1" (
        echo   - ADVERTENCIA: los dumps contienen DROP TABLE.
        echo   - La importacion puede reemplazar datos existentes.
        "%MYSQL_BIN%" -u root -p < "%SECURITY_SQL%"
        if !errorlevel! neq 0 (
            echo   [ERROR] No se pudo importar dirpoles_security.
            exit /b 1
        )
        "%MYSQL_BIN%" -u root -p < "%BUSINESS_SQL%"
        if !errorlevel! neq 0 (
            echo   [ERROR] No se pudo importar dirpoles_business.
            exit /b 1
        )
        echo   - Bases de datos importadas correctamente.
    ) else (
        echo   - La importacion automatica se omite por seguridad.
        echo   - Los dumps contienen DROP TABLE y pueden reemplazar datos.
        echo   - Para importar explicitamente, ejecuta:
        echo       setup_windows.bat --import-databases
    )
) else (
    echo   [INFO] MySQL CLI no detectado. Puedes importar facilmente los archivos:
    echo     - %SECURITY_SQL%
    echo     - %BUSINESS_SQL%
    echo   desde phpMyAdmin: http://localhost/phpmyadmin
)

:: ---------------------------------------------------------------
:: 6. REVISIÓN DE EXTENSIONES EN php.ini
:: ---------------------------------------------------------------
echo.
echo [6/6] Verificacion de php.ini en XAMPP:
echo   ---------------------------------------------------------------------
echo   Asegurate de que las siguientes lineas esten sin punto y coma inicial (;)
echo   en tu archivo C:\xampp\php\php.ini (Panel de XAMPP ^> Config ^> PHP php.ini):
echo.
echo     extension=fileinfo     ^<-- VITAL: XAMPP la trae comentada por defecto
echo     extension=openssl
echo     extension=pdo_mysql
echo     extension=mbstring
echo   ---------------------------------------------------------------------

echo.
echo =====================================================================
echo   CONFIGURACION COMPLETADA
echo =====================================================================
echo   1. Inicia los modulos Apache y MySQL en el Panel de Control de XAMPP.
echo   2. Abre tu navegador web en:
echo      http://localhost/DIRPOLES-4
echo.
echo   Verifica las credenciales iniciales en la documentacion y cambia la contrasena.
if "!IMPORT_DATABASES!"=="0" echo   3. Importa las bases de datos con: setup_windows.bat --import-databases
echo =====================================================================
echo.
pause
