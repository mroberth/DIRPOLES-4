# 📚 Arquitectura y Core Técnico: Sistema DIRPOLES 4

Este documento constituye la referencia técnica principal para el ecosistema **DIRPOLES 4**. Su propósito es proporcionar una visión exhaustiva de la infraestructura, las decisiones de diseño y los estándares de implementación que rigen el proyecto.

A diferencia de un manual de usuario, esta guía se enfoca en el "core" del sistema, detallando la lógica detrás del enrutamiento, los protocolos de seguridad y la integración de microservicios. Está diseñada para facilitar la escalabilidad del software y asegurar que cualquier desarrollador pueda comprender la base técnica sin ambigüedades.

---

## 1. 🛠️ Stack Tecnológico y Entorno

DIRPOLES 4 implementa una arquitectura distribuida que separa las responsabilidades de gestión de negocio (Backend en PHP) de los procesos de cómputo intensivo (Microservicio de Inteligencia Artificial en Python).

### 1.1 Backend Core (Gestión y Lógica de Negocio)
* **Lenguaje:** PHP 8+ (Estricto, tipado y orientado a objetos).
* **Servidor Web:** Apache.
* **Base de Datos:** MySQL. El esquema se divide en `dirpoles_business` (datos transaccionales) y `dirpoles_security` (gestión de identidades y accesos) para aislar los entornos de seguridad.
* **Gestión de Paquetes:** Composer (`composer.json`).
* **Dependencias Clave:**
    * `firebase/php-jwt`: Implementación del estándar JWT para autenticación.
    * `vlucas/phpdotenv`: Carga de variables de entorno estáticas.

### 1.2 Frontend (Interfaz de Cliente)
* **Lenguaje:** JavaScript (Vanilla JS / jQuery ES6+), HTML5, CSS3.
* **Framework UI:** Bootstrap 5.
* **Gestión de Assets:** Las dependencias del frontend se manejan de dos formas:
    1.  **Librerías Core Estables:** Alojadas directamente en `/plugins/` (Select2, DataTables, SweetAlert2). Esto elimina la dependencia de un registro remoto (NPM) en entornos de producción restrictivos y asegura la disponibilidad offline.
    2.  **Herramientas Auxiliares:** Gestionadas vía `package.json` (`npm install`) para módulos específicos como `driver.js`.

### 1.3 Microservicio de IA (Análisis Computacional)
* **Lenguaje:** Python 3.11+. Se delega el procesamiento matemático y algorítmico a Python debido a la madurez de su ecosistema para Data Science y Machine Learning, superando las limitaciones nativas de PHP en estas áreas.
* **Framework:** FastAPI. Seleccionado por su alto rendimiento, soporte nativo de asincronía (`asyncio`) y generación automática de documentación OpenAPI (Swagger).
* **Comunicación:** Arquitectura RESTful (Backend-to-Backend) con payload JSON.

---

## 2. 🏗️ Arquitectura PHP (MVC Custom y Routing)

Se prescindió del uso de frameworks comerciales voluminosos (como Laravel o Symfony) en favor de una implementación **MVC (Modelo-Vista-Controlador)** propietaria, optimizada para baja latencia.

### 2.1 Implementación MVC
La separación de responsabilidades se define estrictamente:
* **Modelos (`/app/Models/`):** Capa exclusiva de acceso a datos e interacción con PDO.
* **Vistas (`/app/Views/`):** Capa de presentación renderizada en el servidor.
* **Controladores (`/app/Controllers/`):** Orquestadores del flujo de negocio.

### 2.2 Sistema de Enrutamiento y Front Controller (`.htaccess`)
El patrón de diseño *Front Controller* se aplica mediante la reescritura de URLs en el archivo `.htaccess`. Todas las peticiones HTTP (ej: `http://localhost/DIRPOLES_4/endpoint`) se interceptan y redirigen a `index.php`. 
Esto centraliza el control de acceso, permite URLs semánticas y habilita optimizaciones a nivel de servidor (compresión GZIP y políticas de caché agresivas para estáticos).

### 2.3 Bootstraping (`index.php`)
El ciclo de vida de la aplicación sigue este orden:
1.  Registro del *autoloader* (Composer).
2.  Inyección de variables de entorno (`.env`).
3.  Carga de configuraciones globales (`config.php`) y dependencias core (`bootstrap.php`).
4.  Delegación de la petición a la clase principal: `\App\Core\Router::ejecutar();`

### 2.4 Resolución de Rutas y Lazy Loading
El módulo `Router.php` mapea la URI entrante contra las definiciones en `routes.php`. Se implementó un patrón de **Lazy Loading** (Carga Perezosa) mediante la función `load_controller()`. Los controladores se instancian (`require_once`) únicamente cuando la ruta correspondiente es invocada, reduciendo drásticamente el *footprint* de memoria por transacción.

---

## 3. 🧩 Core de Objetos (POO) y Abstracción de Datos

La capa de Modelos implementa principios avanzados de POO para asegurar la integridad de los datos antes de las transacciones SQL.

### 3.1 Encapsulamiento y Mutadores
El estado de las entidades de la base de datos se mantiene en arrays privados (ej: `$atributos = []` en `BeneficiarioModel.php`). 
La inyección de datos se gestiona a través del método mágico `__set($propiedad, $valor)`. Esto actúa como una capa de mutación y validación automática. Por ejemplo, al asignar un email, el mutador lo estandariza (minúsculas, validación de formato RFC) o lanza una `Exception` antes de que el controlador intente el `INSERT`.

### 3.2 Herencia en la Capa de Acceso a Datos
El diseño implementa una jerarquía de clases para abstraer la conexión:
* Clase Base: `Database.php` (Manejo del objeto PDO).
* Clases Intermedias: `BusinessModel.php` y `SecurityModel.php` (Configuraciones específicas por base de datos).
* Clases Finales: `BeneficiarioModel extends BusinessModel`.
Esto centraliza la lógica de conexión y reduce la duplicidad de código.

### 3.3 Polimorfismo en Transacciones
La abstracción de transacciones CRUD se maneja mediante el método polimórfico `manejarAccion($action)`. El controlador invoca `$modelo->manejarAccion('registrar')` sin conocer la implementación de la consulta. El modelo evalúa el `$action` y delega la ejecución a métodos privados transaccionales.

---

## 4. 🔒 Arquitectura de Seguridad (Middleware y Tokens)

DIRPOLES 4 implementa un modelo de autenticación híbrido (Sesiones de Servidor + Tokens de Estado) validado mediante Middleware.

### 4.1 Capa de Middleware
Se utiliza una arquitectura de *interceptores* a nivel de enrutamiento. En `routes.php`, la directiva `Router::antes('ALL', '.*', callback)` evalúa el contexto de seguridad de la petición entrante. El middleware determina los permisos de acceso (Rutas Públicas vs. Rutas Protegidas) antes de inicializar cualquier controlador.

### 4.2 Emisión y Transporte JWT (JSON Web Token)
El ciclo de autenticación en `loginController.php` opera bajo las siguientes fases:
1.  **Capa 1:** Instanciación de la sesión en el servidor (`$_SESSION`).
2.  **Capa 2:** El servicio `JwtHandler` firma digitalmente un payload (identidad del usuario) utilizando el algoritmo **RS256** (cifrado asimétrico) con la llave privada `app/Config/Keys/jwt_private.pem`. La validación del token se realiza con la llave pública `jwt_public.pem`.
3.  **Transporte Seguro:** El token se despacha al cliente exclusivamente a través de una **Cookie `HttpOnly`**. Esta directiva mitiga ataques de Cross-Site Scripting (XSS), ya que imposibilita la lectura del token a través de JavaScript del lado del cliente (`document.cookie`).

### 4.3 Verificación de Identidad Dual
Para acceder a endpoints protegidos, el Middleware ejecuta un proceso de validación cruzada:
1.  Verifica la existencia del estado de sesión (`$_SESSION`).
2.  Extrae y decodifica la cookie JWT entrante.
3.  Valida la integridad de la firma criptográfica y el periodo de expiración del token.
4.  **Validación de Integridad:** Cruza el `user_id` contenido en el payload del JWT con el registrado en `$_SESSION`. Discrepancias en esta validación asumen manipulación de estado y desencadenan el cierre forzado de sesión y registro de incidente.

---

## 5. 🤖 Orquestación de Microservicios (FastAPI)

Los procesos de cálculo intensivo se abstraen de la capa de PHP mediante llamadas RPC (Remote Procedure Call) sobre HTTP a la API de Python.

### 5.1 Arquitectura Desacoplada
El sistema opera en puertos lógicos separados:
* Backend PHP (Gestión transaccional): Puerto `80/443`.
* Microservicio de IA (Inferencia y estadística): Puerto `8000` (uvicorn).

### 5.2 Flujo de Peticiones (Backend-to-Backend)
Para procesar un análisis, el flujo evita la exposición de la API al cliente final:
1.  El Frontend solicita el análisis al controlador PHP.
2.  El controlador invoca la clase de servicio `MicroservicioIA.php` para serializar el payload.
3.  Se ejecuta una petición HTTP sincrónica mediante `cURL` dirigida al endpoint de FastAPI (ej: `POST /api/v1/analizar`).
4.  El microservicio procesa el tensor/payload, retorna la inferencia estructurada, y PHP la transfiere como respuesta a la vista.
Esta topología asegura que la IP pública y la arquitectura del microservicio permanezcan ocultas.

### 5.3 Autenticación de Servicios (API Key)
El acceso al puerto `8000` está restringido por autorización de cabeceras. Las peticiones desde PHP deben incluir el header `X-API-Key`. El microservicio evalúa este valor contra su propia variable de entorno `API_SECRET_KEY`. Peticiones no firmadas o con firmas inválidas son rechazadas con código HTTP `403 Forbidden`.

---

## 6. 📁 Estrategia de Despliegue y Versionado

### 6.1 Aislamiento de Credenciales (`.env`)
Las variables de entorno críticas (cadenas DSN de bases de datos, API Keys, duración de tokens) se inyectan en tiempo de ejecución a través del archivo `.env` para evitar *hardcoding* de credenciales. El acceso lógico se realiza a través de la superglobal `$_ENV`. Las llaves RSA (login y JWT) se gestionan como archivos PEM en `app/Config/Keys/` y no viajan en el `.env`.

### 6.2 Control de Versiones (`.gitignore`)
El repositorio mantiene un estado limpio mediante la exclusión estricta de artefactos generados y dependencias binarias en el `.gitignore`:
* `.env`: Previene la fuga de secretos (se provee un `.env.example` estructural).
* `logs/`: Exclusión de volcados de error locales.
* `vendor/`: Las dependencias de Composer se excluyen del árbol de versiones. La resolución de paquetes se garantiza mediante el manifiesto `composer.json` y el archivo de bloqueo `composer.lock` para asegurar la paridad de versiones entre entornos de desarrollo y producción.

---

## 📋 Resumen de Flujo de Trabajo para Desarrolladores

Para la implementación de nuevos módulos funcionales, siga el estándar MVC del proyecto:
1.  **Definición de Endpoint:** Registre la nueva URI en `/app/routes.php`.
2.  **Controlador:** Implemente la lógica de orquestación en `/app/Controllers/`.
3.  **Modelo de Datos:** Defina la entidad heredando de `BusinessModel` o `SecurityModel` en `/app/Models/`.
4.  **Persistencia:** Envíe datos al modelo siempre a través de mutadores mágicos (`$modelo->__set()`) para asegurar la validación previa a la inserción.
5.  **Capa de Presentación:** Diseñe la vista correspondiente en `/app/Views/`.
6.  **Servicios Externos:** Para integración con modelos de IA, utilice exclusivamente la fachada `MicroservicioIA.php`.
