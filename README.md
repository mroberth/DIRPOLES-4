# DIRPOLES-4

### Sistema de Gestión Administrativa para la Dirección de Políticas Estudiantiles
**Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB)**

[![PHP](https://img.shields.io/badge/PHP-8.2%20%2F%208.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-8%20%2F%2010.4%2B-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/Licencia-Institucional%20%2F%20Acad%C3%A9mica-blue)](#-licencia-y-propiedad-intelectual)

**DIRPOLES-4** es el **Sistema de Gestión Administrativa para la Dirección de Políticas Estudiantiles (DIRPOLES)** de la Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB). Desarrollado como un **monolito modular híbrido en PHP 8.2+ / 8.3** con **MVC propio** (sin frameworks comerciales), sirve páginas HTML renderizadas en el servidor (Puerta HTML) y una API RESTful JSON estricta (Puerta JSON) desacoplada para consumo web y preparado para servicios de Inteligencia Artificial (FastAPI).

Este repositorio constituye la versión final refactorizada, segura e inmutable del sistema, abarcando la automatización integral de **18 módulos funcionales, clínicos, logísticos y administrativos**. Es **100% compatible tanto con Windows (XAMPP 8.2.12+) como con Linux (PHP 8.3+)**.

> **Proyecto Socio-Integrador y Tecnológico (PSIT)** — Trabajo Especial de Grado para la obtención del título de **Ingeniero(a) en Informática** (Trayecto IV, PNF Informática, UPTAEB).

---

## 📖 Tabla de Contenido

- [✨ Características Principales](#-características-principales)
- [🧱 Stack Tecnológico](#-stack-tecnológico)
- [🏛 Arquitectura del Sistema](#-arquitectura-del-sistema)
- [📋 Requisitos de Infraestructura](#-requisitos-de-infraestructura)
- [🚀 Instalación y Despliegue](#-instalación-y-despliegue)
  - [Opción A — Script Automatizado para Windows / XAMPP (`setup_windows.bat`)](#opción-a--script-automatizado-para-windows--xampp-setup_windowsbat)
  - [Opción B — Script Automatizado para Linux (`setup_linux.sh`)](#opción-b--script-automatizado-para-linux-setup_linuxsh)
  - [Opción C — Guía Paso a Paso para Windows (XAMPP)](#opción-c--guía-paso-a-paso-para-windows-xampp)
  - [Opción D — Instalación Manual Paso a Paso (Linux)](#opción-d--instalación-manual-paso-a-paso-linux)
- [🔑 Gestión de Credenciales Iniciales](#-gestión-de-credenciales-iniciales)
- [✅ Verificación del Despliegue](#-verificación-del-despliegue)
- [⚙ Variables de Entorno (.env)](#-variables-de-entorno-env)
- [🗂 Estructura del Código Fuente](#-estructura-del-código-fuente)
- [🧩 Módulos del Sistema (18/18 Operativos)](#-módulos-del-sistema-1818-operativos)
- [🔐 Marco de Seguridad Defensiva](#-marco-de-seguridad-defensiva)
- [📚 Documentación Técnica](#-documentación-técnica)
- [🛠 Solución de Problemas Frecuentes](#-solución-de-problemas-frecuentes)
- [📄 Licencia y Propiedad Intelectual](#-licencia-y-propiedad-intelectual)

---

## ✨ Características Principales

- **Criptografía Asimétrica y Autenticación Defensiva:**
  - Cifrado en tránsito asimétrico con **RSA-2048 (PKCS#1)** en cliente → descifrado seguro en servidor → almacenamiento con hash **Bcrypt (cost factor 10)**.
  - **Tokens JWT RS256** en cookie `HttpOnly` + **Refresh Tokens con rotación de un solo uso** (*one-time use*) con digest SHA-256 en base de datos.
  - **Protección Anti-Fuerza Bruta Persistente** en BD (`login_intentos`) con bloqueo automático a los 3 intentos fallidos.
- **Doble Base de Datos Aislada:**
  - `dirpoles_security`: Autenticación, matriz RBAC, auditoría forense (`bitacora`), tokens y notificaciones.
  - `dirpoles_business`: Expedientes clínicos, beneficiarios, inventario médico, jornadas, transporte y becas.
- **Concurrencia e Integridad Transaccional:**
  - Bloqueo pesimista nivel de fila (`SELECT ... FOR UPDATE`) en descuento de insumos médicos y control de aforo masivo en jornadas.
  - Kardex de movimientos transaccionales en tiempo real para inventario médico, mobiliario y flota de transporte.
- **Autorización Atómica RBAC:** Matriz dinámica (Roles × Módulos × Permisos: `crear`, `leer`, `editar`, `eliminar`).
- **Streaming SSE:** Canal bidireccional asíncrono (*Server-Sent Events*) para la campana de notificaciones del topbar sin bloqueo de sesión (`session_write_close()`).
- **Analítica y Reportes:** 10 tableros estadísticos dinámicos con filtrado server-side en whitelist, gráficos Chart.js v2.9.4 local y exportación a PDF y Excel.

---

## 🧱 Stack Tecnológico

| Capa / Componente | Tecnología / Estándar |
|---|---|
| **Lenguaje Backend** | **PHP 8.2+ / 8.3** (Compatible con XAMPP 8.2.12+ en Windows y PHP 8.3+ en Linux; MVC propio modular nativo) |
| **Bases de Datos** | **MySQL 8.0+ / MariaDB 10.4+** (Dos esquemas aislados: `dirpoles_security` y `dirpoles_business`) |
| **Servidor Web** | **Apache 2.4+** con módulo `mod_rewrite` activo |
| **Dependencias PHP** | [Composer 2.x](https://getcomposer.org/) · `firebase/php-jwt` · `vlucas/phpdotenv` |
| **Frontend & UI** | **HTML5 + Vanilla JS Modular + Bootstrap 5** (SB Admin 2) |
| **Librerías Frontend Local** | DataTables, Select2, SweetAlert2, Driver.js, FullCalendar, Chart.js v2.9.4 local |
| **Seguridad / Criptografía** | RSA-2048 (OpenSSL) · Bcrypt · JWT RS256 · SHA-256 |

---

## 🏛 Arquitectura del Sistema

DIRPOLES-4 opera bajo el patrón **Monolito Modular con Front Controller Híbrido (Regla de las Dos Puertas)**:

```
Navegador ──▶ .htaccess ──▶ index.php (Front Controller)
                                   │
                                   ├─ RateLimitMiddleware   (Token Bucket por IP/Endpoint)
                                   ├─ SessionAuthMiddleware (Sesión PHP + Validación JWT RS256)
                                   └─ Router ──▶ Controlador Funcional ──▶ Modelo (SQL PDO)
                                                                 └──▶ Respuesta (JSON / HTML)
```

### Regla de las Puertas y Canales

| Puerta / Canal | Identificador | Tipo de Respuesta | Propósito |
|---|---|---|---|
| **Puerta HTML** | Rutas estándar (ej: `medicina/consultar`) | Renderizado HTML Server-Side (SSR) | Navegación entre vistas y plantillas del sistema. |
| **Puerta JSON** | Rutas bajo `api/` (ej: `api/medicina/listar`) | JSON con Contrato Único (`{exito: bool, datos/error}`) | Operaciones asíncronas AJAX, DataTables y API. |
| **Streaming SSE** | `sse/notificaciones` | `text/event-stream` | Eventos y alertas en tiempo real en la barra superior. |
| **Tercera Puerta** | Endpoints binarios (ej: `respaldo/descargar`) | Stream `application/octet-stream` o PDF | Descarga de respaldos `.sql` e informes FPDF. |

---

## 📋 Requisitos de Infraestructura

| Requisito | Versión Soportada | Extensión / Módulo Requerido |
|---|---|---|
| **Servidor PHP** | **8.2+ o 8.3+** (ej. XAMPP 8.2.12+ en Windows o PHP 8.3+ en Linux) | `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `json` |
| **Motor MySQL / MariaDB** | 8.0+ / 10.4+ | Motor InnoDB con soporte de transacciones ACID y `FOR UPDATE` |
| **Servidor Web** | Apache 2.4+ | `mod_rewrite` e `AllowOverride All` habilitados |
| **Gestor de Paquetes** | Composer 2.x | Para autoloader PSR-4 y dependencias de producción |
| **Herramientas de Criptografía**| PHP CLI / OpenSSL | Para generación de pares de claves RSA-2048 (script multiplataforma incluido) |

> [!IMPORTANT]
> **Compatibilidad Nativa Total (Windows con XAMPP 8.2.12+ y Linux con PHP 8.3+):**  
> El sistema fue concebido bajo estándares de PHP 8.2+ y las dependencias de desarrollo del repositorio fueron ajustadas a **PHPUnit 11** (compatible nativamente con `PHP >= 8.2`).  
> De este modo, la instalación estándar mediante `composer install` (o `composer install --no-dev` para entornos de producción) funciona **directamente y sin advertencias ni bloqueos de versión** tanto en Windows (XAMPP 8.2.12+) como en Linux (PHP 8.3+).

---

## 🚀 Instalación y Despliegue

### Opción A — Script Automatizado para Windows / XAMPP (`setup_windows.bat`)

Si utilizas **Windows con XAMPP** (incluso con **PHP 8.2.12**), el repositorio cuenta con el script automatizado `setup_windows.bat`:

1. Abre tu terminal (CMD, PowerShell o Git Bash) y clona el repositorio dentro de la carpeta `htdocs` de XAMPP:
   ```cmd
   cd C:\xampp\htdocs
   git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
   cd DIRPOLES-4
   ```
2. Ejecuta el archivo por lotes (o haz doble clic sobre él en el Explorador de Archivos):
   ```cmd
   setup_windows.bat
   ```
   El script se encargará automáticamente de:
   - Crear el archivo `.env` configurado para XAMPP (`root` sin contraseña).
   - Crear los directorios `logs/`, `uploads/` y `app/Config/Keys/`.
   - Generar las 4 llaves RSA-2048 (`login` y `jwt`) mediante PHP CLI o OpenSSL.
   - Instalar las dependencias de producción compatibles con PHP 8.2 (`composer install --no-dev`).
   - Importar las bases de datos `dirpoles_security` y `dirpoles_business` en MySQL.

3. Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP.
4. Accede en tu navegador a: **`http://localhost/DIRPOLES-4`**

---

### Opción B — Script Automatizado para Linux (`setup_linux.sh`)

Para distribuciones Linux (Debian, Ubuntu, Linux Mint) con Apache y PHP 8.3:

```bash
git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
cd DIRPOLES-4
chmod +x setup_linux.sh
./setup_linux.sh
```

El script ejecuta automáticamente:
1. Creación del archivo de configuración Apache Alias (`/DIRPOLES-4`).
2. Generación del archivo `.env` a partir de `.env.example`.
3. Generación criptográfica de las llaves RSA de 2048 bits para Login y JWT en `app/Config/Keys/` con permisos `600`.
4. Creación de directorios de almacenamiento (`logs/`, `uploads/`) con permisos requeridos.
5. Instalación de dependencias de PHP vía Composer.
6. Importación automática de los dos esquemas SQL desde `docs/bd/`.
7. Reinicio del servicio Apache.

Acceso inmediato en: **`http://localhost/DIRPOLES-4`**

---

### Opción C — Guía Paso a Paso para Windows (XAMPP)

Si prefieres realizar la instalación manualmente en Windows con XAMPP:

#### 1. Ubicación y Clonación
Clona el repositorio dentro del directorio raíz web de XAMPP (`htdocs`):
```cmd
cd C:\xampp\htdocs
git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
cd DIRPOLES-4
```

#### 2. Habilitar Extensiones en `php.ini` (XAMPP)
Abre el archivo de configuración `C:\xampp\php\php.ini` (o desde el Panel de XAMPP haz clic en el botón **Config** junto a Apache y selecciona **PHP (php.ini)**).

Verifica y descomenta (elimina el punto y coma `;` inicial) las siguientes extensiones:
```ini
extension=fileinfo     ; <-- VITAL: viene desactivada por defecto en XAMPP y es requerida para validar uploads/fotos
extension=openssl
extension=pdo_mysql
extension=mbstring
```
Guarda los cambios y reinicia Apache en XAMPP.

#### 3. Configurar Variables de Entorno (`.env`)
Copia la plantilla de entorno:
```cmd
copy .env.example .env
```
En XAMPP, el usuario por defecto de MySQL es `root` sin contraseña. Tu `.env` debe quedar con:
```env
DB_HOST=localhost
DB_NAME=dirpoles_business
DB_USER=root
DB_PASS=
DB_SECURITY_NAME=dirpoles_security
DB_SECURITY_USER=root
DB_SECURITY_PASS=
```

#### 4. Generar Llaves Criptográficas RSA (Multiplataforma)
DIRPOLES-4 incluye un generador PHP nativo que no requiere instalar herramientas externas:
```cmd
php scripts/generar_llaves.php
```
*(Alternativamente, si usas Git Bash, puedes generarlas con OpenSSL CLI: `openssl genrsa -out app/Config/Keys/login_private.pem 2048`, etc.)*.

#### 5. Instalar Dependencias con Composer (PHP 8.2 / 8.3)
Gracias a que las dependencias de desarrollo están fijadas en **PHPUnit 11** (compatible con `PHP >= 8.2`), puedes ejecutar la instalación estándar directamente:
```cmd
composer install
```
> [!TIP]
> Si vas a desplegar en un servidor de producción o deseas una instalación más ligera y rápida omitiendo librerías de pruebas, puedes usar `composer install --no-dev`. Ambas variantes funcionarán perfectamente en PHP 8.2 y 8.3.

#### 6. Importar Bases de Datos
Puedes importarlas de dos formas:
- **Vía phpMyAdmin:** Entra en `http://localhost/phpmyadmin`, crea o importa directamente los scripts:
  1. `docs\bd\dirpoles_security.sql`
  2. `docs\bd\dirpoles_business.sql`
- **Vía Línea de Comandos (CMD):**
  ```cmd
  C:\xampp\mysql\bin\mysql.exe -u root < docs\bd\dirpoles_security.sql
  C:\xampp\mysql\bin\mysql.exe -u root < docs\bd\dirpoles_business.sql
  ```

#### 7. Carpetas de Almacenamiento
Asegúrate de que existan las carpetas para logs y subida de archivos:
```cmd
if not exist logs mkdir logs
if not exist uploads mkdir uploads
```

Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP y visita:  
**`http://localhost/DIRPOLES-4`**

---

### Opción D — Instalación Manual Paso a Paso (Linux)

#### 1. Clonar el repositorio
```bash
git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
cd DIRPOLES-4
```

#### 2. Configurar variables de entorno
```bash
cp .env.example .env
```
Ajusta credenciales de base de datos en `.env`.

#### 3. Instalar dependencias PHP
```bash
composer install --no-dev
```

#### 4. Generar Llaves Criptográficas RSA
```bash
php scripts/generar_llaves.php
```
*(O usando OpenSSL CLI)*:
```bash
mkdir -p app/Config/Keys
openssl genrsa -out app/Config/Keys/login_private.pem 2048
openssl rsa -in app/Config/Keys/login_private.pem -pubout -out app/Config/Keys/login_public.pem
openssl genrsa -out app/Config/Keys/jwt_private.pem 2048
openssl rsa -in app/Config/Keys/jwt_private.pem -pubout -out app/Config/Keys/jwt_public.pem
chmod 600 app/Config/Keys/*_private.pem
chmod 644 app/Config/Keys/*_public.pem
```

#### 5. Importar Bases de Datos
```bash
mysql -u root -p < docs/bd/dirpoles_security.sql
mysql -u root -p < docs/bd/dirpoles_business.sql
```

#### 6. Permisos de Directorios
```bash
mkdir -p logs uploads
chmod 777 logs uploads
```

---

## 🔑 Gestión de Credenciales Iniciales

El esquema inicial incluye usuarios administrativos pre-configurados. Las contraseñas están resguardadas con hash **Bcrypt**. Para establecer una contraseña conocida para pruebas o administración:

```bash
# 1) Genera el hash Bcrypt de tu nueva contraseña desde la terminal:
php -r "echo password_hash('TuClaveSegura123!', PASSWORD_BCRYPT), PHP_EOL;"
```

Actualiza el hash en la base de datos de seguridad:

```sql
UPDATE dirpoles_security.empleado 
   SET clave = 'HASH_GENERADO_AQUI', estatus = 1 
 WHERE correo = 'admin@gmail.com';
```

---

## ✅ Verificación del Despliegue

1. Abre `http://localhost/DIRPOLES-4/login` en el navegador.
2. Inicia sesión con las credenciales configuradas.
3. Deberías visualizar el **Panel de Inicio** con las tarjetas estadísticas del módulo y el calendario operativo personal.

---

## ⚙ Variables de Entorno (.env)

| Variable | Descripción | Ejemplo |
|---|---|---|
| `APP_DEBUG` | Muestra detalles de depuración (`true` desarrollo) | `false` |
| `DB_HOST` | Host del motor de base de datos MySQL | `localhost` |
| `DB_NAME` | Nombre del esquema de **negocio** | `dirpoles_business` |
| `DB_USER` / `DB_PASS` | Credenciales de la BD de negocio | `root` / *(vacío)* |
| `DB_SECURITY_NAME` | Nombre del esquema de **seguridad** | `dirpoles_security` |
| `DB_SECURITY_USER` / `DB_SECURITY_PASS` | Credenciales de la BD de seguridad | `root` / *(vacío)* |
| `JWT_EXPIRATION` | Expiración del token JWT en segundos | `3600` (1 h) |
| `REFRESH_EXPIRATION` | Expiración del Refresh Token en segundos | `1296000` (15 días) |
| `APP_ENV` | Entorno de ejecución | `local` \| `staging` \| `production` |
| `CORS_ALLOWED_ORIGINS` | Dominio(s) permitidos en CORS | `http://localhost:5173` |

---

## 🗂 Estructura del Código Fuente

```
DIRPOLES-4/
├── index.php                  Front Controller (CORS → sesión → handler → Router)
├── .htaccess                  Redirección centralizada a index.php
├── .env / .env.example        Configuración de entorno
├── composer.json              Autoload PSR-4 + dependencias
├── setup_linux.sh             Script de instalación automatizado (Linux)
│
├── app/
│   ├── Core/                  Infraestructura: Router, Database, Respuesta,
│   │                          ExcepcionApi, ErrorCodes, Autorizacion,
│   │                          Bitacora, Notificador, JwtHandler
│   ├── Middlewares/           RateLimitMiddleware, SessionAuthMiddleware
│   ├── Controllers/           Controladores funcionales (rutas web y APIs)
│   ├── Models/                Modelos de negocio y seguridad (con manejarAccion)
│   ├── Views/                 Plantillas HTML renderizadas en servidor (SSR)
│   ├── routes/                Unidades de enrutamiento por módulo
│   ├── Config/                Catálogos, tarjetas dashboard, permisos RBAC
│   │                          Keys/ (Llaves RSA no versionadas)
│   ├── bootstrap.php          Inicializador de la aplicación
│   └── routes.php             Rutas principales y middlewares globales
│
├── dist/
│   ├── css/                   Estilos CSS Vanilla y temas custom
│   └── js/
│       ├── core/              apiFetch, AlertManager, logout, select2-init...
│       └── modulos/           Lógica cliente por módulo (tours, stats, CRUD)
│
├── plugins/                   Librerías frontend auto-hospedadas (Bootstrap,
│                              DataTables, Select2, SweetAlert2, FullCalendar...)
│
├── docs/
│   ├── MANUAL_TECNICO_DIRPOLES4.md Manual Técnico Maestro (Capítulos 1 al 6)
│   ├── bd/                    Esquemas SQL completos (security y business)
│   └── auditoria/             Informes de auditoría de seguridad
│
├── logs/                      Registros de errores php_errors.log (no versionado)
└── vendor/                    Dependencias Composer (no versionado)
```

---

## 🧩 Módulos del Sistema (18/18 Operativos)

| Módulo | ID | Estado | Descripción Funcional |
|---|:---:|:---:|---|
| **Gestionar Empleados** | 1 | ✅ | Administra personal médico, docente, técnico y administrativo, roles y accesos. |
| **Gestionar Beneficiarios** | 2 | ✅ | Expedientes de estudiantes de la UPTAEB por PNF, trayecto, sección y contacto. |
| **Gestionar Citas** | 3 | ✅ | Agenda de Psicología y atención clínica contra horarios de disponibilidad. |
| **Diagnósticos de Psicología** | 4 | ✅ | Evaluaciones clínicas, seguimiento psicológico y constancias en PDF. |
| **Diagnósticos de Medicina** | 5 | ✅ | Consultas médicas primarias y descuento automático de medicamentos con `FOR UPDATE`. |
| **Diagnósticos de Orientación** | 6 | ✅ | Asistencia socioeducativa, vocacional, motivos y recomendaciones institucionales. |
| **Diagnósticos de Trabajo Social**| 7 | ✅ | Hub de 4 sub-flujos (becas, exoneraciones, FAMES, embarazadas) + Estudio Socioeconómico FPDF. |
| **Diagnósticos de Discapacidad** | 8 | ✅ | Expediente de atención a personas con diversidad funcional y habilidades. |
| **Gestionar Inventario Médico** | 9 | ✅ | Kardex transaccional de medicamentos, entradas, salidas justificadas y vencimiento. |
| **Gestionar Referencias** | 10 | ✅ | Remisión interdepartamental entre áreas de salud con historial de transiciones. |
| **Gestionar Jornadas Médicas** | 11 | ✅ | Eventos masivos de salud, control de aforo pesimista `FOR UPDATE` y consumo de insumos. |
| **Gestionar Mobiliario y Equipos**| 12 | ✅ | Hub de 3 pestañas (bienes muebles, equipos con serial único y Fichas Técnicas responsables). |
| **Gestionar Transporte** | 13 | ✅ | Control de flota vehicular, rutas universitarias, choferes (`id_tipo_emp=8`) y repuestos. |
| **Configuraciones del Sistema** | 14 | ✅ | Administración centralizada de catálogos y parámetros globales. |
| **Reportes Estadísticos** | 15 | ✅ | 10 tableros estadísticos con filtrado server-side, Chart.js local v2.9.4 y consumo IA. |
| **Auditoría / Bitácora** | 16 | ✅ | Registro inmutable de trazabilidad forense por IP, usuario y catálogo de acciones. |
| **Matriz de Permisos (RBAC)** | 17 | ✅ | Matriz dinámica de permisos atómicos (`crear`, `leer`, `editar`, `eliminar`) por rol. |
| **Gestionar Horarios** | 18 | ✅ | Administración exclusiva de turnos y agendas por especialista de salud. |

---

## 🔐 Marco de Seguridad Defensiva

DIRPOLES-4 cumple estrictamente con el estándar **OWASP ASVS v4.0.3**:

- **Cifrado Híbrido en Tránsito y Reposo:** RSA-2048 en navegador + Bcrypt en servidor.
- **Inmunidad a Inyecciones SQL:** 100% de consultas preparadas PDO con tipado explícito (`PARAM_INT`, `PARAM_STR`).
- **Inmunidad a XSS:** Sanitización fail-fast en modelo (`__set()`) + escape contextual en vistas (`htmlspecialchars()`).
- **Seguridad en Cookies y JWT:** Firma RS256, cookies `HttpOnly`, `SameSite=Lax` y rotación de Refresh Token.
- **Protección contra BOLA / IDOR:** Métodos de autorización a nivel de fila (`asegurarAlcance()`) en controladores y modelos.

---

## 📚 Documentación Técnica

Toda la arquitectura, especificaciones de base de datos, lógica de los 18 módulos y argumentos para la defensa de grado se encuentran detallados en:

* **[Manual Técnico Maestro de DIRPOLES-4](docs/MANUAL_TECNICO_DIRPOLES4.md):** Documento oficial consolidado (Capítulos 1 al 6, Glosario y Estándares ISO/OWASP).
* **[Guía de Construcción de Módulos](GUIA-MODULOS.md):** Guía paso a paso para la incorporación de nuevas funcionalidades.
* **[Reglas de Desarrollo para IA / Agentes](AGENTS.md):** Estándares y convenciones de código del repositorio.

---

## 🛠 Solución de Problemas Frecuentes

### 1. Error de compatibilidad en Composer con versiones de PHP
- **Estado en este repositorio:** El proyecto cuenta con **PHPUnit 11.5**, el cual es 100% compatible con **PHP >= 8.2** (tanto en Windows con XAMPP 8.2.12 como en Linux con PHP 8.3). Un simple `composer install` resolverá todas las dependencias sin conflictos.
- **Instalación ligera / Producción:** Si estás en un servidor donde solo requieres la aplicación en ejecución (sin pruebas unitarias), puedes omitir las dependencias dev:
  ```cmd
  composer install --no-dev
  ```

### 2. Error al subir comprobantes o fotos: `Call to undefined function mime_content_type()`
- **Causa:** En instalaciones estándar de XAMPP para Windows, la extensión nativa `fileinfo` viene deshabilitada por defecto.
- **Solución:**
  1. Abre el archivo `C:\xampp\php\php.ini` (o presiona el botón **Config** junto a Apache en el Panel de XAMPP y selecciona `PHP (php.ini)`).
  2. Busca la línea `;extension=fileinfo` (usa `Ctrl + B` o `Ctrl + F`).
  3. Elimina el punto y coma (`;`) al inicio para que quede:
     ```ini
     extension=fileinfo
     ```
  4. Guarda el archivo y reinicia el servicio Apache en XAMPP.

### 3. Error en Login: `Llave privada de login no encontrada`
- **Causa:** Por seguridad, las llaves criptográficas RSA (`*.pem`) no se incluyen en el repositorio Git y deben generarse en cada entorno.
- **Solución:** Ejecuta el generador multiplataforma de llaves incluido en el sistema:
  ```cmd
  php scripts/generar_llaves.php
  ```
  Esto generará de inmediato los 4 archivos `.pem` necesarios en la carpeta `app/Config/Keys/`.

### 4. Rutas dan error 404 (Not Found) al navegar
- **Causa:** El módulo `mod_rewrite` de Apache no está procesando el archivo `.htaccess`.
- **Solución:**
  1. Verifica que el archivo `.htaccess` esté presente en la raíz del proyecto (`C:\xampp\htdocs\DIRPOLES-4\.htaccess`).
  2. En `C:\xampp\apache\conf\httpd.conf`, comprueba que la línea:
     ```apache
     LoadModule rewrite_module modules/mod_rewrite.so
     ```
     no tenga `#` al inicio.
  3. Asegúrate de que el bloque `<Directory "C:/xampp/htdocs">` tenga `AllowOverride All`.
  4. Reinicia Apache.

### 5. Error de conexión: `Access denied for user 'root'@'localhost'`
- **Causa:** Las credenciales configuradas en `.env` no coinciden con las de tu gestor de base de datos.
- **Solución:** En una instalación fresca de XAMPP, el usuario es `root` y la contraseña está completamente vacía. Ajusta tu `.env`:
  ```env
  DB_USER=root
  DB_PASS=
  DB_SECURITY_USER=root
  DB_SECURITY_PASS=
  ```

---

## 📄 Licencia y Propiedad Intelectual

**Licencia Institucional / Académica**

El sistema **DIRPOLES-4** es un desarrollo tecnológico y académico original diseñado para la **Dirección de Políticas Estudiantiles (DIRPOLES)** de la **Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB)**, Barquisimeto, Estado Lara, Venezuela.

Desarrollado como **Trabajo Especial de Grado / Proyecto Socio-Integrador y Tecnológico (PSIT)** para la obtención del título de **Ingeniero(a) en Informática** en el Programa Nacional de Formación (PNF) en Informática (Trayecto IV).

* **Institución Beneficiaria:** Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB).
* **Todos los derechos reservados © UPTAEB — DIRPOLES.**
* **Uso exclusivo institucional y académico.** Queda prohibida la comercialización o distribución sin la debida autorización de las autoridades institucionales y autores del proyecto.

---

<div align="center">

**DIRPOLES-4** · Dirección de Políticas Estudiantiles · UPTAEB  
Barquisimeto, Venezuela  

</div>
