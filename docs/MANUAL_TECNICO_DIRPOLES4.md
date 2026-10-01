# MANUAL TÉCNICO DE DIRPOLES-4
**Sistema de Gestión Integral para la Atención de Estudiantes Beneficiarios**  
**Dirección de Políticas Estudiantiles (DIRPOLES) — UPTAEB**  

---

# CAPÍTULO 1: Fundamentos de Ingeniería y Evolución Tecnológica (DIRPOLES vs DIRPOLES-4)

## 1.1 Ficha Técnica Institucional

| Parámetro | Detalle Institucional / Técnico |
| :--- | :--- |
| **Institución Beneficiaria** | Universidad Politécnica Territorial Augusto Baldó de Barquisimeto (UPTAEB), Estado Lara, Venezuela. |
| **Dependencia Operativa** | Dirección de Políticas Estudiantiles (DIRPOLES). |
| **Programa Académico** | Programa Nacional de Formación (PNF) en Informática, Trayecto IV. |
| **Naturaleza del Proyecto** | Proyecto Socio-Integrador y Tecnológico (PSIT) — Trabajo Especial de Grado para la obtención del título de Ingeniero(a) en Informática. |
| **Sujeto de Atención / Contexto** | Estudiantes activos y beneficiarios de la UPTAEB que requieren asistencia integral en salud física, psicológica, orientación socioeducativa, apoyo socioeconómico (becas y exoneraciones), asistencia a personas con discapacidad, jornadas masivas y servicios de transporte universitario. |
| **Arquitectura del Software** | Monolito modular nativo en PHP 8+ con MVC propio (Front Controller de Dos Puertas), desacoplado con microservicio de IA en Python (FastAPI) y streaming de eventos asíncronos via SSE. |
| **Bases de Datos** | Doble esquema aislado MySQL: `dirpoles_security` (autenticación, RBAC y auditoría) y `dirpoles_business` (operaciones de negocio y datos clínicos/sociales). |
| **Estado del Desarrollo** | Producción / Versión 4.0 Refactorizada e Inmutable (DIRPOLES-4). |

### Alcance Operativo del Software DIRPOLES-4
El sistema DIRPOLES-4 abarca la automatización integral, digitalización, control de concurrencia y auditoría estricta de 18 módulos funcionales y transversales:

1. **Autenticación y Control de Accesos:** Inicio de sesión con cifrado asimétrico RSA-2048 en cliente, hash Bcrypt en servidor, persistencia de intentos fallidos anti-bruteforce en base de datos (`login_intentos`) y tokens JWT RS256 con Refresh Token rotativo de un solo uso.
2. **Gestionar Empleados (Módulo 1):** Administración de personal administrativo, médico, especializado y técnico, control de roles, cargos y credenciales de acceso.
3. **Gestionar Beneficiarios (Módulo 2):** Control de expedientes de los estudiantes de la UPTAEB, indexados por PNF, trayecto, sección, datos de contacto y estatus académico.
4. **Gestionar Citas (Módulo 3):** Agendamiento de citas de Psicología y servicios especializados, validación de disponibilidad contra horarios de atención y ciclo de vida (Programada, Completada, Cancelada).
5. **Diagnósticos de Psicología (Módulo 4):** Evaluaciones clínicas, seguimiento psicológico, gestión de patologías y emisión de constancias y referencias técnicas en PDF.
6. **Diagnósticos de Medicina (Módulo 5):** Consultas médicas primarias, evaluación de signos vitales, tratamiento y descuento automático transaccional con `FOR UPDATE` de insumos médicos consumidos.
7. **Diagnósticos de Orientación (Módulo 6):** Asistencia socioeducativa y vocacional, control de motivos de consulta, recomendaciones e indicaciones institucionales obligatorias.
8. **Diagnósticos de Trabajo Social (Módulo 7):** Hub con 4 sub-flujos especializados (becas bancarias, exoneraciones de aranceles, ayudas FAMES y atención a embarazadas) más generación del Estudio Socioeconómico interactivo (offcanvas de 5 pasos) exportable a PDF (FPDF).
9. **Diagnósticos de Discapacidad (Módulo 8):** Expediente de estudiantes con diversidad funcional, registro de tipo de discapacidad, grado, habilidades funcionales, apoyo requerido y carnetización.
10. **Gestionar Inventario Médico (Módulo 9):** Control de existencias de medicamentos e insumos médicos, Kardex transaccional (`inventario_medico`), entradas, salidas justificadas, alertas de stock crítico y vencimiento automatizado.
11. **Gestionar Referencias (Módulo 10):** Flujo de remisión inter-áreas (psicología, medicina, trabajo social, orientación, discapacidad), con asignación origen/destino, historial de transiciones y estados (Pendiente, Aceptada, Rechazada).
12. **Gestionar Jornadas Médicas (Módulo 11):** Planificación de eventos masivos de salud, control de aforo por concurrencia mediante `FOR UPDATE`, registro de asistentes y diagnósticos múltiples con consumo de medicamentos.
13. **Gestionar Mobiliario y Equipos (Módulo 12):** Hub con 3 sub-flujos (bienes muebles, equipos tecnológicos con serial obligatorio y Fichas Técnicas por empleado responsable) con Kardex de asignación, reubicación y baja.
14. **Gestionar Transporte (Módulo 13):** Control de flota vehicular, rutas universitarias, asignación de choferes (sin solapamiento por día), catálogo de repuestos, Kardex de inventario y mantenimientos preventivos/correctivos.
15. **Configuraciones del Sistema (Módulo 14):** Administración de catálogos globales, parámetros institucionales y mantenimiento.
16. **Reportes Estadísticos (Módulo 15):** 10 tableros estadísticos dinámicos con filtrado *server-side* mediante lista blanca strict, visualización de métricas en tarjetas `data-stat`, gráficos Chart.js v2.9.4 local y exportaciones a PDF y Excel.
17. **Auditoría y Bitácora Transversal (Módulo 16):** Trazabilidad obligatoria e inmutable de operaciones del sistema (`Crear`, `Lectura`, `Editar`, `Eliminar`, `Login`, `Logout`, `Entrada`, `Salida`, `Descargar`) con registro de usuario, IP y metadatos.
18. **Matriz de Permisos / RBAC (Módulo 17):** Administración de permisos atómicos (`crear`, `leer`, `editar`, `eliminar`) por combinación de rol de empleado y módulo del sistema.

---

## 1.2 Justificación del Paradigma: Monolito Modular Nativo PHP 8+ vs Frameworks y SPAs

El desarrollo de DIRPOLES-4 responde a una decisión consciente de ingeniería de software adaptada a las realidades tecnológicas, operativas e institucionales de la educación superior pública venezolana.

```
                  ┌────────────────────────────────────────────────────────┐
                  │          ARQUITECTURA SELECCIONADA DIRPOLES-4          │
                  │       Monolito Modular Nativo PHP 8+ (MVC 2-Puertas)   │
                  └───────────────────────────┬────────────────────────────┘
                                              │
                    ┌─────────────────────────┴─────────────────────────┐
                    ▼                                                   ▼
 ┌──────────────────────────────────────┐            ┌──────────────────────────────────────┐
 │   ¿Por qué NO Frameworks PHP?        │            │       ¿Por qué NO SPAs (JS)?         │
 │   (Laravel / Symfony)                │            │       (React / Vue / Angular)        │
 ├──────────────────────────────────────┤            ├──────────────────────────────────────┤
 │ • Bootstrapping pesado (15-35MB/req) │            │ • Complejidad de Build/Node (GBs)    │
 │ • Alta latencia en hardware modesto  │            │ • Riesgo de Token en localStorage    │
 │ • Rompimiento por actualización      │            │ • Vulnerabilidad BOLA en cliente     │
 │ • ORM oculta consultas SQL reales    │            │ • Incompatibilidad navegadores viejos │
 └──────────────────────────────────────┘            └──────────────────────────────────────┘
```

### 1. Infraestructura Institucional y Disponibilidad de Recursos
Los servidores del centro de datos de la UPTAEB operan en entornos virtualizados o físicos compartidos, con asignaciones estrictas de memoria RAM (4 GB a 8 GB para todo el ecosistema de aplicaciones de la universidad) y conectividad a Internet que puede presentar interrupciones. 
* **Uso de Frameworks Comerciales (Laravel/Symfony):** Requieren un bootstrapping que inicializa decenas de *Service Providers*, contenedores de inyección de dependencias y facades en cada petición HTTP, elevando el consumo base por request a **15 - 35 MB de RAM** y exigiendo herramientas adicionales como Redis o Swoole para mantener tiempos de respuesta aceptables.
* **Solución Nativa PHP 8+ en DIRPOLES-4:** La arquitectura nativa con autoloading PSR-4 y *Lazy Loading* (carga perezosa de controladores) consume únicamente entre **2 MB y 5 MB de RAM** por petición. Esto permite que el servidor procese un volumen de concurrencia hasta 6 veces mayor en el mismo hardware institucional sin degradación del servicio.

### 2. Curva de Mantenimiento, Autonomía y "Zero Lock-in"
El software desarrollado en el marco del PNF en Informática debe ser mantenible y acreditable por futuras generaciones de estudiantes, profesores y personal técnico de la UPTAEB.
* **El riesgo del desuso tecnológico:** Los proyectos basados en frameworks comerciales sufren obsolescencia acelerada. Cambios de versión mayor (e.g., Laravel 8 a 10, React 16 a 18) rompen dependencias, deprecian métodos y requieren reescrituras masivas o instalaciones pesadas de `node_modules` (que suelen superar 1 GB de espacio y requieren conexión continua a NPM).
* **Sostenibilidad Nativa:** DIRPOLES-4 utiliza exclusivamente estándares web abiertos (PHP 8+ POO estricto, Vanilla JS ES6+, CSS3 nativo, HTML5 semántico y PDO SQL ANSI). No existen transpiladores (Webpack/Vite), compiladores de CSS ni dependencias propietarias. Cualquier desarrollador con conocimientos fundamentales de PHP y SQL puede mantener, auditar o extender el sistema 10 años después de su despliegue sin depender de repositorios de paquetes externos.

### 3. Evitación de Single Page Applications (SPAs) y la Filosofía de "Dos Puertas"
Las arquitecturas SPA modernas (React, Vue) desplazan la lógica de renderizado al navegador del cliente. En un entorno institucional, esto introduce problemas graves:
* **Seguridad de Tokens:** Las SPAs suelen almacenar tokens de autenticación en `localStorage` o `sessionStorage`, exponiéndolos directamente a ataques de lectura de scripts maliciosos (XSS). DIRPOLES-4 utiliza cookies `HttpOnly; SameSite=Strict; Secure`, las cuales son completamente inaccesibles para el contexto de ejecución de JavaScript, garantizando la inmunidad contra la extracción de tokens.
* **Control de Acceso (BOLA/IDOR):** En las SPAs, las reglas de navegación y ocultación de interfaz residen en el cliente, invitando a la elusión de controles mediante la modificación del estado en las herramientas de desarrollo del navegador. DIRPOLES-4 aplica la verificación RBAC y la validación de alcance de datos (*Data Scope*) en el backend, en el 100% de los endpoints.
* **Arquitectura de Dos Puertas:** DIRPOLES-4 resuelve el dilema entre rendimiento y experiencia de usuario dividiendo la aplicación en dos puertas de entrada administradas por el mismo monolito:
  1. **Puerta HTML:** Para la carga inicial de vistas completas, donde PHP renderiza plantillas limpias mediante `require_once`, garantizando rapidez y eliminando estados incompletos.
  2. **Puerta API JSON:** Para interacciones asíncronas dinámicas (tablas DataTables, autocompletado Select2, modales de formulario y peticiones AJAX), donde PHP responde exclusivamente payloads JSON estructurados bajo un contrato estricto.

### 4. Fidelidad al Diagrama de Clases Académico (POO Pura)
Los ORMs comerciales (como Eloquent o Doctrine) introducen magia negra (*magic methods*, *active record* pesado) que ocultan las consultas SQL reales y distorsionan la estructura Orientada a Objetos formal requerida en la evaluación académica de la Ingeniería en Informática.
En DIRPOLES-4, cada modelo (`BeneficiarioModel`, `MedicinaModel`, `MobiliarioModel`) hereda de la abstracción `BusinessModel` o `SecurityModel`, utilizando mutadores mágicos `__set()` con validación estricta y encauzando las consultas a través de transacciones PDO explícitas. Esto garantiza la coincidencia exacta entre el Diagrama de Clases UML del proyecto socio-integrador y la implementación en código fuente.

---

## 1.3 Matriz Comparativa Exhaustiva: Sistema Anterior (DIRPOLES_4) vs Sistema Nuevo (DIRPOLES-4)

La siguiente matriz detalla las diferencias arquitectónicas, de seguridad, persistencia y rendimiento entre la versión heredada y la arquitectura refactorizada de DIRPOLES-4:

| Dimensión Técnica | Sistema Anterior (`DIRPOLES_4`) | Sistema Nuevo (`DIRPOLES-4`) | Impacto en la Ingeniería del Software |
| :--- | :--- | :--- | :--- |
| **1. Patrón Arquitectónico** | Código espagueti incrustado. Archivos monolíticos que mezclaban SQL, HTML, CSS y lógica de negocio en una sola pieza. Redirecciones con `header('Location')` sin middleware. | **MVC de Dos Puertas** con Front Controller nativo (`index.php`). Enrutamiento centralizado en `Router.php` con *Lazy Loading* de controladores. | Eliminación de duplicidad de código, mantenibilidad modular y clara separación de responsabilidades (SoC). |
| **2. Seguridad de Credenciales y Login** | Contraseñas almacenadas en texto plano o hash MD5 vulnerable. Consulta SQL directa vulnerable a inyección: `WHERE correo='$c' AND pass='$p'`. Intentos ilimitados. | Cifrado asimétrico **RSA-2048** en cliente antes de la transmisión. Hashing **Bcrypt** (costo 10+) en servidor. Bloqueo de intentos persistido en BD (`login_intentos`). | Protección total contra sniffing de red, inyección SQL en autenticación y ataques de fuerza bruta. |
| **3. Gestión de Sesión y Tokens** | Sesiones PHP puras (`$_SESSION`) sin firma criptográfica. Reutilización indefinida de IDs de sesión. Vulnerable a *Session Hijacking* y *Fixation*. | Autenticación híbrida: **JWT RS256** (firma asimétrica) en cookie `HttpOnly; SameSite=Strict` + **Refresh Token rotativo** con hash SHA-256 de un solo uso (*one-time use*). | Mitigación total de ataques XSS/CSRF en tokens, revocación inmediata y persistencia segura de estado. |
| **4. Manejo de Base de Datos** | Esquema de base de datos único mezclado. Tablas de usuarios, permisos, expedientes clínicos y bienes muebles compartían el mismo espacio de nombres. | **Doble Base de Datos Aislada**: `dirpoles_security` (autenticación, RBAC y auditoría) y `dirpoles_business` (datos transaccionales de negocio). | Aislamiento físico de seguridad (*Principle of Least Privilege*). Una falla en el negocio no compromete el esquema de acceso. |
| **5. Concurrencia e Inventario** | Restas directas sin control de concurrencia: `UPDATE insumos SET cantidad = cantidad - 1`. Vulnerable a condiciones de carrera (*Race Conditions*) y stock negativo. | Transacciones ACID estrictas con **`SELECT ... FOR UPDATE`**, cálculo de stock en PHP antes de escribir y evaluación atómica de estatus (`Agotado`/`Disponible`). | Cero descuadres de inventario en atención médica simultánea. Garantía de consistencia en el Kardex. |
| **6. Control de Acceso (RBAC / BOLA)** | Validaciones puramente visuales en frontend (ocultar botones con `if`). Endpoints de procesamiento expuestos sin validación en servidor. Vulnerable a IDOR. | RBAC estricto transversal (`Autorizacion::verificar($mod, $perm)`) ejecutado en backend en el 100% de las rutas. Validación de alcance por usuario (*Data Scope*). | Protección absoluta contra BOLA (*Broken Object Level Authorization*) e IDOR (*Insecure Direct Object References*). |
| **7. Manejo de Errores y Excepciones** | Interrupción abrupta mediante `die('Error')`, `echo json_encode(['error'])` malformado o pantallas blancas con volcados `var_dump()` y PHP Warnings. | **`Respuesta::manejarExcepcion()`** global. Jerarquía **`ExcepcionApi`** con códigos de error estandarizados (`ErrorCodes::*`). Ocultación de trazas en producción (`APP_DEBUG=false`). | Respuestas limpias y estructuradas en el contrato JSON o páginas HTML 404/500 dedicadas. Cero filtración de infraestructura. |
| **8. Auditoría y Trazabilidad** | Registros de auditoría esporádicos o inexistentes, insertados manualmente de forma inconsistente en algunas tablas secundarias. | **Bitácora Transversal Obligatoria** (`Bitacora::registrar()`). Registro automático de usuario, rol, IP, user-agent, módulo y acción con **ENUM validado**. | Trazabilidad forense completa e inmutable de todas las operaciones de escritura y lectura sensible del sistema. |
| **9. Carga y Almacenamiento de Archivos** | Invocación de `move_uploaded_file()` confiando en la extensión del cliente (`$_FILES['file']['name']`). Subida a carpetas públicas ejecutables. | Validación en dos fases: lista blanca de extensiones + verificación MIME real mediante la extensión **`finfo`**. Nombres únicos (UUID) y contención de rutas. | Inmunidad contra ejecución remota de código (RCE) mediante la subida de web shells o scripts maliciosos. |
| **10. Experiencia de Usuario e Interfaz** | Recargas completas de página (`F5`) para filtrar, ordenar o paginar listados. Formularios genéricos sin asistente visual ni filtros dependientes. | Interfaz dinámica SPA-like: **DataTables** interactivo, autocompletado **Select2** asíncrono, modales AJAX, SweetAlert2 y tours guiados con **Driver.js** (`#btn-ayuda`). | Navegación fluida, reducción del ancho de banda transferido y acompañamiento interactivo al usuario operativo. |
| **11. Manejo de Eventos Asíncronos** | Polling invasivo mediante `setInterval(ajax, 2000)` generando peticiones HTTP masivas que saturaban el servidor web y la BD. | Streaming asíncrono unidireccional vía **Server-Sent Events (SSE)** en `sse/notificaciones` con cabecera `Content-Type: text/event-stream`. | Notificaciones en tiempo real para el topbar sin sobrecarga de procesamiento ni apertura excesiva de conexiones. |
| **12. Manejo de Reportes y Exportación** | Consultas `SELECT *` masivas cargadas completamente en memoria de servidor sin paginar ni limitar. Exportaciones en tablas HTML crudas. | Endpoints dedicados (`api/reportes/*`) con **lista blanca *server-side*** de filtros permitidos, sanitización estricta y límite encadenado con **`PARAM_INT`**. | Generación eficiente de tableros con Chart.js v2.9.4 local, resguardando la estabilidad del servidor ante consultas pesadas. |

---

## 1.4 Ciclo de Vida Completo de una Petición (Request Lifecycle)

El flujo de ejecución en DIRPOLES-4 está diseñado bajo el patrón **Front Controller** estricto, garantizando que el 100% de las solicitudes HTTP ingresen por un único punto de entrada (`index.php`), atraviesen los anillos de seguridad y middlewares, ejecuten la lógica de negocio y retornen su respuesta por la puerta adecuada (HTML o API JSON).

### Representación Diagramática del Request Lifecycle

```
[ NAVEGADOR / CLIENTE HTTP ]
            │
            │  1. Petición HTTP (ej: POST /api/medicina/crear)
            ▼
┌─────────────────────────────────────────────────────────────────┐
│ SERVIDOR WEB APACHE (.htaccess)                                 │
│ • Intercepta la URI y aplica RewriteRule ^(.*)$ index.php [QSA,L]│
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  2. Redirección interna
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ FRONT CONTROLLER (index.php)                                    │
│ • Autoloading Composer (PSR-4)                                  │
│ • Carga de variables de entorno (.env via Dotenv)               │
│ • Aplicación de cabeceras CORS (Filtrado de orígenes)           │
│ • Configuración e inicio de Sesión PHP (session_start)          │
│ • Carga de dependencias Core (bootstrap.php)                    │
│ • Registro del Handler Global de Excepciones y Shutdown         │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  3. Invocación de Router::ejecutar()
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ NÚCLEO DE ENRUTAMIENTO (App\Core\Router)                        │
│ • Extracción y normalización de la URI y Método HTTP            │
│ • Coincidencia de patrón en la tabla de rutas (routes.php)      │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  4. Cadena de Middlewares Interceptores
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ CAPA DE MIDDLEWARES Y SEGURIDAD                                 │
│ ├── AuthMiddleware / JwtHandler: Decodifica JWT RS256 en cookie │
│ ├── SessionSync: Cruza user_id de JWT con $_SESSION             │
│ └── Autorizacion::verificar($modulo, $permiso): Consulta RBAC   │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  5. Instanciación y Ejecución de Controlador
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ CONTROLADOR DE NEGOCIO (ej: MedicinaController::crear)          │
│ • Extrae y sanea parámetros del body (saneoXSS / htmlspecialchars)│
│ • Instancia el modelo correspondiente (Lazy Loading)            │
│ • Invoca mutadores mágicos ($modelo->__set('propiedad', $valor))│
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  6. Invocación de Capa de Persistencia
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ MODELO DE NEGOCIO (ej: MedicinaModel extends BusinessModel)     │
│ • Abre Transacción ACID ($conexion->beginTransaction())         │
│ • Aplica bloqueo pesimista: SELECT ... FOR UPDATE               │
│ • Ejecuta PDO Prepared Statements con bindValue() estricto      │
│ • Confirma Transacción ($conexion->commit())                    │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  7. Auditoría y Registro Transversal
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ MÓDULO DE BITÁCORA (App\Models\Bitacora::registrar)            │
│ • Escribe registro de auditoría inmutable en dirpoles_security  │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  8. Formateo y Despacho de Respuesta
                    ▼
┌─────────────────────────────────────────────────────────────────┐
│ RESPUESTA DE SALIDA (App\Core\Respuesta)                        │
│ • Evalúa la frontera con Respuesta::esApi()                     │
│ ├── Puerta API JSON ──► Emite JSON estructurado + HTTP Code     │
│ └── Puerta HTML ──────► Renderiza Vista PHP via require_once    │
└───────────────────┬─────────────────────────────────────────────┘
                    │
                    │  9. Payload de Respuesta HTTP
                    ▼
[ NAVEGADOR / CLIENTE HTTP ] (Procesa JSON o Renderiza Interfaz)
```

---

### Desglose Detallado Paso a Paso

#### Paso 1: Intercepción en Servidor Web (`.htaccess`)
Toda petición HTTP dirigida al servidor (e.g., `GET /inicio`, `POST /api/medicina/crear`) es capturada por el módulo `mod_rewrite` de Apache. El archivo `.htaccess` redirige la consulta de forma transparente hacia el Front Controller:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Paso 2: Inicialización y Bootstraping (`index.php`)
El archivo `index.php` actúa como el único orquestador de inicio del sistema:
1. **Constantes del Sistema:** Define `BASE_PATH` y `BASE_URL`.
2. **Autoloading:** Carga el autoloader de Composer (`vendor/autoload.php`) para la resolución dinámica de clases bajo la norma PSR-4 (`App\`).
3. **Variables de Entorno:** Carga el archivo `.env` mediante `Dotenv\Dotenv`.
4. **CORS Global:** Valida la cabecera `HTTP_ORIGIN` contra la lista blanca configurada en `CORS_ALLOWED_ORIGINS`. Si coincide, emite las cabeceras `Access-Control-Allow-Origin` y `Access-Control-Allow-Credentials: true`. Responde inmediatamente `200 OK` si la petición es de tipo Preflight (`OPTIONS`).
5. **Configuración de Sesión:** Ajusta `session.gc_maxlifetime` al mayor valor entre la expiración del JWT y el Refresh Token, e inicia la sesión nativa con `session_start()`.
6. **Manejo Global de Excepciones:** Registra `set_exception_handler([App\Core\Respuesta::class, 'manejarExcepcion'])` y `register_shutdown_function()` para atrapar cualquier error fatal o no controlado, garantizando que el sistema responda siempre en el formato adecuado según la puerta (JSON o HTML).

#### Paso 3: Resolución de Rutas (`App\Core\Router`)
El Router compara el método HTTP (`$_SERVER['REQUEST_METHOD']`) y la URI solicitada contra el mapa de rutas registrado en `app/routes.php`.
```php
Router::post('api/medicina/crear', [MedicinaController::class, 'crear']);
```
Si la ruta no existe, el Router dispara una `ExcepcionApi` con código 404, que es capturada por el handler global y renderiza la vista `app/Views/errors/404.php` o responde JSON 404 si la ruta iniciaba por `api/`.

#### Paso 4: Ejecución de Middlewares de Seguridad
Antes de instanciar el controlador, la ruta ejecuta la tubería de middlewares interceptores:
1. **AuthMiddleware / JwtHandler:** Extrae la cookie cifrada `jwt_token`. Decodifica la firma digital asimétrica **RS256** utilizando la llave pública `app/Config/Keys/jwt_public.pem`. Si el token ha expirado, invoca automáticamente el mecanismo de rotación de Refresh Token (`refresh_token`).
2. **Validación Dual de Integridad:** Compara el `user_id` incrustado en el payload del JWT contra el valor registrado en `$_SESSION['user_id']`. Si existe alguna discrepancia, se asume suplantación de identidad, destruyendo la sesión y bloqueando el acceso.
3. **Verificación RBAC (`Autorizacion::verificar`):** Compara el rol del usuario autenticado contra el módulo y permiso requerido (e.g., Módulo `medicina`, Permiso `crear`). Si no posee el permiso en la tabla `rol_modulo_permiso` de `dirpoles_security`, interrumpe la ejecución arrojando una `ExcepcionApi` de código `403 Forbidden`.

#### Paso 5: Ejecución del Controlador Funcional
El controlador orquestador (e.g., `MedicinaController::crear()`) asume el control:
1. Sanea las entradas recibidas en el cuerpo de la petición (`$_POST` o `php://input`) mediante funciones de sanitización anti-XSS.
2. Instancia el modelo correspondiente mediante *Lazy Loading* (e.g., `$modelo = new MedicinaModel()`).
3. Asigna los atributos al modelo invocando los mutadores mágicos (`$modelo->__set('motivo', $motivo)`), los cuales validan tipos de datos, rangos y formatos antes de tocar la base de datos.

#### Paso 6: Persistencia y Transacción en Modelo (`BusinessModel`)
El modelo ejecuta las operaciones de persistencia garantizando las propiedades ACID:
1. Abre una conexión PDO apuntando a la base de datos `dirpoles_business`.
2. Inicia una transacción explícita: `$conexion->beginTransaction()`.
3. Para insumos médicos o aforo de jornadas, ejecuta una consulta de bloqueo pesimista: `SELECT cantidad FROM insumos WHERE id_insumo = :id FOR UPDATE`.
4. Evalúa el stock remanente en PHP. Si es insuficiente, cancela con `$conexion->rollBack()` y lanza `ExcepcionApi("Stock insuficiente", 400)`.
5. Ejecuta las sentencias `INSERT` o `UPDATE` mediante PDO Prepared Statements binding estricto de tipos (`PDO::PARAM_INT`, `PDO::PARAM_STR`).
6. Confirma la transacción con `$conexion->commit()`.

#### Paso 7: Auditoría Automática (`Bitacora`)
Inmediatamente después del éxito de la transacción, el controlador invoca el servicio transversal de auditoría:
```php
Bitacora::registrar(
    accion: 'Crear',
    descripcion: "Registró consulta médica para el beneficiario C.I. {$cedula}"
);
```
El método escribe una fila inmutable en la tabla `bitacora` de `dirpoles_security`, almacenando el ID del usuario, fecha, hora, dirección IP real del cliente y navegador utilizado.

#### Paso 8: Despacho por la Puerta Adecuada (`App\Core\Respuesta`)
El ciclo finaliza delegando la salida a la clase `Respuesta`:
* **Evaluación de Frontera (`Respuesta::esApi()`):** Inspecciona si la URI inicia por `api/` o si la cabecera `HTTP_ACCEPT` contiene `application/json`.
* **Si es Puerta API JSON:** Invoca `Respuesta::exito($datos)`, emite las cabeceras `Content-Type: application/json; charset=utf-8`, establece el código HTTP (200, 201) y despacha el JSON estricto:
  ```json
  {
    "exito": true,
    "datos": {
      "id_consulta": 105,
      "mensaje": "Consulta médica registrada exitosamente."
    }
  }
  ```
  La función ejecuta `exit` inmediatamente, previniendo cualquier salida adicional.
* **Si es Puerta HTML:** El controlador incluye la vista correspondiente (`require_once BASE_PATH . 'app/Views/medicina/consultar.php'`), inyectando los datos necesarios para que PHP renderice el HTML que se enviará al navegador.

---

# CAPÍTULO 2: Arquitectura de Software y Patrones de Diseño (Core & Frontend)

## 2.1 La Regla de las Dos Puertas (y la Tercera Puerta)

La arquitectura monolítica híbrida de DIRPOLES-4 impone un principio inviolable de separación de fronteras denominado la **Regla de las Dos Puertas**, complementada por una **Tercera Puerta** dedicada a la transmisión de streams binarios.

```
                               ┌───────────────────────────────────┐
                               │     PETICIÓN HTTP ENTRANTE        │
                               └─────────────────┬─────────────────┘
                                                 │
                                       evalúa en Router / Respuesta
                                                 │
                   ┌─────────────────────────────┼─────────────────────────────┐
                   ▼                             ▼                             ▼
       ┌───────────────────────┐     ┌───────────────────────┐     ┌───────────────────────┐
       │     PUERTA 1: HTML    │     │    PUERTA 2: API JSON  │     │   PUERTA 3: BINARIA   │
       ├───────────────────────┤     ├───────────────────────┤     ├───────────────────────┤
       │ • Rutas sin "api/"    │     │ • Rutas "api/*"       │     │ • Descarga PDF / SQL  │
       │ • Accept: text/html   │     │ • Accept: app/json    │     │ • Content-Disposition │
       │ • Respuesta: PHP View │     │ • Respuesta: JSON     │     │ • Stream de Bytes     │
       │ • Renderizado Server  │     │ • Contrato estricto   │     │ • FPDF / Dump DB      │
       └───────────────────────┘     └───────────────────────┘     └───────────────────────┘
```

### 1. Puerta HTML vs. Puerta JSON: La Clase `App\Core\Respuesta`
La clase `Respuesta` es la piedra angular que gobierna la emisión de salidas y la captura centralizada de excepciones. Garantiza que una solicitud jamás reciba un formato incompatible con su frontera.

#### Detección de Frontera (`Respuesta::esApi()`)
El motor evalúa automáticamente si el contexto de la petición pertenece a la Puerta API JSON mediante dos comprobaciones complementarias:
```php
public static function esApi(): bool
{
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $relativa = trim(substr($ruta, strlen($base)), '/');

    if ($relativa === 'api' || str_starts_with($relativa, 'api/')) {
        return true;
    }
    return str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}
```

#### Contrato JSON Unificado
Cuando opera en la Puerta JSON, el sistema garantiza **exactamente dos estructuras estandarizadas**:

* **Respuesta de Éxito (`Respuesta::exito($datos, $estado)`):**
  ```json
  {
    "exito": true,
    "datos": { ... }
  }
  ```
* **Respuesta de Error (`Respuesta::error($e)`):**
  ```json
  {
    "exito": false,
    "error": {
      "codigo": "CEDULA_DUPLICADA",
      "estado": 409,
      "mensaje": "La cédula ingresada ya se encuentra registrada en el sistema.",
      "detalles": null
    }
  }
  ```

#### Handler Global de Excepciones
El método `Respuesta::manejarExcepcion(\Throwable $e)` intercepta cualquier excepción no capturada en los controladores. Si `esApi()` es `true`, responde el JSON estructurado con el código HTTP correspondiente. Si es `false`, registra el error real en `logs/php_errors.log` y renderiza la plantilla HTML de error correspondiente (`app/Views/errors/404.php` o `error.php`), evitando que el usuario visualice trazas o volcados de código.

---

### 2. La Excepción Deliberada: Streaming SSE (`sse/notificaciones`)
El módulo de notificaciones transversales constituye la primera excepción deliberada al patrón de respuesta estándar de las Dos Puertas. La ruta `sse/notificaciones` no devuelve HTML ni JSON cerrado; establece un canal de comunicación unidireccional en tiempo real bajo el estándar **Server-Sent Events (SSE)** (`Content-Type: text/event-stream`).

```php
function streamNotificaciones(): void
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');

    if (function_exists('apache_setenv')) {
        apache_setenv('no-gzip', '1');
    }

    set_time_limit(0);
    ignore_user_abort(true);

    $idEmpleado = $_SESSION['id_empleado'] ?? null;
    if (!$idEmpleado) {
        echo "event: error\ndata: {\"mensaje\": \"No autenticado\"}\n\n";
        flush();
        exit();
    }

    // LIBERACIÓN CRÍTICA DEL CANDADO DE SESIÓN
    session_write_close();

    $ultimoId = max(0, (int) ($_GET['ultimoId'] ?? 0));
    $modelo = new NotificacionesModel();
    $iteracion = 0;

    while (true) {
        if (connection_aborted()) {
            break;
        }

        try {
            $modelo->__set('id_empleado', $idEmpleado);
            $modelo->__set('ultimoId', $ultimoId);
            $nuevas = $modelo->manejarAccion('nuevas_sse');

            foreach ($nuevas as $notif) {
                if ($notif['id'] > $ultimoId) {
                    $ultimoId = (int) $notif['id'];
                }
                echo "event: nueva-notificacion\n";
                echo 'data: ' . json_encode($notif, JSON_UNESCAPED_UNICODE) . "\n\n";

                if (ob_get_level() > 0) ob_flush();
                flush();
                usleep(50000);
            }
        } catch (Throwable $e) {
            error_log('SSE ERROR: ' . $e->getMessage());
            // Reconexión PDO ante caída del socket MySQL (Error 2006)
            $modelo->manejarAccion('reconectar');

            echo "event: error\ndata: {\"mensaje\": \"Error interno del servidor\"}\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }

        if ($iteracion % 5 === 0) {
            echo ": heartbeat\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }

        $iteracion++;
        sleep(3);
    }
}
```

#### Aspectos Clave del Streaming SSE:
1. **Liberación del Candado de Sesión (`session_write_close()`):** PHP bloquea el archivo de sesión nativo durante toda la ejecución de la petición. Debido a que la conexión SSE es un bucle infinito en segundo plano, no liberar la sesión impediría que el navegador del usuario realice cualquier otra solicitud HTTP simultánea en el sistema.
2. **Reconexión PDO Automática (MySQL Error 2006):** Si la base de datos se reinicia o el socket de conexión expira por inactividad (`MySQL server has gone away`), el bloque `catch` invoca `$modelo->manejarAccion('reconectar')`, re-instanciando el objeto PDO sin romper el bucle ni tumbar la conexión del cliente.
3. **Heartbeat de Mantenimiento:** Cada 15 segundos se emite un comentario SSE (`: heartbeat\n\n`) para evitar que los proxys de red, balanceadores o Apache cierren la conexión por inactividad.

---

### 3. La Tercera Puerta: Descarga de Archivos Binarios
La **Tercera Puerta** comprende todos los endpoints que despachan flujos binarios directamente hacia el navegador, utilizando cabeceras `Content-Type` especializadas y omitiendo la envoltura HTML o JSON.

#### a) Descarga del Respaldo de Base de Datos (`.sql`)
En el módulo de Configuración (`configuracionController.php`), las acciones de respaldo generan un dump SQL de la base de datos y lo transmiten mediante transferencia de archivos binarios:
```php
function descargarRespaldo(): void
{
    Autorizacion::verificar('configuraciones', 'crear');
    $modelo = new ConfiguracionModel();
    $sqlContent = $modelo->manejarAccion('generar_dump_sql');

    $filename = 'DIRPOLES4_Backup_' . date('Y-m-d_H-i-s') . '.sql';

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($sqlContent));
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $sqlContent;
    exit;
}
```

#### b) Generación de Documentos PDF en Línea (FPDF)
Para la emisión de Constancias de Atención, Recipes Médicos, Referencias Sociales y Estudios Socioeconómicos, el sistema invoca los procesadores de FPDF ubicados bajo `docs/PDF/`. 
* Las funciones (ej: `generarConstanciaMedicina()`) validan permisos y parámetros con `parametrosDocumento()`, obtienen la información estructurada desde el modelo, registran la acción en la Bitácora y delegan la salida a FPDF.
* FPDF emite las cabeceras `Content-Type: application/pdf` con `Content-Disposition: inline`, permitiendo que el documento se visualice dinámicamente dentro de un visor de PDF integrado en el navegador del usuario.

---

## 2.2 Patrón de Controladores Funcionales

En DIRPOLES-4, los controladores se implementan de forma estricta mediante **Funciones Nativas de PHP** en lugar de Clases Orientadas a Objetos.

```
 ┌────────────────────────────────────────────────────────────────────────┐
 │                      CONTROLADORES EN DIRPOLES-4                       │
 │                    (Estrictamente Funcionales / Pure)                  │
 └───────────────────────────────────┬────────────────────────────────────┘
                                     │
       ┌─────────────────────────────┴─────────────────────────────┐
       ▼                                                           ▼
 ┌─────────────────────────────────────────┐   ┌─────────────────────────────────────────┐
 │   Ventajas de Ingeniería & Rendimiento  │   │   Justificación Académica ante Jurado   │
 ├─────────────────────────────────────────┤   ├─────────────────────────────────────────┤
 │ • Estado NULO (Zero-State Functions)    │   │ • Separación Estricta de Capas (SoC)    │
 │ • Menor consumo de memoria por request  │   │ • Los Objetos viven en la capa Modelo   │
 │ • Eliminación de instanciación innecesaria│ │ • Cero indirección de clases "Runner"   │
 │ • Lazy Loading ultra rápido             │   │ • Transparencia de código procedimental │
 └─────────────────────────────────────────┘   └─────────────────────────────────────────┘
```

### 1. Justificación Técnica y Académica (Frente al Jurado de Grado)
En el diseño de frameworks tradicionales, los controladores suelen convertirse en clases infladas (*Fat Controllers*) que acumulan estado mutable, sobrecarga de constructores, inyección de dependencias pesada e ineficiencias de memoria.

* **Separación Real de Responsabilidades:** Un controlador HTTP solo debe cumplir dos funciones: verificar la autorización de acceso y coordinar la transmisión de datos entre la petición y el modelo. No posee estado propio ni requiere herencia.
* **Fidelidad al Diagrama de Clases:** Toda la lógica orientada a objetos, encapsulamiento de propiedades, reglas de validación y consultas de persistencia residen exclusivamente en los **Modelos** (`BeneficiarioModel`, `MedicinaModel`, etc.). Los controladores actúan como despachadores funcionales *stateless* (sin estado), eliminando clases innecesarias que distorsionan el diagrama de clases UML del proyecto socio-integrador.
* **Rendimiento Nulo de Instanciación:** Al no requerir la creación de objetos de clase para los controladores (`new MedicinaController()`), PHP ejecuta directamente la función asignada por el Router, reduciendo los ciclos de CPU y la huella de memoria.

---

### 2. Mecanismo de Carga Perezosa (`load_controller()`)
Para maximizar la eficiencia en entornos de hardware limitado, los archivos de los controladores no se cargan masivamente al iniciar la aplicación. Se aplica el patrón de **Lazy Loading** (Carga Perezosa) mediante la función `load_controller()`, definida en `app/bootstrap.php`:

```php
function load_controller(string $file): void
{
    static $loaded = [];

    $file = ltrim($file, '/\\');
    $path = rtrim(BASE_PATH, '/\\') . '/app/Controllers/' . $file;

    if (isset($loaded[$path])) {
        return;
    }

    if (is_readable($path)) {
        require_once $path;
        $loaded[$path] = true;
        return;
    }

    throw new \RuntimeException("Controlador no encontrado o no legible: {$path}");
}
```

#### Funcionamiento de `load_controller()`:
1. **Memoización con Estático (`$loaded`):** Mantiene un registro interno en memoria para asegurar que un mismo archivo de controlador jamás sea requerido más de una vez durante el ciclo de vida de la petición.
2. **Carga Bajo Demanda:** El Router o la definición de ruta invoca `load_controller('medicinaController.php')` únicamente cuando la URI solicitada coincide con dicho módulo. Si una petición ingresa a `/api/beneficiarios/listar`, los controladores de medicina, transporte o mobiliario nunca son leídos del disco ni cargados en memoria.
3. **Opción de Precarga para Producción (`PRELOAD_CONTROLLERS`):** En entornos de producción con OPcache activo, `bootstrap.php` permite activar la precarga completa mediante `preload_all_controllers()`, almacenando el bytecode compilado de todos los controladores en la memoria compartida del servidor web.

---

## 2.3 Patrón del Modelo Único y Despachador

Toda la lógica de acceso a datos, validación estricta y transaccionalidad se concentra en los modelos de cada módulo, organizados bajo una jerarquía de clases pura.

```
                             ┌───────────────────────────────┐
                             │    App\Core\Database (Abstract)│
                             │  • $conn (Business PDO)       │
                             │  • $conn_security (Sec PDO)  │
                             └───────────────┬───────────────┘
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │ App\Models\BusinessModel  │               │ App\Models\SecurityModel  │
         ├───────────────────────────┤               ├───────────────────────────┤
         │ • Inicia conexión Business│               │ • Inicia conexión Security│
         └─────────────┬─────────────┘               └─────────────┬─────────────┘
                       │                                           │
                       ▼                                           ▼
         ┌───────────────────────────┐               ┌───────────────────────────┐
         │  BeneficiarioModel /      │               │  EmpleadoModel /          │
         │  MedicinaModel / etc.     │               │  PerfilModel / Bitacora   │
         ├───────────────────────────┤               ├───────────────────────────┤
         │ • __set() mutador mágico  │               │ • __set() mutador mágico  │
         │ • manejarAccion($accion)  │               │ • manejarAccion($accion)  │
         └───────────────────────────┘               └───────────────────────────┘
```

### 1. Jerarquía de Persistencia
La jerarquía de clases abstrae las conexiones PDO aisladas:
1. **`Database` (Clase Abstracta Base):** Implementa la inicialización perezosa (*Lazy Connection*) de las instancias PDO mediante los métodos protegidos `Business()` y `Security()`. Configura `PDO::ERRMODE_EXCEPTION` y fuerza la codificación `utf8mb4`.
2. **`BusinessModel` & `SecurityModel`:** Clases intermedias que heredan de `Database`. Sus constructores activan automáticamente la conexión correspondiente al dominio asignado.
3. **Modelos de Módulo (`[Modulo]Model`):** Clases concretas que heredan de `BusinessModel` (e.g. `BeneficiarioModel`, `MedicinaModel`, `MobiliarioModel`) o `SecurityModel` (e.g. `EmpleadoModel`, `PerfilModel`).

---

### 2. El Despachador `manejarAccion($accion)`
En lugar de implementar capas adicionales de complejidad como el patrón Service/Repository (que duplican interfaces y archivos sin beneficio operacional en un monolito nativo), DIRPOLES-4 implementa el patrón **Despachador Único** mediante el método público `manejarAccion(string $accion)`.

```php
public function manejarAccion(string $accion): mixed
{
    return match ($accion) {
        'crear'           => $this->crear(),
        'listar'          => $this->listar(),
        'obtener'         => $this->obtener(),
        'actualizar'      => $this->actualizar(),
        'eliminar'        => $this->eliminar(),
        'stats'           => $this->stats(),
        'existe_cedula'   => $this->existeCedula($this->__get('tipo_cedula'), $this->__get('cedula'), (int) $this->__get('id_excluir')),
        'existe_correo'   => $this->existeCorreo($this->__get('correo'), (int) $this->__get('id_excluir')),
        default => throw ExcepcionApi::errorInterno("Acción no válida en el Modelo: '{$accion}'."),
    };
}
```

#### Ventajas del Despachador:
* **Punto de Entrada Único:** El controlador nunca invoca métodos privados de persistencia directamente; únicamente interactúa con `manejarAccion('nombre_accion')`.
* **Encapsulamiento Estricto:** Todos los métodos de consulta SQL (`crear()`, `actualizar()`, `listar()`) son **`private`**, garantizando que la lógica de acceso a datos no pueda ser puenteada sin pasar por el despachador.
* **Evaluación Atómica en PHP 8+:** La expresión `match` de PHP 8+ realiza una comparación de tipo estricto (`===`) y dispara una `ExcepcionApi` de error interno (500) si se solicita una acción no registrada en la tabla de despacho.

---

### 3. La Capa de Validación en `__set()` (Filosofía Fail-Fast)
DIRPOLES-4 prohíbe terminantemente el uso de arrays acumulativos de errores (patrón arcaico donde se concatenan fallos `$errores = []` y se retornan al final). En su lugar, se impone la filosofía **Fail-Fast** (Fallo Inmediato) dentro del mutador mágico `__set()`.

```php
public function __set(string $nombre, mixed $valor): void
{
    switch ($nombre) {
        case 'nombres':
            $valor = trim((string) $valor);
            if ($valor === '') {
                throw ExcepcionApi::validacion('El nombre es obligatorio.');
            }
            if (mb_strlen($valor) > 100) {
                throw ExcepcionApi::validacion('El nombre no puede superar 100 caracteres.');
            }
            $this->atributos['nombres'] = $valor;
            break;

        case 'cedula':
            $valor = trim((string) $valor);
            if (!preg_match('/^\d{6,10}$/', $valor)) {
                throw ExcepcionApi::validacion('La cédula debe tener entre 6 y 10 dígitos.');
            }
            $this->atributos['cedula'] = $valor;
            break;

        case 'correo':
            $valor = trim((string) $valor);
            if ($valor === '' || !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                throw ExcepcionApi::validacion('El correo electrónico no es válido.');
            }
            $this->atributos['correo'] = $valor;
            break;

        default:
            throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
    }
}
```

#### Reglas de la Validación en `__set()`:
1. **Asignación Atómica:** Cada propiedad enviada por el controlador es saneada (saneo de espacios, conversión de tipos, sanitización de caracteres) y validada individualmente.
2. **Lanzamiento Inmediato:** Al detectar la primera inconsistencia de formato, rango o tipo, el mutador interrumpe la ejecución arrojando una `ExcepcionApi::validacion('Mensaje descriptivo')` con código HTTP `400 Bad Request`.
3. **Resguardo de la Base de Datos:** Ningún dato inválido o malformado puede alcanzar las consultas SQL, eliminando el riesgo de excepciones PDO no controladas o truncamientos involuntarios en la base de datos.

---

## 2.4 Arquitectura de Doble Base de Datos

Para garantizar la seguridad de la información y aislar los dominios de autenticación frente a la lógica operativa, DIRPOLES-4 implementa un esquema de **Doble Base de Datos Aislada** en MySQL.

```
┌────────────────────────────────────────┐   ┌────────────────────────────────────────┐
│   BASE DE DATOS: dirpoles_security     │   │   BASE DE DATOS: dirpoles_business     │
├────────────────────────────────────────┤   ├────────────────────────────────────────┤
│ • empleado / tipo_empleado             │   │ • beneficiario / pnf                   │
│ • modulo / permisos / rol_mod_permiso  │   │ • citas / servicios                    │
│ • login_intentos (Anti-bruteforce)     │   │ • consulta_psicologica / consulta_med  │
│ • refresh_tokens (Gestión de tokens)   │   │ • orientacion / discapacidad           │
│ • bitacora (Auditoría transversal)     │   │ • becas / exoneracion / fames          │
│ • notificaciones (Bandeja y SSE)       │   │ • insumos / inventario_medico          │
│                                        │   │ • mobiliario / equipos / fichas_tec    │
│                                        │   │ • rutas / vehiculos / repuestos        │
└────────────────────────────────────────┘   └────────────────────────────────────────┘
```

### 1. Separación de Responsabilidades

| Esquema de BD | Propósito de Seguridad / Negocio | Tablas Principales |
| :--- | :--- | :--- |
| **`dirpoles_security`** | **Infraestructura de Seguridad y Control de Accesos:** Almacena identidades de empleados, credenciales cifradas, configuración del RBAC, persistencia de intentos de login, tokens de refresco, auditoría de bitácora y notificaciones. | `empleado`, `tipo_empleado`, `modulo`, `permisos`, `rol_modulo_permiso`, `login_intentos`, `refresh_tokens`, `bitacora`, `notificaciones`. |
| **`dirpoles_business`** | **Dominio Operativo y Expedientes Clínicos/Sociales:** Contiene la información de los estudiantes beneficiarios, citas agendadas, historias clínicas, diagnósticos de especialidades, control de inventario médico, bienes muebles y flota de transporte. | `beneficiario`, `citas`, `consulta_psicologica`, `consulta_medica`, `orientacion`, `becas`, `exoneracion`, `fames`, `gestion_emb`, `discapacidad`, `insumos`, `inventario_medico`, `referencias`, `jornadas_medicas`, `mobiliario`, `equipos`, `rutas`, `vehiculos`, `mantenimiento_vehiculos`. |

---

### 2. Resolución de Identidad Global (`UNION ALL` Cross-Schema)
Un desafío crítico de ingeniería al utilizar bases de datos separadas es garantizar la unicidad de datos de identidad (Cédula, Correo Electrónico y Teléfono) en **todo el sistema**, evitando que un mismo número de cédula o correo exista simultáneamente como Empleado en `dirpoles_security` y como Beneficiario o Proveedor en `dirpoles_business`.

DIRPOLES-4 resuelve este desafío mediante consultas SQL calificadas de alto rendimiento que ejecutan un **`UNION ALL` entre esquemas cruzados**:

```sql
SELECT COUNT(*) FROM (
    SELECT id_empleado 
      FROM dirpoles_security.empleado
     WHERE tipo_cedula = :t1 AND cedula = :c1

    UNION ALL

    SELECT id_beneficiario 
      FROM dirpoles_business.beneficiario
     WHERE tipo_cedula = :t2 AND cedula = :c2 AND id_beneficiario <> :x2

    UNION ALL

    SELECT id_proveedor 
      FROM dirpoles_business.proveedores
     WHERE tipo_documento = :t3 AND num_documento = :c3
) t
```

#### Ventajas de la Resolución Cross-Schema:
1. **Unicidad Absoluta:** La validación remota en tiempo real (ej: `api/beneficiarios/validar_cedula`) verifica la inexistencia del documento en las tres entidades (Empleado, Beneficiario y Proveedor) antes de autorizar el registro.
2. **Exclusión en Edición (`:x2`):** Durante las operaciones de actualización, se pasa el parámetro `id_excluir`, permitiendo que la entidad conserve sus datos propios sin competir contra su propio registro.
3. **Rendimiento Indizado:** Todas las columnas de búsqueda (`tipo_cedula`, `cedula`, `correo`, `telefono`) poseen índices secundarios B-Tree en sus respectivas tablas, garantizando que el `UNION ALL` se resuelva en sub-milisegundos.

---

## 2.5 Arquitectura de Frontend Server-Side Rendered (SSR) y JavaScript Modular

El frontend de DIRPOLES-4 combina la velocidad de carga del renderizado en servidor (SSR) con la fluidez interactiva de módulos JavaScript nativos organizados por responsabilidad.

### 1. Estructura de Plantillas PHP Modular
Cada vista principal se compone mediante la inclusión jerárquica de parciales reutilizables ubicados en `app/Views/template/`:

```
┌─────────────────────────────────────────────────────────────────┐
│ app/Views/template/head.php (Meta, CSS Bootstrap 5, FontAwesome)│
├─────────────────────────────────────────────────────────────────┤
│ app/Views/template/sidebar.php (Menú dinámico según RBAC)      │
├─────────────────────────────────────────────────────────────────┤
│ app/Views/template/header.php / topbar.php (SSE, Perfil Usuario)│
├─────────────────────────────────────────────────────────────────┤
│                       VISTA DEL MÓDULO                          │
│          (ej: app/Views/beneficiario/consultar.php)             │
├─────────────────────────────────────────────────────────────────┤
│ app/Views/template/footer.php (Derechos, Versión)              │
├─────────────────────────────────────────────────────────────────┤
│ app/Views/template/script.php (JS Core + Scripts del Módulo)   │
└─────────────────────────────────────────────────────────────────┘
```

---

### 2. Patrón de Scripts JS por Módulo
Para evitar archivos monolíticos inmanejables (*spaghetti JS*), la lógica del cliente se descompone en archivos especializados almacenados en `dist/js/modulos/<modulo>/`:

| Archivo JavaScript | Responsabilidad en la Interfaz |
| :--- | :--- |
| **`validaciones.js`** | Contiene expresiones regulares, validadores de formulario locales y funciones de validación remota asíncrona (comprobación de unicidad de cédula, correo, teléfono o serial). |
| **`tour.js`** | Define el onboarding interactivo paso a paso mediante `Driver.js` para guiar al usuario en la pantalla (`#btn-ayuda`). |
| **`stats.js`** | Carga y actualiza las métricas y contadores dinámicos de las tarjetas `data-stat` en el encabezado de la página. |
| **`crear.js`** | Gestiona los eventos de envío del formulario de alta, carga de catálogos Select2 y recepción de respuestas de la API. |
| **`editar.js`** | Controla la apertura de modales de edición, consulta de datos por ID (`api/<modulo>/obtener/<id>`) y actualización. |
| **`consultar.js`** | Inicializa y destruye DataTables, gestiona filtros, renderiza filas con escape HTML y procesa confirmaciones SweetAlert2. |

---

### 3. Integración de Componentes Clave

#### a) DataTables (Ciclo de Vida y Renderizado Limpio)
Para prevenir fugas de memoria y duplicación de eventos al recargar datos asíncronos, los listados aplican el patrón de **destrucción limpia**:
```javascript
async function cargarTabla() {
    if ($.fn.DataTable.isDataTable('#tabla-beneficiarios')) {
        $('#tabla-beneficiarios').DataTable().clear().destroy();
    }
    
    const datos = await apiFetch(BASE_URL + 'api/beneficiarios/listar');
    const tbody = document.querySelector('#tabla-beneficiarios tbody');
    tbody.innerHTML = datos.map(b => `
        <tr>
            <td>${escapeHTML(b.tipo_cedula)}-${escapeHTML(b.cedula)}</td>
            <td>${escapeHTML(b.nombres)} ${escapeHTML(b.apellidos)}</td>
            <td>${escapeHTML(b.correo)}</td>
            <td>${renderAcciones(b.id_beneficiario)}</td>
        </tr>
    `).join('');

    $('#tabla-beneficiarios').DataTable({
        language: dataTablesES,
        responsive: true
    });
}
```

#### b) Select2 (Sincronización de Eventos y Clases de Validación)
Los elementos `<select>` enriquecidos con Select2 requieren una sincronización explícita con las clases de validación de Bootstrap 5:
* **Eventos `change` / `reset`:** Al reiniciar un formulario (`form.reset()`), se debe disparar `$('#select-pnf').val(null).trigger('change')` para limpiar la selección visual.
* **Targeting visual (`is-valid` / `is-invalid`):** Select2 oculta el elemento `<select>` nativo. Los estilos de validación del sistema aplican la clase de error directamente sobre el contenedor generado (`.select2-container`), garantizando que los bordes rojos o verdes se muestren correctamente alrededor de la caja desplegable.

#### c) Driver.js (Onboarding Guiado)
Los tours guiados interactivos se activan mediante el botón `#btn-ayuda`. Para elementos Select2, el paso del tour apunta explícitamente al contenedor generado:
```javascript
const tour = driver({
    showProgress: true,
    steps: [
        { element: '#cedula', popover: { title: 'Cédula', description: 'Ingrese el documento de identidad.' } },
        { element: '#id_pnf + .select2-container', popover: { title: 'PNF', description: 'Seleccione el programa de estudio.' } }
    ]
});
```

---

### 4. Manejo de Peticiones Asíncronas (`apiFetch`) y Validación Defensiva

#### Helper Central `apiFetch` (`dist/js/core/apiFetch.js`)
Es la única vía autorizada para realizar peticiones HTTP a la API JSON. Encapsula `fetch` garantizando el cumplimiento del contrato:

```javascript
async function apiFetch(url, opciones = {}) {
    const resp = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store',
        ...opciones,
    });

    const contentType = resp.headers.get('Content-Type') || '';
    const esJson = contentType.includes('json');

    if (resp.status === 401 && esJson) {
        const cuerpo = await resp.json();
        window.location.href = cuerpo.datos?.redireccion ?? BASE_URL + 'login';
        return;
    }

    if (!esJson) {
        throw {
            codigo: 'INTERNAL_SERVER_ERROR',
            estado: resp.status,
            mensaje: 'El servidor devolvió una respuesta no válida.',
        };
    }

    const cuerpo = await resp.json();
    if (!resp.ok || !cuerpo.exito) {
        throw cuerpo.error ?? {
            codigo: 'INTERNAL_SERVER_ERROR',
            estado: resp.status,
            mensaje: 'Error inesperado',
        };
    }
    return cuerpo.datos;
}
```

#### Comprobación Remota con Debounce y Respuesta Defensiva
Las validaciones en tiempo real (disponibilidad de cédula, correo o teléfono) utilizan una técnica de retardo (*debounce* de 400ms) para evitar saturar la API con cada pulsación de tecla.

**Principio de Validación Defensiva:** Si la API falla (error 500, caída de red o timeout), la validación **jamás asume validez** (nunca marca el campo en verde). Responde de forma defensiva mostrando el mensaje en rojo *"No se pudo verificar la disponibilidad"* y retornando `false`, bloqueando el envío del formulario hasta que la comunicación se restablezca.

---

# CAPÍTULO 3: Marco de Seguridad Defensiva, Criptografía y Auditoría

## 3.1 Protocolo de Autenticación Híbrida

DIRPOLES-4 implementa una arquitectura de autenticación multicapa diseñada para proteger las credenciales del usuario desde el navegador del cliente hasta su validación en el servidor de base de datos.

```
 [ NAVEGADOR / CLIENTE ]                                       [ SERVIDOR WEB PHP ]
 ┌────────────────────────┐                                   ┌──────────────────────────────────┐
 │ Contraseña en Plano   │                                   │ app/Config/Keys/login_private.pem│
 │ (ej: "Pass123*")       │                                   │ (Permisos chmod 600 estricto)    │
 └───────────┬────────────┘                                   └────────────────┬─────────────────┘
             │                                                                 │
   Cifrado RSA-2048 en JS                                                      │
   (Llave Pública RSA)                                                         │
             │                                                                 │
             ▼                                                                 ▼
 ┌────────────────────────┐    HTTP POST (password_cifrada)    ┌──────────────────────────────────┐
 │ String Base64 Cifrado  ├───────────────────────────────────►│ openssl_private_decrypt()        │
 └────────────────────────┘                                   │ Descifra contraseña a plano      │
                                                              └────────────────┬─────────────────┘
                                                                               │
                                                                    password_verify($plana, $hash)
                                                                               │
                                                                               ▼
                                                              ┌──────────────────────────────────┐
                                                              │ Hashing Bcrypt (cost 10+)        │
                                                              │ dirpoles_security.empleado       │
                                                              └──────────────────────────────────┘
```

---

### 1. Cifrado Asimétrico en Tránsito con RSA
Para contrarrestar ataques de sniffing de red, intercepción de tráfico en redes no seguras o inspección de paquetes en puntos intermedios (Man-in-the-Middle), la contraseña del usuario se cifra en el navegador del cliente **antes de ser transmitida por HTTP/POST**.

#### a) Cifrado en el Cliente (JavaScript):
El formulario de inicio de sesión utiliza la llave pública RSA-2048 del servidor (`app/Config/Keys/login_public.pem`) para transformar la cadena ingresada en un bloque cifrado de 256 bytes codificado en Base64. La contraseña real jamás viaja en texto claro por la red.

#### b) Descifrado en Servidor (`loginController.php`):
El servidor intercepta el payload Base64 y lo descifra utilizando la llave privada de login (`login_private.pem`):
```php
$keyPath = BASE_PATH . 'app/Config/Keys/login_private.pem';
if (!file_exists($keyPath)) {
    throw ExcepcionApi::errorInterno('Llave privada de login no encontrada.');
}

$cifrada = base64_decode($password, true);
$plana   = '';
if ($cifrada === false || !openssl_private_decrypt($cifrada, $plana, (string) file_get_contents($keyPath))) {
    throw ExcepcionApi::validacion('No se pudo procesar la contraseña. Vuelve a intentarlo.');
}
```

#### c) Control de Permisos en Sistema de Archivos:
El archivo de llave privada `login_private.pem` reside fuera del alcance de la raíz web y está configurado estrictamente con permisos de sistema **`chmod 600`** (perteneciente al usuario del proceso servidor web, e.g., `www-data` o `apache`), impidiendo la lectura por parte de usuarios no privilegiados del sistema operativo.

---

### 2. Hashing e Irreversibilidad con Bcrypt
Una vez descifrada la clave plana en la memoria volátil del servidor, se contrasta contra la credencial almacenada en la tabla `dirpoles_security.empleado`.

* **Algoritmo Bcrypt (Blowfish Cipher):** Utiliza la función nativa `password_hash($plana, PASSWORD_BCRYPT, ['cost' => 10])` para la creación de cuentas.
* **Verificación de Firma:** Invocación de `password_verify($password, $usuario['clave'])` dentro de `loginModel.php`.
* **Protección Criptográfica:** Bcrypt es una función de derivación de claves de una sola vía (*One-Way Key Derivation Function*) que incorpora un *salt* criptográfico aleatorio de 128 bits en cada hash. Esto garantiza que dos usuarios con la misma clave tengan hashes completamente distintos en la base de datos, neutralizando ataques de tablas Arcoiris (*Rainbow Tables*) o diccionarios pre-computados.

---

### 3. Mecanismo Anti-Fuerza Bruta Persistido en BD
DIRPOLES-4 implementa un mecanismo de defensa contra ataques de fuerza bruta (*Brute Force Attack Mitigation*) cuyas políticas y estados se persisten directamente en la base de datos `dirpoles_security` (tabla `login_intentos`).

```php
private const MAX_INTENTOS = 3;

private function autenticar(): mixed
{
    // ... búsqueda del usuario por correo ...
    if ((int) $usuario['estatus'] === 0) {
        throw new ExcepcionApi(
            ErrorCodes::CUENTA_BLOQUEADA,
            403,
            'Cuenta bloqueada, contacte al administrador.'
        );
    }

    if (password_verify($password, $usuario['clave'])) {
        $this->limpiarIntentos($correo);
        return $usuario;
    }

    // Exención de política de bloqueo para administradores
    if (self::esAdministrador($usuario['nombre_tipo'])) {
        throw ExcepcionApi::validacion('Credenciales inválidas.');
    }

    // Registro transaccional del intento fallido
    $intentos = $this->registrarIntentoFallido($correo);

    if ($intentos >= self::MAX_INTENTOS) {
        $this->deshabilitarUsuario();
        $this->limpiarIntentos($correo);
        throw new ExcepcionApi(
            ErrorCodes::CUENTA_BLOQUEADA,
            403,
            'Cuenta bloqueada tras ' . self::MAX_INTENTOS . ' intentos fallidos. Contacte al administrador.'
        );
    }

    throw ExcepcionApi::validacion('Credenciales inválidas.');
}
```

#### Ventajas de la Persistencia en Base de Datos:
1. **Resiliencia ante Limpieza de Cookies / Cambios de Navegador:** Debido a que el número de intentos se vincula a la cuenta del usuario en la base de datos (`login_intentos`) y no a la sesión del navegador, el atacante **no puede evade el bloqueo** borrando cookies, cambiando de navegador o utilizando direcciones IP/proxys diferentes.
2. **Bloqueo Atómico (`estatus = 0`):** Al alcanzar el umbral estricto de 3 intentos fallidos consecutivos (`MAX_INTENTOS = 3`), el sistema ejecuta de forma inmediata un `UPDATE empleado SET estatus = 0 WHERE correo = :correo`, deshabilitando la cuenta hasta que un Administrador la reactive manualmente.
3. **Protección de Cuentas Administrativas:** Las cuentas con rol Administrador o Superusuario están exentas de la deshabilitación automática para evitar ataques de Denegación de Servicio (DoS) que busquen bloquear las cuentas de los gestores del sistema.
4. **Mensaje de Error Genérico:** Ante un fallo de correo o clave, el sistema responde siempre con la frase ambigua *"Credenciales inválidas"*, impidiendo la enumeración de usuarios válidos.

---

## 3.2 Tokens de Acceso y Sesión

DIRPOLES-4 implementa un modelo de sesión híbrido que combina el estado del servidor (`$_SESSION`) con la validación de estado por cliente mediante tokens JWT firmados asimétricamente y Refresh Tokens de un solo uso.

---

### 1. JSON Web Tokens (JWT) firmados con RS256
Los tokens de acceso se emiten en `loginController.php` utilizando la clase `JwtHandler` y la librería `firebase/php-jwt`.

* **Firma Asimétrica RS256 (RSA Signature with SHA-256):** El backend firma digitalmente el payload del token utilizando la llave privada `app/Config/Keys/jwt_private.pem`. Los middlewares de verificación validan la integridad y la autenticidad del token utilizando exclusivamente la llave pública `jwt_public.pem`.
* **Estructura del Payload JWT:**
  ```json
  {
    "iat": 1774972800,
    "exp": 1774976400,
    "data": {
      "id_empleado": 12,
      "nombre": "Carlos",
      "id_tipo_empleado": 2,
      "tipo_empleado": "Médico"
    }
  }
  ```
* **Transporte Seguro en Cookie `HttpOnly`:**
  El JWT se transmite hacia el cliente emitiendo una cookie con las directivas de seguridad máximas:
  ```php
  setcookie('jwt_token', $jwtResult['token'], [
      'expires'  => $jwtResult['expiracion'],
      'path'     => '/',
      'secure'   => $cookieSecure,
      'httponly' => true,
      'samesite' => 'Lax',
  ]);
  ```
  La directiva `httponly: true` impide que cualquier script ejecutado en el navegador (incluso ante una eventual falla XSS) pueda leer el token a través de `document.cookie`.

---

### 2. Refresh Tokens Rotativos y Hashing SHA-256
Para permitir la continuidad operativa sin extender la vigencia del JWT corto (expiración de 1 hora), el sistema implementa la rotación de Refresh Tokens.

```php
private function generarRefreshToken()
{
    $id_empleado = $this->__get('id_empleado');
    $token = bin2hex(random_bytes(64)); // 128 caracteres hexadecimales aleatorios
    $tokenHash = hash('sha256', $token); // Hash SHA-256 para persistencia
    $expiresAt = date('Y-m-d H:i:s', time() + (int) env('REFRESH_EXPIRATION', '2592000'));

    $pdo = $this->getSecurityConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO refresh_tokens (id_empleado, token, expires_at) 
         VALUES (:id, :token, :expires)"
    );
    $stmt->execute([
        'id'      => $id_empleado,
        'token'   => $tokenHash,
        'expires' => $expiresAt
    ]);

    return ['estado' => 'exito', 'token' => $token, 'expiracion' => time() + 2592000];
}
```

#### Medidas de Ciberseguridad en Refresh Tokens:
1. **Almacenamiento Unidireccional (SHA-256):** En la base de datos `dirpoles_security.refresh_tokens` **jamás se almacena la cadena del token original**. Se persiste únicamente el hash digest SHA-256 (`hash('sha256', $token)`). Si un atacante compromete la base de datos o extrae un archivo de respaldo SQL, no podrá utilizar los valores de la tabla para autenticarse, ya que no puede revertir el hash SHA-256 al token original que exige la cookie.
2. **Rotación Atómica de Un Solo Uso (*One-Time Use*):** Al renovar el JWT (`renovar_jwt`), el middleware invalida y elimina inmediatamente el Refresh Token consumido (`DELETE FROM refresh_tokens WHERE token = :hash`), emitiendo un nuevo par de tokens. Si se detecta el reuso de un token ya consumido, se asume suplantación y se revocan automáticamente todos los refresh tokens asociados a ese usuario.

---

### 3. Validación Cruzada en Middleware (`SessionAuth`)
Para prevenir ataques de suplantación mediante tokens clonados (*Token Replay Attack*), los middlewares globales ejecutan una validación cruzada estricta entre la sesión del servidor y el token del cliente:

1. Extrae la cookie `jwt_token` y decodifica el payload utilizando la llave pública `jwt_public.pem`.
2. Verifica la vigencia del claim de expiración (`exp > time()`).
3. **Validación Cruzada de Identidad:** Compara el atributo `data.id_empleado` del JWT decodificado contra `$_SESSION['id_empleado']`.
4. Si los identificadores difieren o la sesión en servidor ha sido destruida (logout o expiración de inactividad), el middleware cancela el procesamiento de forma inmediata arrojando un error `401 Unauthorized` y destruyendo las cookies remanentes.

---

## 3.3 Mitigación de Vulnerabilidades Web (Alineación OWASP Top 10)

La arquitectura de DIRPOLES-4 incorpora controles de seguridad alineados con el estándar **OWASP Top 10** en todos los niveles del monolito.

---

### 1. A03: Injection $\rightarrow$ Inyección SQL y Prepared Statements
El sistema prohíbe la concatenación directa de variables dentro de sentencias SQL. El 100% de las peticiones a la base de datos se ejecutan a través de sentencias preparadas nativas con PDO.

```php
// Binding estricto de tipos en cláusulas LIMIT y OFFSET (BeneficiarioModel.php)
$stmt = $this->conn->prepare(
    "SELECT b.*, p.nombre_pnf 
       FROM beneficiario b
       INNER JOIN pnf p ON b.id_pnf = p.id_pnf
      WHERE b.estatus = 1
      ORDER BY b.id_beneficiario DESC
      LIMIT :limit OFFSET :offset"
);

$stmt->bindValue(':limit', (int) $this->__get('limit'), PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $this->__get('offset'), PDO::PARAM_INT);
$stmt->execute();
```

#### Principios de Prevención SQLi:
* **Asignación Explícita de Tipos:** Las variables numéricas para paginación (`LIMIT`, `OFFSET`) y claves primarias se vinculan explícitamente como `PDO::PARAM_INT`. Esto elimina la vulnerabilidad donde un atacante inyecta expresiones SQL en parámetros de paginación.
* **Tipado Forzado:** Los valores pasados a los métodos de modelo atraviesan los mutadores `__set()`, donde son convertidos mediante `(int)` o `(string)` antes de la preparación de la consulta.

---

### 2. A01: Broken Access Control $\rightarrow$ Control de Alcance (BOLA / IDOR)
Las vulnerabilidades de **Autorización a Nivel de Objeto Roto (BOLA/IDOR)** ocurren cuando un usuario autenticado modifica parámetros en la URL (e.g., `GET /api/medicina/obtener?id=105`) para acceder a registros que pertenecen a otro profesional o usuario.

DIRPOLES-4 neutraliza esta amenaza implementando el patrón `asegurarAlcance($id)` en los modelos de negocio:

```php
private function asegurarAlcance(int $id): array
{
    $sql = 'SELECT cm.*, ss.id_beneficiario, ss.id_empleado
            FROM consulta_medica cm
            INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = cm.id_solicitud_serv
            WHERE cm.id_consulta_med = :id';

    // Si el usuario NO es Administrador ni Superusuario, filtra estrictamente por su ID de empleado
    if (!$this->esAdministrativo()) {
        $sql .= ' AND ss.id_empleado = :id_empleado';
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    if (!$this->esAdministrativo()) {
        $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
    }
    $stmt->execute();

    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$registro) {
        throw ExcepcionApi::noEncontrado('La consulta médica no existe o no está disponible para tu usuario.');
    }
    return $registro;
}
```

#### Garantías contra BOLA / IDOR:
* **Validación en el Motor SQL:** La restricción de titularidad `AND ss.id_empleado = :id_empleado` se evalúa directamente dentro de la consulta SQL.
* **Respuesta Homogénea:** Si un usuario intenta consultar o modificar un ID ajeno, la consulta devuelve 0 filas y el modelo dispara una `ExcepcionApi::noEncontrado()`, impidiendo que el atacante determine si el ID ajeno existe o no.

---

### 3. A03: Injection $\rightarrow$ Cross-Site Scripting (XSS)
DIRPOLES-4 aplica una estrategia de desinfección defensiva en dos fases:

1. **Saneo de Entrada en `__set()`:**
   Todas las cadenas recibidas en el backend se procesan con funciones de sanitización, recortando espacios en blanco (`trim`), limitando la longitud máxima con `mb_substr` y eliminando caracteres nulos o etiquetas maliciosas.
2. **Escapado Estricto en Salida (Vistas y JavaScript):**
   * En plantillas HTML PHP, toda variable se renderiza mediante `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.
   * En JavaScript modular, la inyección de datos dentro de DataTables o modales utiliza la función de escape `escapeHTML(texto)`:
     ```javascript
     function escapeHTML(str) {
         return String(str ?? '').replace(/[&<>"']/g, match => ({
             '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
         }[match]));
     }
     ```

---

### 4. A07: Identification and Authentication Failures $\rightarrow$ Fijación de Sesión y MitM
* **Regeneración de ID de Sesión:** Inmediatamente después de validar las credenciales del usuario en `loginController.php`, se invoca `session_regenerate_id(true)`. Esto invalida la cookie de sesión anterior, garantizando que si un atacante fijó un ID de sesión previo a la autenticación, dicho ID quede obsoleto.
* **Banderas de Cookies HTTP:** Las cookies emitidas por el sistema (`jwt_token`, `refresh_token`, `PHPSESSID`) incorporan las directivas `HttpOnly`, `SameSite=Lax/Strict` y la bandera `Secure` configurada dinámicamente si el servidor detecta HTTPS (`$_SERVER['HTTPS']` o puerto `443`).

---

### 5. A04: Insecure Design $\rightarrow$ Rate Limiting Perimétrico (Token Bucket)
Para proteger la infraestructura contra ataques de denegación de servicio (DoS), sobrecarga de endpoints y escaneos automatizados, se implementa el middleware `RateLimitMiddleware` utilizando el algoritmo **Token Bucket** (Cubo de Tokens) persistido en base de datos.

```php
public static function handle()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $endpoint = self::getCleanEndpoint();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Resuelve la política dinámica por macro-categoría de ruta
    $policy = self::resolvePolicy($method, $endpoint);
    $capacity = (float) $policy['capacity'];
    $rate = (float) $policy['rate'];

    $pdo = self::getPdo();
    $record = self::queryRateLimit($pdo, $ip, $endpoint);
    $currentTime = time();

    if (!$record) {
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, endpoint, tokens_actuales, ultima_peticion) VALUES (:ip, :endpoint, :tokens, :time)");
        $stmt->execute(['ip' => $ip, 'endpoint' => $endpoint, 'tokens' => $capacity - 1.0, 'time' => $currentTime]);
        return;
    }

    $tiempoTranscurrido = max(0, $currentTime - (int)$record['ultima_peticion']);
    $tokensRegenerados = $tiempoTranscurrido * $rate;
    $tokensCalculados = min($capacity, (float)$record['tokens_actuales'] + $tokensRegenerados);

    if ($tokensCalculados < 1.0) {
        $segundosEspera = (int) ceil((1.0 - $tokensCalculados) / $rate);
        if (!headers_sent()) {
            http_response_code(429);
            header('Retry-After: ' . $segundosEspera);
        }
        throw new ExcepcionApi(ErrorCodes::RATE_LIMIT_EXCEDIDO, 429, 'Demasiadas peticiones. Intente en ' . $segundosEspera . 's.');
    }

    $stmt = $pdo->prepare("UPDATE rate_limits SET tokens_actuales = :tokens, ultima_peticion = :time WHERE ip_address = :ip AND endpoint = :endpoint");
    $stmt->execute(['tokens' => $tokensCalculados - 1.0, 'time' => $currentTime, 'ip' => $ip, 'endpoint' => $endpoint]);
}
```

#### Políticas por Macro-Categorías de Ruta:
1. **Autenticación Sensible (e.g. `/login`):** Capacidad de 5 tokens, tasa de regeneración de 5 intentos por cada 5 minutos (`rate = 5/300`).
2. **Escritura Transaccional (POST/PUT/DELETE):** Capacidad de 15 a 30 tokens, regeneración de 30 peticiones por minuto.
3. **Lectura y Búsquedas Asíncronas:** Capacidad de 80 tokens, regeneración de 80 peticiones por minuto.

---

## 3.4 Sistema Transversal de Auditoría (Bitácora)

El módulo de auditoría en DIRPOLES-4 proporciona trazabilidad inmutable y forense de todas las operaciones realizadas dentro de la plataforma.

### 1. Registro Automático de Eventos
La clase estática `App\Core\Bitacora` expone el método universal `Bitacora::registrar($modulo, $accion, $descripcion)`:

```php
public static function registrar(string $modulo, string $accion, string $descripcion): bool
{
    try {
        $idEmpleado = $_SESSION['id_empleado'] ?? null;
        if ($idEmpleado === null) {
            error_log('Bitacora::registrar - sin sesión activa, no se registra: ' . $descripcion);
            return false;
        }

        if (!in_array($accion, self::ACCIONES, true)) {
            error_log("Bitacora::registrar - acción inválida '{$accion}'.");
            return false;
        }

        $stmt = self::conexion()->prepare(
            "INSERT INTO bitacora (id_empleado, modulo, accion, descripcion, fecha)
             VALUES (:id_empleado, :modulo, :accion, :descripcion, NOW())"
        );
        return $stmt->execute([
            ':id_empleado' => (int) $idEmpleado,
            ':modulo'      => mb_strcut(trim($modulo), 0, 50),
            ':accion'      => $accion,
            ':descripcion' => mb_strcut(trim($descripcion), 0, 255),
        ]);
    } catch (Throwable $e) {
        error_log('Bitacora::registrar - ' . $e->getMessage());
        return false;
    }
}
```

---

### 2. Validación Estricta del ENUM de Acciones
Para mantener la consistencia en los datos de auditoría y evitar la inserción de acciones arbitrarias, la constante `Bitacora::ACCIONES` valida la entrada contra la lista blanca oficial definida en el esquema de `dirpoles_security.bitacora`:

```php
public const ACCIONES = [
    'Registro',
    'Lectura',
    'Actualización',
    'Eliminación',
    'Inicio de sesión',
    'Cierre de sesión',
    'Respaldo',
];
```

Si un controlador envía una acción fuera de esta lista blanca (e.g. `'Consulta'` o `'Modificar'`), el método la rechaza de inmediato escribiendo una advertencia en el log de errores del servidor.

---

### 3. Principio de Resiliencia A Prueba de Fallos (*Fail-Safe Audit*)
La auditoría opera bajo el principio de **no interferencia con la transacción de negocio**:
* El bloque `try/catch` interno atrapa cualquier falla de conexión o inserción en la base de datos de seguridad.
* Si el registro de auditoría falla por algún motivo técnico, el error se vuelca en `logs/php_errors.log` y el método retorna `false`, **pero nunca interrumpe ni cancela la transacción de negocio** (e.g., la consulta médica o el registro de beneficiario) que el usuario acaba de completar.

---

### 4. Exclusión Deliberada de Consultas Auxiliares de Interfaz
Para evitar la saturación de la tabla `bitacora` con registros triviales sin valor forense, el sistema excluye deliberadamente las siguientes consultas:
1. **Carga de Catálogos auxiliares de UI:** Peticiones como `apiCatalogosMedicina()` o `apiPnfs()` que solo sirven para popular desplegables `<select>` en formularios.
2. **Peticiones SSE de Notificaciones:** Consultas continuas de la campana del topbar en `sse/notificaciones`.
3. **Verificaciones de Unicidad Remotas:** Comprobaciones en tiempo real (`validar_cedula`, `validar_correo`) mientras el usuario escribe en un formulario.

---

# CAPÍTULO 4: Radiografía Técnica de Módulos (Gestión Institucional y Servicios Clínicos)

---

## 4.1 Módulo de Empleados (id_modulo 1)

### A. Propósito y Flujo Operativo
El Módulo de Empleados administra la masa laboral de la UPTAEB que interactúa con DIRPOLES (médicos, psicólogos, trabajadores sociales, orientadores, especialistas en discapacidad, administrativos y personal técnico). Controla el ciclo de vida de las cuentas de acceso, asignación de cargos/roles y estado operativo (`Activo`/`Inactivo`).

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_security`.
* **Tabla Principal:** `empleado` (`id_empleado`, `id_tipo_empleado`, `cedula`, `tipo_cedula`, `nombre`, `apellido`, `correo`, `telefono`, `direccion`, `fecha_nac`, `clave`, `estatus`, `fecha_creacion`).
* **Tabla de Referencia:** `tipo_empleado` (`id_tipo_empleado`, `nombre_tipo`).
* **Relaciones:** `empleado.id_tipo_empleado` $\rightarrow$ `tipo_empleado.id_tipo_empleado` (FK restringida).

### C. Lógica del Controlador (`empleadoController.php`)
* **Pistas HTML:** `showCrearEmpleado()`, `showConsultarEmpleado()` $\rightarrow$ Verifican `Autorizacion::verificar('empleados', 'crear')` y `'leer'`.
* **Endpoints API JSON:**
  - `apiListarEmpleados()` $\rightarrow$ `Autorizacion::verificar('empleados', 'leer')`.
  - `apiCrearEmpleado()` $\rightarrow$ `Autorizacion::verificar('empleados', 'crear')`.
  - `apiActualizarEmpleado()` $\rightarrow$ `Autorizacion::verificar('empleados', 'editar')`.
  - `apiEliminarEmpleado()` $\rightarrow$ `Autorizacion::verificar('empleados', 'eliminar')`.

### D. Lógica del Modelo (`EmpleadoModel extends SecurityModel`)
* **Validación en `__set()`:** Normalización de cédula (6 a 10 dígitos), correo RFC 5322, teléfono móvil venezolano (`0412|0414|0416|0422|0424|0426`), `id_tipo_empleado` válido.
* **Despachador `manejarAccion()`:** Encauza a `crear()`, `actualizar()`, `eliminar()`, `stats()`, `existe_cedula()`, `existe_correo()`. Hashing Bcrypt al crear o modificar contraseña.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Unicidad Global Cross-Schema:** Cédula, Correo y Teléfono compiten en un `UNION ALL` contra `beneficiario` y `proveedores`.
2. **Protección de Cuentas Administrativas:** Una cuenta con rol Administrador o Superusuario no puede ser eliminada ni deshabilitada si es el último administrador activo en el sistema.
3. **Autoprotección de Sesión:** Un usuario autenticado no puede eliminar ni deshabilitar su propia cuenta activa.

### F. Deficiencias del Sistema Viejo Superadas
En el sistema heredado, los empleados compartían la misma tabla que los beneficiarios en una sola base de datos, las contraseñas se almacenaban en MD5 sin salting, y era posible eliminar cuentas de administrador activas dejando el sistema sin posibilidad de gestión.

---

## 4.2 Módulo de Beneficiarios (id_modulo 2)

### A. Propósito y Flujo Operativo
Gestiona los expedientes estudiantiles de los beneficiarios de la UPTAEB. Registra la filiación personal, Programa Nacional de Formación (PNF), trayecto, sección y contacto, sirviendo como entidad central para todas las solicitudes de atención médica, psicológica y social.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tabla Principal:** `beneficiario` (`id_beneficiario`, `id_pnf`, `seccion`, `nombres`, `apellidos`, `tipo_cedula`, `cedula`, `fecha_nac`, `telefono`, `correo`, `genero`, `direccion`, `estatus`, `fecha_creacion`).
* **Tabla de Referencia:** `pnf` (`id_pnf`, `nombre_pnf`).
* **Relaciones:** `beneficiario.id_pnf` $\rightarrow$ `pnf.id_pnf`. Es referenciada por `solicitud_de_servicio.id_beneficiario`.

### C. Lógica del Controlador (`beneficiarioController.php`)
* **Permisos RBAC:** Módulo `'beneficiarios'`. Verificación en servidor de `crear`, `leer`, `editar`, `eliminar`.
* **Endpoints:** `apiListarBeneficiarios()`, `apiObtenerBeneficiario()`, `apiCrearBeneficiario()`, `apiActualizarBeneficiario()`, `apiEliminarBeneficiario()`, `apiPnfs()`.

### D. Lógica del Modelo (`BeneficiarioModel extends BusinessModel`)
* **Mutadores `__set()`:** Sanitización de nombres/apellidos, validador de cédula (6-10 dígitos), teléfono venezolano estricto, selección obligatoria de PNF.
* **Despachador `manejarAccion()`:** Transacciones PDO para inserción y actualización con binding `PARAM_INT` en paginación.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Verificación Cross-Schema:** No se permite registrar una cédula o correo que pertenezca a un empleado en `dirpoles_security`.
2. **Integridad Referencial:** No se puede eliminar un beneficiario que tenga solicitudes de servicio, citas agendadas o expedientes clínicos activos (lanza `ExcepcionApi::conflicto()` de código HTTP 409).

### F. Deficiencias del Sistema Viejo Superadas
El sistema viejo permitía duplicar cédulas entre empleados y estudiantes, no validaba el formato telefónico y borraba beneficiarios en cascada destruyendo el historial clínico de la universidad.

---

## 4.3 Módulo de Citas - Psicología (id_modulo 3)

### A. Propósito y Flujo Operativo
Gestiona la agenda y agendamiento de atenciones psicológicas para los estudiantes beneficiarios. Permite concertar citas contra la disponibilidad real de los psicólogos de DIRPOLES.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tabla Principal:** `citas` (`id_cita`, `id_solicitud_serv`, `fecha_cita`, `hora_cita`, `estado`, `motivo_cancelacion`).
* **Tabla Vinculada:** `solicitud_de_servicio` (`id_solicitud_serv`, `id_beneficiario`, `id_empleado`, `id_servicios=1`, `fecha_solicitud`).

### C. Lógica del Controlador (`citaController.php`)
* **Permisos RBAC:** Módulo `'citas'`.
* **Endpoints API:** `apiListarCitas()`, `apiCrearCita()`, `apiActualizarEstadoCita()`, `apiEliminarCita()`, `apiHorarioPsicologo()`.

### D. Lógica del Modelo (`CitaModel extends BusinessModel`)
* **Validaciones:** `fecha_cita` $\ge$ fecha actual, `hora_cita` en rango de atención (07:00 a 17:00).
* **Alcance por Rol (`asegurarAlcanceCita()`):** Los psicólogos solo visualizan y gestionan las citas asignadas a su usuario. Administradores/Superusuarios gestionan todas.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Control de No Solapamiento:** Un psicólogo no puede tener dos citas programadas a la misma fecha y hora. Un beneficiario no puede tener dos citas activas en el mismo bloque horario.
2. **Validación contra Horario Laboral:** El modelo consulta `horario_psicologo` para verificar que el psicólogo atienda el día de la semana correspondiente a la `fecha_cita`.
3. **Ciclo de Vida Transaccional:** Cambio de estado de `Programada` a `Completada` o `Cancelada` (exige motivo de cancelación obligatorio).

### F. Deficiencias del Sistema Viejo Superadas
El sistema anterior agendaba citas en horas nocturnas o fines de semana, solapaba citas de un mismo especialista a la misma hora y no restringía la visualización de la agenda entre psicólogos.

---

## 4.4 Módulo de Horarios (id_modulo 18)

### A. Propósito y Flujo Operativo
Establece las franjas de atención semanal de los psicólogos adscritos a DIRPOLES. Es administrado exclusivamente por el personal de Jefatura/Administración.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tabla Principal:** `horario_psicologo` (`id_horario`, `id_empleado`, `dia_semana`, `hora_inicio`, `hora_fin`).
* **FK:** `id_empleado` $\rightarrow$ `dirpoles_security.empleado.id_empleado`.

### C. Lógica del Controlador (`horarioController.php`)
* **Permisos RBAC:** Exclusivo Administrador y Superusuario (`Autorizacion::verificar('horarios', 'crear/editar')`).

### D. Lógica del Modelo (`HorarioModel extends BusinessModel`)
* **Mutadores `__set()`:** `dia_semana` ENUM (`Lunes`, `Martes`, `Miércoles`, `Jueves`, `Viernes`), `hora_inicio` $\ge$ 07:00, `hora_fin` $\le$ 17:00, `hora_fin > hora_inicio`.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Unico Horario por Día y Psicólogo:** Cada psicólogo puede tener como máximo un registro de horario por cada día de la semana.
2. **Franja Estricta Institucional:** No se admiten horas fuera del intervalo 07:00 a 17:00.

### F. Deficiencias del Sistema Viejo Superadas
No existía módulo de horarios en la versión heredada; la disponibilidad de los especialistas no estaba parametrizada en base de datos.

---

## 4.5 Módulo de Medicina (id_modulo 5)

### A. Propósito y Flujo Operativo
Registra las consultas clínicas primarias prestadas a los estudiantes. Administra el examen físico, diagnóstico, tratamiento y el consumo inmediato de insumos y medicamentos del Inventario Médico.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tablas:** `consulta_medica`, `solicitud_de_servicio` (`id_servicios=2`), `detalle_insumo`, `insumos`, `inventario_medico`.
* **FKs:** `consulta_medica.id_solicitud_serv` $\rightarrow$ `solicitud_de_servicio`, `detalle_insumo.id_insumo` $\rightarrow$ `insumos`.

### C. Lógica del Controlador (`medicinaController.php`)
* **Permisos RBAC:** Módulo `'medicina'`, permisos `crear`, `leer`, `editar`, `eliminar`.
* **Endpoints:** `apiListarMedicina()`, `apiObtenerMedicina()`, `apiCrearMedicina()`, `apiActualizarMedicina()`, `apiEliminarMedicina()`, `generarConstanciaMedicina()`, `generarRecipeMedicina()`.

### D. Lógica del Modelo (`MedicinaModel extends BusinessModel`)
* **Mutadores `__set()`:** Estatura (> 0), Peso (> 0), Tipo de sangre ENUM, Motivo, Diagnóstico, Tratamiento.
* **Transacciones ACID:** `crear()` inicia transacción PDO, aplica `SELECT ... FOR UPDATE` sobre los insumos consumidos, descuenta el stock en PHP, evalúa el estatus (`Agotado`/`Disponible`) y registra el movimiento de `'Salida'` en `inventario_medico`.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Evaluación Atómica en MySQL:** Para evitar el bug de doble resta en MySQL al evaluar `CASE WHEN (cantidad - :c) <= 0`, la sentencia de `UPDATE insumos` reordena los campos asegurando que el estado se evalúe con el stock original antes de la resta.
2. **Edición Restringida:** Al editar una consulta médica, **SOLO se permite corregir** patología, estatura, peso, tipo de sangre, motivo, diagnóstico, tratamiento y observaciones. Jamás se permite alterar el beneficiario, el médico tratante ni los insumos descargados.
3. **Insumos No Reembolsables al Eliminar:** Al eliminar un registro clínico, **NO se devuelve el stock al inventario**. El medicamento ya fue administrado al paciente; `insumos.cantidad` e `inventario_medico` permanecen intactos. La Bitácora audita la eliminación registrando los insumos consumidos.

### F. Deficiencias del Sistema Viejo Superadas
Corrección del error grave de base de datos donde MySQL marcaba como `Agotado` insumos con stock positivo, prohibición de alterar insumos consumidos en edición y eliminación limpia sin adulteración de inventarios pasados.

---

## 4.6 Módulo de Orientación (id_modulo 6)

### A. Propósito y Flujo Operativo
Provee atención socioeducativa, vocacional y académica a los estudiantes beneficiarios, emitiendo recomendaciones institucionales y constancias de orientación.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tablas:** `orientacion`, `solicitud_de_servicio` (`id_servicios=3`).
* **FK:** `orientacion.id_solicitud_serv` $\rightarrow$ `solicitud_de_servicio.id_solicitud_serv`.

### C. Lógica del Controlador (`orientacionController.php`)
* **Permisos RBAC:** Módulo `'orientacion'`.
* **Endpoints:** `apiListarOrientacion()`, `apiCrearOrientacion()`, `apiActualizarOrientacion()`, `apiEliminarOrientacion()`, `generarConstanciaOrientacion()`.

### D. Lógica del Modelo (`OrientacionModel extends BusinessModel`)
* **Mutadores `__set()`:** Sanitización y validación estricta de 4 campos de texto obligatorios.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **4 Campos de Texto Obligatorios:** `motivo`, `descripcion`, `indicaciones` y `observaciones` son **estrictamente obligatorios**. Ninguno puede registrarse vacío ni nulo.
2. **Edición Encapsulada:** La edición solo permite actualizar los 4 textos clínicos/educativos. No permite cambiar el estudiante ni el orientador.
3. **Eliminación en Cascada Transaccional:** Elimina `orientacion` y su `solicitud_de_servicio` vinculada dentro de una misma transacción PDO.

### F. Deficiencias del Sistema Viejo Superadas
Superación de la inconsistencia del sistema heredado que dejaba campos nulos generando constancias vacías y permitía alterar la identidad del estudiante atendido.

---

## 4.7 Módulo de Discapacidad (id_modulo 8)

### A. Propósito y Flujo Operativo
Administra el expediente de estudiantes con diversidad funcional de la UPTAEB. Registra el tipo de discapacidad, grado de severidad, habilidades funcionales y necesidades de asistencia para la carnetización y apoyo institucional.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tablas:** `discapacidad`, `solicitud_de_servicio` (`id_servicios=5`).
* **FK:** `discapacidad.id_solicitud_serv` $\rightarrow$ `solicitud_de_servicio.id_solicitud_serv`.

### C. Lógica del Controlador (`discapacidadController.php`)
* **Permisos RBAC:** Módulo `'discapacidad'`.
* **Endpoints:** `apiListarDiscapacidad()`, `apiCrearDiscapacidad()`, `apiActualizarDiscapacidad()`, `apiEliminarDiscapacidad()`.

### D. Lógica del Modelo (`DiscapacidadModel extends BusinessModel`)
* **Validación en `__set()`:** Verificación estricta de ENUMs contra el esquema de base de datos (`tipo_discapacidad`, `grado`, `requiere_asistencia`).

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Campos ENUM Obligatorios:** `tipo_discapacidad` (`Motor`, `Visual`, `Auditiva`, `Intelectual`, `Psicosocial`, `Múltiple`, `Otra`), `grado` (`Leve`, `Moderado`, `Grave`, `Completo`) y `requiere_asistencia` (`Si`, `No`) deben coincidir exactamente con el catálogo.
2. **Obligatoriedad de Habilidades Funcionales:** Es obligatorio registrar el detalle de habilidades funcionales y observaciones para avalar el carnet.
3. **Edición Restringida:** Permite modificar los 11 campos descriptivos de la tabla `discapacidad`, pero prohíbe reasignar el beneficiario o el especialista.

### F. Deficiencias del Sistema Viejo Superadas
Se eliminó la inserción de textos libres en campos que debían ser categóricos, estandarizando los datos para la generación de reportes oficiales ante el CONAPDIS.

---

## 4.8 Módulo de Trabajo Social (id_modulo 7)

### A. Propósito y Flujo Operativo
Constituye el hub de asistencia socioeconómica de DIRPOLES. Administra 4 sub-flujos de apoyo (becas bancarias, exoneración de aranceles, ayudas de salud FAMES y protección a embarazadas), además de la elaboración del **Estudio Socioeconómico**.

### B. Esquema de Base de Datos y Claves Foráneas
* **Esquema:** `dirpoles_business`.
* **Tablas:** `becas`, `exoneracion`, `fames`, `gestion_emb`, `solicitud_de_servicio` (`id_servicios=4`).
* **Relación:** Cada sub-tabla mantiene su propia clave foránea `id_solicitud_serv` apuntando a la solicitud general.

### C. Lógica del Controlador (`trabajoSocialController.php`)
* **Permisos RBAC:** Módulo `'trabajo social'`.
* **Endpoints:** `apiListarTS()`, `apiCrearBeca()`, `apiCrearExoneracion()`, `apiCrearFames()`, `apiCrearEmbarazada()`, `procesarEstudioSocioeconomico()`.

### D. Lógica del Modelo (`TrabajoSocialModel extends BusinessModel`)
* **Listados Aislados con LEFT JOIN:** Las consultas de cada sub-pestaña filtran únicamente las filas pertenecientes a su sub-tipo de solicitud, evitando duplicaciones de filas.
* **Transaccionalidad en Archivos:** El registro de becas u exoneraciones valida la subida de planillas o cartas en formato PDF/imagen antes de confirmar el registro.

### E. Reglas de Negocio Innegociables y Casos Borde
1. **Estudio Socioeconómico Exclusivo:** El Estudio Socioeconómico (formulario offcanvas de 5 pasos) SOLO puede generarse desde registros de Exoneración en estado pendiente (`direccion_estudiose IS NULL`).
2. **Validación MIME Real en Fotos:** Si se adjunta fotografía en el Estudio Socioeconómico, se valida en dos fases: extensión permitida (`jpg`, `jpeg`, `png`, `gif`) + inspección de tipo MIME real mediante `finfo`.
3. **Generación FPDF y Limpieza de Huérfanos:** El PDF generado por FPDF se guarda en `uploads/trabajo_social/exoneracion/estudiose/`. La BD guarda la ruta mediante `UPDATE exoneracion SET direccion_estudiose = :ruta WHERE id_exoneracion = :id AND direccion_estudiose IS NULL`. Si el `UPDATE` afecta 0 filas (e.g. porque otro usuario procesó la exoneración en paralelo), la transacción se revierte y el archivo PDF recién creado **se elimina físicamente del disco** mediante una función de limpieza segura (*best-effort file cleanup*).
4. **Eliminación Segura de Archivos Físicos:** Al eliminar una beca o exoneración, se elimina el registro en BD y se borran del disco los archivos adjuntos (cartas, planillas, PDFs) verificando estrictamente que sus rutas se encuentren contenidas dentro de `uploads/trabajo_social/`.

### F. Deficiencias del Sistema Viejo Superadas
Eliminación de duplicados en listados por consultas SQL defectuosas, desinfección de archivos subidos previniendo la carga de scripts ejecutables en el servidor y gestión automatizada de limpieza de archivos huérfanos en el sistema de archivos.

---

# CAPÍTULO 5: Radiografía Técnica de Módulos (Inventario, Logística, Analítica y Configuración)

---

## 5.1 Módulo de Inventario Médico (id_modulo 9)

### A. Arquitectura de Datos y Kardex de Movimientos
* **Esquema:** `dirpoles_business`.
* **Tabla Principal:** `insumos` (`id_insumo`, `nombre`, `tipo`, `presentacion`, `cantidad`, `fecha_vencimiento`, `estatus`, `descripcion`).
* **Tabla Kardex:** `inventario_medico` (`id_inventario`, `id_insumo`, `id_empleado`, `tipo_movimiento`, `cantidad`, `motivo`, `fecha`).
* **FK:** `inventario_medico.id_insumo` $\rightarrow$ `insumos.id_insumo`, `inventario_medico.id_empleado` $\rightarrow$ `dirpoles_security.empleado.id_empleado`.

### B. Controlador, Rutas y Permisos RBAC (`inventarioController.php`)
* **Nombre RBAC:** `'inventario medico'` (permisos para roles 2 Médicos, 6 Administrador, 10 Superusuario).
* **Rutas API:** `api/inventario/listar`, `api/inventario/crear`, `api/inventario/actualizar`, `api/inventario/eliminar`, `api/inventario/entrada`, `api/inventario/salida`, `api/inventario/historial`.

### C. Lógica del Modelo y Mecanismos de Concurrencia (`InventarioModel`)
1. **Creación con Stock Zero:** Al crear un insumo nuevo, nace obligatoriamente con `cantidad = 0`, estatus `'Agotado'` y registra el movimiento de `'Registro'` en el Kardex. Las existencias ingresan **únicamente** a través del modal/endpoint de Entrada (requiere permiso `crear`).
2. **Entrada / Salida con `FOR UPDATE`:** Transacción ACID con `SELECT cantidad, estatus, fecha_vencimiento FROM insumos WHERE id_insumo = :id FOR UPDATE`.
   - **Entrada:** Solo sobre insumos no vencidos (`estatus != 'Vencido'` y `fecha_vencimiento >= hoy`). Incrementa el stock y actualiza estatus a `'Disponible'`.
   - **Salida:** Exige `cantidad > 0` y motivo obligatorio del catálogo `Vencimiento|Daño|Pérdida|Donación|Uso Interno`. Si la cantidad remanente llega a 0, cambia estatus a `'Agotado'`.
3. **Estatus Dinámico Calculado en Tiempo Real:** Si `fecha_vencimiento < hoy`, la interfaz y las consultas SQL de consulta presentan el estatus como `'Vencido'` en vivo, **sin necesidad de ejecutar `UPDATE` masivos continuos** sobre la base de datos. Las entradas quedan bloqueadas y las salidas deshabilitadas si el stock es 0.
4. **Restricción de Eliminación Integrada:** El insumo solo se puede eliminar si no aparece en `detalle_insumo`, si su stock es 0 y si no registra movimientos en el Kardex distintos de su `'Registro'` inicial.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
En el sistema anterior no existía Kardex de movimientos, se permitían ediciones directas del campo `cantidad` perdiendo trazabilidad, y la fecha de vencimiento no deshabilitaba las entradas de inventario.

---

## 5.2 Módulo de Referencias Interdepartamentales (id_modulo 10)

### A. Arquitectura de Datos y Registro de Historial
* **Esquema:** `dirpoles_business`.
* **Tabla Principal:** `referencias` (`id_referencia`, `id_beneficiario`, `id_servicio_origen`, `id_servicio_destino`, `id_empleado_origen`, `id_empleado_destino`, `motivo`, `observaciones`, `estado`, `fecha_creacion`).
* **Tabla Historial:** `log_referencias` (`id_log`, `id_referencia`, `id_empleado`, `estado_anterior`, `estado_nuevo`, `observaciones`, `fecha`).

### B. Controlador, Rutas y Permisos RBAC (`referenciaController.php`)
* **Nombre RBAC:** `'referencias'` (roles 1, 2, 3, 4, 5, 6, 10).
* **Rutas API:** `api/referencias/listar`, `api/referencias/crear`, `api/referencias/aceptar`, `api/referencias/rechazar`, `api/referencias/eliminar`.

### C. Lógica del Modelo y Concurrencia (`ReferenciaModel`)
1. **Regla de Origen / Destino Server-Side:** El Administrador puede seleccionar el especialista origen y destino. Para cualquier otro profesional, el backend ignora `id_empleado_origen` enviado por el cliente y lo **fuerza en servidor** al usuario autenticado (`$_SESSION['id_empleado']`).
2. **Servicio Destino Distinto:** El backend valida que `id_servicio_destino <> id_servicio_origen` (remisión inter-áreas).
3. **Aceptar / Rechazar con Bloqueo Pessimistic:** Ejecuta `SELECT estado FROM referencias WHERE id_referencia = :id FOR UPDATE`. Solo permite la transición desde el estado `'Pendiente'`. El rechazo exige motivo obligatorio que se guarda en `log_referencias.observaciones` (sin sobrescribir las observaciones originales de la referencia). Notifica al profesional origen.
4. **Eliminación Restringida:** Solo el autor de la referencia (o Admin) puede eliminarla, y solo si está `'Pendiente'`. Borra en transacción `log_referencias` primero (FK) y luego la fila principal.
5. **Control de Alcance (Data Scope):** Empleados no administradores solo listan o ven estadísticas de referencias donde su ID figure como origen o destino.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
El sistema heredado registraba el estado anterior en el historial hardcodeado como `'Pendiente'` sin auditar cambios reales, permitía que un especialista se remitiera a su propia área y no notificaba al destino.

---

## 5.3 Módulo de Jornadas Médicas (id_modulo 11)

### A. Arquitectura de Datos y Relaciones Complejas
* **Esquema:** `dirpoles_business`.
* **Tablas:** `jornadas_medicas`, `jornada_beneficiarios`, `jornada_diagnosticos`, `jornada_insumos`.
* **Estructura:** Una jornada posee $N$ beneficiarios asistentes; cada asistente puede recibir $M$ diagnósticos clínicos; cada diagnóstico consume $K$ insumos médicos.

### B. Controlador, Rutas y Permisos RBAC (`jornadasController.php`)
* **Nombre RBAC:** `'jornadas'` (roles 2 Médicos, 6 Administrador, 10 Superusuario).
* **Rutas:** `jornadas/crear`, `jornadas/consultar`, `jornadas/detalle/{id}`, `api/jornadas/buscar_persona`.

### C. Lógica del Modelo y Serialización de Concurrencia (`JornadaModel`)
1. **Control de Aforo Serializado (`FOR UPDATE`):** Para evitar que dos médicos registren asistentes simultáneamente superando el límite de la jornada, la inscripción ejecuta:
   ```sql
   SELECT aforo, (SELECT COUNT(*) FROM jornada_beneficiarios WHERE id_jornada = :id) as inscritos
     FROM jornadas_medicas 
    WHERE id_jornada = :id AND estatus = 'Activa' 
      FOR UPDATE
   ```
   Si `inscritos >= aforo`, el backend cancela la transacción y responde `ExcepcionApi::validacion("Aforo máximo alcanzado")`. Al editar la jornada, el aforo no puede reducirse por debajo de los asistentes ya registrados.
2. **Autocompletado de Asistentes:** `api/jornadas/buscar_persona` sugiere datos de `beneficiario` o `empleado` por cédula, pero el alta es confirmada manualmente por el usuario. Cédula única por jornada.
3. **Diagnóstico Múltiple por Asistente:** A diferencia del sistema viejo que restringía un diagnóstico por persona, DIRPOLES-4 permite registrar múltiples diagnósticos por asistente.
4. **Descuento de Insumos de Jornada:** Transacción con `FOR UPDATE` sobre `insumos` (estatus `'Disponible'`, stock > 0). Aplica descuento con `CASE` a `'Agotado'` antes de restar y registra movimiento de `'Salida'` en `inventario_medico`. Máx. 30 insumos por diagnóstico.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Cero sobrecupos en jornadas masivas gracias al bloqueo de aforo pesimista, soporte para diagnósticos múltiples por paciente y registro automático del consumo de medicamentos en el Kardex general.

---

## 5.4 Módulo de Mobiliario y Fichas Técnicas (id_modulo 12)

### A. Arquitectura de Datos y Kardex de Bienes
* **Esquema:** `dirpoles_business`.
* **Tablas:** `mobiliario`, `equipos`, `fichas_tecnicas`, `detalle_ficha_mobiliario`, `detalle_ficha_equipo`, `inventario_mob` (Kardex).
* **FKs:** `fichas_tecnicas.id_empleado` $\rightarrow$ `dirpoles_security.empleado.id_empleado`.

### B. Controlador, Rutas y Permisos RBAC (`mobiliarioController.php`)
* **Nombre RBAC:** `'mobiliario'` (roles 6 Administrador, 10 Superusuario).
* **Pestañas:** Mobiliario Mueble, Equipos Tecnológicos, Fichas Técnicas por Responsable.

### C. Lógica del Modelo y Reglas de Inventario (`MobiliarioModel`)
1. **Regla de Una Ficha Técnica Activa por Responsable:** Un empleado solo puede tener **una única Ficha Técnica activa** como responsable del inventario de su departamento. Debe incluir obligatoriamente al menos un bien mueble o equipo.
2. **Disponibilidad Calculada Dinámicamente en SQL:**
   ```sql
   SELECT m.*, (m.cantidad - COALESCE(SUM(dfm.cantidad), 0)) AS disponible
     FROM mobiliario m
     LEFT JOIN detalle_ficha_mobiliario dfm ON dfm.id_mobiliario = m.id_mobiliario
     LEFT JOIN fichas_tecnicas ft ON ft.id_ficha = dfm.id_ficha AND ft.estatus = 'Activa'
    GROUP BY m.id_mobiliario
   ```
   La disponibilidad nunca muta la columna física `mobiliario.cantidad`. Un equipo tecnológico (`serial` único) jamás puede figurar en dos fichas técnicas activas a la vez.
3. **Validación Remota de Serial Único:** `api/mobiliario/validar_serial` exige serial obligatorio e irrepetible para equipos.
4. **Baja Lógica y Bloqueo de Eliminación:** La baja lógica (`estatus = 'Inactivo'`) está bloqueada si el bien pertenece a una ficha técnica activa. La eliminación física se cancela si existen registros en detalles de ficha o Kardex `inventario_mob`.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
El sistema anterior no registraba el detalle de ítems en fichas técnicas, permitía duplicar seriales en equipos y modificaba directamente el stock físico desincronizando la existencia real.

---

## 5.5 Módulo de Transporte y Flota (id_modulo 13)

### A. Arquitectura de Datos y Kardex de Repuestos
* **Esquema:** `dirpoles_business`.
* **Tablas:** `rutas`, `vehiculos`, `proveedores`, `repuestos_vehiculos`, `inventario_repuestos` (Kardex), `asignaciones_rutas`, `mantenimiento_vehiculos`, `repuestos_mantenimiento`.

### B. Controlador, Rutas y Permisos RBAC (`transporteController.php`)
* **Nombre RBAC:** `'transporte'` (roles 6 Administrador, 10 Superusuario).
* **Sub-flujos:** Flota vehicular, rutas universitarias, proveedores, repuestos, asignaciones y mantenimientos.

### C. Lógica del Modelo, Concurrencia y Corrección `AUTO_INCREMENT` (`TransporteModel`)
1. **Validación de Chofer Exclusivo y No Solapamiento:**
   - Asignaciones de ruta filtran y validan únicamente empleados activos con `tipo_empleado = 'Chofer'` (`id_tipo_emp = 8`).
   - `verificarSolapamientoDia()` impide que un vehículo o chofer posea dos asignaciones activas el **MISMO día** (`fecha_asignacion` y `estatus = 'Activa'`).
2. **Solución del Error de Base de Datos 1364 (`AUTO_INCREMENT`):** La columna `asignaciones_rutas.id_asignacion` heredada del dump viejo carecía de `AUTO_INCREMENT`, produciendo fallos 1364 al insertar. Se corrigió en esquema y con script de migración idempotente `docs/bd/transporte_asignaciones_autoincrement.sql`.
3. **Kardex de Repuestos:** Las entradas (`Compra|Devolución|Donación|Ajuste de inventario`) y salidas (`Uso en mantenimiento|Vencimiento|Daño|Pérdida|Donación`) se ejecutan con `FOR UPDATE` sobre `repuestos_vehiculos` y registran el movimiento en `inventario_repuestos`.
4. **Mantenimiento Vehicular:** Crear mantenimiento cambia el estado del vehículo a `'Mantenimiento'`. Eliminar el mantenimiento solo lo reactiva a `'Activo'` si no le restan mantenimientos pendientes.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Solución a la falla crítica 1364 de clave primaria sin autoincremento, prevención de asignaciones simultáneas de choferes el mismo día y control estricto de repuestos usados en taller.

---

## 5.6 Módulo de Reportes Estadísticos (id_modulo 15)

### A. Arquitectura de Analítica y Lista Blanca Server-Side
* **Esquema:** `dirpoles_business` + `dirpoles_security`.
* **Módulos Reportados:** General, Psicología, Medicina, Orientación, Trabajo Social, Discapacidad, Referencias, Jornadas, Mobiliario, Transporte.

### B. Controlador y Filtrado Server-Side (`reportesController.php`)
* **Nombre RBAC:** `'reportes'` (roles 1, 2, 3, 4, 5, 6, 10, 11).
* **Whitelist Server-Side Estricta (`reportesAplicarFiltros()`):** Acepta exclusivamente la lista cerrada de parámetros: `fecha_inicio`, `fecha_fin`, `genero`, `pnf`, `servicio_destino`, `area`, `estado`, `tipo_consulta`, `submodulo`, `grado`, `tipo_discapacidad`, `tipo_bien`, `tipo_vehiculo`, `reporte`, `limit`. Cualquier parámetro ajeno es descartado silenciosamente.

### C. Lógica del Modelo, Chart.js Local y Consumo Agnostic para IA (`ReportesModel`)
1. **Binding Estricto de Paginación (`PARAM_INT`):** El parámetro `limit` tiene valor por defecto 5000 y tope máximo 20000, vinculado explícitamente como `PDO::PARAM_INT` para proteger la memoria del servidor.
2. **Consumo Agnóstico (Backend-to-Backend para Microservicio IA):** Los endpoints `GET api/reportes/*` responden JSON estricto sin componentes HTML. Esta API desacoplada está diseñada para ser consumida directamente por la interfaz web o por el **Microservicio de Inteligencia Artificial en Python (FastAPI)** para inferencias estadísticas sin requerir scraping ni navegador.
3. **Chart.js Local (v2.9.4):** Los gráficos de los 10 tableros utilizan la librería alojada localmente en `dist/js/dashboard/Chart.min.js` (sin dependencias de CDNs externas).
4. **Auditoría Específica:** Todas las consultas de datos de reportes registran automáticamente la acción **`'Lectura'`** en la Bitácora.

### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Eliminación de colapsos de memoria por consultas masivas sin límite, eliminación de vulnerabilidades por parámetros de ordenamiento no sanitizados y disponibilidad offline de gráficos.

---

## 5.7 Módulos de Soporte y Sistema

### 1. Módulo de Notificaciones Transversales (id_modulo 19)

#### A. Arquitectura de Datos y Streaming de Eventos
* **Esquema:** `dirpoles_security`.
* **Tabla Principal:** `notificaciones` (`id_notificacion`, `id_empleado`, `titulo`, `mensaje`, `tipo`, `url`, `leido`, `fecha_creacion`).
* **Clave Foránea:** `notificaciones.id_empleado` $\rightarrow$ `dirpoles_security.empleado.id_empleado` ON DELETE CASCADE.
* **Mecanismo de Difusión:** Doble canal (Bandeja API persistente + Event-Stream en tiempo real via SSE).

#### B. Controlador, Rutas y Permisos RBAC (`notificacionesController.php` y `sseController.php`)
* **Nombre RBAC:** `'notificaciones'` (id_modulo 19; acceso transversal para todo empleado autenticado).
* **Rutas API Bandeja:** `api/notificaciones/listar`, `api/notificaciones/contar_no_leidas`, `api/notificaciones/marcar_leida`, `api/notificaciones/marcar_todas_leidas`, `api/notificaciones/eliminar`.
* **Ruta SSE Stream:** `sse/notificaciones` (responde la cabecera `Content-Type: text/event-stream` y `Cache-Control: no-cache`).

#### C. Lógica del Modelo, Streaming SSE y Concurrencia (`NotificacionesModel`)
1. **Streaming Asíncrono no Bloqueante con `session_write_close()`:** Al conectar el cliente HTTP al endpoint `sse/notificaciones`, el controlador invoca de inmediato `session_write_close()`. Esto libera el cerrojo del archivo de sesión PHP, permitiendo que el navegador realice otras peticiones HTTP concurrentes (navegar, guardar formularios, consultar APIs) sin quedar bloqueado por la conexión SSE persistente.
2. **Heartbeat y Resiliencia PDO (Error 2006):** El bucle del stream emite un comentario ping (`: ping\n\n`) cada 15 segundos para mantener activo el socket HTTP evitando cortes por timeout de Nginx/Apache. Si la conexión MySQL se cae por inactividad (`PDOException: MySQL server has gone away` / Código 2006), el modelo reconecta automáticamente invocando `Database::reconectar()`.
3. **Despacho Transversal:** Otros módulos de negocio (e.g. Citas al agendar, Referencias al remitir, Jornadas al cancelar) insertan notificaciones invocando `NotificacionesModel::crear()`.

#### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Ausencia total de notificaciones en tiempo real en el sistema heredado, requiriendo *polling* continuo que saturaba el servidor web o recargas manuales de página para enterarse de cambios clínicos o administrativos.

---

### 2. Módulo de Configuración del Sistema y Catálogos (id_modulo 14)

#### A. Arquitectura de Datos y Catálogos Centralizados
* **Esquemas:** `dirpoles_business` y `dirpoles_security`.
* **Tablas Administradas:** `servicios`, `tipo_empleado`, `motivos_salida`, parámetros institucionales de la UPTAEB.

#### B. Controlador, Rutas y Permisos RBAC (`configuracionController.php`)
* **Nombre RBAC:** `'configuraciones'` (roles 6 Administrador, 10 Superusuario).
* **Rutas:** `configuracion/consultar`, `configuracion/crear`, `api/configuracion/catalogos`.

#### C. Lógica del Modelo y Saneamiento (`ConfiguracionesModel`)
1. **Gestión Dinámica de Catálogos:** Permite al administrador crear o modificar opciones de catálogos del sistema (e.g. áreas de servicio, tipos de personal) sin alterar el esquema físico de la BD ni requerir intervenciones de código PHP.
2. **Validación Fail-Fast en `__set()`:** Protege contra nombres de catálogos duplicados o vacíos antes de ejecutar sentencias SQL.
3. **Integridad Referencial:** Bloquea la desactivación de ítems de catálogo si existen registros activos asociados en las tablas operativas de negocio.

#### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Catálogos e identificadores duros en el código (*hardcoded*) que requerían edición de código fuente o scripts SQL manuales ante cambios organizativos en la universidad.

---

### 3. Módulo de Auditoría y Bitácora Transversal (id_modulo 16)

#### A. Arquitectura de Datos Forense
* **Esquema:** `dirpoles_security`.
* **Tabla Principal:** `bitacora` (`id_bitacora`, `id_empleado`, `modulo`, `accion`, `descripcion`, `ip`, `fecha`).
* **FK:** `bitacora.id_empleado` $\rightarrow$ `dirpoles_security.empleado.id_empleado` ON DELETE SET NULL.

#### B. Controlador, Rutas y Permisos RBAC (`bitacoraController.php`)
* **Nombre RBAC:** `'bitacora'` (roles 6 Administrador, 10 Superusuario).
* **Rutas:** `bitacora/consultar`, `api/bitacora/listar`, `api/bitacora/exportar_pdf`, `api/bitacora/exportar_excel`.

#### C. Lógica del Modelo y Lista Cerrada de Acciones (`Bitacora` / `SecurityModel`)
1. **Catálogo Inmutable de Acciones (`Bitacora::ACCIONES`):** Valida estrictamente que toda traza de auditoría pertenezca al conjunto cerrado de operaciones reconocidas:
   `'Crear'`, `'Lectura'`, `'Editar'`, `'Eliminar'`, `'Login'`, `'Logout'`, `'Entrada'`, `'Salida'`, `'Descargar'`, `'Respaldo'`. Cualquier intento de registrar una acción no tipificada lanza una excepción de validación.
2. **Trazabilidad Automática:** Registra el usuario autenticado, la IP real del cliente (obtenida mediante `Request::obtenerIp()`), el módulo afectado y el detalle descriptivo de la operación.
3. **Exportación Forense:** Genera reportes PDF (FPDF) y Excel con filtros por rango de fechas, usuario, módulo y tipo de acción para auditorías institucionales de la UPTAEB.

#### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Acciones registradas como cadenas arbitrarias sin estandarización ("Consulta", "Ver", "Modificó"), imposibilidad de exportar la bitácora y ausencia de captura de la dirección IP de origen.

---

### 4. Módulo de Matriz de Permisos y Control RBAC (id_modulo 17)

#### A. Arquitectura de Datos y Mapeo Tecnológico
* **Esquema:** `dirpoles_security`.
* **Tablas:** `rol_modulo_permiso` (`id_rol_modulo_permiso`, `id_tipo_emp`, `id_modulo`, `crear`, `leer`, `editar`, `eliminar`), `modulo`, `tipo_empleado`.
* **Claves Foráneas:** Unicidad compuesta `(id_tipo_emp, id_modulo)`.

#### B. Controlador, Rutas y Permisos RBAC (`permisosController.php`)
* **Nombre RBAC:** `'permisos'` (roles 6 Administrador, 10 Superusuario).
* **Rutas:** `permisos/consultar`, `api/permisos/matriz`, `api/permisos/guardar`.

#### C. Lógica del Modelo y Verificación Dynamic Session (`PermisosModel`)
1. **Verificación de Permisos Atómicos:** La clase `Autorizacion::verificar($modulo, $accion)` consulta la matriz cargada en la sesión del usuario (`$_SESSION['permisos'][$modulo][$accion]`).
2. **Persistencia Transaccional de Matriz:** La actualización de permisos para un rol ejecuta una transacción ACID que reemplaza los permisos del rol modificado y re-evalúa los módulos permitidos (`$_SESSION['modulosPermitidos']`).
3. **Generación Dinámica de Interfaz:** El menú lateral (sidebar), las tarjetas del dashboard y los botones de acción (crear, editar, eliminar) en las vistas se habilitan u ocultan dinámicamente según la matriz de permisos devuelta por el servidor.

#### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Verificación de permisos basada en estructuras `if ($rol == 1)` diseminadas por el código, impidiendo otorgar permisos específicos a nuevos cargos o modificar privilegios sin alterar el código fuente.

---

### 5. Respaldo de Base de Datos y Tercera Puerta (.sql)

#### A. Arquitectura de Exportación Multi-Esquema
* **Esquemas Abarcados:** `dirpoles_security` y `dirpoles_business`.
* **Mecanismo:** Generación directa de script SQL de volcado (DDL + DML) mediante streaming nativo en PHP.

#### B. Controlador y Autenticación Defensiva (`configuracionController.php::respaldoBD()`)
* **Verificación de Permisos:** `Autorizacion::verificar('configuraciones', 'crear')`. Restringido exclusivamente a Administrador y Superusuario.
* **Ruta:** `configuracion/respaldo_bd` / `api/configuracion/respaldo_bd`.

#### C. Lógica de la Tercera Puerta y Seguridad Operativa
1. **Generación Estructurada DDL/DML:** Extrae la definición de tablas (`SHOW CREATE TABLE`), deshabilita temporalmente la verificación de claves foráneas (`SET FOREIGN_KEY_CHECKS=0;`), y exporta los datos utilizando `INSERT INTO` masivos por lotes.
2. **Streaming por la Tercera Puerta Binaria:** Limpia cualquier contenido previo en el búfer de salida con `ob_clean()` y transmite el contenido SQL con cabeceras estrictas de descarga binaria:
   ```php
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename="dirpoles_backup_' . date('Y-m-d_H-i-s') . '.sql"');
   header('Content-Length: ' . strlen($sqlContent));
   ```
3. **Auditoría Obligatoria:** Cada descarga exitosa de respaldo de base de datos registra inmediatamente una entrada en la Bitácora con la acción **`'Respaldo'`**, registrando el usuario y la IP solicitante.

#### D. Deficiencias y Riesgos del Sistema Viejo Superadas
Dependencia de herramientas externas (phpMyAdmin) para respaldos, ausencia de registros de auditoría ante descargas de la base de datos completa y riesgo de exposición de credenciales o datos sin control RBAC.

---

# CAPÍTULO 6: Guía Maestra de Argumentación Técnica para la Defensa de Grado

La defensa del Proyecto Socio-Integrador y Tecnológico (PSIT) ante el jurado evaluador del Programa Nacional de Formación (PNF) en Informática de la UPTAEB exige no solo la presentación funcional del software, sino una justificación científica, defensiva y arquitectónica inexpugnable. A continuación se compila el banco de las 10 interrogantes técnicas de mayor complejidad que suelen formular los docentes expertos y jurados evaluadores, acompañadas de sus respuestas formales de ingeniería de software.

---

## 6.1 Banco de Preguntas y Respuestas Técnicas de Alta Complejidad

### Pregunta 1: "¿Por qué desarrollaron un MVC propio desde cero en lugar de utilizar un framework comercial como Laravel, Symfony o CodeIgniter?"

**Respuesta Técnica:**
"La decisión de implementar un patrón Modelo-Vista-Controlador (MVC) nativo y ligero en PHP 8+ responde a cuatro imperativos de ingeniería de software:

1. **Correspondencia Académica e Institucional:** El modelo conceptual del proyecto fue modelado conforme a los estándares UML aprobados por la comisión del PNF en Informática. Los frameworks comerciales imponen sus propios paradigmas de arquitectura (como Eloquent ORM en Laravel o Doctrine en Symfony) que distorsionan el Diagrama de Clases académico, creando abstracciones complejas que ocultan la verdadera lógica del negocio.
2. **Eficiencia de Recursos e Infraestructura Físico-Tecnológica:** DIRPOLES opera sobre la infraestructura del centro de datos universitario de la UPTAEB, el cual cuenta con servidores de capacidades moderadas y alta concurrencia en horas pico. Un marco de trabajo como Laravel requiere cargar en memoria más de 300 archivos de soporte, *service providers* y dependencias del contenedor de inyección por cada petición HTTP, elevando el consumo base a ~15-25 MB de RAM por *request*. En contraste, nuestro núcleo modular nativo posee una huella de memoria inferior a **1.5 MB por petición**, ofreciendo una latencia de respuesta $10\times$ menor.
3. **Control Total de Transacciones Criptográficas y de Concurrencia:** Los frameworks convencionales abstraen la gestión de transacciones de base de datos a niveles donde resulta complejo e ineficiente inyectar bloqueos pesimistas nativos a nivel de fila (`SELECT ... FOR UPDATE`), serializar la autenticación RSA-2048 o gestionar el streaming asíncrono SSE sin depender de servicios externos como Redis, Pusher o Node.js.
4. **Mantenibilidad Libre de Obsolescencia Programada:** Las actualizaciones de versión en frameworks comerciales (e.g., Laravel 8 a 10) introducen cambios rompientes (*breaking changes*) y deprecación de paquetes que obligan a refactorizaciones continuas que el personal administrativo de la UPTAEB no podría asumir a largo plazo. DIRPOLES-4 es monolítico, autosuficiente y de mantenimiento predecible sobre cualquier servidor estándar con PHP 8+."

---

### Pregunta 2: "¿Por qué los controladores no se construyeron como clases orientadas a objetos y se implementaron como funciones puras en archivos individuales?"

**Respuesta Técnica:**
"Esta decisión responde a una estricta adhesión al **Principio de Responsabilidad Única (SRP)** y a la optimización de carga en memoria (*Lazy Loading*):

1. **Modelado UML Estandarizado:** En la teoría clásica de ingeniería de software y en el modelado UML formal del sistema, la capa de control (*Control Layer*) actúa como un mediador orquestador entre el enrutador de entrada y la capa de negocio (*Model Layer*). Los controladores no poseen estado propio (*stateless*); no almacenan atributos ni encapsulan variaciones polimórficas. Por ende, instanciar objetos vacíos mediante clases para invocar un solo método operativo genera una sobrecarga inútil de instanciación en el Garbage Collector de PHP.
2. **Desacoplamiento y Carga Perezosa (*Lazy Loading*):** Al estructurar cada acción de control como una función pura contenida en su propio archivo dentro de `app/Controllers/`, el cargador de la aplicación (`load_controller()` en `bootstrap.php`) requiere únicamente el archivo específico demandado por la ruta activa. Esto evita incluir en memoria archivos con múltiples métodos no utilizados, a diferencia de los controladores de clase monolíticos que cargan todas sus dependencias e inyectores independientemente del método ejecutado.
3. **Aislamiento de Errores y Determinismo:** Cada función de controlador recibe los datos procesados por la petición (`Request`), evalúa la autorización (`Autorizacion::verificar()`), delega la operación al modelo correspondiente (`manejarAccion()`) y retorna una respuesta inmutable mediante `Respuesta::json()` o `Respuesta::render()`. Esta naturaleza determinista facilita la auditoría estática de código y elimina los efectos secundarios inherentes al estado mutable de las clases."

---

### Pregunta 3: "¿Por qué implementaron dos bases de datos físicamente separadas (`dirpoles_security` y `dirpoles_business`) en lugar de consolidar todo en una sola base de datos?"

**Respuesta Técnica:**
"La separación en dos esquemas de base de datos aislados atiende a la aplicación rigurosa del **Principio de Mínimo Privilegio (PoLP)** y la **Defensa en Profundidad (Defense in Depth)** impuesta por los estándares de ciberseguridad OWASP:

1. **Aislamiento Criptográfico y de Credenciales:** La base de datos `dirpoles_security` almacena exclusivamente las tablas críticas de autenticación (`empleado`, `rol_modulo_permiso`, `login_intentos`, `refresh_tokens`, `bitacora`, `notificaciones`). La base de datos `dirpoles_business` gestiona los datos de operaciones clínicas, expedientes estudiantiles, inventario, transporte y becas. Al segmentar las bases de datos, prevenimos que una inyección SQL accidental en un módulo de negocio pueda listar el hash Bcrypt de las credenciales de acceso o manipular la matriz de permisos.
2. **Segmentación de Credenciales PDO a Nivel de Servidor:** En entornos de alta seguridad, el motor de base de datos configura usuarios MySQL diferenciados: el usuario web operativo no posee permisos de `ALTER` o `DROP` sobre `dirpoles_security`, impidiendo la escalada de privilegios o la alteración del rastro forense contenido en la tabla `bitacora`.
3. **Independencia de Respaldo y Auditoría Forense:** Facilita la extracción de volcados de respaldo independientes; los datos de auditoría e historial de notificaciones pueden conservarse en medios de almacenamiento primarios de solo lectura sin interferir con las operaciones diarias de almacenamiento clínico."

---

### Pregunta 4: "¿Cómo garantizan formalmente que dos usuarios no consuman el último insumo médico o sobrepasen el aforo máximo de una jornada masiva si hacen clic exactamente al mismo tiempo?"

**Respuesta Técnica:**
"El sistema garantiza la consistencia de datos y previene condiciones de carrera (*Race Conditions*) mediante **Bloqueo Pesimista Nivel de Fila (Pessimistic Locking)** serializado sobre transacciones ACID puras de MySQL (InnoDB):

1. **Mecanismo de Insumos Médicos:** Cuando un profesional médico descuenta un insumo en una consulta (Módulo 5) o jornada (Módulo 11), el modelo no ejecuta un `UPDATE` ciego. Abre una transacción explícita (`$db->beginTransaction()`) y ejecuta:
   ```sql
   SELECT cantidad, estatus, fecha_vencimiento 
     FROM insumos 
    WHERE id_insumo = :id 
      FOR UPDATE;
   ```
   La cláusula `FOR UPDATE` le ordena a InnoDB bloquear exclusivamente esa fila para cualquier otra transacción concurrente. Si una segunda petición entra al mismo milisegundo, la base de datos la coloca en cola de espera hasta que la primera transacción ejecute `COMMIT` o `ROLLBACK`. Luego, la segunda transacción lee el valor real actualizado (e.g. `cantidad = 0`), lo que permite al backend rechazar la operación devolviendo una `ExcepcionApi` por stock agotado.
2. **Reordenamiento Defensivo del `UPDATE`:** El script SQL de descuento reordena las cláusulas `SET` para evitar que la evaluación del estado evalúe valores obsoletos:
   ```sql
   UPDATE insumos 
      SET estatus = CASE WHEN (cantidad - :c) <= 0 THEN 'Agotado' ELSE estatus END,
          cantidad = cantidad - :c 
    WHERE id_insumo = :id;
   ```
   MySQL evalúa las expresiones del `SET` de arriba hacia abajo sobre la misma instrucción; al evaluar el `CASE` antes de la resta, se asegura de validar el stock disponible remanente exacto."

---

### Pregunta 5: "¿Por qué implementan Tokens JWT (JSON Web Tokens) firmados con RS256 si la aplicación utiliza renderizado en servidor (SSR)?"

**Respuesta Técnica:**
"DIRPOLES-4 no es un monolito clásico puramente SSR; implementa una **Arquitectura Híbrida de Dos Puertas**:

1. **Desacoplamiento de Interfaces y Renderizado Dual:** Mientras que la navegación entre páginas generales utiliza la Puerta HTML (SSR para vistas rápidas), todas las interacciones ricas de los módulos (tablas DataTables, modales de edición, autocompletado en vivo, offcanvas de 5 pasos del Estudio Socioeconómico y gráficos de reportes) consumen exclusivamente la **Puerta JSON (API RESTful)** mediante llamadas asíncronas `fetch()`.
2. **Autenticación Desacoplada y Firma Asimétrica RS256:** El uso de JWT firmado digitalmente con clave privada RSA-2048 en servidor (`app/Config/Keys/jwt_private.pem`) permite autenticar cada petición AJAX de la Puerta JSON de forma apátrida (*stateless*) y segura. La firma asimétrica garantiza que el payload del token no pueda ser falsificado ni tamperizado por el cliente.
3. **Consumo Agnóstico por Servicios Externos:** Esta decisión de arquitectura prepara a DIRPOLES-4 para ser consumido de forma agnóstica por otros componentes del ecosistema universitario, como el **Microservicio de Inteligencia Artificial en Python (FastAPI)** para analítica predictiva de salud y deserción, el cual valida la validez del token JWT utilizando la clave pública RSA sin necesidad de consultar la base de datos de sesiones de PHP."

---

### Pregunta 6: "¿Por qué almacenan los Refresh Tokens utilizando un hash SHA-256 en lugar de algoritmos adaptativos como Bcrypt?"

**Respuesta Técnica:**
"Esta elección responde a la diferencia fundamental de entropía entre las contraseñas ingresadas por humanos y las credenciales aleatorias generadas por la máquina, combinada con la necesidad de optimización de rendimiento:

1. **Análisis de Entropía:** Las contraseñas humanas poseen una entropía baja (palabras comunes, fechas, patrones), lo que las hace vulnerables a ataques de fuerza bruta y diccionarios offline. Algoritmos como Bcrypt introducen un *cost factor* elevado (factor de costo 10 = ~80-100 ms por hash) para ralentizar intencionadamente dichos ataques.
2. **Entropía Máxima de Tokens Aleatorios:** El Refresh Token de DIRPOLES-4 es una cadena criptográficamente segura de 512 bits generada con `random_bytes(64)`. La probabilidad de adivinar mediante fuerza bruta una cadena de 512 bits de entropía completa es matemáticamente nula ($2^{512}$ combinaciones). Por lo tanto, no se requiere un costo computacional de ralentización (*key stretching*).
3. **Mitigación de Denegación de Servicio (DoS) y Eficiencia:** Computar Bcrypt en cada renovación de token de acceso expone al servidor a ataques de DoS por agotamiento de CPU. La función Hash SHA-256 es ultra-rápida (menos de 0.01 ms) y proporciona un almacenamiento unidireccional (*one-way digest*). Si un atacante sustrae un respaldo de la base de datos `dirpoles_security`, solo obtendrá los digests SHA-256, los cuales resultan inútiles para suplantar sesiones ya que es imposible revertir la función hash para obtener el Refresh Token original."

---

### Pregunta 7: "¿Por qué no utilizaron una arquitectura SPA (Single Page Application) basada en frameworks como React, Angular o Vue.js?"

**Respuesta Técnica:**
"La elección arquitectónica descartó el patrón SPA en favor del Monolito Modular Nativo por tres factores determinantes:

1. **Heterogeneidad de Hardware Cliente:** La red informática institucional de la UPTAEB cuenta con equipos de computación con especificaciones de hardware modestas o desactualizadas en departamentos administrativos y laboratorios. Las SPAs delegan la construcción del DOM y el procesamiento de scripts pesados de JavaScript al navegador del cliente, lo que genera cuellos de botella de rendimiento, congelamiento de pestañas y alto consumo de memoria RAM en el navegador.
2. **Simplicidad de Despliegue y Superficie de Ataque:** Una SPA requiere entornos de construcción Node.js, empaquetadores (Webpack/Vite), hidratación del estado y manejo de rutas en cliente que introducen cientos de dependencias de terceros (`npm packages`) propensas a vulnerabilidades en la cadena de suministro (*supply-chain attacks*). DIRPOLES-4 se despliega mediante una copia directa de archivos sobre cualquier servidor web PHP nativo sin compilaciones intermedias.
3. **Renderizado Inicial Inmediato (SSR):** El renderizado en servidor entrega el HTML listo para visualizar, garantizando tiempos de carga de primer despliegue (*First Contentful Paint*) inferiores a 200 ms, ofreciendo una experiencia fluida al usuario final independientemente de la capacidad de su equipo."

---

### Pregunta 8: "¿Cómo evitan que un médico o psicólogo malintencionado altere el expediente clínico o la cita de un estudiante atendido por otro especialista?"

**Respuesta Técnica:**
"El sistema previene los ataques de **BOLA / IDOR (Broken Object Level Authorization / Insecure Direct Object References)** mediante la combinación del control RBAC y métodos de **Autorización a Nivel de Fila (*Row-Level Authorization*)** en la capa del Modelo:

1. **Falla del RBAC Convencional:** El control RBAC tradicional (`Autorizacion::verificar('medicina', 'editar')`) valida si el usuario posee el permiso genérico de editar registros de medicina, pero no puede determinar si es el dueño o responsable específico de la fila con `id_diagnostico = 45`.
2. **Implementación de `asegurarAlcance()`:** En los modelos operativos de salud y consultas, cada método de modificación o lectura individual invoca un mecanismo de verificación de alcance:
   ```php
   protected function asegurarAlcance(int $idRegistro, int $idEmpleadoSesion): void {
       if ($_SESSION['id_tipo_emp'] === self::ROL_ADMIN) {
           return; // El Administrador posee alcance global
       }
       $registro = $this->obtenerPorId($idRegistro);
       if ((int)$registro['id_empleado'] !== $idEmpleadoSesion) {
           throw ExcepcionApi::prohibido("No posee privilegios para modificar un registro expedido por otro especialista.");
       }
   }
   ```
3. **Validación en Servidor:** El parámetro `id_empleado` recibido desde peticiones HTTP es ignorado o sobreescrito de forma forzada por `$_SESSION['id_empleado']`, garantizando que la autorización a nivel de objeto sea evaluada de forma inmutable en el backend."

---

### Pregunta 9: "¿Por qué no utilizaron un ORM (Object-Relational Mapper) como Eloquent o Doctrine para interactuar con la base de datos?"

**Respuesta Técnica:**
"Los ORMs agregan una capa de abstracción inconveniente para los requerimientos específicos de DIRPOLES-4:

1. **Problema de Consultas $N+1$:** Los ORMs aplican estrategias de carga perezosa (*Lazy Loading*) que generan cientos de peticiones SQL adicionales no deseadas al iterar listados o DataTables complejos (e.g. listar 100 citas requiriendo datos del beneficiario, empleado, servicio y estado). DIRPOLES-4 utiliza sentencias SQL optimizadas con `LEFT JOIN` e `INNER JOIN` explícitos ejecutadas en una sola consulta.
2. **Incapacidad de Expresar Consultas Analíticas Complejas:** Los 10 tableros del Módulo de Reportes (Módulo 15) requieren agregaciones avanzadas, funciones de ventana, `UNION ALL` cross-schema entre `dirpoles_security` y `dirpoles_business`, y filtros dinámicos. Representar estas consultas en sintaxis de ORM genera código ilegible, ineficiente y difícil de depurar.
3. **Control Cero en Instrucciones Criptográficas y Lockings:** Operaciones transaccionales críticas que exigen `FOR UPDATE`, `CASE` reordenados o funciones de auditoría requieren SQL nativo. El uso del componente `Database` (basado en PDO nativo) nos otorga un **control milimétrico sobre la sentencia SQL**, la memoria asignada y el tipado estricto de parámetros."

---

### Pregunta 10: "¿Qué medidas técnicas integrales adoptaron para garantizar inmunidad contra Inyecciones SQL y ataques de Scripting Entre Sitios (XSS)?"

**Respuesta Técnica:**
"El marco de seguridad defensiva de DIRPOLES-4 aplica las recomendaciones OWASP ASVS mediante dos barreras infranqueables:

1. **Mitigación Total de Inyección SQL (Prepared Statements + Tipado PDO):**
   - **Prohibición de Concatenación:** Está estrictamente prohibida la concatenación de variables en cadenas SQL (`"WHERE id = " . $id`).
   - **Sentencias Preparadas con Binding Explícito:** Toda interacción con la BD se realiza mediante marcadores de parámetros (`:param`) vinculados con tipos de datos explícitos PDO:
     ```php
     $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
     $stmt->bindValue(':busqueda', '%' . $this->busqueda . '%', PDO::PARAM_STR);
     ```
   - **Paginación Segura:** Parámetros numéricos delicados como `LIMIT` u `OFFSET` se castean a `(int)` y se bindean explícitamente como `PDO::PARAM_INT`, previniendo inyecciones en subcláusulas SQL.

2. **Mitigación de Scripting Entre Sitios - XSS (Sanitización y Escape Contextual):**
   - **Sanitización de Entrada (Fail-Fast en Modelo):** La clase base `BusinessModel` y `SecurityModel` sanitizan todo atributo asignado a través de `__set()` mediante saneamiento de texto (`texto()`), eliminando etiquetas HTML peligrosas y caracteres de control mediante `strip_tags()` y trim de espacios.
   - **Escape Contextual de Salida:** Toda variable renderizada en las vistas HTML se procesa mediante la función helper `escapeHTML()` (encapsulamiento de `htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`), convirtiendo caracteres especiales (`<`, `>`, `"`, `'`, `&`) en entidades HTML inofensivas.
   - **Seguridad en Frontend (Vanilla JS):** Los scripts modales y DataTables insertan datos procesados dinámicamente mediante `.textContent` o la función utilitaria `escapeHTML()` de `apiFetch.js`, previniendo la ejecución de scripts arbitrarios en el DOM del navegador."

---

# CONCLUSIONES Y SECCIONES COMPLEMENTARIAS

## Glosario Técnico de Términos de Ingeniería de Software

* **ACID (Atomicity, Consistency, Isolation, Durability):** Conjunto de cuatro propiedades que garantizan que las transacciones de una base de datos procesen de manera confiable las operaciones del sistema.
* **BOLA / IDOR (Broken Object Level Authorization / Insecure Direct Object References):** Vulnerabilidad de seguridad donde un sistema expone una referencia a un objeto interno del sistema sin realizar una verificación de autorización a nivel de objeto o fila.
* **CSRF (Cross-Site Request Forgery):** Ataque malicioso donde un usuario autenticado ejecuta acciones no deseadas en una aplicación web en la que se encuentra autenticado.
* **Front Controller:** Patrón de diseño donde un solo canalizador o controlador centralizado maneja todas las peticiones HTTP de entrada a la aplicación web.
* **JWT (JSON Web Token):** Estándar abierto (RFC 7519) que define un modo compacto y autónomo para transmitir información de forma segura entre partes como un objeto JSON firmado criptográficamente.
* **Kardex:** Sistema de registro contable e inventariado para auditar secuencialmente las entradas, salidas y existencias físicas de bienes e insumos.
* **Lazy Loading (Carga Perezosa):** Patrón de diseño que difiere la inicialización o carga de un recurso o archivo hasta el momento preciso en que es requerido.
* **Monolito Modular:** Arquitectura donde el código del sistema se organiza internamente en módulos funcionales bien delimitados, pero se despliega y ejecuta dentro de una única unidad de aplicación.
* **OWASP (Open Web Application Security Project):** Fundación internacional dedicada a mejorar la seguridad del software a través de proyectos de código abierto y estándares de ciberseguridad.
* **Pessimistic Locking (Bloqueo Pesimista):** Estrategia de concurrencia que asume la posibilidad de conflictos e impide que otros usuarios modifiquen un registro mediante bloqueos de base de datos (`FOR UPDATE`) mientras la transacción actual esté abierta.
* **RBAC (Role-Based Access Control):** Mecanismo de restricción de acceso a recursos del sistema basado en los roles y permisos asignados a usuarios individuales.
* **RS256 (RSA Signature with SHA-256):** Algoritmo de firma digital asimétrica que utiliza un par de llaves criptográficas (pública y privada) para firmar y verificar tokens de acceso de forma segura.
* **SPA (Single Page Application):** Aplicación web que interactúa con el usuario redescribiendo dinámicamente la página actual en lugar de cargar páginas enteras desde un servidor.
* **SSE (Server-Sent Events):** Estándar de comunicación unidireccional que permite a un servidor enviar eventos y datos a los clientes web a través de una conexión HTTP persistente en tiempo real.
* **SSR (Server-Side Rendering):** Técnica de desarrollo web donde las páginas HTML se generan dinámicamente en el servidor en respuesta a cada petición del navegador.

---

## Referencias a Estándares Internacionales y Marco Regulatorio

1. **ISO/IEC 25010:2011 — Systems and software engineering — Systems and software Quality Requirements and Evaluation (SQuaRE) — System and software quality models.**
   * *Aplicación en DIRPOLES-4:* Utilizado como marco fundamental para evaluar los atributos de calidad de software: mantenibilidad, rendimiento, seguridad, fiabilidad y portabilidad.
2. **OWASP Application Security Verification Standard (ASVS) Version 4.0.3.**
   * *Aplicación en DIRPOLES-4:* Implementado para la verificación de controles de seguridad en autenticación (V2), control de acceso (V4), validación y sanitización de entradas (V5), criptografía (V6) y arquitectura de base de datos (V14).
3. **RFC 7519 — JSON Web Token (JWT). Internet Engineering Task Force (IETF), 2015.**
   * *Aplicación en DIRPOLES-4:* Estándar aplicado strictly en el diseño, expiración y firma asimétrica RS256 de los tokens de acceso del sistema.
4. **RFC 6749 — The OAuth 2.0 Authorization Framework. IETF, 2012.**
   * *Aplicación en DIRPOLES-4:* Referencia conceptual para el ciclo de vida, rotación de un solo uso (*one-time use*) y revocación de Refresh Tokens.
5. **NIST Special Publication 800-63B — Digital Identity Guidelines: Authentication and Lifecycle Management.**
   * *Aplicación en DIRPOLES-4:* Guía aplicada en el almacenamiento seguro de credenciales (hash Bcrypt con salt automático) y protección contra ataques de fuerza bruta.
6. **Ley Especial contra los Delitos Informáticos (Gaceta Oficial N° 38.270 de Venezuela, 2005).**
   * *Aplicación en DIRPOLES-4:* Cumplimiento estricto del marco legal nacional sobre confidencialidad de datos personales, acceso indebido y preservación de la bitácora forense de auditoría de sistemas públicos.

