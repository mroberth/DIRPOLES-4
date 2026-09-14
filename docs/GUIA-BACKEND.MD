# GUÍA-BACKEND.MD — Manual completo del backend PHP (Controller → Repository)

> **Objetivo:** documento autocontenido que explica **todo** el backend de EH-SYSTEM,
> desde la respuesta JSON más superficial hasta la consulta SQL más profunda.
> Sirve como plantilla para ajustar otro backend con la misma arquitectura.
>
> Orden de lectura: **de arriba hacia abajo** (la petición HTTP entra por arriba y baja en cascada).

---

## Índice

1. [Visión general: qué hace el backend](#1-visión-general)
2. [El contrato JSON: la respuesta que el frontend espera](#2-el-contrato-json)
3. [El estándar de errores: DomainException + ErrorCodes](#3-el-estándar-de-errores)
4. [El Controller: higiene de transporte](#4-el-controller)
5. [El Service: lógica de negocio](#5-el-service)
6. [El Repository: SQL puro](#6-el-repository)
7. [Traits reutilizables: ValidaParametros y PatronBusqueda](#7-traits-reutilizables)
8. [El Router: directorio telefónico de URLs](#8-el-router)
9. [Middlewares: los porteros de la petición](#9-middlewares)
10. [CORS: autorización entre orígenes](#10-cors)
11. [PDO y prepared statements: seguridad contra inyección SQL](#11-pdo-y-prepared-statements)
12. [JWT: autenticación sin estado](#12-jwt)
13. [RBAC: roles, permisos y atributos PHP](#13-rbac)
14. [Receta: crear un módulo nuevo desde cero](#14-receta-módulo-nuevo)
15. [Reglas de oro del backend](#15-reglas-de-oro)
16. [Glosario](#16-glosario)

---

## 1. Visión general

El backend es una **API REST** que recibe peticiones HTTP, las procesa en 3 capas y responde JSON.

```
   Petición HTTP (JSON)
        │
        ▼
┌─────────────────────────────────────────────────┐
│  index.php                                      │
│  • Carga .env                                   │
│  • Handler global de excepciones                │
│  • CORS headers                                 │
│  • Router → dispatch()                          │
│    → RateLimitMiddleware → AuthMiddleware        │
│    → RolMiddleware → Controlador                │
└─────────────────────────────────────────────────┘
        │
        ▼
┌───────────────────┐
│    CONTROLLER     │  Conoce HTTP: lee la petición, responde JSON.
└───────────────────┘
        │ llama
        ▼
┌───────────────────┐
│     SERVICE       │  Conoce el NEGOCIO: reglas, validaciones, decisiones.
└───────────────────┘
        │ llama
        ▼
┌───────────────────┐
│   REPOSITORY      │  Conoce SQL: consultas a la base de datos.
└───────────────────┘
        │
        ▼
   Base de datos
```

**Tecnologías:**
- PHP nativo con POO (sin frameworks)
- Composer + autoloading PSR-4
- PDO para acceso a base de datos (MariaDB/MySQL)
- Firebase JWT para tokens
- Apache como servidor web

**Estructura de carpetas:**

```
EH-backend/
├── public/
│   └── index.php              ← Punto de entrada (entrypoint)
├── app/
│   ├── Config/
│   │   └── Conexion.php       ← Conexión PDO
│   ├── Core/
│   │   ├── Router.php         ← Directorio telefónico de URLs
│   │   ├── Response.php       ← Contrato JSON
│   │   ├── JWTHandler.php     ← Creación y verificación de tokens
│   │   ├── Attributes/
│   │   │   └── RequierePermiso.php  ← Etiqueta RBAC
│   │   ├── Exceptions/
│   │   │   ├── DomainException.php  ← Excepción con metadatos
│   │   │   └── ErrorCodes.php       ← Registro central de códigos
│   │   ├── Middleware/
│   │   │   ├── RateLimitMiddleware.php
│   │   │   ├── AuthMiddleware.php
│   │   │   └── RolMiddleware.php
│   │   └── Traits/
│   │       ├── ValidaParametros.php  ← Validación de paginación (Services)
│   │       └── PatronBusqueda.php    ← Mecánica LIKE (Repositories)
│   ├── Controllers/           ← Capa HTTP
│   ├── Services/              ← Capa de negocio
│   ├── Repositories/          ← Capa SQL
│   └── Routes/                ← Registro de rutas por módulo
└── vendor/                    ← Dependencias de Composer
```

**Autoloading PSR-4:** el namespace refleja la carpeta. `App\Services\UsuariosService` vive en `app/Services/UsuariosService.php`. Composer encuentra la clase automáticamente — no hay `require` a mano.

---

## 2. El contrato JSON

Todo el backend responde con **exactamente dos formatos**, definidos en `app/Core/Response.php`:

### 2.1. Respuesta de éxito

```json
{
  "success": true,
  "data": { ... }
}
```

El `data` puede ser cualquier cosa: un objeto, un array, un string, un booleano. El frontend siempre recibe el envoltorio `success + data`.

### 2.2. Respuesta de error

```json
{
  "success": false,
  "error": {
    "codigo": "ERROR_CODE",
    "estado": 400,
    "mensaje": "Mensaje entendible para el usuario"
  }
}
```

Los tres campos del error:
- **`codigo`** (string): identificador estable y programable. El frontend puede hacer `if (error.codigo === 'USERNAME_ALREADY_EXISTS')`. Nunca cambia.
- **`estado`** (int): el código HTTP real que acompaña la respuesta.
- **`mensaje`** (string): texto legible para el usuario final.

### 2.3. Código fuente: Response.php

```php
class Response {
    public static function jsonError(string $codigo, int $estado, string $mensajeReal) {
        http_response_code($estado);

        // En producción, el mensaje real se oculta (seguridad)
        $mensaje = (($_ENV['APP_DEBUG'] ?? 'false') === 'true')
            ? $mensajeReal
            : 'Ocurrió un error en el servidor.';

        echo json_encode([
            "success" => false,
            "error" => [
                "codigo" => $codigo,
                "estado" => $estado,
                "mensaje" => $mensaje
            ]
        ]);
        exit();  // ← Termina la ejecución después de responder
    }

    public static function jsonSuccess(mixed $data, int $estado = 200) {
        http_response_code($estado);
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
        exit();
    }
}
```

**Detalles importantes:**
- `http_response_code($estado)` fija el código HTTP antes del JSON.
- `exit()` después de cada respuesta: no debe ejecutarse nada más después.
- `APP_DEBUG` controla si el mensaje real se expone (desarrollo) o se oculta (producción).
- `jsonSuccess` acepta `mixed $data`: puede recibir un array, objeto, string, etc.

### 2.4. Códigos HTTP comunes

| Código | Significado | Cuándo se usa |
|--------|-------------|---------------|
| 200 | OK | Listar, crear, editar, eliminar con éxito |
| 400 | Bad Request | Campos vacíos, parámetros inválidos, JSON malformado |
| 401 | Unauthorized | Token faltante, corrupto o expirado |
| 403 | Forbidden | Autenticado pero sin permiso para esta acción |
| 404 | Not Found | Ruta inexistente o recurso no encontrado |
| 409 | Conflict | El nombre de usuario ya existe (duplicado) |
| 429 | Too Many Requests | Rate limiting (demasiadas peticiones) |
| 500 | Internal Server Error | Error técnico inesperado (bug, BD caída) |

---

## 3. El estándar de errores

### 3.1. El problema

Una excepción normal de PHP solo lleva un texto. El Controller tendría que "adivinar" qué código HTTP responder y qué string usar como código de error.

### 3.2. La solución: DomainException

`app/Core/Exceptions/DomainException.php` es una excepción que **transporta sus propios metadatos**:

```php
class DomainException extends Exception {
    public function __construct(
        protected readonly string $codigo,   // "USERNAME_ALREADY_EXISTS"
        protected readonly int $estado,     // 409
        string $mensaje                     // "El nombre de usuario ya está en uso."
    ) {
        parent::__construct($mensaje);
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getEstado(): int { return $this->estado; }
}
```

**Cómo se usa** (en el Service):

```php
throw new DomainException(
    ErrorCodes::USERNAME_YA_EXISTE,   // metadato: código estándar
    409,                               // metadato: estado HTTP
    'El nombre de usuario ya está en uso.'  // dato: mensaje legible
);
```

**El Controller ya no adivina:** vuelca los metadatos directo al contrato JSON.

### 3.3. ErrorCodes: el registro central

`app/Core/Exceptions/ErrorCodes.php` centraliza **todos** los códigos como constantes:

```php
final class ErrorCodes {
    // Autenticación
    public const USUARIO_NO_ENCONTRADO  = 'USER_NOT_FOUND';
    public const CREDENCIALES_INVALIDAS = 'INVALID_CREDENTIALS';
    public const USUARIO_INACTIVO       = 'USER_INACTIVE';
    public const JWT_SECRET_FALTANTE    = 'JWT_SECRET_MISSING';

    // Usuarios
    public const USERNAME_YA_EXISTE    = 'USERNAME_ALREADY_EXISTS';
    public const VALIDACION            = 'VALIDATION_ERROR';
    public const JSON_INVALIDO         = 'INVALID_JSON';
    public const METODO_NO_PERMITIDO   = 'METHOD_NOT_ALLOWED';

    // Genéricos
    public const ERROR_INTERNO = 'INTERNAL_SERVER_ERROR';

    // Token JWT
    public const TOKEN_FALTANTE = 'TOKEN_MISSING';
    public const TOKEN_INVALIDO = 'TOKEN_INVALID';
    public const TOKEN_EXPIRADO = 'TOKEN_EXPIRED';

    // Rate limiting
    public const RATE_LIMIT_EXCEDIDO = 'RATE_LIMIT_EXCEEDED';

    // Autorización
    public const ACCESO_DENEGADO     = 'ACCESS_DENIED';
    public const MODULO_NO_DISPONIBLE = 'MODULE_UNAVAILABLE';

    // Configuración — Roles
    public const ROL_NO_ENCONTRADO  = 'ROLE_NOT_FOUND';
    public const ROL_YA_EXISTE      = 'ROLE_ALREADY_EXISTS';
    public const ROL_EN_USO         = 'ROLE_IN_USE';

    // Configuración — Permisos
    public const PERMISO_NO_ENCONTRADO = 'PERMISSION_NOT_FOUND';
    public const PERMISO_YA_EXISTE     = 'PERMISSION_ALREADY_EXISTS';
    public const PERMISO_EN_USO        = 'PERMISSION_IN_USO';

    // Configuración — Módulos
    public const MODULO_NO_ENCONTRADO = 'MODULE_NOT_FOUND';
    public const MODULO_YA_EXISTE     = 'MODULE_ALREADY_EXISTS';
    public const MODULO_EN_USO        = 'MODULE_IN_USE';
}
```

**Regla absoluta:** todo código nuevo se agrega aquí. **Nunca** se escriben strings de error sueltos en el Service.

### 3.4. El flujo completo de un error

```
1. Service detecta un problema:
   throw new DomainException(ErrorCodes::USERNAME_YA_EXISTE, 409, '...')

2. La excepción viaja hacia arriba (stack unwinding):
   Repository  →  Service  →  Controller

3. Controller la atrapa:
   catch (DomainException $e) {
       Response::jsonError($e->getCodigo(), $e->getEstado(), $e->getMessage());
   }

4. Si nadie la atrapa, el handler global de index.php la captura:
   set_exception_handler(function($e) {
       if ($e instanceof DomainException) {
           Response::jsonError($e->getCodigo(), $e->getEstado(), $e->getMessage());
       }
   });
```

**Por qué esto cumple Open/Closed (OCP):** agregar un error nuevo = crear un `throw` nuevo. El `catch` del Controller **nunca cambia**.

### 3.5. El handler global en index.php

```php
set_exception_handler(function($e) {
    if ($e instanceof DomainException) {
        Response::jsonError($e->getCodigo(), $e->getEstado(), $e->getMessage());
        return;
    }

    // Cualquier otra excepción no capturada = 500 genérico
    $mensaje = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
        ? "Excepción no capturada: " . $e->getMessage()
        : "Ocurrió un error crítico en el servidor.";

    Response::jsonError("UNCAUGHT_EXCEPTION", 500, $mensaje);
});
```

### 3.6. Las dos ramas de errores

| Rama | Naturaleza | Ejemplo | Cómo se responde |
|------|-----------|---------|-----------------|
| **Esperada (negocio)** | El sistema la anticipa | "username ya existe", "contraseña incorrecta" | `DomainException` con sus metadatos |
| **Inesperada (técnica)** | Fallo que no debería pasar | `PDOException`, `TypeError` | 500 genérico + `error_log` |

**Regla para clasificar:** ¿el usuario puede corregirlo? → *esperada*. ¿No puede hacer nada? → *inesperada*.

---

## 4. El Controller

### 4.1. Qué es

El Controller es la **primera capa** que toca la petición HTTP. Su trabajo es:
1. Leer los datos de la petición (body, query params, parámetros de URL).
2. Limpiarlos (trim, json_decode, ?? null).
3. Delegar al Service.
4. Responder con `Response::jsonSuccess` o el doble `catch` estándar.

### 4.2. Lo que el Controller SÍ hace

| Acción | Ejemplo |
|--------|---------|
| Leer el body JSON | `$data = json_decode(file_get_contents('php://input'), true)` |
| Verificar que sea array | `if (!is_array($data))` → 400 |
| Leer query params | `$pagina = $_GET['pagina'] ?? null` |
| Limpiar strings | `trim($data['nombres'] ?? '')` |
| Llamar al Service | `$this->usuariosService->crear_usuario($data)` |
| Responder éxito | `Response::jsonSuccess($resultado)` |
| Capturar errores | `catch (DomainException $e)` + `catch (Throwable $e)` |

### 4.3. Lo que el Controller NUNCA hace

| Acción prohibida | Por qué |
|------------------|---------|
| Validar reglas de negocio | Eso es trabajo del Service |
| Castear tipos (`(int)`, `(bool)`) | `(int) null = 0` borra la distinción "no llegó" vs "llegó 0" |
| Usar `password_hash` | Eso es negocio → Service |
| Escribir SQL | Eso es Repository |
| Responder sin `Response` | Nada de `echo` directo o `exit()` sin pasar por Response |

### 4.4. El patrón estándar de un método CRUD

```php
class UsuariosController {
    private UsuariosService $usuariosService;

    public function __construct() {
        $this->usuariosService = new UsuariosService();
    }

    #[RequierePermiso('usuarios', 'crear')]
    public function crear_usuario() {
        // 1. LEER el body
        $data = json_decode(file_get_contents('php://input'), true);

        // 2. VERIFICAR que sea JSON válido
        if (!is_array($data)) {
            Response::jsonError(ErrorCodes::JSON_INVALIDO, 400,
                'El cuerpo de la solicitud no es un JSON válido.');
        }

        // 3. LIMPIAR datos (trim, ?? '') — SIN castear tipos
        $nombres  = trim($data['nombres'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');
        $username  = trim($data['username'] ?? '');
        $password  = $data['password'] ?? '';
        $id_rol    = $data['id_rol'] ?? null;
        $estado    = $data['estado'] ?? null;

        // 4. DELEGAR al Service dentro de un try-catch doble
        try {
            $usuario = $this->usuariosService->crear_usuario([
                'nombres'  => $nombres,
                'apellidos' => $apellidos,
                'username'  => $username,
                'password'  => $password,
                'id_rol'    => $id_rol,
                'estado'    => $estado,
            ]);

            Response::jsonSuccess($usuario);

        // 5a. Error de negocio → vuelvo los metadatos
        } catch (DomainException $e) {
            Response::jsonError($e->getCodigo(), $e->getEstado(), $e->getMessage());

        // 5b. Error inesperado → log + 500 genérico
        } catch (Throwable $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            Response::jsonError(ErrorCodes::ERROR_INTERNO, 500,
                'No se pudo crear el usuario.');
        }
    }
}
```

**Orden de los catch:** `DomainException` PRIMERO, `Throwable` después. Si se invierte, el genérico se traga al específico.

### 4.5. Rutas con parámetro `{id}`

Cuando la ruta es `PUT /v1/usuarios/{id}`, el `{id}` llega al método **posicionalmente**:

```php
// Ruta: PUT /v1/usuarios/{id}
// El Router pasa el valor como PRIMER argumento

#[RequierePermiso('usuarios', 'editar')]
public function actualizar_usuario(int $id_usuario) {
    // $id_usuario viene de la URL, NO del body
    $data = json_decode(file_get_contents('php://input'), true);
    // ...
}
```

**El nombre del parámetro del método NO tiene que coincidir con el de la ruta.** El Router usa `array_values` para pasar los parámetros por posición, no por nombre.

### 4.6. Métodos GET (sin body)

```php
#[RequierePermiso('usuarios', 'leer')]
public function consultar_usuarios() {
    // Los query params se leen de $_GET
    $pagina = $_GET['pagina'] ?? null;
    $limite = $_GET['limite'] ?? null;
    $buscar = $_GET['buscar'] ?? null;

    $resultado = $this->usuariosService->consultar_usuarios($pagina, $limite, $buscar);
    Response::jsonSuccess($resultado);
}
```

### 4.7. El atributo #[RequierePermiso]

Cada método del Controller que requiere autorización lleva una etiqueta:

```php
use App\Core\Attributes\RequierePermiso;

#[RequierePermiso('usuarios', 'leer')]     // ← lee
#[RequierePermiso('usuarios', 'crear')]    // ← crea
#[RequierePermiso('usuarios', 'editar')]   // ← edita
#[RequierePermiso('usuarios', 'eliminar')] // ← elimina
```

**Los valores deben coincidir EXACTOS** con `modulo.nombre` y `permiso.nombre` en la BD security.

**Sin etiqueta = fail-closed (403):** si una ruta nueva olvida la etiqueta, RolMiddleware la bloquea por defecto. Es deliberado: mejor bloquear de más que dejar abierta.

---

## 5. El Service

### 5.1. Qué es

El Service es la **capa de negocio**. Contiene:
- Toda la validación de datos.
- Las reglas de negocio.
- La traducción de errores técnicos a `DomainException`.
- La orquestación del Repository.

**Nunca conoce HTTP** ni `Response`. Solo lanza `DomainException` y devuelve arrays de éxito.

### 5.2. Lo que el Service SÍ hace

| Acción | Ejemplo |
|--------|---------|
| Validar campos obligatorios | `if ($data['nombres'] === '') throw ...` |
| Validar formatos | `is_numeric($data['id_rol'])` |
| Validar unicidad | `$this->repository->buscarPorUsername(...)` |
| Normalizar tipos DESPUÉS de validar | `$data['id_rol'] = (int) $data['id_rol']` |
| Hashear contraseñas | `password_hash($data['password'], PASSWORD_DEFAULT)` |
| Traducir PDOException a DomainException | `catch (\PDOException $e)` → SQLSTATE 23000 → 409 |
| Armar el contrato de paginación | `{ items, paginacion: { pagina, limite, total, total_paginas } }` |

### 5.3. Lo que el Service NUNCA hace

| Acción prohibida | Por qué |
|------------------|---------|
| Usar `Response` o `http_response_code` | No conoce HTTP |
| Escribir SQL | Eso es Repository |
| Recibir datos de `$_GET` o `$_POST` | Eso es Controller |
| Hashear la contraseña dos veces | Doble hash rompe el login |

### 5.4. Ejemplo completo: crear un registro

```php
class UsuariosService {
    use \App\Core\Traits\ValidaParametros;  // normalizarEntero + normalizarBusqueda

    private UsuariosRepository $usuariosRepository;

    public function __construct() {
        $this->usuariosRepository = new UsuariosRepository();
    }

    public function crear_usuario(array $data): array {
        // 1. VALIDAR campos obligatorios
        if ($data['nombres'] === '' || $data['apellidos'] === '' ||
            $data['username'] === '' || $data['password'] === '' ||
            $data['id_rol'] === null || $data['estado'] === null) {
            throw new DomainException(
                ErrorCodes::VALIDACION, 400,
                'Todos los campos son obligatorios.'
            );
        }

        // 1b. Normalizar tipos DESPUÉS de validar presencia
        if (!is_numeric($data['id_rol'])) {
            throw new DomainException(
                ErrorCodes::VALIDACION, 400,
                'El rol debe ser un número válido.'
            );
        }
        $data['id_rol'] = (int) $data['id_rol'];
        $data['estado'] = filter_var($data['estado'], FILTER_VALIDATE_BOOLEAN);

        // 2. VALIDAR unicidad (defensa / UX amable)
        $usuarioExistente = $this->usuariosRepository->buscarPorUsername($data['username']);
        if ($usuarioExistente) {
            throw new DomainException(
                ErrorCodes::USERNAME_YA_EXISTE, 409,
                'El nombre de usuario ya está en uso.'
            );
        }

        // 3. PROCESAR datos de negocio (hash)
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // 4. PERSISTIR en la BD
        try {
            $usuario = $this->usuariosRepository->crear_usuario($data);
        } catch (\PDOException $e) {
            // SQLSTATE 23000 = violación de constraint (duplicado en carrera)
            if ($e->getCode() === '23000') {
                throw new DomainException(
                    ErrorCodes::USERNAME_YA_EXISTE, 409,
                    'El nombre de usuario ya está en uso.'
                );
            }
            throw $e;  // cualquier otro error sigue viajando
        }

        if ($usuario === null) {
            throw new DomainException(
                ErrorCodes::ERROR_INTERNO, 500,
                'No se pudo crear el usuario.'
            );
        }

        return $usuario;
    }
}
```

**El orden es siempre:** validar → normalizar → procesar → persistir → devolver.

### 5.5. Ejemplo: listado paginado con búsqueda

```php
public function consultar_usuarios(mixed $pagina, mixed $limite, mixed $buscar): array {
    // 1. Normalizar y validar parámetros de paginación (fail-closed)
    $pagina = $this->normalizarEntero($pagina, 1, 1, null, 'pagina');
    $limite = $this->normalizarEntero($limite, 20, 1, 100, 'limite');
    $buscar = $this->normalizarBusqueda($buscar);

    // 2. Contar total de registros (para calcular páginas)
    $total = $this->usuariosRepository->contar_usuarios($buscar);

    // 3. Traer una página de datos
    $items = $this->usuariosRepository->consultar_usuarios($pagina, $limite, $buscar);

    // 4. Armar el contrato
    return [
        'items' => $items,
        'paginacion' => [
            'pagina'        => $pagina,
            'limite'        => $limite,
            'total'         => $total,
            'total_paginas' => (int) ceil($total / $limite),
        ],
    ];
}
```

### 5.6. El trait ValidaParametros

El Service lo usa con `use \App\Core\Traits\ValidaParametros;` para obtener dos métodos:

**`normalizarEntero($valor, $defecto, $minimo, $maximo, $campo)`:**
- `null` o vacío → devuelve el `$defecto`.
- No es un entero real (`FILTER_VALIDATE_INT`) → lanza 400.
- Fuera de rango → lanza 400.
- Ejemplo: `?limite=1.5` → 400 (rechaza decimales). `?limite=1e2` → 400 (rechaza notación científica).

**`normalizarBusqueda($valor)`:**
- `null` o vacío → `null` (sin filtro).
- No es texto → lanza 400.
- Más de 100 caracteres → lanza 400.
- Devuelve el texto recortado o `null`.

**Por qué `FILTER_VALIDATE_INT` y no `(int)`:**
- `(int) "1.5"` → `1` (trunca silenciosamente).
- `(int) "1e2"` → `100` (acepta notación científica).
- `FILTER_VALIDATE_INT` rechaza todo lo que no sea un entero puro.

---

## 6. El Repository

### 6.1. Qué es

El Repository es la **capa SQL**. Su único trabajo es hablar con la base de datos usando PDO prepared statements.

### 6.2. Lo que el Repository SÍ hace

| Acción | Ejemplo |
|--------|---------|
| Conectarse a la BD | `$this->conn = Conexion::obtenerConexion('security')` |
| Preparar y ejecutar SQL | `$stmt = $this->conn->prepare($query)` |
| Vincular parámetros | `$stmt->bindParam(':nombre', $valor)` |
| Ejecutar | `$stmt->execute()` |
| Devolver resultados | `$stmt->fetchAll(PDO::FETCH_ASSOC)` |

### 6.3. Lo que el Repository NUNCA hace

| Acción prohibida | Por qué |
|------------------|---------|
| Usar `Response` o `http_response_code` | No conoce HTTP |
| Tener `try-catch` | Las `PDOException` viajan al Service |
| Usar `DomainException` o `ErrorCodes` | No conoce el negocio |
| Validar datos de entrada | Eso es Service |
| Hashear contraseñas | Eso es Service |

### 6.4. Ejemplo completo: CRUD de una entidad

```php
class UsuariosRepository {
    use \App\Core\Traits\PatronBusqueda;  // patronBusqueda + vincularBusqueda

    private readonly PDO $conn;

    public function __construct() {
        $this->conn = Conexion::obtenerConexion('security');
    }

    // ─── CONDICIÓN DE BÚSQUEDA (una sola fuente de verdad) ──────────

    private const CONDICION_BUSCAR =
        "nombres LIKE :b_nombres OR apellidos LIKE :b_apellidos OR username LIKE :b_username";

    // ─── CONTAR (para la paginación) ────────────────────────────────

    public function contar_usuarios(?string $buscar = null): int {
        $query = "SELECT COUNT(*) FROM usuario";

        if ($buscar !== null) {
            $query .= " WHERE " . self::CONDICION_BUSCAR;
        }

        $stmt = $this->conn->prepare($query);

        if ($buscar !== null) {
            $this->vincularBusqueda($stmt, $buscar,
                [':b_nombres', ':b_apellidos', ':b_username']);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // ─── CONSULTAR (traer una página) ───────────────────────────────

    public function consultar_usuarios(int $pagina, int $limite, ?string $buscar = null): array {
        $offset = ($pagina - 1) * $limite;

        $query = "SELECT id_usuario, nombres, apellidos, username,
                         usuario.id_rol, rol.nombre as nombre_rol, estado
                  FROM usuario
                  INNER JOIN rol ON usuario.id_rol = rol.id_rol";

        if ($buscar !== null) {
            $query .= " WHERE " . self::CONDICION_BUSCAR;
        }

        $query .= " ORDER BY id_usuario DESC LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($buscar !== null) {
            $this->vincularBusqueda($stmt, $buscar,
                [':b_nombres', ':b_apellidos', ':b_username']);
        }

        // LIMIT/OFFSET SIEMPRE con PARAM_INT (nunca concatenado)
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── BUSCAR POR CAMPO (para unicidad) ───────────────────────────

    public function buscarPorUsername(string $username, ?int $exceptId = null): ?array {
        $query = "SELECT id_usuario FROM usuario WHERE username = :username";

        if ($exceptId !== null) {
            $query .= " AND id_usuario <> :except_id";
        }

        $query .= " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);

        if ($exceptId !== null) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ?: null;
    }

    // ─── CREAR ──────────────────────────────────────────────────────

    public function crear_usuario(array $data): ?array {
        $query = "INSERT INTO usuario (nombres, apellidos, username, password, id_rol, estado)
                  VALUES (:nombres, :apellidos, :username, :password, :id_rol, :estado)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindParam(':id_rol', $data['id_rol'], PDO::PARAM_INT);
        $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_BOOL);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            return [
                'id_usuario' => (int) $this->conn->lastInsertId(),
                'nombres'    => $data['nombres'],
                'apellidos'  => $data['apellidos'],
                'username'   => $data['username'],
                'id_rol'     => $data['id_rol'],
                'estado'     => $data['estado'],
            ];
        }

        return null;
    }

    // ─── ACTUALIZAR ─────────────────────────────────────────────────

    public function actualizar_usuario(array $data): ?array {
        $query = "UPDATE usuario
                  SET nombres = :nombres, apellidos = :apellidos,
                      username = :username, id_rol = :id_rol, estado = :estado";

        if (!empty($data['password'])) {
            $query .= ", password = :password";
        }

        $query .= " WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombres', $data['nombres']);
        $stmt->bindParam(':apellidos', $data['apellidos']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':id_rol', $data['id_rol'], PDO::PARAM_INT);
        $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_BOOL);
        $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);

        if (!empty($data['password'])) {
            $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        }

        $stmt->execute();

        // rowCount() devuelve 0 si NINGÚN valor cambió, así que
        // la existencia se verifica aparte con un SELECT 1
        $existe = $this->conn->prepare(
            "SELECT 1 FROM usuario WHERE id_usuario = :id_usuario LIMIT 1"
        );
        $existe->bindValue(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
        $existe->execute();

        if ($existe->fetchColumn()) {
            return [
                'id_usuario' => (int) $data['id_usuario'],
                'nombres'    => $data['nombres'],
                'apellidos'  => $data['apellidos'],
                'username'   => $data['username'],
                'id_rol'     => (int) $data['id_rol'],
                'estado'     => (bool) $data['estado'],
            ];
        }

        return null;
    }

    // ─── ELIMINAR ───────────────────────────────────────────────────

    public function eliminar_usuario(int $id_usuario): bool {
        $query = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
```

### 6.5. Detalles críticos del Repository

**`LIMIT` y `OFFSET` SIEMPRE con `PDO::PARAM_INT`:**
```php
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);  // ✅ seguro
// $query .= " LIMIT $limite";                          // ❌ inyección SQL
```

**`rowCount()` en UPDATE:** devuelve 0 si ningún valor cambió (editar y guardar los mismos datos). Por eso la existencia se verifica con un `SELECT 1` aparte.

**`CONDICION_BUSCAR` como constante:** se usa en el `COUNT` y el `SELECT`. Definirla una sola vez garantiza que el total de páginas nunca discrepe con las filas mostradas.

**Sin `try-catch`:** las `PDOException` viajan solas hasta el Service, que las traduce a `DomainException`.

---

## 7. Traits reutilizables

### 7.1. Qué es un trait

Un trait es un conjunto de métodos reutilizable que una clase puede "importarse" con `use`. PHP "pega" los métodos dentro de la clase a nivel de compilación.

```php
class UsuariosService {
    use \App\Core\Traits\ValidaParametros;  // ← importa normalizarEntero + normalizarBusqueda
}
```

**La diferencia con herencia:** la herencia responde "¿qué **eres**?"; el trait responde "¿qué **tienes**?". Un `UsuariosService` **no es** un `ValidaParametros` — **tiene** la habilidad de validar parámetros.

### 7.2. ValidaParametros (para Services)

```php
trait ValidaParametros {
    private function normalizarEntero(
        mixed $valor, int $defecto, int $minimo, ?int $maximo, string $campo
    ): int {
        if ($valor === null || $valor === '') {
            return $defecto;
        }

        $filtrado = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $minimo, 'max_range' => $maximo ?? PHP_INT_MAX],
        ]);

        if ($filtrado === false) {
            $rango = $maximo === null
                ? "mayor o igual a {$minimo}"
                : "entre {$minimo} y {$maximo}";
            throw new DomainException(
                ErrorCodes::VALIDACION, 400,
                "El parámetro '{$campo}' debe ser un número entero {$rango}."
            );
        }

        return $filtrado;
    }

    private function normalizarBusqueda(mixed $valor): ?string {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (!is_string($valor)) {
            throw new DomainException(
                ErrorCodes::VALIDACION, 400,
                "El parámetro 'buscar' debe ser texto."
            );
        }

        $valor = trim($valor);
        $longitud = function_exists('mb_strlen') ? mb_strlen($valor) : strlen($valor);

        if ($longitud > 100) {
            throw new DomainException(
                ErrorCodes::VALIDACION, 400,
                "El parámetro 'buscar' no puede superar los 100 caracteres."
            );
        }

        return $valor === '' ? null : $valor;
    }
}
```

**Uso típico en el Service:**
```php
$pagina = $this->normalizarEntero($pagina, 1, 1, null, 'pagina');     // default 1, sin tope
$limite = $this->normalizarEntero($limite, 20, 1, 100, 'limite');    // default 20, tope 100
$buscar = $this->normalizarBusqueda($buscar);                         // texto limpio o null
```

### 7.3. PatronBusqueda (para Repositories)

```php
trait PatronBusqueda {
    private function patronBusqueda(string $texto): string {
        return '%' . addcslashes($texto, '%_\\') . '%';
    }

    private function vincularBusqueda(
        \PDOStatement $stmt, string $buscar, array $placeholders
    ): void {
        $patron = $this->patronBusqueda($buscar);
        foreach ($placeholders as $placeholder) {
            $stmt->bindValue($placeholder, $patron);
        }
    }
}
```

**Por qué un placeholder por columna:**

```sql
WHERE nombres LIKE :b_nombres
   OR apellidos LIKE :b_apellidos
   OR username LIKE :b_username
```

PDO nativo (sin emulación) rechaza repetir el mismo placeholder (`:buscar × 3`) con el error `HY093 Invalid parameter number`. Por eso cada columna tiene su propio placeholder, y `vincularBusqueda()` los llena todos con el mismo patrón.

**Por qué se escapan `%` y `_`:** en SQL, `%` significa "cualquier texto" y `_` significa "un carácter". Si el usuario escribe `100%` y no se escapa, buscaría "todo lo que empiece por 100". Con `addcslashes`, se busca el **literal** `100%`.

---

## 8. El Router

### 8.1. Qué es

El Router es el **directorio telefónico** del backend: recibe la URL + método HTTP y decide qué controlador y qué método atienden la petición.

### 8.2. Cómo se registran las rutas

Cada módulo tiene su archivo en `app/Routes/`:

```php
// app/Routes/usuarios.php
use App\Controllers\UsuariosController;

$router->get("/v1/usuarios", [UsuariosController::class, "consultar_usuarios"]);
$router->get("/v1/usuarios/validar-username", [UsuariosController::class, "validar_username"]);
$router->post("/v1/usuarios", [UsuariosController::class, "crear_usuario"]);
$router->put("/v1/usuarios/{id}", [UsuariosController::class, "actualizar_usuario"]);
$router->delete("/v1/usuarios/{id}", [UsuariosController::class, "eliminar_usuario"]);
```

El `[Clase, método]` es un *callable*: "ejecuta este método de esta clase".

### 8.3. Rutas exactas vs rutas con parámetro

- **Exacta:** `/v1/usuarios` → coincide solo con esa URL literal.
- **Con parámetro:** `/v1/usuarios/{id}` → el `{id}` se compila a una regex que captura cualquier valor.

**El orden importa:** primero se busca coincidencia **exacta** y, si no hay, se prueban los patrones. Así `/v1/usuarios/validar-username` (exacta) **no cae** en `/v1/usuarios/{id}`.

### 8.4. El flujo de dispatch

```php
public function dispatch(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');

    // 1. Buscar ruta exacta primero
    $handler = $this->rutasExactas[$method][$uri] ?? null;

    // 2. Si no hay, probar patrones {id}
    if ($handler === null) {
        foreach ($this->rutasPatron[$method] ?? [] as $ruta) {
            if (preg_match($ruta['regex'], $uri, $coincidencias)) {
                $handler = $ruta['handler'];
                // Extraer parámetros nombrados
                foreach ($coincidencias as $clave => $valor) {
                    if (is_string($clave)) {
                        $params[$clave] = $valor;
                    }
                }
                break;
            }
        }
    }

    // 3. Si no se encontró → 404
    if ($handler === null) {
        Response::jsonError("ROUTE_NOT_FOUND", 404, "La ruta no existe.");
    }

    // 4. Validar que la clase/método existan (antes de middlewares)
    // 5. Ejecutar cadena de middlewares
    // 6. Ejecutar el controlador pasando parámetros posicionalmente
    $controllerInstance->$action(...array_values($params));
}
```

### 8.5. Auto-carga de rutas

`app/Routes/rutas.php` carga automáticamente todos los archivos `.php` del directorio:

```php
foreach (glob(BASE_PATH . '/app/Routes/*.php') as $archivoRuta) {
    if (basename($archivoRuta) === 'rutas.php') continue;
    require_once $archivoRuta;
}
```

**Crear un archivo nuevo en `app/Routes/` es suficiente:** se carga sin tocar nada más.

---

## 9. Middlewares

### 9.1. Qué es un middleware

Un middleware es una pieza que se interpone **entre** la ruta y el controlador para **filtrar** la petición. Puede dejarla pasar o rechazarla.

### 9.2. La cadena de middlewares

```
Petición → RateLimit → Auth → Rol → Controlador
```

| # | Middleware | Pregunta | Si falla |
|---|-----------|----------|----------|
| 1 | `RateLimitMiddleware` | ¿No estás abusando? (rutas públicas) | 429 `RATE_LIMIT_EXCEEDED` |
| 2 | `AuthMiddleware` | ¿Traes un JWT válido? | 401 `TOKEN_MISSING/INVALID/EXPIRED` |
| 3 | `RolMiddleware` | ¿Tu rol tiene permiso para esto? | 403 `ACCESO_DENEGADO` o `MODULE_UNAVAILABLE` |

**Los middlewares NO responden JSON.** Solo lanzan `DomainException`. Quien responde es el handler global de `index.php`.

### 9.3. RateLimitMiddleware

Limita peticiones a endpoints **públicos** (login). Algoritmo "token bucket":
- Capacidad: 5 fichas (ráfaga).
- Recarga: 1 ficha cada 60 segundos.
- Balde vacío → 429.
- Clave: IP del cliente (en rutas públicas no hay token).
- Atomicidad: transacción + `SELECT ... FOR UPDATE`.
- Epoch Unix: `UNIX_TIMESTAMP()` en BD, `time()` en PHP (nunca strings de fecha).
- Fail-open: si la BD falla, deja pasar + `error_log`.

### 9.4. AuthMiddleware

Verifica el token JWT:

```php
class AuthMiddleware {
    public const RUTAS_PUBLICAS = [
        'POST' => ['/v1/auth/login'],
    ];

    public static function proteger(string $method, string $uri): void {
        // 1. Ruta pública → pasar sin token
        if (self::esRutaPublica($method, $uri)) {
            return;
        }

        // 2. Extraer header Authorization
        $header = self::obtenerHeaderAuthorization();

        if ($header === null || !preg_match('/^Bearer\s+(.+)$/i', $header, $coincidencias)) {
            throw new DomainException(ErrorCodes::TOKEN_FALTANTE, 401,
                'No se proporcionó un token de autenticación.');
        }

        // 3. Verificar el token (traduciendo errores de Firebase)
        try {
            self::$usuario = JWTHandler::verificarToken($coincidencias[1]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new DomainException(ErrorCodes::TOKEN_EXPIRADO, 401,
                'El token ha expirado.');
        } catch (\Throwable $e) {
            throw new DomainException(ErrorCodes::TOKEN_INVALIDO, 401,
                'El token es inválido.');
        }
    }
}
```

**Detalle:** `self::$usuario` guarda el token decodificado para que RolMiddleware lo lea sin re-decodificar.

### 9.5. RolMiddleware

Lee la etiqueta `#[RequierePermiso]` del método y verifica en la BD:

```php
class RolMiddleware {
    public const RUTAS_SOLO_AUTENTICACION = [
        'POST' => ['/v1/auth/renovar'],
    ];

    public static function autorizar(string $method, string $uri, array $handler): void {
        // 1. Rutas públicas → sin token no hay rol
        if (AuthMiddleware::esRutaPublica($method, $uri)) return;

        // 2. Rutas de sesión (renovar) → JWT basta, sin RBAC
        if (self::esRutaSoloAutenticacion($method, $uri)) return;

        // 3. Fail-closed: handler sin clase → 403
        if (!is_array($handler)) {
            throw new DomainException(ErrorCodes::ACCESO_DENEGADO, 403, '...');
        }

        // 4. Leer etiqueta con Reflection
        [$clase, $metodo] = $handler;
        $atributos = (new ReflectionMethod($clase, $metodo))
            ->getAttributes(RequierePermiso::class);

        // 5. Fail-closed: sin etiqueta → 403
        if (empty($atributos)) {
            throw new DomainException(ErrorCodes::ACCESO_DENEGADO, 403, '...');
        }

        $requerido = $atributos[0]->newInstance();

        // 6. Verificar estado del módulo (solo 'Activo' autoriza)
        // 7. Verificar permiso en rol_modulo_permiso (JOIN modulo + permiso)
        // 8. Si no existe → 403 ACCESO_DENEGADO
    }
}
```

**RUTAS_SOLO_AUTENTICACION:** rutas como `renovar` que exigen JWT válido pero **ningún permiso de módulo**. Cualquier usuario autenticado puede renovar su propia sesión.

---

## 10. CORS

### 10.1. Qué es

CORS (Cross-Origin Resource Sharing) es una regla de seguridad del navegador: una página del origen A **no puede** pedir datos a un origen B a menos que B lo autorice.

En EH-SYSTEM hay dos orígenes distintos:
- `http://eh-system.test` (frontend)
- `http://api.eh-system.test` (API)

### 10.2. Cómo se configura

En `public/index.php`:

```php
$origen = $_SERVER['HTTP_ORIGIN'] ?? '';
$origenesPermitidos = array_filter([
    'http://eh-system.test',
    $_ENV['APP_URL'] ?? null,
]);

if (in_array($origen, $origenesPermitidos)) {
    header("Access-Control-Allow-Origin: $origen");
    header('Access-Control-Allow-Credentials: true');
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, Accept, X-Requested-With");
header("Access-Control-Max-Age: 86400");

// Responder 200 a las peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

El navegador hace una petición `OPTIONS` previa (preflight) antes de un `PUT`/`DELETE`/`POST` con JSON. El backend debe responder 200 a esa petición para que la real pase.

---

## 11. PDO y prepared statements

### 11.1. PDO

PDO (PHP Data Objects) es la extensión de PHP para hablar con bases de datos. `app/Config/Conexion.php` entrega una conexión PDO lista:

```php
$conn = Conexion::obtenerConexion('security');  // BD de seguridad
```

### 11.2. Prepared statements

Separan **la estructura** de la consulta de **los datos**:

```php
$stmt = $this->conn->prepare("SELECT * FROM usuario WHERE username = :username");
$stmt->bindParam(':username', $username);  // los datos van APARTE
$stmt->execute();
```

**Los datos nunca se interpretan como SQL**, por más raros que sean. Si `$username = "1' OR '1'='1"`, PDO lo trata como texto literal, no como instrucción SQL.

### 11.3. `bindParam` vs `bindValue`

- `bindParam`: vincula por **referencia** (si la variable cambia después, el valor cambia).
- `bindValue`: copia el valor **en ese momento**.

Usa `bindValue` cuando el valor ya está listo (como el patrón de búsqueda o el offset).

### 11.4. SQLSTATE 23000

Código de MySQL cuando se viola un **índice UNIQUE** (dos registros con el mismo valor). El Service lo traduce:

```php
catch (\PDOException $e) {
    if ($e->getCode() === '23000') {
        throw new DomainException(ErrorCodes::USERNAME_YA_EXISTE, 409,
            'El nombre de usuario ya está en uso.');
    }
    throw $e;
}
```

---

## 12. JWT

### 12.1. Qué es

JWT (JSON Web Token) es un token firmado que **contiene la sesión**. El servidor no guarda estado: el token ES la sesión.

### 12.2. Estructura

```
eyJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3... .s9FkH2d...
└── header ──┘  └── payload ──┘  └── firma ──┘
```

1. **Header:** algoritmo (HS256) y tipo.
2. **Payload:** datos — `iat` (emitido), `exp` (expira: 1 hora), `data` (id_usuario, id_rol...).
3. **Firma:** hash de header+payload con la clave secreta. Si alguien modifica el payload, la firma deja de coincidir.

### 12.3. Cómo se usa

- **Login:** el backend firma un JWT y lo devuelve.
- **Cada petición privada:** el frontend adjunta `Authorization: Bearer <token>`.
- **Middleware:** extrae y verifica el token.
- **Renovación:** `POST /v1/auth/renovar` → el backend re-firma con +1 hora.

**El frontend NUNCA fabrica un token** (no tiene la clave secreta). Renovar = pedir un token nuevo al backend.

---

## 13. RBAC

### 13.1. Qué es

RBAC (Role-Based Access Control) organiza el acceso así:

```
USUARIO  ──tiene──►  ROL  ──tiene──►  PERMISOS
(mroberth)          (Admin)          (usuarios.leer, usuarios.crear, ...)
                    (Empleado)       (solo usuarios.leer)
```

No se dan permisos al usuario directamente: se le asigna un rol, y el rol tiene permisos. Para cambiar el acceso de 50 empleados, cambias el rol, no a cada persona.

### 13.2. La BD

```
rol              modulo           permiso
─────            ─────            ──────
id_rol           id_modulo        id_permiso
nombre           nombre           nombre
                 estado           (Activo/Mantenimiento/Inactivo)

rol_modulo_permiso (tabla puente)
─────
id_rol → rol.id_rol
id_modulo → modulo.id_modulo
id_permiso → permiso.id_permiso
```

### 13.3. El atributo PHP

```php
#[RequierePermiso('usuarios', 'editar')]
public function actualizar_usuario(int $id_usuario) { ... }
```

**Los valores deben coincidir EXACTOS** con `modulo.nombre` y `permiso.nombre` de la BD.

### 13.4. El flujo de verificación

```
1. AuthMiddleware confirma JWT → tenemos id_rol
2. RolMiddleware lee la etiqueta #[RequierePermiso('usuarios','editar')]
3. ¿El módulo 'usuarios' está Activo?  NO → 403 MODULE_UNAVAILABLE
4. ¿El rol tiene 'editar' en 'usuarios'?  NO → 403 ACCESO_DENEGADO
5. Pasa → se ejecuta el controlador
```

---

## 14. Receta: crear un módulo nuevo desde cero

Siguiendo el patrón exacto de los módulos existentes (usuarios, configuración):

### Paso 1: ErrorCodes.php

Agregar las constantes de error:

```php
// En app/Core/Exceptions/ErrorCodes.php
public const ENTIDAD_NO_ENCONTRADA = 'ENTITY_NOT_FOUND';
public const ENTIDAD_YA_EXISTE     = 'ENTITY_ALREADY_EXISTS';
public const ENTIDAD_EN_USO        = 'ENTITY_IN_USE';
```

### Paso 2: Repository

Crear `app/Repositories/EntidadRepository.php`:

```php
namespace App\Repositories;

use PDO;
use App\Config\Conexion;

class EntidadRepository {
    use \App\Core\Traits\PatronBusqueda;

    private readonly PDO $conn;

    public function __construct() {
        $this->conn = Conexion::obtenerConexion('security');
    }

    private const CONDICION_BUSCAR = "nombre LIKE :b_nombre";

    public function contar(?string $buscar = null): int { ... }
    public function consultar(int $pagina, int $limite, ?string $buscar = null): array { ... }
    public function buscarPorNombre(string $nombre, ?int $exceptId = null): ?array { ... }
    public function crear(array $data): ?array { ... }
    public function actualizar(array $data): ?array { ... }
    public function eliminar(int $id): bool { ... }
}
```

**Lo único específico de cada módulo:** la constante `CONDICION_BUSCAR`, el `COUNT(*)`, el `SELECT` con columnas y JOIN, y el `LIMIT/OFFSET` con `PARAM_INT`.

### Paso 3: Service

Crear `app/Services/EntidadService.php`:

```php
namespace App\Services;

use App\Repositories\EntidadRepository;
use App\Core\Exceptions\DomainException;
use App\Core\Exceptions\ErrorCodes;

class EntidadService {
    use \App\Core\Traits\ValidaParametros;

    private EntidadRepository $repository;

    public function __construct() {
        $this->repository = new EntidadRepository();
    }

    public function consultar(mixed $pagina, mixed $limite, mixed $buscar): array {
        $pagina = $this->normalizarEntero($pagina, 1, 1, null, 'pagina');
        $limite = $this->normalizarEntero($limite, 20, 1, 100, 'limite');
        $buscar = $this->normalizarBusqueda($buscar);

        $total = $this->repository->contar($buscar);
        $items = $this->repository->consultar($pagina, $limite, $buscar);

        return [
            'items' => $items,
            'paginacion' => [
                'pagina'        => $pagina,
                'limite'        => $limite,
                'total'         => $total,
                'total_paginas' => (int) ceil($total / $limite),
            ],
        ];
    }

    public function crear(array $data): array {
        // 1. Validar obligatorios
        if ($data['nombre'] === '') {
            throw new DomainException(ErrorCodes::VALIDACION, 400,
                'Todos los campos son obligatorios.');
        }

        // 2. Validar unicidad
        if ($this->repository->buscarPorNombre($data['nombre'])) {
            throw new DomainException(ErrorCodes::ENTIDAD_YA_EXISTE, 409,
                'El registro ya existe.');
        }

        // 3. Persistir
        try {
            $resultado = $this->repository->crear($data);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException(ErrorCodes::ENTIDAD_YA_EXISTE, 409,
                    'El registro ya existe.');
            }
            throw $e;
        }

        if ($resultado === null) {
            throw new DomainException(ErrorCodes::ERROR_INTERNO, 500,
                'No se pudo crear el registro.');
        }

        return $resultado;
    }
}
```

### Paso 4: Controller

Crear `app/Controllers/EntidadController.php`:

```php
namespace App\Controllers;

use App\Services\EntidadService;
use App\Core\Attributes\RequierePermiso;
use App\Core\Exceptions\DomainException;
use App\Core\Exceptions\ErrorCodes;
use App\Core\Response;
use Throwable;

class EntidadController {
    private EntidadService $service;

    public function __construct() {
        $this->service = new EntidadService();
    }

    #[RequierePermiso('entidad', 'leer')]
    public function consultar() {
        $pagina = $_GET['pagina'] ?? null;
        $limite = $_GET['limite'] ?? null;
        $buscar = $_GET['buscar'] ?? null;

        $resultado = $this->service->consultar($pagina, $limite, $buscar);
        Response::jsonSuccess($resultado);
    }

    #[RequierePermiso('entidad', 'crear')]
    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            Response::jsonError(ErrorCodes::JSON_INVALIDO, 400,
                'El cuerpo de la solicitud no es un JSON válido.');
        }

        $nombre = trim($data['nombre'] ?? '');

        try {
            $resultado = $this->service->crear(['nombre' => $nombre]);
            Response::jsonSuccess($resultado);
        } catch (DomainException $e) {
            Response::jsonError($e->getCodigo(), $e->getEstado(), $e->getMessage());
        } catch (Throwable $e) {
            error_log("Error al crear entidad: " . $e->getMessage());
            Response::jsonError(ErrorCodes::ERROR_INTERNO, 500,
                'No se pudo crear el registro.');
        }
    }
}
```

### Paso 5: Rutas

Crear `app/Routes/entidad.php`:

```php
use App\Controllers\EntidadController;

$router->get("/v1/entidad", [EntidadController::class, "consultar"]);
$router->get("/v1/entidad/validar-nombre", [EntidadController::class, "validar_nombre"]);
$router->post("/v1/entidad", [EntidadController::class, "crear"]);
$router->put("/v1/entidad/{id}", [EntidadController::class, "actualizar"]);
$router->delete("/v1/entidad/{id}", [EntidadController::class, "eliminar"]);
```

**No tocar `rutas.php`:** el `glob` auto-carga el archivo nuevo.

### Paso 6: Seeds en la BD

```sql
-- Crear el módulo
INSERT INTO modulo (nombre, estado) VALUES ('entidad', 'Activo');

-- Crear los permisos (si no existen ya)
INSERT INTO permiso (nombre) VALUES ('leer'), ('crear'), ('editar'), ('eliminar');

-- Asignar permisos al rol Admin
INSERT INTO rol_modulo_permiso (id_rol, id_modulo, id_permiso)
SELECT 1, m.id_modulo, p.id_permiso
FROM modulo m, permiso p
WHERE m.nombre = 'entidad'
  AND p.nombre IN ('leer', 'crear', 'editar', 'eliminar');
```

### Checklist de coherencia

- [ ] `ErrorCodes.php` tiene las constantes del módulo
- [ ] Repository tiene `use PatronBusqueda` + `CONDICION_BUSCAR`
- [ ] Repository usa `PDO::PARAM_INT` para LIMIT/OFFSET
- [ ] Repository NO tiene `try-catch`
- [ ] Service tiene `use ValidaParametros`
- [ ] Service valida TODO antes de normalizar
- [ ] Service traduce `PDOException` 23000 → `DomainException`
- [ ] Controller tiene `#[RequierePermiso]` en cada método
- [ ] Controller tiene doble catch (`DomainException` + `Throwable`)
- [ ] Controller NO castea tipos antes de validar
- [ ] Las rutas usan `{id}` para PUT/DELETE
- [ ] Seeds del módulo y permisos creados en la BD

---

## 15. Reglas de oro del backend

1. **Cada capa conoce a la de abajo; ninguna conoce a la de arriba.** Controller = HTTP, Service = negocio, Repository = SQL.

2. **`return` = camino feliz; `throw` = camino de error.** El Service nunca devuelve "un error" como resultado normal.

3. **Todo error de negocio = `DomainException`** con código (de `ErrorCodes.php`) + estado + mensaje. Nunca strings sueltos.

4. **`password_hash` UNA sola vez, en el Service.** Doble hash rompe el login en silencio.

5. **No castear en el Controller.** Pasar datos crudos; el Service valida presencia y normaliza después. Nunca `(bool) "false"` (da `true`).

6. **La unicidad real la garantiza el índice UNIQUE de la BD**; el check del Service es UX/defensa. Traducir SQLSTATE 23000 → 409.

7. **El Repository es SQL puro:** sin `Response`, sin `try-catch`, sin códigos de negocio.

8. **Listados paginados:** usa los traits `ValidaParametros` (Service) y `PatronBusqueda` (Repository). No reescribas la validación.

9. **`LIMIT`/`OFFSET` siempre con `PARAM_INT`**, nunca concatenados.

10. **El id de una ruta `{id}` se recibe posicionalmente en el método**, nunca se lee del body.

11. **Ruta nueva sin `#[RequierePermiso]` = fail-closed (403).** Etiquetar siempre; el módulo debe estar Activo.

12. **Los timestamps entre sistemas se comparan como epoch Unix**, nunca como strings de fecha.

---

## 16. Glosario

| Término | Definición |
|---------|-----------|
| **Contrato JSON** | La forma fija de las respuestas: `{ success, data }` / `{ success: false, error: { codigo, estado, mensaje } }` |
| **DomainException** | Excepción con metadatos: codigo + estado + mensaje decididos por quien la lanza |
| **ErrorCodes** | Registro central de constantes con los códigos del contrato |
| **Open/Closed (OCP)** | Abierto a extensión (nuevos `throw`), cerrado a modificación (el `catch` nunca cambia) |
| **Trait** | Conjunto de métodos reutilizable que una clase se "importa" con `use` |
| **PDO** | Extensión de PHP para hablar con bases de datos |
| **Prepared statement** | Consulta SQL con la estructura y los datos separados |
| **Placeholder** | El `:nombre` en un prepared statement |
| **Inyección SQL** | Atacar una consulta metiendo SQL dentro de los datos del usuario |
| **SQLSTATE 23000** | Código de MySQL cuando se viola un índice UNIQUE |
| **Paginación** | Entregar una página a vez: `LIMIT`/`OFFSET` + `COUNT(*)` |
| **LIKE / comodín** | `%` = cualquier texto, `_` = un carácter; se escapan para buscar literales |
| **HY093** | Error de PDO al repetir el mismo placeholder |
| **Router** | Quién decide qué controlador atiende cada URL + método |
| **Middleware** | Guardia que filtra la petición antes del controlador |
| **Rate limiting** | Limitar cuántas peticiones se aceptan por unidad de tiempo |
| **Token bucket** | Algoritmo de rate limiting: fichas de ráfaga + recarga constante |
| **Epoch Unix** | Segundos desde 1970, sin zona horaria |
| **Fail-open / fail-closed** | Ante un fallo: dejar pasar (open) / bloquear (closed) |
| **JWT** | Token firmado que ES la sesión: header.payload.firma |
| **Bearer** | Estándar `Authorization: Bearer <token>` |
| **RBAC** | Control de acceso por roles: usuario → rol → permisos |
| **Atributo PHP** | Etiqueta `#[...]` sobre una clase/método; metadatos que otro código lee con Reflection |
| **Reflection** | Capacidad de PHP de inspeccionarse a sí mismo en tiempo de ejecución |
| **CORS** | Regla del navegador: un origen no puede pedir a otro sin autorización |
| **PSR-4** | Estándar de autoloading: el namespace refleja la carpeta |

---

*Este documento es la guía de referencia para construir y mantener el backend de EH-SYSTEM. Para el estado actual del sistema, consulta `AGENT.MD`. Para la teoría completa (frontend + backend), consulta `GUIA-TECNICA.MD`.*
