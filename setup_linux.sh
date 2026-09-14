#!/bin/bash
set -e

PROJECT_DIR="$(dirname "$(readlink -f "$0")")"
PROJECT_NAME="$(basename "$PROJECT_DIR")"

echo "=== Configuración automática: $PROJECT_NAME ==="
echo "Directorio: $PROJECT_DIR"

# ---------------------------------------------------------------
# 1. CONFIGURACIÓN DE APACHE (Alias + Directorio)
# ---------------------------------------------------------------
echo ""
echo "[1/7] Creando configuración de Apache..."
sudo tee /etc/apache2/conf-available/dirpoles.conf > /dev/null << EOF
Alias /${PROJECT_NAME} "${PROJECT_DIR}"

<Directory "${PROJECT_DIR}">
    Options FollowSymLinks MultiViews
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
    if [ -f "$PROJECT_DIR/.env.example" ]; then
        cp "$PROJECT_DIR/.env.example" "$PROJECT_DIR/.env"
    fi
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

generate_key_pair() {
    local name=$1
    if [ ! -f "$KEYS_DIR/${name}_private.pem" ]; then
        openssl genrsa -out "$KEYS_DIR/${name}_private.pem" 2048 2>/dev/null
        openssl rsa -in "$KEYS_DIR/${name}_private.pem" -pubout -out "$KEYS_DIR/${name}_public.pem" 2>/dev/null
        # 600: solo el dueño lee/escribe (llaves PRIVADAS)
        chmod 600 "$KEYS_DIR/${name}_private.pem"
        chmod 644 "$KEYS_DIR/${name}_public.pem"
        echo "  → ${name} OK"
    else
        echo "  → ${name} ya existe"
    fi
}

generate_key_pair "login"
generate_key_pair "jwt"

# ---------------------------------------------------------------
# 4. DIRECTORIOS Y PERMISOS
# ---------------------------------------------------------------
echo "[4/7] Creando directorios y permisos..."
mkdir -p "$PROJECT_DIR/logs"
chmod 777 "$PROJECT_DIR/logs"
chmod 644 "$PROJECT_DIR/.env"

# ---------------------------------------------------------------
# 5. DEPENDENCIAS PHP (Composer)
# ---------------------------------------------------------------
echo "[5/7] Instalando dependencias PHP (Composer)..."
cd "$PROJECT_DIR"
if [ ! -d vendor ]; then
    composer install --no-interaction
else
    echo "  → vendor/ ya existe"
fi

# ---------------------------------------------------------------
# 6. BASES DE DATOS (MySQL)
# ---------------------------------------------------------------
echo "[6/7] Importando bases de datos..."
if command -v mysql &> /dev/null; then
    mysql -u root < "$PROJECT_DIR/docs/bd/dirpoles_security.sql" 2>/dev/null && \
        echo "  → dirpoles_security importada" || \
        echo "  → dirpoles_security: ya existe o error (verifica MySQL)"
    mysql -u root < "$PROJECT_DIR/docs/bd/dirpoles_business.sql" 2>/dev/null && \
        echo "  → dirpoles_business importada" || \
        echo "  → dirpoles_business: ya existe o error (verifica MySQL)"
else
    echo "  ⚠️  mysql CLI no encontrado. Importa manualmente los archivos .sql de docs/bd/"
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
echo "============================================"
