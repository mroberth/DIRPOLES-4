# INFORME DE AUDITORÍA INTERNA DE SEGURIDAD Y ARQUITECTURA
**Proyecto:** DIRPOLES-4 (Esqueleto Base y Módulos Transaccionales)  
**Institución:** UPTAEB — PNF en Informática (Trayecto IV)  
**Fecha:** 2026-10-01  
**Tipo de Auditoría:** Estática de Código Fuente (White-Box Code Review)  
**Clasificación:** Confidencial / Interna  

---

## 1. Resumen Ejecutivo
- **Dictamen General:** Favorable con Observaciones (Aprobado para Producción y Defensa)
- **Nivel de Seguridad Global:** Excelente (Arquitectura defensiva multicapa, cero vulnerabilidades abiertas)
- **Resumen Estadístico Consolidado de Hallazgos:**
  - 🔴 **Críticos:** 0 (Sin fallos de inyección SQL, bypass de autenticación, BOLA/IDOR ni Path Traversal).
  - 🟠 **Altos:** 0 pendientes (1 corregido: [HALLAZGO-SEC-01] Refresh Token con hash SHA-256 en BD).
  - 🟡 **Medios:** 0 pendientes (3 corregidos: [HALLAZGO-SEC-02] Flag `secure` dinámico en cookies; [HALLAZGO-SEC-05] Constraints `UNIQUE` e `ExcepcionApi::yaExiste()` en Transporte; [HALLAZGO-SEC-08] Validación MIME estricta con `finfo` en Foto del Estudio SE).
  - 🟢 **Bajos / Informativos:** 7 ([HALLAZGO-SEC-03], [HALLAZGO-SEC-04], [HALLAZGO-SEC-06], [HALLAZGO-SEC-07], [HALLAZGO-SEC-09], [HALLAZGO-SEC-10] y [HALLAZGO-SEC-11] Verificaciones positivas de robustez en JWT, gestión de errores, orden SQL `SET`, bloqueos `FOR UPDATE`, aislamiento de datos por usuario, contención de archivos y Whitelist en reportes).

---

## 2. Evaluación de Conformidad con Reglas Innegociables (AGENTS.md)

| Regla Arquitectónica | Estado | Observación / Evidencia |
| :--- | :---: | :--- |
| **Controladores = Solo funciones** | ✅ | Respetado en `citaController`, `referenciaController`, `trabajoSocialController` y `reportesController`. |
| **Modelos únicos con `manejarAccion`** | ✅ | Respetado en `CitaModel`, `ReferenciaModel`, `TrabajoSocialModel` y `ReportesModel`. |
| **Regla de las Dos Puertas (HTML vs JSON)** | ✅ | Verificado en `Respuesta::esApi()`, reportes y controladores de PDF (tercera puerta PDF). |
| **Manejo de Errores con `ExcepcionApi`** | ✅ | Modelos de control de acceso y PDFs capturan errores y lanzan `ExcepcionApi` semánticas. |
| **Auditoría obligatoria en Bitácora** | ✅ | Consultas de reportes registradas con la acción oficial `'Lectura'`. Citas y Referencias auditadas. |
| **Cero capas Service / Repository** | ✅ | Estructura plana respetada (Controladores → Modelos con PDO directo). |
| **Frontend SSR con templates y JS propio** | ✅ | Renderizado de vistas PHP nativas y plantillas FPDF sin frameworks externos. |

---

## 3. Matriz Detallada de Hallazgos

### [HALLAZGO-SEC-01] Almacenamiento de Refresh Token en Texto Plano en Base de Datos
- **Severidad:** 🟠 Alta
- **Dimensión:** Seguridad Criptográfica / OWASP A02:2021 (Cryptographic Failures)
- **Archivos Afectados:** `app/Core/JwtHandler.php` (métodos `generarRefreshToken` y `renovarJWT`), tabla `dirpoles_security.refresh_tokens`
- **Descripción del Detalle:**
  En `JwtHandler.php`, los tokens de refresco son generados con `bin2hex(random_bytes(64))` e insertados directamente en texto plano dentro de la columna `token` de la tabla `refresh_tokens`.
- **Escenario de Riesgo (Impacto Teórico):**
  Si un atacante o usuario no autorizado obtiene acceso de lectura a la base de datos de seguridad (vía volcado de BD o inyección SQL en algún punto vulnerable), obtendrá los tokens de refresco activos y podrá generar nuevos tokens de acceso JWT para cualquier cuenta sin conocer las credenciales del usuario.
- **Evidencia en Código:**
  ```php
  // app/Core/JwtHandler.php (líneas 145-148 y 216-219)
  $stmt = $pdo->prepare(
      "INSERT INTO refresh_tokens (id_empleado, token, expires_at) 
       VALUES (:id_empleado, :token, :expires_at)"
  );
  ```
- **Recomendación:**
  Almacenar en BD únicamente el hash unidireccional del token de refresco (ej. `hash('sha256', $token)`), entregando la cadena aleatoria original al cliente en la cookie `httponly`. Al renovar, buscar por el hash del token recibido.

**Estado post-auditoría:** ✅ Mitigado / Resuelto  
**Fecha de Corrección:** 2026-10-01  
**Detalle de la Solución:** Se actualizó `app/Core/JwtHandler.php` para que `generarRefreshToken()` calcule el hash `hash('sha256', $token)` y persista en `refresh_tokens.token` únicamente el hash SHA-256, retornando la cadena aleatoria original en texto plano para la cookie. En `renovarJWT()`, la búsqueda en BD se realiza calculando `hash('sha256', $tokenCookie)` sobre el token recibido en la cookie.

---

### [HALLAZGO-SEC-02] Ausencia de Flag `secure` Explícito en la Emisión de Cookies HTTP
- **Severidad:** 🟡 Media
- **Dimensión:** Configuración de Seguridad / OWASP A05:2021 (Security Misconfiguration)
- **Archivos Afectados:** `app/Controllers/loginController.php` (funciones `iniciar_sesion` y `refresh_token`), `app/Middlewares/SessionAuthMiddleware.php`
- **Descripción del Detalle:**
  Las cookies de seguridad `jwt_token` y `refresh_token` se configuran con `httponly => true` y `samesite => Lax`, pero omiten la propiedad `'secure' => true` (o una verificación dinámica basada en `isset($_SERVER['HTTPS'])`).
- **Escenario de Riesgo (Impacto Teórico):**
  En un despliegue sobre HTTPS sin directiva HSTS estricta en el servidor web, las cookies de sesión/JWT podrían ser enviadas en claro a través de HTTP si la navegación se degrada o es redirigida manualmente, permitiendo la captura de tokens mediante Man-in-the-Middle (MitM).
- **Evidencia en Código:**
  ```php
  // app/Controllers/loginController.php (líneas 91-96)
  setcookie('jwt_token', $jwtResult['token'], [
      'expires'  => $jwtResult['expiracion'],
      'path'     => '/',
      'httponly' => true,
      'samesite' => 'Lax',
  ]);
  ```
- **Recomendación:**
  Definir la opción `'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'` o forzar `'secure' => true` en ambientes de producción (definido mediante variable de entorno `.env`).

**Estado post-auditoría:** ✅ Mitigado / Resuelto  
**Fecha de Corrección:** 2026-10-01  
**Detalle de la Solución:** Se configuró el flag `'secure'` en todas las llamadas a `setcookie()` en `loginController.php` y `SessionAuthMiddleware.php` evaluando dinámicamente si la solicitud se realiza por HTTPS (`(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) || filter_var(env('COOKIE_SECURE', false), FILTER_VALIDATE_BOOLEAN)`).

---

### [HALLAZGO-SEC-03] Robustez y Mecanismo Antirrobo en Gestión de Sesión, JWT y Bloqueo de Intentos
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Autenticación y Control de Acceso / OWASP A07:2021 (Identification and Authentication Failures)
- **Archivos Afectados:** `app/Controllers/loginController.php`, `app/Models/loginModel.php`, `app/Core/JwtHandler.php`, `app/Middlewares/RateLimitMiddleware.php`
- **Descripción del Detalle:**
  Se verificaron y confirmaron los siguientes controles defensivos en el núcleo:
  1. **Regeneración de Sesión:** `iniciar_sesion()` ejecuta `session_regenerate_id(true)` inmediatamente tras autenticar, erradicando ataques de Fijación de Sesión.
  2. **Cifrado Asimétrico en Tránsito:** Las credenciales de login viajan cifradas con RSA desde el cliente y se descifran con la llave privada `login_private.pem`.
  3. **Persistencia BD del Anti-Bruteforce:** `loginModel.php` incrementa y consulta los intentos en la tabla `login_intentos`. Bloquea la cuenta (`estatus = 0`) tras 3 fallos consecutivos y no puede evadirse borrando la cookie `PHPSESSID`.
  4. **Protección DoS a Administradores:** Los administradores no se bloquean por intentos en BD para evitar auto-bloqueos masivos maliciosos, pero están protegidos perimetralmente por `RateLimitMiddleware` (5 peticiones por 5 minutos).
  5. **Rotación Atómica de Refresh Tokens:** `JwtHandler::renovarJWT` aplica un solo uso (*one-time use*) revocando el token usado e insertando uno nuevo dentro de una transacción PDO con bloqueo pesimista `FOR UPDATE`.
  6. **Validación Dual de Integridad:** `SessionAuthMiddleware` valida que `$_SESSION['id_empleado']` sea idéntico al payload del JWT validado.

---

### [HALLAZGO-SEC-04] Hermeticidad en Manejo Global de Excepciones y Ausencia de Stack Traces
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Fuga de Información / OWASP A05:2021 (Security Misconfiguration)
- **Archivos Afectados:** `index.php`, `app/Core/Respuesta.php`, `app/Core/Router.php`
- **Descripción del Detalle:**
  El sistema implementa el manejo centralizado de excepciones vía `set_exception_handler` y `register_shutdown_function` en `index.php`, canalizado por `Respuesta::manejarExcepcion()`.
  Cuando la variable de entorno `APP_DEBUG` está establecida en `false`:
  - En la puerta API (`api/*` o `Accept: application/json`), las excepciones no controladas devuelven una estructura JSON estándar con el código `ERROR_INTERNO` y el mensaje genérico `"Ocurrió un error inesperado en el servidor."`.
  - En la puerta HTML, se muestra la plantilla limpia de error (`app/Views/errors/error.php` o `404.php`), sin exponer rutas internas del servidor ni trazas de ejecución (*stack traces*).
  - Toda información técnica sensible se escribe exclusivamente en los logs del servidor vía `error_log()`.

---

### [HALLAZGO-SEC-05] Riesgo de Condición de Carrera por Ausencia de Constraints `UNIQUE` en Tablas de Transporte
- **Severidad:** 🟡 Media
- **Dimensión:** Integridad de Datos y Concurrencia / OWASP A04:2021 (Insecure Design)
- **Archivos Afectados:** `app/Models/TransporteModel.php`, tablas `vehiculos`, `repuestos_vehiculos` y `proveedores`
- **Descripción del Detalle:**
  Las validaciones de unicidad para datos críticos (como la `placa` de un vehículo, el `codigo` o `nombre` de un repuesto, y el `documento`/`correo` de un proveedor) se realizan exclusivamente mediante consultas PHP previo a la inserción. Las tablas correspondientes en la base de datos no cuentan con restricciones de clave única (`UNIQUE KEY`).
- **Escenario de Riesgo (Impacto Teórico):**
  Bajo peticiones simultáneas concurrentes (enviadas en el mismo milisegundo por dos usuarios o por reintentos de red), ambas ejecuciones pueden superar la verificación en PHP (`SELECT COUNT(*) = 0`) antes de que se ejecute la primera inserción, generando registros duplicados de vehículos o proveedores en la base de datos.
- **Evidencia en Código:**
  ```php
  // app/Models/TransporteModel.php (validaciones en PHP previo a INSERT)
  private function verificarPlacaExistente(string $placa, int $excluirId = 0): void {
      $stmt = $this->conn->prepare("SELECT COUNT(*) FROM vehiculos WHERE placa = :placa AND id_vehiculo <> :excluir");
      // ...
  }
  ```
- **Recomendación:**
  Añadir índices de unicidad (`UNIQUE INDEX`) en la base de datos MySQL para `vehiculos(placa)`, `repuestos_vehiculos(codigo)` y `proveedores(documento)`, capturando el código de error SQL `23000` en el bloque catch del modelo.

**Estado post-auditoría:** ✅ Mitigado / Resuelto  
**Fecha de Corrección:** 2026-10-01  
**Detalle de la Solución:** Se creó el archivo de migración `docs/bd/transporte_unique_indexes.sql` con las sentencias `ALTER TABLE` para agregar restricciones `UNIQUE` en `vehiculos(placa)`, `repuestos_vehiculos(codigo)` y `proveedores(tipo_documento, num_documento)`. En `app/Models/TransporteModel.php`, se envolvieron las operaciones de inserción y actualización en bloques `try...catch (PDOException $e)` comprobando la excepción `SQLSTATE 23000` para capturar cualquier violación de constraint y lanzar la excepción semántica `ExcepcionApi::yaExiste()`.

---

### [HALLAZGO-SEC-06] Evaluación Correcta del Orden `SET` en Sentencias de Descuento de Insumos
- **Severidad:** 🟢 Informativa (Aprobado / Corrección Verificada)
- **Dimensión:** Integridad de Lógica de Negocio / OWASP A04:2021
- **Archivos Afectados:** `app/Models/MedicinaModel.php`, `app/Models/JornadaModel.php`
- **Descripción del Detalle:**
  Se verificó el orden de asignación en la cláusula `SET` de los `UPDATE` que descuentan stock de insumos. En MySQL, las expresiones en `SET` se evalúan en secuencia. El código coloca la asignación de `estatus` (evaluada mediante `CASE WHEN (cantidad - :cantidad) <= 0`) **antes** de la asignación `cantidad = cantidad - :cantidad`.
- **Evaluación:**
  Esta disposición garantiza que `(cantidad - :cantidad)` en el `CASE` se calcule sobre el valor original del stock en la BD. Se evita el error donde una resta previa hubiese causado un falso positivo de estado `'Agotado'` mientras aún quedaba stock disponible.

---

### [HALLAZGO-SEC-07] Control Riguroso de Concurrencia y Aforo con Bloqueos Pesimistas (`FOR UPDATE`)
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Integridad de Concurrencia y Control de Aforo / OWASP A04:2021
- **Archivos Afectados:** `app/Models/InventarioModel.php`, `app/Models/MedicinaModel.php`, `app/Models/JornadaModel.php`, `app/Models/TransporteModel.php`
- **Descripción del Detalle:**
  Se confirmó la implementación sistemática de transacciones PDO combinadas con bloqueos pesimistas (`SELECT ... FOR UPDATE`):
  1. **Aforo de Jornadas (`JornadaModel`):** Al registrar asistentes, se bloquea la fila de la cabecera de la jornada con `FOR UPDATE`, impidiendo que dos peticiones simultáneas sobrepasen el aforo máximo (`aforo_maximo`).
  2. **Descuento de Stock (`InventarioModel`, `MedicinaModel`, `JornadaModel`, `TransporteModel`):** Cada insumo o repuesto es bloqueado con `FOR UPDATE` antes de verificar su disponibilidad y vencimiento.
  3. **Control de Insumos Vencidos:** Las entradas de inventario y las prescripciones médicas/jornadas verifican `fecha_vencimiento >= CURDATE()` bajo el bloqueo pesimista, impidiendo el uso o ingreso de material expirado.
  4. **Rollback en Excepciones:** Todos los bloques `catch` de mutación verifican `$this->conn->inTransaction()` y ejecutan `$this->conn->rollBack()` antes de propagar la `ExcepcionApi`.

---

### [HALLAZGO-SEC-08] Validación de Tipo de Archivo en Foto de Estudio Socioeconómico Basada Únicamente en Extensión
- **Severidad:** 🟡 Media
- **Dimensión:** Carga de Archivos y Configuración de Seguridad / OWASP A05:2021 (Security Misconfiguration)
- **Archivos Afectados:** `docs/PDF/EstudioSE/procesar.php` (líneas 57-70)
- **Descripción del Detalle:**
  Al procesar la imagen opcional enviada en la solicitud del Estudio Socioeconómico, el sistema valida la extensión del nombre del archivo en la solicitud HTTP (`pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION)`), verificando que pertenezca al arreglo `['jpg', 'jpeg', 'png', 'gif']`. Sin embargo, no se realiza una comprobación de tipo MIME real del contenido con `finfo_file()` o `mime_content_type()`.
- **Escenario de Riesgo (Impacto Teórico):**
  Un usuario malintencionado podría renombrar un script malicioso o archivo de texto a una extensión como `.png` y enviarlo. Aunque GD e imagerotate procesan o fallan si el archivo no es una imagen válida, la ausencia de verificación MIME real relaja la seguridad en el punto de entrada de archivos.
- **Evidencia en Código:**
  ```php
  // docs/PDF/EstudioSE/procesar.php
  if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
      $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
      $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
      if (in_array($extension, $extensionesPermitidas)) { ... }
  }
  ```
- **Recomendación:**
  Incorporar verificación de tipo MIME mediante `finfo_open(FILEINFO_MIME_TYPE)` asegurando que el contenido corresponda a `image/jpeg`, `image/png` o `image/gif`.

**Estado post-auditoría:** ✅ Mitigado / Resuelto  
**Fecha de Corrección:** 2026-10-01  
**Detalle de la Solución:** Se incorporó en `docs/PDF/EstudioSE/procesar.php` la validación física de tipo MIME del archivo temporal mediante `finfo_open(FILEINFO_MIME_TYPE)` y `finfo_file()`, restringiendo la subida únicamente a los tipos MIME legítimos `image/jpeg`, `image/png` y `image/gif` antes de mover o procesar la imagen.

---

### [HALLAZGO-SEC-09] Protección Efectiva contra Vulnerabilidades BOLA/IDOR mediante Verificación de Alcance
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Control de Acceso Nivel Objeto / OWASP A01:2021 (Broken Access Control)
- **Archivos Afectados:** `app/Models/CitaModel.php`, `app/Models/ReferenciaModel.php`, `app/Models/TrabajoSocialModel.php`
- **Descripción del Detalle:**
  Se confirmó que la consulta, edición y eliminación de registros valida estrictamente la pertenencia del recurso al usuario de la sesión mediante métodos de alcance como `asegurarAlcanceCita()` y `asegurarAlcance()`:
  1. **Citas (`CitaModel`):** Un psicólogo no puede consultar ni modificar citas pertenecientes a otro psicólogo (filtro por `id_empleado = :id_usuario`).
  2. **Referencias (`ReferenciaModel`):** Un empleado no administrativo únicamente puede acceder a referencias donde su ID sea el de origen (`id_empleado_origen`) o el de destino (`id_empleado_destino`). El origen es forzado en el servidor desde `$_SESSION['id_empleado']`, impidiendo la suplantación.
  3. **Respuestas a Referencias:** Solo el empleado destino o un Administrador puede aceptar/rechazar una referencia pendiente.

---

### [HALLAZGO-SEC-10] Prevención de Salto de Directorio (Path Traversal) en Gestión de Archivos PDF
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Integridad de Sistema de Archivos / OWASP A01:2021 (Broken Access Control)
- **Archivos Afectados:** `app/Models/TrabajoSocialModel.php` (método `eliminarArchivo`), `app/Controllers/trabajoSocialController.php`
- **Descripción del Detalle:**
  Al desvincular o eliminar un estudio socioeconómico u otro archivo adjunto del módulo de Trabajo Social, el sistema aplica la verificación `str_starts_with($rutaRelativa, 'uploads/trabajo_social/')` antes de concatenar el `BASE_PATH` y llamar a `unlink()`.
- **Evaluación:**
  Esta verificación detiene intentos de borrado mediante secuencias de escape como `../../` o rutas fuera de la carpeta autorizada.

---

### [HALLAZGO-SEC-11] Aplicación Estricta de Lista Blanca de Filtros y Registro de Auditoría en Reportes
- **Severidad:** 🟢 Informativa (Aprobado / Buena Práctica)
- **Dimensión:** Auditoría y Sanitización de Entradas / OWASP A03:2021 (Injection)
- **Archivos Afectados:** `app/Controllers/reportesController.php` (función `reportesAplicarFiltros`), `app/Models/ReportesModel.php`
- **Descripción del Detalle:**
  1. **Whitelist de Filtros:** La función `reportesAplicarFiltros()` utiliza un arreglo cerrado de claves autorizadas (`fecha_inicio`, `fecha_fin`, `genero`, `pnf`, `servicio_destino`, `area`, `estado`, `tipo_consulta`, `submodulo`, `grado`, `tipo_discapacidad`, `tipo_bien`, `tipo_vehiculo`, `seccion_transporte`, `reporte`, `limit`). Parámetros desconocidos son ignorados.
  2. **Auditoría con Acción Legítima:** Toda generación de datos de reporte invoca `Bitacora::registrar('Reportes', 'Lectura', ...)` utilizando el tipo de acción oficial `'Lectura'`, cumpliendo con la lista de acciones válidas del núcleo.

---

## 4. Análisis de Concurrencia y Transaccionalidad

### 4.1 Arquitectura Transaccional y Manejo de Aislamiento
Todos los modelos de negocio del sistema que gestionan tablas compuestas o de inventario (`InventarioModel`, `MedicinaModel`, `JornadaModel`, `TransporteModel`) heredan de `BusinessModel` y gestionan transacciones explícitas mediante el driver `PDO` (`$this->conn->beginTransaction()`).

El patrón estándar implementado garantiza la atomicidad:
```php
try {
    $this->conn->beginTransaction();
    // 1. Bloqueo pesimista SELECT ... FOR UPDATE
    // 2. Validación de reglas de negocio en PHP
    // 3. Mutación de datos (INSERT/UPDATE/DELETE)
    $this->conn->commit();
} catch (ExcepcionApi $e) {
    if ($this->conn->inTransaction()) {
        $this->conn->rollBack();
    }
    throw $e;
} catch (Throwable $e) {
    if ($this->conn->inTransaction()) {
        $this->conn->rollBack();
    }
    error_log("Error en Modelo: " . $e->getMessage());
    throw ExcepcionApi::errorInterno("Mensaje neutro al cliente");
}
```

### 4.2 Control Pesimista de Aforos e Inventario
1. **Jornadas Médicas (Aforo de Asistentes):**
   En `JornadaModel::registrarAsistente`, la transacción realiza un `SELECT aforo_maximo, estatus, fecha_fin FROM jornadas_medicas WHERE id_jornada = :id FOR UPDATE`. Esto serializa el registro de participantes: si 5 personas intentan inscribirse simultáneamente cuando queda 1 solo cupo, el bloqueo asegura que solo 1 transacción reserve el lugar y las 4 restantes sean rechazadas con `ExcepcionApi::validacion("El aforo de la jornada médica ha sido alcanzado.")`.

2. **Descuento de Insumos Médicos y Repuestos:**
   En `MedicinaModel::registrarInsumos`, `JornadaModel::registrarDiagnostico` y `InventarioModel::salida`, se aplica el mismo mecanismo sobre la tabla `insumos`. La verificación del stock disponible (`cantidad >= :solicitada`) se realiza *dentro* del bloque `FOR UPDATE`, erradicando cualquier posibilidad de stock negativo por condiciones de carrera.

3. **Kardex de Inventario y Auditoría:**
   Todas las entradas, salidas y registros de inventario insertan de forma atómica en `inventario_medico` o `inventario_repuestos` el movimiento correspondiente, registrando el ID del empleado responsable extraído directamente de la sesión verificada.

---

## 5. Deuda Técnica y Plan de Acción

### 5.1 Acciones Prioritarias (Previas a la Defensa del Proyecto de Grado)
1. **Hashing de Refresh Tokens (`[HALLAZGO-SEC-01]`):**
   Modificar `JwtHandler.php` para que al emitir y renovar refresh tokens se almacene únicamente el hash unidireccional `hash('sha256', $token)` en la columna `token` de la tabla `refresh_tokens`, dejando la cadena aleatoria original exclusivamente en la cookie `httponly`.
2. **Flag `secure` en Cookies HTTP (`[HALLAZGO-SEC-02]`):**
   Actualizar las llamadas a `setcookie()` en `loginController.php` y `SessionAuthMiddleware.php` para incluir `'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'` o forzar `true` en el entorno de producción.
3. **Índices de Unicidad en MySQL (`[HALLAZGO-SEC-05]`):**
   Ejecutar un script de migración SQL que incorpore restricciones de unicidad (`UNIQUE INDEX`) en la base de datos de transporte para `vehiculos(placa)`, `repuestos_vehiculos(codigo)` y `proveedores(documento)`, garantizando que las condiciones de carrera no puedan generar filas duplicadas.
4. **Validación de Tipo MIME en Subida de Archivos (`[HALLAZGO-SEC-08]`):**
   Añadir la verificación `finfo_open(FILEINFO_MIME_TYPE)` en `docs/PDF/EstudioSE/procesar.php` para validar que el contenido real del archivo subido sea `image/jpeg`, `image/png` o `image/gif`.

### 5.2 Comandos de Verificación Sintáctica Recomendados

#### 1. Verificación Sintáctica PHP (`php -l`)
Para validar periódicamente que ningún archivo PHP contenga errores de sintaxis, ejecute los siguientes comandos en la raíz del proyecto:

```bash
# 1. Archivos del Núcleo y Bootstrap
php -l index.php app/routes.php app/bootstrap.php

# 2. Clases Core y Middlewares
php -l app/Core/Router.php app/Core/JwtHandler.php app/Core/Autorizacion.php app/Core/Respuesta.php app/Core/Bitacora.php app/Core/ExcepcionApi.php
php -l app/Middlewares/SessionAuthMiddleware.php app/Middlewares/RateLimitMiddleware.php

# 3. Controladores y Modelos de Negocio
php -l app/Controllers/*.php
php -l app/Models/*.php

# 4. Procesadores de Documentos PDF
php -l docs/PDF/constancia/procesar.php docs/PDF/referencia/procesar.php docs/PDF/recipe/procesar.php docs/PDF/EstudioSE/procesar.php
```

#### 2. Verificación Sintáctica de JavaScript Client-Side (`node --check`)
Para validar la sintaxis de todos los scripts JavaScript de la aplicación sin ejecutar un entorno de empaquetado:

```bash
# Comprobación sintáctica de todos los módulos JavaScript
find dist/js/ -name "*.js" -exec node --check {} \;
```