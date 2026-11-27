#!/usr/bin/env bash
# =====================================================================
# DIRPOLES-4: Script de Carga Automatizada de Seeds (Tanda 2)
# Ubicación: scripts/importar_seed_nuevas.sh
#
# Carga en orden topológico estricto todos los archivos de seed
# sintéticos de la carpeta docs/bd/seed_nuevas/ en la base de datos
# de negocio (dirpoles_business), leyendo la configuración de .env.
# =====================================================================

set -Eeuo pipefail

# Colores para la salida en consola
VERDE="\033[0;32m"
ROJO="\033[0;31m"
AMARILLO="\033[1;33m"
AZUL="\033[0;34m"
CYAN="\033[0;36m"
NC="\033[0m" # Sin color

# Directorio base del proyecto
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
SEEDS_DIR="$PROJECT_DIR/docs/bd/seed_nuevas"
ENV_FILE="$PROJECT_DIR/.env"

echo -e "${CYAN}======================================================================${NC}"
echo -e "${CYAN}   DIRPOLES-4 — IMPORTADOR AUTOMATIZADO DE SEEDS SINTÉTICOS (TANDA 2)  ${NC}"
echo -e "${CYAN}======================================================================${NC}\n"

# 1. Detectar cliente mariadb o mysql
if command -v mariadb >/dev/null 2>&1; then
    CLI_BIN="mariadb"
elif command -v mysql >/dev/null 2>&1; then
    CLI_BIN="mysql"
else
    echo -e "${ROJO}[ERROR] No se encontró el cliente 'mariadb' ni 'mysql' en el PATH.${NC}" >&2
    echo -e "        Instálalo con: sudo dnf install mariadb (en Fedora) o equivalente." >&2
    exit 1
fi

# 2. Leer configuración desde .env si existe
DB_HOST="localhost"
DB_NAME="dirpoles_business"
DB_USER="root"
DB_PASS=""

if [[ -f "$ENV_FILE" ]]; then
    echo -e "${AZUL}[INFO] Leyendo credenciales desde .env...${NC}"
    # Extraer variables sin evaluar scripts maliciosos
    ENV_HOST=$(grep -E '^DB_HOST=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '\r"' || true)
    ENV_NAME=$(grep -E '^DB_NAME=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '\r"' || true)
    ENV_USER=$(grep -E '^DB_USER=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '\r"' || true)
    ENV_PASS=$(grep -E '^DB_PASS=' "$ENV_FILE" | cut -d '=' -f2- | tr -d '\r"' || true)

    [[ -n "$ENV_HOST" ]] && DB_HOST="$ENV_HOST"
    [[ -n "$ENV_NAME" ]] && DB_NAME="$ENV_NAME"
    [[ -n "$ENV_USER" ]] && DB_USER="$ENV_USER"
    [[ -n "$ENV_PASS" ]] && DB_PASS="$ENV_PASS"
else
    echo -e "${AMARILLO}[AVISO] Archivo .env no encontrado. Usando valores por defecto (host=$DB_HOST, user=$DB_USER, db=$DB_NAME).${NC}"
fi

# Parámetros de conexión a MariaDB / MySQL
MYSQL_CMD=("$CLI_BIN" --default-character-set=utf8mb4 -h "$DB_HOST" -u "$DB_USER")
if [[ -n "$DB_PASS" ]]; then
    MYSQL_CMD+=("-p$DB_PASS")
else
    # Si DB_PASS está vacío, preguntar si desea ingresar contraseña manualmente
    read -r -p "¿Deseas ingresar una contraseña para el usuario '$DB_USER'? [s/N]: " RESP
    if [[ "$RESP" =~ ^[sS]$ ]]; then
        MYSQL_CMD+=("-p")
    fi
fi

# Verificar conectividad con la base de datos
echo -e "${AZUL}[INFO] Verificando conexión a la base de datos '$DB_NAME'...${NC}"
if ! "${MYSQL_CMD[@]}" -e "USE \`$DB_NAME\`;" >/dev/null 2>&1; then
    echo -e "${ROJO}[ERROR] No se pudo conectar a la base de datos '$DB_NAME'.${NC}" >&2
    echo -e "        Verifica que el servicio MySQL/MariaDB esté activo y las credenciales sean correctas." >&2
    exit 1
fi
echo -e "${VERDE}[OK] Conexión establecida con éxito.${NC}\n"

# 3. Lista ordenada de archivos de seed
SEEDS=(
    "seed_beneficiarios2.sql"
    "seed_citas2.sql"
    "seed_psicologia2.sql"
    "seed_medicina2.sql"
    "seed_orientacion2.sql"
    "seed_discapacidad2.sql"
    "seed_trabajo_social2.sql"
    "seed_referencias2.sql"
    "seed_recientes_medicina_becas.sql"
)

TOTAL_SEEDS=${#SEEDS[@]}
EXITO_COUNT=0

echo -e "${CYAN}Iniciando importación de $TOTAL_SEEDS archivos de seed en orden topológico:${NC}"
echo -e "----------------------------------------------------------------------"

# 4. Iterar y ejecutar cada seed
for i in "${!SEEDS[@]}"; do
    NUM=$((i + 1))
    SEED_FILE="${SEEDS[$i]}"
    FULL_PATH="$SEEDS_DIR/$SEED_FILE"

    echo -ne "[${NUM}/${TOTAL_SEEDS}] Importando ${SEED_FILE}... "

    if [[ ! -f "$FULL_PATH" ]]; then
        echo -e "${ROJO}[NO ENCONTRADO]${NC}" >&2
        echo -e "${ROJO}        Ruta faltante: $FULL_PATH${NC}" >&2
        exit 1
    fi

    # Ejecutar seed redirigiendo el archivo a mysql
    if "${MYSQL_CMD[@]}" "$DB_NAME" < "$FULL_PATH"; then
        echo -e "${VERDE}[OK]${NC}"
        EXITO_COUNT=$((EXITO_COUNT + 1))
    else
        echo -e "${ROJO}[FALLÓ]${NC}" >&2
        echo -e "${ROJO}[ERROR] Ocurrió un error al ejecutar $SEED_FILE. Abortando.${NC}" >&2
        exit 1
    fi
done

echo -e "----------------------------------------------------------------------"
echo -e "${VERDE}¡Importación completada! ($EXITO_COUNT de $TOTAL_SEEDS archivos aplicados con éxito).${NC}\n"

# 5. Consultas de verificación final
echo -e "${CYAN}======================================================================${NC}"
echo -e "${CYAN}                RESUMEN DE REGISTROS EN BASE DE DATOS                 ${NC}"
echo -e "${CYAN}======================================================================${NC}"

"${MYSQL_CMD[@]}" "$DB_NAME" -t << 'SQL'
SELECT 'Beneficiarios Totales' AS Concepto, COUNT(*) AS Total FROM beneficiario
UNION ALL
SELECT 'Citas de Psicología', COUNT(*) FROM cita
UNION ALL
SELECT 'Consultas de Psicología', COUNT(*) FROM consulta_psicologica
UNION ALL
SELECT 'Consultas de Medicina', COUNT(*) FROM consulta_medica
UNION ALL
SELECT 'Atenciones de Orientación', COUNT(*) FROM orientacion
UNION ALL
SELECT 'Expedientes de Discapacidad', COUNT(*) FROM discapacidad
UNION ALL
SELECT 'Becas Estudiantiles', COUNT(*) FROM becas
UNION ALL
SELECT 'Exoneraciones de Aranceles', COUNT(*) FROM exoneracion
UNION ALL
SELECT 'Ayudas Médicas FAMES', COUNT(*) FROM fames
UNION ALL
SELECT 'Atención a Gestantes', COUNT(*) FROM gestion_emb
UNION ALL
SELECT 'Referencias Inter-departamentales', COUNT(*) FROM referencias
UNION ALL
SELECT 'Trazabilidad en Log Referencias', COUNT(*) FROM log_referencias
UNION ALL
SELECT 'Consultas Médicas (Hoy 26/11)', COUNT(*) FROM consulta_medica WHERE fecha_creacion = '2026-11-26'
UNION ALL
SELECT 'Becas Registradas (Hoy 26/11)', COUNT(*) FROM becas WHERE fecha_creacion = '2026-11-26'
UNION ALL
SELECT 'Consultas Médicas (Última Semana: 18-26 Nov)', COUNT(*) FROM consulta_medica WHERE fecha_creacion BETWEEN '2026-11-18' AND '2026-11-26'
UNION ALL
SELECT 'Becas Registradas (Última Semana: 18-26 Nov)', COUNT(*) FROM becas WHERE fecha_creacion BETWEEN '2026-11-18' AND '2026-11-26';
SQL

echo -e "\n${CYAN}Distribución de Referencias por Estado:${NC}"
"${MYSQL_CMD[@]}" "$DB_NAME" -t -e "SELECT estado AS Estado, COUNT(*) AS Total FROM referencias GROUP BY estado;"

echo -e "\n${VERDE}Todo listo. El entorno de datos sintéticos (2024 - 26/11/2026) está activo y verificado.${NC}"
