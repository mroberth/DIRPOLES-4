# AGENTS.md — Contexto para IA (DIRPOLES-4)

> Este archivo define las reglas inmutables, la arquitectura y los contratos
> técnicos de DIRPOLES-4. Toda IA o agente de desarrollo debe seguir este
> documento con estricta rigurosidad. El código y la documentación se redactan
> en **español**.
>
> 📖 Para reglas de negocio específicas de cada módulo existente, consulta
> **`docs/REGLAS_MODULOS.md`**.

---

## 1. Qué es este repositorio

**DIRPOLES-4** es el sistema base para la gestión integral de estudiantes beneficiarios
de la **UPTAEB** (Proyecto de Grado de Ingeniería en Informática, PNF Informática).
Es un monolito híbrido en **PHP 8+ con MVC propio** (sin frameworks como Laravel o Symfony),
con frontend renderizado en servidor y componentes dinámicos con AJAX/Fetch.

---

## 2. Reglas innegociables (Exigencias académicas y de diseño)

1. **Controllers = SOLO funciones procesales.** Jamás declarar clases en controladores (`class XController`). No instanciar lógica de clase dentro del controlador.
2. **Clases solo en `app/Models/` y `app/Core/`.** Excepción documentada: procesadores de PDF en `docs/PDF/*` (`GenerarPDF`, `GenerarConstancia`, `GenerarReferencia`, `GenerarRecipe`).
3. **NO introducir capas Service, Repository o UseCase.** La lógica de orquestación vive en el despachador `manejarAccion()`; las consultas SQL viven en métodos privados del Modelo.
4. **Frontend renderizado en servidor.** PHP + plantillas en `app/Views/template/`. Prohibido convertir el proyecto en SPA o desacoplar frontend/backend.
5. **Doble base de datos:** `dirpoles_security` (usuarios, RBAC, bitácora, tokens) y `dirpoles_business` (operaciones de negocio).
6. **Despachador `manejarAccion($accion)`** como único punto de entrada público a los modelos.
7. **Autocontenido:** Prohibido agregar librerías externas vía CDN (`<script src="https://...">`) o dependencias npm/composer no justificadas. Todas las librerías residen en `/plugins` y `/dist`.
8. **Consultar el sistema viejo (`../DIRPOLES_4`) solo como referencia funcional**, nunca copiar su código, estilo o estructura obsoleta.
9. **Criterio de completitud:** Un módulo debe incluir permisos RBAC, validación frontend/backend, estados visuales, tour Driver.js, estadísticas, consulta con DataTables, auditoría y comandos de verificación.
10. **PROHIBIDO EJECUTAR COMANDOS EN LA TERMINAL (OPTIMIZACIÓN DE TOKENS):**
    La IA **NUNCA** debe invocar o ejecutar herramientas de terminal (ej. `bash`, `php -l`, `node --check`, `git`, etc.) directamente. Toda ejecución automática gasta excesivos tokens de la API por reenvío de contexto. En su lugar, la IA debe limitarse a leer, buscar, analizar y editar archivos, e **indicar explícitamente en su mensaje final los comandos que el usuario debe ejecutar manualmente en su terminal**. Esto incluye no usar `run_in_terminal`, ni ejecutar comandos mediante tareas, scripts, shells o herramientas indirectas.

---

## 3. Arquitectura de respuesta y errores

### La Regla de las Puertas
| Puerta | Tipo de Ruta | Salida esperada | Ejemplo |
|---|---|---|---|
| **HTML** | Rutas fuera de `api/` | HTML renderizado o redirección | `GET /citas/consultar` |
| **JSON** | Rutas bajo `api/*` o `Accept: application/json` | JSON según contrato `Respuesta` | `POST /api/citas/crear` |
| **Descargas / Stream** | Rutas especiales dedicadas | PDF en línea, `.sql` o SSE | `sse/notificaciones`, `medicina/recipe/{id}` |

Nunca mezclar: una página web jamás escupe JSON suelto; un endpoint `api/*` jamás renderiza HTML.

### Contrato JSON Único (`App\Core\Respuesta`)
Toda respuesta de API finaliza con `Respuesta::exito()` o `Respuesta::error()` (ambos ejecutan `exit`):
```jsonc
// Éxito (HTTP 200 / 201):
{ "exito": true, "datos": { ... } }

// Error (HTTP 400, 401, 403, 404, 409, 500):
{ "exito": false, "error": { "codigo": "ALREADY_EXISTS", "estado": 409, "mensaje": "..." } }
```

### Manejo de Excepciones
- Los modelos **LANZAN** excepciones, nunca devuelven arrays de error (`return ['error' => ...]`).
- Usar las fábricas de `App\Core\ExcepcionApi`:
  - `ExcepcionApi::validacion($mensaje)` → HTTP 400 (`VALIDATION_ERROR`)
  - `ExcepcionApi::noEncontrado($mensaje)` → HTTP 404 (`NOT_FOUND`)
  - `ExcepcionApi::yaExiste($mensaje)` → HTTP 409 (`ALREADY_EXISTS`)
  - `ExcepcionApi::enUso($mensaje)` → HTTP 409 (`IN_USE`, para SQLSTATE 23000)
  - `ExcepcionApi::accesoDenegado($mensaje)` → HTTP 403 (`ACCESS_DENIED`)
  - `ExcepcionApi::errorInterno($mensaje)` → HTTP 500 (`INTERNAL_SERVER_ERROR`)
- Nuevos códigos deben declararse como constantes en `App\Core\ErrorCodes.php`.

---

## 4. Anatomía de la Sesión y Autenticación

El usuario autenticado en el sistema **es un empleado**. No existe tabla `usuarios`.
Tras autenticar, `$_SESSION` expone de forma garantizada:

```php
$_SESSION = [
    'id_empleado'       => (int) 5,               // ID del empleado
    'nombre'            => (string) 'Carlos',
    'apellido'          => (string) 'Pérez',
    'correo'            => (string) 'carlos@uptaeb.edu.ve',
    'id_tipo_empleado'  => (int) 2,               // ID del rol (OJO: no es 'id_tipo_emp' ni 'rol')
    'tipo_empleado'     => (string) 'Medico',     // Nombre descriptivo del rol
    'estatus'           => (int) 1,               // 1 = Activo
    'modulosPermitidos' => (array) [...],         // Lista de permisos de navegación
];
```

- **Autenticación:** Contraseña cifrada en cliente con RSA (`login_public.pem`) → descifrada en backend (`login_private.pem`) → verificada con `password_verify` (bcrypt).
- **JWT + Refresh:** JWT RS256 en cookie HttpOnly + refresh token con rotación en BD (`refresh_tokens`, hash SHA-256).
- **Bloqueo:** 3 intentos fallidos bloquean la cuenta (persistido en tabla `login_intentos`).

---

## 5. Bases de datos, Roles y Concurrencia

### Mapeo de Tablas por Esquema
- **`dirpoles_security`** (Modelo base `SecurityModel` → `$this->conn_security`):
  `empleado`, `tipo_empleado`, `modulo`, `permiso`, `rol_modulo_permiso`, `bitacora`, `login_intentos`, `refresh_tokens`, `rate_limits`, `notificaciones`.
- **`dirpoles_business`** (Modelo base `BusinessModel` → `$this->conn`):
  `beneficiario`, `solicitud_de_servicio`, `citas`, `horarios`, `medicina`, `insumos`, `inventario_medico`, `orientacion`, `discapacidad`, `becas`, `exoneraciones`, `fames`, `embarazadas`, `referencias`, `log_referencias`, `mobiliario`, `equipos`, `fichas_tecnicas`, `rutas`, `vehiculos`, `proveedores`, `repuestos_vehiculos`, etc.

### Roles del Sistema (`id_tipo_emp`)
| ID | Rol | En matriz (`roles_sistema.php`) | Alcance principal |
|:---:|---|:---:|---|
| **1** | Psicólogo | Sí | Citas y consultas de psicología |
| **2** | Médico | Sí | Consultas médicas, recetas, jornadas |
| **3** | Trabajador Social | Sí | Becas, exoneraciones, estudios SE |
| **4** | Orientador | Sí | Orientación vocacional y conductual |
| **5** | Discapacidad | Sí | Diagnósticos y apoyo a discapacidad |
| **6** | Administrador | Sí | Control total de módulos y operaciones |
| **7** | Secretaria | Sí | Citas y apoyo administrativo |
| **8** | Chofer | No (no inicia sesión) | Asignable en transporte |
| **10** | Superusuario | Sí | Control total, permisos, respaldo BD |
| **12** | Enfermero | Sí | Salud y triaje |

### Transacciones y Concurrencia
Para operaciones críticas (stock de insumos, aforo de jornadas, asignaciones, estados):
```php
try {
    $this->conn->beginTransaction();
    // SELECT ... FOR UPDATE
    // Operaciones SQL de escritura
    $this->conn->commit();
} catch (\Throwable $e) {
    if ($this->conn->inTransaction()) {
        $this->conn->rollBack();
    }
    throw $e;
}
```
> [!WARNING]
> PDO **no soporta transacciones distribuidas** entre conexiones distintas. No mezclar `$this->conn` y `$this->conn_security` dentro de un mismo `beginTransaction()`. `Bitacora::registrar()` abre su propia conexión aislada, por lo que puede llamarse sin alterar la transacción activa.

### Unicidad Global de Identidad
Cédula, correo y teléfono son únicos entre `empleado` (security), `beneficiario` (business) y `proveedores` (business). Las comprobaciones se realizan mediante `UNION ALL` cross-schema desde la conexión de negocio calificando el esquema: `dirpoles_security.empleado`.

---

## 6. Helpers del Core y Frontend (Cheat Sheet)

### Backend (PHP)
```php
// 1. RBAC (Primera instrucción en el controlador. Lanza 403 si falla)
// Permisos: 'crear', 'leer', 'editar', 'eliminar'
Autorizacion::verificar('citas', 'crear');
$puedeLeer = Autorizacion::tiene('citas', 'leer');

// 2. Auditoría (Nunca lanza excepciones. id_empleado tomado de $_SESSION)
// Acciones válidas (ENUM estricto): 'Registro', 'Lectura', 'Actualización', 'Eliminación', 'Inicio de sesión', 'Cierre de sesión', 'Respaldo'
Bitacora::registrar('Citas', 'Registro', 'Agendó cita para el beneficiario ID 12');

// 3. Notificaciones internas (Campana SSE)
Notificador::enviar($idEmpleadoDestino, 'Nueva cita agendada', 'citas/consultar', 'info');

// 4. Respuestas API
Respuesta::exito($datos = null, $codigoHttp = 200);
Respuesta::error(\Throwable $e);

// 5. Entorno y Tercera Puerta PDF
$variable = env('DB_HOST', 'localhost');
$params = parametrosDocumento($areaRequerida = false); // ['tramite' => ..., 'hora' => ..., 'area' => ...]
```

### Frontend (JavaScript)
```javascript
// 1. Peticiones HTTP seguras (dist/js/core/apiFetch.js)
// Retorna datos directos. Lanza { codigo, estado, mensaje } ante errores HTTP o exito:false
const datos = await apiFetch(BASE_URL + 'api/modulo/listar');
await apiFetch(BASE_URL + 'api/modulo/crear', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
});

// 2. Notificaciones y Alertas (dist/js/core/AlertManager.js — Métodos en inglés)
AlertManager.success('Guardado', 'Registro completado con éxito', BASE_URL + 'modulo/consultar');
AlertManager.error('Error', error.mensaje || 'Ocurrió un error inesperado');
AlertManager.warning('Atención', 'Verifique los campos requeridos');
const confirmacion = await AlertManager.confirm('¿Eliminar?', 'Esta acción no se puede deshacer');

// 3. Select2 Dinámico (dist/js/core/select-2-init.js)
window.initSelect2(formularioOContenedor); // Ejecutar después de inyectar <option> vía AJAX
```

---

## 7. Módulos del Sistema (Resumen)

| ID | Módulo | Tabla(s) Principal(es) | BD | Permiso Especial / Alcance |
|:---:|---|---|---|---|
| **1** | Empleados | `empleado` | Security | CRUD administrativo |
| **2** | Beneficiarios | `beneficiario` | Business | CRUD global |
| **3** | Citas | `citas` | Business | Psicólogo solo ve sus propias citas |
| **5** | Medicina | `medicina`, `insumos`, `inventario_medico` | Business | Stock descontado no se revierte al borrar |
| **6** | Orientación | `orientacion`, `solicitud_de_servicio` | Business | 4 textos obligatorios, sin inventario |
| **7** | Trabajo Social | `becas`, `exoneraciones`, `fames`, `embarazadas` | Business | Estudio socioeconómico genera PDF en FPDF |
| **8** | Discapacidad | `discapacidad` | Business | Grado y tipo según ENUM estricto |
| **9** | Inventario Médico | `insumos`, `inventario_medico` | Business | Nace stock 0. Entrada/Salida con kardex |
| **10** | Referencias | `referencias`, `log_referencias` | Business | Origen vs Destino. Flujo Pendiente/Aceptada/Rechazada |
| **11** | Jornadas Médicas | `jornadas_medicas`, `jornada_asistentes` | Business | Control estricto de aforo con FOR UPDATE |
| **12** | Mobiliario | `mobiliario`, `equipos`, `fichas_tecnicas` | Business | Serial obligatorio en equipos; kardex de bajas |
| **13** | Transporte | `rutas`, `vehiculos`, `repuestos_vehiculos` | Business | No solapamiento de chofer/vehículo el mismo día |
| **14** | Configuración | Catálogos generales | Business | Catálogos y respaldo de base de datos |
| **15** | Reportes | Tablas operativas según área | Business | Filtros server-side estrictos, Chart.js local |
| **16** | Bitácora | `bitacora` | Security | Auditoría del sistema |
| **17** | Permisos | `rol_modulo_permiso` | Security | Matriz Rol × Módulo × Permiso |
| **18** | Horarios | `horarios` | Business | Exclusivo Psicología (07:00 a 17:00) |
| **19** | Notificaciones | `notificaciones` | Security | Avisos y SSE para campanita de topbar |

*(Consulta `docs/REGLAS_MODULOS.md` para las reglas exhaustivas de cada módulo)*.

---

## 8. Anti-Patrones Prohibidos (Lo que una IA NUNCA debe hacer)

- ❌ **NUNCA** declarar clases en los controladores (`class ModuloController`). Solo funciones procesales.
- ❌ **NUNCA** hacer `echo json_encode(...)` o `die()`. La salida JSON se emite únicamente vía `Respuesta::exito()` o `Respuesta::error()`.
- ❌ **NUNCA** devolver arrays de error en modelos (`return ['exito' => false]`). Lanzar `throw ExcepcionApi::...`.
- ❌ **NUNCA** inventar strings de códigos de error. Usar o registrar la constante en `ErrorCodes.php`.
- ❌ **NUNCA** leer el ID del usuario emisor desde el cliente (`$_POST['id_empleado']`) cuando la operación es propia; tomarlo siempre de `$_SESSION['id_empleado']`.
- ❌ **NUNCA** asumir que existe una tabla `usuarios`. El usuario es la tabla `empleado`.
- ❌ **NUNCA** usar librerías externas por CDN o agregar paquetes que rompan la autocontención del proyecto.
- ❌ **NUNCA** mezclar las puertas: una ruta de página web nunca debe retornar JSON; un endpoint `api/*` nunca debe retornar HTML.

---

## 9. Plantillas Canónicas (Boilerplates)

### Controlador (`app/Controllers/ejemploController.php`)
```php
<?php
use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Respuesta;
use App\Models\EjemploModel;

function crear(): void {
    Autorizacion::verificar('ejemplo', 'crear');
    $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $modelo = new EjemploModel();
    foreach ($datos as $clave => $valor) {
        $modelo->__set($clave, $valor);
    }

    $idGenerado = $modelo->manejarAccion('crear');
    Bitacora::registrar('Ejemplo', 'Registro', "Creó el registro ID: $idGenerado");
    Respuesta::exito(['id' => $idGenerado], 201);
}
```

### Modelo (`app/Models/EjemploModel.php`)
```php
<?php
namespace App\Models;
use App\Core\ExcepcionApi;
use Throwable;

class EjemploModel extends BusinessModel {
    private ?int $id = null;
    private ?string $nombre = null;

    public function __set(string $propiedad, mixed $valor): void {
        switch ($propiedad) {
            case 'id':
                $this->id = filter_var($valor, FILTER_VALIDATE_INT) ?: null;
                break;
            case 'nombre':
                $v = trim((string)$valor);
                if ($v === '') throw ExcepcionApi::validacion('El nombre es requerido.');
                $this->nombre = $v;
                break;
            default:
                throw ExcepcionApi::validacion("Propiedad no reconocida: $propiedad");
        }
    }

    public function __get(string $propiedad): mixed {
        return $this->$propiedad ?? null;
    }

    public function manejarAccion(string $accion): mixed {
        return match ($accion) {
            'crear'     => $this->crearSql(),
            'consultar' => $this->consultarSql(),
            default     => throw ExcepcionApi::validacion("Acción no válida: $accion"),
        };
    }

    private function crearSql(): int {
        $stmt = $this->conn->prepare("INSERT INTO ejemplo (nombre) VALUES (:nombre)");
        $stmt->bindValue(':nombre', $this->nombre);
        $stmt->execute();
        return (int) $this->conn->lastInsertId();
    }
}
```

### Rutas (`app/routes/ejemplo.php`)
```php
<?php
// app/routes/ejemplo.php
$router->get('/ejemplo/consultar', function() {
    load_controller('ejemploController.php');
    consultarVista();
});

$router->post('/api/ejemplo/crear', function() {
    load_controller('ejemploController.php');
    crear();
});
```

---

## 10. Protocolo de Trabajo y Verificación

Cuando se solicite crear, modificar o corregir un módulo:
1. **Inspeccionar:** Consultar `docs/bd/` para tablas e índices, y `docs/REGLAS_MODULOS.md` para reglas del área.
2. **Implementar:** Seguir la secuencia Modelo (`__set` + `manejarAccion`) → Controlador (solo funciones + `Autorizacion::verificar`) → Rutas (`api/*` vs vistas) → Vistas y JS (`apiFetch`, `AlertManager`, `tour.js`, `DataTables`).
3. **Respetar la Regla 10 (Sin comandos automáticos):** La IA no ejecutará la terminal. La respuesta final **SIEMPRE** entregará al usuario los comandos exactos para que los ejecute en su terminal local:
   ```text
   php -l app/Models/TuModeloModel.php
   php -l app/Controllers/tuModuloController.php
   php -l app/routes/tu_modulo.php
   node --check dist/js/modulos/tu_modulo/crear.js
   ```
4. **Resumen al usuario:** Detallar archivos modificados, reglas implementadas y pruebas manuales pendientes.
