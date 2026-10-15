@echo off
setlocal EnableDelayedExpansion

title DIRPOLES-4 - Instalador y Configurador para Windows (XAMPP)

echo =====================================================================
echo          DIRPOLES-4 - Configuracion Automatica para Windows (XAMPP)
echo =====================================================================
echo.

set "SCRIPT_DIR=%~dp0"
cd /d "%SCRIPT_DIR%"

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

where php >nul 2>nul
if %errorlevel% equ 0 (
    php "scripts\generar_llaves.php"
) else (
    set "OPENSSL_BIN="
    where openssl >nul 2>nul
    if !errorlevel! equ 0 (
        set "OPENSSL_BIN=openssl"
    ) else (
        if exist "C:\xampp\apache\bin\openssl.exe" (
            set "OPENSSL_BIN=C:\xampp\apache\bin\openssl.exe"
        )
    )
    if defined OPENSSL_BIN (
        if not exist "app\Config\Keys\login_private.pem" (
            "!OPENSSL_BIN!" genrsa -out "app\Config\Keys\login_private.pem" 2048 2>nul
            "!OPENSSL_BIN!" rsa -in "app\Config\Keys\login_private.pem" -pubout -out "app\Config\Keys\login_public.pem" 2>nul
            echo   - Llaves RSA de Login generadas con OpenSSL.
        ) else (
            echo   - Llaves de Login ya existen.
        )
        if not exist "app\Config\Keys\jwt_private.pem" (
            "!OPENSSL_BIN!" genrsa -out "app\Config\Keys\jwt_private.pem" 2048 2>nul
            "!OPENSSL_BIN!" rsa -in "app\Config\Keys\jwt_private.pem" -pubout -out "app\Config\Keys\jwt_public.pem" 2>nul
            echo   - Llaves RSA de JWT (RS256) generadas con OpenSSL.
        ) else (
            echo   - Llaves de JWT ya existen.
        )
    ) else (
        echo   [AVISO] No se encontro PHP ni OpenSSL en PATH.
        echo   Ejecuta: php scripts\generar_llaves.php una vez configures PHP en tu PATH.
    )
)

:: ---------------------------------------------------------------
:: 4. DEPENDENCIAS COMPOSER (PHP 8.2 y 8.3)
:: ---------------------------------------------------------------
echo.
echo [4/6] Instalando dependencias con Composer...
where composer >nul 2>nul
if %errorlevel% equ 0 (
    echo   - Ejecutando: composer install --no-interaction
    composer install --no-interaction
    if !errorlevel! equ 0 (
        echo   - Dependencias instaladas correctamente.
    ) else (
        echo   - Reintentando instalacion ligera: composer install --no-dev --no-interaction
        composer install --no-dev --no-interaction
    )
) else (
    echo   [AVISO] Composer CLI no detectado en el PATH de Windows.
    echo   Si ya instalaste Composer, abre una terminal en esta carpeta y corre:
    echo   composer install
)

:: ---------------------------------------------------------------
:: 5. IMPORTACION DE BASES DE DATOS
:: ---------------------------------------------------------------
echo.
echo [5/6] Importando Bases de Datos en MariaDB / MySQL...
set "MYSQL_BIN="
where mysql >nul 2>nul
if %errorlevel% equ 0 (
    set "MYSQL_BIN=mysql"
) else (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
    )
)

if defined MYSQL_BIN (
    echo   - Importando esquemas dirpoles_security y dirpoles_business...
    "%MYSQL_BIN%" -u root < "docs\bd\dirpoles_security.sql" 2>nul
    if %errorlevel% equ 0 (
        echo   - Base de datos dirpoles_security importada.
    ) else (
        echo   - [INFO] Si dirpoles_security ya existe o tiene contrasena, importala desde phpMyAdmin.
    )
    "%MYSQL_BIN%" -u root < "docs\bd\dirpoles_business.sql" 2>nul
    if %errorlevel% equ 0 (
        echo   - Base de datos dirpoles_business importada.
    ) else (
        echo   - [INFO] Si dirpoles_business ya existe o tiene contrasena, importala desde phpMyAdmin.
    )
) else (
    echo   [INFO] MySQL CLI no detectado. Puedes importar facilmente los archivos:
    echo     - docs\bd\dirpoles_security.sql
    echo     - docs\bd\dirpoles_business.sql
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
echo   INSTALACION COMPLETADA
echo =====================================================================
echo   1. Inicia los modulos Apache y MySQL en el Panel de Control de XAMPP.
echo   2. Abre tu navegador web en:
echo      http://localhost/DIRPOLES-4
echo.
echo   Credenciales maestras: admin@gmail.com
echo =====================================================================
echo.
pause
