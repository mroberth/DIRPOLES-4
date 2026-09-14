# 🏗️ DIRPOLES-4 — Guía de Arquitectura: API REST

> **Este documento es el "guión" de la migración.** Explica qué se copia desde `DIRPOLES_4`, por qué, cómo está organizada la nueva API y qué NO se copia. Es el punto de partida antes de tocar código.

---

## 1. 🎯 La idea en una frase

**`DIRPOLES_4` queda intacto** (sigue siendo el monolito que renderiza HTML para el navegador). **`DIRPOLES-4` se convierte en una API REST pura**: JSON entra, JSON sale, **cero HTML**, cero sesiones PHP renderizadas. La app móvil y cualquier cliente futuro (SPA, React, Vue, otro backend) consumirán esta API.

```
                    ┌─────────────────────────────┐
                    │        NAVEGADOR            │
                    │  (HTML, CSS, JS tradicional)│
                    └──────────────┬──────────────┘
                                   │
                 ┌─────────────────▼─────────────────┐
                 │  DIRPOLES_4  (MONOLITO - intacto) │
                 │  PHP + MVC + Views/*.php          │
                 │  Renderiza HTML (login, inicio…)  │
                 └─────────────────┬─────────────────┘
                                   │
        MySQL: dirpoles_business   │   MySQL: dirpoles_security
              ▲        ▲           │         ▲
              │        │           │         │
              │        └────────────┼─────────┘
              │                    │
              │     ┌──────────────▼──────────────────────┐
              │     │   DIRPOLES-4  (API REST - nuevo)    │
              │     │   PHP 8 + JWT + JSON only           │
              │     │   Sin Views, sin sesiones, sin HTML │
              │     └──────────────▲──────────────────────┘
              │                    │
              │      ┌─────────────┴─────────────┐
              │      │                           │
     ┌────────┴──────┴─────┐          ┌──────────┴──────────┐
     │    APP MÓVIL        │          │  CLIENTES FUTUROS   │
     │ ( hoy api/movil de  │          │ ( SPA, integraciones│
     │   DIRPOLES_4 )      │          │   otros sistemas )  │
     └─────────────────────┘          └─────────────────────┘
```

> **Nota:** Las dos bases de datos (`dirpoles_business` y `dirpoles_security`) se comparten. La API no duplica datos: consulta las mismas tablas. Por eso las credenciales van en el `.env` de cada proyecto y ambas apuntan al mismo servidor MySQL.

---

## 2. 🧭 Principios REST que aplicaremos (nivel universitario)

| Principio | Cómo se cumple aquí |
|---|---|
| **Recursos, no acciones** | `POST /api/v1/auth/login`, `GET /api/v1/beneficiarios/{id}` — el verbo HTTP dice la acción, la URL nombra el recurso. (Adiós a `{"modulo": "x", "accion": "y"}`.) |
| **Sin estado (Stateless)** | **No hay `$_SESSION`**. Toda identidad viaja en el JWT del header `Authorization: Bearer <token>`. Cada petición es autocontenida. |
| **Códigos de estado HTTP reales** | `200` OK, `201` creado, `204` sin contenido, `400` petición malformada, `401` no autenticado, `403` sin permisos/bloqueado, `404` no existe, `409` conflicto, `422` validación, `429` rate limit, `500` error interno. |
| **Formato de error estándar (RFC 7807-like)** | Todos los errores tienen la misma forma JSON (ver §6). El cliente siempre sabe dónde buscar `code`, `message`, `details`. |
| **Versionado en la URL** | Todo endpoint vive bajo `/api/v1/...`. Cuando rompamos compatibilidad → `/api/v2/...` sin matar clientes viejos. |
| **JSON único y uniforme** | `Content-Type: application/json; charset=utf-8` en TODA respuesta. Nunca HTML. |
| **Seguridad por capas** | RSA en login (igual que el monolito), JWT RS256, refresh tokens en BD, rate limiting, CORS explícito, prepared statements. |

---

## 3. 📦 QUÉ se copia de `DIRPOLES_4` → `DIRPOLES-4` (y qué NO)

### 3.1 ✅ Se copia (adaptado a la API)

| Origen (`DIRPOLES_4/app/...`) | Destino (`DIRPOLES-4/app/...`) | Por qué |
|---|---|---|
| `Core/Database.php` | `Core/Database.php` | Conexión PDO dual (business/security). **Idéntico, es la capa de datos.** |
| `Models/SecurityModel.php` | `Models/SecurityModel.php` | Base de los modelos de seguridad. Idéntico. |
| `Models/BusinessModel.php` | `Models/BusinessModel.php` | Base de los modelos de negocio. Idéntico. |
| `Models/loginModel.php` | `Models/AuthModel.php` | La lógica `authenticate()` / `Deshabilitar()` es exactamente la que necesita el login. **Se recorta** (fuera las estadísticas del dashboard, eso no es auth) y se renombra a dominio limpio. |
| `Models/BitacoraModel.php` | `Models/BitacoraModel.php` | Auditoría: cada login/logout debe quedar registrado. **Idéntico** (sus validadores `__set` son un buen estándar). |
| `Core/JwtHandler.php` | `Core/JwtHandler.php` | Emisión/validación JWT RS256 + refresh tokens en BD. Idéntico: es lógica pura, sin render. |
| `app/Config/Keys/*.pem` | `app/Config/Keys/*.pem` | Las llaves RSA de login y de firma JWT. **Ver nota §7** sobre cómo generar llaves nuevas si prefieres aislar los proyectos. |
| Lógica de intentos fallidos (3 intentos → bloqueo) | `Controllers/AuthController.php` | Se conserva la regla de negocio, pero devuelve `403` en vez de mensajes de vista. |
| Política de rate limit por niveles | `Middlewares/RateLimitMiddleware.php` | Mismo token-bucket en BD (`rate_limits`), pero **responde siempre JSON** (fuera la página HTML de bloqueo). |
| Tablas de BD (`dirpoles_security.rate_limits`, `refresh_tokens`, `bitacora`) | *(no se copian, se reutilizan)* | La API usa las mismas tablas de seguridad que el monolito. Ver `docs/SQL_API.sql`. |

### 3.2 ❌ NO se copia (queda en el monolito)

| Excluido | Motivo |
|---|---|
| `app/Views/**` (todo) | La API no renderiza nada. El front (web y móvil) será consumidor de JSON. |
| `app/Config/modulos_sidebar.php` | Es presentación (menú). En una API, el cliente decide su UI; la API expone permisos vía endpoint. |
| `Middlewares/SessionAuthMiddleware.php` | Depende de `$_SESSION` y redirecciones HTML. Se sustituye por `JwtAuthMiddleware` (stateless). |
| `Controllers/Movil/**` (el "mini-router" JSON `modulo/accion`) | Es justo lo que se va a **reemplazar** por REST real: cada acción se convierte en un endpoint con su verbo y recurso propio. |
| Lógica de "vista previa" de módulos (dashboard, reportes HTML, PDFs) | Llegará en fases posteriores, cada una como recurso REST. |
| `package.json`, `dist/`, `plugins/` | Son assets de front. La API no tiene front. |

> 💡 **Regla de oro para todo el proyecto:** *si un archivo necesita `$_SESSION`, `header('Location: …')` o `require views/…`, no entra a DIRPOLES-4 tal cual; se adapta o se reescribe.*

---

## 4. 🗺️ Esquema de carpetas objetivo (DIRPOLES-4)

```
DIRPOLES-4/
├── index.php                      # Front controller único (punto de entrada)
├── .htaccess                      # Todo → index.php
├── .env.example                   # Plantilla de configuración
├── composer.json                  # Autoload PSR-4 (App\) + firebase/php-jwt + phpdotenv
├── GUIA_ARQUITECTURA_API.md       # ← Este documento
├── README.md                      # Arranque rápido + ejemplos curl
├── docs/
│   ├── PLAN_MIGRACION_REST.md     # Plan por fases: qué se migra y cuándo
│   └── SQL_API.sql                # Tablas que la API espera encontrar en dirpoles_security
├── logs/                          # php_errors.log
└── app/
    ├── bootstrap.php              # Registro de helpers + autoload de config
    ├── routes.php                 # Registro central de endpoints (/api/v1/...)
    ├── Config/
    │   └── config.php             # Constantes DB + JWT (lee .env)
    ├── Core/
    │   ├── Database.php           # ← copiado tal cual del monolito
    │   ├── Router.php             # ← adaptado: BASE_PATH 'api', soporta {param} con nombre
    │   └── JwtHandler.php         # ← copiado tal cual del monolito
    ├── Http/
    │   ├── Request.php            # Envoltura de la petición (JSON body, headers, params)
    │   └── ApiResponse.php        # ← EL ESTÁNDAR: éxito/error uniformes + códigos HTTP
    ├── Middlewares/
    │   ├── RateLimitMiddleware.php # ← adaptado: solo JSON
    │   └── JwtAuthMiddleware.php   # ← nuevo: reemplaza a SessionAuthMiddleware
    ├── Controllers/
    │   └── AuthController.php      # login, refresh, me, logout
    └── Models/
        ├── SecurityModel.php      # ← copiado tal cual
        ├── BusinessModel.php      # ← copiado tal cual
        ├── AuthModel.php          # ← recorte de loginModel
        └── BitacoraModel.php      # ← copiado tal cual
```

Comparado con el monolito **desaparecen** `Views/` y `Controllers/Movil/` con su `switch(modulo/accion)`; **aparecen** `Http/` (estándar de respuestas) y `Middlewares/JwtAuthMiddleware.php` (autenticación stateless).

---

## 5. 🔁 Cómo funciona una petición (ciclo de vida)

Ejemplo: la app móvil hace `POST /DIRPOLES-4/api/v1/auth/login`.

```
  App móvil                    DIRPOLES-4                          MySQL
 ┌──────────┐   1. POST /api/v1/auth/login
 │  (fetch/  │ ────────────────────────────►  index.php
 │  axios +  │        Body: {correo, password(RSA)}   │
 │ JSEncrypt)│                                        │ 2. CORS + headers JSON
 └──────────┘                                        │ 3. Composer autoload + .env
                                                     │ 4. Router::ejecutar()
                                                     │
                                                     │ 5. Middleware RateLimit ──────► rate_limits
                                                     │      (token bucket por IP)      (SELECT/UPDATE)
                                                     │ 6. Route handler: AuthController::login
                                                     │
                                                     │ 7. Request: parsea JSON, valida campos
                                                     │ 8. AuthModel::authenticate ──────────► empleado
                                                     │      (SELECT … WHERE correo=…)        + tipo_empleado
                                                     │      password_verify(bcrypt)
                                                     │ 9. JwtHandler: firma JWT (RS256) + crea refresh token ─► refresh_tokens
                                                     │ 10. BitacoraModel: registra el evento ────────────────► bitacora
                                                     │ 11. ApiResponse::success(200, {token, empleado})
  ◄──────────────────────────────────────────────────┘
   200 OK
   { "success": true, "data": { "token": "…", "refresh_token": "…", "user": {…} } }
```

Y para **cualquier endpoint protegido** (`GET /api/v1/...`), el flujo es igual pero antes del controlador corre `JwtAuthMiddleware`:

1. Lee el header `Authorization: Bearer <jwt>`.
2. `JwtHandler::validar` → verifica firma RS256 con la llave pública y la expiración.
3. Inyecta los datos del token en la petición (`$request->user`).
4. Si falla → `401` con el formato de error estándar. Nada más se ejecuta.

> **Diferencia clave con el monolito:** allí la "protección" era *sesión + JWT + redirección al login*. Aquí es *solo JWT + respuesta JSON*. Un token válido es la única llave de entrada; no hay estado de servidor que mantener.

---

## 6. 📐 Estándar de respuesta (el contrato de la API)

Toda respuesta, éxito o error, tiene **exactamente la misma forma**:

```jsonc
// ÉXITO
{
  "success": true,
  "message": "Has iniciado sesión correctamente.",
  "data": { "token": "eyJ...", "user": { "id_empleado": 1, "nombre": "Ana" } },
  "meta": { "timestamp": "2026-09-05T12:00:00Z" }
}

// ERROR
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",   // código legible y estable para el cliente
    "message": "Credenciales inválidas.",
    "details": []                    // ej: errores de campo en validaciones 422
  },
  "meta": { "timestamp": "2026-09-05T12:00:00Z" }
}
```

Mapa de errores del login (fase 1):

| HTTP | `code` | Cuándo |
|---|---|---|
| 400 | `INVALID_JSON` | El body no es JSON válido |
| 400 | `DECRYPTION_FAILED` | La contraseña no se pudo descifrar con RSA |
| 401 | `INVALID_CREDENTIALS` | Correo o contraseña incorrectos |
| 403 | `ACCOUNT_BLOCKED` | Cuenta con `estatus = 0` (bloqueo manual o por 3 intentos) |
| 422 | `VALIDATION_ERROR` | Falta `correo` o `password` |
| 429 | `RATE_LIMITED` | Demasiadas peticiones (header `Retry-After`) |
| 500 | `INTERNAL_ERROR` | Excepción no controlada (el detalle real va solo al log) |

> 🔒 Nivel universitario: en `500` **nunca** se filtra el mensaje de la excepción al cliente; se registra en `logs/php_errors.log` y se responde con un mensaje genérico + `code` estable.

---

## 7. 📋 Contrato de endpoints de la Fase 1 (Login)

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| GET | `/api/v1/health` | pública | Health check (monitoreo, CI/CD) |
| POST | `/api/v1/auth/login` | pública | Autentica y devuelve `token` + `refresh_token` |
| POST | `/api/v1/auth/refresh` | pública (usa refresh token) | Renueva el JWT |
| GET | `/api/v1/auth/me` | **JWT** | Datos del empleado autenticado |
| POST | `/api/v1/auth/logout` | JWT | Revoca el refresh token + bitácora |

Ejemplo con curl:

```bash
# 1. Health
curl http://localhost/DIRPOLES-4/api/v1/health

# 2. Login (password cifrada con RSA usando la llave pública, igual que el monolito)
curl -X POST http://localhost/DIRPOLES-4/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"correo":"empleado@uptaeb.edu","password":"<RSA_base64>"}'

# 3. Endpoint protegido
curl http://localhost/DIRPOLES-4/api/v1/auth/me \
  -H "Authorization: Bearer <token>"
```

**Sobre el cifrado RSA del login:** el monolito cifra la contraseña en el cliente con `login_public.pem` (JSEncrypt) y la descifra en el servidor. La API conserva ese mismo mecanismo para mantener compatibilidad con la app móvil que ya lo hace. Expondremos `GET /api/v1/auth/public-key` para que los clientes obtengan la llave pública sin duplicarla.

**Sobre las llaves:** si quieres aislar por completo DIRPOLES-4 del monolito, genera llaves nuevas:

```bash
cd DIRPOLES-4/app/Config/Keys
openssl genrsa -out jwt_private.pem 2048
openssl rsa -in jwt_private.pem -pubout -out jwt_public.pem
openssl genrsa -out login_private.pem 2048
openssl rsa -in login_private.pem -pubout -out login_public.pem
```

> Las llaves no se copian al repositorio (`.gitignore` ya excluye `*.pem`). Solo `index.php` las referencia.

---

## 8. 🚦 Fases de migración (roadmap)

| Fase | Alcance | Estado |
|---|---|---|
| **0** | Esqueleto + estándar de errores + Login (health, login, refresh, me, logout) | ✅ **Esta entrega** |
| 1 | `GET /api/v1/empleados`, `GET /api/v1/beneficiarios` (solo lectura, con JWT) | ⬜ |
| 2 | Escritura de beneficiarios y citas (`POST`, `PUT`, `DELETE` + permisos por rol) | ⬜ |
| 3 | Diagnósticos por área (psicología, medicina, TS, orientación, discapacidad) | ⬜ |
| 4 | Inventario, transporte, jornadas, referencias | ⬜ |
| 5 | Reportes (incluye puente al microservicio IA vía `X-API-Key`) | ⬜ |
| 6 | Paginación estándar (`?page=&per_page=`), filtros, HATEOAS ligero | ⬜ |

Cada fase añade `Controllers/` + `Models/` + rutas; el esqueleto (Core, Http, Middlewares) **no vuelve a tocarse**. Ese es el beneficio de hacerlo bien desde el inicio.

---

## 9. 🔀 Transición de la app móvil

Hoy la app apunta a `DIRPOLES_4/api/movil` con payloads `{modulo, accion}`. La migración recomendada:

1. **Hoy (fase 0):** la app puede apuntar ya a `POST /api/v1/auth/login` — la respuesta incluye `success/data` además de `estado/mensaje`, así que basta cambiar la URL y leer el nuevo campo.
2. **Después:** cada módulo de `Controllers/Movil/*` se reencarna en recursos REST (`/beneficiarios`, `/citas`, `/perfil`…). El cliente móvil actualiza sus llamadas módulo por módulo.
3. **Al final:** se elimina `api/movil` del monolito. El monolito queda solo para el navegador; toda API vive en DIRPOLES-4.

---

## 10. ✅ Checklist de esta entrega (fase 0)

- [x] Documentación de arquitectura (este archivo) y plan de migración
- [x] Front controller (`index.php`) + `.htaccess` + CORS JSON
- [x] `Core/`: Database, Router (adaptado a `/api/v1`), JwtHandler
- [x] `Models/`: SecurityModel, BusinessModel, AuthModel, BitacoraModel
- [x] `Http/`: ApiResponse (estándar de errores) + Request
- [x] `Middlewares/`: RateLimit (JSON) + JwtAuth (stateless)
- [x] `Controllers/AuthController.php` con login RSA + JWT + bloqueo por intentos
- [x] Rutas `/api/v1/...` registradas y verificadas con `php -l`
- [x] `docs/SQL_API.sql` con las tablas que la API espera en `dirpoles_security`
- [x] Bloqueo por 3 intentos fallidos migrado de `$_SESSION` a BD (`login_attempts`) — la API es stateless
