# GUIA-MODULOS.md — Cómo crear tu primer módulo en DIRPOLES-4

Esta es la guía de trabajo diario del esqueleto. Explica el flujo completo para
agregar un módulo de negocio (usaremos **Productos** como ejemplo) respetando el
MVC híbrido y las reglas de la universidad.

---

## 0. Las reglas de oro (innegociables)

1. **Controllers = solo funciones.** Nunca clases ni `new` dentro de un controlador. Las clases viven en `app/Models/` y `app/Core/`.
2. **El controlador habla HTTP.** Toma `$_POST`/`php://input`, llama al modelo con `manejarAccion()`, y responde con `Respuesta::exito()` o deja que las excepciones suban al handler global.
3. **El modelo habla SQL y lanza excepciones.** Nunca devuelve `['estado' => 'error']`: lanza `ExcepcionApi`. El `return` es el camino feliz; el `throw` es el camino de error.
4. **JSON solo por la puerta `api/*`.** Las páginas web renderizan HTML. Nunca mezclar.
5. **Códigos de error nuevos → `app/Core/ErrorCodes.php`.** Nunca strings sueltos.
6. **Auditoría y notificación en una línea** con `Bitacora::registrar()` y `Notificador::enviar()`.

### El ciclo de vida de una petición

```
Navegador/App
   │  http://localhost/DIRPOLES-4/api/productos/crear
   ▼
index.php          → CORS (.env) → sesión → .env → config → handler global
   ▼
routes.php         → middlewares globales: RateLimit → SessionAuth
   ▼
app/routes/*.php   → matchea la ruta → load_controller('productosController.php')
   ▼
productosController.php
   │  Autorizacion::verificar('productos', 'crear');   ← RBAC (lanza 403 si no)
   │  $modelo->__set(...); $datos = $modelo->manejarAccion('crear');
   │  Bitacora::registrar(...); Notificador::enviar(...);
   ▼
Respuesta::exito($datos)   →  { "exito": true, "datos": ... }
   (si el modelo lanzó ExcepcionApi → { "exito": false, "error": {...} })
```

---

## 1. Identificar el módulo en la base de datos

**Ya NO hace falta insertar módulos**: los 20 módulos del sistema ya existen
en la tabla `modulo` (BD `dirpoles_security`). Tu trabajo es identificar el
`id_modulo` real de TU módulo y usarlo en el sidebar, en `Autorizacion` y en
los mensajes de bitácora:

| id | Módulo | id | Módulo |
|---|---|---|---|
| 1 | Empleados | 11 | Jornadas |
| 2 | Beneficiarios | 12 | Mobiliario |
| 3 | Citas | 13 | Transporte |
| 4 | Psicologia | 14 | Configuracion |
| 5 | Medicina | 15 | Reportes |
| 6 | Orientacion | 16 | Bitacora |
| 7 | Trabajador Social | 17 | Permisos |
| 8 | Discapacidad | 18 | Horarios |
| 9 | Inventario Medico | 19 | Notificaciones |
| 10 | Referencias | 20 | Perfil |

```sql
-- Verificar el id del módulo con el que vas a trabajar (ya existe)
SELECT id_modulo FROM modulo WHERE nombre = 'Inventario Medico';
```

> En esta guía **"Productos" es solo un ejemplo ilustrativo** de nombres y
> campos; en la práctica trabaja sobre uno de los módulos existentes de la
> tabla anterior. No crear módulos nuevos por el momento.

**Permisos** (tabla `rol_modulo_permiso`; permisos: **1=Crear, 2=Leer,
3=Editar, 4=Eliminar**): el módulo debe tener filas para tu rol o no se verá
en el sidebar. Si falta algún permiso, asígnalo desde la pantalla de
**Permisos** del administrador o con un SQL puntual:

```sql
-- Ejemplo: dar todos los permisos al rol Administrador (1) sobre Inventario (9)
INSERT INTO rol_modulo_permiso (id_tipo_emp, id_modulo, id_permiso)
VALUES (1, 9, 1), (1, 9, 2), (1, 9, 3), (1, 9, 4);
```

---

## 2. Las rutas — `app/routes/productos.php`

Un archivo por módulo. Se auto-carga solo (el `glob()` de `app/routes.php` lo lee).

```php
<?php
// app/routes/productos.php

use App\Core\Router;

// ---------- PUERTA HTML (páginas renderizadas) ----------
Router::get('productos/consultar', function () {
    load_controller('productosController.php');
    showConsultarProductos();          // página con DataTable
});

Router::get('productos/crear', function () {
    load_controller('productosController.php');
    showCrearProducto();               // formulario
});

// ---------- PUERTA JSON (API, prefijo api/) ----------
Router::get('api/productos/listar', function () {
    load_controller('productosController.php');
    apiListarProductos();
});

Router::post('api/productos/crear', function () {
    load_controller('productosController.php');
    apiCrearProducto();
});
```

Convenciones de rutas:
- Páginas: `productos/accion` → GET → devuelven HTML (renderizan vista).
- API: `api/productos/accion` → GET para leer, POST para escribir → devuelven JSON.
- Parámetros en URL: `'productos/ver/{id}'` — el Router inyecta el valor en
  `$_GET['id']`; el controlador lo lee desde ahí (no como argumento del closure).

---

## 3. El controlador — `app/Controllers/productosController.php`

Solo funciones. Es el ÚNICO que toca HTTP y el ÚNICO que responde.

```php
<?php
// app/Controllers/productosController.php

use App\Models\ProductoModel;
use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Notificador;
use App\Core\Respuesta;
use App\Core\ExcepcionApi;

// ==================== PUERTA HTML ====================

/** Página: consultar productos (tabla). */
function showConsultarProductos(): void
{
    Autorizacion::verificar('productos', 'leer');
    require_once BASE_PATH . '/app/Views/productos/consultar.php';
}

/** Página: formulario de creación. */
function showCrearProducto(): void
{
    Autorizacion::verificar('productos', 'crear');
    require_once BASE_PATH . '/app/Views/productos/crear.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: listar productos. */
function apiListarProductos(): void
{
    Autorizacion::verificar('productos', 'leer');

    $modelo = new ProductoModel();
    Respuesta::exito($modelo->manejarAccion('listar'));
    // Si el modelo lanza ExcepcionApi, NO la atrapamos: el handler global
    // de index.php la convierte en {exito:false, error:{...}} automáticamente.
}

/** API: crear producto. */
function apiCrearProducto(): void
{
    Autorizacion::verificar('productos', 'crear');

    // 1. El controlador lee HTTP (JSON del body o POST de formulario)
    $entrada = json_decode(file_get_contents('php://input'), true)
        ?? $_POST;

    // 2. Delega al modelo (el modelo valida y lanza si algo falla)
    $modelo = new ProductoModel();
    $modelo->__set('nombre',  $entrada['nombre']  ?? '');
    $modelo->__set('precio',  $entrada['precio']  ?? 0);
    $modelo->__set('stock',   $entrada['stock']   ?? 0);
    $nuevo = $modelo->manejarAccion('crear');

    // 3. Efectos secundarios en una línea cada uno (nunca lanzan)
    Bitacora::registrar(
        'Productos',
        'Registro',
        'Creó el producto "' . $nuevo['nombre'] . '"'
    );
    Notificador::enviar(
        (int) ($_SESSION['id_empleado']),
        'Producto creado',
        'productos/consultar',
        'info'
    );

    // 4. Respuesta única y estándar
    Respuesta::exito($nuevo, 201);
}
```

Puntos clave:
- `Autorizacion::verificar()` es SIEMPRE la primera línea. Lanza 403 solo si falta el permiso.
- **No hay try/catch para formatear errores**: si `manejarAccion('crear')` lanza
  `ExcepcionApi::yaExiste('El producto ya existe')`, el handler global responde
  `409 {exito:false, error:{codigo:'ALREADY_EXISTS', estado:409, mensaje:'...'}}`.
  Atrapa excepciones SOLO si necesitas lógica adicional antes de responder.
- `Respuesta::exito()` / `Respuesta::error()` terminan la ejecución (`exit`), no hace falta `return`.

---

## 4. El modelo — `app/Models/ProductoModel.php`

Aquí van las clases. Extiende `BusinessModel` (datos transaccionales) o
`SecurityModel` (usuarios/permisos/bitácora). Patrón obligatorio:
`__set` valida → `manejarAccion()` despacha → métodos privados hacen el SQL.

> **Unicidad global de identidad**: cédula (tipo+documento), correo y
> teléfono son únicos entre `empleado`, `beneficiario` y `proveedores`.
> Si tu módulo guarda alguno de esos datos, valida contra las tres tablas
> con el patrón `UNION ALL` cross-schema de `EmpleadoModel::existeX()` /
> `BeneficiarioModel::existeX()` (regla completa en `AGENTS.md` §7). El
> mensaje de duplicado es genérico: "ya está registrado en el sistema"
> (no digas en qué módulo).

```php
<?php
// app/Models/ProductoModel.php

namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;
use App\Core\ErrorCodes;

class ProductoModel extends BusinessModel
{
    private $atributos = [];

    // ---------- Capa de validación (se ejecuta en CADA asignación) ----------
    public function __set($nombre, $valor)
    {
        switch ($nombre) {
            case 'nombre':
                $valor = trim((string) $valor);
                if ($valor === '' || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El nombre es obligatorio (máx. 100 caracteres).');
                }
                break;

            case 'precio':
                if (!is_numeric($valor) || $valor < 0) {
                    throw ExcepcionApi::validacion('El precio debe ser un número mayor o igual a 0.');
                }
                $valor = (float) $valor;
                break;

            case 'stock':
                if (!filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) {
                    throw ExcepcionApi::validacion('El stock debe ser un entero mayor o igual a 0.');
                }
                $valor = (int) $valor;
                break;
        }

        $this->atributos[$nombre] = $valor;
    }

    public function __get($atributo)
    {
        return $this->atributos[$atributo] ?? null;
    }

    // ---------- Despachador ----------
    public function manejarAccion($accion)
    {
        switch ($accion) {
            case 'listar':  return $this->listar();
            case 'crear':   return $this->crear();
            case 'obtener': return $this->obtener();
            default:
                throw ExcepcionApi::errorInterno("Acción no válida: {$accion}");
        }
    }

    // ---------- Acciones (SQL + reglas de negocio) ----------

    private function listar(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_producto, nombre, precio, stock
                 FROM producto
                 ORDER BY nombre ASC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 50), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al listar productos.');
        }
    }

    private function crear(): array
    {
        try {
            // Regla de negocio: el nombre no puede repetirse
            if ($this->existeNombre($this->__get('nombre'))) {
                throw ExcepcionApi::yaExiste('Ya existe un producto con ese nombre.');
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO producto (nombre, precio, stock)
                 VALUES (:nombre, :precio, :stock)"
            );
            $stmt->execute([
                ':nombre' => $this->__get('nombre'),
                ':precio' => $this->__get('precio'),
                ':stock'  => $this->__get('stock'),
            ]);

            return [
                'id_producto' => (int) $this->conn->lastInsertId(),
                'nombre'      => $this->__get('nombre'),
                'precio'      => $this->__get('precio'),
                'stock'       => $this->__get('stock'),
            ];
        } catch (PDOException $e) {
            throw $this->mapearErrorBd($e, 'Error al crear el producto.');
        }
    }

    private function obtener(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id_producto, nombre, precio, stock FROM producto WHERE id_producto = :id"
        );
        $stmt->bindValue(':id', (int) $this->__get('id'), PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw ExcepcionApi::noEncontrado('El producto solicitado no existe.');
        }
        return $producto;
    }

    // ---------- Helpers privados (tu "repository" dentro del modelo) ----------

    private function existeNombre(string $nombre): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM producto WHERE nombre = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Traduce errores técnicos de MySQL a ExcepcionApi entendible.
     * 23000 = constraint violation (duplicado / FK en uso).
     */
    private function mapearErrorBd(PDOException $e, string $mensaje): ExcepcionApi
    {
        error_log('ProductoModel: ' . $e->getMessage());
        if ($e->getCode() === '23000') {
            throw ExcepcionApi::enUso('El registro está relacionado con otros datos.');
        }
        return ExcepcionApi::errorInterno($mensaje);
    }
}
```

Puntos clave:
- `throw ExcepcionApi::...` SIEMPRE. Nunca `return ['estado' => 'error']`.
- `LIMIT/OFFSET` SIEMPRE con `bindValue(..., PDO::PARAM_INT)`.
- Los métodos privados de consulta son tu "repository": el método público de
  `manejarAccion()` orquesta (como un "service"), los privados hacen SQL.
- Si una acción necesita datos de negocio, se agrega una NUEVA acción al
  `switch` de `manejarAccion()`. Nunca se exponen los métodos privados.

---

## 5. La vista — `app/Views/productos/consultar.php`

Copia `app/Views/inicio/dashboard.php` como plantilla. Estructura:

```php
<?php
// app/Views/productos/consultar.php
$titulo = "Productos";                       // ← ANTES de head.php
include 'app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Productos</h1>

                    <table id="tablaProductos" class="table table-striped"></table>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <!-- JS del módulo al final, con defer, en orden de dependencia -->
    <script src="<?= BASE_URL ?>dist/js/modulos/productos/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/productos/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/productos/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/productos/crear.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/productos/consultar.js" defer></script>
</body>
</html>
```

Reglas de vistas:
- `$titulo` se define antes de `head.php` (lo usa `script.php` para no cargar
  FullCalendar/DataTables en el login).
- Todo dato dinámico impreso va con `htmlspecialchars()` o `<?= ... ?>` de datos ya confiables.
- El JS del módulo vive en `dist/js/modulos/<modulo>/` (**carpeta en singular**)
  dividido por pantalla: `validaciones.js`, `tour.js`, `stats.js`, `crear.js`,
  `editar.js`, `consultar.js` — incluidos con `defer` al final, en ese orden.
- Si el módulo tiene formulario: `novalidate`, errores junto a cada campo
  (`<div class="form-text text-danger">`), clases `is-valid`/`is-invalid`,
  Select2 (`.select2` + `data-placeholder`) y botón `#btn-ayuda` conectado al tour.
- Si el módulo tiene tabla: `<table id="tabla">` vacía (tbody vacío) para DataTables.

---

## 6. El JS del módulo — `dist/js/modulos/<modulo>/`

Carpeta en singular (como `empleado/`, `cita/`, `horario/`) con un archivo por
pantalla, incluidos con `defer` en la vista en este orden: `stats.js`,
`validaciones.js`, `tour.js`, `editar.js`/`crear.js`, `consultar.js`.

- `validaciones.js` — validaciones reutilizables crear/editar
  (`ModuloValidaciones.configurar(form, {idExcluir})`), en vivo con `input`/`change`,
  remotas con `apiFetch` + debounce; si la API falla → rojo "No se pudo
  verificar…" + `return false` (nunca verde).
- `tour.js` — Driver.js con `ModuloTour.iniciar()`, botón `#btn-ayuda`; para
  `<select class="select2">` apuntar a `select.nextElementSibling`.
- `stats.js` — tarjetas `[data-stat]` con UNA sola llamada a `api/<modulo>/stats`.
- `crear.js` / `editar.js` — solo la lógica de esa pantalla; tras guardar,
  éxito con AlertManager/SweetAlert2 y refresco de stats/tabla sin recarga.
- `consultar.js` — pinta filas con `apiFetch` (texto escapado), destruye la
  instancia anterior y reinicia `$('#tabla').DataTable()` con idioma local y
  botones `excelHtml5`/`pdfHtml5` excluyendo la columna Acciones.

El helper `apiFetch` **ya existe** en `dist/js/core/apiFetch.js` (lo carga
`template/script.php`): no lo copies ni lo redefinas, solo úsalo. Devuelve
`cuerpo.datos` en éxito y lanza `error.codigo` en fallo:

```javascript
// dist/js/modulos/productos/crear.js
async function crearProducto(datos) {
    try {
        await apiFetch(BASE_URL + 'api/productos/crear', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos),
        });
        Swal.fire('¡Listo!', 'Producto creado', 'success');
    } catch (error) {
        // Programa contra error.codigo (estable), nunca contra mensaje
        if (error.codigo === 'ALREADY_EXISTS') {
            Swal.fire('Duplicado', error.mensaje, 'info');
        } else {
            Swal.fire('Error', error.mensaje, 'error');
        }
    }
}
```

---

## 7. La entrada del sidebar — `app/Config/modulos_sidebar.php`

```php
return [
    // ⬇️ Clave = id_modulo REAL de tu módulo (tabla del paso 1; los ids 1–20
    // ya están ocupados). Reemplaza 9 por el id que te devolvió tu SELECT.
    9 => [
        'key'    => 'productos',
        'icon'   => 'fa-box',
        'titulo' => 'Productos',
        'subitems' => [
            // 'permiso' => 2 (Leer): controla la VISIBILIDAD del subitem
            ['url' => 'productos/crear',      'texto' => 'Crear',      'permiso' => 2],
            ['url' => 'productos/consultar',  'texto' => 'Consultar',  'permiso' => 2],
        ],
    ],
];
```

El sidebar muestra el grupo solo si el rol tiene permiso "Leer" sobre ese
`id_modulo` (verificado contra `$_SESSION['modulosPermitidos']`, que llena
`showInicio()` al cargar el dashboard).

---

## 8. Checklist final del módulo

- [ ] `id_modulo` real identificado en la tabla `modulo` (ya existen; sin INSERT) y permisos del rol en `rol_modulo_permiso`
- [ ] `app/routes/productos.php` con páginas (HTML, GET) y API (`api/…`, GET leer / POST escribir)
- [ ] `app/Controllers/productosController.php`: solo funciones; `Autorizacion::verificar()` primera línea de cada función
- [ ] `app/Models/ProductoModel.php`: `__set` valida, `manejarAccion()` despacha, `throw ExcepcionApi` para errores
- [ ] Bitácora con `Bitacora::registrar()` en toda operación de escritura
- [ ] Vista(s) copiando un módulo existente, `$titulo` antes de `head.php`, validación visual, Select2 y `#btn-ayuda`
- [ ] JS en `dist/js/modulos/<modulo>/` dividido por pantalla, usando `apiFetch`, `data-stat`, DataTables y programando contra `error.codigo`
- [ ] Entrada en `app/Config/modulos_sidebar.php` con el `id_modulo` real
- [ ] Card en `app/Config/dashboard_cards.php` (marcar `'disponible' => true`)
- [ ] `php -l` sobre cada PHP nuevo y `node --check` sobre cada JS nuevo (entregar los comandos al usuario, sin ejecutarlos)

---

## 9. Chuleta de códigos de error

| Código (`error.codigo`) | HTTP | Cuándo usarlo (fábrica) |
|---|---|---|
| `VALIDATION_ERROR` | 400 | `ExcepcionApi::validacion('...')` |
| `INVALID_JSON` | 400 | `ExcepcionApi::jsonInvalido()` |
| `NOT_FOUND` | 404 | `ExcepcionApi::noEncontrado('...')` |
| `ALREADY_EXISTS` | 409 | `ExcepcionApi::yaExiste('...')` |
| `IN_USE` | 409 | `ExcepcionApi::enUso('...')` |
| `ACCESS_DENIED` | 403 | `ExcepcionApi::accesoDenegado()` (lo lanza `Autorizacion`) |
| `UNAUTHENTICATED` | 401 | lo lanza el middleware / `Autorizacion` |
| `RATE_LIMIT_EXCEEDED` | 429 | lo lanza el `RateLimitMiddleware` |
| `INTERNAL_SERVER_ERROR` | 500 | `ExcepcionApi::errorInterno()` |

¿Falta uno? Agrégalo como constante en `app/Core/ErrorCodes.php` (con su
comentario de estado HTTP) y crea una fábrica en `ExcepcionApi` si tiene caso propio.

---

## 10. Recuperar código del sistema antiguo

La historia de git conserva el sistema completo. Rescata lo que necesites:

```bash
# ¿Qué había?
git ls-tree HEAD~1 --name-only app/Controllers/
git ls-tree HEAD~1 --name-only app/Models/

# Rescatar un archivo completo
git show HEAD~1:app/Controllers/beneficiarioController.php > app/Controllers/beneficiarioController.php
git show HEAD~1:app/Views/beneficiarios/ -r --name-only   # lista las vistas de ese módulo

# Ver un archivo sin restaurarlo
git show HEAD~1:app/Models/BeneficiarioModel.php | less
```

> Al rescatar un módulo viejo, **adáptalo a las convenciones nuevas**:
> reemplaza los `echo json_encode(...)` ad-hoc por `Respuesta::exito()`, los
> `['estado' => 'error']` del modelo por `throw ExcepcionApi::...`, y los
> bloques de PermisosModel/Bitacora por `Autorizacion::verificar()` /
> `Bitacora::registrar()`.
