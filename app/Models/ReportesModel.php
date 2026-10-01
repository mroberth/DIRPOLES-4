<?php

namespace App\Models;

use App\Core\ExcepcionApi;
use PDO;
use Throwable;

/**
 * Class ReportesModel
 * ---------------------------------------------------------------
 * Modelo de negocio de los reportes estadísticos (Módulo 15).
 *
 * Reglas permanentes (decisiones del usuario):
 *  - Los filtros se aplican EN SERVIDOR vía query params (fecha_inicio,
 *    fecha_fin, genero, pnf, area, estado, ...), para que el futuro
 *    microservicio IA pueda consumir los mismos endpoints sin navegador.
 *  - Toda consulta se limita con `limit` (defecto 5000, tope 20000).
 *  - Sin alcance de datos: cualquier usuario con permiso 'leer' ve todos
 *    los registros (decisión del usuario: no se limitan cédulas por rol).
 *  - Los errores se lanzan como ExcepcionApi (nunca arrays de error ni
 *    Exception genérica) y lo técnico se registra con error_log.
 *  - Psicología y Medicina devuelven DOS colecciones (morbilidad+citas,
 *    consultas+insumos); el resto devuelve un array de filas.
 */
class ReportesModel extends BusinessModel
{
    private const LIMITE_DEFECTO = 5000;
    private const LIMITE_MAXIMO  = 20000;

    /** Etiquetas del reporte general (literals del UNION: deben coincidir). */
    private const AREAS_GENERAL = [
        'Becas', 'Exoneración', 'FAMES',
        'Medicina', 'Orientación', 'Discapacidad', 'Psicología',
    ];
    private const SUBMODULOS_TS = ['Becas', 'Exoneración', 'FAMES', 'Gestión Embarazo'];
    private const ESTADOS_CITA = ['Pendiente', 'Confirmada', 'Atendida', 'Cancelada', 'No asistió'];
    private const ESTADOS_REFERENCIA = ['Pendiente', 'Aceptada', 'Rechazada'];
    private const ESTADOS_JORNADA = ['Activa', 'Cancelada', 'Finalizada'];
    private const ESTADOS_INVENTARIO = ['Activo', 'Inactivo'];
    private const ESTADOS_VEHICULO = ['Activo', 'Inactivo', 'Mantenimiento'];
    private const TIPOS_CONSULTA = ['Diagnóstico', 'Retiro temporal', 'Cambio de carrera'];
    private const TIPOS_DISCAPACIDAD = ['Física', 'Sensorial', 'Intelectual', 'Múltiple', 'Otro'];
    private const GRADOS_DISCAPACIDAD = ['Leve', 'Moderado', 'Grave'];
    private const TIPOS_BIEN = ['Mobiliario', 'Equipo'];
    private const TIPOS_VEHICULO = ['Autobús', 'Camioneta', 'Automóvil'];
    private const SECCIONES_TRANSPORTE = ['Vehículos', 'Rutas', 'Proveedores', 'Repuestos', 'Asignaciones', 'Mantenimientos'];

    private array $atributos = [];

    // ==================== Validación de atributos (__set) ====================

    public function __set(string $nombre, $valor): void
    {
        switch ($nombre) {
            case 'fecha_inicio':
            case 'fecha_fin':
                $this->atributos[$nombre] = $this->validarFecha($valor, $nombre);
                break;

            case 'genero':
                $genero = mb_strtoupper(trim((string) $valor));
                if ($genero !== '' && !in_array($genero, ['M', 'F'], true)) {
                    throw ExcepcionApi::validacion("El género debe ser 'M' (Masculino) o 'F' (Femenino).");
                }
                $this->atributos[$nombre] = $genero === '' ? null : $genero;
                break;

            case 'pnf':
            case 'servicio_destino':
                $this->atributos[$nombre] = $this->validarId($valor, $nombre);
                break;

            case 'area':
                $this->atributos[$nombre] = $this->validarLista($valor, self::AREAS_GENERAL, 'Área');
                break;

            case 'submodulo':
                $this->atributos[$nombre] = $this->validarLista($valor, self::SUBMODULOS_TS, 'Submódulo');
                break;

            case 'tipo_consulta':
                $this->atributos[$nombre] = $this->validarLista($valor, self::TIPOS_CONSULTA, 'Tipo de consulta');
                break;

            case 'tipo_discapacidad':
                $this->atributos[$nombre] = $this->validarLista($valor, self::TIPOS_DISCAPACIDAD, 'Tipo de discapacidad');
                break;

            case 'grado':
                $this->atributos[$nombre] = $this->validarLista($valor, self::GRADOS_DISCAPACIDAD, 'Grado');
                break;

            case 'tipo_bien':
                $this->atributos[$nombre] = $this->validarLista($valor, self::TIPOS_BIEN, 'Tipo de bien');
                break;

            case 'tipo_vehiculo':
                $this->atributos[$nombre] = $this->validarLista($valor, self::TIPOS_VEHICULO, 'Tipo de vehículo');
                break;

            case 'seccion_transporte':
                $this->atributos[$nombre] = $this->validarLista($valor, self::SECCIONES_TRANSPORTE, 'Sección de transporte');
                break;

            case 'estado':
                $this->atributos[$nombre] = $this->texto($valor, $nombre);
                break;

            case 'reporte':
                $this->atributos[$nombre] = $this->texto($valor, $nombre);
                break;

            case 'limit':
                $this->atributos[$nombre] = $this->validarLimit($valor);
                break;

            default:
                throw ExcepcionApi::validacion("Filtro no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre)
    {
        return $this->atributos[$nombre] ?? null;
    }

    // ==================== Despachador ====================

    public function manejarAccion(string $accion)
    {
        $this->asegurarRangoFechas();

        switch ($accion) {
            case 'reporteGeneral':
                return $this->getReportDataGeneral();

            case 'reporteMedicina':
                return $this->getReportDataMedicina();

            case 'reportePsicologia':
                return $this->getReportDataPsicologia();

            case 'reporteOrientacion':
                return $this->getReportDataOrientacion();

            case 'reporteTrabajoSocial':
                return $this->getReportDataTrabajoSocial();

            case 'reporteDiscapacidad':
                return $this->getReportDataDiscapacidad();

            case 'reporteReferencias':
                return $this->getReportDataReferencias();

            case 'reporteJornadas':
                return $this->getReportDataJornadas();

            case 'reporteMobiliario':
                return $this->getReportDataMobiliario();

            case 'reporteTransporte':
                return $this->getReportDataTransporte();

            case 'stats':
                return $this->getStats();

            case 'catalogos':
                return $this->getCatalogos();

            default:
                error_log("ReportesModel::manejarAccion - acción no reconocida: {$accion}");
                throw ExcepcionApi::errorInterno('La operación solicitada no está disponible.');
        }
    }

    // ==================== Helpers de filtros ====================

    /**
     * Rango de fechas sobre una columna (cualquier tipo temporal; usa DATE()
     * para que un rango de días incluya el día completo con horas).
     * Con $desde/$hasta explícitos se ignora el filtro del usuario
     * (se usa para los contadores "del mes").
     */
    private function filtroFechas(array &$where, array &$params, string $columna, ?string $desde = null, ?string $hasta = null): void
    {
        $desde = $desde ?? $this->__get('fecha_inicio');
        $hasta = $hasta ?? $this->__get('fecha_fin');
        if ($desde !== null) {
            $where[] = "DATE({$columna}) >= :f_desde";
            $params['f_desde'] = $desde;
        }
        if ($hasta !== null) {
            $where[] = "DATE({$columna}) <= :f_hasta";
            $params['f_hasta'] = $hasta;
        }
    }

    /** Filtro exacto genérico: si el atributo viene vacío no se agrega. */
    private function filtro(array &$where, array &$params, string $atributo, string $columna): void
    {
        $valor = $this->__get($atributo);
        if ($valor === null || $valor === '') {
            return;
        }
        $where[] = "{$columna} = :f_{$atributo}";
        $params['f_' . $atributo] = $valor;
    }

    /** El estado admitido depende del reporte: se valida contra su catálogo. */
    private function validarEstado(array $permitidos): void
    {
        $estado = $this->__get('estado');
        if ($estado !== null && !in_array($estado, $permitidos, true)) {
            throw ExcepcionApi::validacion(
                'El estado "' . $estado . '" no es válido. Opciones: ' . implode(', ', $permitidos) . '.'
            );
        }
    }

    private function clausulaWhere(array $where): string
    {
        return $where === [] ? '' : ' WHERE 1=1 AND ' . implode(' AND ', $where);
    }

    /**
     * WHERE con una condición extra (ej: "solo Becas"). Se usa cuando varios
     * contadores comparten los mismos filtros base.
     */
    private function clausulaCon(array $where, string $condicion): string
    {
        return $this->clausulaWhere(array_merge($where, [$condicion]));
    }

    private function limite(): int
    {
        return (int) ($this->__get('limit') ?? self::LIMITE_DEFECTO);
    }

    private function asegurarRangoFechas(): void
    {
        $desde = $this->__get('fecha_inicio');
        $hasta = $this->__get('fecha_fin');
        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            throw ExcepcionApi::validacion('La fecha inicial no puede ser mayor que la fecha final.');
        }
    }

    /** Primer y último día del mes en curso (para los contadores "del mes"). */
    private static function rangoMesActual(): array
    {
        return [date('Y-m-01'), date('Y-m-t')];
    }

    private function esAdmin(): bool
    {
        $tipo = $_SESSION['tipo_empleado'] ?? '';
        return strpos(strtolower($tipo), 'administrador') !== false
            || strpos(strtolower($tipo), 'superusuario') !== false;
    }

    private function idEmpleadoSesion(): int
    {
        return (int) ($_SESSION['id_empleado'] ?? 0);
    }

    /**
     * Aplica el alcance confidencial por empleado si el usuario no es Admin/Superusuario.
     */
    private function filtroEmpleado(array &$where, array &$params, string $columna = 'ss.id_empleado'): void
    {
        if (!$this->esAdmin()) {
            $where[] = "{$columna} = :f_id_empleado_sesion";
            $params['f_id_empleado_sesion'] = $this->idEmpleadoSesion();
        }
    }

    // ==================== Helpers de ejecución ====================

    /**
     * Ejecuta una consulta de filas. Los parámetros se vinculan como texto
     * (son valores saneados en __set) salvo :limit, siempre PARAM_INT.
     */
    private function consultar(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $clave => $valor) {
                $stmt->bindValue(':' . $clave, $valor, PDO::PARAM_STR);
            }
            if (strpos($sql, ':limit') !== false) {
                $stmt->bindValue(':limit', $this->limite(), PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('ReportesModel::consultar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo generar el reporte solicitado.');
        }
    }

    /** Ejecuta un conteo (COUNT(*) u operación escalar) y devuelve un entero. */
    private function contar(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $clave => $valor) {
                $stmt->bindValue(':' . $clave, $valor, PDO::PARAM_STR);
            }
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return $valor === false || $valor === null ? 0 : (int) $valor;
        } catch (Throwable $e) {
            error_log('ReportesModel::contar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron calcular las estadísticas del reporte.');
        }
    }

    /** Devuelve la primera fila de una consulta escalar (o [] si no hay filas). */
    private function fila(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $clave => $valor) {
                $stmt->bindValue(':' . $clave, $valor, PDO::PARAM_STR);
            }
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila === false ? [] : $fila;
        } catch (Throwable $e) {
            error_log('ReportesModel::fila - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron calcular las estadísticas del reporte.');
        }
    }

    // ==================== Validaciones de valores ====================

    private function validarFecha($valor, string $nombre): ?string
    {
        $fecha = trim((string) $valor);
        if ($fecha === '') {
            return null;
        }
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $partes)
            || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            throw ExcepcionApi::validacion("El filtro '{$nombre}' debe ser una fecha válida (AAAA-MM-DD).");
        }
        return $fecha;
    }

    private function validarId($valor, string $nombre): ?int
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }
        $id = filter_var($texto, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            throw ExcepcionApi::validacion("El filtro '{$nombre}' debe ser un identificador válido.");
        }
        return (int) $id;
    }

    private function validarLista($valor, array $permitidos, string $etiqueta): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }
        if (!in_array($texto, $permitidos, true)) {
            throw ExcepcionApi::validacion(
                "El filtro {$etiqueta} no es válido. Opciones: " . implode(', ', $permitidos) . '.'
            );
        }
        return $texto;
    }

    private function validarLimit($valor): int
    {
        $texto = trim((string) ($valor ?? ''));
        if ($texto === '') {
            return self::LIMITE_DEFECTO;
        }
        $limit = filter_var($texto, FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1) {
            throw ExcepcionApi::validacion('El límite de registros debe ser un número entero mayor que cero.');
        }
        return min((int) $limit, self::LIMITE_MAXIMO);
    }

    private function texto($valor, string $nombre): ?string
    {
        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }
        $texto = str_replace(['<', '>'], '', $texto);
        if (mb_strlen($texto) > 60) {
            throw ExcepcionApi::validacion("El filtro '{$nombre}' no puede superar los 60 caracteres.");
        }
        return $texto;
    }

    // ==================== 1. Reporte General ====================

    /**
     * Consolidado de atenciones de los 7 servicios (Becas, Exoneración,
     * FAMES, Medicina, Orientación, Discapacidad y Psicología).
     * Se envuelve en un subquery para filtrar de forma homogénea.
     */
    private function subqueryGeneral(): string
    {
        // [área visible, tabla real, alias, columna de fecha]
        $ramas = [
            ['Becas', 'becas', 'bp', 'bp.fecha_creacion'],
            ['Exoneración', 'exoneracion', 'ep', 'ep.fecha_creacion'],
            ['FAMES', 'fames', 'fp', 'fp.fecha_creacion'],
            ['Medicina', 'consulta_medica', 'mp', 'mp.fecha_creacion'],
            ['Orientación', 'orientacion', 'op', 'op.fecha_creacion'],
            ['Discapacidad', 'discapacidad', 'dp', 'dp.fecha_creacion'],
            ['Psicología', 'consulta_psicologica', 'cp', 'cp.fecha_creacion'],
        ];

        $filtroEmp = !$this->esAdmin() ? ' AND ss.id_empleado = ' . $this->idEmpleadoSesion() : '';

        $consultas = [];
        foreach ($ramas as [$area, $tabla, $alias, $fecha]) {
            $consultas[] = "
                SELECT
                    b.id_beneficiario,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    '{$area}' AS area,
                    DATE({$fecha}) AS fecha
                FROM {$tabla} {$alias}
                JOIN solicitud_de_servicio ss ON {$alias}.id_solicitud_serv = ss.id_solicitud_serv
                JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
                WHERE {$fecha} IS NOT NULL{$filtroEmp}";
        }

        return implode(' UNION ALL ', $consultas);
    }

    private function getReportDataGeneral(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'genero', 't.genero');
        $this->filtro($where, $params, 'pnf', 't.id_pnf');
        $this->filtro($where, $params, 'area', 't.area');

        $sql = 'SELECT * FROM (' . $this->subqueryGeneral() . ') t'
             . $this->clausulaWhere($where)
             . ' ORDER BY t.fecha DESC, t.apellidos ASC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 2. Reporte Medicina ====================

    private function getReportDataMedicina(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'cm.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');

        $sql = "SELECT
                    cm.id_consulta_med,
                    cm.fecha_creacion AS fecha,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    cm.motivo_visita AS motivo,
                    cm.diagnostico,
                    cm.tratamiento
                FROM consulta_medica cm
                JOIN solicitud_de_servicio ss ON cm.id_solicitud_serv = ss.id_solicitud_serv
                JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf"
             . $this->clausulaWhere($where)
             . ' ORDER BY cm.fecha_creacion DESC, cm.id_consulta_med DESC LIMIT :limit';

        $consultas = $this->consultar($sql, $params);

        // Inventario de insumos (reporte auxiliar de Medicina).
        $whereInsumos = [];
        $paramsInsumos = [];
        $this->filtroFechas($whereInsumos, $paramsInsumos, 'i.fecha_creacion');

        $sqlInsumos = "SELECT
                    i.id_insumo,
                    i.nombre_insumo,
                    i.tipo_insumo,
                    pi.nombre_presentacion AS presentacion,
                    i.cantidad,
                    i.fecha_vencimiento,
                    CASE WHEN i.fecha_vencimiento < CURDATE() THEN 'Vencido' ELSE i.estatus END AS estatus
                FROM insumos i
                LEFT JOIN presentacion_insumo pi ON i.id_presentacion = pi.id_presentacion"
             . $this->clausulaWhere($whereInsumos)
             . ' ORDER BY i.fecha_vencimiento ASC, i.nombre_insumo ASC LIMIT :limit';

        $insumos = $this->consultar($sqlInsumos, $paramsInsumos);

        return ['consultas' => $consultas, 'insumos' => $insumos];
    }

    // ==================== 3. Reporte Psicología ====================

    private function getReportDataPsicologia(): array
    {
        $this->validarEstado(self::ESTADOS_CITA);

        // 3a. Morbilidad (consultas psicológicas).
        $whereMorb = [];
        $paramsMorb = [];
        $this->filtroFechas($whereMorb, $paramsMorb, 'cp.fecha_creacion');
        $this->filtro($whereMorb, $paramsMorb, 'pnf', 'b.id_pnf');
        $this->filtro($whereMorb, $paramsMorb, 'tipo_consulta', 'cp.tipo_consulta');
        $this->filtroEmpleado($whereMorb, $paramsMorb, 'ss.id_empleado');

        $sqlMorb = "SELECT
                    cp.id_psicologia,
                    DATE(cp.fecha_creacion) AS fecha,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    cp.tipo_consulta,
                    cp.diagnostico
                FROM consulta_psicologica cp
                JOIN solicitud_de_servicio ss ON cp.id_solicitud_serv = ss.id_solicitud_serv
                JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf"
             . $this->clausulaWhere($whereMorb)
             . ' ORDER BY cp.fecha_creacion DESC, cp.id_psicologia DESC LIMIT :limit';

        // 3b. Citas.
        $whereCitas = [];
        $paramsCitas = [];
        $this->filtroFechas($whereCitas, $paramsCitas, 'c.fecha');
        $this->filtro($whereCitas, $paramsCitas, 'pnf', 'b.id_pnf');
        $this->filtro($whereCitas, $paramsCitas, 'estado', 'ec.nombre');
        $this->filtroEmpleado($whereCitas, $paramsCitas, 'c.id_empleado');

        $sqlCitas = "SELECT
                    c.id_cita,
                    c.fecha,
                    TIME_FORMAT(c.hora, '%H:%i') AS hora,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    ec.nombre AS estado,
                    CONCAT(e.nombre, ' ', e.apellido) AS psicologo
                FROM cita c
                JOIN beneficiario b ON c.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
                LEFT JOIN estado_cita ec ON ec.id_estado = c.estatus
                LEFT JOIN dirpoles_security.empleado e ON e.id_empleado = c.id_empleado"
             . $this->clausulaWhere($whereCitas)
             . ' ORDER BY c.fecha DESC, c.hora DESC, c.id_cita DESC LIMIT :limit';

        return [
            'morbilidad' => $this->consultar($sqlMorb, $paramsMorb),
            'citas' => $this->consultar($sqlCitas, $paramsCitas),
        ];
    }

    // ==================== 4. Reporte Orientación ====================

    private function getReportDataOrientacion(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'o.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');

        $sql = "SELECT
                    o.id_orientacion,
                    o.fecha_creacion AS fecha,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    o.motivo_orientacion AS motivo_consulta,
                    o.descripcion_orientacion AS descripcion_caso,
                    o.indicaciones_orientacion AS indicaciones
                FROM orientacion o
                JOIN solicitud_de_servicio ss ON o.id_solicitud_serv = ss.id_solicitud_serv
                JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf"
             . $this->clausulaWhere($where)
             . ' ORDER BY o.fecha_creacion DESC, o.id_orientacion DESC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 5. Reporte Trabajo Social ====================

    /**
     * Becas, Exoneraciones, FAMES y Gestión de Embarazo.
     * Cada rama aporta su submódulo y un detalle legible.
     */
    private function subqueryTrabajoSocial(): string
    {
        $filtroEmp = !$this->esAdmin() ? ' AND ss.id_empleado = ' . $this->idEmpleadoSesion() : '';
        return "
            SELECT
                'Becas' AS submodulo,
                DATE(bec.fecha_creacion) AS fecha,
                b.id_beneficiario,
                b.nombres, b.apellidos,
                CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                b.genero, b.id_pnf, pnf.nombre_pnf,
                CONCAT('Banco: ', bec.tipo_banco) AS detalle_extra
            FROM becas bec
            JOIN solicitud_de_servicio ss ON bec.id_solicitud_serv = ss.id_solicitud_serv
            JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
            LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
            WHERE 1=1{$filtroEmp}

            UNION ALL

            SELECT
                'Exoneración',
                DATE(exo.fecha_creacion),
                b.id_beneficiario,
                b.nombres, b.apellidos,
                CONCAT(b.tipo_cedula, '-', b.cedula),
                b.genero, b.id_pnf, pnf.nombre_pnf,
                CONCAT('Motivo: ', COALESCE(exo.motivo, 'No indicado'))
            FROM exoneracion exo
            JOIN solicitud_de_servicio ss ON exo.id_solicitud_serv = ss.id_solicitud_serv
            JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
            LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
            WHERE 1=1{$filtroEmp}

            UNION ALL

            SELECT
                'FAMES',
                DATE(fam.fecha_creacion),
                b.id_beneficiario,
                b.nombres, b.apellidos,
                CONCAT(b.tipo_cedula, '-', b.cedula),
                b.genero, b.id_pnf, pnf.nombre_pnf,
                CONCAT('Ayuda: ', fam.tipo_ayuda)
            FROM fames fam
            JOIN solicitud_de_servicio ss ON fam.id_solicitud_serv = ss.id_solicitud_serv
            JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
            LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
            WHERE 1=1{$filtroEmp}

            UNION ALL

            SELECT
                'Gestión Embarazo',
                DATE(emb.fecha_creacion),
                b.id_beneficiario,
                b.nombres, b.apellidos,
                CONCAT(b.tipo_cedula, '-', b.cedula),
                b.genero, b.id_pnf, pnf.nombre_pnf,
                CONCAT('Semanas de gestación: ', emb.semanas_gest)
            FROM gestion_emb emb
            JOIN solicitud_de_servicio ss ON emb.id_solicitud_serv = ss.id_solicitud_serv
            JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
            LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf
            WHERE 1=1{$filtroEmp}";
    }

    private function getReportDataTrabajoSocial(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'pnf', 't.id_pnf');
        $this->filtro($where, $params, 'submodulo', 't.submodulo');

        $sql = 'SELECT * FROM (' . $this->subqueryTrabajoSocial() . ') t'
             . $this->clausulaWhere($where)
             . ' ORDER BY t.fecha DESC, t.submodulo ASC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 6. Reporte Discapacidad ====================

    private function getReportDataDiscapacidad(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'd.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtro($where, $params, 'tipo_discapacidad', 'd.tipo_discapacidad');
        $this->filtro($where, $params, 'grado', 'd.grado');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');

        $sql = "SELECT
                    d.id_discapacidad,
                    d.fecha_creacion AS fecha,
                    b.nombres,
                    b.apellidos,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    b.genero,
                    b.id_pnf,
                    pnf.nombre_pnf,
                    d.tipo_discapacidad,
                    d.grado,
                    d.requiere_asistencia,
                    d.carnet_discapacidad
                FROM discapacidad d
                JOIN solicitud_de_servicio ss ON d.id_solicitud_serv = ss.id_solicitud_serv
                JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario
                LEFT JOIN pnf ON b.id_pnf = pnf.id_pnf"
             . $this->clausulaWhere($where)
             . ' ORDER BY d.fecha_creacion DESC, d.id_discapacidad DESC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 7. Reporte Referencias ====================

    private function getReportDataReferencias(): array
    {
        $this->validarEstado(self::ESTADOS_REFERENCIA);

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'r.fecha_referencia');
        $this->filtro($where, $params, 'estado', 'r.estado');
        $this->filtro($where, $params, 'servicio_destino', 'r.id_servicio_destino');

        $sql = "SELECT
                    r.id_referencia,
                    r.fecha_referencia AS fecha,
                    r.estado,
                    b.nombres AS nombres_benef,
                    b.apellidos AS apellidos_benef,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula_benef,
                    so.nombre_serv AS servicio_origen,
                    sd.nombre_serv AS servicio_destino,
                    r.motivo
                FROM referencias r
                JOIN beneficiario b ON r.id_beneficiario = b.id_beneficiario
                JOIN servicio so ON r.id_servicio_origen = so.id_servicios
                JOIN servicio sd ON r.id_servicio_destino = sd.id_servicios"
             . $this->clausulaWhere($where)
             . ' ORDER BY r.fecha_referencia DESC, r.id_referencia DESC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 8. Reporte Jornadas Médicas ====================

    private function getReportDataJornadas(): array
    {
        $this->validarEstado(self::ESTADOS_JORNADA);

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'j.fecha_inicio');
        $this->filtro($where, $params, 'estado', 'j.estatus');

        $sql = "SELECT
                    j.id_jornada,
                    j.nombre_jornada,
                    j.tipo_jornada,
                    j.ubicacion,
                    j.fecha_inicio,
                    j.fecha_fin,
                    j.estatus,
                    j.aforo_maximo,
                    (SELECT COUNT(*) FROM jornada_beneficiarios jb WHERE jb.id_jornada = j.id_jornada) AS total_asistentes,
                    (SELECT COUNT(*) FROM jornada_diagnosticos jd
                        JOIN jornada_beneficiarios jb2 ON jd.id_jornada_beneficiario = jb2.id_jornada_beneficiario
                        WHERE jb2.id_jornada = j.id_jornada) AS total_diagnosticos
                FROM jornadas_medicas j"
             . $this->clausulaWhere($where)
             . ' ORDER BY j.fecha_inicio DESC, j.id_jornada DESC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 9. Reporte Mobiliario y Equipos ====================

    private function subqueryMobiliario(): string
    {
        return "
            SELECT
                m.id_mobiliario,
                CONCAT(
                    COALESCE(tm.nombre, 'Sin tipo'),
                    ' — ', COALESCE(NULLIF(m.marca, ''), 'sin marca'),
                    ' ', COALESCE(NULLIF(m.modelo, ''), 'sin modelo')
                ) AS nombre_item,
                'Mobiliario' AS tipo_bien,
                COALESCE(tm.nombre, 'Sin tipo') AS categoria,
                m.cantidad,
                m.estatus,
                DATE(m.fecha_registro) AS fecha
            FROM mobiliario m
            LEFT JOIN tipo_mobiliario tm ON m.id_tipo_mobiliario = tm.id_tipo_mobiliario

            UNION ALL

            SELECT
                eq.id_equipo,
                CONCAT(
                    COALESCE(te.nombre, 'Sin tipo'),
                    ' — ', COALESCE(NULLIF(eq.marca, ''), 'sin marca'),
                    ' ', COALESCE(NULLIF(eq.modelo, ''), 'sin modelo'),
                    ' (', COALESCE(NULLIF(eq.serial, ''), 'sin serial'), ')'
                ) AS nombre_item,
                'Equipo',
                COALESCE(te.nombre, 'Sin tipo'),
                1,
                eq.estatus,
                DATE(eq.fecha_registro)
            FROM equipos eq
            LEFT JOIN tipo_equipo te ON eq.id_tipo_equipo = te.id_tipo_equipo";
    }

    private function getReportDataMobiliario(): array
    {
        $this->validarEstado(self::ESTADOS_INVENTARIO);

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'tipo_bien', 't.tipo_bien');
        $this->filtro($where, $params, 'estado', 't.estatus');

        $sql = 'SELECT * FROM (' . $this->subqueryMobiliario() . ') t'
             . $this->clausulaWhere($where)
             . ' ORDER BY t.tipo_bien ASC, t.nombre_item ASC LIMIT :limit';

        return $this->consultar($sql, $params);
    }

    // ==================== 10. Reporte Transporte ====================

    private function getReportDataTransporte(): array
    {
        $seccion = $this->__get('seccion_transporte');
        $limit = $this->__get('limit') ?? self::LIMITE_DEFECTO;

        // 1. Vehículos
        $vehiculos = [];
        if (!$seccion || $seccion === 'Vehículos') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'v.fecha_adquisicion');
            $this->filtro($where, $params, 'tipo_vehiculo', 'v.tipo');
            $this->filtro($where, $params, 'estado', 'v.estado');
            $sql = "SELECT v.id_vehiculo, v.placa, v.modelo, v.tipo, v.estado, v.fecha_adquisicion,
                           (SELECT COUNT(*) FROM asignaciones_rutas ar WHERE ar.id_vehiculo = v.id_vehiculo AND ar.estatus = 'Activa') AS asignaciones_activas,
                           (SELECT COUNT(*) FROM mantenimiento_vehiculos mv WHERE mv.id_vehiculo = v.id_vehiculo) AS total_mantenimientos
                    FROM vehiculos v" . $this->clausulaWhere($where) . " ORDER BY v.placa ASC LIMIT :limit";
            $params['limit'] = $limit;
            $vehiculos = $this->consultar($sql, $params);
        }

        // 2. Rutas
        $rutas = [];
        if (!$seccion || $seccion === 'Rutas') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'r.fecha_creacion');
            $this->filtro($where, $params, 'estado', 'r.estatus');
            $sql = "SELECT r.id_ruta, r.nombre_ruta, r.tipo_ruta, r.punto_partida, r.punto_destino, r.estatus, r.fecha_creacion,
                           (SELECT COUNT(*) FROM asignaciones_rutas ar WHERE ar.id_ruta = r.id_ruta AND ar.estatus = 'Activa') AS asignaciones_activas
                    FROM rutas r" . $this->clausulaWhere($where) . " ORDER BY r.nombre_ruta ASC LIMIT :limit";
            $params['limit'] = $limit;
            $rutas = $this->consultar($sql, $params);
        }

        // 3. Proveedores
        $proveedores = [];
        if (!$seccion || $seccion === 'Proveedores') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'p.fecha_creacion');
            $this->filtro($where, $params, 'estado', 'p.estatus');
            $sql = "SELECT p.id_proveedor, p.nombre, p.tipo_documento, p.num_documento, p.telefono, p.correo, p.estatus, p.fecha_creacion
                    FROM proveedores p" . $this->clausulaWhere($where) . " ORDER BY p.nombre ASC LIMIT :limit";
            $params['limit'] = $limit;
            $proveedores = $this->consultar($sql, $params);
        }

        // 4. Repuestos
        $repuestos = [];
        if (!$seccion || $seccion === 'Repuestos') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'rp.fecha_creacion');
            $this->filtro($where, $params, 'estado', 'rp.estatus');
            $sql = "SELECT rp.id_repuesto, rp.nombre, rp.cantidad, rp.estatus, rp.fecha_creacion, p.nombre AS proveedor
                    FROM repuestos_vehiculos rp
                    LEFT JOIN proveedores p ON rp.id_proveedor = p.id_proveedor"
                   . $this->clausulaWhere($where) . " ORDER BY rp.nombre ASC LIMIT :limit";
            $params['limit'] = $limit;
            $repuestos = $this->consultar($sql, $params);
        }

        // 5. Asignaciones
        $asignaciones = [];
        if (!$seccion || $seccion === 'Asignaciones') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'ar.fecha_asignacion');
            $this->filtro($where, $params, 'estado', 'ar.estatus');
            $sql = "SELECT ar.id_asignacion, ar.fecha_asignacion, ar.estatus,
                           r.nombre_ruta, v.placa, v.modelo,
                           CONCAT(e.nombre, ' ', e.apellido) AS chofer, e.cedula AS cedula_chofer
                    FROM asignaciones_rutas ar
                    JOIN rutas r ON ar.id_ruta = r.id_ruta
                    JOIN vehiculos v ON ar.id_vehiculo = v.id_vehiculo
                    LEFT JOIN dirpoles_security.empleado e ON ar.id_empleado = e.id_empleado"
                   . $this->clausulaWhere($where) . " ORDER BY ar.fecha_asignacion DESC LIMIT :limit";
            $params['limit'] = $limit;
            $asignaciones = $this->consultar($sql, $params);
        }

        // 6. Mantenimientos
        $mantenimientos = [];
        if (!$seccion || $seccion === 'Mantenimientos') {
            $where = []; $params = [];
            $this->filtroFechas($where, $params, 'mv.fecha');
            $sql = "SELECT mv.id_mantenimiento, mv.tipo, mv.fecha, mv.descripcion,
                           v.placa, v.modelo, v.tipo AS tipo_vehiculo
                    FROM mantenimiento_vehiculos mv
                    JOIN vehiculos v ON mv.id_vehiculo = v.id_vehiculo"
                   . $this->clausulaWhere($where) . " ORDER BY mv.fecha DESC LIMIT :limit";
            $params['limit'] = $limit;
            $mantenimientos = $this->consultar($sql, $params);
        }

        return [
            'vehiculos'      => $vehiculos,
            'rutas'          => $rutas,
            'proveedores'    => $proveedores,
            'repuestos'      => $repuestos,
            'asignaciones'   => $asignaciones,
            'mantenimientos' => $mantenimientos,
        ];
    }

    // ==================== Estadísticas (tarjetas data-stat) ====================

    private function getStats(): array
    {
        $reporte = $this->__get('reporte');
        $mapa = [
            'general' => 'statsGeneral',
            'medicina' => 'statsMedicina',
            'psicologia' => 'statsPsicologia',
            'orientacion' => 'statsOrientacion',
            'trabajo_social' => 'statsTrabajoSocial',
            'discapacidad' => 'statsDiscapacidad',
            'referencias' => 'statsReferencias',
            'jornadas' => 'statsJornadas',
            'mobiliario' => 'statsMobiliario',
            'transporte' => 'statsTransporte',
        ];

        if ($reporte === null || !isset($mapa[$reporte])) {
            throw ExcepcionApi::validacion('El reporte solicitado no existe.');
        }

        $metodo = $mapa[$reporte];
        return $this->{$metodo}();
    }

    private function statsGeneral(): array
    {
        $subquery = '(' . $this->subqueryGeneral() . ') t';

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'genero', 't.genero');
        $this->filtro($where, $params, 'pnf', 't.id_pnf');
        $this->filtro($where, $params, 'area', 't.area');
        $clausula = $this->clausulaWhere($where);

        $total = $this->contar("SELECT COUNT(*) FROM {$subquery}{$clausula}", $params);

        [$mesIni, $mesFin] = self::rangoMesActual();
        $whereMes = [];
        $paramsMes = [];
        $this->filtroFechas($whereMes, $paramsMes, 't.fecha', $mesIni, $mesFin);
        $this->filtro($whereMes, $paramsMes, 'genero', 't.genero');
        $this->filtro($whereMes, $paramsMes, 'pnf', 't.id_pnf');
        $this->filtro($whereMes, $paramsMes, 'area', 't.area');
        $mes = $this->contar("SELECT COUNT(*) FROM {$subquery}" . $this->clausulaWhere($whereMes), $paramsMes);

        $genero = $this->fila(
            "SELECT COUNT(CASE WHEN t.genero = 'F' THEN 1 END) AS mujeres,
                    COUNT(CASE WHEN t.genero = 'M' THEN 1 END) AS hombres
             FROM {$subquery}{$clausula}",
            $params
        );

        return [
            'atenciones_total' => $total,
            'atenciones_mes' => $mes,
            'mujeres' => (int) ($genero['mujeres'] ?? 0),
            'hombres' => (int) ($genero['hombres'] ?? 0),
        ];
    }

    private function statsMedicina(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'cm.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');

        $from = " FROM consulta_medica cm
                 JOIN solicitud_de_servicio ss ON cm.id_solicitud_serv = ss.id_solicitud_serv
                 JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario"
              . $this->clausulaWhere($where);

        $total = $this->contar('SELECT COUNT(*)' . $from, $params);

        [$mesIni, $mesFin] = self::rangoMesActual();
        $whereMes = [];
        $paramsMes = [];
        $this->filtroFechas($whereMes, $paramsMes, 'cm.fecha_creacion', $mesIni, $mesFin);
        $this->filtro($whereMes, $paramsMes, 'genero', 'b.genero');
        $this->filtro($whereMes, $paramsMes, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($whereMes, $paramsMes, 'ss.id_empleado');
        $mes = $this->contar('SELECT COUNT(*)' . ' FROM consulta_medica cm
                 JOIN solicitud_de_servicio ss ON cm.id_solicitud_serv = ss.id_solicitud_serv
                 JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
                 . $this->clausulaWhere($whereMes), $paramsMes);

        $whereInsumos = [];
        $paramsInsumos = [];
        $this->filtroFechas($whereInsumos, $paramsInsumos, 'i.fecha_creacion');
        $insumos = $this->contar(
            'SELECT COUNT(*) FROM insumos i' . $this->clausulaWhere($whereInsumos),
            $paramsInsumos
        );

        $porVencer = $this->contar(
            "SELECT COUNT(*) FROM insumos i
             WHERE i.fecha_vencimiento >= CURDATE()
               AND i.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
               AND i.estatus = 'Disponible'"
        );

        return [
            'consultas_total' => $total,
            'consultas_mes' => $mes,
            'insumos_total' => $insumos,
            'insumos_por_vencer' => $porVencer,
        ];
    }

    private function statsPsicologia(): array
    {
        $this->validarEstado(self::ESTADOS_CITA);

        $whereMorb = [];
        $paramsMorb = [];
        $this->filtroFechas($whereMorb, $paramsMorb, 'cp.fecha_creacion');
        $this->filtro($whereMorb, $paramsMorb, 'pnf', 'b.id_pnf');
        $this->filtro($whereMorb, $paramsMorb, 'tipo_consulta', 'cp.tipo_consulta');
        $this->filtroEmpleado($whereMorb, $paramsMorb, 'ss.id_empleado');

        $morbilidad = $this->contar(
            'SELECT COUNT(*) FROM consulta_psicologica cp
             JOIN solicitud_de_servicio ss ON cp.id_solicitud_serv = ss.id_solicitud_serv
             JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
            . $this->clausulaWhere($whereMorb),
            $paramsMorb
        );

        $whereCitas = [];
        $paramsCitas = [];
        $this->filtroFechas($whereCitas, $paramsCitas, 'c.fecha');
        $this->filtro($whereCitas, $paramsCitas, 'pnf', 'b.id_pnf');
        $this->filtro($whereCitas, $paramsCitas, 'estado', 'ec.nombre');
        $this->filtroEmpleado($whereCitas, $paramsCitas, 'c.id_empleado');

        $fromCitas = " FROM cita c
                       JOIN beneficiario b ON c.id_beneficiario = b.id_beneficiario
                       LEFT JOIN estado_cita ec ON ec.id_estado = c.estatus"
                   . $this->clausulaWhere($whereCitas);

        $citas = $this->contar('SELECT COUNT(*)' . $fromCitas, $paramsCitas);
        $atendidas = $this->contar(
            'SELECT COUNT(*)' . $this->clausulaCon($whereCitas, "ec.nombre = 'Atendida'"),
            $paramsCitas
        );
        $pendientes = $this->contar(
            'SELECT COUNT(*)' . $this->clausulaCon($whereCitas, "ec.nombre = 'Pendiente'"),
            $paramsCitas
        );

        return [
            'morbilidad_total' => $morbilidad,
            'citas_total' => $citas,
            'citas_atendidas' => $atendidas,
            'citas_pendientes' => $pendientes,
        ];
    }

    private function statsOrientacion(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'o.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');

        $from = " FROM orientacion o
                 JOIN solicitud_de_servicio ss ON o.id_solicitud_serv = ss.id_solicitud_serv
                 JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario"
              . $this->clausulaWhere($where);

        $total = $this->contar('SELECT COUNT(*)' . $from, $params);

        [$mesIni, $mesFin] = self::rangoMesActual();
        $whereMes = [];
        $paramsMes = [];
        $this->filtroFechas($whereMes, $paramsMes, 'o.fecha_creacion', $mesIni, $mesFin);
        $this->filtro($whereMes, $paramsMes, 'genero', 'b.genero');
        $this->filtro($whereMes, $paramsMes, 'pnf', 'b.id_pnf');
        $this->filtroEmpleado($whereMes, $paramsMes, 'ss.id_empleado');
        $mes = $this->contar(
            'SELECT COUNT(*) FROM orientacion o
             JOIN solicitud_de_servicio ss ON o.id_solicitud_serv = ss.id_solicitud_serv
             JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
            . $this->clausulaWhere($whereMes),
            $paramsMes
        );

        $genero = $this->fila(
            "SELECT COUNT(CASE WHEN b.genero = 'F' THEN 1 END) AS mujeres,
                    COUNT(CASE WHEN b.genero = 'M' THEN 1 END) AS hombres" . $from,
            $params
        );

        return [
            'orientaciones_total' => $total,
            'orientaciones_mes' => $mes,
            'mujeres' => (int) ($genero['mujeres'] ?? 0),
            'hombres' => (int) ($genero['hombres'] ?? 0),
        ];
    }

    private function statsTrabajoSocial(): array
    {
        $subquery = '(' . $this->subqueryTrabajoSocial() . ') t';

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'pnf', 't.id_pnf');
        $this->filtro($where, $params, 'submodulo', 't.submodulo');
        $clausula = $this->clausulaWhere($where);

        $total = $this->contar("SELECT COUNT(*) FROM {$subquery}{$clausula}", $params);

        [$mesIni, $mesFin] = self::rangoMesActual();
        $whereMes = [];
        $paramsMes = [];
        $this->filtroFechas($whereMes, $paramsMes, 't.fecha', $mesIni, $mesFin);
        $this->filtro($whereMes, $paramsMes, 'pnf', 't.id_pnf');
        $this->filtro($whereMes, $paramsMes, 'submodulo', 't.submodulo');
        $mes = $this->contar("SELECT COUNT(*) FROM {$subquery}" . $this->clausulaWhere($whereMes), $paramsMes);

        $becas = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.submodulo = 'Becas'"),
            $params
        );
        $exoneraciones = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.submodulo = 'Exoneración'"),
            $params
        );

        return [
            'registros_total' => $total,
            'registros_mes' => $mes,
            'becas_total' => $becas,
            'exoneraciones_total' => $exoneraciones,
        ];
    }

    private function statsDiscapacidad(): array
    {
        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'd.fecha_creacion');
        $this->filtro($where, $params, 'genero', 'b.genero');
        $this->filtro($where, $params, 'pnf', 'b.id_pnf');
        $this->filtro($where, $params, 'tipo_discapacidad', 'd.tipo_discapacidad');
        $this->filtro($where, $params, 'grado', 'd.grado');
        $this->filtroEmpleado($where, $params, 'ss.id_empleado');
        $clausula = $this->clausulaWhere($where);

        $from = " FROM discapacidad d
                 JOIN solicitud_de_servicio ss ON d.id_solicitud_serv = ss.id_solicitud_serv
                 JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario"
              . $clausula;

        $total = $this->contar('SELECT COUNT(*)' . $from, $params);

        [$mesIni, $mesFin] = self::rangoMesActual();
        $whereMes = [];
        $paramsMes = [];
        $this->filtroFechas($whereMes, $paramsMes, 'd.fecha_creacion', $mesIni, $mesFin);
        $this->filtro($whereMes, $paramsMes, 'genero', 'b.genero');
        $this->filtro($whereMes, $paramsMes, 'pnf', 'b.id_pnf');
        $this->filtro($whereMes, $paramsMes, 'tipo_discapacidad', 'd.tipo_discapacidad');
        $this->filtro($whereMes, $paramsMes, 'grado', 'd.grado');
        $this->filtroEmpleado($whereMes, $paramsMes, 'ss.id_empleado');
        $mes = $this->contar(
            'SELECT COUNT(*) FROM discapacidad d
             JOIN solicitud_de_servicio ss ON d.id_solicitud_serv = ss.id_solicitud_serv
             JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
            . $this->clausulaWhere($whereMes),
            $paramsMes
        );

        $graves = $this->contar(
            'SELECT COUNT(*) FROM discapacidad d
             JOIN solicitud_de_servicio ss ON d.id_solicitud_serv = ss.id_solicitud_serv
             JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
            . $this->clausulaCon($where, "d.grado = 'Grave'"),
            $params
        );

        $conCarnet = $this->contar(
            'SELECT COUNT(*) FROM discapacidad d
             JOIN solicitud_de_servicio ss ON d.id_solicitud_serv = ss.id_solicitud_serv
             JOIN beneficiario b ON ss.id_beneficiario = b.id_beneficiario'
            . $this->clausulaCon($where, "d.carnet_discapacidad IS NOT NULL AND d.carnet_discapacidad <> ''"),
            $params
        );

        return [
            'discapacidad_total' => $total,
            'discapacidad_mes' => $mes,
            'graves' => $graves,
            'con_carnet' => $conCarnet,
        ];
    }

    private function statsReferencias(): array
    {
        $this->validarEstado(self::ESTADOS_REFERENCIA);

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'r.fecha_referencia');
        $this->filtro($where, $params, 'estado', 'r.estado');
        $this->filtro($where, $params, 'servicio_destino', 'r.id_servicio_destino');
        $clausula = $this->clausulaWhere($where);

        $total = $this->contar('SELECT COUNT(*) FROM referencias r' . $clausula, $params);
        $pendientes = $this->contar(
            'SELECT COUNT(*) FROM referencias r' . $this->clausulaCon($where, "r.estado = 'Pendiente'"),
            $params
        );
        $aceptadas = $this->contar(
            'SELECT COUNT(*) FROM referencias r' . $this->clausulaCon($where, "r.estado = 'Aceptada'"),
            $params
        );
        $rechazadas = $this->contar(
            'SELECT COUNT(*) FROM referencias r' . $this->clausulaCon($where, "r.estado = 'Rechazada'"),
            $params
        );

        return [
            'referencias_total' => $total,
            'pendientes' => $pendientes,
            'aceptadas' => $aceptadas,
            'rechazadas' => $rechazadas,
        ];
    }

    private function statsJornadas(): array
    {
        $this->validarEstado(self::ESTADOS_JORNADA);

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 'j.fecha_inicio');
        $this->filtro($where, $params, 'estado', 'j.estatus');
        $clausula = $this->clausulaWhere($where);

        $fila = $this->fila(
            "SELECT COUNT(*) AS total,
                    COUNT(CASE WHEN j.estatus = 'Activa' THEN 1 END) AS activas,
                    COUNT(CASE WHEN j.estatus = 'Finalizada' THEN 1 END) AS finalizadas,
                    COALESCE(SUM((SELECT COUNT(*) FROM jornada_beneficiarios jb
                                  WHERE jb.id_jornada = j.id_jornada)), 0) AS asistentes
             FROM jornadas_medicas j{$clausula}",
            $params
        );

        return [
            'jornadas_total' => (int) ($fila['total'] ?? 0),
            'jornadas_activas' => (int) ($fila['activas'] ?? 0),
            'jornadas_finalizadas' => (int) ($fila['finalizadas'] ?? 0),
            'total_asistentes' => (int) ($fila['asistentes'] ?? 0),
        ];
    }

    private function statsMobiliario(): array
    {
        $this->validarEstado(self::ESTADOS_INVENTARIO);

        $subquery = '(' . $this->subqueryMobiliario() . ') t';

        $where = [];
        $params = [];
        $this->filtroFechas($where, $params, 't.fecha');
        $this->filtro($where, $params, 'tipo_bien', 't.tipo_bien');
        $this->filtro($where, $params, 'estado', 't.estatus');

        $mobiliarios = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.tipo_bien = 'Mobiliario'"),
            $params
        );
        $equipos = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.tipo_bien = 'Equipo'"),
            $params
        );
        $activos = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.estatus = 'Activo'"),
            $params
        );
        $inactivos = $this->contar(
            'SELECT COUNT(*) FROM ' . $subquery . $this->clausulaCon($where, "t.estatus = 'Inactivo'"),
            $params
        );

        return [
            'mobiliario_total' => $mobiliarios,
            'equipos_total' => $equipos,
            'activos' => $activos,
            'inactivos' => $inactivos,
        ];
    }

    private function statsTransporte(): array
    {
        $veh = $this->fila("SELECT COUNT(*) AS total,
                                   COUNT(CASE WHEN estado = 'Activo' THEN 1 END) AS activos,
                                   COUNT(CASE WHEN estado = 'Mantenimiento' THEN 1 END) AS mantenimiento
                            FROM vehiculos");
        $rut = $this->fila("SELECT COUNT(*) AS total,
                                   COUNT(CASE WHEN estatus = 'Activa' THEN 1 END) AS activas
                            FROM rutas");
        $prov = $this->fila("SELECT COUNT(*) AS total FROM proveedores");
        $rep = $this->fila("SELECT COUNT(*) AS total,
                                  COUNT(CASE WHEN cantidad < 5 AND estatus = 'Disponible' THEN 1 END) AS bajo_stock
                           FROM repuestos_vehiculos");
        $asig = $this->fila("SELECT COUNT(*) AS total FROM asignaciones_rutas WHERE estatus = 'Activa'");

        return [
            'vehiculos_total'         => (int) ($veh['total'] ?? 0),
            'vehiculos_activos'       => (int) ($veh['activos'] ?? 0),
            'vehiculos_mantenimiento' => (int) ($veh['mantenimiento'] ?? 0),
            'rutas_total'             => (int) ($rut['total'] ?? 0),
            'rutas_activas'           => (int) ($rut['activas'] ?? 0),
            'proveedores_total'       => (int) ($prov['total'] ?? 0),
            'repuestos_total'         => (int) ($rep['total'] ?? 0),
            'repuestos_bajo_stock'    => (int) ($rep['bajo_stock'] ?? 0),
            'asignaciones_activas'    => (int) ($asig['total'] ?? 0),
        ];
    }

    // ==================== Catálogos para los selects ====================

    private function getCatalogos(): array
    {
        $pnfs = $this->consultar(
            'SELECT id_pnf AS id, nombre_pnf AS nombre FROM pnf WHERE estatus = 1 ORDER BY nombre_pnf'
        );
        $servicios = $this->consultar(
            'SELECT id_servicios AS id, nombre_serv AS nombre FROM servicio WHERE estatus = 1 ORDER BY nombre_serv'
        );
        $estadosCita = $this->consultar(
            'SELECT id_estado AS id, nombre AS nombre FROM estado_cita WHERE es_activo = 1 ORDER BY nombre'
        );

        return [
            'pnfs' => $pnfs,
            'servicios' => $servicios,
            'estados_cita' => $estadosCita,
            'areas' => self::AREAS_GENERAL,
            'submodulos' => self::SUBMODULOS_TS,
            'tipos_consulta' => self::TIPOS_CONSULTA,
            'tipos_discapacidad' => self::TIPOS_DISCAPACIDAD,
            'grados' => self::GRADOS_DISCAPACIDAD,
            'estados_referencia' => self::ESTADOS_REFERENCIA,
            'estados_jornada' => self::ESTADOS_JORNADA,
            'estados_inventario' => self::ESTADOS_INVENTARIO,
            'estados_vehiculo' => self::ESTADOS_VEHICULO,
            'tipos_vehiculo' => self::TIPOS_VEHICULO,
            'secciones_transporte' => self::SECCIONES_TRANSPORTE,
            'tipos_bien' => self::TIPOS_BIEN,
        ];
    }
}
