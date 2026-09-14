# AGENTS.md — Contexto para IA (DIRPOLES-4)

> Este archivo le da contexto a cualquier agente de IA que trabaje en este
> repositorio. Léelo COMPLETO antes de hacer cambios. La documentación de
> trabajo del proyecto está en español y el código usa nombres en español:
> mantén esa convención.

---

## 1. Qué es este repositorio

**DIRPOLES-4** es el **esqueleto base** del sistema DIRPOLES: un monolito
híbrido PHP 8+ con MVC propio (SIN framework), para la gestión integral de
estudiantes beneficiarios de la UPTAEB. Es el proyecto final de la carrera de
Ingeniería en Informática (4to trayecto, PNF Informática).

Este repositorio fue **purgado a propósito**: quedó la infraestructura
(login, RBAC, auditoría, notificaciones, middlewares, templates) y se van
construyendo los módulos de negocio uno a uno. Un módulo nuevo se crea
siguiendo **`GUIA-MODULOS.md`** (paso a paso, con ejemplo completo). Léela
antes de proponer código nuevo.

### Estado actual (verificado)

- **Autenticación completa**: login (RSA + bcrypt), JWT RS256, refresh token
  **con rotación (one-time use)**, bloqueo por intentos **persistido en BD**
  (`login_intentos`), cierre de sesión.
- **Módulo transversal de Notificaciones** ya operativo: bandeja del usuario
  (listar/contar/marcar/eliminar) por API + streaming **SSE** para la campana
  del topbar. Es la primera excepción deliberada a "solo `api/*` o HTML":
  `sse/notificaciones` responde `text/event-stream`.
- **Panel de Inicio (`/inicio`) operativo**: una sola vista que se compone por
  rol/permisos. El administrador ve **cards de módulos** (generadas desde
  `$_SESSION['modulosPermitidos']` + `app/Config/dashboard_cards.php`, con
  **contador real** vía `data-stat`/`api/dashboard/stats`) y un resumen
  operativo; cada empleado ve las **estadísticas de su módulo**. Todos tienen
  **calendario personal** con CRUD por API.
- **Módulos ya construidos sobre el esqueleto** (con rutas, controladores,
  modelos, vistas y JS siguiendo la GUIA-MODULOS.md):
  - **Empleados** (id_modulo 1): CRUD completo, stats, validaciones asíncronas.
  - **Beneficiarios** (id_modulo 2): CRUD completo, stats, validaciones asíncronas.
  - **Configuración** (id_modulo 14): catálogos del sistema (crear/consultar).
  - **Bitácora** (id_modulo 16): consulta de auditoría con filtros y exportación.
  - **Permisos** (id_modulo 17): matriz rol × módulo × permiso.
  - **Respaldo BD** (tercera puerta: descarga `.sql`, solo Administrador/Superusuario).
- **Faltan los módulos de negocio pesados**: citas, psicología, medicina,
  orientación, trabajo social, discapacidad, inventario, referencias, jornadas,
  mobiliario, transporte, horarios (ver `app/Config/dashboard_cards.php`, las
  cards con `'disponible' => false` son los pendientes).

### Repositorio y relación con el sistema completo

- Este repositorio **SÍ es git ahora** y está publicado en GitHub:
  `https://github.com/mroberth/DIRPOLES-4` (rama `main`). Sí existen `git log`
  y el historial; sigue SIN existir tags. Las llaves RSA (`app/Config/Keys/`),
  `.env` y `vendor/` NO se versionan (`.gitignore`).
- El sistema completo anterior (con todos sus módulos) sigue estando en el
  directorio hermano `../DIRPOLES_4` (fuera de este repo, sin git).
- Puedes CONSULTAR el sistema viejo como referencia, pero el código nuevo debe
  seguir las convenciones nuevas de este esqueleto (no copiar-pegar estilo viejo).

---

## 2. Reglas innegociables (exigencias de la universidad)

1. **Controllers = SOLO funciones.** Jamás declarar clases, ni instanciar
   lógica de clase dentro de un controlador (razón: el diagrama de clases de
   la documentación solo documenta Modelos y Core).
2. **Clases solo en `app/Models/` y `app/Core/`.**
3. **NO introducir capas Service/Repository/UseCase.** El "service" es la
   acción de `manejarAccion()`; el "repository" son los métodos privados SQL
   de la misma clase modelo. No crear carpetas `Services/`, `Repositories/`.
4. **Frontend renderizado en servidor** (PHP + templates en
   `app/Views/template/`). NO proponer separar backend/frontend, NO proponer
   SPA, NO proponer APIs REST "de libro".
5. **Doble base de datos**: `dirpoles_security` (usuarios, RBAC, bitácora,
   tokens, notificaciones, rate limits) y `dirpoles_business` (transacciones).
   Jerarquía: `Database → SecurityModel / BusinessModel → [Tu]Model`.
6. **Mantener el despachador `manejarAccion($accion)`** como punto de entrada
   público del modelo. (Histórico: en el sistema viejo `loginModel` usaba
   `manejador()`; este esqueleto ya lo migró a `manejarAccion()`.)
7. No agregar dependencias composer/npm sin justificar; el sistema es
   deliberadamente autocontenido (plugins locales en `/plugins`).

---

## 3. La regla de las DOS PUERTAS (arquitectura de respuesta)

| Puerta | Ruta | Responde | Ejemplo |
|---|---|---|---|
| HTML | cualquier ruta fuera de `api/` | HTML renderizado o redirección | `productos/consultar` |
| JSON | rutas bajo `api/*` o `Accept: application/json` | JSON del contrato | `api/productos/crear` |

Nunca mezclar: una página NUNCA escupe JSON, un endpoint `api/*` NUNCA
renderiza HTML. El handler global decide el formato según la puerta — el
controlador no formatea errores.

**Única excepción**: `sse/notificaciones` (`text/event-stream`), que no es ni
HTML ni JSON; vive fuera de `api/` y la maneja `sseController.php`.

**Tercera puerta (descarga de archivo)**: `respaldo/descargar` responde un
archivo `.sql` (`Content-Type: application/sql`, `Content-Disposition:
attachment`). Tampoco es HTML ni JSON; la maneja `backupController.php` y solo
la puede usar Administrador/Superusuario.

### Contrato JSON ÚNICO (clase `App\Core\Respuesta`)

```jsonc
// Éxito (cualquier endpoint JSON):
{ "exito": true, "datos": <cualquier cosa> }

// Error:
{ "exito": false, "error": { "codigo": "ALREADY_EXISTS", "estado": 409, "mensaje": "..." } }
```

- Controlador: `Respuesta::exito($datos, 201)` o `Respuesta::error($e)` (ambos hacen `exit`).
- El frontend JS programa contra `error.codigo` (estable), NUNCA contra `mensaje`.
- **Resuelto**: `RateLimitMiddleware` ya responde 429 con el contrato
  (`ErrorCodes::RATE_LIMIT_EXCEDIDO`). Mantén ese patrón en cualquier límite nuevo.

---

## 4. Manejo de errores (obligatorio)

- **Los modelos LANZAN, no devuelven errores**: `throw ExcepcionApi::yaExiste('...')`.
  Nunca `return ['estado' => 'error']` ni arrays con `exito => false`.
- **Excepciones de negocio** → `App\Core\ExcepcionApi` con fábricas:
  `validacion()` 400 · `noEncontrado()` 404 · `yaExiste()` 409 · `enUso()` 409 ·
  `accesoDenegado()` 403 · `errorInterno()` 500 · `jsonInvalido()` 400.
- **Códigos nuevos** → constantes en `App\Core\ErrorCodes.php`. Prohibido
  inventar strings de error sueltos.
- **El controlador NO necesita try/catch para formatear**: si una excepción
  escapa, el handler global de `index.php` la convierte en JSON (puerta api)
  o página HTML (página). Atrapa excepciones solo si hay lógica extra antes
  de responder.
- SQLSTATE 23000 (constraint) → `ExcepcionApi::enUso()`.
- `LIMIT/OFFSET` SIEMPRE con `bindValue(..., PDO::PARAM_INT)`.
- **Página 404 dedicada**: `app/Views/errors/404.php` (autocontenida). La usan
  `Router::rutaNoEncontrada()` y `Respuesta::manejarExcepcion()` cuando el
  estado es 404. El resto de estados HTML sigue usando `errors/error.php`.

---

## 5. Helpers de una línea (Core) — úsalos, no reinventes

```php
// RBAC: lanza ExcepcionApi 403 si falta el permiso. SIEMPRE primera línea del controlador.
Autorizacion::verificar('productos', 'crear');   // nombres, resuelve IDs contra BD
Autorizacion::tiene('productos', 'leer');        // variante booleana

// Auditoría (NUNCA lanza; id_empleado y fecha automáticos):
Bitacora::registrar('Productos', 'Registro', 'Creó el producto "X"');
// Acciones válidas (ENUM real de la tabla bitacora):
// Registro, Lectura, Actualización, Eliminación, Inicio de sesión,
// Cierre de sesión, Respaldo

// Notificación (NUNCA lanza):
Notificador::enviar($idReceptor, 'Título', 'productos/consultar', 'info');
```

> OJO: `Bitacora::ACCIONES` debe coincidir EXACTAMENTE con el ENUM real de
> `bitacora.accion`. Si agregas una acción al arreglo sin migrar el ENUM, el
> INSERT falla y el error se traga (log + false). Hoy ambas listas tienen las
> siete acciones válidas (incluye `Respaldo`).

---

## 6. Convenciones por capa

**Controller** (`app/Controllers/`, solo funciones, carga perezosa con
`load_controller('xController.php')`):
1. `Autorizacion::verificar()` primera línea.
2. Lee HTTP (`$_POST`, `php://input`).
3. Setea atributos del modelo con `__set` y llama `manejarAccion()`.
4. Efectos secundarios: `Bitacora::registrar()`, `Notificador::enviar()`.
5. `Respuesta::exito()` (API) o `require_once` de la vista (página).

**Model** (`app/Models/`, clases con el patrón fijo):
- `__set()` = capa de validación (lanza `ExcepcionApi::validacion()` si mal).
- `manejarAccion($accion)` = switch despachador (única puerta pública).
- métodos privados = SQL + reglas de negocio; lanzan `ExcepcionApi`.
- Extiende `BusinessModel` (negocio) o `SecurityModel` (seguridad).
- Conexiones: `$this->conn` (business) / `$this->conn_security` (security).

**View** (`app/Views/`):
- `$titulo` definido ANTES de `include 'app/Views/template/head.php'`.
- Estructura: head → sidebar → header → contenido → footer → script.
- Salida dinámica con `htmlspecialchars()`.
- JS del módulo en `dist/js/modulos/<modulo>/<archivo>.js`, incluido al final.
- El JS consume la API con `fetch` + `Accept: application/json` (helper
  `dist/js/core/apiFetch.js`), maneja `401` navegando a `datos.redireccion`,
  y distingue errores por `error.codigo`.
- **Patrón de JS por módulo** (ver `dist/js/modulos/empleado/`): separa en
  `validaciones.js` (validaciones reutilizables crear/editar vía
  `EmpleadoValidaciones.configurar(form, {idExcluir, claveOpcional})`),
  `tour.js` (Driver.js, con botón `#btn-ayuda`), `stats.js` (tarjetas de
  resumen del módulo por `[data-stat]`) y `crear.js`/`editar.js` (solo la
  lógica de esa pantalla). Se cargan con `defer`, en ese orden.
- **Select2**: se inicializa por clase (`.select2`) con
  `dist/js/core/select-2-init.js` y `window.initSelect2(scope)` para reps.
  El estado de validación (rojo/verde) se pinta con
  `dist/css/etc/select2-validacion.css` (cubre `.is-valid`/`.is-invalid`
  tanto en el `<select>` como en `.select2-selection`).
- **Composición por rol (ejemplo: `/inicio`)**: una sola vista shell decide el
  contenido por permisos/rol. Las piezas reutilizables viven en
  `app/Views/inicio/components/` (`stat_card.php`, `card_modulo.php`, un
  `stats_*.php` por rol). Las stats se pintan desde
  `data-stat="clave"` que rellena `dist/js/modulos/dashboard/dashboard_stats.js`
  con UNA llamada a `api/dashboard/stats`; NO hardcodees datos en la vista.

**Routes** (`app/routes/`): un archivo por módulo; se auto-cargan por glob.
Páginas: `modulo/accion` (GET). API: `api/modulo/accion` (GET leer / POST escribir).
- **Los parámetros de URL `{id}` se inyectan en `$_GET`.** El Router reconoce
  el patrón (ej: `modulo/ver/{id}`) y expone el valor como query param: el
  controlador lo lee con `$_GET['id']` (o `filter_input`). No se pasan como
  argumentos posicionales al closure de la ruta.

**Sidebar**: entrada en `app/Config/modulos_sidebar.php` con clave = `id_modulo`
real de la BD. El módulo debe existir en la tabla `modulo` y tener filas en
`rol_modulo_permiso` para verse. Hoy ya tiene entradas para Empleados (1),
Beneficiarios (2) y Configuración (14, con subitems de Bitácora/Permisos/Respaldo
que usan `id_modulo` explícito porque validan contra otro módulo); AGREGA AHÍ tu
módulo nuevo al crearlo.

---

## 7. Base de datos y seguridad (no romper)

- Esquemas SQL de referencia en `docs/bd/dirpoles_{security,business}.sql`
  y scripts incrementales idempotentes: `docs/bd/notificaciones_modulo.sql`,
  `docs/bd/login_intentos.sql`, `docs/bd/bitacora_respaldo.sql`.
- RBAC: tablas `modulo`, `permiso` (**1=Crear, 2=Leer, 3=Editar, 4=Eliminar**),
  `rol_modulo_permiso` (rol × módulo × permiso). El sidebar se filtra por
  permiso id 2 (Leer) vía `PermisosModel::obtenerPermisosSidebar`.
- Módulo real de Notificaciones = `id_modulo` 19 (nombre `Notificaciones`).
- Login: contraseña cifrada RSA en el cliente → descifrada con
  `app/Config/Keys/login_private.pem` → bcrypt. Bloqueo tras 3 intentos
  fallidos **persistidos en `login_intentos`** (tabla de `dirpoles_security`),
  para no-admin. `session_regenerate_id(true)` al autenticar (anti
  session-fixation).
- Sesión PHP + JWT RS256 en cookie HttpOnly, validación cruzada en
  `SessionAuthMiddleware` (id_empleado sesión vs payload JWT). Refresh tokens
  en BD (`refresh_tokens`) con **rotación one-time use**: `renovar_jwt` revoca
  el token usado y emite uno nuevo en la misma transacción; revocados en logout.
  - **Deuda vigente (verificada 2026-09-14)**: el refresh token se guarda en
    texto plano en `refresh_tokens`; lo ideal es hashearlo (SHA-256) como una
    contraseña. No ha sido resuelta aún.
- `session.gc_maxlifetime` se fija en `index.php` al máximo entre
  `JWT_EXPIRATION` y `REFRESH_EXPIRATION`, para que la sesión no muera antes
  que el JWT (el refresh depende de la sesión).
- `RateLimitMiddleware`: token bucket por IP×endpoint persistido en BD
  (`dirpoles_security.rate_limits`). Responde 429 con el contrato JSON o con
  `errors/rate_limit.php` según la puerta.
- **SSE (`sse/notificaciones`)**: mantiene una conexión PDO por hasta 1 h. Si
  MySQL se reinicia o cierra el socket (`2006 MySQL server has gone away`),
  `NotificacionesModel::reconectar()` la reabre y `sseController` la invoca al
  capturar el error, para no fallar en cada iteración.
- Llaves RSA en `app/Config/Keys/` (NO versionadas; privadas chmod 600).
- `.env`: `APP_DEBUG`, `DB_*`, `DB_SECURITY_*`, `JWT_EXPIRATION`,
  `REFRESH_EXPIRATION`, `APP_ENV`, `CORS_ALLOWED_ORIGINS` (no `CORS_ORIGINS`).
  Los orígenes CORS NO se hardcodean en `index.php`.

---

## 8. Mapa rápido del repositorio

```
index.php               Front controller: CORS(.env) → sesión → dotenv → handler global → Router
app/routes.php          Middlewares globales + rutas esenciales + 404/405 por puerta
app/bootstrap.php       load_controller() + helper env() + logs
app/Core/               Router, Database, JwtHandler, Respuesta, ExcepcionApi,
                        ErrorCodes, Autorizacion, Bitacora, Notificador
app/Middlewares/        RateLimitMiddleware, SessionAuthMiddleware
app/Controllers/        loginController.php, notificacionesController.php,
                        sseController.php, dashboardController.php,
                        empleadoController.php, beneficiarioController.php,
                        permisosController.php, configuracionController.php,
                        bitacoraController.php, backupController.php (solo funciones)
app/Models/             loginModel, PermisosModel, NotificacionesModel,
                        DashboardModel, CalendarioModel, SecurityModel, BusinessModel,
                        EmpleadoModel, BeneficiarioModel, ConfiguracionModel, BitacoraModel,
                        BackupModel
app/Views/              template/ (head, header, sidebar, footer, script),
                        inicio/dashboard.php (shell compuesto por rol), login.php,
                        errors/ (404, error, rate_limit, access_denied),
                        inicio/components/ (stat_card, card_modulo, stats_*),
                        empleados/, beneficiarios/ (crear + consultar con modal),
                        permisos/ (matriz de permisos rol × módulo),
                        configuracion/ (crear + consultar catálogos, respaldo BD),
                        bitacora/ (consulta de auditoría)
app/Config/             modulos_sidebar.php, dashboard_cards.php, roles_sistema.php,
                        configuracion_catalogos.php, Keys/ (RSA, no versionadas)
                        (NO existe config.php: la config va por .env)
app/routes/             notificaciones.php, dashboard.php, empleados.php,
                        beneficiarios.php, permisos.php, configuracion.php,
                        bitacora.php, backup.php (los demás módulos los creas tú)
docs/                   docs/MANUAL_DIRPOLES_CONTEXTO.md (manual de contexto),
                        docs/guia_arquitectura_dirpoles.md (arquitectura),
                        docs/GUIA-BACKEND.MD (guía backend),
                        docs/IMPROVEMENT_PLAN.md (plan de mejoras), SQL (bd/),
                        PDF (fpdf)
dist/                   CSS/JS/IMG propios:
                        js/core/ (apiFetch, AlertManager, logout, modalManager),
                        js/login/login.js, js/jwt-refresh.js,
                        js/modulos/notificaciones/control.js,
                        js/modulos/dashboard/dashboard_stats.js,
                        js/modulos/calendario/calendario_personal.js,
                        js/modulos/{empleado,beneficiario,configuracion,bitacora,permisos}/,
                        css/dashboard/dashboard.css
plugins/                Librerías front auto-hospedadas (Bootstrap 5, DataTables, Select2,
                        SweetAlert2, FullCalendar, jsPDF, jsencrypt...)
GUIA-MODULOS.md         ← GUÍA PRINCIPAL para crear módulos nuevos
GUIA-BACKEND-FRONTEND.md  Guía explicativa backend+frontend (conceptos y
                        diferencias con el sistema viejo) — para defensa
README.md               Instalación y estructura
uploads/                Archivos subidos por los módulos (hoy: trabajo_social/)
setup_linux.sh          Instalación automática (Apache, llaves, BD)
logs/                   php_errors.log
```

---

## 9. Flujo de una petición (memoria rápida)

```
HTTP → index.php (CORS, sesión, handler global)
     → Router: RateLimitMiddleware → SessionAuthMiddleware
     → app/routes/*.php → load_controller() → función del controlador
     → Autorizacion::verificar() → Modelo::__set → manejarAccion() (SQL, throws)
     → Bitacora/Notificador (efectos, nunca lanzan)
     → Respuesta::exito() [api]  ó  vista HTML [página]  ó  SSE [sse/*]
```

---

## 10. Al proponer cambios, verifica

- [ ] ¿Respetaste las reglas de la sección 2? (sin clases en Controllers, sin capas nuevas)
- [ ] ¿Los errores usan `ExcepcionApi` + `ErrorCodes`? (cero arrays de error ad-hoc)
- [ ] ¿Toda salida JSON pasa por `Respuesta`?
- [ ] ¿Las rutas nuevas respetan la regla de las dos puertas?
- [ ] ¿Operaciones de escritura registran en Bitácora?
- [ ] `php -l` sobre cada archivo PHP modificado.
