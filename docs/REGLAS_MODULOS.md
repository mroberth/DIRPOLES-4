# REGLAS_MODULOS.md — Reglas de Negocio Específicas por Módulo (DIRPOLES-4)

> Este archivo complementa a `AGENTS.md`. Contiene las reglas funcionales,
> restricciones de edición/eliminación, alcance y particularidades históricas
> de cada módulo construido sobre el esqueleto DIRPOLES-4. Consúltalo cuando
> debas trabajar, auditar o extender un módulo específico.

---

## 1. Empleados (id_modulo 1)
- **Base de Datos**: `dirpoles_security.empleado`.
- **RBAC**: Administrador (6) y Superusuario (10) con CRUD completo.
- **Unicidad**: Cédula (tipo + documento), correo y teléfono son únicos a nivel global (compiten con `beneficiario` y `proveedores` mediante consulta cross-schema).
- **Validaciones**: Asíncronas en tiempo real vía `apiFetch`.

---

## 2. Beneficiarios (id_modulo 2)
- **Base de Datos**: `dirpoles_business.beneficiario`.
- **RBAC**: Administrador, Superusuario y roles operativos que registren beneficiarios.
- **Unicidad**: Cédula, correo y teléfono compiten en el `UNION ALL` cross-schema con `empleado` y `proveedores`.
- **Estatus**: Beneficiario activo/inactivo.

---

## 3. Citas (id_modulo 3)
- **Base de Datos**: `dirpoles_business.citas`.
- **Área**: Psicología.
- **Alcance**: 
  - Administrador/Superusuario: asigna y gestiona citas para cualquier psicólogo activo.
  - Psicólogo (rol 1): SOLO gestiona citas donde su `id_empleado` coincide con el psicólogo asignado.
- **Disponibilidad**: Validada contra la tabla `horarios` del psicólogo.
- **Estados**: Programada, Atendida, Cancelada.

---

## 4. Medicina (id_modulo 5)
- **Tablas**: `dirpoles_business.medicina`, `solicitud_de_servicio` (`id_servicios=2`), `insumos`, `inventario_medico`.
- **RBAC**: Médico (rol 2), Administrador (6), Superusuario (10). Enfermero (rol 12) pendiente.
- **Insumos en Diagnóstico**:
  - Descuento de stock en transacción con `FOR UPDATE`.
  - `CASE` a `'Agotado'` debe evaluarse **antes** de restar en MySQL para evitar desajustes de stock.
- **Reglas permanentes de Edición**:
  - La edición **SOLO** permite: `patologia`, `estatura`, `peso`, `tipo_sangre`, `motivo`, `diagnostico`, `tratamiento`, `observaciones`.
  - **JAMÁS** permite editar: beneficiario, empleado que atendió ni los insumos descargados.
- **Regla permanente de Eliminación**:
  - Al eliminar un registro médico **NO se revierte el inventario** (el insumo ya fue utilizado físicamente: `insumos.cantidad` e `inventario_medico` quedan intactos, la Bitácora audita los insumos usados).
- **Recipe Médico (Tercera Puerta PDF)**:
  - `GET medicina/recipe/{id}` genera PDF en línea (`GenerarRecipe` en `docs/PDF/recipe/procesar.php`).
  - No usa query params (`tramite`/`hora`); abre directo con `window.open` desde el botón `js-recipe`.

---

## 5. Orientación (id_modulo 6)
- **Tablas**: `solicitud_de_servicio` (`id_servicios=3`) + `orientacion`.
- **RBAC**: Orientador (rol 4), Administrador (6), Superusuario (10).
- **Reglas permanentes**:
  - Los 4 campos de texto (`motivo`, `descripcion`, `indicaciones`, `observaciones`) son **obligatorios** (corrige inconsistencias del sistema anterior).
  - La edición **SOLO** permite modificar estos 4 textos (jamás beneficiario ni empleado).
  - Al eliminar se borran `orientacion` + `solicitud_de_servicio` en una sola transacción. Sin inventario ni `detalle_patologia`.

---

## 6. Discapacidad (id_modulo 8)
- **Tablas**: `solicitud_de_servicio` (`id_servicios=5`) + `discapacidad`.
- **RBAC**: Discapacidad (rol 5), Administrador (6), Superusuario (10).
- **Reglas permanentes**:
  - Son obligatorios: `tipo_discapacidad`, `diagnostico`, `grado`, `habilidades_funcionales`, `observaciones`.
  - Los campos ENUM (`tipo_discapacidad`, `grado`, `requiere_asistencia`) se validan contra el esquema estricto.
  - La edición **SOLO** permite los 11 campos de la tabla propia (jamás beneficiario ni empleado).
  - Al eliminar se borran `discapacidad` + `solicitud_de_servicio` en transacción.
  - Stats: `total`, `del_mes`, `graves`, `con_carnet`.

---

## 7. Trabajo Social (id_modulo 7)
- **Tablas**: `solicitud_de_servicio` (`id_servicios=4`), `becas`, `exoneraciones`, `fames`, `embarazadas`.
- **RBAC**: Trabajador Social (rol 3), Administrador (6), Superusuario (10).
- **Hub con 4 pestañas**:
  - Cada sub-registro vive en su tabla con `id_solicitud_serv`. Los listados usan `LEFT JOIN` sin duplicación.
  - La edición **SOLO** permite los campos editables de cada tipo (nunca beneficiario, empleado ni archivos).
- **Estudio Socioeconómico (Offcanvas de 5 pasos → FPDF)**:
  - Solo habilitado para exoneraciones **pendientes** (`direccion_estudiose IS NULL`).
  - Requiere permiso `crear`. Foto opcional validada por extensión (jpg/jpeg/png/gif) y MIME.
  - Los campos detallados del estudio no se persisten en tablas SQL (el PDF físico es el registro legal; la Bitácora guarda insumos).
  - Vínculo seguro: `UPDATE exoneraciones SET direccion_estudiose = ... WHERE id_solicitud_serv = ... AND direccion_estudiose IS NULL`. Si no afecta filas, se borra el PDF huérfano para evitar colisiones.
- **Eliminación**: Borra sub-registro + solicitud y elimina el PDF físico bajo `uploads/trabajo_social/` (best-effort).

---

## 8. Inventario Médico (id_modulo 9)
- **Tablas**: `insumos`, `inventario_medico` (kardex).
- **RBAC**: Nombre `'inventario medico'` en minúsculas para autorización; roles 2 (Médico), 6 (Admin), 10 (Super).
- **Reglas permanentes de Stock**:
  - **Crear insumo NO asigna cantidad**: Nace con cantidad `0`, estatus `'Agotado'` y movimiento `'Registro'` en el kardex.
  - El stock entra **únicamente** con el botón/modal **Entrada** (permiso `crear`).
  - Fecha de vencimiento: al CREAR debe ser `>= hoy`. Al EDITAR se admite cualquier fecha válida.
  - La edición **SOLO** permite: `nombre`, `tipo`, `presentacion`, `fecha_vencimiento`, `descripcion` (jamás cantidad ni estatus).
  - **Entrada**: Solo insumos no vencidos (`fecha_vencimiento >= hoy`).
  - **Salida**: Solo con cantidad `> 0` y motivo obligatorio del catálogo: `Vencimiento|Daño|Pérdida|Donación|Uso Interno`.
  - Ambos movimientos usan transacción + `FOR UPDATE` calculando stock en PHP.
- **Eliminación**: Bloqueada si el insumo está en `detalle_insumo`, si tiene stock `> 0` o si tiene movimientos distintos a `'Registro'`.
- **Estatus efectivo**: Si `fecha_vencimiento < hoy`, la UI muestra `'Vencido'`.

---

## 9. Referencias (id_modulo 10)
- **Tablas**: `referencias`, `log_referencias`.
- **RBAC**: Roles 1, 2, 3, 4, 5, 6 y 10.
- **Reglas permanentes de Flujo**:
  - **Origen/Destino**: El Administrador puede elegir emisor y receptor. Cualquier otro rol es forzado en backend como emisor (`id_empleado_origen` = sesión).
  - El servicio de destino debe ser **diferente** al de origen.
  - Motivo y observaciones son **obligatorios**.
  - Nace en estado `'Pendiente'` y notifica al destinatario vía `Notificador::enviar()`.
  - **Aceptar/Rechazar**: Solo el empleado destino o el Administrador. Solo desde estado `'Pendiente'` con transacción + `FOR UPDATE`. El rechazo exige motivo que va a `log_referencias.observaciones` (no pisa la referencia original).
  - **Eliminar**: Solo el origen o Administrador, y solo si sigue `'Pendiente'`. Borra log primero (FK) y luego la fila padre.
  - **Alcance**: Sin rol Admin, el usuario solo lista y cuenta referencias donde es origen o destino.

---

## 10. Mobiliario (id_modulo 12)
- **Tablas**: `mobiliario`, `equipos`, `fichas_tecnicas`, `detalle_ficha_mobiliario`, `detalle_ficha_equipo`, `historial_inventario`.
- **RBAC**: Roles 6 (Admin) y 10 (Super) con 4 permisos.
- **Reglas permanentes**:
  - **Ficha Técnica**: Máximo **una activa por empleado responsable**. Requiere obligatoriamente al menos un ítem (mobiliario o equipo).
  - **Serial de Equipos**: Único y obligatorio a nivel de sistema.
  - **Disponibilidad de Mobiliario**: `cantidad` total menos lo asignado en fichas activas (calculado dinámicamente en SQL; nunca se muta `mobiliario.cantidad` al asignar).
  - Un equipo no puede estar asignado a dos fichas activas simultáneamente.
  - La edición de mobiliario no puede fijar una cantidad menor a lo actualmente asignado.
  - **Baja lógica** (`estatus = 'Inactivo'`): Bloqueada si el ítem está en una ficha activa.
  - Al editar una ficha, los detalles se reemplazan de forma completa en transacción.
  - Todo movimiento registra en `historial_inventario` (`asignacion`, `reubicacion`, `modificacion`, `baja`).

---

## 11. Jornadas Médicas (id_modulo 11)
- **Tablas**: `jornadas_medicas`, `jornada_beneficiarios`, `jornada_diagnosticos`, `jornada_insumos`.
- **RBAC**: Roles 2 (Médico), 6 (Admin) y 10 (Super).
- **Reglas permanentes**:
  - **Aforo**: Verificado bajo transacción con `FOR UPDATE` sobre la cabecera de la jornada. Dos altas concurrentes nunca exceden el aforo.
  - **Asistentes**: Registro manual con sugerencias opcionales por cédula (`buscar_persona`). Una cédula no se repite en la misma jornada. Solo se registran si la jornada está `'Activa'` y vigente.
  - Eliminar asistente bloqueado si ya posee diagnósticos. Eliminar jornada solo si no tiene asistentes (en caso contrario, cancelarla).
  - **Diagnósticos**: Múltiples diagnósticos por asistente. Los 3 campos de texto (`diagnostico`, `tratamiento`, `observaciones`) son obligatorios.
  - **Insumos**: Descuento con `FOR UPDATE` sobre `insumos` con estatus `'Disponible'`, no vencidos y con stock. Salida en kardex `inventario_medico`. Máximo 30 insumos por diagnóstico. Al eliminar un diagnóstico, el stock **NO** se devuelve.

---

## 12. Transporte (id_modulo 13)
- **Tablas**: `rutas`, `vehiculos`, `proveedores`, `repuestos_vehiculos`, `inventario_repuestos` (kardex), `asignaciones_rutas`, `mantenimiento_vehiculos`, `repuestos_mantenimiento`.
- **RBAC**: Roles 6 (Admin) y 10 (Super).
- **Reglas permanentes**:
  - **Asignaciones**: Un chofer o vehículo no puede tener dos asignaciones activas el **mismo día** (`verificarSolapamientoDia()`).
  - **Choferes**: El selector solo admite empleados activos con rol Chofer (`id_tipo_emp = 8`).
  - `asignaciones_rutas.id_asignacion` tiene `AUTO_INCREMENT` corregido en el script `docs/bd/transporte_asignaciones_autoincrement.sql`.
  - **Repuestos**: Nacen con stock `0`, `'Agotado'` y movimiento `'Registro'`. Entrada/Salida validada con transacción y motivos fijos (`MOTIVOS_ENTRADA`, `MOTIVOS_SALIDA`).
  - **Mantenimiento**: Pone el vehículo en `'Mantenimiento'`. Eliminar mantenimiento solo reactiva el vehículo si no le restan otros activos.
  - Los repuestos asociados a mantenimientos se consultan en lote (evitando N+1).

---

## 13. Reportes Estadísticos (id_modulo 15)
- **RBAC**: Roles 1, 2, 3, 4, 5, 6, 10 y 11 con los 4 permisos.
- **Reglas permanentes**:
  - **Filtros Server-Side**: La lista blanca en `reportesAplicarFiltros()` solo admite: `fecha_inicio`, `fecha_fin`, `genero`, `pnf`, `servicio_destino`, `area`, `estado`, `tipo_consulta`, `submodulo`, `grado`, `tipo_discapacidad`, `tipo_bien`, `tipo_vehiculo`, `reporte`, `limit`.
  - Parámetros desconocidos se descartan; valores inválidos responden 400.
  - `limit`: por defecto 5000, máximo 20000, bindeado con `PDO::PARAM_INT`.
  - **Contrato de datos**:
    - Reportes simples devuelven array `datos`.
    - Psicología devuelve `{ morbilidad: [], citas: [] }`.
    - Medicina devuelve `{ consultas: [], insumos: [] }`.
    - Endpoint `stats?reporte=<clave>` devuelve objeto de contadores.
  - Toda consulta audita en Bitácora con la acción `'Lectura'` (nunca `'Consulta'`).
  - Gráficos renderizados con Chart.js local v2.9.4 (`ReportesComunes.grafico()`).

---

## 14. Horarios (id_modulo 18)
- **Tablas**: `dirpoles_business.horarios`.
- **RBAC**: Exclusivo para Administrador (6) y Superusuario (10).
- **Reglas**:
  - Máximo un horario por psicólogo y día de la semana.
  - Rango horario válido entre las 07:00 y las 17:00.

---

## 15. Centro de Ayuda (Grupo transversal `group_ayuda`)
- **Controlador**: `ayudaController.php`, Modelo: `AyudaModel.php`.
- **Comportamiento**: Guías interactivas y manuales filtrados dinámicamente según los permisos del usuario (`$_SESSION['modulosPermitidos']` y `$_SESSION['id_tipo_empleado']`).
