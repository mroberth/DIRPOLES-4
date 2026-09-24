# GUÍA BACKEND + FRONTEND — DIRPOLES-4 (estado actual)

> Esta guía explica, **desde cero**, cómo está construido DIRPOLES-4 y **por qué
> funciona bien**, comparándolo con el sistema anterior (DIRPOLES_4). Está
> pensada para que puedas **explicar en detalle** lo que hicimos en backend y
> frontend, y defender las decisiones técnicas.
>
> Léela en orden. Cuando aparezca un concepto (RSA, JWT, bcrypt, token bucket,
> PSR-4, etc.) se explica ahí mismo antes de usarlo.

---

## Índice

1. [Qué es DIRPOLES-4 y qué estamos refactorizando](#1)
2. [Arquitectura general (MVC híbrido)](#2)
3. [El arranque: `index.php` paso a paso](#3)
4. [Enrutamiento (Router) y parámetros `{id}`](#4)
5. [Middlewares: rate limit y sesión/JWT](#5)
6. [La capa de datos (PDO y SQL seguro)](#6)
7. [El patrón de Modelo](#7)
8. [Manejo de errores unificado](#8)
9. [Seguridad — Autenticación](#9)
10. [Seguridad — Autorización (RBAC)](#10)
11. [Auditoría y Notificaciones](#11)
12. [El frontend: templates renderizados en servidor](#12)
13. [El frontend: arquitectura JavaScript](#13)
14. [El contrato de la API desde el navegador](#14)
15. [Validaciones reutilizables (crear/editar)](#15)
16. [Librerías de frontend y para qué sirven](#16)
17. [Optimización: el caso de la matriz de permisos](#17)
18. [Estándares y conceptos aplicados (resumen)](#18)
19. [Diferencias con DIRPOLES_4 y por qué ahora funciona](#19)
20. [Cómo explicarlo en una defensa (preguntas típicas)](#20)

---

<a name="1"></a>
## 1. Qué es DIRPOLES-4 y qué estamos refactorizando

DIRPOLES-4 es un **monolito híbrido**: una sola aplicación PHP que sirve tanto
páginas HTML como una API JSON (para el frontend y para la eventual app móvil).
No usa framework (Laravel, Symfony…), sino un MVC propio, pequeño y explícito.

**¿Qué significa "monolito híbrido"?**

- *Monolito*: todo el código vive en un solo repositorio y se despliega como una
  sola aplicación. Lo contrario sería microservicios.
- *Híbrido*: la misma aplicación responde **HTML** (páginas renderizadas en el
  servidor) y **JSON** (endpoints tipo API), dependiendo de la ruta.

**¿Qué refactorizamos?**

El sistema anterior (DIRPOLES_4) funcionaba, pero tenía inconsistencias:
errores que se formateaban de 5 maneras distintas, permisos repetidos en cada
función, respuestas JSON ad-hoc, y lógica que fallaba en silencio. En este
repositorio **reescribimos la misma funcionalidad con reglas claras y únicas**,
y fuimos construyendo los módulos uno a uno.

**¿Qué llevamos construido?**

- Autenticación completa (login RSA + bcrypt, JWT RS256, refresh token con
  rotación, bloqueo por intentos, logout).
- Módulo transversal de **Notificaciones** (bandeja + streaming SSE).
- **Panel de Inicio** compuesto por rol/permisos (cards de módulos, stats,
  calendario personal).
- Módulos de **Empleados**, **Beneficiarios** (CRUD completo con modales).
- **Permisos** (matriz rol × módulo × permiso).
- **Configuración** (crear/consultar los 7 catálogos del sistema).
- **Bitácora** (auditoría, solo lectura, con filtros).
- **Respaldo BD** (descarga `.sql` de negocio o seguridad).

---

<a name="2"></a>
## 2. Arquitectura general (MVC híbrido)

### 2.1 El patrón MVC

**MVC** (Model–View–Controller) separa una aplicación en tres responsabilidades:

- **Model (Modelo)**: reglas de negocio y acceso a datos. En DIRPOLES-4 son
  clases en `app/Models/`. *No saben nada de HTTP*: no leen `$_POST`, no
  imprimen JSON.
- **View (Vista)**: presentación. En DIRPOLES-4 son plantillas PHP en
  `app/Views/`. *No saben de SQL*: solo muestran datos.
- **Controller (Controlador)**: puente entre HTTP y el modelo. En DIRPOLES-4 son
  **solo funciones** en `app/Controllers/` (regla de la universidad: el
  diagrama de clases solo documenta Modelos y Core, así que no hay clases en
  controladores).

### 2.2 El patrón Front Controller

**Front Controller** significa que **todas** las peticiones entran por un único
archivo: `index.php`. Ventaja: la configuración (CORS, sesión, manejo de
errores, ruteo) se hace **una sola vez** y aplica a todo.

```
Navegador
   │  https://host/DIRPOLES-4/api/empleados/listar
   ▼
.htaccess  →  RewriteRule a index.php (si el archivo no existe)
   ▼
index.php  (Front Controller)
   ▼
Router → Middlewares → Controlador → Modelo (SQL) → Respuesta (JSON/HTML)
```

### 2.3 La regla de las DOS PUERTAS (y una tercera)

| Puerta | Ruta | Responde | Ejemplo |
|---|---|---|---|
| HTML | fuera de `api/` | HTML renderizado o redirección | `empleados/consultar` |
| JSON | bajo `api/` (o `Accept: application/json`) | JSON del contrato | `api/empleados/listar` |
| **SSE** (excepción) | `sse/notificaciones` | `text/event-stream` | campana en vivo |
| **Archivo** (excepción) | `respaldo/descargar` | `.sql` (`application/sql`) | descarga de respaldo |

**Por qué la regla es útil**: el frontend siempre sabe qué esperar. Una ruta
`api/*` **jamás** devuelve HTML (ni siquiera una página de error: devuelve el
JSON de error). Una página **jamás** escupe JSON. Esto evita el problema clásico
del sistema viejo, donde un error a mitad de una petición AJAX devolvía una
página HTML y el JavaScript reventaba al intentar `JSON.parse`.

### 2.4 Estructura de capas del backend

```
app/
├── Core/          Clases de infraestructura (no negocio):
│                  Router, Database, Respuesta, ExcepcionApi, ErrorCodes,
│                  Autorizacion, Bitacora, Notificador, JwtHandler
├── Middlewares/   Filtros globales: RateLimit, SessionAuth
├── Models/        Clases de negocio: extienden Business/SecurityModel
├── Controllers/   SOLO funciones: leen HTTP y llaman al modelo
├── Views/         Plantillas PHP (HTML)
├── routes/        Un archivo por módulo (se autocarga)
└── Config/        Configuración no sensible y mapas (sidebar, cards, catálogos)
```

**Jerarquía de conexión a datos** (se explica en la sección 6):

```
Database (Core)
 ├── SecurityModel  → base de datos dirpoles_security
 └── BusinessModel  → base de datos dirpoles_business
      └── [Tu]Model  → hereda una de las dos
```

---

<a name="3"></a>
## 3. El arranque: `index.php` paso a paso

`index.php` es el corazón. Hace, en orden:

```php
1. define BASE_PATH y BASE_URL        // rutas absolutas del proyecto
2. require vendor/autoload.php        // autoload de Composer (PSR-4)
3. carga el .env (phpdotenv)          // variables de entorno
4. aplica CORS                        // según CORS_ALLOWED_ORIGINS
5. responde 200 a OPTIONS (preflight)
6. session_start()                    // sesión PHP
7. require bootstrap.php + routes.php // helpers y rutas
8. registra el handler de excepciones // error centralizado
9. registra el shutdown handler       // errores fatales
10. Router::ejecutar()                // ¡a trabajar!
```

### 3.1 ¿Qué es `vendor/autoload.php` y PSR-4?

**Composer** es el gestor de dependencias de PHP. Cuando le pides una librería
(ej. `firebase/php-jwt`) la descarga en `vendor/` y genera un archivo
`autoload.php` que sabe **cargar automáticamente las clases** cuando las usas.

**PSR-4** es un estándar del PHP-FIG (grupo que define normas de PHP) que dice
cómo mapear el *namespace* de una clase a su archivo. En `composer.json`:

```json
"autoload": { "psr-4": { "App\\": "app/" } }
```

Significa: la clase `App\Models\EmpleadoModel` vive en `app/Models/EmpleadoModel.php`.
Con esto **no hay que hacer `require` manual** de cada clase: Composer la carga
cuando se usa. *Estándar: PSR-4 (autoloading).*

### 3.2 ¿Qué es `.env` y por qué no hardcodear secretos?

`.env` es un archivo de texto con variables clave=valor (contraseñas de BD,
duración del JWT, etc.). La librería **phpdotenv** las carga en `$_ENV`.

**¿Por qué?** Meter contraseñas en el código (como el viejo `app/Config/config.php`
con constantes `DB_PASS`) es peligroso: se filtran al compartir el repo. Con
`.env` el código no cambia entre entornos; solo cambia el `.env` (que va en
`.gitignore`). Esto es una práctica estándar (12-Factor App, factor III: Config).

### 3.3 CORS (Cross-Origin Resource Sharing)

**CORS** es un mecanismo de seguridad del navegador: por defecto, una página de
un origen (`http://localhost:5173`) **no** puede leer respuestas de otro origen
(`http://localhost`) salvo que el servidor lo autorice con cabeceras.

- El navegador primero manda una petición `OPTIONS` ("preflight") preguntando si
  puede. El servidor responde con `Access-Control-Allow-Origin`.
- Solo permitimos los orígenes listados en `CORS_ALLOWED_ORIGINS` (del `.env`),
  nunca todos (`*`), porque `*` con credenciales está prohibido.

*Estándar: W3C CORS.*

### 3.4 El handler global de excepciones

```php
set_exception_handler([App\Core\Respuesta::class, 'manejarExcepcion']);
```

Esto le dice a PHP: "si una excepción no la atrapa nadie, llámame". Nuestro
`manejarExcepcion` decide el formato según la puerta (JSON o HTML) y registra el
error real en el log. **Ventaja**: ningún controlador necesita `try/catch` para
formatear errores (esto es justo lo que el sistema viejo hacía mal, repitiendo
`try/catch + echo json_encode` en cada función).

El `register_shutdown_function` es una **red de seguridad final** para errores
fatales (`E_ERROR`, `E_PARSE`) que no viajan como excepción (por ejemplo, si se
acaba la memoria). Garantiza que aun así se respete el contrato.

---

<a name="4"></a>
## 4. Enrutamiento (Router) y parámetros `{id}`

### 4.1 Cómo funciona el Router

Las rutas se registran así:

```php
Router::get('api/empleados/listar', function () {
    load_controller('empleadoController.php');
    apiListarEmpleados();
});
```

`Router::ejecutar()` hace:

1. Toma el método HTTP y la ruta pedida (`parse_url`).
2. Le quita el prefijo base (`/DIRPOLES-4`).
3. Corre los middlewares (sección 5).
4. Busca una ruta cuyo patrón coincida y cuyo método coincida.
5. Llama al closure. Si no hay ruta → 404; si el método no coincide → 405.

### 4.2 Patrones con parámetros y la inyección en `$_GET`

Un patrón como `api/empleados/obtener/{id}` se convierte en una expresión
regular: `{id}` → `[^/]+` (`[^/]+` = "uno o más caracteres que no sean `/`").
Así `api/empleados/obtener/25` coincide.

**Detalle importante**: los *closures* del Router no reciben argumentos, así que
los valores capturados se **inyectan en `$_GET`**:

```php
Router::get('api/empleados/obtener/{id}', function () {
    load_controller('empleadoController.php');
    apiObtenerEmpleado();   // el controlador lee $_GET['id']
});
```

Así el controlador lo lee con `$_GET['id']` o `filter_input`, igual que un query
param normal (`?id=25`). Con varios parámetros, ej. `obtener/{catalogo}/{id}`,
se inyectan ambos.

### 4.3 La autocarga de rutas por módulo

`app/routes.php` hace `foreach (glob(BASE_PATH.'app/routes/*.php'))`. Es decir,
**cada archivo nuevo de ruta se carga solo**: no hay que registrar nada en un
archivo central. Esto reduce errores al olvidar registrar una ruta.

---

<a name="5"></a>
## 5. Middlewares: rate limit y sesión/JWT

Un **middleware** es un filtro que se ejecuta **antes** del controlador. En
DIRPOLES-4 hay dos globales, en este orden:

```
Petición → RateLimitMiddleware → SessionAuthMiddleware → Ruta
```

### 5.1 RateLimitMiddleware (Token Bucket)

**¿Qué es rate limiting?** Limitar cuántas peticiones puede hacer un cliente en
un tiempo, para evitar fuerza bruta, scraping y abuso.

**¿Qué es Token Bucket?** Un algoritmo estándar: imagina un balde con fichas.
- Cada petición gasta **1 ficha**.
- El balde se **rellena** a una tasa constante (ej. 5 por minuto).
- Si no hay fichas → se rechaza con **HTTP 429** (`Too Many Requests`).

La implementación guarda en BD (`dirpoles_security.rate_limits`) por
**IP × endpoint**: `tokens_actuales` y `ultima_peticion`. Al llegar una petición
recalcula las fichas regeneradas según el tiempo transcurrido.

**Políticas por sensibilidad**:

| Nivel | Endpoint | Capacidad | Tasa |
|---|---|---|---|
| Login | `iniciar_sesion` | 5 | 5 / 5 min |
| IA | termina en `_ia` | 3 | 3 / 5 min |
| Validaciones | contiene `validar_`/`verificar_` | 80 | 80 / min |
| Escritura | cualquier POST | 15 | 15 / min |
| Lectura | cualquier GET | 30 | 30 / min |

**¿Por qué "token bucket" y no "contador fijo"?** Porque permite ráfagas (usar
varias fichas seguidas) y se recupera suavemente con el tiempo, sin bloquear
todo el minuto. *Estándar/algoritmo clásico de redes.*

Cuando responde 429, lo hace con el **mismo contrato JSON** que todo el sistema
(sección 8) y cabecera `Retry-After`. Esto es una corrección: antes devolvía un
JSON distinto.

### 5.2 SessionAuthMiddleware (sesión + JWT)

Este middleware valida **dos cosas a la vez** antes de dejar pasar la petición:

1. Que exista **sesión PHP** con `id_empleado`.
2. Que la **cookie JWT** sea válida y su `id_empleado` coincida con el de la
   sesión (validación cruzada).

**¿Por qué validar dos?** Porque son dos mecanismos distintos: la sesión PHP vive
en el servidor (archivo en disco con un id en cookie) y es **stateful**; el JWT
es un token firmado que el cliente guarda en cookie y es **stateless** (el
servidor no lo consulta en BD para validarlo). Exigir ambos da defensa en
profundidad: aunque roben uno, el otro debe coincidir.

Rutas públicas: `login`, `iniciar_sesion`, `logout`, `error`. `refresh_token`
exige sesión pero **no** JWT (porque el JWT es justo lo que expiró).

Si falta sesión/JWT, redirige al login:
- Si es AJAX/JSON → 401 con el contrato `{exito:false, error:{...}, datos:{redireccion}}`.
- Si es navegación normal → flash en sesión + `Location: login`.

---

<a name="6"></a>
## 6. La capa de datos (PDO y SQL seguro)

### 6.1 ¿Qué es PDO?

**PDO** (PHP Data Objects) es la capa de abstracción de PHP para hablar con
bases de datos. Permite usar **sentencias preparadas** y manejar errores como
excepciones (`PDO::ERRMODE_EXCEPTION`).

### 6.2 Las dos bases de datos

- `dirpoles_security`: usuarios, roles, permisos, bitácora, tokens, rate limits,
  notificaciones, intentos de login.
- `dirpoles_business`: beneficiarios, citas, consultas, inventario, catálogos,
  calendario, etc.

**¿Por qué separar?** Separa datos sensibles (credenciales/auditoría) de datos
operativos. Fue una decisión de diseño del proyecto.

`Database` (en `app/Core/`) expone dos métodos `Business()` y `Security()` que
crean la conexión PDO correspondiente. `SecurityModel` y `BusinessModel` las
invocan en su constructor, y cada modelo hereda la que necesita:
`$this->conn` (negocio) o `$this->conn_security` (seguridad).

### 6.3 Inyección SQL y por qué usamos sentencias preparadas

**Inyección SQL**: si concatenas datos del usuario en una consulta:

```php
// MAL (vulnerable)
$sql = "SELECT * FROM empleado WHERE correo = '$correo'";
// Si $correo = "' OR '1'='1", el WHERE siempre es verdadero.
```

**Sentencia preparada**: se le manda a MySQL la *plantilla* con huecos (`:correo`)
y **por separado** los valores. MySQL nunca interpreta el valor como SQL:

```php
$stmt = $pdo->prepare("SELECT * FROM empleado WHERE correo = :correo");
$stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
$stmt->execute();
```

*Estándar/práctica OWASP: la defensa número uno contra inyección SQL.*

Además, para `LIMIT/OFFSET` siempre usamos `PDO::PARAM_INT`, porque si se pasan
como string MySQL los rechaza.

### 6.4 ¿Validar o escapar? Son cosas distintas

- **Validar**: comprobar que el dato cumple el formato (un correo válido, una
  cédula numérica). Es lo que hace `__set()`.
- **Escapar**: neutralizar caracteres peligrosos al **mostrar** el dato en HTML.
  Es lo que hace `htmlspecialchars()` en las vistas (evita XSS).

Se usan **juntas**: validar en la entrada y escapar en la salida.

---

<a name="7"></a>
## 7. El patrón de Modelo

Todos los modelos siguen el mismo patrón fijo:

```php
class EmpleadoModel extends SecurityModel
{
    private array $atributos = [];

    // 1) CAPA DE VALIDACIÓN: en cada asignación
    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'correo':
                if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    throw ExcepcionApi::validacion('El correo no es válido.');
                }
                $this->atributos['correo'] = $valor;
                break;
            default:
                throw ExcepcionApi::validacion("Atributo desconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed { return $this->atributos[$nombre] ?? null; }

    // 2) DESPACHADOR: única puerta pública
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'crear'  => $this->crear(),
            'listar' => $this->listar(),
            default  => throw ExcepcionApi::errorInterno("Acción no válida: {$accion}."),
        };
    }

    // 3) MÉTODOS PRIVADOS: SQL + reglas, lanzan ExcepcionApi
    private function crear(): array { /* ... */ }
}
```

**Puntos clave y por qué:**

- **`__set()` valida y lanza**. Centraliza la validación: es imposible guardar
  un dato inválido porque setear ya lo rechaza. *`__set`/`__get` son "métodos
  mágicos" de PHP que se ejecutan al hacer `$modelo->atributo = valor`.*
- **`manejarAccion()` es la única puerta pública**: el controlador no puede
  llamar `crear()` directamente (es privado). Así el modelo controla qué
  operaciones existen. Es el "service" del modelo.
- **Los métodos privados son el "repository"**: el SQL vive ahí, no en una capa
  aparte. (Regla del proyecto: no crear carpetas `Services/` ni `Repositories/`.)
- **El modelo LANZA, no devuelve errores**: `throw ExcepcionApi::yaExiste(...)`
  en vez de `return ['exito' => false]`. Esto es **la diferencia más grande con
  el sistema viejo** y se explica en la sección 8.

**Separación de "forma" y "fondo"**: la *forma* (crear/listar/actualizar) es
igual en todos los módulos; el *fondo* (columnas, reglas) cambia por modelo.

---

<a name="8"></a>
## 8. Manejo de errores unificado

### 8.1 El problema del sistema viejo

En el código viejo cada función hacía algo así:

```php
try {
    // ...
    echo json_encode(['exito' => true, 'mensaje' => '...']);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
```

Problemas:
- El formato de error variaba (`mensaje`, `data`, `error`, `status`...).
- El frontend no podía programar de forma estable.
- Los modelos también devolvían `['exito' => false]`, mezclando "negocio" con
  "técnico".
- Errores silenciosos (se tragaban con `return false`).

### 8.2 La solución: excepciones + un contrato + un handler

**a) `ExcepcionApi`**: una sola excepción de negocio con metadatos:

```php
throw ExcepcionApi::yaExiste('Ese correo ya está registrado en el sistema.');
// Lleva: codigo=ALREADY_EXISTS, estado=409, mensaje=...
```

Fábricas: `validacion()` 400, `noEncontrado()` 404, `yaExiste()` 409,
`enUso()` 409, `accesoDenegado()` 403, `errorInterno()` 500, `jsonInvalido()` 400.

**b) `ErrorCodes`**: registro central de códigos **estables**. El frontend
programa contra el **código** (`ALREADY_EXISTS`), nunca contra el mensaje (que
puede cambiar de redacción). *Principio: los mensajes son para humanos, los
códigos para máquinas.*

**c) `Respuesta`** (contrato JSON único):

```jsonc
// Éxito:
{ "exito": true, "datos": <cualquier cosa> }

// Error:
{ "exito": false, "error": { "codigo": "ALREADY_EXISTS", "estado": 409, "mensaje": "..." } }
```

Todo el JSON sale por `Respuesta::exito($datos)` o `Respuesta::error($e)`
(ambos hacen `exit`). Es el **único punto de salida JSON**.

**d) El handler global**: si una excepción escapa, `Respuesta::manejarExcepcion`
decide:
- Puerta `api/*` → JSON del contrato.
- Página → registra el error real en `logs/` y muestra `errors/error.php`.

**Ventaja**: los controladores **no** tienen `try/catch` para formatear. Su
código expresa el camino feliz, y el error lo maneja el Core una sola vez.

### 8.3 Estados HTTP usados (y por qué)

| Código | Significado | Uso |
|---|---|---|
| 200 | OK | lectura/actualización exitosa |
| 201 | Created | se creó un recurso |
| 400 | Bad Request | datos inválidos |
| 401 | Unauthorized | falta autenticación |
| 403 | Forbidden | autenticado pero sin permiso |
| 404 | Not Found | recurso/ruta inexistente |
| 405 | Method Not Allowed | verbo HTTP incorrecto |
| 409 | Conflict | duplicado o con dependencias |
| 429 | Too Many Requests | rate limit |
| 500 | Internal Server Error | fallo técnico |

*Estándar: RFC 9110 (semántica de HTTP).*

---

<a name="9"></a>
## 9. Seguridad — Autenticación

Aquí está la parte más "jugosa" para explicar. El login tiene **cuatro capas**.

### 9.1 Cifrado de la contraseña en el cliente (RSA)

**El flujo completo del login:**

```
1. El servidor embute su llave PÚBLICA RSA en la página de login.
2. El usuario escribe correo + contraseña.
3. El JavaScript (JSEncrypt) cifra la contraseña con esa llave PÚBLICA.
4. Envía { correo, password_cifrado } por POST.
5. El servidor descifra con su llave PRIVADA (OpenSSL).
6. Compara la contraseña resultante contra el hash bcrypt en la BD.
```

**¿Qué es cifrado asimétrico (RSA)?** A diferencia del cifrado simétrico (una
misma clave para cifrar y descifrar), el asimétrico usa una **pareja**:
- **Pública**: se puede repartir; sirve para cifrar (o verificar firmas).
- **Privada**: solo la tiene el servidor; sirve para descifrar (o firmar).

Algo cifrado con la pública **solo** se puede descifrar con la privada. RSA es el
 algoritmo de clave pública más conocido. *Estándar: RFC 8017 (PKCS #1).*

**PKCS#1 v1.5**: es el "esquema de relleno" (padding) que usa `openssl_public_encrypt`
y JSEncrypt por defecto; el relleno añade bytes aleatorios para que cifrar la
misma contraseña dos veces dé resultados distintos y sea resistente a ataques.

**Pregunta típica: "¿pero si ya uso HTTPS, para qué cifrar?"**
- El cifrado RSA aquí es **defensa en profundidad**: si la contraseña nunca viaja
  en claro, aunque alguien capture el POST (logs del servidor, un proxy mal
  configurado, un error de TLS), solo ve texto cifrado que no puede abrir.
- Además, desacopla la contraseña del cifrado del canal.

Las llaves viven en `app/Config/Keys/` (`login_public.pem`, `login_private.pem`)
y **nunca** se versionan. La privada con permisos `600` (solo el dueño lee/escribe).

### 9.2 Verificación con bcrypt

**Nunca se guarda la contraseña en la BD.** Se guarda un **hash**. Un hash es una
función de una vía: fácil de calcular, imposible (en la práctica) de revertir.

**¿Qué es bcrypt?** Es un algoritmo de hashing diseñado para contraseñas
(basado en el cifrado Blowfish). Sus propiedades clave:

- **Salt** (sal): un valor aleatorio que se mezcla con la contraseña antes de
  hashear. Se guarda *dentro* del hash. Evita que dos usuarios con la misma
  contraseña tengan el mismo hash, y neutraliza las "tablas precalculadas"
  (rainbow tables).
- **Work factor (coste)**: nº de rondas de cómputo. Hace que calcular un hash sea
  lento (a propósito), lo que encarece los ataques de fuerza bruta. El prefijo
  `$2y$10$` significa bcrypt con coste 10.

En PHP: `password_hash($clave, PASSWORD_BCRYPT)` para crear y
`password_verify($clave, $hash)` para comparar. **No** se hace `==` de hashes
(comparación de tiempo constante interna).

**¿Por qué no MD5/SHA1/SHA256?** Porque son **rápidos**: se pueden probar miles
de millones por segundo con GPU. bcrypt es lentamente ajustable. *Estándar:
recomendación de OWASP Password Storage Cheat Sheet.*

### 9.3 Sesión + JWT RS256

**¿Qué es un JWT?** JSON Web Token (RFC 7519). Es una cadena `x.y.z`:

```
HEADER.PAYLOAD.FIRMA
```

- **Header**: algoritmo usado (ej. `{"alg":"RS256","typ":"JWT"}`).
- **Payload (claims)**: datos (`iat` = emitido, `exp` = expira, `data.id_empleado`).
- **Firma**: el servidor firma header+payload con su llave.

El JWT es **stateless**: el servidor no necesita guardar la sesión para validarlo;
solo verifica la firma. Si alguien altera el payload, la firma deja de coincidir.

**RS256 = RSA + SHA-256 (firma asimétrica).** Hay dos variantes comunes:
- **HS256**: firma simétrica (misma clave para firmar y verificar). Si muchos
  servicios verifican, todos tendrían la clave de firma (riesgo).
- **RS256**: firma con la **privada** y se verifica con la **pública**. Solo el
  servidor puede firmar; cualquiera puede verificar con la pública. Es lo
  correcto para un sistema con múltiples clientes (web y móvil).

Se guarda en una cookie **HttpOnly** (el JavaScript no puede leerla → mitiga
robo por XSS) y **SameSite=Lax** (no se envía en peticiones cross-site → mitiga
CSRF).

### 9.4 Refresh token y su rotación

**¿Por qué dos tokens?** El JWT de acceso es de vida **corta** (1 hora) para
limitar el daño si se filtra. El **refresh token** es de vida **larga** (15 días)
y sirve **solo** para pedir un JWT nuevo, sin re-login.

**Rotación (one-time use)**: cada vez que se usa un refresh token, se **revoca**
ese y se emite uno **nuevo**, en la misma transacción. Así, si roban una copia
del token y el dueño lo usa, el robado deja de servir. Si el robado se usa
primero, el dueño detecta y vuelve a loguearse. *Concepto: OAuth 2.0 (RFC 6749)
usa refresh tokens; la rotación es una práctica de seguridad recomendada
(OWASP).*

Implementado en `JwtHandler::renovarJWT()` con `SELECT ... FOR UPDATE` (bloquea
la fila para que dos peticiones simultáneas no usen el mismo token) + revocar +
insertar nuevo + emitir JWT + `commit`.

### 9.5 Bloqueo por intentos fallidos

Regla: tras **3 intentos fallidos** en una cuenta **no administrador**, se
deshabilita. El contador vive en la tabla `login_intentos` (BD de seguridad).

**¿Por qué en BD y no en la sesión?** El sistema viejo lo guardaba en
`$_SESSION`. Como la sesión la controla el cliente (borrando la cookie el
contador se reinicia), **no servía**. En BD no se puede evadir. La IP se
registra para referencia. Al autenticar bien, se limpia (`DELETE`).

### 9.6 `session_regenerate_id` y `session.gc_maxlifetime`

**Session fixation**: si un atacante logra fijar el `PHPSESSID` que usarás (ej.
por la URL) y tú inicias sesión, su cookie apunta a tu sesión autenticada.
`session_regenerate_id(true)` genera un **id nuevo** al autenticar y borra el
archivo viejo, cortando el ataque. *Práctica OWASP.*

**`gc_maxlifetime`**: PHP borra archivos de sesión inactivos más viejos que ese
valor (por defecto 1440 s = 24 min). Como el JWT dura 1 h, en `index.php` se sube
al máximo entre JWT y refresh, para que la sesión **no muera antes** que el JWT.

---

<a name="10"></a>
## 10. Seguridad — Autorización (RBAC)

**¿Qué es RBAC?** Role-Based Access Control: los permisos se asignan a **roles**,
y los usuarios tienen roles. No se da permiso usuario por usuario, sino por rol.

**Modelo de datos**:
- `modulo` (Empleados, Beneficiarios…).
- `permiso` (1=Crear, 2=Leer, 3=Editar, 4=Eliminar).
- `rol_modulo_permiso`: qué rol puede qué acción sobre qué módulo.

**El helper de una línea**:

```php
Autorizacion::verificar('empleados', 'crear');  // lanza 403 si no tiene permiso
```

Internamente resuelve nombre→id con caché por petición y consulta la tabla.
**Antes** (sistema viejo) cada función repetía 6 líneas para verificar permisos.
**Ahora** es una línea y es imposible olvidarla (o queda en la revisión).

**Diferencia clave**: el guardia se pone **en el controlador** (primera línea), no
en la vista. Ocultar un botón no es seguridad; la seguridad es que el endpoint
rechace.

---

<a name="11"></a>
## 11. Auditoría y Notificaciones

### 11.1 Bitácora (auditoría)

`Bitacora::registrar($modulo, $accion, $descripcion)` inserta en `bitacora`
tomando el `id_empleado` de la sesión y `NOW()` de la BD.

**Garantía "nunca lanza"**: la auditoría **no puede** romper la operación de
negocio. Si el INSERT falla, solo deja log y sigue. Esto explica por qué el módulo
de Bitácora (lectura) es **solo lectura**: es un registro legal/inmutable, no se
edita ni se borra.

**Acciones válidas (ENUM)**: Registro, Lectura, Actualización, Eliminación,
Inicio de sesión, Cierre de sesión y **Respaldo** (esta última se agregó al ENUM
justo cuando construimos el módulo de respaldo, porque faltaba y el registro
fallaba en silencio — un bug que existía también en el viejo).

### 11.2 Notificador (notificaciones)

`Notificador::enviar($idReceptor, $titulo, $url, $tipo)` inserta en dos tablas de
forma **atómica** (transacción): la notificación (contenido) y el destinatario.
También "nunca lanza".

El **streaming SSE** (`sse/notificaciones`) mantiene una conexión abierta y emite
eventos `text/event-stream` cuando hay notificación nueva. Es la excepción
deliberada a las dos puertas. Si MySQL se reinicia y la conexión muere
(`2006 MySQL server has gone away`), `NotificacionesModel::reconectar()` la
reabre.

---

<a name="12"></a>
## 12. El frontend: templates renderizados en servidor

**Server-Side Rendering (SSR)**: el HTML se arma en PHP antes de enviarlo. No hay
SPA (React/Vue) ni backend separado (regla del proyecto). Ventajas aquí:
- Carga inicial simple y predecible.
- Menos JavaScript y menos dependencias.
- Ideal para un panel administrativo.

**Estructura de una vista**:

```php
<?php
$titulo = "Consultar Empleados";       // ANTES de head.php
include 'app/Views/template/head.php'; // abre <html><head> y carga CSS
?>
<body id="page-top">
  <div id="wrapper">
    <?php include 'app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <?php include 'app/Views/template/header.php'; ?>
        <div class="container-fluid">  ...contenido...  </div>
      </div>
      <?php include 'app/Views/template/footer.php'; ?>
    </div>
  </div>
  <?php include 'app/Views/template/script.php'; ?>
  <script src=".../modulo/consultar.js" defer></script>
</body>
</html>
```

**Carga selectiva de assets**: `head.php`/`script.php` usan `$titulo` para **no**
cargar librerías pesadas (FullCalendar, DataTables, Select2) en el login. Se
llama "code splitting" a nivel de página.

**Escapado (anti-XSS)**: todo dato dinámico en HTML va con
`htmlspecialchars()`. **XSS** (Cross-Site Scripting) es inyectar JavaScript a
través de un dato; escapar convierte `<script>` en `&lt;script&gt;` (texto
inofensivo).

---

<a name="13"></a>
## 13. El frontend: arquitectura JavaScript

Sin framework, pero con orden. Se separa en **Core** (infraestructura) y
**módulos** (cada pantalla).

```
dist/js/
├── core/                 Infraestructura reutilizable
│   ├── apiFetch.js       Helper para consumir la API (contrato)
│   ├── AlertManager.js   Alertas/confirmaciones (SweetAlert2) + interceptores
│   ├── logout.js         Cerrar sesión
│   ├── modalManager.js   Modales
│   └── select-2-init.js  Inicializar Select2 por clase
├── modulos/
│   ├── empleado/         validaciones.js, tour.js, stats.js, crear.js, editar.js, consultar.js
│   ├── beneficiario/     (mismo patrón)
│   ├── permisos/         validaciones, manager, stats
│   ├── configuracion/    validaciones.js, crear.js, consultar.js, backup.js, tour.js
│   ├── bitacora/         consultar.js, stats.js, tour.js
│   ├── dashboard/        dashboard_stats.js
│   └── notificaciones/   control.js
└── login/login.js
```

**Convención**: un módulo = una carpeta; cada archivo tiene **una** responsabilidad.

- `validaciones.js`: validaciones reutilizables (crear **y** editar).
- `tour.js`: tour con Driver.js.
- `stats.js`: rellenar tarjetas `[data-stat]`.
- `crear.js` / `editar.js`: la lógica de esa pantalla.
- `consultar.js`: tabla + acciones.

**¿Por qué separar?** Para no duplicar: el mismo archivo de validaciones sirve
para crear y editar; el de tour se reusa en varios módulos.

---

<a name="14"></a>
## 14. El contrato de la API desde el navegador

`apiFetch` es el **único** helper para llamar la API. Garantiza:

```js
async function apiFetch(url, opciones = {}) {
    const resp = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',   // envía sesión + cookies JWT
        cache: 'no-store',            // la API nunca se cachea
        ...opciones,
    });

    if (resp.status === 401 && esJson) { /* navega a datos.redireccion */ }

    if (!esJson) throw { codigo:'INTERNAL_SERVER_ERROR', ... };  // respuesta no JSON

    const cuerpo = await resp.json();
    if (!resp.ok || !cuerpo.exito) throw cuerpo.error;  // { codigo, estado, mensaje }
    return cuerpo.datos;
}
```

**Puntos que debes poder explicar:**
- `credentials: 'same-origin'` hace que el navegador mande la cookie de sesión y
  el JWT (si no, la petición iría "anónima").
- Si la respuesta no es JSON (una página HTML de error, un proxy caído), **no**
  intenta `JSON.parse` (que reventaría): lanza un error controlado.
- En 401 navega a la redirección que manda el middleware (`datos.redireccion`).
- El módulo **programa contra `error.codigo`**, no contra el mensaje.

Esto es la contraparte de la sección 8: el contrato único hace que el frontend sea
predecible.

---

<a name="15"></a>
## 15. Validaciones reutilizables (crear/editar)

Cada módulo tiene un `validaciones.js` que expone:

```js
const validador = EmpleadoValidaciones.configurar(form, {
    idExcluir: 0,          // en edición: id de la fila (no marcar su propio correo)
    claveOpcional: true,   // en edición: la contraseña puede ir vacía
});
```

Provee validaciones **locales** (formato, longitud, requerido) y **remotas**
(unicidad contra la API: "ese correo ya existe"). Detalles finos que corregimos:

- **Select2 y los eventos**: Select2 dispara el `change` como **evento de
  jQuery**, que **no** llega a un `addEventListener` nativo. Por eso, para los
  `<select class="select2">` enlazamos también con jQuery (`$(campo).on('change
  select2:select', ...)`). Sin esto, los selects no se validaban (bug real).
- **No cortar la validación con `return` temprano**: si el "tipo de cédula" está
  vacío y retornábamos de inmediato, la "cédula" nunca se marcaba. Ahora se
  validan por separado para marcar **ambos**.
- **Si la API remota falla** (caída de red, 500, 429): el campo se marca en
  **rojo** con "No se pudo verificar…" y se bloquea el envío; **jamás se deja
  en verde**, porque no se verificó nada. Los errores de negocio del backend
  (400/404/409, p. ej. "ya existe") muestran `error.mensaje` tal cual.

**Validación en dos capas**: el cliente da retroalimentación inmediata; el
servidor es la **autoridad** (no se puede confiar en el cliente). Por eso las
mismas reglas están en `validaciones.js` **y** en `__set()` del modelo.

---

<a name="16"></a>
## 16. Librerías de frontend y para qué sirven

- **Bootstrap 5**: sistema de rejilla (grid) y componentes (cards, modales,
  botones). *Tema:* SB Admin 2 (adaptado a BS5).
- **jQuery**: utilidades DOM y AJAX (lo requieren Select2/DataTables).
- **Select2**: convierte un `<select>` en un buscador con estilo. Se inicializa
  **por clase** (`.select2`) para no repetir código en cada módulo. El estado de
  validación (rojo/verde) se pinta con CSS propio (`select2-validacion.css`).
- **DataTables**: tablas con búsqueda, orden, paginación y **exportación a Excel
  y PDF**. Trae empaquetadas las librerías de exportación (JSZip, pdfMake), por
  eso funciona sin dependencias externas.
- **SweetAlert2** (vía `AlertManager`): modales de alerta/confirmación. Además
  intercepta respuestas **429** para avisar del rate limit.
- **Driver.js**: tours guiados paso a paso (exigencia de la universidad).
- **FullCalendar**: calendario personal del panel de Inicio.
- **JSEncrypt**: cifrado RSA en el navegador (login).

**¿Por qué librerías locales en `/plugins`?** El sistema es autocontenido: no
depende de CDN (que puede caer o cambiar) y funciona sin internet.

---

<a name="17"></a>
## 17. Optimización: el caso de la matriz de permisos

La matriz de permisos muestra **roles × módulos × permisos** (13 × 19 × 4 = 988
checkboxes). Renderizarla completa en el servidor generaba **2.3 MB de HTML**.

**Qué hicimos (optimización real):**
1. **Filtrar roles** que no usan el sistema (Chofer, Mecánico, Obreros,
   Administrativo) con `app/Config/roles_sistema.php`.
2. **No renderizar los toggles en el servidor**: el servidor manda solo las
   cards de rol; el JS pide **un JSON compacto** (`api/permisos/matriz`, ~2.4 KB)
   y renderiza **bajo demanda**: la matriz de un rol se construye al **expandir**
   su card.

**Resultado**: HTML **2.3 MB → 17 KB** (99.3 % menos); el DOM solo crece para los
roles que abres. Esto es un ejemplo de "lazy rendering" (render perezoso).

---

<a name="18"></a>
## 18. Estándares y conceptos aplicados (resumen)

| Concepto | Qué es / de dónde viene | Dónde se usa |
|---|---|---|
| MVC | Arquitectura de separación de responsabilidades | Toda la app |
| Front Controller | Un único punto de entrada | `index.php` |
| PSR-4 | Estándar de autoloading de PHP-FIG | `composer.json` |
| 12-Factor (config) | Configuración por entorno | `.env` + phpdotenv |
| HTTP status codes | Semántica de respuestas (RFC 9110) | `Respuesta`, `ErrorCodes` |
| CORS | Acceso entre orígenes (W3C) | `index.php` |
| Prepared statements | Defensa contra inyección SQL (OWASP) | Todos los modelos |
| RSA / PKCS#1 v1.5 | Cifrado asimétrico y su padding (RFC 8017) | Login (JSEncrypt/OpenSSL) |
| bcrypt | Hashing adaptativo con salt | `password_hash/verify` |
| JWT RS256 | Token firmado asimétricamente (RFC 7519) | `JwtHandler` |
| Refresh token + rotación | Renovación de sesión (OAuth2 RFC 6749 / OWASP) | `JwtHandler` |
| Session fixation / `session_regenerate_id` | Ataque y su mitigación (OWASP) | `loginController` |
| `HttpOnly` / `SameSite` | Seguridad de cookies (CSRF/XSS) | Cookies JWT |
| XSS + `htmlspecialchars` | Inyección de scripts y su defensa | Vistas |
| RBAC | Control de acceso basado en roles | `Autorizacion` |
| Token Bucket | Algoritmo de rate limiting | `RateLimitMiddleware` |
| SSE | Server-Sent Events (W3C) | `sse/notificaciones` |
| SSE/HTTP descarga | Puertas especiales | `sse/*`, `respaldo/descargar` |
| SSR / lazy rendering | Render en servidor / bajo demanda | Vistas / matriz de permisos |
| Migraciones idempotentes | SQL que se puede correr varias veces | `docs/bd/*.sql` |

---

<a name="19"></a>
## 19. Diferencias con DIRPOLES_4 y por qué ahora funciona

### 19.1 Tabla comparativa

| Aspecto | DIRPOLES_4 (viejo) | DIRPOLES-4 (ahora) |
|---|---|---|
| Formato de errores | `echo json_encode(['exito'=>false,'mensaje'=>...])` en cada función | Contrato único vía `Respuesta` + handler global |
| Modelos ante errores | Devolvían `['exito'=>false]` / `['status'=>false]` | **Lanzan** `ExcepcionApi` (el controlador no formatea) |
| Códigos de error | Strings sueltos e inconsistentes | `ErrorCodes` central y **estable** |
| Permisos | 6 líneas repetidas por función (boilerplate) | `Autorizacion::verificar()` en 1 línea |
| Configuración | Constantes en `app/Config/config.php` | `.env` (phpdotenv) |
| Auditoría | Acciones del arreglo no coincidían con el ENUM → fallaba en silencio | `Bitacora::ACCIONES` = ENUM exacto (+`Respaldo`) |
| Refresh token | No rotaba | Rotación one-time use con transacción |
| Intentos de login | En `$_SESSION` (evadible) | Persistidos en `login_intentos` |
| `RateLimit` 429 | JSON ad-hoc, esquema hardcodeado | Contrato + `Retry-After` |
| Rutas | Endpoints sueltos (`validar_pnf`, `bitacora_data_json`) | Convención `modulo/accion` y `api/modulo/accion` |
| Frontend JS | Un archivo por pantalla, validaciones duplicadas | `core/` + `modulos/` con `validaciones.js` reutilizable |
| Vistas | `<style>`/`<script>` inline | CSS/JS en `dist/` (separación de capas) |
| Duplicación | 7 catálogos con lógica repetida (1147 líneas) | 1 config + 1 modelo genérico (~150 líneas) |

### 19.2 Por qué "ahora sí funciona" (argumento técnico)

1. **Un solo camino para cada cosa.** Éxito → `Respuesta::exito`; error → una
   excepción con metadatos; formato decidido por el Core. No hay 5 variantes que
   el frontend deba adivinar.
2. **Separación estricta de capas.** El controlador no hace SQL; el modelo no
   imprime; la vista no consulta. Cada bug vive en una sola capa y es localizable.
3. **Reglas en dos capas.** El cliente guía; el servidor decide. La validación
   del modelo es la autoridad (no se puede saltar llamando la API directo).
4. **Menos duplicación.** Motor genérico para catálogos; validaciones
   compartidas; `Autorizacion`/`Bitacora`/`Notificador` en una línea. Menos código
   → menos bugs.
5. **Seguridad por defecto.** Prepared statements (inyección), bcrypt (password
   storage), RSA (clave en tránsito), JWT RS256, rotación de refresh, RBAC,
   rate limit, cookies HttpOnly/SameSite, escapado anti-XSS, `session_regenerate_id`.
6. **Errores ya no se tragan.** Modelos que lanzan + handler global + log real;
   se acabaron los `return false` silenciosos.
7. **Rendimiento consciente.** Carga selectiva de assets y render bajo demanda
   (matriz de permisos 2.3 MB → 17 KB).

---

<a name="20"></a>
## 20. Cómo explicarlo en una defensa (preguntas típicas)

**"¿Por qué no usaron un framework?"**
> El proyecto es un MVC propio para demostrar el dominio del patrón y de PHP
> puro. Mantenemos separación de capas y estándares (PSR-4, prepared statements,
> contrato HTTP) sin la magia de un framework.

**"¿Cómo protegen la contraseña?"**
> Nunca viaja en claro: se cifra en el navegador con la llave pública RSA
> (PKCS#1) y el servidor la descifra con su privada; en BD se guarda un hash
> bcrypt con salt. La verificación es `password_verify`, no una comparación
> directa.

**"¿Qué es el JWT y por qué RS256?"**
> Es un token firmado (RFC 7519). RS256 firma con la privada y verifica con la
> pública, así solo el servidor puede emitir tokens y cualquier cliente verificar
> sin conocer la clave de firma.

**"¿Qué pasa si roban un refresh token?"**
> El refresh token rota: al usarse se revoca. Una copia robada deja de servir
> cuando el dueño la usa, y si el atacante la usa primero, el dueño es forzado a
> re-autenticarse.

**"¿Cómo evitan la inyección SQL?"**
> Con sentencias preparadas (PDO) en el 100 % de las consultas; los valores se
> envían separados del SQL, así MySQL nunca los interpreta como código.

**"¿Cómo evitan el XSS?"**
> Escapando toda salida dinámica con `htmlspecialchars()` y guardando datos
> validados; además el JWT va en cookie HttpOnly.

**"¿Por qué el error 409?"**
> Es un conflicto: el recurso ya existe (duplicado) o no puede borrarse por tener
> dependencias. Lo devuelve `ExcepcionApi::yaExiste/enUso`.

**"¿Por qué paginan con LIMIT/OFFSET y `PDO::PARAM_INT`?"**
> Por seguridad y correctitud: LIMIT/OFFSET deben ser enteros; bindearlos como
> enteros evita que MySQL los rechace o se inyecte algo.

**"¿Por qué la matriz de permisos no pesa?"**
> No se renderiza completa en el servidor. El servidor manda las cards y un JSON
> compacto; el JS arma cada rol **al expandirlo** (lazy rendering).

---

### Cierre

La diferencia de fondo no es "usar tal función", sino **tener reglas únicas y
respetarlas en todas las capas**: formato de respuesta, manejo de errores,
validación, autorización y seguridad. Eso es lo que hace que este DIRPOLES-4 sea
predecible, auditable y defendible técnicamente.
