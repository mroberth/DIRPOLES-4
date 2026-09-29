<?php
namespace App\Models;

use PDO;
use Throwable;
use DateTime;
use App\Core\ExcepcionApi;

/**
 * JornadaModel — módulo Jornadas (id_modulo 11 = 'Jornadas').
 *
 * Tablas (todas en dirpoles_business):
 *  - jornadas_medicas        cabecera: nombre, tipo, aforo, rango de fechas,
 *                            ubicación, estatus (Activa | Cancelada | Finalizada).
 *  - jornada_beneficiarios   asistentes: NO son solo beneficiarios del sistema
 *                            (también comunidad, profesores, empleados…), así
 *                            que la fila guarda SU copia de los datos y su
 *                            tipo_paciente.
 *  - jornada_diagnosticos    uno o MÁS diagnósticos por asistente, con el
 *                            empleado médico que lo firmó.
 *  - jornada_insumos         insumos usados en cada diagnóstico (descuento de
 *                            stock real en insumos + kardex inventario_medico).
 *
 * Reglas permanentes (decisiones del usuario, 2026-09-29):
 *  - Aforo: cada jornada tiene límite de personas; se respeta con transacción
 *    + FOR UPDATE sobre la cabecera para que dos registros simultáneos no
 *    rebasen el aforo. El aforo NO puede bajar por debajo de los ya registrados.
 *  - Asistentes: alta manual con autocompletado opcional por cédula (el backend
 *    solo busca; quien decide los datos es el usuario). Una misma cédula NO
 *    puede estar dos veces en la MISMA jornada (distintas jornadas sí).
 *  - Asistente solo en jornada 'Activa' y cuya fecha_fin no haya pasado.
 *  - Eliminar asistente: bloqueado si ya tiene diagnóstico o si la jornada no
 *    está Activa (mismo criterio que el sistema viejo).
 *  - Diagnósticos: MUCHOS por asistente (el viejo solo dejaba uno). Se agregan
 *    solo en jornada 'Activa'; el texto se puede corregir después, los INSUMOS
 *    nunca (se agregan al crear, como en Medicina).
 *  - Insumos: transacción + FOR UPDATE sobre insumos, estatus 'Disponible',
 *    sin vencer y con stock; descuento con CASE a 'Agotado' y movimiento
 *    'Salida' en el kardex inventario_medico (patrón Medicina).
 *  - Eliminar diagnóstico NO devuelve stock (el insumo ya se usó), igual que
 *    Medicina; sí borra jornada_insumos por la FK.
 *  - Eliminar jornada: solo si NO tiene asistentes (si no, Cancelarla).
 *  - Sin alcance de datos: quien tiene permiso ve todas las jornadas.
 *  - RBAC: nombre 'jornadas'; permisos en BD ya existentes para roles
 *    2 (Médico), 6 (Administrador) y 10 (Superusuario): NO hay SQL nuevo.
 */
class JornadaModel extends BusinessModel
{
    private const ESTADOS_JORNADA = ['Activa', 'Cancelada', 'Finalizada'];
    private const TIPOS_CEDULA     = ['V', 'E', 'J', 'G'];
    private const GENEROS          = ['Femenino', 'Masculino'];
    private const TIPOS_PACIENTE   = [
        'Estudiante',
        'Personal Obrero',
        'Personal Docente',
        'Personal Administrativo',
        'Comunidad',
    ];
    private const TIPOS_JORNADA = [
        'Médica Integral',
        'Vacunación',
        'Odontológica',
        'Pediátrica',
        'Asistencia Social',
        'Revisión Médica',
    ];
    private const MAX_AFORO         = 5000;   // tope práctico de asistentes
    private const MAX_INSUMOS       = 30;     // insumos distintos por diagnóstico
    private const MAX_NOMBRE        = 100;    // jornadas_medicas.nombre_jornada
    private const MAX_TIPO          = 50;     // jornadas_medicas.tipo_jornada
    private const MAX_UBICACION     = 255;
    private const MAX_DESCRIPCION   = 2000;
    private const MAX_TEXTO         = 2000;   // diagnostico/tratamiento/observaciones

    private array $atributos = [];

    //=== Acceso a atributos ===========================================

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_jornada':
            case 'id_jornada_beneficiario':
            case 'id_jornada_diagnostico':
            case 'id_empleado':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El valor de '{$nombre}' no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'limit':
            case 'offset':
                $this->atributos[$nombre] = max(0, (int) $valor);
                break;

            // ---------------- cabecera de la jornada ----------------
            case 'nombre_jornada':
                $valor = $this->texto($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El nombre de la jornada es obligatorio.');
                }
                if (mb_strlen($valor) < 3 || mb_strlen($valor) > self::MAX_NOMBRE) {
                    throw ExcepcionApi::validacion('El nombre debe tener entre 3 y ' . self::MAX_NOMBRE . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_jornada':
                $valor = $this->texto($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El tipo de jornada es obligatorio.');
                }
                if (mb_strlen($valor) < 3 || mb_strlen($valor) > self::MAX_TIPO) {
                    throw ExcepcionApi::validacion('El tipo de jornada debe tener entre 3 y ' . self::MAX_TIPO . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'aforo_maximo':
                $aforo = filter_var($valor, FILTER_VALIDATE_INT);
                if ($aforo === false || $aforo < 1 || $aforo > self::MAX_AFORO) {
                    throw ExcepcionApi::validacion(
                        'El aforo máximo debe ser un número entero entre 1 y ' . self::MAX_AFORO . '.'
                    );
                }
                $this->atributos[$nombre] = $aforo;
                break;

            case 'fecha_inicio':
            case 'fecha_fin':
                $etiqueta = $nombre === 'fecha_inicio' ? 'el inicio' : 'el cierre';
                $this->atributos[$nombre] = $this->fechaHora($valor, $etiqueta);
                break;

            case 'ubicacion':
                $valor = $this->texto($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La ubicación es obligatoria.');
                }
                if (mb_strlen($valor) < 3 || mb_strlen($valor) > self::MAX_UBICACION) {
                    throw ExcepcionApi::validacion('La ubicación debe tener entre 3 y ' . self::MAX_UBICACION . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'descripcion':
                $valor = $this->texto($valor);
                if ($valor !== '' && mb_strlen($valor) > self::MAX_DESCRIPCION) {
                    throw ExcepcionApi::validacion('La descripción no puede superar ' . self::MAX_DESCRIPCION . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'estatus':
                $valor = $this->texto($valor);
                if (!in_array($valor, self::ESTADOS_JORNADA, true)) {
                    throw ExcepcionApi::validacion('El estatus de la jornada no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            // ---------------- asistente ----------------
            case 'tipo_cedula':
                $valor = mb_strtoupper($this->texto($valor));
                if (!in_array($valor, self::TIPOS_CEDULA, true)) {
                    throw ExcepcionApi::validacion('El tipo de documento no es válido (V, E, J o G).');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'cedula':
                $valor = preg_replace('/\D+/', '', (string) $valor);
                if ($valor === '' || strlen($valor) < 5 || strlen($valor) > 12) {
                    throw ExcepcionApi::validacion('La cédula debe tener entre 5 y 12 dígitos.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'nombres':
            case 'apellidos':
                $valor = $this->texto($valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El campo ' . $nombre . ' es obligatorio.');
                }
                if (mb_strlen($valor) < 2 || mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El campo ' . $nombre . ' debe tener entre 2 y 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'fecha_nacimiento':
                $fecha = $this->texto($valor);
                if ($fecha === '') {
                    throw ExcepcionApi::validacion('La fecha de nacimiento es obligatoria.');
                }
                if (!$this->esFecha($fecha)) {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no es válida.');
                }
                if ($fecha > date('Y-m-d') || $fecha < '1900-01-01') {
                    throw ExcepcionApi::validacion('La fecha de nacimiento no puede ser futura ni anterior a 1900.');
                }
                $this->atributos[$nombre] = $fecha;
                break;

            case 'genero':
                $valor = $this->texto($valor);
                if (!in_array($valor, self::GENEROS, true)) {
                    throw ExcepcionApi::validacion('Debes indicar el género (Femenino o Masculino).');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_paciente':
                $valor = $this->texto($valor);
                if (!in_array($valor, self::TIPOS_PACIENTE, true)) {
                    throw ExcepcionApi::validacion('El tipo de paciente no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'telefono':
                $valor = preg_replace('/\D+/', '', (string) $valor);
                if ($valor === '' || strlen($valor) < 7 || strlen($valor) > 12) {
                    throw ExcepcionApi::validacion('El teléfono debe tener entre 7 y 12 dígitos.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'correo':
                $valor = mb_strtolower($this->texto($valor));
                if ($valor !== '') {
                    if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                        throw ExcepcionApi::validacion('El correo no tiene un formato válido.');
                    }
                    if (mb_strlen($valor) > 100) {
                        throw ExcepcionApi::validacion('El correo no puede superar 100 caracteres.');
                    }
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'direccion':
                $valor = $this->texto($valor);
                if (mb_strlen($valor) > 255) {
                    throw ExcepcionApi::validacion('La dirección no puede superar 255 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            // ---------------- diagnóstico ----------------
            case 'diagnostico':
            case 'tratamiento':
                $valor = $this->texto($valor);
                $campo = $nombre === 'diagnostico' ? 'El diagnóstico' : 'El tratamiento';
                if ($valor === '') {
                    throw ExcepcionApi::validacion($campo . ' es obligatorio.');
                }
                if (mb_strlen($valor) < 2 || mb_strlen($valor) > self::MAX_TEXTO) {
                    throw ExcepcionApi::validacion($campo . ' debe tener entre 2 y ' . self::MAX_TEXTO . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'observaciones':
                $valor = $this->texto($valor);
                if ($valor !== '' && mb_strlen($valor) > self::MAX_TEXTO) {
                    throw ExcepcionApi::validacion('Las observaciones no pueden superar ' . self::MAX_TEXTO . ' caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'insumos':
                if ($valor === null || $valor === '') {
                    $this->atributos['insumos'] = [];
                    break;
                }
                if (!is_array($valor)) {
                    throw ExcepcionApi::validacion('Los insumos deben enviarse como una lista.');
                }
                $this->atributos['insumos'] = $valor;
                break;

            // ---------------- lecturas (sin validación de negocio) ----------------
            case 'tipo_documento':
                $valor = mb_strtoupper($this->texto($valor));
                if (!in_array($valor, self::TIPOS_CEDULA, true)) {
                    throw ExcepcionApi::validacion('El tipo de documento no es válido (V, E, J o G).');
                }
                $this->atributos[$nombre] = $valor;
                break;

            default:
                throw ExcepcionApi::validacion("Atributo no reconocido: '{$nombre}'.");
        }
    }

    public function __get(string $nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    //=== Despachador ==================================================
    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'crear'                  => $this->crear(),
            'listar'                 => $this->listar(),
            'obtener'                => $this->obtener(),
            'actualizar'             => $this->actualizar(),
            'eliminar'               => $this->eliminar(),
            'stats'                  => $this->stats(),
            'catalogos'              => $this->catalogos(),
            'destinatarios'          => $this->destinatarios(),
            'asistentes'             => $this->asistentes(),
            'agregar_asistente'      => $this->agregarAsistente(),
            'eliminar_asistente'     => $this->eliminarAsistente(),
            'diagnosticos'           => $this->diagnosticos(),
            'agregar_diagnostico'    => $this->agregarDiagnostico(),
            'actualizar_diagnostico' => $this->actualizarDiagnostico(),
            'eliminar_diagnostico'   => $this->eliminarDiagnostico(),
            'buscar_persona'         => $this->buscarPersona(),
            'insumos_disponibles'    => $this->insumosDisponibles(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en JornadaModel: '{$accion}'."),
        };
    }

    //=== Helpers privados ============================================

    /** Limpia y sanea un texto libre (anti-XSS). */
    private function texto(mixed $valor): string
    {
        $valor = str_replace(['<', '>'], '', (string) $valor);
        return trim($valor);
    }

    /** Normaliza datetime-local ('2026-10-01T08:00') o 'Y-m-d H:i:s'. */
    private function fechaHora(mixed $valor, string $campo): string
    {
        $valor = trim(str_replace(['<', '>'], '', (string) $valor));
        if ($valor === '') {
            throw ExcepcionApi::validacion('La fecha y hora de ' . $campo . ' es obligatoria.');
        }
        $valor = str_replace('T', ' ', $valor);
        $fecha = DateTime::createFromFormat('Y-m-d H:i:s', $valor)
            ?: DateTime::createFromFormat('Y-m-d H:i', $valor);
        if (!$fecha) {
            throw ExcepcionApi::validacion('La fecha y hora de ' . $campo . ' no tiene un formato válido.');
        }
        return $fecha->format('Y-m-d H:i:s');
    }

    /**
     * Regla de fechas de la cabecera: el cierre nunca puede ser anterior al
     * inicio y, al CREAR, no puede estar ya en el pasado (una jornada nueva
     * debe poder ocurrir). Al EDITAR solo se exige el orden, para que se
     * puedan corregir o cancelar jornadas viejas.
     */
    private function validarRangoFechas(bool $exigirCierreFuturo): void
    {
        $inicio = (string) $this->__get('fecha_inicio');
        $fin    = (string) $this->__get('fecha_fin');

        if ($fin < $inicio) {
            throw ExcepcionApi::validacion('La fecha de cierre no puede ser anterior a la de inicio.');
        }
        if ($exigirCierreFuturo && substr($fin, 0, 10) < date('Y-m-d')) {
            throw ExcepcionApi::validacion('No se puede crear una jornada cuya fecha de cierre ya pasó.');
        }
    }

    private function esFecha(string $fecha): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    /** Cuenta los asistentes registrados en una jornada. */
    private function personasJornada(int $idJornada): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM jornada_beneficiarios WHERE id_jornada = :id'
        );
        $stmt->bindValue(':id', $idJornada, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cabecera de la jornada (opcionalmente bloqueada con FOR UPDATE dentro
     * de una transacción). Si no existe lanza 404.
     */
    private function cabecera(int $idJornada, bool $bloquear = false): array
    {
        $sql = 'SELECT * FROM jornadas_medicas WHERE id_jornada = :id'
            . ($bloquear ? ' FOR UPDATE' : '');
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idJornada, PDO::PARAM_INT);
        $stmt->execute();
        $jornada = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$jornada) {
            throw ExcepcionApi::noEncontrado('La jornada solicitada no existe.');
        }
        return $jornada;
    }

    /** Regla: solo una jornada Activa y aún vigente acepta escrituras. */
    private function exigirActiva(array $jornada): void
    {
        if ($jornada['estatus'] !== 'Activa') {
            throw ExcepcionApi::validacion(
                'La jornada está en estatus "' . $jornada['estatus'] . '": no admite más registros.'
            );
        }
        if (substr($jornada['fecha_fin'], 0, 10) < date('Y-m-d')) {
            throw ExcepcionApi::validacion('La jornada ya finalizó por fecha: no admite más registros.');
        }
    }

    //=== Catálogos ====================================================

    /** Catálogos del formulario (estáticos: no tocan la BD). */
    private function catalogos(): array
    {
        return [
            'tipo_cedula'   => self::TIPOS_CEDULA,
            'genero'        => self::GENEROS,
            'tipo_paciente' => self::TIPOS_PACIENTE,
            'tipo_jornada'  => self::TIPOS_JORNADA,
            'estatus'       => self::ESTADOS_JORNADA,
        ];
    }

    /**
     * Empleados con permiso de LECTURA en el módulo Jornadas (roles 2, 6 y 10),
     * menos el autor: a ellos se les avisa cuando se crea una jornada.
     * Es una consulta de seguridad desde la conexión de negocio (mismo patrón
     * cross-schema que EmpleadoModel/CitaModel); si falla no rompe nada.
     */
    private function destinatarios(): array
    {
        try {
            $autor = (int) $this->__get('id_empleado');
            $stmt = $this->conn->prepare(
                "SELECT DISTINCT e.id_empleado
                 FROM dirpoles_security.empleado e
                 INNER JOIN dirpoles_security.rol_modulo_permiso r
                    ON r.id_tipo_emp = e.id_tipo_empleado
                 WHERE r.id_modulo = 11 AND r.id_permiso = 2
                   AND e.estatus = 1 AND e.id_empleado <> :autor"
            );
            $stmt->bindValue(':autor', $autor, PDO::PARAM_INT);
            $stmt->execute();
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable $e) {
            error_log('JornadaModel::destinatarios - ' . $e->getMessage());
            return [];
        }
    }

    //=== Jornada: CRUD ================================================

    private function crear(): array
    {
        $this->validarRangoFechas(true);

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'INSERT INTO jornadas_medicas
                    (nombre_jornada, tipo_jornada, aforo_maximo, fecha_inicio,
                     fecha_fin, ubicacion, descripcion, estatus)
                 VALUES (:nombre, :tipo, :aforo, :inicio, :fin, :ubicacion, :descripcion, \'Activa\')'
            );
            $stmt->bindValue(':nombre',     $this->__get('nombre_jornada'));
            $stmt->bindValue(':tipo',       $this->__get('tipo_jornada'));
            $stmt->bindValue(':aforo',      $this->__get('aforo_maximo'), PDO::PARAM_INT);
            $stmt->bindValue(':inicio',     $this->__get('fecha_inicio'));
            $stmt->bindValue(':fin',        $this->__get('fecha_fin'));
            $stmt->bindValue(':ubicacion',  $this->__get('ubicacion'));
            $stmt->bindValue(':descripcion', $this->__get('descripcion') ?? '');
            $stmt->execute();

            $id = (int) $this->conn->lastInsertId();
            $this->conn->commit();

            return [
                'id_jornada'      => $id,
                'nombre_jornada'  => $this->__get('nombre_jornada'),
                'aforo_maximo'    => $this->__get('aforo_maximo'),
                'fecha_inicio'    => $this->__get('fecha_inicio'),
                'fecha_fin'       => $this->__get('fecha_fin'),
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::crear - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar la jornada.');
        }
    }

    private function listar(): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT j.id_jornada, j.nombre_jornada, j.tipo_jornada, j.aforo_maximo,
                        j.fecha_inicio, j.fecha_fin, j.ubicacion, j.descripcion,
                        j.estatus, j.fecha_creacion,
                        IFNULL(p.personas, 0) AS personas,
                        IFNULL(d.diagnosticos, 0) AS diagnosticos
                 FROM jornadas_medicas j
                 LEFT JOIN (
                     SELECT id_jornada, COUNT(*) AS personas
                     FROM jornada_beneficiarios GROUP BY id_jornada
                 ) p ON p.id_jornada = j.id_jornada
                 LEFT JOIN (
                     SELECT jb.id_jornada, COUNT(*) AS diagnosticos
                     FROM jornada_diagnosticos jd
                     INNER JOIN jornada_beneficiarios jb
                        ON jb.id_jornada_beneficiario = jd.id_jornada_beneficiario
                     GROUP BY jb.id_jornada
                 ) d ON d.id_jornada = j.id_jornada
                 ORDER BY j.fecha_inicio DESC, j.id_jornada DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 200), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),   PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('JornadaModel::listar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar las jornadas.');
        }
    }

    /** Cabecera + contadores (sirve para la página de detalle y el modal). */
    private function obtener(): array
    {
        try {
            $id = (int) $this->__get('id_jornada');
            $jornada = $this->cabecera($id);
            $jornada['personas']      = $this->personasJornada($id);
            $jornada['diagnosticos']  = $this->contarDiagnosticosJornada($id);
            $jornada['ocupacion']     = $jornada['aforo_maximo'] > 0
                ? round(($jornada['personas'] / $jornada['aforo_maximo']) * 100, 1)
                : 0;
            return $jornada;
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('JornadaModel::obtener - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo cargar la jornada.');
        }
    }

    private function contarDiagnosticosJornada(int $idJornada): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*)
             FROM jornada_diagnosticos jd
             INNER JOIN jornada_beneficiarios jb
                ON jb.id_jornada_beneficiario = jd.id_jornada_beneficiario
             WHERE jb.id_jornada = :id'
        );
        $stmt->bindValue(':id', $idJornada, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function actualizar(): array
    {
        $this->validarRangoFechas(false);

        try {
            $id = (int) $this->__get('id_jornada');
            $this->conn->beginTransaction();

            // FOR UPDATE: el aforo se compara contra el recuento real.
            $jornada = $this->cabecera($id, true);
            $personas = $this->personasJornada($id);
            $aforo = (int) $this->__get('aforo_maximo');
            if ($aforo < $personas) {
                throw ExcepcionApi::validacion(
                    "No se puede reducir el aforo a {$aforo}: ya hay {$personas} personas registradas en esta jornada."
                );
            }

            $stmt = $this->conn->prepare(
                'UPDATE jornadas_medicas
                 SET nombre_jornada = :nombre, tipo_jornada = :tipo, aforo_maximo = :aforo,
                     fecha_inicio = :inicio, fecha_fin = :fin, ubicacion = :ubicacion,
                     descripcion = :descripcion, estatus = :estatus
                 WHERE id_jornada = :id'
            );
            $stmt->bindValue(':nombre',     $this->__get('nombre_jornada'));
            $stmt->bindValue(':tipo',       $this->__get('tipo_jornada'));
            $stmt->bindValue(':aforo',      $aforo, PDO::PARAM_INT);
            $stmt->bindValue(':inicio',     $this->__get('fecha_inicio'));
            $stmt->bindValue(':fin',        $this->__get('fecha_fin'));
            $stmt->bindValue(':ubicacion',  $this->__get('ubicacion'));
            $stmt->bindValue(':descripcion', $this->__get('descripcion') ?? '');
            $stmt->bindValue(':estatus',    $this->__get('estatus'));
            $stmt->bindValue(':id',         $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            return [
                'id_jornada'     => $id,
                'nombre_jornada' => $this->__get('nombre_jornada'),
                'estatus'        => $this->__get('estatus'),
                'aforo_maximo'   => $aforo,
                'personas'       => $personas,
                'estatus_anterior' => $jornada['estatus'],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::actualizar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la jornada.');
        }
    }

    /** Solo se elimina una jornada SIN asistentes (si no, se cancela). */
    private function eliminar(): array
    {
        try {
            $id = (int) $this->__get('id_jornada');
            $this->conn->beginTransaction();

            $jornada = $this->cabecera($id, true);
            $personas = $this->personasJornada($id);
            if ($personas > 0) {
                throw ExcepcionApi::enUso(
                    "La jornada tiene {$personas} personas registradas: cancelarla en lugar de eliminarla."
                );
            }

            $stmt = $this->conn->prepare('DELETE FROM jornadas_medicas WHERE id_jornada = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            return [
                'id_jornada'     => $id,
                'nombre_jornada' => $jornada['nombre_jornada'],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::eliminar - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar la jornada.');
        }
    }

    private function stats(): array
    {
        try {
            $total = (int) $this->conn
                ->query('SELECT COUNT(*) FROM jornadas_medicas')
                ->fetchColumn();

            $activas = (int) $this->conn
                ->query("SELECT COUNT(*) FROM jornadas_medicas WHERE estatus = 'Activa'")
                ->fetchColumn();

            $finalizadas = (int) $this->conn
                ->query("SELECT COUNT(*) FROM jornadas_medicas WHERE estatus = 'Finalizada'")
                ->fetchColumn();

            // "Este mes": jornadas cuyo INICIO ocurre en el mes en curso.
            $mes = (int) $this->conn
                ->query(
                    "SELECT COUNT(*) FROM jornadas_medicas
                      WHERE fecha_inicio >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')
                        AND fecha_inicio < DATE_FORMAT(CURRENT_DATE() + INTERVAL 1 MONTH, '%Y-%m-01')"
                )
                ->fetchColumn();

            return [
                'jornadas_total'       => $total,
                'jornadas_activas'     => $activas,
                'jornadas_finalizadas' => $finalizadas,
                'jornadas_mes'         => $mes,
            ];
        } catch (Throwable $e) {
            error_log('JornadaModel::stats - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar las estadísticas del módulo.');
        }
    }

    //=== Asistentes ===================================================

    /** Asistentes de una jornada con edad y cantidad de diagnósticos. */
    private function asistentes(): array
    {
        try {
            $id = (int) $this->__get('id_jornada');
            $this->cabecera($id); // 404 si la jornada no existe

            $stmt = $this->conn->prepare(
                "SELECT jb.id_jornada_beneficiario, jb.tipo_cedula, jb.cedula, jb.nombres,
                        jb.apellidos, jb.fecha_nacimiento, jb.genero, jb.tipo_paciente,
                        jb.telefono, jb.correo, jb.direccion, jb.fecha_atencion, jb.estatus,
                        CASE WHEN jb.fecha_nacimiento IS NULL THEN NULL
                             ELSE TIMESTAMPDIFF(YEAR, jb.fecha_nacimiento, CURDATE()) END AS edad,
                        IFNULL(d.diagnosticos, 0) AS diagnosticos
                 FROM jornada_beneficiarios jb
                 LEFT JOIN (
                     SELECT id_jornada_beneficiario, COUNT(*) AS diagnosticos
                     FROM jornada_diagnosticos
                     GROUP BY id_jornada_beneficiario
                 ) d ON d.id_jornada_beneficiario = jb.id_jornada_beneficiario
                 WHERE jb.id_jornada = :id
                 ORDER BY jb.fecha_atencion DESC, jb.id_jornada_beneficiario DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':id',     $id, PDO::PARAM_INT);
            $stmt->bindValue(':limit',  (int) ($this->__get('limit')  ?? 1000), PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) ($this->__get('offset') ?? 0),    PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (ExcepcionApi $e) {
            throw $e;
        } catch (Throwable $e) {
            error_log('JornadaModel::asistentes - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron listar los asistentes de la jornada.');
        }
    }

    private function agregarAsistente(): array
    {
        try {
            $idJornada = (int) $this->__get('id_jornada');
            $this->conn->beginTransaction();

            // FOR UPDATE: el aforo se valida contra la fila de la cabecera,
            // así dos registros simultáneos no rebasan el límite.
            $jornada = $this->cabecera($idJornada, true);
            $this->exigirActiva($jornada);

            $tipo = $this->__get('tipo_cedula');
            $cedula = $this->__get('cedula');

            $stmt = $this->conn->prepare(
                'SELECT id_jornada_beneficiario FROM jornada_beneficiarios
                 WHERE id_jornada = :jornada AND tipo_cedula = :tipo AND cedula = :cedula
                 LIMIT 1 FOR UPDATE'
            );
            $stmt->bindValue(':jornada', $idJornada, PDO::PARAM_INT);
            $stmt->bindValue(':tipo',    $tipo);
            $stmt->bindValue(':cedula',  $cedula);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                throw ExcepcionApi::yaExiste(
                    'Esa persona (CI: ' . $tipo . '-' . $cedula . ') ya está registrada en esta jornada.'
                );
            }

            $personas = $this->personasJornada($idJornada);
            if ($personas >= (int) $jornada['aforo_maximo']) {
                throw ExcepcionApi::validacion(
                    'La jornada ya alcanzó su aforo máximo de ' . (int) $jornada['aforo_maximo'] . ' personas.'
                );
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO jornada_beneficiarios
                    (tipo_cedula, cedula, nombres, apellidos, fecha_nacimiento, genero,
                     tipo_paciente, telefono, correo, direccion, id_jornada)
                 VALUES (:tipo, :cedula, :nombres, :apellidos, :nacimiento, :genero,
                         :tipo_paciente, :telefono, :correo, :direccion, :jornada)'
            );
            $stmt->bindValue(':tipo',         $tipo);
            $stmt->bindValue(':cedula',       $cedula);
            $stmt->bindValue(':nombres',      $this->__get('nombres'));
            $stmt->bindValue(':apellidos',    $this->__get('apellidos'));
            $stmt->bindValue(':nacimiento',   $this->__get('fecha_nacimiento'));
            $stmt->bindValue(':genero',       $this->__get('genero'));
            $stmt->bindValue(':tipo_paciente', $this->__get('tipo_paciente'));
            $stmt->bindValue(':telefono',     $this->__get('telefono'));
            $stmt->bindValue(':correo',       $this->__get('correo') ?? '');
            $stmt->bindValue(':direccion',    $this->__get('direccion') ?? '');
            $stmt->bindValue(':jornada',      $idJornada, PDO::PARAM_INT);
            $stmt->execute();

            $id = (int) $this->conn->lastInsertId();
            $this->conn->commit();

            return [
                'id_jornada_beneficiario' => $id,
                'id_jornada'              => $idJornada,
                'nombre_jornada'          => $jornada['nombre_jornada'],
                'tipo_cedula'             => $tipo,
                'cedula'                  => $cedula,
                'nombres'                 => $this->__get('nombres'),
                'apellidos'               => $this->__get('apellidos'),
                'tipo_paciente'           => $this->__get('tipo_paciente'),
                'personas'                => $personas + 1,
                'aforo_maximo'            => (int) $jornada['aforo_maximo'],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::agregarAsistente - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar a la persona en la jornada.');
        }
    }

    private function eliminarAsistente(): array
    {
        try {
            $id = (int) $this->__get('id_jornada_beneficiario');
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "SELECT jb.*, j.estatus AS estatus_jornada, j.fecha_fin, j.nombre_jornada
                 FROM jornada_beneficiarios jb
                 INNER JOIN jornadas_medicas j ON j.id_jornada = jb.id_jornada
                 WHERE jb.id_jornada_beneficiario = :id
                 FOR UPDATE"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$persona) {
                throw ExcepcionApi::noEncontrado('La persona registrada en la jornada no existe.');
            }

            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) FROM jornada_diagnosticos WHERE id_jornada_beneficiario = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ((int) $stmt->fetchColumn() > 0) {
                throw ExcepcionApi::enUso(
                    'No se puede eliminar: la persona ya tiene un diagnóstico registrado en la jornada.'
                );
            }

            if ($persona['estatus_jornada'] !== 'Activa'
                || substr($persona['fecha_fin'], 0, 10) < date('Y-m-d')) {
                throw ExcepcionApi::validacion(
                    'No se puede eliminar: la jornada ya no está activa.'
                );
            }

            $stmt = $this->conn->prepare(
                'DELETE FROM jornada_beneficiarios WHERE id_jornada_beneficiario = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            return [
                'id_jornada_beneficiario' => $id,
                'id_jornada'              => (int) $persona['id_jornada'],
                'nombre_jornada'          => $persona['nombre_jornada'],
                'nombre'                  => trim($persona['nombres'] . ' ' . $persona['apellidos']),
                'cedula'                  => $persona['tipo_cedula'] . '-' . $persona['cedula'],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::eliminarAsistente - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar a la persona de la jornada.');
        }
    }

    /**
     * Autocompletado por cédula: busca en beneficiario (business) y en
     * empleado (security) con el mismo patrón UNION ALL cross-schema que
     * EmpleadoModel/BeneficiarioModel. Devuelve null si no existe.
     */
    private function buscarPersona(): array
    {
        try {
            $tipo = $this->__get('tipo_documento');
            $cedula = $this->__get('cedula');

            $stmt = $this->conn->prepare(
                "SELECT 'beneficiario' AS origen, b.tipo_cedula, b.cedula, b.nombres, b.apellidos,
                        b.fecha_nac AS fecha_nacimiento,
                        CASE WHEN b.genero = 'F' THEN 'Femenino'
                             WHEN b.genero = 'M' THEN 'Masculino'
                             ELSE NULL END AS genero,
                        b.telefono, b.correo, b.direccion
                 FROM beneficiario b
                 WHERE b.tipo_cedula = :tipo AND b.cedula = :cedula
                 UNION ALL
                 SELECT 'empleado', e.tipo_cedula, e.cedula, e.nombre, e.apellido,
                        e.fecha_nacimiento, NULL, e.telefono, e.correo, e.direccion
                 FROM dirpoles_security.empleado e
                 WHERE e.tipo_cedula = :tipo2 AND e.cedula = :cedula2
                 LIMIT 1"
            );
            $stmt->bindValue(':tipo',   $tipo);
            $stmt->bindValue(':cedula', $cedula);
            $stmt->bindValue(':tipo2',  $tipo);
            $stmt->bindValue(':cedula2', $cedula);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                return ['encontrado' => false];
            }

            return [
                'encontrado'        => true,
                'origen'            => $fila['origen'],
                'tipo_cedula'       => $fila['tipo_cedula'] ?: $tipo,
                'cedula'            => $fila['cedula'] ?: $cedula,
                'nombres'           => $fila['nombres'] ?? '',
                'apellidos'         => $fila['apellidos'] ?? '',
                'fecha_nacimiento'  => $fila['fecha_nacimiento'] ?? '',
                'genero'            => $fila['genero'] ?? '',
                'telefono'          => preg_replace('/\D+/', '', (string) ($fila['telefono'] ?? '')),
                'correo'            => $fila['correo'] ?? '',
                'direccion'         => $fila['direccion'] ?? '',
            ];
        } catch (Throwable $e) {
            error_log('JornadaModel::buscarPersona - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo buscar la persona por cédula.');
        }
    }

    //=== Diagnósticos =================================================

    /** Diagnósticos de un asistente con sus insumos (agrupados). */
    private function diagnosticos(): array
    {
        try {
            $id = (int) $this->__get('id_jornada_beneficiario');

            $stmt = $this->conn->prepare(
                'SELECT jd.id_jornada_diagnostico, jd.id_jornada_beneficiario,
                        jd.id_empleado_medico, jd.diagnostico, jd.tratamiento,
                        jd.observaciones, jd.fecha_diagnostico,
                        CONCAT(e.nombre, \' \', e.apellido) AS medico,
                        jb.id_jornada
                 FROM jornada_diagnosticos jd
                 INNER JOIN jornada_beneficiarios jb
                    ON jb.id_jornada_beneficiario = jd.id_jornada_beneficiario
                 LEFT JOIN dirpoles_security.empleado e ON e.id_empleado = jd.id_empleado_medico
                 WHERE jd.id_jornada_beneficiario = :id
                 ORDER BY jd.fecha_diagnostico DESC, jd.id_jornada_diagnostico DESC'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$filas) {
                return [];
            }

            $stmt = $this->conn->prepare(
                'SELECT ji.id_jornada_diagnostico, ji.id_jornada_insumo, ji.id_insumo,
                        ji.cantidad_usada, ji.descripcion, i.nombre_insumo
                 FROM jornada_insumos ji
                 INNER JOIN insumos i ON i.id_insumo = ji.id_insumo
                 INNER JOIN jornada_diagnosticos jd
                    ON jd.id_jornada_diagnostico = ji.id_jornada_diagnostico
                 WHERE jd.id_jornada_beneficiario = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $insumos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $agrupados = [];
            foreach ($insumos as $insumo) {
                $agrupados[(int) $insumo['id_jornada_diagnostico']][] = $insumo;
            }

            foreach ($filas as $i => $fila) {
                $filas[$i]['insumos'] = $agrupados[(int) $fila['id_jornada_diagnostico']] ?? [];
            }

            return $filas;
        } catch (Throwable $e) {
            error_log('JornadaModel::diagnosticos - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los diagnósticos.');
        }
    }

    /**
     * Diagnóstico + insumos en una transacción. Descuento de stock con
     * FOR UPDATE (mismo patrón que MedicinaModel::registrarInsumos).
     */
    private function agregarDiagnostico(): array
    {
        try {
            $idAsistente = (int) $this->__get('id_jornada_beneficiario');
            $insumos = (array) ($this->__get('insumos') ?? []);
            if (count($insumos) > self::MAX_INSUMOS) {
                throw ExcepcionApi::validacion(
                    'Un diagnóstico puede llevar como máximo ' . self::MAX_INSUMOS . ' insumos distintos.'
                );
            }
            $this->validarInsumos($insumos);

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "SELECT jb.*, j.estatus AS estatus_jornada, j.fecha_fin, j.nombre_jornada
                 FROM jornada_beneficiarios jb
                 INNER JOIN jornadas_medicas j ON j.id_jornada = jb.id_jornada
                 WHERE jb.id_jornada_beneficiario = :id
                 FOR UPDATE"
            );
            $stmt->bindValue(':id', $idAsistente, PDO::PARAM_INT);
            $stmt->execute();
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$persona) {
                throw ExcepcionApi::noEncontrado('La persona seleccionada ya no existe en la jornada.');
            }
            $this->exigirActiva([
                'estatus'   => $persona['estatus_jornada'],
                'fecha_fin' => $persona['fecha_fin'],
            ]);

            $stmt = $this->conn->prepare(
                'INSERT INTO jornada_diagnosticos
                    (id_jornada_beneficiario, id_empleado_medico, diagnostico,
                     tratamiento, observaciones, fecha_diagnostico)
                 VALUES (:beneficiario, :empleado, :diagnostico, :tratamiento,
                         :observaciones, NOW())'
            );
            $stmt->bindValue(':beneficiario',  $idAsistente, PDO::PARAM_INT);
            $stmt->bindValue(':empleado',      (int) $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->bindValue(':diagnostico',   $this->__get('diagnostico'));
            $stmt->bindValue(':tratamiento',   $this->__get('tratamiento'));
            $stmt->bindValue(':observaciones', $this->__get('observaciones') ?? '');
            $stmt->execute();
            $idDiagnostico = (int) $this->conn->lastInsertId();

            $descontados = $this->descontarInsumos($insumos, $idDiagnostico);

            $this->conn->commit();

            return [
                'id_jornada_diagnostico' => $idDiagnostico,
                'id_jornada_beneficiario' => $idAsistente,
                'id_jornada'              => (int) $persona['id_jornada'],
                'nombre_jornada'          => $persona['nombre_jornada'],
                'persona'                 => trim($persona['nombres'] . ' ' . $persona['apellidos']),
                'diagnostico'             => $this->__get('diagnostico'),
                'insumos'                 => $descontados,
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::agregarDiagnostico - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo registrar el diagnóstico.');
        }
    }

    /** Valida la lista de insumos ANTES de abrir la transacción. */
    private function validarInsumos(array $insumos): void
    {
        foreach ($insumos as $fila) {
            if (!is_array($fila)) {
                throw ExcepcionApi::validacion('Uno de los insumos seleccionados no es válido.');
            }
            $idInsumo = (int) ($fila['id_insumo'] ?? 0);
            $cantidad = (int) ($fila['cantidad'] ?? 0);
            if ($idInsumo < 1 || $cantidad < 1) {
                throw ExcepcionApi::validacion('Cada insumo necesita un identificador y una cantidad mayor que cero.');
            }
        }
    }

    /**
     * Descuenta stock, registra kardex y devuelve el detalle usado.
     * Lanza (y por tanto revierte la transacción) si algún insumo no sirve.
     */
    private function descontarInsumos(array $insumos, int $idDiagnostico): array
    {
        $detalle = [];
        $vistos = [];

        foreach ($insumos as $fila) {
            $idInsumo = (int) $fila['id_insumo'];
            $cantidad = (int) $fila['cantidad'];

            if (isset($vistos[$idInsumo])) {
                throw ExcepcionApi::validacion('Hay un insumo repetido en la lista.');
            }
            $vistos[$idInsumo] = true;

            // FOR UPDATE: dos diagnósticos simultáneos no descuentan el mismo stock.
            $stmt = $this->conn->prepare(
                'SELECT nombre_insumo, cantidad, estatus, fecha_vencimiento
                 FROM insumos WHERE id_insumo = :id FOR UPDATE'
            );
            $stmt->bindValue(':id', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();
            $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$insumo) {
                throw ExcepcionApi::noEncontrado('Uno de los insumos seleccionados ya no existe.');
            }
            if ($insumo['estatus'] !== 'Disponible'
                || $insumo['fecha_vencimiento'] < date('Y-m-d')) {
                throw ExcepcionApi::validacion(
                    'El insumo "' . $insumo['nombre_insumo'] . '" ya no está disponible.'
                );
            }
            if ((int) $insumo['cantidad'] < $cantidad) {
                throw ExcepcionApi::validacion(sprintf(
                    'Stock insuficiente para "%s" (disponible: %d).',
                    $insumo['nombre_insumo'],
                    (int) $insumo['cantidad']
                ));
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO jornada_insumos
                    (id_jornada_diagnostico, id_insumo, cantidad_usada, descripcion)
                 VALUES (:diagnostico, :insumo, :cantidad, :descripcion)'
            );
            $stmt->bindValue(':diagnostico', $idDiagnostico, PDO::PARAM_INT);
            $stmt->bindValue(':insumo',      $idInsumo, PDO::PARAM_INT);
            $stmt->bindValue(':cantidad',    $cantidad, PDO::PARAM_INT);
            $stmt->bindValue(':descripcion', 'Insumo utilizado en jornada médica');
            $stmt->execute();

            $stmt = $this->conn->prepare(
                "UPDATE insumos
                 SET estatus = CASE WHEN (cantidad - :cantidad) <= 0 THEN 'Agotado' ELSE estatus END,
                     cantidad = cantidad - :cantidad
                 WHERE id_insumo = :id"
            );
            $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt->bindValue(':id', $idInsumo, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                "INSERT INTO inventario_medico
                    (id_insumo, id_empleado, tipo_movimiento, cantidad, descripcion)
                 VALUES (:insumo, :empleado, 'Salida', :cantidad, :descripcion)"
            );
            $stmt->bindValue(':insumo',     $idInsumo, PDO::PARAM_INT);
            $stmt->bindValue(':empleado',   (int) $this->__get('id_empleado'), PDO::PARAM_INT);
            $stmt->bindValue(':cantidad',   $cantidad, PDO::PARAM_INT);
            $stmt->bindValue(':descripcion', 'Salida por jornada médica (diagnóstico #' . $idDiagnostico . ')');
            $stmt->execute();

            $detalle[] = [
                'id_insumo'    => $idInsumo,
                'nombre'       => $insumo['nombre_insumo'],
                'cantidad'     => $cantidad,
                'stock_restante' => (int) $insumo['cantidad'] - $cantidad,
            ];
        }

        return $detalle;
    }

    /** Edición SOLO de los textos del diagnóstico (nunca insumos ni persona). */
    private function actualizarDiagnostico(): array
    {
        try {
            $id = (int) $this->__get('id_jornada_diagnostico');

            $stmt = $this->conn->prepare(
                'SELECT jd.id_jornada_diagnostico, jb.id_jornada, jb.nombres, jb.apellidos,
                        j.nombre_jornada
                 FROM jornada_diagnosticos jd
                 INNER JOIN jornada_beneficiarios jb
                    ON jb.id_jornada_beneficiario = jd.id_jornada_beneficiario
                 INNER JOIN jornadas_medicas j ON j.id_jornada = jb.id_jornada
                 WHERE jd.id_jornada_diagnostico = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $diagnostico = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$diagnostico) {
                throw ExcepcionApi::noEncontrado('El diagnóstico no existe.');
            }

            $stmt = $this->conn->prepare(
                'UPDATE jornada_diagnosticos
                 SET diagnostico = :diagnostico, tratamiento = :tratamiento,
                     observaciones = :observaciones
                 WHERE id_jornada_diagnostico = :id'
            );
            $stmt->bindValue(':diagnostico',   $this->__get('diagnostico'));
            $stmt->bindValue(':tratamiento',   $this->__get('tratamiento'));
            $stmt->bindValue(':observaciones', $this->__get('observaciones') ?? '');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'id_jornada_diagnostico' => $id,
                'id_jornada'             => (int) $diagnostico['id_jornada'],
                'nombre_jornada'         => $diagnostico['nombre_jornada'],
                'persona'                => trim($diagnostico['nombres'] . ' ' . $diagnostico['apellidos']),
                'diagnostico'            => $this->__get('diagnostico'),
            ];
        } catch (Throwable $e) {
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::actualizarDiagnostico - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar el diagnóstico.');
        }
    }

    /**
     * Borra el diagnóstico y sus insumos (FK). El stock YA descontado NO se
     * devuelve: el insumo se usó (misma regla permanente que Medicina).
     */
    private function eliminarDiagnostico(): array
    {
        try {
            $id = (int) $this->__get('id_jornada_diagnostico');
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'SELECT jd.id_jornada_diagnostico, jb.id_jornada, jb.nombres, jb.apellidos,
                        j.nombre_jornada,
                        (SELECT COUNT(*) FROM jornada_insumos ji
                          WHERE ji.id_jornada_diagnostico = jd.id_jornada_diagnostico) AS insumos
                 FROM jornada_diagnosticos jd
                 INNER JOIN jornada_beneficiarios jb
                    ON jb.id_jornada_beneficiario = jd.id_jornada_beneficiario
                 INNER JOIN jornadas_medicas j ON j.id_jornada = jb.id_jornada
                 WHERE jd.id_jornada_diagnostico = :id
                 FOR UPDATE'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $diagnostico = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$diagnostico) {
                throw ExcepcionApi::noEncontrado('El diagnóstico no existe.');
            }

            $stmt = $this->conn->prepare(
                'DELETE FROM jornada_insumos WHERE id_jornada_diagnostico = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                'DELETE FROM jornada_diagnosticos WHERE id_jornada_diagnostico = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();

            return [
                'id_jornada_diagnostico' => $id,
                'id_jornada'             => (int) $diagnostico['id_jornada'],
                'nombre_jornada'         => $diagnostico['nombre_jornada'],
                'persona'                => trim($diagnostico['nombres'] . ' ' . $diagnostico['apellidos']),
                'insumos'                => (int) $diagnostico['insumos'],
            ];
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            if ($e instanceof ExcepcionApi) {
                throw $e;
            }
            error_log('JornadaModel::eliminarDiagnostico - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo eliminar el diagnóstico.');
        }
    }

    //=== Insumos ======================================================

    /** Insumos que SÍ se pueden usar: disponibles, sin vencer y con stock. */
    private function insumosDisponibles(): array
    {
        try {
            $stmt = $this->conn->query(
                "SELECT i.id_insumo, i.nombre_insumo, i.cantidad, i.fecha_vencimiento,
                        i.tipo_insumo, p.nombre_presentacion
                 FROM insumos i
                 INNER JOIN presentacion_insumo p ON p.id_presentacion = i.id_presentacion
                 WHERE i.estatus = 'Disponible'
                   AND i.cantidad > 0
                   AND i.fecha_vencimiento >= CURDATE()
                 ORDER BY i.nombre_insumo ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('JornadaModel::insumosDisponibles - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudieron cargar los insumos disponibles.');
        }
    }
}
