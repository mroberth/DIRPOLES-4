# DIRPOLES-4

### Sistema de Gestión de la Dirección de Políticas Estudiantiles — UPTAEB

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-8%20%2F%2010.4%2B-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/Licencia-Académica-lightgrey)](#-licencia)

**DIRPOLES-4** es el sistema de gestión Administrativa para la dirección de políticas estudiantiles de la Universidad Politécnica Territorial del Estado Lara Andrés Eloy Blanco (UPTAEB)**. Es un **monolito híbrido en PHP 8** con
**MVC propio** (sin framework) que sirve **páginas HTML renderizadas en el
servidor** y una **API JSON** con contrato único.

Este repositorio es una **reconstrucción limpia** del sistema anterior
(`DIRPOLES_4`): conserva su funcionalidad pero corrige sus inconsistencias de
arquitectura, seguridad y manejo de errores. Se construye **módulo a módulo**
siguiendo [`GUIA-MODULOS.md`](GUIA-MODULOS.md).

> Proyecto final — 4to trayecto, PNF Informática.

---

## 📖 Tabla de contenido

- [✨ Características](#-características)
- [🧱 Stack tecnológico](#-stack-tecnológico)
- [🏛 Arquitectura](#-arquitectura)
- [📋 Requisitos](#-requisitos)
- [🚀 Instalación](#-instalación)
  - [Opción A — Script automático (Linux)](#opción-a--script-automático-linux)
  - [Opción B — Manual paso a paso](#opción-b--manual-paso-a-paso)
- [🔑 Crear/restablecer un usuario](#-crearrestablecer-un-usuario)
- [✅ Verificar la instalación](#-verificar-la-instalación)
- [⚙ Variables de entorno](#-variables-de-entorno)
- [🗂 Estructura del proyecto](#-estructura-del-proyecto)
- [🧩 Módulos](#-módulos)
- [🔐 Seguridad](#-seguridad)
- [📚 Documentación](#-documentación)
- [🛠 Solución de problemas](#-solución-de-problemas)
- [📄 Licencia](#-licencia)

---

## ✨ Características

- **Autenticación completa y endurecida**
  - Contraseña cifrada con **RSA (PKCS#1)** en el navegador → descifrada en el
    servidor → almacenada con **bcrypt**.
  - **JWT RS256** en cookie `HttpOnly` + **refresh token con rotación**
    (un solo uso).
  - **Bloqueo por intentos fallidos** persistido en base de datos.
  - `session_regenerate_id` (anti *session fixation*).
- **Autorización RBAC** (roles × módulos × permisos: Crear/Leer/Editar/Eliminar).
- **Contrato JSON único** y manejo de errores centralizado.
- **Rate limiting** por *Token Bucket* (IP × endpoint).
- **Auditoría** (bitácora) y **notificaciones** en tiempo real (SSE) con campana
  en el topbar.
- **Panel de Inicio** compuesto por rol/permisos (cards de módulos, estadísticas
  y calendario personal).
- **Módulos de gestión** con CRUD, búsqueda, exportación a Excel/PDF y tours
  guiados (Driver.js).
- **Respaldo de base de datos** descargable en `.sql`.

---

## 🧱 Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | **PHP 8.1+** (MVC propio, sin framework) |
| Base de datos | **MySQL / MariaDB** (dos esquemas) |
| Servidor web | **Apache 2.4+** con `mod_rewrite` |
| Dependencias PHP | [Composer](https://getcomposer.org/) · `firebase/php-jwt` · `vlucas/phpdotenv` |
| Frontend | **HTML5 + Bootstrap 5** (tema SB Admin 2) + **jQuery** |
| Librerías front | DataTables, Select2, SweetAlert2, Driver.js, FullCalendar, jsPDF, JSEncrypt |
| Autenticación | RSA (OpenSSL) · bcrypt · JWT RS256 |

---

## 🏛 Arquitectura

**Monolito híbrido con *Front Controller***: todas las peticiones entran por
`index.php`, que aplica CORS, sesión, middlewares y el manejo global de errores.

```
Navegador ──▶ .htaccess ──▶ index.php ──▶ Router
                                   │
                                   ├─ RateLimitMiddleware   (Token Bucket)
                                   ├─ SessionAuthMiddleware (sesión + JWT)
                                   └─ Ruta ──▶ Controlador ──▶ Modelo (SQL)
                                                        └──▶ Respuesta (JSON/HTML)
```

### La regla de las **dos puertas**

| Puerta | Ruta | Responde | Ejemplo |
|---|---|---|---|
| **HTML** | fuera de `api/` | Página renderizada o redirección | `empleados/consultar` |
| **JSON** | bajo `api/` (o `Accept: application/json`) | JSON del contrato | `api/empleados/listar` |
| *SSE* | `sse/notificaciones` | `text/event-stream` | campana en vivo |
| *Archivo* | `respaldo/descargar` | `.sql` | respaldo de BD |

**Contrato JSON único:**

```jsonc
// Éxito
{ "exito": true, "datos": { "...": "..." } }

// Error
{ "exito": false, "error": { "codigo": "ALREADY_EXISTS", "estado": 409, "mensaje": "..." } }
```

El backend **decide el formato** según la puerta; un endpoint `api/*` nunca
devuelve HTML y una página nunca devuelve JSON.

---

## 📋 Requisitos

| Componente | Versión mínima | Notas |
|---|---|---|
| **PHP** | 8.1+ | extensiones: `openssl`, `pdo_mysql`, `mbstring` |
| **MySQL / MariaDB** | 8.0 / 10.4+ | dos bases: `dirpoles_security` y `dirpoles_business` |
| **Apache** | 2.4+ | con `mod_rewrite` habilitado |
| **Composer** | 2.x | gestor de dependencias PHP |
| **OpenSSL** | CLI | para generar las llaves RSA |

Verifica con:

```bash
php -v
php -m | grep -E "pdo_mysql|openssl|mbstring"
composer --version
apache2 -v
```

---

## 🚀 Instalación

### Opción A — Script automático (Linux)

El repositorio incluye `setup_linux.sh`, que hace casi todo:

```bash
git clone <url-del-repo> DIRPOLES-4
cd DIRPOLES-4
chmod +x setup_linux.sh
./setup_linux.sh
```

El script:

1. Configura un **Alias de Apache** (`/DIRPOLES-4`).
2. Crea el `.env` desde `.env.example`.
3. **Genera las llaves RSA** (login + JWT, privadas con permisos `600`).
4. Crea `logs/` y ajusta permisos.
5. Instala dependencias con **Composer**.
6. **Importa las bases de datos** desde `docs/bd/`.
7. Reinicia Apache.

Al terminar, abre `http://localhost/DIRPOLES-4`.

> Requiere permisos de `sudo` para Apache.

---

### Opción B — Manual paso a paso

#### 1. Clonar el repositorio

```bash
git clone <url-del-repo> DIRPOLES-4
cd DIRPOLES-4
```

> El nombre de la carpeta puede ser cualquiera: la URL base se detecta sola.

#### 2. Variables de entorno

```bash
cp .env.example .env
```

Edita `.env` con los datos de tu servidor MySQL (ver
[tabla de variables](#-variables-de-entorno)).

#### 3. Dependencias PHP

```bash
composer install
```

#### 4. Llaves RSA

```bash
mkdir -p app/Config/Keys

# Llaves para el LOGIN (cifrado de la contraseña)
openssl genrsa -out app/Config/Keys/login_private.pem 2048
openssl rsa -in app/Config/Keys/login_private.pem -pubout -out app/Config/Keys/login_public.pem

# Llaves para el JWT (firma y verificación de tokens)
openssl genrsa -out app/Config/Keys/jwt_private.pem 2048
openssl rsa -in app/Config/Keys/jwt_private.pem -pubout -out app/Config/Keys/jwt_public.pem

# La privada solo la lee el servidor
chmod 600 app/Config/Keys/*_private.pem
chmod 644 app/Config/Keys/*_public.pem
```

> ⚠️ Las llaves **nunca** se suben al repositorio (están en `.gitignore`).

#### 5. Base de datos

Los dumps ya crean las bases y sus tablas:

```bash
mysql -u root -p < docs/bd/dirpoles_security.sql
mysql -u root -p < docs/bd/dirpoles_business.sql
```

> **¿Ya tenías las bases creadas?** Aplica solo los scripts incrementales
> (son **idempotentes**, se pueden repetir sin romper nada):
>
> ```bash
> mysql -u root -p dirpoles_security < docs/bd/login_intentos.sql
> mysql -u root -p dirpoles_security < docs/bd/notificaciones_modulo.sql
> mysql -u root -p dirpoles_security < docs/bd/bitacora_respaldo.sql
> ```

#### 6. Permisos

```bash
mkdir -p logs
chmod 777 logs
chmod 644 .env
# El servidor web (www-data) debe poder escribir archivos subidos:
mkdir -p uploads
chmod -R 777 uploads
```

#### 7. Servidor web (Apache)

El proyecto trae un `.htaccess` que redirige todo a `index.php`. Necesitas un
*Alias* o *VirtualHost*. Opción rápida (Debian/Ubuntu):

```bash
sudo tee /etc/apache2/conf-available/dirpoles.conf > /dev/null <<'EOF'
Alias /DIRPOLES-4 "/ruta/absoluta/DIRPOLES-4"
<Directory "/ruta/absoluta/DIRPOLES-4">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF

sudo a2enconf dirpoles
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🔑 Crear/restablecer un usuario

Los dumps incluyen usuarios de ejemplo (p. ej. `admin@gmail.com`). Sus
contraseñas están **hasheadas con bcrypt**, así que no se pueden "leer". Para
dejar una cuenta operativa con una contraseña conocida:

```bash
# 1) Genera el hash bcrypt de tu nueva contraseña
php -r "echo password_hash('TuClave123!', PASSWORD_BCRYPT), PHP_EOL;"
```

Copia el hash y actualízalo:

```bash
mysql -u root dirpoles_security -e \
"UPDATE empleado SET clave='PEGA_AQUI_EL_HASH', estatus=1 WHERE correo='admin@gmail.com';"
```

> La contraseña del **login** debe tener **al menos 8 caracteres** e incluir
> letra, número y un carácter especial (así lo pide la validación del sistema).

---

## ✅ Verificar la instalación

1. Abre `http://localhost/DIRPOLES-4/login`.
2. Inicia sesión con el usuario que configuraste.
3. Deberías llegar al **Panel de Inicio** con tus tarjetas y el calendario.

Comprobaciones rápidas:

```bash
# ¿Conecta la base de seguridad?
mysql -u root dirpoles_security -e "SELECT COUNT(*) FROM empleado;"

# ¿Hay errores de PHP?
tail -n 30 logs/php_errors.log
```

---

## ⚙ Variables de entorno

| Variable | Descripción | Ejemplo |
|---|---|---|
| `APP_DEBUG` | `true` muestra detalles de error (solo desarrollo) | `false` |
| `DB_HOST` | Host de ambas bases de datos | `localhost` |
| `DB_NAME` | Base de datos de **negocio** | `dirpoles_business` |
| `DB_USER` / `DB_PASS` | Usuario/contraseña de negocio | `root` / *(vacío)* |
| `DB_SECURITY_NAME` | Base de datos de **seguridad** | `dirpoles_security` |
| `DB_SECURITY_USER` / `DB_SECURITY_PASS` | Usuario/contraseña de seguridad | `root` / *(vacío)* |
| `JWT_EXPIRATION` | Vida del JWT en segundos | `3600` (1 h) |
| `REFRESH_EXPIRATION` | Vida del refresh token en segundos | `2592000` (30 días) |
| `APP_ENV` | Entorno | `local` \| `staging` \| `production` |
| `CORS_ALLOWED_ORIGINS` | Orígenes permitidos (coma) | `http://localhost:5173` |

---

## 🗂 Estructura del proyecto

```
DIRPOLES-4/
├── index.php                  Front Controller (CORS → sesión → handler → Router)
├── .htaccess                  Redirige todo a index.php
├── .env / .env.example        Configuración por entorno
├── composer.json              Autoload PSR-4 + dependencias
├── setup_linux.sh             Instalación automática
│
├── app/
│   ├── Core/                  Infraestructura: Router, Database, Respuesta,
│   │                          ExcepcionApi, ErrorCodes, Autorizacion,
│   │                          Bitacora, Notificador, JwtHandler
│   ├── Middlewares/           RateLimitMiddleware, SessionAuthMiddleware
│   ├── Controllers/           SOLO funciones (login, dashboard, empleados,
│   │                          beneficiarios, permisos, configuración,
│   │                          bitácora, respaldo, notificaciones, SSE)
│   ├── Models/                Clases con manejarAccion() (negocio/seguridad)
│   ├── Views/                 Plantillas HTML (template/, inicio/, errors/, …)
│   ├── routes/                Un archivo por módulo (autocarga)
│   ├── Config/                modulos_sidebar.php, dashboard_cards.php,
│   │                          configuracion_catalogos.php, roles_sistema.php,
│   │                          Keys/ (RSA, no versionadas)
│   ├── bootstrap.php          Carga de controladores + logs
│   └── routes.php             Rutas esenciales + middlewares globales
│
├── dist/
│   ├── css/                   Estilos propios
│   └── js/
│       ├── core/              apiFetch, AlertManager, logout, select-2-init…
│       └── modulos/           JS por módulo (validaciones, tour, stats, crear…)
│
├── plugins/                   Librerías front auto-hospedadas (Bootstrap,
│                              DataTables, Select2, SweetAlert2, FullCalendar…)
│
├── docs/
│   ├── bd/                    Esquemas SQL + scripts incrementales
│   └── …                      Manuales y guías
│
├── logs/                      php_errors.log
└── vendor/                    Dependencias de Composer (no versionado)
```

---

## 🧩 Módulos

| Módulo | Estado | Descripción |
|---|---|---|
| Autenticación | ✅ | Login RSA+bcrypt, JWT RS256, refresh con rotación, logout |
| Panel de Inicio | ✅ | Shell por rol, cards de módulos, stats, calendario |
| Notificaciones | ✅ | Bandeja + campana en tiempo real (SSE) |
| Empleados | ✅ | Crear, consultar, editar (modal), eliminar, stats |
| Beneficiarios | ✅ | Crear, consultar, editar (modal), eliminar, stats |
| Permisos | ✅ | Matriz rol × módulo × permiso |
| Configuración | ✅ | Crear/consultar los catálogos del sistema |
| Bitácora | ✅ | Auditoría con filtros y exportación |
| Respaldo BD | ✅ | Descarga `.sql` de negocio o seguridad |
| Módulos de negocio | 🔜 | Citas, Psicología, Medicina, Inventario, Reportes… |

---

## 🔐 Seguridad

- **Contraseñas**: nunca viajan ni se guardan en texto plano.
  `RSA (cliente) → OpenSSL (servidor) → bcrypt (BD)`.
- **Inyección SQL**: 100 % de consultas con **sentencias preparadas** (PDO).
- **XSS**: escapado de salida con `htmlspecialchars()`.
- **CSRF/mitigación**: cookies `HttpOnly` + `SameSite=Lax`.
- **JWT** firmado con **RS256** (solo el servidor firma).
- **Rate limiting** por *Token Bucket* y **bloqueo por intentos fallidos**.
- **RBAC** verificado en el servidor (no se confía en ocultar botones).
- **Auditoría** de todas las operaciones de escritura.

El detalle completo está en [`GUIA-BACKEND-FRONTEND.md`](GUIA-BACKEND-FRONTEND.md).

---

## 📚 Documentación

| Documento | Contenido |
|---|---|
| [`GUIA-MODULOS.md`](GUIA-MODULOS.md) | **Cómo crear un módulo nuevo**, paso a paso |
| [`GUIA-BACKEND-FRONTEND.md`](GUIA-BACKEND-FRONTEND.md) | Explicación completa de backend y frontend, con conceptos y estándares |
| [`GUIA_ARQUITECTURA_API.md`](GUIA_ARQUITECTURA_API.md) | Arquitectura de la API |
| [`docs/bd/`](docs/bd/) | Esquemas SQL y scripts incrementales |
| [`AGENTS.md`](AGENTS.md) | Contexto técnico del repositorio (reglas y convenciones) |

---

## 🛠 Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| `404 Not Found` | Apache no encuentra el proyecto | Configura el *Alias* / *VirtualHost* |
| `500 Internal Server Error` | Falta una base, permisos o `vendor/` | Revisa `logs/php_errors.log` |
| "No se pudo procesar la contraseña" | Apache no lee la llave privada | `chmod 644 app/Config/Keys/login_private.pem` |
| `Unknown database 'dirpoles_…'` | Bases no importadas | Importa los SQL de `docs/bd/` |
| `Class "App\Models\…" not found` | `vendor/` ausente | `composer install` |
| Redirección constante al login | Cookie JWT no se guarda | Revisa `APP_URL`/dominio y `SameSite` |
| Página en blanco | Error fatal | Revisa `logs/php_errors.log` |
| "Cuenta bloqueada" | 3 intentos fallidos | Reactiva con `UPDATE empleado SET estatus=1 …` |
| "Límite de peticiones excedido" | Rate limit (429) | Espera lo indicado en `Retry-After` |

---

## 📄 Licencia

Proyecto **académico** desarrollado para la **UPTAEB** (Universidad Politécnica
Territorial de los Altos Llanos Occidentales "José Antonio Anzoátegui").
Software de uso educativo; consulta con el autor antes de reutilizarlo.

---

<div align="center">

**DIRPOLES-4** · Dirección de Políticas Estudiantiles · UPTAEB

Hecho con PHP, mucho café y buenas prácticas ☕

</div>
