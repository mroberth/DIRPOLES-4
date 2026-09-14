# DIRPOLES 4 — Contexto Completo del Sistema

## Información General

**Nombre del Sistema:** DIRPOLES 4 (Dirección de Políticas Estudiantiles)
**Institución:** UPTAEB (Universidad Politécnica Territorial Augusto Baldó de Barquisimeto)
**Tipo de Sistema:** Sistema de Gestión Integral para la atención de estudiantes beneficiarios
**Arquitectura:** Monolito híbrido PHP (backend renderiza frontend vía `require_once`)
**Base de Datos:** MySQL (dos bases: `dirpoles_security` para usuarios/permisos, `dirpoles_business` para lógica del negocio)
**Fecha de Desarrollo:** 2024-2026 (proyecto universitario)

---

## ¿Qué hace el sistema?

DIRPOLES 4 es un sistema web que gestiona toda la atención integral a estudiantes beneficiarios de la UPTAEB. Cubre desde la creación del expediente del beneficiario, pasando por agendamiento de citas, diagnósticos en 5 áreas especializadas, control de inventarios, transporte, hasta la generación de reportes estadísticos y document oficial (constancias, recipes, estudios socioeconómicos).

---

## Roles y Tipos de Empleado

El sistema tiene 13 tipos de empleado definidos en la tabla `tipo_empleado`:

| ID | Rol | Servicio Asociado |
|----|-----|-------------------|
| 1 | Psicólogo | Psicología |
| 2 | Médico | Medicina |
| 3 | Trabajador Social | Trabajo Social |
| 4 | Orientador | Orientación |
| 5 | Discapacidad | Discapacidad |
| 6 | Administrador | Gerente |
| 7 | Secretaria | General |
| 8 | Chofer | Transporte |
| 9 | Mecánico | Transporte |
| 10 | Superusuario | Gerente |
| 11 | Administrativo | General |
| 12 | Enfermero | Medicina |
| 13 | Obreros | General |

### Permisos CRUD

Cada módulo tiene 4 permisos básicos:
- **Crear** (ID 1) — Registrar nuevos registros
- **Leer** (ID 2) — Consultar registros existentes
- **Editar** (ID 3) — Modificar registros existentes
- **Eliminar** (ID 4) — Borrar registros

Los permisos se asignan por rol × módulo en la tabla `rol_modulo_permiso`. El Administrador y Superusuario tienen acceso total a todos los módulos.

---

## Estructura del Sidebar (Menú Lateral)

El sidebar se genera dinámicamente desde `app/Config/modulos_sidebar.php` y filtra los módulos según los permisos del usuario logueado.

### Módulos del Sistema

| ID | Módulo | Submenús |
|----|--------|----------|
| — | **Inicio** | Siempre visible. Calendario de actividades + dashboard |
| 1 | **Gestionar Empleados** | Crear, Consultar |
| 2 | **Gestionar Beneficiarios** | Crear, Consultar |
| 3 | **Gestionar Citas** | Crear, Consultar |
| 4-8 | **Gestionar Diagnósticos** | Psicología, Medicina, Orientación, Trabajo Social, Discapacidad |
| 9 | **Gestionar Inventario Médico** | Crear, Consultar |
| 10 | **Gestionar Referencias** | Crear, Consultar |
| 11 | **Gestionar Jornadas** | Crear, Consultar |
| 12 | **Gestionar Mobiliario** | Crear, Consultar |
| 13 | **Gestionar Transporte** | Consultar |
| 14 | **Configuraciones** | Crear, Consultar, Bitácora, Permisos Empleados, Respaldo BD (solo Admin) |
| 15 | **Reportes Estadísticos** | General, Psicología, Medicina, Orientación, Trabajo Social, Discapacidad, Referencias, Jornadas, Mobiliario, Transporte |
| — | **Ayuda** | Guía de Usuario |
| — | **Cerrar Sesión** | Siempre visible |

### Visibilidad por Rol

- **Administrador/Superusuario:** Ve TODO el sidebar incluyendo "Respaldo BD"
- **Empleado especializado (Psicólogo, Médico, etc.):** Solo ve los módulos a los que tiene permisos asignados
- **Ayuda:** Siempre visible para todos los roles

---

## Módulos Detallados

### 1. INICIO DE SESIÓN
- **URL:** `/login`
- **Funcionalidad:** Formulario con correo electrónico y contraseña
- **Seguridad:** Contraseña cifrada con RSA + hash bcrypt, JWT con RS256
- **Dashboard:** Calendario de actividades con eventos programados + métricas del sistema
- **Bienvenida:** Muestra nombre del usuario en el encabezado

### 2. GESTIONAR EMPLEADOS (Módulo 1)
- **Crear Empleado:** Formulario con datos personales (nombre, apellido, cédula, teléfono, correo, fecha nacimiento, dirección), asignación de rol/cargo, y contraseña inicial
- **Consultar Empleados:** DataTable con listado de empleados, botones de Ver (modal solo lectura), Editar (modal con formulario), Eliminar (SweetAlert confirmación + AJAX POST)
- **Campos:** Nombre, Apellido, Tipo de cédula (V/E), Cédula, Correo, Teléfono, Dirección, Fecha nacimiento, Rol, Estatus (Activo/Inactivo)
- **Validaciones:** Cédula única, correo único, campos obligatorios

### 3. GESTIONAR BENEFICIARIOS (Módulo 2)
- **Crear Beneficiario:** Formulario con datos del estudiante beneficiario
- **Consultar Beneficiarios:** DataTable con botones de Ver, Editar, Eliminar
- **Campos:** Cédula, Nombres, Apellidos, PNF (Programa Nacional de Formación), Semestre, Sección, Teléfono, Correo, Dirección
- **Relación:** Se vincula con diagnósticos a través de `solicitud_de_servicio`

### 4. GESTIONAR CITAS (Módulo 3)
- **Crear Cita:** Formulario que selecciona beneficiario + empleado + fecha + hora + servicio
- **Consultar Citas:** DataTable con estados de cita (Programada, Pendiente, Completada, Cancelada)
- **Estados:** Los estados se gestionan con botones de acción
- **Calendario:** Las citas aparecen en el calendario del dashboard
- **Notificación:** Al crear una cita se genera notificación automática

### 5. DIAGNÓSTICOS (Módulos 4-8)

Los diagnósticos son el corazón del sistema. Cada área especializada tiene su propio módulo con crear, consultar, ver, editar, eliminar, generar constancia (PDF) y generar referencia (PDF).

#### 5.1 PSICOLOGÍA (Módulo 4)
- **Tabla:** `consulta_psicologica`
- **Crear:** Formulario con tipo de consulta (Diagnóstico, Atención, Seguimiento), patología, diagnóstico, tratamiento, observaciones
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Generar Constancia (PDF), Generar Referencia (PDF)
- **Campos:** Tipo de consulta, Diagnóstico, Tratamiento general, Motivo de retiro, Duración del retiro, Motivo de cambio, Observaciones, Patología asociada
- **Constancia PDF:** Muestra nombre del beneficiario, cédula y fecha en una plantilla predefinida
- **Referencia PDF:** Muestra datos del beneficiario, fecha, nombre del empleado, cargo y teléfono

#### 5.2 MEDICINA (Módulo 5)
- **Tabla:** `consulta_medica`
- **Crear:** Formulario médico con examen físico, signos vitales, diagnóstico, tratamiento
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Generar Recipe (PDF), Generar Referencia (PDF)
- **Recipe PDF:** Usa plantilla `recipe_medico.png` con campos de medicamentos, indicaciones
- **Referencia PDF:** Muestra datos del paciente, motivo, diagnóstico

#### 5.3 ORIENTACIÓN (Módulo 6)
- **Tabla:** `orientacion`
- **Crear:** Formulario con motivo de orientación, descripción, recomendaciones
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Generar Constancia, Generar Referencia
- **Constancia/Referencia:** PDFs con datos del beneficiario y recomendaciones

#### 5.4 TRABAJO SOCIAL (Módulo 7)
Este es el módulo más complejo. Tiene 4 sub-módulos:

**a) Registro de Becas**
- **Tabla:** `becas`
- **Crear:** Formulario con beneficiario, tipo de banco, cuenta BCV, planilla (PDF obligatorio)
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

**b) Registro de Exoneraciones**
- **Tabla:** `exoneracion`
- **Crear:** Formulario con beneficiario, motivo de exoneración, carnet de discapacidad (si aplica), carta (PDF obligatorio)
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Generar Constancia, Generar Referencia, **Estudio Socioeconómico**
- **Estudio Socioeconómico:** Se abre en un panel deslizante (offcanvas de Bootstrap derecha a izquierda). Es un formulario extenso con:
  - Datos del beneficiario (nombre, cédula, edad, estado civil, teléfono, ocupación, dirección)
  - Grupo familiar (5 miembros con: nombre, edad, parentesco, estado civil, instrucción, ocupación, sueldo, aporte al hogar)
  - Tabla de ingresos y egresos (sueldo, trabajos, renta, pensiones, ayudas, otros / alimentación, vivienda, servicios, educación, transporte, salud)
  - Tenencia de la vivienda (propia, casa, alquilada, prestada, hipoteca, pagando, etc.)
  - Observaciones
  - Botón "Generar PDF" que crea el estudio socioeconómico
  - El PDF se guarda en `uploads/trabajo_social/exoneracion/estudiose/`
  - **IMPORTANTE:** Solo se puede generar el estudio si YA EXISTE un registro previo de exoneración

**c) Registro de FAMES**
- **Tabla:** `fames`
- **Crear:** Formulario con beneficiario, patología, tipo de ayuda, otro tipo
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

**d) Gestión de Embarazadas**
- **Tabla:** `gestion_emb`
- **Crear:** Formulario con beneficiaria, patología, semanas de gestación, código patria, serial patria, estado
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

#### 5.5 DISCAPACIDAD (Módulo 8)
- **Tabla:** `discapacidad`
- **Crear:** Formulario con datos del beneficiario, tipo de discapacidad, necesidades de apoyo
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Generar Constancia, Generar Referencia

### 6. INVENTARIO MÉDICO (Módulo 9)
- **Tabla:** `insumo_medico`
- **Crear:** Formulario con nombre del insumo, cantidad, fecha de vencimiento, presentación, proveedor
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar, Entrada (+), Salida (-)
- **Funcionalidad especial:** Control de stock con entradas y salidas, alertas de vencimiento

### 7. REFERENCIAS (Módulo 10)
- **Tabla:** `referencias`
- **Crear:** Formulario con beneficiario, servicio origen, servicio destino, empleado origen, empleado destino, motivo
- **Consultar:** DataTable con estados: Pendiente, Aceptada, Rechazada
- **Gestión:** El empleado destino puede aceptar o rechazar la referencia
- **Flujo:** Empleado origen crea → Empleado destino acepta/rechaza → Se registra en bitácora

### 8. JORNADAS (Módulo 11)
- **Tabla:** `jornada`
- **Crear:** Formulario con nombre de jornada, fecha, propósito
- **Consultar:** DataTable con listado de jornadas
- **Funcionalidad:** Eventos especiales de atención masiva

### 9. INVENTARIO MOBILIARIO (Módulo 12)
Tiene 3 pestañas en la vista de consultar:

**a) Mobiliario**
- **Tabla:** `mobiliario`
- **Crear:** Formulario con tipo de mobiliario, marca, modelo, color, cantidad, estado, servicio, fecha de adquisición, descripción, observaciones
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

**b) Equipos**
- **Tabla:** `equipos`
- **Crear:** Formulario con tipo de equipo, marca, modelo, serial, color, estado, servicio, fecha de adquisición, descripción, observaciones
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

**c) Fichas Técnicas**
- **Tabla:** `fichas_tecnicas`
- **Crear:** Modal con nombre de ficha, servicio, responsable (empleado), fecha de creación, descripción
- **Consultar:** DataTable con botones: Ver, Editar, Eliminar

### 10. TRANSPORTE (Módulo 13)
- **Consulta general** de flota vehicular
- Sub-módulos: Vehículos, Rutas, Proveedores, Repuestos, Mantenimientos

### 11. CONFIGURACIONES (Módulo 14)
- **Crear/Consultar configuraciones** del sistema
- **Bitácora:** Historial de todas las acciones del sistema (quién hizo qué y cuándo)
- **Permisos de Empleados:** Gestión de permisos por rol × módulo
- **Respaldo de BD:** Solo visible para Administrador/Superusuario

### 12. REPORTES ESTADÍSTICOS (Módulo 15)
- **Reporte General:** Filtros por fecha, género. Muestra tablas + gráficas automáticas
- **Reportes por Área:** Psicología, Medicina, Orientación, Trabajo Social, Discapacidad, Referencias, Jornadas, Mobiliario, Transporte
- **Exportación:** A Excel (.xlsx) y PDF (con membrete institucional)

### 13. AYUDA
- **Sistema de ayuda tipo Facebook** con barra de búsqueda inteligente
- **14 categorías** de ayuda con preguntas frecuentes
- Cada pregunta abre un **modal con pasos numerados** e íconos
- Botón **"Manual Completo"** en cada modal (actualmente muestra mensaje de "próximamente")
- Archivos: `ayuda.php` (vista), `ayuda.js` (lógica), `ayuda-data.js` (contenido de guías)

---

## Flujo General del Sistema

```
1. Login → Dashboard (Calendario + Métricas)
2. Crear Beneficiario → Se registra en el sistema
3. Crear Cita → Se agenda atención con empleado especializado
4. Registrar Diagnóstico → Según el área (Psicología, Medicina, Orientación, TS, Discapacidad)
5. Generar Documentos → Constancia, Recipe, Referencia (PDFs)
6. Inventario → Control de insumos médicos, mobiliario, equipos
7. Reportes → Análisis estadístico + exportación
8. Configuración → Permisos, bitácora, respaldo
```

---

## Documentos PDF Generados

| Documento | Ubicación | Plantilla |
|-----------|-----------|-----------|
| Constancia | `PDF/constancia/procesar.php` | `constancia.png` |
| Referencia | `PDF/referencia/procesar.php` | `referencia.png` |
| Recipe Médico | `PDF/MEDICINA/procesar.php` | `recipe_medico.png` |
| Estudio Socioeconómico | `PDF/EstudioSE/procesar.php` | `EstudioSocioeconomico.png` |
| Reportes | Generados con FPDF dinámicamente | Sin plantilla fija |

---

## Seguridad Implementada

- **Autenticación dual:** Sesión PHP + JWT (RS256 con Firebase)
- **Rate Limiting:** Token bucket por endpoint en BD
- **Prepared Statements:** Todas las queries usan `bindValue/bindParam`
- **CORS:** Configurado con preflight OPTIONS
- **Descifrado RSA:** Login descifra la contraseña con llave privada
- **Bitácora:** Registro automático de todas las acciones
- **Permisos:** Sistema CRUD por rol × módulo

---

## Instrucciones para Generar los Manuales

### Manual 1: Manual del Empleado (Roles: Psicólogo, Médico, Orientador, Trabajador Social, Discapacidad)

**Audiencia:** Empleados especializados que atienden beneficiarios
**Contenido esperado:**
1. Cómo iniciar sesión
2. Navegación por el sidebar
3. Gestión de Beneficiarios (solo consulta)
4. Gestión de Citas (crear y consultar)
5. Registro de Diagnósticos (según su especialidad):
   - Psicólogo: Psicología (crear, editar, eliminar, constancia, referencia)
   - Médico: Medicina (crear, editar, eliminar, recipe, referencia)
   - Orientador: Orientación (crear, editar, eliminar, constancia, referencia)
   - TS: Trabajo Social (becas, exoneraciones, fames, embarazadas, estudio socioeconómico)
   - Discapacidad: Discapacidad (crear, editar, eliminar, constancia, referencia)
6. Inventario Médico (solo para Médicos/Enfermeros)
7. Referencias sociales
8. Cómo exportar reportes de su área
9. Cómo usar la ayuda del sistema

**Formato:** PDF con capturas de pantalla, pasos numerados, consejos y notas importantes. Dejar espacios marcados con `[INSERTAR CAPTURA: descripción]` para que el usuario agregue screenshots.

### Manual 2: Manual del Administrador/Superusuario

**Audiencia:** Administradores y Superusuarios con acceso total
**Contenido esperado:**
1. Cómo iniciar sesión
2. Panel principal (Dashboard y Calendario)
3. Gestión de Empleados (crear, editar, eliminar, asignar roles)
4. Gestión de Beneficiarios (crear, editar, eliminar)
5. Gestión de Citas (crear, editar, eliminar)
6. Todos los módulos de Diagnósticos (visión general)
7. Inventario Médico (crear, entradas, salidas)
8. Inventario Mobiliario (mobiliario, equipos, fichas técnicas)
9. Referencias sociales
10. Jornadas
11. Transporte
12. Configuraciones:
    - Gestión de configuración del sistema
    - Permisos de empleados (asignar permisos por rol)
    - Bitácora de auditoría
    - Respaldo de base de datos
13. Reportes Estadísticos (todos los tipos)
14. Exportación de datos (Excel y PDF)
15. Seguridad y buenas prácticas

**Formato:** PDF más extenso, con capturas de pantalla, diagramas de flujo, tablas de permisos, y guías paso a paso detalladas. Dejar espacios marcados con `[INSERTAR CAPTURA: descripción]`.

### Consejos para la IA generadora:
- Usar lenguaje claro y directo, en español
- Cada paso debe ser accionable ("Haga clic en...", "Seleccione...", "Complete el campo...")
- Incluir íconos Unicode para referencia visual (🔍, 📝, ✅, ⚠️, 💡)
- Los screenshots se agregan manualmente después de generar el PDF
- Mantener un formato consistente: Título → Descripción → Pasos → Consejo
- Usar colores corporativos: azul (#4e73df) para acciones primarias, verde para éxito, rojo para advertencias
