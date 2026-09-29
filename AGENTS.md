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
  - **Citas** (id_modulo 3): agenda de Psicología, disponibilidad contra horarios,
    permisos por rol, CRUD, estados, stats y DataTables.
  - **Medicina** (id_modulo 5): diagnósticos médicos (creación con insumos —
    descuento de stock en transacción con `FOR UPDATE` —, consulta con
    DataTables, edición restringida y eliminación). **Reglas permanentes**:
    la edición SOLO permite patología, estatura, peso, tipo de sangre, motivo,
    diagnóstico, tratamiento y observaciones (jamás beneficiario, empleado que
    atendió ni insumos); al ELIMINAR **no se revierte el inventario** (el
    insumo ya se usó: `insumos.cantidad` y `inventario_medico` quedan intactos
    y la Bitácora registra los insumos usados). Pendiente: Enfermero (tipo 12)
    sin permisos en este módulo y pruebas manuales de navegador.
  - **Orientación** (id_modulo 6): diagnósticos de orientación (solicitud con
    `id_servicios=3` + tabla `orientacion`; CRUD con DataTables, stats y tour).
    **Reglas permanentes**: los 4 campos de texto (motivo, descripción,
    indicaciones, observaciones) son **obligatorios** (decisión del usuario,
    corrige la inconsistencia del sistema viejo); la edición SOLO permite esos
    4 textos (jamás beneficiario ni empleado que atendió); al ELIMINAR se borran
    `orientacion` + `solicitud_de_servicio` en transacción (no hay inventario
    ni tablas hijas). Sin `detalle_patologia`.
  - **Discapacidad** (id_modulo 8): diagnósticos de discapacidad (solicitud con
    `id_servicios=5` + tabla `discapacidad`; CRUD con DataTables, stats y tour).
    **Reglas permanentes**: son **obligatorios** `tipo_discapacidad`,
    `diagnostico`, `grado`, `habilidades_funcionales` y `observaciones`
    (formulario del sistema viejo); los campos ENUM (`tipo_discapacidad`,
    `grado`, `requiere_asistencia`) se validan contra el esquema; la edición
    SOLO permite los 11 campos de la tabla (jamás beneficiario ni empleado que
    atendió); al ELIMINAR se borran `discapacidad` + `solicitud_de_servicio`
    en transacción (sin inventario). Stats: `total`, `del_mes`, `graves`,
    `con_carnet`. RBAC ya existente: tipos 5 (Discapacidad), 6 y 10 en módulo 8.
  - **Trabajo Social** (id_modulo 7): hub con 4 pestañas (becas, exoneraciones,
    FAMES, embarazadas) + consulta con DataTables, edición restringida y
    eliminación; estudio socioeconómico (offcanvas de 5 pasos → PDF con FPDF
    en `docs/PDF/EstudioSE/procesar.php`). **Reglas permanentes**:
    - Cada sub-registro vive en su tabla con `id_solicitud_serv` (una solicitud
      del servicio 4 por sub-registro; `listar*` usa LEFT JOINs que no duplican).
    - La edición SOLO permite los campos editables de cada tipo (nunca
      beneficiario, empleado ni archivos); el PDF de exoneración no se
      regenera desde editar.
    - Al ELIMINAR se borra sub-registro + `solicitud_de_servicio` en
      transacción y se eliminan los PDFs físicos (best-effort, solo rutas
      bajo `uploads/trabajo_social/`).
    - Estudio socioeconómico: SOLO desde exoneraciones **pendientes**
      (`direccion_estudiose IS NULL`); permiso `crear`; foto opcional
      (jpg/jpeg/png/gif validado por extensión y MIME); los campos NO se
      persisten (el PDF es el registro, la Bitácora guarda los insumos);
      el vínculo es `UPDATE ... SET direccion_estudiose=... WHERE ... IS NULL`
      con alcance; si el UPDATE no afecta filas se deshace (borra el PDF
      huérfano) para no pisar un estudio ajeno.
    - Stats: `total`, `del_mes`, `pendientes_estudio`, `estudios_generados`
      (una sola query con LEFT JOINs; clave `admin_ts_total` en dashboard).
    - Tour: `#btn-ayuda` (página) y `#btn-ayuda-estudio` (offcanvas, requiere
      el offcanvas abierto).
  - **Inventario Médico** (id_modulo 9): CRUD de insumos (`insumos`) con kardex
    de movimientos (`inventario_medico`), Entrada/Salida de stock, historial y
    stats; tabla con DataTables y 4 modales (editar, entrada, salida,
    historial). **Reglas permanentes**:
    - El nombre para RBAC es `'inventario medico'` (Autorizacion resuelve
      `LOWER(modulo.nombre)`); permisos reales en BD: roles 2, 6 y 10.
    - Crear NO lleva cantidad: el insumo nace con cantidad 0 y estatus
      `'Agotado'` (y movimiento `'Registro'` en el kardex); el stock entra
      solo con el botón Entrada (permiso `crear` del módulo 9).
    - La fecha de vencimiento al CREAR debe ser >= hoy; al EDITAR se admite
      cualquier fecha válida (la edición SOLO permite nombre, tipo,
      presentación, fecha y descripción; jamás cantidad ni estatus).
    - Entrada: solo insumos no vencidos (estatus != 'Vencido' y fecha >= hoy);
      Salida: solo con cantidad > 0 y tiene motivo obligatorio del catálogo
      `Vencimiento|Daño|Pérdida|Donación|Uso Interno`. Ambos usan
      transacción + `FOR UPDATE` con stock calculado en PHP.
    - Eliminar: bloqueado si está en `detalle_insumo`, si tiene stock o si ya
      tiene movimientos distintos de `'Registro'`.
    - Estatus mostrado en la tabla = efectivo: si `fecha_vencimiento < hoy`
      se pinta `'Vencido'` sin masivos UPDATE; la Entrada se deshabilita y la
      Salida se deshabilita con stock 0.
    - Stats: `insumos_total`, `insumos_disponibles`, `insumos_por_vencer`
      (≤30 días) y `insumos_criticos` (cantidad < 10 con `Disponible`);
      el dashboard usa la clave `admin_insumos_total`.
    - Duplicado: misma presentación + nombre + tipo + fecha de vencimiento
      (validación remota en vivo `api/inventario/validar_insumo` con
      `id_excluir` en edición).
    - Tour: `#btn-ayuda` en crear; el objetivo de un `select2` apunta a su
      `.select2-container`.
    - **Bug corregido (2026-09-29)**: el `UPDATE` de descuento de
      `MedicinaModel.registrarInsumos` marcaba `'Agotado'` con stock restante
      > 0: MySQL evalúa el `SET` de arriba hacia abajo con el valor ya
      actualizado, así que el `CASE WHEN (cantidad - :c) <= 0` restaba dos
      veces (ej: stock 5, uso 4 → quedaba 1 pero 'Agotado'). Corregido
      reordenando: el `CASE` se evalúa ANTES de restar y usa el stock
      original. Verificar al usar insumos en Medicina que el estatus quede
      correcto.
  - **Referencias** (id_modulo 10): flujo de referencias entre áreas
    (`referencias` + `log_referencias`) con crear, consulta con DataTables,
    detalle con historial y acciones aceptar/rechazar/eliminar. **Reglas
    permanentes** (decisiones del usuario):
    - RBAC: nombre `'referencias'`; permisos en BD ya existentes: roles
      1, 2, 3, 4, 5, 6 y 10 (no se agregaron Secretaria/Enfermero: decisión
      del usuario; no hay SQL nuevo).
    - Origen/destino: el **Administrador elige quién refiere y quién
      recibe**; cualquier otro empleado es SIEMPRE el origen (forzado en
      servidor: se ignora `id_empleado_origen` enviado por el cliente).
    - El servicio destino debe ser **DISTINTO** al de origen (referir entre
      áreas); se valida en backend aunque el frontend excluye el origen de
      la lista de destinos.
    - Crear: beneficiario + origen + destino + **motivo y observaciones
      OBLIGATORIOS** (decisión del usuario: como el sistema viejo) →
      estado `'Pendiente'` + notificación al destino (tipo `'referencia'`,
      url `referencias/consultar`).
    - Aceptar/Rechazar: solo el **destino** o admin, y solo desde
      `'Pendiente'` (transacción + `FOR UPDATE`); el rechazo exige motivo
      que se guarda en `log_referencias.observaciones` (NO pisa las
      observaciones originales de la referencia); bitácora + notificación
      al origen en ambos casos.
    - Eliminar: solo el **origen** o admin, y solo `'Pendiente'`; borra
      `log_referencias` primero (FK) y luego la fila, en transacción, y
      notifica al destino (si el que borra no es él).
    - Cada transición inserta el log con `estado_anterior` REAL (el sistema
      viejo lo hardcodeaba en `'Pendiente'` y no validaba el estado previo).
    - Alcance de datos: sin admin solo se listan/stats las referencias donde
      el empleado es origen o destino; admin ve todas.
    - Stats: `referencias_total`, `referencias_pendientes`,
      `referencias_aceptadas`, `referencias_mes` (con alcance); el
      dashboard usa la clave global `admin_referidos_total`.
    - Tour: `#btn-ayuda` en crear; el objetivo de un `select2` apunta a su
      `.select2-container`.
  - **Mobiliario** (id_modulo 12): hub con 3 sub-flujos (mobiliario,
    equipos y fichas técnicas) sobre las tablas `mobiliario`, `equipos`,
    `fichas_tecnicas`, `detalle_ficha_mobiliario`, `detalle_ficha_equipo` e
    `historial_inventario` (kardex). Crear con pestañas y consulta con
    DataTables por pestaña. **Reglas permanentes** (decisiones del usuario,
    2026-09-29):
    - RBAC: nombre `'mobiliario'`; permisos en BD ya existentes: roles
      (`id_tipo_emp`) 6 y 10 con los 4 permisos (no hay SQL nuevo).
    - Ficha técnica: **UNA activa por empleado responsable**, y enlaza ítems
      reales (`detalle_ficha_*`, que el sistema viejo no usaba); obligatorio
      **al menos un ítem** de mobiliario o un equipo (modelo `validarDetalles`
      + `MobiliarioValidaciones.detalle.validar`).
    - Alta pieza a pieza; el kardex registra `asignacion`, `reubicacion`,
      `modificacion` y `baja`. El historial SIEMPRE se escribe (bitácora aparte).
    - Equipos: **serial único y obligatorio** con validación remota
      `api/mobiliario/validar_serial` (`id_excluir` en edición).
    - Disponibilidad de mobiliario = `cantidad` − asignado en fichas activas
      (calculada en SQL, nunca se muta `mobiliario.cantidad`); un equipo no
      puede estar en dos fichas activas.
    - Edición de mobiliario no puede bajar `cantidad` por debajo de lo ya
      asignado; **el estatus nunca se edita** (solo el botón Baja).
    - Baja lógica (`estatus='Inactivo'`) bloqueada si el ítem está en una
      ficha activa; eliminación bloqueada si está en `detalle_ficha_*` o
      `inventario_mob` (ambas con transacción).
    - La edición SOLO permite los campos editables del sub-flujo (nunca
      estatus); las filas de detalle se REEMPLAZAN completas al editar la
      ficha.
    - Stats: `total_mobiliarios`, `total_equipos`, `fichas_activas`,
      `inventario_mes`; el dashboard usa la clave `admin_mobiliario_total`.
    - Tour: `#btn-ayuda` en crear (pasos por pestaña activa); los formularios
      de edición viven en modales de `consultar.php` (sin tour propio).
    - Los listados aceptan `limit`/`offset` con defecto 200 (patrón de
      Inventario): es una carga cliente, no server-side de DataTables.
  - **Jornadas Médicas** (id_modulo 11): 3 páginas — `jornadas/crear`,
    `jornadas/consultar` (DataTables + modal de edición) y
    `jornadas/detalle/{id}` (cabecera + aforo + asistentes + diagnósticos) —
    sobre `jornadas_medicas`, `jornada_beneficiarios`, `jornada_diagnosticos`
    y `jornada_insumos` (todas en business, ya existentes). **Reglas
    permanentes** (decisiones del usuario, 2026-09-29):
    - RBAC: nombre `'jornadas'`; permisos en BD ya existentes: roles
      (`id_tipo_emp`) 2 (Médico), 6 (Administrador) y 10 (Superusuario) con
      los 4 permisos. **No hay SQL nuevo.**
    - Aforo: transacción + `FOR UPDATE` sobre la cabecera; dos altas
      simultáneas nunca lo rebasan y al editar no puede bajar por debajo de
      los ya registrados. La jornada nace `'Activa'`.
    - Asistentes: alta **manual** con autocompletar opcional por cédula
      (`api/jornadas/buscar_persona`, que solo SUGIERE datos de
      `beneficiario` y `empleado`; quien confirma es el usuario). Una misma
      cédula no se repite en la MISMA jornada (distintas jornadas sí) y solo
      se registra mientras la jornada esté `'Activa'` y `fecha_fin` vigente.
    - Eliminar asistente: bloqueado si ya tiene diagnóstico o si la jornada
      no está Activa; eliminar jornada: solo si NO tiene asistentes (si no,
      Cancelarla).
    - Diagnósticos: **VARIOS por asistente** (mejora sobre el sistema viejo,
      que dejaba uno); se agregan solo en jornada Activa; los 3 textos son
      obligatorios (`tratamiento` es NULL en BD pero se exige, como el
      sistema viejo); la edición SOLO corrige textos (nunca persona ni
      insumos); al eliminar NO se devuelve el stock.
    - Insumos: transacción + `FOR UPDATE` sobre `insumos`, estatus
      `'Disponible'`, no vencidos y con stock; descuento con `CASE` a
      `'Agotado'` **antes** de restar (el `SET` de MySQL se evalúa de arriba
      hacia abajo) y movimiento `'Salida'` en el kardex
      `inventario_medico` (patrón Medicina). Máx. 30 insumos por
      diagnóstico, sin repetidos (el frontend los fusiona).
    - Sin alcance de datos: quien tiene permiso ve todas las jornadas.
    - Stats: `jornadas_total`, `jornadas_activas`, `jornadas_finalizadas`,
      `jornadas_mes`; el dashboard usa la clave `admin_jornadas_total`.
    - Tour: `#btn-ayuda` con `JornadasTour.iniciar()` (crear) e
      `iniciarDetalle()` (detalle, abre el colapso del asistente antes de
      empezar); la edición usa el tour de crear dentro del modal.
    - Los permisos de la API: leer → `leer`; crear/asistente/diagnóstico
      (alta)/buscar/insumos → `crear`; actualizar/corregir diagnóstico →
      `editar`; eliminar → `eliminar`.
    - El tipo de jornada puede venir de datos heredados fuera del catálogo
      fijo (p. ej. `'Medica'` del seed): `editar.js` lo conserva como opción
      extra en el `<select>` para no perderlo al abrir el modal.
  - **Configuración** (id_modulo 14): catálogos del sistema (crear/consultar).
  - **Bitácora** (id_modulo 16): consulta de auditoría con filtros y exportación.
  - **Permisos** (id_modulo 17): matriz rol × módulo × permiso.
  - **Horarios** (id_modulo 18): administración exclusiva de Administrador/
    Superusuario, un horario por psicólogo y día, rango 07:00–17:00.
  - **Respaldo BD** (tercera puerta: descarga `.sql`, solo Administrador/Superusuario).
  - **Faltan los módulos de negocio pesados**: transporte (ver
    `app/Config/dashboard_cards.php`, las cards con `'disponible' => false`
    son los pendientes).

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
2. **Clases solo en `app/Models/` y `app/Core/`.** Excepción documentada:
   las generadoras de PDF viven junto a su plantilla en `docs/PDF/*`
   (`GenerarPDF` en `EstudioSE/procesar.php`, `GenerarConstancia` en
   `constancia/procesar.php`, `GenerarReferencia` en `referencia/procesar.php`,
   `GenerarRecipe` en `recipe/procesar.php`).
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
8. Antes de crear código, leer `AGENTS.md`, `GUIA-MODULOS.md`, el esquema SQL
  relacionado y al menos un módulo nuevo equivalente (normalmente Empleados,
  Beneficiarios, Citas u Horarios). La implementación vieja en `../DIRPOLES_4`
  sirve únicamente para descubrir reglas funcionales; no se copia su contrato,
  sus rutas ni sus respuestas de error.
9. Un módulo no está terminado si solo funciona el camino feliz. Debe incluir
  permisos, validación frontend y backend, estados visuales, tour, estadísticas,
  tabla/consulta cuando corresponda, auditoría, responsive básico y una lista
  de comandos de verificación manual para el usuario.
10. **PROHIBIDO EJECUTAR COMANDOS EN LA TERMINAL (OPTIMIZACIÓN DE TOKENS):**
  La IA **NUNCA** debe invocar o ejecutar herramientas de terminal (ej. `bash`,
  `php -l`, `node --check`, `git`, etc.) directamente. Toda ejecución automática
  gasta excesivos tokens de la API por reenvío de contexto. En su lugar, la IA
  debe limitarse a leer, buscar, analizar y editar archivos, e **indicar
  explícitamente en su mensaje final los comandos que el usuario debe ejecutar
  manualmente en su terminal**. Esto incluye no usar `run_in_terminal`, ni
  ejecutar comandos mediante tareas, scripts, shells o herramientas indirectas.

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

**Tercera puerta PDF (Constancia de Atención / Referencia / Recipe)**: las 5
rutas por módulo (`<modulo>/constancia/{id}`, `<modulo>/referencia/{id}` y
`trabajo-social/constancia|referencia/{tipo}/{id}`) responden `application/pdf`
en línea (`FPDF::Output('I')` + `exit`). Flujo fijo: `Autorizacion::verificar`
(permiso `leer` del módulo) → helper global `parametrosDocumento()` (en
`app/bootstrap.php`, sanea `tramite`/`hora`/`area` vía `$_GET`) → acción
`datos_documento` del modelo (aplica `asegurarAlcance`) → `Bitacora::registrar`
(`Registro`) → `require` del procesador → `GenerarConstancia::generar($datos)` /
`GenerarReferencia::generar($datos)`. **Solo Medicina** tiene además
`GET medicina/recipe/{id}` → `GenerarRecipe::generar($datos)`
(`docs/PDF/recipe/procesar.php`, talonaria "TRATAMIENTO/INDICACIONES":
izquierda diagnóstico + tratamiento, derecho observaciones; NO usa
`parametrosDocumento()` porque no lleva query params; el botón `js-recipe`
abre directo con `window.open`, sin diálogo). Los procesadores son **clases en
`docs/PDF/{constancia,referencia,recipe}/procesar.php`**
(excepción a "clases solo en `app/Models/` y `app/Core/`, igual que
`GenerarPDF` del estudio socioeconómico): sobreponen texto a una plantilla PNG;
las coordenadas nuevas (hora, del día, trámite, área en constancia/referencia;
INDICACIONES en recipe) son constantes marcadas CALIBRAR — si el usuario
reporta desalineación, ajustar SOLO esas constantes y conservar las
coordenadas viejas. En frontend el diálogo común vive en
`dist/js/core/documentos.js` (`window.DocumentosPDF.abrir({tipo, ruta,
registro, tramite})` con SweetAlert2 y `datalist` de áreas del catálogo
`servicio`); se incluye con `<script defer>` en las 5 vistas `consultar.php`
y los botones `js-constancia`/`js-referencia` están en la columna Acciones de
los 5 `consultar.js`.

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

> **Frontend**: antes de crear o editar CUALQUIER vista o JS de módulo, lee
> `GUIA-FRONTEND.md`. Es el contrato de frontend (helpers globales, contrato de
> campos, patrón de `validaciones/tour/stats/crear/editar/consultar`,
> prohibiciones). El módulo canónico es Empleados: copia su patrón real y
> adapta nombres; no inventes código genérico.

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
- **Reset de Select2**: `form.reset()` no actualiza por sí solo la representación
  visible. El helper global `select-2-init.js` escucha `reset` y dispara `change`.
  No implementar soluciones incompatibles por módulo; si se agregan selects
  dinámicos, llamar `window.initSelect2(form)` después de insertar las opciones.
- **Driver.js obligatorio en formularios**: toda pantalla de creación o edición
  que tenga flujo no trivial debe incluir `tour.js`, un botón `#btn-ayuda`,
  cargar el tour antes de la lógica de pantalla y conectar el botón a
  `ModuloTour.iniciar()`. Para un `<select class="select2">`, Driver.js debe
  apuntar a `select.nextElementSibling` (`.select2-container`), no al `<select>`
  original oculto. El tour debe describir reglas reales, no texto genérico.
- **DataTables obligatorio en consultas tabulares**: una vista de consulta con
  tabla debe cargar su `consultar.js`, inicializar `$('#tabla').DataTable()`
  después de pintar los datos y destruir la instancia antes de recargar. Debe
  incluir idioma local, ordenamiento, paginación y, cuando el módulo lo permita,
  botones `excelHtml5` y `pdfHtml5` excluyendo la columna Acciones. Nunca insertar
  una fila manual con un único `<td colspan>` en una tabla que DataTables va a
  inicializar: usar `tbody` vacío y dejar que DataTables muestre el estado vacío.
- **Carga dinámica**: si un `<select>` o tabla se llena por API, la secuencia es
  `apiFetch` → insertar opciones/filas → `initSelect2(scope)` o DataTables. No
  inicializar Select2 antes de insertar las opciones.
- **Eventos en tiempo real**: los inputs usan `input`/`change`; los Select2 usan
  `change select2:select select2:clear`. Las validaciones remotas deben llevar
  debounce y, si la API de unicidad o disponibilidad **falla** (caída, 500, 429),
  marcar el campo en ROJO con "No se pudo verificar…" y devolver `false`;
  jamás dejarlo en verde ni asumir que es válido. Los errores de negocio del
  backend (400/404/409) muestran `error.mensaje` tal cual.
- **Validación visual**: cada campo validable debe tener `<div id="campoError"
  class="form-text text-danger"></div>`, alternar `is-valid`/`is-invalid` y
  limpiar mensaje y estado al resetear. Validar siempre en submit aunque ya se
  haya validado en vivo.
- **Estadísticas**: si el módulo tiene tarjetas, usar `data-stat="clave"`, un
  `stats.js` separado y una sola llamada a `api/<modulo>/stats`. No hardcodear
  cifras ni duplicar consultas desde cada tarjeta.
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
`rol_modulo_permiso` para verse. Hoy tiene entradas para Empleados (1),
Beneficiarios (2), Citas (3), Inventario Médico (9), Referencias (10),
Mobiliario (12), Psicología (4, con subitems de Psicología/Medicina/
Orientación/Discapacidad/Trabajo Social), Horarios (18) y Configuración (14,
con subitems de Bitácora/Permisos/Respaldo que usan `id_modulo` explícito
porque validan contra otro módulo); AGREGA AHÍ tu módulo nuevo al crearlo.

### Convenciones de nombres frontend

- Carpeta: `dist/js/modulos/<modulo>/` en singular cuando así esté establecido
  por el módulo existente (`empleado`, `beneficiario`, `cita`, `horario`).
- Archivos recomendados: `validaciones.js`, `tour.js`, `stats.js`, `crear.js`,
  `editar.js`, `consultar.js`. No concentrar todo en un único archivo si la
  pantalla tiene tabla, modal y formulario.
- La vista incluye los scripts con `defer` en orden de dependencia:
  `stats.js`, `validaciones.js`, `tour.js`, `editar.js` y/o `crear.js`,
  `consultar.js`. El helper global ya se carga desde `script.php`.
- Todos los textos de usuario y nombres de variables nuevos deben seguir la
  convención en español del repositorio.

---

## 7. Base de datos y seguridad (no romper)

- Esquemas SQL de referencia en `docs/bd/dirpoles_{security,business}.sql`
  y scripts incrementales idempotentes: `docs/bd/notificaciones_modulo.sql`,
  `docs/bd/login_intentos.sql`, `docs/bd/bitacora_respaldo.sql`.
- RBAC: tablas `modulo`, `permiso` (**1=Crear, 2=Leer, 3=Editar, 4=Eliminar**),
  `rol_modulo_permiso` (rol × módulo × permiso). El sidebar se filtra por
  permiso id 2 (Leer) vía `PermisosModel::obtenerPermisosSidebar`.
- Módulo real de Notificaciones = `id_modulo` 19 (nombre `Notificaciones`).
- **Unicidad global de identidad**: cédula (tipo+documento), correo y
  teléfono son únicos entre `empleado` (security), `beneficiario` y
  `proveedores` (business). Los `existeX()`/`validarX()` de `EmpleadoModel`,
  `BeneficiarioModel` y `PerfilModel` lo resuelven con un `UNION ALL`
  cross-schema ejecutado desde la conexión **business**
  (`dirpoles_security.empleado` calificado; patrón ya usado por `CitaModel`
  y `HorarioModel`). Se compite por igual tipo de documento (V↔V, J↔J): un
  RIF J/G nunca bloquea una cédula V/E ni al revés; el `id_excluir` de
  edición solo se aplica en la rama de la tabla propia. En producción con
  usuarios de BD separados hace falta `GRANT SELECT` de cada esquema sobre
  la tabla ajena. **Deuda técnica**: ninguna de las tres tablas tiene
  `UNIQUE KEY` en estos campos; la única barrera hoy es el código (una
  carrera puede duplicar).
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
                        citaController.php, horarioController.php,
                        permisosController.php, configuracionController.php,
                        bitacoraController.php, backupController.php,
                        trabajoSocialController.php, inventarioController.php,
                        referenciaController.php, mobiliarioController.php,
                        jornadaController.php
                        (solo funciones)
app/Models/             loginModel, PermisosModel, NotificacionesModel,
                        DashboardModel, CalendarioModel, SecurityModel, BusinessModel,
                        EmpleadoModel, BeneficiarioModel, CitaModel, HorarioModel,
                        ConfiguracionModel, BitacoraModel,
                        BackupModel, InventarioModel, ReferenciaModel,
                        MobiliarioModel, JornadaModel
app/Views/              template/ (head, header, sidebar, footer, script),
                        inicio/dashboard.php (shell compuesto por rol), login.php,
                        errors/ (404, error, rate_limit, access_denied),
                        inicio/components/ (stat_card, card_modulo, stats_*),
                        empleados/, beneficiarios/ (crear + consultar con modal),
                        citas/ (crear + consultar con edición),
                        horarios/ (crear + consultar con edición),
                        permisos/ (matriz de permisos rol × módulo),
                        configuracion/ (crear + consultar catálogos, respaldo BD),
                        bitacora/ (consulta de auditoría),
                        trabajo-social/ (hub crear con 4 pestañas + consultar con
                        DataTables; components/ con stats y estudio-socioeconomico),
                        inventario/ (crear + consultar con DataTables y modales
                        de editar/entrada/salida/historial; components/stats.php),
                        referencias/ (crear con cascada servicio→empleados +
                        consultar con DataTables, detalle con historial y modal
                        de rechazo; components/stats.php),
                        mobiliario/ (hub crear con 3 pestañas + consultar con
                        DataTables por pestaña, modales de editar ×3,
                        reubicación e historial; components/stats.php),
                        jornadas/ (crear + consultar con DataTables y modal
                        de edición, detalle/{id} con asistentes y
                        diagnósticos; components/stats.php)
app/Config/             modulos_sidebar.php, dashboard_cards.php, roles_sistema.php,
                        configuracion_catalogos.php, Keys/ (RSA, no versionadas)
                        (NO existe config.php: la config va por .env)
app/routes/             notificaciones.php, dashboard.php, empleados.php,
                        beneficiarios.php, citas.php, horarios.php,
                        permisos.php, configuracion.php, bitacora.php,
                        backup.php, trabajo-social.php, inventario.php,
                        referencias.php, mobiliario.php, jornadas.php
                        (los demás módulos los creas tú)
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
                        js/modulos/{empleado,beneficiario,cita,horario,configuracion,bitacora,permisos}/,
                        js/modulos/trabajo-social/ (crear, consultar, editar,
                        pendientes, estudio, stats, tour, validaciones*),
                        js/modulos/inventario/ (stats, tour, validaciones,
                        crear, editar, consultar),
                        js/modulos/referencias/ (stats, tour, validaciones,
                        crear, consultar),
                        js/modulos/mobiliario/ (stats, tour, validaciones,
                        crear, editar, consultar),
                        js/modulos/jornadas/ (stats, tour, validaciones,
                        crear, editar, consultar, detalle, diagnosticos),
                        css/dashboard/dashboard.css
plugins/                Librerías front auto-hospedadas (Bootstrap 5, DataTables, Select2,
                        SweetAlert2, FullCalendar, jsPDF, jsencrypt...)
GUIA-MODULOS.md         ← GUÍA PRINCIPAL para crear módulos nuevos
GUIA-FRONTEND.md        ← GUÍA del frontend (helpers, patrón de vistas/JS, checks)
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

La IA no ejecuta estas comprobaciones. Debe revisar estáticamente los archivos
modificados y entregar al usuario los comandos equivalentes para ejecutarlos
manualmente en su terminal local.


---

## 11. Protocolo obligatorio para crear un módulo

Cuando el usuario pida "crear", "elaborar", "terminar" o "ajustar" un
módulo, la IA debe ejecutar este flujo completo. No debe detenerse después de
crear solo el modelo o la vista.

### 11.1 Descubrimiento mínimo antes de editar

Antes del primer cambio, la IA debe identificar y documentar mentalmente:

1. La tabla o tablas del módulo en `docs/bd/dirpoles_business.sql` o
   `docs/bd/dirpoles_security.sql`.
2. El `id_modulo` y los permisos reales en la tabla `modulo`/
   `rol_modulo_permiso`.
3. El rol que puede crear, consultar, editar y eliminar.
4. Un módulo nuevo equivalente para copiar estructura, no código ciego:
   - CRUD y DataTables: `empleado` o `beneficiario`.
   - Autoservicio: `perfil`.
   - Agenda y disponibilidad: `cita` y `horario`.
5. Las reglas del sistema viejo en `../DIRPOLES_4`, si existen; solo para
   descubrir reglas funcionales que el SQL no explica.
6. Las rutas, nombres de campos y restricciones de base de datos que deben
   permanecer compatibles.
   

La IA debe formar una hipótesis local sobre el comportamiento y realizar una
edición pequeña seguida de una validación ejecutable antes de ampliar el
alcance.

### 11.2 Entregables mínimos de un módulo CRUD

Salvo que el usuario limite explícitamente el alcance, un módulo CRUD debe
incluir:

```text
app/Models/<Modulo>Model.php
app/Controllers/<modulo>Controller.php
app/routes/<modulo>.php
app/Views/<modulo>/crear.php                 si existe creación
app/Views/<modulo>/consultar.php             si existe consulta
app/Views/<modulo>/components/              si hay stats o piezas repetidas
dist/js/modulos/<modulo>/validaciones.js
dist/js/modulos/<modulo>/tour.js             si hay formulario
dist/js/modulos/<modulo>/stats.js            si hay tarjetas
dist/js/modulos/<modulo>/crear.js            si existe creación
dist/js/modulos/<modulo>/editar.js           si existe edición/modal
dist/js/modulos/<modulo>/consultar.js        si existe tabla
```

También debe actualizar, cuando corresponda:

- `app/Config/modulos_sidebar.php`.
- `app/Config/dashboard_cards.php`.
- `docs/bd/` con un script idempotente si hacen falta datos RBAC o cambios de
  esquema.
- `AGENTS.md` si se incorpora una regla permanente o una deuda técnica nueva.

### 11.3 Secuencia backend obligatoria

1. **Modelo**
   - Extender `BusinessModel` o `SecurityModel` según la tabla propietaria.
   - Declarar atributos y validar cada asignación en `__set()`.
   - Implementar `__get()` y `manejarAccion()`.
   - Mantener métodos SQL privados.
   - Lanzar `ExcepcionApi` en toda validación, duplicado, ausencia, conflicto
     o error técnico.
   - Validar relaciones, estado activo, permisos de alcance, fechas, horas,
     solapamientos, unicidad y reglas de negocio en backend aunque ya exista
     validación frontend.
   - Si consulta ambas bases de datos, inicializar explícitamente la conexión
     adicional con el patrón de `Database`.

2. **Controlador**
   - Crear solo funciones.
   - Poner `Autorizacion::verificar()` como primera instrucción de cada función
     pública, salvo una comprobación adicional de rol que venga inmediatamente
     después.
   - Tomar JSON o `$_POST`, nunca leer parámetros de negocio desde la sesión
     excepto el usuario autenticado y su rol.
   - Para operaciones propias, ignorar IDs de propietario enviados por el
     cliente y derivarlos de `$_SESSION`.
   - Auditar cada escritura con `Bitacora::registrar()`.
   - Notificar solo cuando exista un receptor real y el efecto lo requiera.
   - Responder exclusivamente mediante `Respuesta`.

3. **Rutas**
   - Páginas bajo `<modulo>/...` y solo `GET`.
   - APIs bajo `api/<modulo>/...`.
   - Lecturas por `GET`, escrituras por `POST`.
   - Usar `{id}` cuando sea un parámetro de URL; leerlo desde `$_GET['id']`.
   - No conservar rutas antiguas sin prefijo `api/` salvo compatibilidad
     explícitamente solicitada.

### 11.4 Secuencia frontend obligatoria

> Lee primero `GUIA-FRONTEND.md` (patrón canónico: `app/Views/empleados/` +
> `dist/js/modulos/empleado/`) y copia ese patrón antes de escribir.

#### Formularios

- Incluir `novalidate` y errores junto a cada campo.
- Validar en tiempo real y nuevamente al enviar.
- Pintar `is-invalid` con mensaje y `is-valid` solo después de una validación
  exitosa real.
- Para validaciones remotas usar `apiFetch`, debounce y manejar errores por
  `error.codigo`; una API caída no equivale a "válido": marcar en ROJO
  "No se pudo verificar…" y bloquear el envío (nunca verde).
- Para `select2`:
  1. clase `select2` en el `<select>`;
  2. `data-placeholder` coherente;
  3. `window.initSelect2(form)` después de opciones AJAX;
  4. eventos `change select2:select select2:clear`;
  5. clases en el `<select>` y en `.select2-selection`;
  6. reset probado visualmente.
- Para contraseñas, fechas, horas, rangos y confirmaciones, validar también
  dependencias entre campos, no solo cada campo aislado.
- Tras guardar, mostrar éxito, limpiar/rellenar controles y actualizar stats o
  tabla sin recarga completa cuando el módulo ya usa ese patrón.

#### Tours

- Toda pantalla de creación/edición con varios controles debe tener `tour.js`
  y `#btn-ayuda`.
- El tour debe omitir o resolver con gracia elementos ausentes.
- En Select2, enfocar el hermano `.select2-container` visible:

```js
function objetivo(selector) {
    const elemento = document.querySelector(selector);
    if (!elemento) return selector;
    if (elemento.tagName === 'SELECT' && elemento.classList.contains('select2')
        && elemento.nextElementSibling?.classList.contains('select2-container')) {
        return elemento.nextElementSibling;
    }
    return elemento;
}
```

#### Consultas

- Usar DataTables para tablas de consulta.
- Cargar datos con `apiFetch`, escapar texto antes de insertar HTML y destruir la
  instancia antes de pintar una recarga.
- Incluir idioma local, búsqueda, paginación y ordenamiento.
- Incluir Excel/PDF si el módulo consulta datos operativos, excluyendo Acciones.
- Nunca usar IDs repetidos dentro de filas generadas; usar clases y delegación.
- El estado vacío se representa con `tbody` vacío, no con una fila `colspan`
  incompatible con el número de columnas.

### 11.5 Roles y alcance

La IA debe distinguir entre permiso RBAC y alcance de datos:

| Usuario | Citas | Horarios | Regla de alcance |
|---|---|---|---|
| Administrador | Crear/leer/editar/eliminar | Crear/leer/editar/eliminar | Puede asignar citas a cualquier psicólogo activo |
| Superusuario | Según permisos concedidos | Según permisos concedidos | La UI y backend lo tratan como administrativo para Horarios |
| Psicólogo | Sus citas | Sin acceso | Solo puede crear y gestionar citas con su propio ID |
| Otros empleados | Sin acceso salvo permiso explícito | Sin acceso | No asumir permisos por tipo de empleado |

Esta tabla es específica de los módulos actuales; cada módulo nuevo debe
definir su propia matriz antes de escribir controladores. El backend debe
repetir la restricción aunque el sidebar o el formulario oculten controles.

### 11.6 Criterio de módulo terminado

Antes de afirmar que un módulo está listo, comprobar todos los puntos:

- [ ] SQL y relaciones entendidos.
- [ ] Modelo con `__set`, `__get`, `manejarAccion` y excepciones correctas.
- [ ] Controlador sin clases y con autorización primera.
- [ ] Rutas separadas por puerta HTML/JSON.
- [ ] RBAC, sidebar y dashboard configurados.
- [ ] Crear/consultar/editar/eliminar implementados según alcance solicitado.
- [ ] Validación backend duplicada en frontend.
- [ ] Validación en vivo, mensajes y clases visuales.
- [ ] Select2 probado después de carga AJAX y después de reset.
- [ ] Driver.js y botón de ayuda funcionando en cada formulario aplicable.
- [ ] DataTables y exportaciones funcionando en cada consulta aplicable.
- [ ] Stats con `data-stat` y una sola llamada.
- [ ] Bitácora en cada escritura y notificación cuando corresponda.
- [ ] Fechas, horas, rangos, conflictos y estados probados con datos inválidos.
- [ ] No se rompieron cambios ajenos en archivos modificados por el usuario.

La IA debe entregar, sin ejecutarlos, los comandos de sintaxis correspondientes
a cada archivo modificado. Como mínimo, incluir `php -l` para cada PHP y
`node --check` para cada JavaScript del módulo.

### 11.7 Pruebas mínimas por módulo

La IA debe enumerar estos escenarios y dejar al usuario los pasos para probarlos
manualmente cuando sean aplicables. La IA no ejecuta comandos ni pruebas de
terminal por la regla de optimización de tokens:

1. Página accesible con rol permitido.
2. Página rechazada con rol no permitido.
3. API sin sesión responde 401 con el contrato.
4. API con permiso insuficiente responde 403 con el contrato.
5. Campo vacío, formato inválido y longitud inválida.
6. Duplicado o conflicto de negocio.
7. Fecha/hora fuera de rango y solapamiento.
8. Creación exitosa y auditoría.
9. Edición exitosa y exclusión del propio registro en validaciones únicas.
10. Eliminación exitosa y registro inexistente.
11. Recarga de tabla y estado vacío sin warnings de DataTables.
12. Select2: seleccionar, limpiar y resetear.
13. Driver.js: botón, objetivo visible y avance de pasos.
14. Validación remota fallida (API caída o 500, p. ej. validación de unicidad o
    de disponibilidad): campo en ROJO con "No se pudo verificar…" y envío
    bloqueado hasta reintentar; en ningún caso queda en verde.

Si no hay entorno de navegador, la IA debe informar que la validación visual
queda pendiente. No debe afirmar que la interfaz fue probada porque haya
inspeccionado el código o porque el usuario aún no haya ejecutado los comandos.

### 11.8 Regla de comunicación con el usuario

Al terminar un módulo, el resumen debe mencionar:

- Archivos creados y modificados.
- Roles y permisos aplicados.
- Reglas funcionales implementadas.
- Validaciones frontend/backend.
- Revisión estática realizada y cualquier prueba manual pendiente.
- Datos SQL/RBAC que el usuario todavía debe ejecutar o verificar.

**Comandos para ejecutar manualmente**

La respuesta final debe incluir una sección explícita con los comandos que el
usuario debe ejecutar en su terminal local. Debe adaptar los nombres al módulo
real, por ejemplo:

```text
He terminado con los cambios. Por favor, ejecuta los siguientes comandos en tu terminal para verificar que la sintaxis de todos los archivos sea correcta:

php -l app/Models/<Modulo>Model.php
php -l app/Controllers/<modulo>Controller.php
node --check dist/js/modulos/<modulo>/crear.js
```

Si existen más archivos PHP o JavaScript modificados, debe añadir un comando
para cada uno. Si la tarea requiere pruebas de navegador, debe indicar también
los escenarios que el usuario debe probar manualmente y no afirmar que fueron
ejecutados por la IA.

No decir "módulo completo" si falta una parte del checklist anterior.
