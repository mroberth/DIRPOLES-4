# Contexto de infraestructura y despliegue en AWS

> **Propósito:** este documento describe el entorno de producción actual de
> DIRPOLES y sirve como referencia para desarrolladores, administradores,
> auditores y agentes de IA.
>
> **Regla importante:** los valores de este documento describen el entorno
> existente. Antes de modificar infraestructura, confirmar que los nombres,
> rutas, versiones y direcciones continúan vigentes.

## 1. Resumen ejecutivo

DIRPOLES está desplegado en una instancia **AWS EC2** que concentra:

1. El monolito web **DIRPOLES-4**.
2. El microservicio de inteligencia artificial **DIRPOLES-IA**.
3. Los dos esquemas MySQL utilizados por el sistema.

La aplicación móvil y otros clientes externos consumen la API del monolito
mediante HTTPS. El microservicio de IA no está expuesto públicamente: solo
acepta conexiones locales desde el propio servidor.

### Flujo general

```text
Cliente web o móvil
        │
        │ HTTPS
        ▼
Apache + Certbot
        │
        ▼
DIRPOLES-4 (PHP/MVC)
        ├── MySQL local
        │     ├── dirpoles_business
        │     └── dirpoles_security
        │
        └── HTTP local + X-API-Key
              ▼
        DIRPOLES-IA (FastAPI/Uvicorn)
```

## 2. Servidor AWS EC2

| Elemento | Configuración actual |
|---|---|
| Proveedor | Amazon Web Services (AWS) |
| Servicio | EC2 |
| Región | `us-east-2` — Ohio |
| Zona de disponibilidad | `us-east-2c` |
| Identificador lógico | `DIRPOLES-Server` |
| Instance ID | `i-0f53fc4d203448304` |
| Tipo de instancia | `t3.micro` |
| CPU | 2 vCPU |
| Memoria | Aproximadamente 1 GB de RAM |
| Sistema operativo | Ubuntu 26.04.1 LTS |
| Kernel registrado | `7.0.0-1012-aws` |
| Arquitectura | `x86_64` |
| Usuario SSH | `ubuntu` |
| Acceso SSH | Llave privada `dirpoles-key.pem` |

### Direccionamiento y dominio

| Elemento | Valor |
|---|---|
| IPv4 pública | `3.19.72.82` |
| IPv4 privada | `172.31.34.181` |
| Dominio público | [https://dirpoles.duckdns.org](https://dirpoles.duckdns.org) |
| Transporte público | HTTPS |
| Certificado | Let's Encrypt administrado con Certbot |

> Las direcciones IP pueden cambiar si la instancia se detiene y no utiliza una
> Elastic IP. Verificar siempre la dirección actual en AWS antes de actualizar
> DNS, reglas de firewall o documentación operativa.

### Memoria de intercambio

Debido al límite de memoria de `t3.micro`, el servidor tiene un archivo Swap
permanente:

```text
/swapfile
```

Características registradas:

- Tamaño: 2.0 GB.
- Persistencia: configurado en `/etc/fstab`.
- Propósito: reducir el riesgo de que el kernel termine MySQL durante picos
  de consumo de memoria.

El Swap es una medida de estabilidad, no un sustituto de memoria RAM. Si el
servidor empieza a utilizarlo de forma constante, se debe revisar el consumo o
escalar la instancia.

## 3. Componentes desplegados

### 3.1. Monolito DIRPOLES-4

| Elemento | Configuración |
|---|---|
| Ruta | `/var/www/html` |
| Lenguaje | PHP 8.5.4 |
| Arquitectura | MVC propio, sin framework pesado |
| Servidor web | Apache |
| Dependencias | Composer |
| Instalación de producción | `composer install --no-dev --optimize-autoloader` |
| API | Endpoints bajo el prefijo `/api` |
| Dominio público | `https://dirpoles.duckdns.org` |

El monolito contiene los 18 módulos funcionales, administrativos, clínicos y
logísticos del sistema.

#### Seguridad del monolito

- Llaves RSA almacenadas en:

  ```text
  /var/www/html/app/Config/Keys/
  ```

- Archivos esperados:

  ```text
  login_private.pem
  login_public.pem
  jwt_private.pem
  jwt_public.pem
  ```

- Las llaves se utilizan para:
  - Descifrar la contraseña enviada durante el login.
  - Firmar y validar tokens JWT con RS256.
- El archivo `.env` y las llaves privadas no se versionan en Git.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- El propietario operativo registrado es `www-data:www-data`.
- La aplicación utiliza permisos generales restringidos y permisos `600` para:
  - `/var/www/html/.env`
  - `/var/www/html/app/Config/Keys/*_private.pem`
- `RateLimitMiddleware` utiliza la base de datos de seguridad para controlar
  ráfagas de peticiones por IP y endpoint.

#### Método de despliegue

El código se obtiene desde el repositorio oficial:

```text
https://github.com/mroberth/DIRPOLES-4.git
```

Las llaves RSA y el archivo `.env` se crean o copian directamente en el
servidor y permanecen fuera del control de versiones.

### 3.2. Microservicio DIRPOLES-IA

| Elemento | Configuración |
|---|---|
| Ruta | `/var/www/dirpoles-ia` |
| Tecnología | Python 3 + FastAPI |
| Servidor ASGI | Uvicorn |
| Entorno virtual | `/var/www/dirpoles-ia/.venv` |
| Proveedor de IA | Google AI Studio |
| Modelo registrado | `gemini-3.5-flash` |
| Host de escucha | `127.0.0.1` |
| Puerto | `8000` |
| Modo | Servicio persistente de systemd |
| Unidad | `/etc/systemd/system/dirpoles-ia.service` |
| Reinicio | `Restart=always` |

#### Comunicación con DIRPOLES-4

El microservicio solo escucha en `127.0.0.1:8000`. Por tanto, no debe
publicarse directamente en Internet ni abrirse su puerto en el Security Group
de AWS.

El monolito lo consume mediante HTTP local:

```text
http://127.0.0.1:8000
```

La autenticación entre ambos componentes se realiza con la cabecera:

```text
X-API-Key: <valor configurado>
```

La clave compartida se configura mediante `DIRPOLES_IA_API_KEY` y debe
permanecer únicamente en los archivos `.env` de los servicios correspondientes.
Nunca debe exponerse al navegador, al código JavaScript ni al repositorio.

#### Rendimiento registrado

Tiempo medido en producción:

- Aproximadamente 20.13 segundos para analizar 114 registros históricos.

Este dato es una referencia histórica, no un SLA. Debe volver a medirse si
cambian el modelo, la cantidad de registros, la instancia EC2 o el proveedor.

### 3.3. MySQL y separación de datos

MySQL Community Server se ejecuta localmente en:

```text
127.0.0.1:3306
```

#### Esquemas

| Esquema | Responsabilidad |
|---|---|
| `dirpoles_business` | Beneficiarios, atención estudiantil, módulos operativos, inventarios, transporte y demás datos de negocio |
| `dirpoles_security` | Autenticación, sesiones, RBAC, bitácora, notificaciones, intentos de login y límites de solicitudes |

#### Usuarios de base de datos

| Usuario | Uso | Permisos |
|---|---|---|
| `dirpoles_user` | Conexión del monolito PHP | Lectura y escritura sobre ambos esquemas |
| `dirpoles_ia_reader` | Conexión del microservicio de IA | Solo `SELECT` sobre ambos esquemas |

El microservicio de IA debe conservar permisos de solo lectura. No se le deben
conceder permisos de inserción, actualización, eliminación, DDL o administración.

## 4. Despliegue y actualización

### 4.1. Requisitos previos

Antes de desplegar, confirmar:

- Acceso SSH funcional con la llave autorizada.
- Instancia EC2 en ejecución.
- Security Group con los puertos estrictamente necesarios:
  - `22` para SSH, idealmente restringido a IPs administrativas.
  - `80` para HTTP y redirección o validación de Certbot.
  - `443` para HTTPS.
- El puerto `3306` no debe exponerse públicamente.
- El puerto `8000` no debe exponerse públicamente.
- Existe una copia de seguridad reciente de ambas bases de datos.
- Se conocen los valores de producción de `.env` y `DIRPOLES_IA_API_KEY`.

### 4.2. Despliegue del monolito

El flujo general es:

```bash
cd /var/www/html
git pull origin main
composer install --no-dev --optimize-autoloader
```

Después de actualizar:

1. Confirmar que `.env` conserva `APP_ENV=production`.
2. Confirmar que `APP_DEBUG=false`.
3. Confirmar la existencia de las cuatro llaves RSA.
4. Confirmar propietario y permisos:

   ```bash
   sudo chown -R www-data:www-data /var/www/html
   sudo chmod 600 /var/www/html/.env
   sudo chmod 600 /var/www/html/app/Config/Keys/*_private.pem
   ```

5. Validar la configuración de Apache.
6. Recargar Apache:

   ```bash
   sudo systemctl reload apache2
   ```

No se deben reemplazar `.env`, llaves RSA, uploads ni logs con una copia del
repositorio.

### 4.3. Despliegue del microservicio

La actualización debe conservar el entorno virtual y la configuración de
producción:

```bash
cd /var/www/dirpoles-ia
git pull origin main
.venv/bin/pip install -r requirements.txt
sudo systemctl restart dirpoles-ia
sudo systemctl status dirpoles-ia --no-pager
```

Comprobar que el servicio siga escuchando solamente de forma local:

```bash
ss -lntp | grep ':8000'
```

La salida esperada debe indicar `127.0.0.1:8000`, no `0.0.0.0:8000`.

### 4.4. Actualización del certificado HTTPS

Certbot/Let's Encrypt administra el certificado del dominio. Después de una
renovación o modificación de Apache, comprobar:

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
sudo certbot renew --dry-run
```

## 5. Copias de seguridad y base de datos

Antes de importar un dump, ejecutar una copia de seguridad de ambos esquemas.
Los dumps del proyecto pueden contener instrucciones destructivas como
`DROP TABLE`; no deben importarse sobre producción sin una decisión explícita y
una copia recuperable.

Ejemplo conceptual de respaldo:

```bash
mysqldump -u dirpoles_user -p \
  --routines --triggers dirpoles_security > security-backup.sql

mysqldump -u dirpoles_user -p \
  --routines --triggers dirpoles_business > business-backup.sql
```

Los archivos de respaldo contienen información sensible. Deben almacenarse con
permisos restringidos y eliminarse según la política institucional de
retención.

## 6. Comprobaciones operativas

### Estado de servicios

```bash
sudo systemctl status apache2 --no-pager
sudo systemctl status mysql --no-pager
sudo systemctl status dirpoles-ia --no-pager
```

### Puertos locales

```bash
ss -lntp | grep -E ':(3306|8000)'
```

La configuración esperada es:

- MySQL: accesible localmente, no desde Internet.
- DIRPOLES-IA: `127.0.0.1:8000`, no desde Internet.

### Logs

```bash
sudo journalctl -u apache2 -n 100 --no-pager
sudo journalctl -u mysql -n 100 --no-pager
sudo journalctl -u dirpoles-ia -n 100 --no-pager
```

Los logs de aplicación se encuentran dentro de:

```text
/var/www/html/logs/
```

No se deben publicar logs, `.env`, llaves privadas ni dumps SQL mediante
Apache.

### Verificación funcional mínima

Después de un despliegue:

1. Abrir `https://dirpoles.duckdns.org/login`.
2. Comprobar que el login funciona.
3. Comprobar que se cargue `/inicio`.
4. Consultar una operación de lectura.
5. Ejecutar una operación de escritura controlada y verificar la bitácora.
6. Comprobar que el endpoint de reportes/IA puede comunicarse con
   `127.0.0.1:8000`.
7. Confirmar que no se muestran errores de depuración al usuario.

## 7. Reglas de seguridad que deben conservarse

- No subir `.env`, llaves RSA, contraseñas, tokens, API keys ni dumps con datos
  reales al repositorio.
- No exponer públicamente MySQL (`3306`) ni DIRPOLES-IA (`8000`).
- Mantener `APP_DEBUG=false` en producción.
- Usar HTTPS para todo tráfico externo.
- Mantener `dirpoles_ia_reader` con permisos exclusivamente de lectura.
- Respaldar antes de actualizar esquemas o importar dumps.
- Revisar los permisos después de cada despliegue.
- No sustituir las llaves RSA durante una actualización normal: cambiarlas
  invalida tokens y requiere un procedimiento de rotación controlado.
- Restringir SSH por IP cuando sea posible y proteger la llave privada local.

## 8. Información que debe actualizarse cuando cambie el entorno

Actualizar este documento si cambia cualquiera de los siguientes elementos:

- Región, zona o tipo de instancia EC2.
- Instance ID, IP pública, IP privada o dominio.
- Sistema operativo, versión de PHP, MySQL o Python.
- Ruta del monolito o del microservicio.
- Puerto o dirección de escucha del microservicio.
- Nombre de la unidad systemd.
- Modelo o proveedor de IA.
- Usuarios, permisos o esquemas de base de datos.
- Método de despliegue, respaldo o renovación de certificados.

No escribir valores secretos en este archivo. Documentar únicamente el nombre
de la variable, la ruta o el procedimiento; conservar el valor real en el
gestor de secretos o en los archivos de configuración protegidos del servidor.
