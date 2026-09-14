# DIRPOLES-4 — Esqueleto base (MVC, monolito híbrido)

Base mínima y funcional del sistema DIRPOLES: autenticación completa, motor RBAC,
auditoría, rate limiting y notificaciones (bandeja + SSE) como infraestructura.
**Sin módulos de negocio**: esa parte se construye módulo a módulo siguiendo
`GUIA-MODULOS.md`.

> ⚠️ El sistema completo anterior (con todos sus módulos) está en el directorio
> hermano `../DIRPOLES_4`. Este repositorio **no es un repositorio git** (no hay
> `.git`): para consultar código viejo, lee directamente `../DIRPOLES_4`.

---

## Requisitos previos

| Componente | Versión |
|------------|---------|
| PHP | 8.1+ (con `openssl`, `pdo_mysql`, `mbstring`) |
| MySQL / MariaDB | 10.4+ |
| Apache | 2.4+ (con `mod_rewrite`) |
| Composer | 2.x |
| OpenSSL | CLI (`openssl` en terminal) |

---

## Instalación

### 1. Clonar

```bash
git clone <url-del-repo> DIRPOLES-4
cd DIRPOLES-4
```

> El nombre de la carpeta puede ser cualquiera. El sistema detecta la URL base automáticamente.

### 2. Script automático (Linux)

```bash
chmod +x setup_linux.sh
./setup_linux.sh
```

El script configura Apache, crea el `.env`, genera las llaves RSA (privadas con
permiso `600`), instala Composer e importa las bases de datos.

### Manual, paso a paso

**Variables de entorno:**

```bash
cp .env.example .env
```

| Variable | Descripción |
|----------|-------------|
| `APP_DEBUG` | `true` solo en desarrollo (los errores muestran detalles) |
| `JWT_EXPIRATION` | Duración del JWT en segundos (3600 = 1 h; se renueva con refresh token) |
| `REFRESH_EXPIRATION` | Duración del refresh token (1296000 = 15 días) |
| `CORS_ALLOWED_ORIGINS` | Orígenes autorizados separados por coma (app móvil, clientes dev) |

**Dependencias:**

```bash
composer install
```

**Llaves RSA** (cifrado asimétrico para login y JWT):

```bash
mkdir -p app/Config/Keys

# Llaves para login (cifrado de contraseñas)
openssl genrsa -out app/Config/Keys/login_private.pem 2048
openssl rsa -in app/Config/Keys/login_private.pem -pubout -out app/Config/Keys/login_public.pem

# Llaves para JWT (firma y validación de tokens)
openssl genrsa -out app/Config/Keys/jwt_private.pem 2048
openssl rsa -in app/Config/Keys/jwt_private.pem -pubout -out app/Config/Keys/jwt_public.pem

chmod 600 app/Config/Keys/*_private.pem   # privadas: solo el dueño
chmod 644 app/Config/Keys/*_public.pem
```

**Base de datos** (los SQL están en `docs/bd/`):

```bash
mysql -u root -p < docs/bd/dirpoles_security.sql
mysql -u root -p < docs/bd/dirpoles_business.sql
```

> Si ya tenías la BD creada, aplica los scripts incrementales en `docs/bd/`
> (p. ej. `notificaciones_modulo.sql`, `login_intentos.sql`): son idempotentes.

**Permisos:**

```bash
mkdir -p logs
chmod 777 logs
chmod 644 .env
```

---

## Verificar instalación

Abre `http://localhost/DIRPOLES-4/login`. Deberías ver el formulario de inicio de
sesión. Tras autenticarte llegas a un dashboard vacío: es tu lienzo.

---

## Estructura del esqueleto

```
app/
├── Config/
│   ├── Keys/                 # Llaves RSA (no versionadas)
│   └── modulos_sidebar.php   # Mapa del sidebar (vacío: agrégalo aquí)
├── Controllers/              # SOLO funciones (regla de la universidad)
│   ├── loginController.php
│   ├── notificacionesController.php
│   ├── sseController.php
│   └── dashboardController.php
├── Core/                     # CLASES de infraestructura
│   ├── Router.php            #   Enrutador
│   ├── Database.php          #   Conexión dual (business/security)
│   ├── JwtHandler.php        #   JWT + refresh tokens
│   ├── ErrorCodes.php        #   Registro central de códigos de error
│   ├── ExcepcionApi.php      #   Excepción de negocio (código + HTTP + mensaje)
│   ├── Respuesta.php         #   Contrato JSON único + handler global
│   ├── Autorizacion.php      #   RBAC en una línea
│   ├── Bitacora.php          #   Auditoría en una línea
│   └── Notificador.php       #   Notificaciones en una línea
├── Middlewares/              # Rate limiting + sesión/JWT
├── Models/                   # CLASES con manejarAccion()
│   ├── BusinessModel.php     #   Base para BD de negocio
│   ├── SecurityModel.php     #   Base para BD de seguridad
│   ├── loginModel.php
│   ├── PermisosModel.php
│   ├── NotificacionesModel.php
│   ├── DashboardModel.php
│   └── CalendarioModel.php
├── Views/
│   ├── template/             # head, header, sidebar, footer, script
│   ├── inicio/               # Dashboard (ejemplo canónico de vista)
│   ├── errors/               # 404.php, error.php, rate_limit.php, access_denied.php
│   └── login.php
├── routes/                   # Un archivo .php por módulo (notificaciones.php, dashboard.php)
├── bootstrap.php             # Autocarga de controladores + logs
└── routes.php                # Rutas esenciales + middlewares globales
```

---

## La regla de las dos puertas

| Puerta | Responde | Ejemplos |
|--------|----------|----------|
| Rutas bajo `api/*` (o `Accept: application/json`) | **JSON** con contrato `{exito, datos}` / `{exito, error}` | endpoints de la app móvil |
| Cualquier otra ruta | **HTML** (vista renderizada o redirección) | páginas web |

El handler global de `index.php` decide el formato automáticamente: si una
excepción escapa en `api/*` responde JSON del contrato; en una página, registra
el error y muestra una página HTML. **Ningún controlador necesita try/catch para
formatear.**

---

## Recuperar código del sistema antiguo

El sistema completo vive en el directorio hermano `../DIRPOLES_4`:

```bash
# Listar los controladores que existían
ls ../DIRPOLES_4/app/Controllers/

# Copiar un archivo como referencia a tu zona de trabajo
cp ../DIRPOLES_4/app/Controllers/beneficiarioController.php /tmp/
```

Puedes consultarlo como referencia, pero el código nuevo debe seguir las
convenciones de este esqueleto (no copiar-pegar el estilo viejo).

---

## Solución de problemas

| Error | Causa | Solución |
|-------|-------|----------|
| `404 Not Found` al acceder | Apache no encuentra el proyecto | Configurar Alias o copiar a htdocs |
| `500 Internal Server Error` | Permisos o BD faltante | Revisar `logs/php_errors.log` |
| `Fallo al descifrar la contraseña` | Apache no puede leer la llave privada | `chmod 644 app/Config/Keys/login_private.pem` |
| `Unknown database 'dirpoles_...'` | BD no creada | Importar SQL de `docs/bd/` |
| Clase no encontrada | `vendor/` no existe | `composer install` |
| Página en blanco tras login | Error fatal registrado | Revisar `logs/php_errors.log` |

---

## Documentación

- **`GUIA-MODULOS.md`** — Cómo crear tu primer módulo, paso a paso (ruta → controlador → modelo → vista).
- **`docs/GUIA-BACKEND.MD`** — Referencia del backend de EH-SYSTEM (guía externa).
- **`docs/`** — Manual de contexto, guía de arquitectura original, plan de mejoras, SQL.
