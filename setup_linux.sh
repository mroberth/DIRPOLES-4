#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_DIR="$(dirname "$(readlink -f "$0")")"
PROJECT_NAME="$(basename "$PROJECT_DIR")"
SECURITY_SQL="$PROJECT_DIR/docs/bd/dirpoles_security_actualizada.sql"
BUSINESS_SQL="$PROJECT_DIR/docs/bd/dirpoles_business_actualizada.sql"
APACHE_CONF="/etc/apache2/conf-available/dirpoles.conf"
IMPORT_DATABASES="${IMPORT_DATABASES:-0}"

echo "=== Configuración automática: $PROJECT_NAME ==="
echo "Directorio: $PROJECT_DIR"

if [[ "${1:-}" == "--import-databases" ]]; then
    IMPORT_DATABASES=1
fi

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "[ERROR] No se encontró el comando requerido: $1" >&2
        exit 1
    fi
}

for command_name in sudo php composer openssl mysql systemctl a2enconf a2enmod; do
    require_command "$command_name"
done

if [[ ! -f "$PROJECT_DIR/.env.example" ]]; then
    echo "[ERROR] No se encontró .env.example." >&2
    exit 1
fi

if [[ ! -f "$SECURITY_SQL" || ! -f "$BUSINESS_SQL" ]]; then
    echo "[ERROR] No se encontraron los dumps SQL esperados:" >&2
    echo "        $SECURITY_SQL" >&2
    echo "        $BUSINESS_SQL" >&2
    exit 1
fi

for extension_name in openssl pdo_mysql mbstring fileinfo; do
    if ! php -m | grep -Eiq "^${extension_name}$"; then
        echo "[ERROR] Falta la extensión PHP requerida: $extension_name" >&2
        exit 1
    fi
done

# ---------------------------------------------------------------
# 1. CONFIGURACIÓN DE APACHE (Alias + Directorio)
# ---------------------------------------------------------------
echo ""
echo "[1/7] Creando configuración de Apache..."
sudo tee "$APACHE_CONF" > /dev/null << EOF
Alias /${PROJECT_NAME} "${PROJECT_DIR}"

<Directory "${PROJECT_DIR}">
    Options FollowSymLinks -MultiViews
    AllowOverride All
    Require all granted
</Directory>
EOF

sudo a2enconf dirpoles
sudo a2enmod rewrite

# ---------------------------------------------------------------
# 2. VARIABLES DE ENTORNO (.env)
# ---------------------------------------------------------------
echo "[2/7] Creando .env desde .env.example..."
if [ ! -f "$PROJECT_DIR/.env" ]; then
    cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
    echo "  → .env creado (revisa CORS_ALLOWED_ORIGINS y ajusta los valores)"
else
    echo "  → .env ya existe, se conserva"
fi

# ---------------------------------------------------------------
# 3. LLAVES RSA (JWT + Login)
# ---------------------------------------------------------------
echo "[3/7] Generando llaves RSA..."
KEYS_DIR="$PROJECT_DIR/app/Config/Keys"
mkdir -p "$KEYS_DIR"

php "$PROJECT_DIR/scripts/generar_llaves.php"
for key_name in login jwt; do
    for key_type in private public; do
        key_path="$KEYS_DIR/${key_name}_${key_type}.pem"
        if [[ ! -s "$key_path" ]]; then
            echo "[ERROR] No se generó la llave esperada: $key_path" >&2
            exit 1
        fi
    done
done

# ---------------------------------------------------------------
# 4. DIRECTORIOS Y PERMISOS
# ---------------------------------------------------------------
echo "[4/7] Creando directorios y permisos..."
mkdir -p "$PROJECT_DIR/logs" "$PROJECT_DIR/uploads"
sudo chown -R "$USER:www-data" "$PROJECT_DIR/logs" "$PROJECT_DIR/uploads"
sudo chmod 750 "$PROJECT_DIR/logs" "$PROJECT_DIR/uploads"
sudo chown "$USER:www-data" "$PROJECT_DIR/.env"
sudo chmod 640 "$PROJECT_DIR/.env"

# ---------------------------------------------------------------
# 5. DEPENDENCIAS PHP (Composer)
# ---------------------------------------------------------------
echo "[5/7] Instalando dependencias PHP (Composer)..."
cd "$PROJECT_DIR"
composer install --no-interaction --prefer-dist

# ---------------------------------------------------------------
# 6. BASES DE DATOS (MySQL)
# ---------------------------------------------------------------
echo "[6/7] Importando bases de datos..."
if [[ "$IMPORT_DATABASES" != "1" ]]; then
    echo "  → Omitida por seguridad. Los dumps contienen DROP TABLE."
    echo "    Para importarlos explícitamente: ./setup_linux.sh --import-databases"
else
    MYSQL_USER="${MYSQL_USER:-root}"
    MYSQL_HOST="${MYSQL_HOST:-localhost}"
    MYSQL_PASSWORD="${MYSQL_PASSWORD-}"
    MYSQL_ARGS=(-h "$MYSQL_HOST" -u "$MYSQL_USER")
    if [[ -n "$MYSQL_PASSWORD" ]]; then
        MYSQL_ARGS+=("-p$MYSQL_PASSWORD")
    fi
    mysql "${MYSQL_ARGS[@]}" < "$SECURITY_SQL"
    echo "  → dirpoles_security importada"
    mysql "${MYSQL_ARGS[@]}" < "$BUSINESS_SQL"
    echo "  → dirpoles_business importada"
fi

# ---------------------------------------------------------------
# 7. REINICIAR APACHE
# ---------------------------------------------------------------
echo "[7/7] Reiniciando Apache..."
sudo systemctl restart apache2

echo ""
echo "============================================"
echo "  ✅ Configuración completada"
echo "  Accede: http://localhost/${PROJECT_NAME}"
echo "  Nota: revisa .env y ejecuta la importación SQL explícita si corresponde."
echo "============================================"
