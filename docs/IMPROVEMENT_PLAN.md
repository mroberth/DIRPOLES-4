# DIRPOLES 4 — Plan de Mejoras: Backend + Frontend (sin romper MVC)

**Alcance:** refactorización organizacional del monolito híbrido. Sin frameworks, sin separar backend de frontend, sin capas arquitectónicas nuevas.
**Restricciones respetadas:** MVC como lo enseña la universidad; **sin clases en los Controllers** (siguen siendo funciones puras); clases solo permitidas en `app/Models/` y `app/Core/`; las vistas siguen renderizándose con PHP vía `require_once` + templates.

---

## 0. Resumen rápido (TL;DR)

**No necesitas Service/Repository** para tener un backend limpio. En MVC estricto, las responsabilidades que te faltan ya tienen un hogar natural:

| En tu proyecto con Service-Repository | Dónde vive en DIRPOLES (compatible con MVC) |
|---|---|
| Controller (el único que habla HTTP) | **Controller** — mismo rol, pero esbelto |
| Service (reglas de negocio) | **Model** — dentro de las acciones de `manejarAccion()` |
| Repository (SQL / PDO) | **Model** — métodos privados de la misma clase |
| Excepciones de dominio (`ValidationException`, ...) | `App\Core\ExcepcionApi` (nueva, **1 clase**) |
| Handler global de excepciones | `try/catch` alrededor de `Router::ejecutar()` en `index.php` |
| Formateador de respuestas | `App\Core\Respuesta` (nueva, **1 clase**) |

O sea: las "piezas que faltan" no son capas — son **convenciones + un puñado de clases Core**. Tus reglas ya permiten esta categoría de clase: `Router`, `Database`, `JwtHandler` y `MicroservicioIA` son exactamente eso hoy.

El plan completo añade **5 clases Core pequeñas estáticas**, no cambia ninguna carpeta, y migra **módulo por módulo** sin romper nada.

---

## 1. Reglas de oro (innegociables)

1. Mantener MVC: Controllers (funciones), Views (templates PHP), Models (clases).
2. **Sin clases en Controllers.** Nunca.
3. Sin Service/Repository/UseCase, sin carpetas nuevas tipo `app/Services/`.
4. Mantener el frontend renderizado en servidor (vistas + `app/Views/template/*`).
5. Mantener la doble base de datos (`security` / `business`) y la jerarquía `Database → BusinessModel/SecurityModel`.
6. Mantener `manejarAccion($action)` como punto de entrada del modelo (es un buen patrón de polimorfismo, defendible).

---

## 2. Diagnóstico — lo que el código muestra hoy

### 2.1 No existe un contrato de respuesta estándar
Verificado en el código:

- `loginModel` responde `['estado' => 'exito' | 'error' | 'bloqueado', ...]`
- `PermisosModel::guardar_permisos_lote()` responde `['exito' => true|false, 'mensaje' => ...]`
- `PermisosModel::obtener_permisos_por_rol()` responde `['exito' => ..., 'data' => ...]`
- Los controladores hacen `echo` de JSON ad-hoc (`estado`, `mensaje`, a veces `exito`, a veces `success`...)
- La API móvil responde con códigos HTTP reales + `estado`; el lado web responde 200 para casi todo.

**Costo:** cada consumidor (JS de las vistas, app móvil) tiene su propia lógica de parseo, y nada se puede validar ni manejar globalmente.

### 2.2 Hasta el despachador es inconsistente
`manejarAccion()` en todos lados, pero `loginModel` expone `manejador()` (llamado desde `loginController.php` y `Controllers/Movil/General.php`). Cosa pequeña, gran señal de falta de estándar.

### 2.3 Un bug vivo causado por la arquitectura de copy-paste
`PermisosModel::obtener_permisos_por_rol()` ejecuta `$this->conn->prepare($sql)`, pero `PermisosModel` conecta a la BD de **seguridad** (`$this->conn_security`). `$this->conn` es `null` → **error fatal** (`Call to a member function prepare() on null`). Además consulta `rol_modulo_permiso`, que es una tabla de seguridad, así que la conexión de negocio estaría mal de todas formas.

Este es exactamente el tipo de bug que deja de existir cuando las convenciones de "quién conecta a dónde / quién responde qué" se centralizan.

### 2.4 El boilerplate que describiste, cuantificado
Por cada función de acción, repetido en ~27 controladores:

- Bloque de verificación de permisos: ~8–10 líneas
- Bloque de bitácora: ~10–12 líneas
- Bloque de notificación: ~8–10 líneas

Eso son ~30 líneas × decenas de funciones = **miles de líneas duplicadas**. Si mañana necesitas agregar la IP a cada entrada de bitácora, tienes que tocar todos los archivos. Eso es, por definición, inviable de mantener.

### 2.5 Los modelos se tragan las excepciones
Patrón típico actual: `catch (Throwable $e) { error_log(...); return []; }`. El controlador recibe `[]` y no puede distinguir "error de validación" de "BD caída" de "no encontrado" — o sea, no puede mapear nada a HTTP ni a un mensaje útil para el usuario.

### 2.6 La API móvil
Un solo endpoint (`POST /api/movil`) con despacho por `switch` de `modulo` + `accion`. Funciona, pero:

- La verificación del token Bearer (`verificarTokenMovil()`) se repite a mano en cada `Movil/*.php`.
- No hay URLs de recursos (no existe `api/movil/beneficiarios`), todo es una acción en string.
- **Trampa latente:** `SessionAuthMiddleware` lista exactamente `'api/movil'` como ruta pública. El día que agregues `api/movil/loquesea` como subruta, el middleware empezará a exigir sesión PHP + cookie JWT a la app móvil (que no tiene ninguna de las dos) y todo se rompe en silencio.

### 2.7 Tu instinto es correcto
> "El controlador es quien debe saber HTTP."

Sí. Hoy esa frontera tiene fugas: los middlewares hacen `echo` de JSON, los controladores arman payloads a mano, los modelos devuelven "respuestas" en vez de datos o excepciones. El plan de abajo arregla exactamente eso.

---

## 3. La idea central: Core es tu punto de extensión

La universidad permite clases en **Model** y **Core**. Core ya *es* tu capa de framework (`Router`, `Database`, `JwtHandler`, `MicroservicioIA`). Agregamos 5 clases diminutas y estáticas a Core — sin capa nueva, sin violar MVC:

| Clase nueva | Responsabilidad | Líneas (aprox.) |
|---|---|---|
| `App\Core\ExcepcionApi` | El único tipo de excepción que lanzan los modelos (tipada por constantes, sin subclases) | ~60 |
| `App\Core\Respuesta` | El único lugar que emite JSON / redirects / detecta "¿este cliente quiere JSON?" | ~80 |
| `App\Core\Autorizacion` | Exigir permisos en una línea | ~25 |
| `App\Core\Bitacora` | Auditoría en una línea (nunca lanza) | ~25 |
| `App\Core\Notificador` | Notificaciones en una línea (nunca lanza) | ~25 |

Para el diagrama de clases: son clases de **framework/infraestructura**, la misma categoría que `Router` y `Database` — 5 filas, triviales de documentar. Si necesitas minimizar el conteo aún más: el mínimo de Fase 1 son 3 (`ExcepcionApi`, `Respuesta`, `Autorizacion`); `Bitacora`/`Notificador` pueden quedar inline hasta la Fase 2.

---

## 4. Propuesta 1 — Un sobre JSON para todo el sistema

Toda respuesta JSON (AJAX web, móvil, middlewares) usa **exactamente una sola forma**:

```json
{
  "exito": true,
  "mensaje": "Beneficiario registrado correctamente.",
  "datos": { "id": 42 }
}
```

En fallo: `"exito": false`, un `mensaje` legible por humanos, y el código de estado HTTP carga la semántica:

| Situación | HTTP |
|---|---|
| OK | 200 |
| Error de validación (cédula vacía, email malformado) | 422 |
| No autenticado (JWT malo/expirado) | 401 |
| Autenticado pero sin permiso | 403 |
| No encontrado | 404 |
| Duplicado (cédula/correo ya existe) | 409 |
| Cuenta bloqueada | 423 (o 403) |
| Error inesperado del servidor | 500 |
| Rate limit excedido | 429 (ya está implementado) |

`Respuesta` es la única clase que conoce la mecánica:

```php
namespace App\Core;

class Respuesta
{
    /** ¿Este cliente espera JSON? (AJAX, API móvil, endpoints *_data) */
    public static function clienteEsperaJson(): bool
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') return true;
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) return true;
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    public static function json(array $cuerpo, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function exito(string $mensaje = '', array $datos = [], int $codigo = 200): void
    {
        self::json(['exito' => true, 'mensaje' => $mensaje, 'datos' => $datos], $codigo);
    }

    public static function error(string $mensaje, int $codigo = 400, array $datos = []): void
    {
        self::json(['exito' => false, 'mensaje' => $mensaje, 'datos' => $datos], $codigo);
    }
}
```

Fíjate en la división del trabajo: **el controlador sigue decidiendo** *qué* responder (eso es conocimiento HTTP, su trabajo); `Respuesta` solo centraliza *cómo* se escribe al cable. El controlador sigue siendo el que habla HTTP — ahora con un vocabulario en vez de `echo` a mano alzada.

---

## 5. Propuesta 2 — Las excepciones fluyen hacia arriba, el HTTP ocurre en el borde

### 5.1 Una sola clase de excepción, tipada por constantes

En vez de 6 subclases (mejor para tu diagrama de clases), una sola clase con factories:

```php
namespace App\Core;

class ExcepcionApi extends \Exception
{
    public const VALIDACION    = 'validacion';      // 422
    public const AUTORIZACION  = 'autorizacion';    // 403
    public const NO_AUTH       = 'no_auth';         // 401
    public const NO_ENCONTRADO = 'no_encontrado';   // 404
    public const CONFLICTO     = 'conflicto';       // 409 (duplicados)
    public const NEGOCIO       = 'negocio';         // 400 (regla de negocio)
    public const INTERNA       = 'interna';         // 500

    private string $tipo;
    private int $http;
    private array $datos;

    public function __construct(string $mensaje, string $tipo = self::INTERNA,
                                int $http = 500, array $datos = [],
                                ?\Throwable $previa = null)
    {
        parent::__construct($mensaje, 0, $previa);
        $this->datos = $datos;
        $this->tipo = $tipo;
        $this->http = $http;
    }

    public function getTipo(): string { return $this->tipo; }
    public function getHttp(): int { return $this->http; }
    public function getDatos(): array { return $this->datos; }

    // ---- Factories (lo único que controladores y modelos llaman) ----
    public static function validacion(string $m, array $d = []): self
    { return new self($m, self::VALIDACION, 422, $d); }

    public static function autorizacion(string $m = 'No tiene permisos para esta acción.'): self
    { return new self($m, self::AUTORIZACION, 403); }

    public static function noAuth(string $m = 'Sesión expirada o inválida.'): self
    { return new self($m, self::NO_AUTH, 401); }

    public static function noEncontrado(string $m = 'Recurso no encontrado.'): self
    { return new self($m, self::NO_ENCONTRADO, 404); }

    public static function conflicto(string $m): self
    { return new self($m, self::CONFLICTO, 409); }

    public static function negocio(string $m): self
    { return new self($m, self::NEGOCIO, 400); }
}
```

### 5.2 El handler global — exactamente como tu sistema anterior

En `index.php`, se envuelve el router. Este es tu "handler en index.php" del proyecto Service-Repository, trasplantado:

```php
// index.php (bloque final)
try {
    \App\Core\Router::ejecutar();
} catch (\App\Core\ExcepcionApi $e) {
    error_log("[ExcepcionApi] {$e->getTipo()}: {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
    \App\Core\Respuesta::desdeExcepcion($e);
} catch (\Throwable $e) {
    error_log("[Fatal] {$e->getMessage()} en {$e->getFile()}:{$e->getLine()}");
    \App\Core\Respuesta::desdeExcepcion(
        new \App\Core\ExcepcionApi('Error interno del servidor.', ExcepcionApi::INTERNA, 500)
    );
}
```

Y `Respuesta::desdeExcepcion()` decide el formato:

```php
public static function desdeExcepcion(ExcepcionApi $e): void
{
    if (self::clienteEsperaJson()) {
        $cuerpo = ['exito' => false, 'mensaje' => $e->getMessage(), 'datos' => $e->getDatos()];
        if ($e->getTipo() === ExcepcionApi::NO_AUTH) {
            $cuerpo['redireccion'] = BASE_URL . 'login';
        }
        self::json($cuerpo, $e->getHttp());
    }

    // Navegación de página (no JSON):
    if ($e->getTipo() === ExcepcionApi::NO_AUTH) {
        $_SESSION['mensaje_redireccion'] = json_encode([
            'estado' => 'error', 'titulo' => 'Sesión', 'mensaje' => $e->getMessage()
        ]);
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    if ($e->getTipo() === ExcepcionApi::AUTORIZACION) {
        require_once BASE_PATH . 'app/Views/errors/access_denied.php';
        exit;
    }
    http_response_code($e->getHttp());
    // vista de error genérica o redirect con flash, según prefieras
    exit;
}
```

**Consecuencia:** los controladores dejan de tener bloques `try/catch` en casi todos lados, los modelos dejan de devolver arrays de error, y la decisión JSON-vs-página vive en UN solo lugar (la lógica que hoy está a medias dentro de `SessionAuthMiddleware::redirigirLogin()` y `RateLimitMiddleware` se muda a `Respuesta` y ambos middlewares la reutilizan).

---

## 6. Propuesta 3 — El boilerplate se vuelve una línea

```php
namespace App\Core;

class Autorizacion
{
    /** Lanza ExcepcionApi::autorizacion() si el rol no tiene el permiso. */
    public static function requerir(string $modulo, string $permiso): void
    {
        $m = new \App\Models\PermisosModel();
        $m->__set('Modulo', $modulo);
        $m->__set('Permiso', $permiso);
        $m->__set('Rol', $_SESSION['id_tipo_empleado'] ?? null);

        if (!$m->manejarAccion('Verificar')) {
            throw ExcepcionApi::autorizacion();
        }
    }
}

class Bitacora
{
    /** Nunca lanza: un fallo de auditoría no debe romper la operación. */
    public static function registrar(string $modulo, string $accion, string $descripcion): void
    {
        try {
            $b = new \App\Models\BitacoraModel();
            $b->__set('id_empleado', $_SESSION['id_empleado'] ?? null);
            $b->__set('modulo', $modulo);
            $b->__set('accion', $accion);
            $b->__set('descripcion', $descripcion);
            $b->__set('fecha', date('Y-m-d H:i:s'));
            $b->manejarAccion('registrar_bitacora');
        } catch (\Throwable $e) {
            error_log('[Bitacora] ' . $e->getMessage());
        }
    }
}

class Notificador
{
    public static function enviar(int $idEmpleado, string $titulo, string $mensaje): void
    {
        try {
            $n = new \App\Models\NotificacionesModel();
            $n->__set('id_empleado', $idEmpleado);
            $n->__set('titulo', $titulo);
            $n->__set('mensaje', $mensaje);
            $n->manejarAccion('registrar'); // ajustar al nombre real de la acción
        } catch (\Throwable $e) {
            error_log('[Notificador] ' . $e->getMessage());
        }
    }
}
```

Estas clases envuelven tus **modelos existentes** — son helpers de orquestación, no una capa de servicios. Misma categoría que `Router`.

---

## 7. Antes / Después (una función real de controlador)

### Antes (patrón actual típico, ~40 líneas)

```php
function beneficiario_registrar()
{
    // 1) Permiso (~10 líneas)
    $permisos = new PermisosModel();
    $permisos->__set('Modulo', 'Gestionar Beneficiarios');
    $permisos->__set('Permiso', 'crear');
    $permisos->__set('Rol', $_SESSION['id_tipo_empleado']);
    if (!$permisos->manejarAccion('Verificar')) {
        header('Content-Type: application/json');
        echo json_encode(['estado' => 'error', 'mensaje' => 'Sin permisos']);
        exit;
    }

    // 2) Operación (~10 líneas + try/catch interno del modelo)
    try {
        $modelo = new BeneficiarioModel();
        foreach ($_POST as $campo => $valor) { $modelo->__set($campo, $valor); }
        $resultado = $modelo->manejarAccion('registrar');
        if (!$resultado['exito']) { /* otro echo json... */ }
    } catch (Throwable $e) { /* otro echo json... */ }

    // 3) Bitácora (~12 líneas)
    // 4) Notificación (~10 líneas)
    // 5) echo json_encode(...) + exit
}
```

### Después (~10 líneas)

```php
function beneficiario_registrar()
{
    Autorizacion::requerir('Gestionar Beneficiarios', 'crear');

    $modelo = new BeneficiarioModel();
    foreach ($_POST as $campo => $valor) {
        $modelo->__set($campo, $valor);
    }
    $id = $modelo->manejarAccion('registrar'); // lanza ExcepcionApi si algo falla

    Bitacora::registrar('Beneficiarios', 'Registro', "Registró al beneficiario #{$id}");
    Notificador::enviar($_POST['id_empleado_asignado'] ?? 0, 'Nuevo beneficiario',
                        'Se registró un nuevo beneficiario en el sistema.');

    Respuesta::exito('Beneficiario registrado correctamente.', ['id' => $id]);
}
```

Y del lado del modelo:

```php
private function registrar(): int
{
    try {
        // ... INSERT con prepare/bindValue ...
        return (int) $this->conn->lastInsertId();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // llave única duplicada
            throw ExcepcionApi::conflicto('Ya existe un registro con esa cédula.');
        }
        error_log('[BeneficiarioModel::registrar] ' . $e->getMessage());
        throw new ExcepcionApi('No se pudo registrar el beneficiario.');
    }
}
```

**Regla de oro para todo el refactor:**
- Modelo: devuelve **datos** en éxito, **lanza `ExcepcionApi`** en fallo, nunca hace echo/headers/exit.
- Controlador: verifica permiso (1 línea), llama al modelo, bitácora/notificación (1 línea cada una), responde con `Respuesta` (1 línea). Sabe HTTP, nada más.
- Core: excepciones, formato, helpers.
- La decisión JSON-vs-página: solo en `Respuesta`.

---

## 8. Convenciones de la capa Modelo

1. **Un solo nombre de despachador:** renombrar `loginModel::manejador()` → `manejarAccion()` y actualizar sus 2 sitios de llamada (`loginController.php`, `Controllers/Movil/General.php`). Opcionalmente mantener `manejador()` como alias deprecado durante la migración.
2. **Éxito = devolver datos.** No `['exito' => true, 'data' => ...]` — simplemente el valor (int, array, lo que sea). La ausencia de una excepción ES la señal de éxito.
3. **Fallo = lanzar `ExcepcionApi`** con la factory correcta. Errores inesperados: loggear el detalle, lanzar un mensaje genérico (nunca filtrar SQL/stack al cliente).
4. **Mantener la validación de los mutadores `__set`** — es buena, es defendible en tu documentación, sigue usándola como capa de validación previa al INSERT.
5. **Arreglar de inmediato:** `PermisosModel::obtener_permisos_por_rol()` debe usar `$this->conn_security` (actualmente usa `$this->conn` = null → fatal).

---

## 9. API Móvil v2 — rutas de recursos, compatible hacia atrás

Mantén `POST /api/movil` funcionando exactamente como hoy (alias legado; la app no cambia el día uno). Luego agrega rutas de recursos reales que reutilizan los mismos procesadores `Movil/*.php`:

```php
// app/routes/api.movil.php

// ---- LEGADO: se mantiene tal cual ----
Router::post('api/movil', function () {
    load_controller('movilController.php');
    manejarPeticionMovil();
});

// ---- NUEVO: recursos REST ----
Router::post('api/movil/login', function () { load_controller('movilController.php'); movil_login(); });
Router::get('api/movil/me', ...);
Router::get('api/movil/beneficiarios', ...);
Router::post('api/movil/beneficiarios', ...);
Router::get('api/movil/citas', ...);
// etc.
```

Y un solo middleware de autenticación en vez de las llamadas manuales a `verificarTokenMovil()`:

```php
// app/Middlewares/MovilAuthMiddleware.php
class MovilAuthMiddleware
{
    public static function handle(): void
    {
        $ruta = /* misma limpieza de ruta que los otros middlewares */;

        // Legado: el endpoint viejo mantiene su verificación interna
        // (allí conviven login y acciones autenticadas)
        if ($ruta === 'api/movil') return;

        // El login es público
        if ($ruta === 'api/movil/login') return;

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth || !preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
            Respuesta::error('Token no proporcionado.', 401);
        }
        $jwt = new JwtHandler();
        $jwt->__set('token', $m[1]);
        if ($jwt->manejarAccion('validar')['estado'] !== 'exito') {
            Respuesta::error('Token inválido o expirado.', 401);
        }
    }
}

// routes.php (después de los middlewares globales):
Router::antes(['GET','POST'], 'api/movil.*', [MovilAuthMiddleware::class, 'handle']);
```

> `Router::antes()` soporta patrones regex, así que `'api/movil.*'` funciona con la implementación actual del `Router` (compila el patrón a `#^api/movil.*$#`).

**⚠️ Arreglo obligatorio complementario:** en `SessionAuthMiddleware`, la lista de rutas públicas es de coincidencia exacta (`in_array($rutaActual, $rutasPublicas)`), así que cualquier subruta nueva `api/movil/...` de repente exigiría sesión PHP. Cambiar la verificación a por prefijo:

```php
if (in_array($rutaActual, $rutasPublicas)
    || $rutaRelativa === ''
    || str_starts_with($rutaActual, 'api/movil')) {
    return;
}
```

**Beneficios para DIRPOLES_APP:** URLs reales por recurso, una sola implementación de auth, el mismo sobre que la web (`exito/mensaje/datos`), y la capa de red de la app solo necesita parsear una sola forma.

---

## 10. Frontend — mejorar sin separar nada

La arquitectura renderizada en servidor se queda. El cambio de mayor valor es un único cliente JS compartido:

```js
// app/Views/template/api.js (incluido desde script.php)
async function apiFetch(url, datos = null) {
    const opciones = {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    };
    if (datos) {
        opciones.method = 'POST';
        opciones.body = datos instanceof FormData ? datos : JSON.stringify(datos);
        if (!(datos instanceof FormData)) opciones.headers['Content-Type'] = 'application/json';
    }
    const r = await fetch(BASE_URL + url, opciones);
    const cuerpo = await r.json().catch(() => ({}));

    if (r.status === 401) {
        location.href = cuerpo.redireccion ?? (BASE_URL + 'login');
        throw new Error(cuerpo.mensaje ?? 'Sesión expirada');
    }
    if (r.status === 429) {
        Swal.fire('Demasiadas peticiones', cuerpo.mensaje ?? 'Espera un momento.', 'warning');
        throw new Error(cuerpo.mensaje ?? 'Rate limit');
    }
    if (!r.ok || cuerpo.exito === false) {
        Swal.fire('Error', cuerpo.mensaje ?? 'Error inesperado', 'error');
        throw new Error(cuerpo.mensaje ?? 'Error');
    }
    return cuerpo; // { exito, mensaje, datos }
}
```

El JS de cada módulo llama `apiFetch('beneficiario_registrar', formData)` y obtiene comportamiento consistente: 401 → redirect, 429 → aviso, error → toast, éxito → datos. Un solo archivo es dueño de toda esa lógica.

Ítems adicionales de bajo esfuerzo (opcionales, para después):

- **Mensajes flash:** ya tienes el patrón `$_SESSION['mensaje_redireccion']` — extraer un mini helper para que todas las vistas lo muestren igual.
- **CSRF (clase Core opcional, +1 al diagrama):** `Csrf::token()` / `Csrf::verificar()` inyectado en formularios y validado en los POST. Buen punto para la defensa; no es urgente.
- **Mover los orígenes CORS a `.env`:** la lista blanca en `index.php` está hardcodeada a puertos localhost — bien para desarrollo, debe ser configurable para producción.

---

## 11. Plan de migración (sin romper nada, módulo por módulo)

**Fase 0 — Convenciones.** Acordar el sobre + mapa de excepciones (este documento). Nada de código.

**Fase 1 — Solo Core.** Agregar `ExcepcionApi`, `Respuesta`, `Autorizacion`, `Bitacora`, `Notificador`; agregar el `try/catch` en `index.php`; alinear `SessionAuthMiddleware` + `RateLimitMiddleware` al sobre (durante un período de transición emitir también las claves viejas, ej. `estado` + `exito`, para no romper nada a mitad de vuelo). Arreglar el bug de `PermisosModel::obtener_permisos_por_rol()`. Renombrar `manejador()` → `manejarAccion()`.
👉 **No se cambia ningún módulo. El sistema se comporta idéntico.**

**Fase 2 — Piloto.** Refactorizar **un módulo de punta a punta** como plantilla: `beneficiario` (web) + `Movil/General.php` (login/me/logout móvil). Esto produce la "implementación de referencia" que copias para todo lo demás.

**Fase 3 — Despliegue.** Un módulo a la vez, cada uno en su propio commit:
`empleado → citas → diagnósticos (psicologia → medicina → orientacion → ts → discapacidad) → inventarios → referencias → jornadas → transporte → config/permisos/bitacora/backup → reportes → ayuda/perfil/horario`.

**Fase 4 — Extras opcionales.** CSRF, CORS a `.env`, convenciones de logs, políticas de rate limit a config.

**Definition of Done (por módulo):**
- [ ] Funciones de controlador ≤ ~15 líneas; cero `echo json_encode` a mano alzada; cero try/catch salvo deliberados.
- [ ] Modelo devuelve datos o lanza `ExcepcionApi`; sin arrays de error; sin `return []` en fallo.
- [ ] `Autorizacion::requerir(...)` como primera línea de cada acción protegida.
- [ ] `Bitacora::registrar(...)` + `Notificador::enviar(...)` donde aplique.
- [ ] Pase de regresión: login → visibilidad del sidebar → camino feliz del CRUD → permiso denegado muestra 403 → duplicado muestra 409 → los PDFs siguen generándose → entrada de bitácora creada → la app móvil sigue funcionando.

---

## 12. Cómo defender esto en la universidad

- **Sigue siendo MVC.** Vistas intactas; los Controllers siguen siendo archivos de funciones sin clases; los Models siguen siendo las únicas clases de datos. Core gana helpers de la *misma clase que ya tenía* (`Router`, `Database`).
- **Sin capas nuevas.** El diagrama crece en **5 clases de framework** (`ExcepcionApi`, `Respuesta`, `Autorizacion`, `Bitacora`, `Notificador`), 0 carpetas nuevas, 0 patrones con nombres rimbombantes.
- **La narrativa se escribe sola:** "El Modelo concentra reglas de negocio y acceso a datos y comunica fallos mediante excepciones tipadas; el Controlador es el único que decide la respuesta HTTP; el Core centraliza el manejo de excepciones y el formato de respuesta." — Eso es MVC de libro con fronteras limpias.

---

## 13. Bugs y victorias rápidas encontrados en la auditoría

1. 🔴 **`PermisosModel::obtener_permisos_por_rol()`** usa `$this->conn` (null → error fatal); debe ser `$this->conn_security`. Además consulta una tabla de seguridad, así que la BD de negocio estaría mal de todas formas.
2. 🟠 **Inconsistencia del despachador:** `manejarAccion()` vs `manejador()` (`loginModel`).
3. 🟠 **La verificación de rutas públicas de `SessionAuthMiddleware` es de coincidencia exacta** — agregar cualquier subruta `api/movil/*` romperá en silencio la app móvil (ver §9).
4. 🟡 **Restos de debug:** `showInicio()` en `loginController.php` escribe `print_r` de permisos en `error_log` en cada carga del dashboard.
5. 🟡 **Orígenes CORS hardcodeados** a puertos localhost en `index.php` — mover a `.env` antes de cualquier despliegue real.
6. 🟡 **El README sugiere `chmod 644` sobre las llaves PEM privadas** — debería ser `600` (solo el dueño) para llaves privadas.
7. 🟢 `config.yml` es un `{}` vacío y parece sin uso — candidata a eliminación (confirmar primero).
8. 🟢 `composer.json` declara `cboden/ratchet` + `react/*` (WebSockets) con cero uso — remover si las notificaciones en tiempo real no están en el roadmap, o implementarlas, pero no cargar dependencias muertas.

---

## 14. ¿Qué es tu arquitectura? — Nomenclatura (útil para la tesis)

### 14.1 Tu confusión, resuelta

Preguntaste: *"los endpoints responden JSON pero también hacemos `require_once` para renderizar vistas. ¿Es una API? ¿No es REST? ¿O cómo?"*

La respuesta corta: **no hay contradicción. Tu monolito tiene dos "puertas" distintas, y cada una es una cosa diferente.**

```
                        MONOLITO DIRPOLES (una sola app PHP)
                                      │
        ┌─────────────────────────────┴─────────────────────────────┐
        ▼                                                           ▼
  PUERTA 1: VISTAS                                            PUERTA 2: JSON
  Navegador pide una página                                   Cliente (app móvil, JS de
  → Controller → Model → el controller                        una vista) pide datos
  hace require_once de la vista                               → Controller → Model
  → la respuesta es **HTML**                                  → la respuesta es **JSON**

  Esto NO es una API.                                         Esto SÍ es una API
  Es una **aplicación web                                     (cada endpoint es una
  renderizada en servidor** —                                 interfaz para otro programa).
  MVC clásico de libro.                                       Pero **NO es REST** —
                                                              es una **API estilo RPC**
                                                              ("acción por parámetro").
```

### 14.2 Los términos, definidos sin humo

| Término | Qué significa de verdad | ¿Aplica a DIRPOLES? |
|---|---|---|
| **Aplicación web renderizada en servidor** (server-rendered) | El servidor arma el HTML completo y el navegador solo lo muestra. Las vistas son PHP. | ✅ Así funciona toda la parte web (vistas + templates). Es el MVC canónico: el Controller elige la vista y la renderiza. |
| **API** | *Interfaz de programación*: cualquier conjunto de endpoints que **otro programa** puede consumir de forma predecible. | ✅ `/api/movil` es una API. Cualquier endpoint nuevo que responda JSON también lo será. |
| **API REST** | Un *estilo* de API: recursos con URL propia (`/beneficiarios/15`), verbos HTTP con significado (GET/POST/PUT/DELETE), códigos de estado correctos, sin estado. | ❌ `/api/movil` no es REST: es una sola URL que decide por parámetro (`?accion=...`). Eso se llama **API estilo RPC** (Remote Procedure Call) o "por acciones". Y no es porque renderices vistas — es por el **diseño de esa URL**, no por las vistas. |
| **API RPC / por acciones** | Una URL que funciona como "llámame a esta función": `?accion=listar_beneficiarios`. Simple, y perfectamente legítima. | ✅ Es exactamente lo que hace hoy la app móvil. |
| **Monolito** | Una sola aplicación/servidor que contiene todo (UI + lógica + datos). | ✅ Eso eres tú, y no es una mala palabra: Laravel, Django y Rails hacen lo mismo. |

### 14.3 La analogía del restaurante

- El **comedor** son las vistas: la gente entra, se sienta y le sirven platos completos (HTML). Ahí no hay "API" — es el servicio directo al cliente humano.
- La **ventanilla de llevar** es `/api/movil`: otro negocio (la app móvil) pide cajas específicas (JSON) para llevar. Eso sí es una API.
- La **cocina** son los Modelos: la misma cocina alimenta al comedor y a la ventanilla.
- El restaurante no "deja de ser restaurante" por tener ventanilla, y la ventanilla no tiene que funcionar como el comedor. Solo necesitan **cocina compartida y reglas claras**.

### 14.4 Cómo nombrarlo en la documentación de la tesis

> *"DIRPOLES 4 es una aplicación web monolítica bajo el patrón MVC, con interfaz renderizada en servidor (PHP + Bootstrap), que expone una API JSON interna de estilo RPC (`/api/movil`) para su cliente móvil, autenticada por tokens Bearer."*

Esa frase es correcta, defendible y no promete nada que el código no cumpla. **No digas "API REST"** en la tesis: un jurado que sepa del tema te lo va a cobrar (`?accion=` no es REST). Si algún día migras la API móvil a rutas de recursos (§9 de este plan), *ahí* sí podrás decir "API RESTful".

### 14.5 La regla que te da la "estructura clara" que pides

La clave para que el híbrido no se sienta caótico es una **convención de frontera**:

1. **Toda ruta bajo `api/` responde SIEMPRE JSON** (éxito o error, con el sobre del §4). Nunca renderiza vistas, nunca redirige.
2. **Toda otra ruta responde HTML** (vista o redirección). Nunca escupe JSON suelto.
3. **El handler global de errores (§5) pregunta en qué puerta estámos** y formatea acorde:
   - Petición a `api/*` (o `Accept: application/json` / `X-Requested-With`) → error JSON del contrato.
   - Petición de página → log del error + redirección/página de error HTML (nunca un stack trace en pantalla).

Con esas tres reglas, "una API que también renderiza vistas" deja de ser ambiguo: cada petición tiene exactamente un formato de respuesta según su puerta, y eso lo decide el Core, no cada controlador a mano.

---

## 15. Qué tomar de `GUIA-BACKEND.MD` (EH-SYSTEM) y qué no

Leí la guía completa. Es un backend Controller → Service → Repository con excepciones tipadas. **La buena noticia: casi todo lo valioso de esa guía NO depende de las capas — son disciplina y clases Core, que tu MVC ya permite.**

### 15.1 Mapa de traducción: EH-SYSTEM → DIRPOLES

| Concepto en la guía (EH-SYSTEM) | Dónde vive en DIRPOLES (MVC híbrido) | ¿Se puede aplicar? |
|---|---|---|
| Controller (solo HTTP) | Controller (funciones) — solo HTTP **+ renderizar vista** (eso es MVC de libro) | ✅ Ya lo haces; falta la disciplina |
| Service (reglas de negocio) | **El Modelo** — la acción dentro de `manejarAccion()` | ✅ Absorbido por el Model |
| Repository (SQL puro) | **El Modelo** — métodos privados de la misma clase | ✅ Absorbido por el Model |
| `Response::jsonSuccess/jsonError` | `Core\Respuesta::exito()/error()` (§4 de este plan) | ✅ Directo |
| `DomainException` (código + estado + mensaje) | `Core\ExcepcionApi` (§5 de este plan) | ✅ Directo |
| `ErrorCodes` (constantes centrales) | Idéntico — clase Core con constantes | ✅ Directo |
| Handler global en `index.php` | Igual, **con una adaptación**: decidir JSON vs HTML según la puerta (§14.5) | ✅ Con ese ajuste |
| Doble catch estándar en el controller | Idéntico (`ExcepcionApi` primero, `Throwable` después) — o confiar en el handler global | ✅ Directo |
| Traits `ValidaParametros` / `PatronBusqueda` | Los Traits viven en Core y se usan **dentro de los Modelos** (los Models sí son clases — pueden `use` traits) | ✅ Directo y elegante |
| Contrato de paginación `{items, paginacion}` | Estándar de listados para la API v2 y los Data | ✅ Directo |
| Reglas de oro (validar → normalizar → procesar → persistir; `return` = feliz, `throw` = error; no castear antes de validar; `LIMIT/OFFSET` con `PARAM_INT`; SQLSTATE 23000 → 409; epoch Unix) | Reglas de **disciplina**, no de capas | ✅ Todas, tal cual |
| `#[RequierePermiso]` (atributos + Reflection) | Tu controllers son **funciones**, no clases — y aunque PHP permite atributos en funciones (`ReflectionFunction`), es magia innecesaria. Equivalente honesto: `Autorizacion::verificar('modulo','permiso')` como primera línea (§6) | ⚠️ Adaptado |
| Receta de módulo nuevo + checklist | Misma receta, sin las clases Repository/Service: el checklist vive en la convención del Model | ✅ Adaptado |
| Router con `{id}` + regex + params posicionales | Tu Router ya existe; mejorar hacia rutas de recursos es parte de la API v2 (§9) | 🟡 Opcional |
| **Carpetas `Services/` y `Repositories/`** | **Prohibidas por la universidad** — su contenido se absorbe en el Model | ❌ No |
| PSR-4 + namespaces en todo | Reescribiría cada archivo por un benefit cosmético; tu `load_controller()` funciona | ❌ No vale la pena |
| Separar frontend/backend (dos orígenes + CORS) | Tú decidiste no separar (y es razonable) | ❌ No |
| JWT por header `Authorization` en la web | Tu web usa sesión + JWT en cookie HttpOnly (más seguro para vistas) — ya es mejor que la guía para tu caso. El header Bearer queda para la app móvil | ❌ No cambiar |

### 15.2 Las 3 ideas de la guía que valen oro aquí

1. **"El error viaja hacia arriba; quien responde es el borde."** En EH-SYSTEM eso lo hace Service→Controller. En DIRPOLES es **Model→Controller→(Core)**: el Modelo lanza `ExcepcionApi` y no devuelve `['estado' => 'error']` disfrazado de éxito. Es el cambio de mentalidad #1 y no requiere capas nuevas.
2. **`ErrorCodes` como registro central.** Un solo lugar con las constantes (`BENEFICIARIO_YA_EXISTE`, `TOKEN_EXPIRADO`...). El JS de las vistas y la app móvil programan contra esos códigos, nunca contra los mensajes.
3. **El checklist al crear un módulo.** La guía termina con una lista de verificación; tu versión (sin capas extra) sería:

   - [ ] Acciones nuevas registradas en `manejarAccion()` (o `manejador()` unificado)
   - [ ] El Model **valida antes de normalizar**, y normaliza antes de persistir
   - [ ] El Model lanza `ExcepcionApi` con código de `ErrorCodes` — nunca `echo`, nunca `exit`, nunca arrays-estado
   - [ ] `LIMIT/OFFSET` con `PDO::PARAM_INT`; búsqueda LIKE con comodines escapados
   - [ ] El controller: lee HTTP → llama Model → `Respuesta::exito()` o `Respuesta::error($e)`; sin lógica de negocio
   - [ ] Bitácora + notificación con los helpers de una línea (§6), no copiando bloques
   - [ ] Permiso verificado con `Autorizacion::verificar()` (fail-closed)

### 15.3 Una advertencia honesta

La guía está escrita para un backend 100% JSON: por eso el handler global responde **siempre** JSON. En tu híbrido eso sería un bug (un error en una página devolvería JSON al navegador). La adaptación del §14.5 no es opcional — es lo que hace que todo lo demás de la guía encaje en tu monolito.

---

*Fin del plan. No se modificó nada del repositorio para producir este documento.*
