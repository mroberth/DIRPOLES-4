# DIRPOLES-4

### Sistema de Gestión Administrativa para la Dirección de Políticas Estudiantiles
**Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB)**

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-8%20%2F%2010.4%2B-4479A1?logo=mysql&logoColor=white)](https://mariadb.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer&logoColor=white)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/Licencia-Institucional%20%2F%20Acad%C3%A9mica-blue)](#-licencia-y-propiedad-intelectual)

**DIRPOLES-4** es el **Sistema de Gestión Administrativa para la Dirección de Políticas Estudiantiles (DIRPOLES)** de la Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB). Desarrollado como un **monolito modular híbrido en PHP 8+** con **MVC propio** (sin frameworks comerciales), sirve páginas HTML renderizadas en el servidor (Puerta HTML) y una API RESTful JSON estricta (Puerta JSON) desacoplada para consumo web y preparado para servicios de Inteligencia Artificial (FastAPI).

Este repositorio constituye la versión final refactorizada, segura e inmutable del sistema, abarcando la automatización integral de **18 módulos funcionales, clínicos, logísticos y administrativos**.

> **Proyecto Socio-Integrador y Tecnológico (PSIT)** — Trabajo Especial de Grado para la obtención del título de **Ingeniero(a) en Informática** (Trayecto IV, PNF Informática, UPTAEB).

---

## 📖 Tabla de Contenido

- [✨ Características Principales](#-características-principales)
- [🧱 Stack Tecnológico](#-stack-tecnológico)
- [🏛 Arquitectura del Sistema](#-arquitectura-del-sistema)
- [📋 Requisitos de Infraestructura](#-requisitos-de-infraestructura)
- [🚀 Instalación y Despliegue](#-instalación-y-despliegue)
  - [Opción A — Script Automatizado (Linux)](#opción-a--script-automatizado-linux)
  - [Opción B — Instalación Manual](#opción-b--instalación-manual)
- [🔑 Gestión de Credenciales Iniciales](#-gestión-de-credenciales-iniciales)
- [✅ Verificación del Despliegue](#-verificación-del-despliegue)
- [⚙ Variables de Entorno (.env)](#-variables-de-entorno-env)
- [🗂 Estructura del Código Fuente](#-estructura-del-código-fuente)
- [🧩 Módulos del Sistema (18/18 Operativos)](#-módulos-del-sistema-1818-operativos)
- [🔐 Marco de Seguridad Defensiva](#-marco-de-seguridad-defensiva)
- [📚 Documentación Técnica](#-documentación-técnica)
- [🛠 Solución de Problemas Frecuentes](#-solución-de-problemas-frecuentes)
- [📄 Licencia y Propiedad Intelectual](#-licencia-y-propiedad-intelectual)

---

## ✨ Características Principales

- **Criptografía Asimétrica y Autenticación Defensiva:**
  - Cifrado en tránsito asimétrico con **RSA-2048 (PKCS#1)** en cliente → descifrado seguro en servidor → almacenamiento con hash **Bcrypt (cost factor 10)**.
  - **Tokens JWT RS256** en cookie `HttpOnly` + **Refresh Tokens con rotación de un solo uso** (*one-time use*) con digest SHA-256 en base de datos.
  - **Protección Anti-Fuerza Bruta Persistente** en BD (`login_intentos`) con bloqueo automático a los 3 intentos fallidos.
- **Doble Base de Datos Aislada:**
  - `dirpoles_security`: Autenticación, matriz RBAC, auditoría forense (`bitacora`), tokens y notificaciones.
  - `dirpoles_business`: Expedientes clínicos, beneficiarios, inventario médico, jornadas, transporte y becas.
- **Concurrencia e Integridad Transaccional:**
  - Bloqueo pesimista nivel de fila (`SELECT ... FOR UPDATE`) en descuento de insumos médicos y control de aforo masivo en jornadas.
  - Kardex de movimientos transaccionales en tiempo real para inventario médico, mobiliario y flota de transporte.
- **Autorización Atómica RBAC:** Matriz dinámica (Roles × Módulos × Permisos: `crear`, `leer`, `editar`, `eliminar`).
- **Streaming SSE:** Canal bidireccional asíncrono (*Server-Sent Events*) para la campana de notificaciones del topbar sin bloqueo de sesión (`session_write_close()`).
- **Analítica y Reportes:** 10 tableros estadísticos dinámicos con filtrado server-side en whitelist, gráficos Chart.js v2.9.4 local y exportación a PDF y Excel.

---

## 🧱 Stack Tecnológico

| Capa / Componente | Tecnología / Estándar |
|---|---|
| **Lenguaje Backend** | **PHP 8.1+** (MVC propio modular nativo, sin frameworks comerciales) |
| **Bases de Datos** | **MySQL 8.0+ / MariaDB 10.4+** (Dos esquemas aislados: `dirpoles_security` y `dirpoles_business`) |
| **Servidor Web** | **Apache 2.4+** con módulo `mod_rewrite` activo |
| **Dependencias PHP** | [Composer 2.x](https://getcomposer.org/) · `firebase/php-jwt` · `vlucas/phpdotenv` |
| **Frontend & UI** | **HTML5 + Vanilla JS Modular + Bootstrap 5** (SB Admin 2) |
| **Librerías Frontend Local** | DataTables, Select2, SweetAlert2, Driver.js, FullCalendar, Chart.js v2.9.4 local |
| **Seguridad / Criptografía** | RSA-2048 (OpenSSL) · Bcrypt · JWT RS256 · SHA-256 |

---

## 🏛 Arquitectura del Sistema

DIRPOLES-4 opera bajo el patrón **Monolito Modular con Front Controller Híbrido (Regla de las Dos Puertas)**:

```
Navegador ──▶ .htaccess ──▶ index.php (Front Controller)
                                   │
                                   ├─ RateLimitMiddleware   (Token Bucket por IP/Endpoint)
                                   ├─ SessionAuthMiddleware (Sesión PHP + Validación JWT RS256)
                                   └─ Router ──▶ Controlador Funcional ──▶ Modelo (SQL PDO)
                                                                 └──▶ Respuesta (JSON / HTML)
```

### Regla de las Puertas y Canales

| Puerta / Canal | Identificador | Tipo de Respuesta | Propósito |
|---|---|---|---|
| **Puerta HTML** | Rutas estándar (ej: `medicina/consultar`) | Renderizado HTML Server-Side (SSR) | Navegación entre vistas y plantillas del sistema. |
| **Puerta JSON** | Rutas bajo `api/` (ej: `api/medicina/listar`) | JSON con Contrato Único (`{exito: bool, datos/error}`) | Operaciones asíncronas AJAX, DataTables y API. |
| **Streaming SSE** | `sse/notificaciones` | `text/event-stream` | Eventos y alertas en tiempo real en la barra superior. |
| **Tercera Puerta** | Endpoints binarios (ej: `respaldo/descargar`) | Stream `application/octet-stream` o PDF | Descarga de respaldos `.sql` e informes FPDF. |

---

## 📋 Requisitos de Infraestructura

| Requisito | Versión Mínima | Extensión / Módulo Requerido |
|---|---|---|
| **Servidor PHP** | 8.1 o superior | `openssl`, `pdo_mysql`, `mbstring`, `fileinfo`, `json` |
| **Motor MySQL / MariaDB** | 8.0+ / 10.4+ | Motor InnoDB con soporte de transacciones ACID y `FOR UPDATE` |
| **Servidor Web** | Apache 2.4+ | `mod_rewrite` e `AllowOverride All` habilitados |
| **Gestor de Paquetes** | Composer 2.x | Para autoloader PSR-4 y librerías base |
| **Herramientas de Red** | OpenSSL CLI | Para generación de pares de claves RSA |

---

## 🚀 Instalación y Despliegue

### Opción A — Script Automatizado (Linux)

El repositorio incluye el script ejecutable `setup_linux.sh` para entorno Linux (Debian, Ubuntu, Linux Mint):

```bash
git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
cd DIRPOLES-4
chmod +x setup_linux.sh
./setup_linux.sh
```

El script ejecuta automáticamente:
1. Creación del archivo de configuración Apache Alias (`/DIRPOLES-4`).
2. Generación del archivo `.env` a partir de `.env.example`.
3. Generación criptográfica de las llaves RSA de 2048 bits para Login y JWT en `app/Config/Keys/` con permisos `600`.
4. Creación de directorios de almacenamiento (`logs/`, `uploads/`) con permisos requeridos.
5. Instalación de dependencias de PHP vía Composer.
6. Importación automática de los dos esquemas SQL desde `docs/bd/`.
7. Reinicio del servicio Apache.

Acceso inmediato en: `http://localhost/DIRPOLES-4`

---

### Opción B — Instalación Manual Paso a Paso

#### 1. Clonar el repositorio
```bash
git clone https://github.com/mroberth/DIRPOLES-4.git DIRPOLES-4
cd DIRPOLES-4
```

#### 2. Configurar variables de entorno
```bash
cp .env.example .env
```
Ajusta credenciales de base de datos en `.env`.

#### 3. Instalar dependencias PHP
```bash
composer install
```

#### 4. Generar Llaves Criptográficas RSA
```bash
mkdir -p app/Config/Keys

# Llaves RSA para cifrado de login en cliente
openssl genrsa -out app/Config/Keys/login_private.pem 2048
openssl rsa -in app/Config/Keys/login_private.pem -pubout -out app/Config/Keys/login_public.pem

# Llaves RSA para firma asimétrica de JWT (RS256)
openssl genrsa -out app/Config/Keys/jwt_private.pem 2048
openssl rsa -in app/Config/Keys/jwt_private.pem -pubout -out app/Config/Keys/jwt_public.pem

# Permisos strictly de lectura por el servidor
chmod 600 app/Config/Keys/*_private.pem
chmod 644 app/Config/Keys/*_public.pem
```

#### 5. Importar Bases de Datos
```bash
mysql -u root -p < docs/bd/dirpoles_security.sql
mysql -u root -p < docs/bd/dirpoles_business.sql
```

#### 6. Permisos de Directorios
```bash
mkdir -p logs uploads
chmod 777 logs uploads
```

---

## 🔑 Gestión de Credenciales Iniciales

El esquema inicial incluye usuarios administrativos pre-configurados. Las contraseñas están resguardadas con hash **Bcrypt**. Para establecer una contraseña conocida para pruebas o administración:

```bash
# 1) Genera el hash Bcrypt de tu nueva contraseña desde la terminal:
php -r "echo password_hash('TuClaveSegura123!', PASSWORD_BCRYPT), PHP_EOL;"
```

Actualiza el hash en la base de datos de seguridad:

```sql
UPDATE dirpoles_security.empleado 
   SET clave = 'HASH_GENERADO_AQUI', estatus = 1 
 WHERE correo = 'admin@gmail.com';
```

---

## ✅ Verificación del Despliegue

1. Abre `http://localhost/DIRPOLES-4/login` en el navegador.
2. Inicia sesión con las credenciales configuradas.
3. Deberías visualizar el **Panel de Inicio** con las tarjetas estadísticas del módulo y el calendario operativo personal.

---

## ⚙ Variables de Entorno (.env)

| Variable | Descripción | Ejemplo |
|---|---|---|
| `APP_DEBUG` | Muestra detalles de depuración (`true` desarrollo) | `false` |
| `DB_HOST` | Host del motor de base de datos MySQL | `localhost` |
| `DB_NAME` | Nombre del esquema de **negocio** | `dirpoles_business` |
| `DB_USER` / `DB_PASS` | Credenciales de la BD de negocio | `root` / *(vacío)* |
| `DB_SECURITY_NAME` | Nombre del esquema de **seguridad** | `dirpoles_security` |
| `DB_SECURITY_USER` / `DB_SECURITY_PASS` | Credenciales de la BD de seguridad | `root` / *(vacío)* |
| `JWT_EXPIRATION` | Expiración del token JWT en segundos | `3600` (1 h) |
| `REFRESH_EXPIRATION` | Expiración del Refresh Token en segundos | `1296000` (15 días) |
| `APP_ENV` | Entorno de ejecución | `local` \| `staging` \| `production` |
| `CORS_ALLOWED_ORIGINS` | Dominio(s) permitidos en CORS | `http://localhost:5173` |

---

## 🗂 Estructura del Código Fuente

```
DIRPOLES-4/
├── index.php                  Front Controller (CORS → sesión → handler → Router)
├── .htaccess                  Redirección centralizada a index.php
├── .env / .env.example        Configuración de entorno
├── composer.json              Autoload PSR-4 + dependencias
├── setup_linux.sh             Script de instalación automatizado (Linux)
│
├── app/
│   ├── Core/                  Infraestructura: Router, Database, Respuesta,
│   │                          ExcepcionApi, ErrorCodes, Autorizacion,
│   │                          Bitacora, Notificador, JwtHandler
│   ├── Middlewares/           RateLimitMiddleware, SessionAuthMiddleware
│   ├── Controllers/           Controladores funcionales (rutas web y APIs)
│   ├── Models/                Modelos de negocio y seguridad (con manejarAccion)
│   ├── Views/                 Plantillas HTML renderizadas en servidor (SSR)
│   ├── routes/                Unidades de enrutamiento por módulo
│   ├── Config/                Catálogos, tarjetas dashboard, permisos RBAC
│   │                          Keys/ (Llaves RSA no versionadas)
│   ├── bootstrap.php          Inicializador de la aplicación
│   └── routes.php             Rutas principales y middlewares globales
│
├── dist/
│   ├── css/                   Estilos CSS Vanilla y temas custom
│   └── js/
│       ├── core/              apiFetch, AlertManager, logout, select2-init...
│       └── modulos/           Lógica cliente por módulo (tours, stats, CRUD)
│
├── plugins/                   Librerías frontend auto-hospedadas (Bootstrap,
│                              DataTables, Select2, SweetAlert2, FullCalendar...)
│
├── docs/
│   ├── MANUAL_TECNICO_DIRPOLES4.md Manual Técnico Maestro (Capítulos 1 al 6)
│   ├── bd/                    Esquemas SQL completos (security y business)
│   └── auditoria/             Informes de auditoría de seguridad
│
├── logs/                      Registros de errores php_errors.log (no versionado)
└── vendor/                    Dependencias Composer (no versionado)
```

---

## 🧩 Módulos del Sistema (18/18 Operativos)

| Módulo | ID | Estado | Descripción Funcional |
|---|:---:|:---:|---|
| **Gestionar Empleados** | 1 | ✅ | Administra personal médico, docente, técnico y administrativo, roles y accesos. |
| **Gestionar Beneficiarios** | 2 | ✅ | Expedientes de estudiantes de la UPTAEB por PNF, trayecto, sección y contacto. |
| **Gestionar Citas** | 3 | ✅ | Agenda de Psicología y atención clínica contra horarios de disponibilidad. |
| **Diagnósticos de Psicología** | 4 | ✅ | Evaluaciones clínicas, seguimiento psicológico y constancias en PDF. |
| **Diagnósticos de Medicina** | 5 | ✅ | Consultas médicas primarias y descuento automático de medicamentos con `FOR UPDATE`. |
| **Diagnósticos de Orientación** | 6 | ✅ | Asistencia socioeducativa, vocacional, motivos y recomendaciones institucionales. |
| **Diagnósticos de Trabajo Social**| 7 | ✅ | Hub de 4 sub-flujos (becas, exoneraciones, FAMES, embarazadas) + Estudio Socioeconómico FPDF. |
| **Diagnósticos de Discapacidad** | 8 | ✅ | Expediente de atención a personas con diversidad funcional y habilidades. |
| **Gestionar Inventario Médico** | 9 | ✅ | Kardex transaccional de medicamentos, entradas, salidas justificadas y vencimiento. |
| **Gestionar Referencias** | 10 | ✅ | Remisión interdepartamental entre áreas de salud con historial de transiciones. |
| **Gestionar Jornadas Médicas** | 11 | ✅ | Eventos masivos de salud, control de aforo pesimista `FOR UPDATE` y consumo de insumos. |
| **Gestionar Mobiliario y Equipos**| 12 | ✅ | Hub de 3 pestañas (bienes muebles, equipos con serial único y Fichas Técnicas responsables). |
| **Gestionar Transporte** | 13 | ✅ | Control de flota vehicular, rutas universitarias, choferes (`id_tipo_emp=8`) y repuestos. |
| **Configuraciones del Sistema** | 14 | ✅ | Administración centralizada de catálogos y parámetros globales. |
| **Reportes Estadísticos** | 15 | ✅ | 10 tableros estadísticos con filtrado server-side, Chart.js local v2.9.4 y consumo IA. |
| **Auditoría / Bitácora** | 16 | ✅ | Registro inmutable de trazabilidad forense por IP, usuario y catálogo de acciones. |
| **Matriz de Permisos (RBAC)** | 17 | ✅ | Matriz dinámica de permisos atómicos (`crear`, `leer`, `editar`, `eliminar`) por rol. |
| **Gestionar Horarios** | 18 | ✅ | Administración exclusiva de turnos y agendas por especialista de salud. |

---

## 🔐 Marco de Seguridad Defensiva

DIRPOLES-4 cumple estrictamente con el estándar **OWASP ASVS v4.0.3**:

- **Cifrado Híbrido en Tránsito y Reposo:** RSA-2048 en navegador + Bcrypt en servidor.
- **Inmunidad a Inyecciones SQL:** 100% de consultas preparadas PDO con tipado explícito (`PARAM_INT`, `PARAM_STR`).
- **Inmunidad a XSS:** Sanitización fail-fast en modelo (`__set()`) + escape contextual en vistas (`htmlspecialchars()`).
- **Seguridad en Cookies y JWT:** Firma RS256, cookies `HttpOnly`, `SameSite=Lax` y rotación de Refresh Token.
- **Protección contra BOLA / IDOR:** Métodos de autorización a nivel de fila (`asegurarAlcance()`) en controladores y modelos.

---

## 📚 Documentación Técnica

Toda la arquitectura, especificaciones de base de datos, lógica de los 18 módulos y argumentos para la defensa de grado se encuentran detallados en:

* **[Manual Técnico Maestro de DIRPOLES-4](docs/MANUAL_TECNICO_DIRPOLES4.md):** Documento oficial consolidado (Capítulos 1 al 6, Glosario y Estándares ISO/OWASP).
* **[Guía de Construcción de Módulos](GUIA-MODULOS.md):** Guía paso a paso para la incorporación de nuevas funcionalidades.
* **[Reglas de Desarrollo para IA / Agentes](AGENTS.md):** Estándares y convenciones de código del repositorio.

---

## 📄 Licencia y Propiedad Intelectual

**Licencia Institucional / Académica**

El sistema **DIRPOLES-4** es un desarrollo tecnológico y académico original diseñado para la **Dirección de Políticas Estudiantiles (DIRPOLES)** de la **Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB)**, Barquisimeto, Estado Lara, Venezuela.

Desarrollado como **Trabajo Especial de Grado / Proyecto Socio-Integrador y Tecnológico (PSIT)** para la obtención del título de **Ingeniero(a) en Informática** en el Programa Nacional de Formación (PNF) en Informática (Trayecto IV).

* **Institución Beneficiaria:** Universidad Politécnica Territorial del Estado Lara "Andrés Eloy Blanco" (UPTAEB).
* **Todos los derechos reservados © UPTAEB — DIRPOLES.**
* **Uso exclusivo institucional y académico.** Queda prohibida la comercialización o distribución sin la debida autorización de las autoridades institucionales y autores del proyecto.

---

<div align="center">

**DIRPOLES-4** · Dirección de Políticas Estudiantiles · UPTAEB  
Barquisimeto, Venezuela  

</div>
