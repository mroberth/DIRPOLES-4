<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Core\ExcepcionApi;

/**
 * TrabajoSocialModel
 * ---------------------------------------------------------------
 * Módulo Trabajo Social (id_modulo 7). Sub-registros: becas,
 * exoneracion, fames y gestion_emb; todos cuelgan de
 * solicitud_de_servicio con id_servicios = 4 ('Trabajador Social').
 * Etapas construidas: becas, exoneraciones (con pendientes de estudio),
 * FAMES y embarazadas.
 *
 * Patrón del esqueleto: __set valida → manejarAccion() despacha →
 * métodos privados hacen el SQL y lanzan ExcepcionApi.
 */
class TrabajoSocialModel extends BusinessModel
{
    /** id_servicios = 4 → 'Trabajador Social' (catálogo servicio). */
    private const ID_SERVICIO_TS = 4;

    /** Códigos de banco válidos (tipo_banco varchar(4)). */
    private const BANCOS = [
        '0102', '0156', '0172', '0114', '0171', '0166', '0175', '0128',
        '0163', '0115', '0151', '0173', '0105', '0191', '0138', '0137',
        '0104', '0168', '0134', '0177', '0146', '0174', '0108', '0157',
        '0169', '0178',
    ];

    /** Motivos válidos de exoneración (varchar(100) de la tabla). */
    private const MOTIVOS = ['Inscripción', 'Paquete de Grado', 'Otro'];

    /** Tipos de ayuda FAMES (varchar(100) de la tabla). */
    private const TIPOS_AYUDA = ['Económica', 'Operaciones', 'Exámenes', 'Otros'];

    /** Rango de semanas de gestación (formulario del sistema viejo). */
    private const SEMANAS_GEST_MIN = 1;
    private const SEMANAS_GEST_MAX = 45;

    /** Sub-registros consultables (etapa de consulta/edición/eliminación). */
    private const TIPOS = ['becas', 'exoneraciones', 'fames', 'embarazadas'];

    /** Estados válidos de gestión_emb.estado (varchar(20), form. viejo). */
    private const ESTADOS = ['En Proceso', 'Aprobado', 'Rechazado'];

    /** Nombre del banco a partir de su código (CASE del sistema viejo). */
    private const CASE_BANCO = "CASE b.tipo_banco
                                WHEN '0102' THEN 'BANCO DE VENEZUELA'
                                WHEN '0156' THEN '100% BANCO'
                                WHEN '0172' THEN 'BANCAMIGA BANCO MICROFINANCIERO C.A'
                                WHEN '0114' THEN 'BANCARIBE'
                                WHEN '0171' THEN 'BANCO ACTIVO'
                                WHEN '0166' THEN 'BANCO AGRICOLA DE VENEZUELA'
                                WHEN '0175' THEN 'BANCO DIGITAL DE LOS TRABAJADORES'
                                WHEN '0128' THEN 'BANCO CARONI'
                                WHEN '0163' THEN 'BANCO DEL TESORO'
                                WHEN '0115' THEN 'BANCO EXTERIOR'
                                WHEN '0151' THEN 'BANCO FONDO COMUN'
                                WHEN '0173' THEN 'BANCO INTERNACIONAL DE DESARROLLO'
                                WHEN '0105' THEN 'BANCO MERCANTIL'
                                WHEN '0191' THEN 'BANCO NACIONAL DE CREDITO'
                                WHEN '0138' THEN 'BANCO PLAZA'
                                WHEN '0137' THEN 'BANCO SOFITASA'
                                WHEN '0104' THEN 'BANCO VENEZOLANO DE CREDITO'
                                WHEN '0168' THEN 'BANCRECER'
                                WHEN '0134' THEN 'BANESCO'
                                WHEN '0177' THEN 'BANFANB'
                                WHEN '0146' THEN 'BANGENTE'
                                WHEN '0174' THEN 'BANPLUS'
                                WHEN '0108' THEN 'BBVA PROVINCIAL'
                                WHEN '0157' THEN 'DELSUR BANCO UNIVERSAL'
                                WHEN '0169' THEN 'MI BANCO'
                                WHEN '0178' THEN 'N58 BANCO DIGITAL BANCO MICROFINANCIERO S.A'
                                ELSE 'BANCO NO IDENTIFICADO'
                            END AS nombre_banco";

    private array $atributos = [];

    public function __set(string $nombre, mixed $valor): void
    {
        switch ($nombre) {
            case 'id_beca':
            case 'id_beneficiario':
            case 'id_empleado':
            case 'id_usuario':
            case 'id_patologia':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion("El {$nombre} no es válido.");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'tipo_empleado':
                $this->atributos[$nombre] = trim((string) $valor);
                break;

            case 'tipo':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::TIPOS, true)) {
                    throw ExcepcionApi::validacion('El tipo de registro no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'id_registro':
                if (filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                    throw ExcepcionApi::validacion('El identificador del registro no es válido.');
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'estado':
                $valor = trim((string) $valor);
                if (!in_array($valor, self::ESTADOS, true)) {
                    throw ExcepcionApi::validacion('El estado de la gestión no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_banco':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El tipo de banco es obligatorio.');
                }
                if (!in_array($valor, self::BANCOS, true)) {
                    throw ExcepcionApi::validacion('El tipo de banco no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'cta_bcv':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La cuenta BCV es obligatoria.');
                }
                if (!preg_match('/^[0-9]{16}$/', $valor)) {
                    throw ExcepcionApi::validacion('La cuenta BCV debe tener exactamente 16 dígitos.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'motivo':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El motivo de exoneración es obligatorio.');
                }
                if (!in_array($valor, self::MOTIVOS, true)) {
                    throw ExcepcionApi::validacion('El motivo de exoneración no es válido.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El motivo no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'otro_motivo':
                $valor = trim((string) $valor);
                if ($valor !== '' && mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El detalle del motivo no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'carnet_discapacidad':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El carnet de discapacidad es obligatorio.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El carnet de discapacidad no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'tipo_ayuda':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('El tipo de ayuda es obligatorio.');
                }
                if (!in_array($valor, self::TIPOS_AYUDA, true)) {
                    throw ExcepcionApi::validacion('El tipo de ayuda no es válido.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'otro_tipo':
                $valor = trim((string) $valor);
                if ($valor !== '' && mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('El detalle del tipo de ayuda no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'semanas_gest':
                $valor = filter_var($valor, FILTER_VALIDATE_INT);
                if ($valor === false || $valor < self::SEMANAS_GEST_MIN || $valor > self::SEMANAS_GEST_MAX) {
                    throw ExcepcionApi::validacion('Las semanas de gestación deben estar entre 1 y 45.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'codigo_patria':
            case 'serial_patria':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    // Opcionales en la tabla (int NULL).
                    $this->atributos[$nombre] = null;
                    break;
                }
                if (!preg_match('/^[0-9]{1,10}$/', $valor)
                    || (int) $valor < 1 || (int) $valor > 2147483647) {
                    throw ExcepcionApi::validacion("El {$nombre} debe ser numérico (máx. 10 dígitos).");
                }
                $this->atributos[$nombre] = (int) $valor;
                break;

            case 'direccion_pdf':
            case 'direccion_carta':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion(
                        $nombre === 'direccion_pdf'
                            ? 'La planilla de inscripción es obligatoria.'
                            : 'La carta de exoneración es obligatoria.'
                    );
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('La ruta del archivo no puede superar 100 caracteres.');
                }
                $this->atributos[$nombre] = $valor;
                break;

            case 'direccion_estudiose':
                $valor = trim((string) $valor);
                if ($valor === '') {
                    throw ExcepcionApi::validacion('La ruta del estudio socioeconómico es obligatoria.');
                }
                if (!str_starts_with($valor, 'uploads/trabajo_social/')) {
                    throw ExcepcionApi::validacion('La ruta del estudio socioeconómico no es válida.');
                }
                if (mb_strlen($valor) > 100) {
                    throw ExcepcionApi::validacion('La ruta del estudio socioeconómico no puede superar 100 caracteres.');
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

    public function manejarAccion(string $accion): mixed
    {
        return match ($accion) {
            'beneficiarios' => $this->beneficiarios(),
            'patologias' => $this->patologias(),
            'crear_beca' => $this->crearBeca(),
            'crear_exoneracion' => $this->crearExoneracion(),
            'crear_fames' => $this->crearFames(),
            'crear_embarazada' => $this->crearEmbarazada(),
            'exoneraciones_pendientes' => $this->exoneracionesPendientes(),
            'validar_estudio' => $this->validarEstudioPendiente(),
            'vincular_estudio' => $this->vincularEstudio(),
            'stats' => $this->stats(),
            'listar' => $this->listar(),
            'datos_documento' => $this->datosDocumento(),
            'actualizar' => $this->actualizar(),
            'eliminar' => $this->eliminar(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en TrabajoSocialModel: '{$accion}'."),
        };
    }

    // ---------- Catálogos ----------

    private function beneficiarios(): array
    {
        $stmt = $this->conn->query(
            'SELECT id_beneficiario, nombres, apellidos, tipo_cedula, cedula, genero
             FROM beneficiario WHERE estatus = 1 ORDER BY nombres, apellidos'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Patologías habilitadas para FAMES y embarazadas: se excluyen
     * 1 y 2 ('Sin patología médica'/'psicológica'), igual que el
     * sistema viejo (TsModel::obtener_patologias).
     */
    private function patologias(): array
    {
        $stmt = $this->conn->query(
            'SELECT id_patologia, nombre_patologia, tipo_patologia
             FROM patologia WHERE id_patologia NOT IN (1, 2)
             ORDER BY nombre_patologia'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- Consulta ----------

    /** Despacha el listado por el atributo `tipo` (sub-registro). */
    private function listar(): array
    {
        return match ($this->__get('tipo')) {
            'becas' => $this->listarBecas(),
            'exoneraciones' => $this->listarExoneraciones(),
            'fames' => $this->listarFames(),
            'embarazadas' => $this->listarEmbarazadas(),
            default => throw ExcepcionApi::validacion('El tipo de registro no es válido.'),
        };
    }

    private function listarBecas(): array
    {
        $sql = 'SELECT b.id_becas, b.id_solicitud_serv, b.fecha_creacion,
                       b.tipo_banco, ' . self::CASE_BANCO . ',
                       b.cta_bcv, b.direccion_pdf,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(ben.nombres, \' \', ben.apellidos) AS beneficiario,
                       CONCAT(ben.tipo_cedula, \'-\', ben.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, \' \', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, \'-\', e.cedula) AS cedula_empleado
                FROM becas b
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = b.id_solicitud_serv
                INNER JOIN beneficiario ben ON ben.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado';
        return $this->ejecutarListado($sql, 'b.fecha_creacion DESC, b.id_becas DESC');
    }

    private function listarExoneraciones(): array
    {
        $sql = 'SELECT ex.id_exoneracion, ex.id_solicitud_serv, ex.fecha_creacion,
                       ex.motivo, ex.otro_motivo, ex.carnet_discapacidad,
                       ex.direccion_carta, ex.direccion_estudiose,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(ben.nombres, \' \', ben.apellidos) AS beneficiario,
                       CONCAT(ben.tipo_cedula, \'-\', ben.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, \' \', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, \'-\', e.cedula) AS cedula_empleado
                FROM exoneracion ex
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = ex.id_solicitud_serv
                INNER JOIN beneficiario ben ON ben.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado';
        return $this->ejecutarListado($sql, 'ex.fecha_creacion DESC, ex.id_exoneracion DESC');
    }

    private function listarFames(): array
    {
        $sql = 'SELECT f.id_fames, f.id_solicitud_serv, f.fecha_creacion,
                       f.tipo_ayuda, f.otro_tipo,
                       f.id_detalle_patologia, dp.id_patologia, p.nombre_patologia,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(ben.nombres, \' \', ben.apellidos) AS beneficiario,
                       CONCAT(ben.tipo_cedula, \'-\', ben.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, \' \', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, \'-\', e.cedula) AS cedula_empleado
                FROM fames f
                INNER JOIN detalle_patologia dp ON dp.id_detalle_patologia = f.id_detalle_patologia
                INNER JOIN patologia p ON p.id_patologia = dp.id_patologia
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = f.id_solicitud_serv
                INNER JOIN beneficiario ben ON ben.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado';
        return $this->ejecutarListado($sql, 'f.fecha_creacion DESC, f.id_fames DESC');
    }

    private function listarEmbarazadas(): array
    {
        $sql = 'SELECT g.id_gestion, g.id_solicitud_serv, g.fecha_creacion,
                       g.semanas_gest, g.estado, g.codigo_patria, g.serial_patria,
                       g.id_detalle_patologia, dp.id_patologia, p.nombre_patologia,
                       ss.id_beneficiario, ss.id_empleado,
                       CONCAT(ben.nombres, \' \', ben.apellidos) AS beneficiario,
                       CONCAT(ben.tipo_cedula, \'-\', ben.cedula) AS cedula_beneficiario,
                       CONCAT(e.nombre, \' \', e.apellido) AS empleado,
                       CONCAT(e.tipo_cedula, \'-\', e.cedula) AS cedula_empleado
                FROM gestion_emb g
                INNER JOIN detalle_patologia dp ON dp.id_detalle_patologia = g.id_detalle_patologia
                INNER JOIN patologia p ON p.id_patologia = dp.id_patologia
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = g.id_solicitud_serv
                INNER JOIN beneficiario ben ON ben.id_beneficiario = ss.id_beneficiario
                INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado';
        return $this->ejecutarListado($sql, 'g.fecha_creacion DESC, g.id_gestion DESC');
    }

    /**
     * Aplica el alcance (los no administrativos solo ven lo que él
     * registró), el orden cronológico inverso y devuelve las filas.
     */
    private function ejecutarListado(string $sqlBase, string $orden): array
    {
        $sql = $sqlBase;
        if (!$this->esAdministrativo()) {
            $sql .= ' WHERE ss.id_empleado = :id_empleado';
        }
        $sql .= " ORDER BY {$orden}";

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- Registro ----------
    /** Transacción: solicitud_de_servicio (servicio 4) → becas. */
    private function crearBeca(): array
    {
        $this->validarRequeridos(['id_beneficiario', 'tipo_banco', 'cta_bcv', 'direccion_pdf']);
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $this->validarBeneficiario($idBeneficiario);

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_TS,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => (int) $this->__get('id_usuario'),
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO becas (id_solicitud_serv, cta_bcv, direccion_pdf, tipo_banco, fecha_creacion)
                 VALUES (:solicitud, :cta_bcv, :direccion_pdf, :tipo_banco, CURDATE())'
            );
            $stmt->execute([
                ':solicitud' => $idSolicitud,
                ':cta_bcv' => $this->__get('cta_bcv'),
                ':direccion_pdf' => $this->__get('direccion_pdf'),
                ':tipo_banco' => $this->__get('tipo_banco'),
            ]);
            $idBeca = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_beca' => $idBeca,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::crearBeca - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la beca por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la beca.');
        }
    }

    // ---------- Exoneraciones ----------

    /** Transacción: solicitud_de_servicio (servicio 4) → exoneracion. */
    private function crearExoneracion(): array
    {
        $this->validarRequeridos(['id_beneficiario', 'motivo', 'carnet_discapacidad', 'direccion_carta']);
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $this->validarBeneficiario($idBeneficiario);

        if ($this->__get('motivo') === 'Otro' && (string) $this->__get('otro_motivo') === '') {
            throw ExcepcionApi::validacion('Cuando el motivo es "Otro" debes detallarlo.');
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_TS,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => (int) $this->__get('id_usuario'),
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            // direccion_estudiose queda NULL = pendiente de estudio
            // socioeconómico (regla del sistema viejo).
            $stmt = $this->conn->prepare(
                'INSERT INTO exoneracion
                   (id_solicitud_serv, motivo, otro_motivo, direccion_carta,
                    direccion_estudiose, carnet_discapacidad, fecha_creacion)
                 VALUES (:solicitud, :motivo, :otro_motivo, :direccion_carta,
                        NULL, :carnet, CURDATE())'
            );
            $stmt->execute([
                ':solicitud' => $idSolicitud,
                ':motivo' => $this->__get('motivo'),
                ':otro_motivo' => $this->__get('otro_motivo') ?: 'No aplica',
                ':direccion_carta' => $this->__get('direccion_carta'),
                ':carnet' => $this->__get('carnet_discapacidad'),
            ]);
            $idExoneracion = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_exoneracion' => $idExoneracion,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::crearExoneracion - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la exoneración por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la exoneración.');
        }
    }

    /**
     * Exoneraciones SIN estudio socioeconómico (direccion_estudiose IS NULL).
     * Devuelve además los datos que luego precargan el formulario del
     * estudio. Los no administrativos solo ven las que él registró.
     */
    private function exoneracionesPendientes(): array
    {
        $sql = "SELECT e.id_exoneracion, e.motivo, e.otro_motivo, e.fecha_creacion,
                       ss.id_beneficiario, ss.id_empleado,
                       b.nombres, b.apellidos, b.tipo_cedula, b.cedula,
                       b.fecha_nac AS fecha_nacimiento, b.seccion, b.correo,
                       b.telefono, b.genero, b.direccion,
                       p.nombre_pnf AS pnf_nombre
                FROM exoneracion e
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = e.id_solicitud_serv
                INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
                LEFT JOIN pnf p ON p.id_pnf = b.id_pnf
                WHERE e.direccion_estudiose IS NULL";
        if (!$this->esAdministrativo()) {
            $sql .= ' AND ss.id_empleado = :id_empleado';
        }
        $sql .= ' ORDER BY e.fecha_creacion DESC, e.id_exoneracion DESC';

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estudio socioeconómico: la exoneración debe existir, estar en el
     * alcance del usuario y TODAVÍA no tener PDF (solo se generan desde
     * pendientes, como el sistema viejo). Devuelve la fila para auditar.
     */
    private function validarEstudioPendiente(): array
    {
        $fila = $this->asegurarAlcance('exoneraciones', (int) $this->__get('id_registro'));
        if (!empty($fila['direccion_estudiose'])) {
            throw ExcepcionApi::yaExiste('La exoneración ya tiene un estudio socioeconómico generado.');
        }
        return $fila;
    }

    /**
     * Vincula el PDF recién generado con la exoneración. La sentencia se
     * condiciona a que siga pendiente y al alcance del usuario, para cerrar
     * la carrera entre la validación previa y este UPDATE.
     */
    private function vincularEstudio(): void
    {
        $sql = 'UPDATE exoneracion e
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = e.id_solicitud_serv
                SET e.direccion_estudiose = :ruta
                WHERE e.id_exoneracion = :id
                  AND e.direccion_estudiose IS NULL';
        if (!$this->esAdministrativo()) {
            $sql .= ' AND ss.id_empleado = :id_empleado';
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':ruta', $this->__get('direccion_estudiose'));
            $stmt->bindValue(':id', (int) $this->__get('id_registro'), PDO::PARAM_INT);
            if (!$this->esAdministrativo()) {
                $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
            }
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('TrabajoSocialModel::vincularEstudio - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo vincular el estudio con la exoneración.');
        }

        if ($stmt->rowCount() === 0) {
            throw ExcepcionApi::yaExiste('La exoneración ya no está pendiente de estudio.');
        }
    }

    /**
     * Tarjetas del módulo: totales, registros del mes y estado de los
     * estudios socioeconómicos. Alcance: los no administrativos solo
     * cuentan sus propias solicitudes (mismo criterio que listar).
     * Cada solicitud del servicio 4 tiene UN solo sub-registro, por lo que
     * los LEFT JOIN no multiplican filas.
     */
    private function stats(): array
    {
        $mes = 'MONTH(CURRENT_DATE())';
        $anio = 'YEAR(CURRENT_DATE())';
        $sql = 'SELECT
                    SUM(CASE WHEN b.id_becas IS NOT NULL THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN x.id_exoneracion IS NOT NULL THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN f.id_fames IS NOT NULL THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN g.id_gestion IS NOT NULL THEN 1 ELSE 0 END) AS total,
                    SUM(CASE WHEN b.id_becas IS NOT NULL
                                AND MONTH(b.fecha_creacion) = ' . $mes . '
                                AND YEAR(b.fecha_creacion) = ' . $anio . '
                           THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN x.id_exoneracion IS NOT NULL
                                AND MONTH(x.fecha_creacion) = ' . $mes . '
                                AND YEAR(x.fecha_creacion) = ' . $anio . '
                           THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN f.id_fames IS NOT NULL
                                AND MONTH(f.fecha_creacion) = ' . $mes . '
                                AND YEAR(f.fecha_creacion) = ' . $anio . '
                           THEN 1 ELSE 0 END)
                  + SUM(CASE WHEN g.id_gestion IS NOT NULL
                                AND MONTH(g.fecha_creacion) = ' . $mes . '
                                AND YEAR(g.fecha_creacion) = ' . $anio . '
                           THEN 1 ELSE 0 END) AS del_mes,
                    SUM(CASE WHEN x.id_exoneracion IS NOT NULL
                                AND x.direccion_estudiose IS NULL
                           THEN 1 ELSE 0 END) AS pendientes_estudio,
                    SUM(CASE WHEN x.id_exoneracion IS NOT NULL
                                AND x.direccion_estudiose IS NOT NULL
                           THEN 1 ELSE 0 END) AS estudios_generados
                FROM solicitud_de_servicio ss
                LEFT JOIN becas b ON b.id_solicitud_serv = ss.id_solicitud_serv
                LEFT JOIN exoneracion x ON x.id_solicitud_serv = ss.id_solicitud_serv
                LEFT JOIN fames f ON f.id_solicitud_serv = ss.id_solicitud_serv
                LEFT JOIN gestion_emb g ON g.id_solicitud_serv = ss.id_solicitud_serv
                WHERE ss.id_servicios = ' . self::ID_SERVICIO_TS;
        if (!$this->esAdministrativo()) {
            $sql .= ' AND ss.id_empleado = :id_empleado';
        }

        $stmt = $this->conn->prepare($sql);
        if (!$this->esAdministrativo()) {
            $stmt->bindValue(':id_empleado', (int) $this->__get('id_usuario'), PDO::PARAM_INT);
        }
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($fila['total'] ?? 0),
            'del_mes' => (int) ($fila['del_mes'] ?? 0),
            'pendientes_estudio' => (int) ($fila['pendientes_estudio'] ?? 0),
            'estudios_generados' => (int) ($fila['estudios_generados'] ?? 0),
        ];
    }

    // ---------- FAMES ----------

    /**
     * Transacción: solicitud_de_servicio (servicio 4) → detalle_patologia
     * → fames. Cada registro crea su propia fila en detalle_patologia
     * (patrón del sistema viejo: TsModel::registrarFames).
     */
    private function crearFames(): array
    {
        $this->validarRequeridos(['id_beneficiario', 'id_patologia', 'tipo_ayuda']);
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $this->validarBeneficiario($idBeneficiario);
        $this->validarPatologia((int) $this->__get('id_patologia'));

        if ($this->__get('tipo_ayuda') === 'Otros' && (string) $this->__get('otro_tipo') === '') {
            throw ExcepcionApi::validacion('Cuando el tipo de ayuda es "Otros" debes detallarlo.');
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_TS,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => (int) $this->__get('id_usuario'),
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO detalle_patologia (id_patologia) VALUES (:id_patologia)'
            );
            $stmt->execute([':id_patologia' => (int) $this->__get('id_patologia')]);
            $idDetalle = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO fames
                   (id_solicitud_serv, id_detalle_patologia, tipo_ayuda, otro_tipo, fecha_creacion)
                 VALUES (:solicitud, :detalle, :tipo_ayuda, :otro_tipo, CURDATE())'
            );
            $stmt->execute([
                ':solicitud' => $idSolicitud,
                ':detalle' => $idDetalle,
                ':tipo_ayuda' => $this->__get('tipo_ayuda'),
                ':otro_tipo' => $this->__get('otro_tipo') ?: 'No aplica',
            ]);
            $idFames = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_fames' => $idFames,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::crearFames - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar el FAMES por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar el FAMES.');
        }
    }

    // ---------- Embarazadas ----------

    /**
     * Transacción: solicitud_de_servicio (servicio 4) → detalle_patologia
     * → gestion_emb. Reglas: género femenino, una sola gestión "En
     * Proceso" por beneficiaria y estado que nace "En Proceso"
     * (TsModel::registrarEmb / validar_embarazadas_registradas).
     */
    private function crearEmbarazada(): array
    {
        $this->validarRequeridos(['id_beneficiario', 'id_patologia', 'semanas_gest']);
        $idBeneficiario = (int) $this->__get('id_beneficiario');
        $beneficiario = $this->buscarBeneficiario($idBeneficiario);
        // char(10): la semilla usa 'F'/'M' pero se acepta también
        // 'Femenino' por si hay datos heredados.
        $genero = strtoupper(trim((string) ($beneficiario['genero'] ?? '')));
        if (!str_starts_with($genero, 'F')) {
            throw ExcepcionApi::validacion('La gestión de embarazo aplica solo a beneficiarias de género femenino.');
        }
        $this->validarPatologia((int) $this->__get('id_patologia'));

        try {
            $this->conn->beginTransaction();

            // Solo una gestión "En Proceso" por beneficiaria.
            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) FROM solicitud_de_servicio ss
                 INNER JOIN gestion_emb ge ON ge.id_solicitud_serv = ss.id_solicitud_serv
                 WHERE ss.id_beneficiario = :id_beneficiario
                   AND ss.id_servicios = :servicio
                   AND ge.estado = :estado'
            );
            $stmt->execute([
                ':id_beneficiario' => $idBeneficiario,
                ':servicio' => self::ID_SERVICIO_TS,
                ':estado' => 'En Proceso',
            ]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw ExcepcionApi::yaExiste('La beneficiaria ya tiene una gestión de embarazo en proceso.');
            }

            $stmt = $this->conn->prepare(
                'INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
                 VALUES (:servicio, :id_beneficiario, :id_empleado)'
            );
            $stmt->execute([
                ':servicio' => self::ID_SERVICIO_TS,
                ':id_beneficiario' => $idBeneficiario,
                ':id_empleado' => (int) $this->__get('id_usuario'),
            ]);
            $idSolicitud = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO detalle_patologia (id_patologia) VALUES (:id_patologia)'
            );
            $stmt->execute([':id_patologia' => (int) $this->__get('id_patologia')]);
            $idDetalle = (int) $this->conn->lastInsertId();

            $stmt = $this->conn->prepare(
                'INSERT INTO gestion_emb
                   (id_solicitud_serv, id_detalle_patologia, semanas_gest,
                    codigo_patria, serial_patria, estado, fecha_creacion)
                 VALUES (:solicitud, :detalle, :semanas,
                         :codigo_patria, :serial_patria, :estado, CURDATE())'
            );
            $stmt->execute([
                ':solicitud' => $idSolicitud,
                ':detalle' => $idDetalle,
                ':semanas' => (int) $this->__get('semanas_gest'),
                ':codigo_patria' => $this->__get('codigo_patria'),
                ':serial_patria' => $this->__get('serial_patria'),
                ':estado' => 'En Proceso',
            ]);
            $idGestion = (int) $this->conn->lastInsertId();

            $this->conn->commit();
            return [
                'id_gestion' => $idGestion,
                'id_solicitud_serv' => $idSolicitud,
            ];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::crearEmbarazada - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo registrar la gestión por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo registrar la gestión de embarazo.');
        }
    }

    // ---------- Actualización ----------

    /** Despacha la edición por el atributo `tipo` (sub-registro). */
    private function actualizar(): array
    {
        return match ($this->__get('tipo')) {
            'becas' => $this->actualizarBeca(),
            'exoneraciones' => $this->actualizarExoneracion(),
            'fames' => $this->actualizarFames(),
            'embarazadas' => $this->actualizarEmbarazada(),
            default => throw ExcepcionApi::validacion('El tipo de registro no es válido.'),
        };
    }

    /**
     * Edición restringida de la beca: SOLO tipo_banco y cta_bcv.
     * Beneficiario, empleado que atendió y planilla jamás se tocan.
     */
    private function actualizarBeca(): array
    {
        $actual = $this->asegurarAlcance('becas', (int) $this->__get('id_registro'));
        $this->validarRequeridos(['tipo_banco', 'cta_bcv']);

        try {
            $stmt = $this->conn->prepare(
                'UPDATE becas SET tipo_banco = :tipo_banco, cta_bcv = :cta_bcv
                 WHERE id_becas = :id'
            );
            $stmt->execute([
                ':tipo_banco' => $this->__get('tipo_banco'),
                ':cta_bcv' => $this->__get('cta_bcv'),
                ':id' => (int) $actual['id_becas'],
            ]);
            return ['tipo' => 'becas', 'id' => (int) $actual['id_becas'], 'beneficiario' => $actual['beneficiario']];
        } catch (PDOException $e) {
            error_log('TrabajoSocialModel::actualizarBeca - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la beca.');
        }
    }

    /**
     * Edición restringida: SOLO motivo, detalle del motivo y carnet.
     * Los PDFs (carta/estudio) y el beneficiario no se modifican aquí.
     */
    private function actualizarExoneracion(): array
    {
        $actual = $this->asegurarAlcance('exoneraciones', (int) $this->__get('id_registro'));
        $this->validarRequeridos(['motivo', 'carnet_discapacidad']);
        if ($this->__get('motivo') === 'Otro' && (string) $this->__get('otro_motivo') === '') {
            throw ExcepcionApi::validacion('Cuando el motivo es "Otro" debes detallarlo.');
        }
        // Si el cliente no envía el detalle, se conserva el valor actual.
        $otroMotivo = array_key_exists('otro_motivo', $this->atributos)
            ? $this->__get('otro_motivo')
            : $actual['otro_motivo'];

        try {
            $stmt = $this->conn->prepare(
                'UPDATE exoneracion
                 SET motivo = :motivo, otro_motivo = :otro_motivo, carnet_discapacidad = :carnet
                 WHERE id_exoneracion = :id'
            );
            $stmt->execute([
                ':motivo' => $this->__get('motivo'),
                ':otro_motivo' => $otroMotivo ?: 'No aplica',
                ':carnet' => $this->__get('carnet_discapacidad'),
                ':id' => (int) $actual['id_exoneracion'],
            ]);
            return ['tipo' => 'exoneraciones', 'id' => (int) $actual['id_exoneracion'], 'beneficiario' => $actual['beneficiario']];
        } catch (PDOException $e) {
            error_log('TrabajoSocialModel::actualizarExoneracion - ' . $e->getMessage());
            throw ExcepcionApi::errorInterno('No se pudo actualizar la exoneración.');
        }
    }

    /**
     * Edición restringida: SOLO tipo de ayuda (y detalle) y patología.
     * Beneficiario y empleado que atendió jamás se tocan.
     */
    private function actualizarFames(): array
    {
        $actual = $this->asegurarAlcance('fames', (int) $this->__get('id_registro'));
        $this->validarRequeridos(['id_patologia', 'tipo_ayuda']);
        $this->validarPatologia((int) $this->__get('id_patologia'));
        if ($this->__get('tipo_ayuda') === 'Otros' && (string) $this->__get('otro_tipo') === '') {
            throw ExcepcionApi::validacion('Cuando el tipo de ayuda es "Otros" debes detallarlo.');
        }
        $otroTipo = array_key_exists('otro_tipo', $this->atributos)
            ? $this->__get('otro_tipo')
            : $actual['otro_tipo'];

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'UPDATE fames SET tipo_ayuda = :tipo_ayuda, otro_tipo = :otro_tipo
                 WHERE id_fames = :id'
            );
            $stmt->execute([
                ':tipo_ayuda' => $this->__get('tipo_ayuda'),
                ':otro_tipo' => $otroTipo ?: 'No aplica',
                ':id' => (int) $actual['id_fames'],
            ]);

            $stmt = $this->conn->prepare(
                'UPDATE detalle_patologia SET id_patologia = :id_patologia
                 WHERE id_detalle_patologia = :id_detalle'
            );
            $stmt->execute([
                ':id_patologia' => (int) $this->__get('id_patologia'),
                ':id_detalle' => (int) $actual['id_detalle_patologia'],
            ]);

            $this->conn->commit();
            return ['tipo' => 'fames', 'id' => (int) $actual['id_fames'], 'beneficiario' => $actual['beneficiario']];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::actualizarFames - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo actualizar el FAMES por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo actualizar el FAMES.');
        }
    }

    /**
     * Edición restringida: SOLO patología, semanas, patria y estado.
     * Beneficiario y empleado que atendió jamás se tocan.
     */
    private function actualizarEmbarazada(): array
    {
        $actual = $this->asegurarAlcance('embarazadas', (int) $this->__get('id_registro'));
        $this->validarRequeridos(['id_patologia', 'semanas_gest', 'estado']);
        $this->validarPatologia((int) $this->__get('id_patologia'));

        // Solo puede existir UNA gestión "En Proceso" por beneficiaria.
        if ($this->__get('estado') === 'En Proceso') {
            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) FROM gestion_emb g
                 INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = g.id_solicitud_serv
                 WHERE ss.id_beneficiario = :id_beneficiario
                   AND g.estado = :estado
                   AND g.id_gestion <> :id_excluir'
            );
            $stmt->execute([
                ':id_beneficiario' => (int) $actual['id_beneficiario'],
                ':estado' => 'En Proceso',
                ':id_excluir' => (int) $actual['id_gestion'],
            ]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw ExcepcionApi::yaExiste('La beneficiaria ya tiene otra gestión de embarazo en proceso.');
            }
        }

        // Si el cliente no envía la patria, se conserva el valor actual.
        $codigo = array_key_exists('codigo_patria', $this->atributos)
            ? $this->__get('codigo_patria')
            : $actual['codigo_patria'];
        $serial = array_key_exists('serial_patria', $this->atributos)
            ? $this->__get('serial_patria')
            : $actual['serial_patria'];

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                'UPDATE gestion_emb
                 SET semanas_gest = :semanas, codigo_patria = :codigo,
                     serial_patria = :serial, estado = :estado
                 WHERE id_gestion = :id'
            );
            $stmt->execute([
                ':semanas' => (int) $this->__get('semanas_gest'),
                ':codigo' => $codigo,
                ':serial' => $serial,
                ':estado' => $this->__get('estado'),
                ':id' => (int) $actual['id_gestion'],
            ]);

            $stmt = $this->conn->prepare(
                'UPDATE detalle_patologia SET id_patologia = :id_patologia
                 WHERE id_detalle_patologia = :id_detalle'
            );
            $stmt->execute([
                ':id_patologia' => (int) $this->__get('id_patologia'),
                ':id_detalle' => (int) $actual['id_detalle_patologia'],
            ]);

            $this->conn->commit();
            return ['tipo' => 'embarazadas', 'id' => (int) $actual['id_gestion'], 'beneficiario' => $actual['beneficiario']];
        } catch (ExcepcionApi $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            throw $e;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::actualizarEmbarazada - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se pudo actualizar la gestión por una relación inválida.')
                : ExcepcionApi::errorInterno('No se pudo actualizar la gestión de embarazo.');
        }
    }

    // ---------- Eliminación ----------

    /** Despacha la eliminación por el atributo `tipo` (sub-registro). */
    private function eliminar(): array
    {
        return match ($this->__get('tipo')) {
            'becas' => $this->eliminarBeca(),
            'exoneraciones' => $this->eliminarExoneracion(),
            'fames' => $this->eliminarFames(),
            'embarazadas' => $this->eliminarEmbarazada(),
            default => throw ExcepcionApi::validacion('El tipo de registro no es válido.'),
        };
    }

    /** Borra la beca y su solicitud, en transacción. */
    private function eliminarBeca(): array
    {
        $actual = $this->asegurarAlcance('becas', (int) $this->__get('id_registro'));

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM becas WHERE id_becas = :id');
            $stmt->execute([':id' => (int) $actual['id_becas']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::eliminarBeca - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la beca tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la beca.');
        }

        $this->eliminarArchivo($actual['direccion_pdf']);
        return ['tipo' => 'becas', 'id' => (int) $actual['id_becas'], 'beneficiario' => $actual['beneficiario']];
    }

    /** Borra la exoneración y su solicitud, en transacción. */
    private function eliminarExoneracion(): array
    {
        $actual = $this->asegurarAlcance('exoneraciones', (int) $this->__get('id_registro'));

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM exoneracion WHERE id_exoneracion = :id');
            $stmt->execute([':id' => (int) $actual['id_exoneracion']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::eliminarExoneracion - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la exoneración tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la exoneración.');
        }

        $this->eliminarArchivo($actual['direccion_carta']);
        $this->eliminarArchivo($actual['direccion_estudiose']);
        return ['tipo' => 'exoneraciones', 'id' => (int) $actual['id_exoneracion'], 'beneficiario' => $actual['beneficiario']];
    }

    /** Borra el FAMES, su detalle de patología y su solicitud, en transacción. */
    private function eliminarFames(): array
    {
        $actual = $this->asegurarAlcance('fames', (int) $this->__get('id_registro'));

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM fames WHERE id_fames = :id');
            $stmt->execute([':id' => (int) $actual['id_fames']]);
            $stmt = $this->conn->prepare('DELETE FROM detalle_patologia WHERE id_detalle_patologia = :id');
            $stmt->execute([':id' => (int) $actual['id_detalle_patologia']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::eliminarFames - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: el FAMES tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar el FAMES.');
        }

        return ['tipo' => 'fames', 'id' => (int) $actual['id_fames'], 'beneficiario' => $actual['beneficiario']];
    }

    /** Borra la gestión, su detalle de patología y su solicitud, en transacción. */
    private function eliminarEmbarazada(): array
    {
        $actual = $this->asegurarAlcance('embarazadas', (int) $this->__get('id_registro'));

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('DELETE FROM gestion_emb WHERE id_gestion = :id');
            $stmt->execute([':id' => (int) $actual['id_gestion']]);
            $stmt = $this->conn->prepare('DELETE FROM detalle_patologia WHERE id_detalle_patologia = :id');
            $stmt->execute([':id' => (int) $actual['id_detalle_patologia']]);
            $stmt = $this->conn->prepare('DELETE FROM solicitud_de_servicio WHERE id_solicitud_serv = :id');
            $stmt->execute([':id' => (int) $actual['id_solicitud_serv']]);
            $this->conn->commit();
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            error_log('TrabajoSocialModel::eliminarEmbarazada - ' . $e->getMessage());
            throw $e->getCode() === '23000'
                ? ExcepcionApi::enUso('No se puede eliminar: la gestión tiene registros relacionados.')
                : ExcepcionApi::errorInterno('No se pudo eliminar la gestión de embarazo.');
        }

        return ['tipo' => 'embarazadas', 'id' => (int) $actual['id_gestion'], 'beneficiario' => $actual['beneficiario']];
    }

    /**
     * Borra un PDF subido por el módulo (best-effort): el registro ya
     * no existe, por lo que el archivo quedaría huérfano en disco.
     */
    private function eliminarArchivo(?string $rutaRelativa): void
    {
        if (!$rutaRelativa || !str_starts_with($rutaRelativa, 'uploads/trabajo_social/')) {
            return;
        }
        $ruta = BASE_PATH . '/' . ltrim($rutaRelativa, '/');
        if (is_file($ruta)) {
            @unlink($ruta);
        }
    }

    // ---------- Documentos (constancia / referencia) ----------

    /**
     * Datos para la Constancia/Referencia (tercera puerta PDF) de cualquier
     * sub-registro (becas, exoneraciones, fames, embarazadas). Reutiliza
     * asegurarAlcance() para respetar el alcance y resuelve beneficiario,
     * cédula, fecha de atención, empleado, cargo y teléfono.
     */
    private function datosDocumento(): array
    {
        $registro = $this->asegurarAlcance(
            (string) $this->__get('tipo'),
            (int) $this->__get('id_registro')
        );
        $stmt = $this->conn->prepare(
            "SELECT CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
                    CONCAT(b.tipo_cedula, '-', b.cedula) AS cedula,
                    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
                    te.tipo AS cargo,
                    e.telefono
             FROM solicitud_de_servicio ss
             INNER JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
             INNER JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
             INNER JOIN dirpoles_security.tipo_empleado te ON te.id_tipo_emp = e.id_tipo_empleado
             WHERE ss.id_solicitud_serv = :id"
        );
        $stmt->execute([':id' => (int) $registro['id_solicitud_serv']]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            throw ExcepcionApi::noEncontrado('La solicitud del registro ya no existe.');
        }
        return [
            'beneficiario' => (string) $fila['beneficiario'],
            'cedula'       => (string) $fila['cedula'],
            'empleado'     => (string) $fila['empleado'],
            'cargo'        => (string) ($fila['cargo'] ?? ''),
            'telefono'     => (string) ($fila['telefono'] ?? ''),
            'fecha'        => (string) ($registro['fecha_creacion'] ?? ''),
        ];
    }

    // ---------- Validaciones de negocio ----------

    /**
     * Carga el sub-registro con su solicitud y beneficiario, aplicando
     * el alcance: los no administrativos solo ven lo que él registró
     * (mismo criterio que el listado). Lanza 404 si no existe.
     */
    private function asegurarAlcance(string $tipo, int $id): array
    {
        $tablas = [
            'becas' => ['tabla' => 'becas', 'alias' => 'b', 'pk' => 'b.id_becas'],
            'exoneraciones' => ['tabla' => 'exoneracion', 'alias' => 'ex', 'pk' => 'ex.id_exoneracion'],
            'fames' => ['tabla' => 'fames', 'alias' => 'f', 'pk' => 'f.id_fames'],
            'embarazadas' => ['tabla' => 'gestion_emb', 'alias' => 'g', 'pk' => 'g.id_gestion'],
        ];
        if (!isset($tablas[$tipo])) {
            throw ExcepcionApi::validacion('El tipo de registro no es válido.');
        }
        $def = $tablas[$tipo];

        $sql = "SELECT {$def['alias']}.*, ss.id_solicitud_serv, ss.id_beneficiario, ss.id_empleado,
                       CONCAT(ben.nombres, ' ', ben.apellidos) AS beneficiario
                FROM {$def['tabla']} {$def['alias']}
                INNER JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = {$def['alias']}.id_solicitud_serv
                INNER JOIN beneficiario ben ON ben.id_beneficiario = ss.id_beneficiario
                WHERE {$def['pk']} = :id";
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
            throw ExcepcionApi::noEncontrado('El registro no existe o no está disponible para tu usuario.');
        }
        return $registro;
    }

    /** Verifica que los campos indicados estén presentes y no vacíos. */
    private function validarRequeridos(array $campos): void
    {
        foreach ($campos as $campo) {
            if ($this->__get($campo) === null || $this->__get($campo) === '') {
                throw ExcepcionApi::validacion("El campo {$campo} es obligatorio.");
            }
        }
    }

    /** Devuelve la fila del beneficiario activo o lanza 404. */
    private function buscarBeneficiario(int $id): array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_beneficiario, genero FROM beneficiario
             WHERE id_beneficiario = :id AND estatus = 1'
        );
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            throw ExcepcionApi::noEncontrado('El beneficiario no existe o está inactivo.');
        }
        return $fila;
    }

    private function validarBeneficiario(int $id): void
    {
        $this->buscarBeneficiario($id);
    }

    /** La patología debe existir y no ser "Sin patología" (1 ni 2). */
    private function validarPatologia(int $id): void
    {
        $stmt = $this->conn->prepare('SELECT id_patologia FROM patologia WHERE id_patologia = :id');
        $stmt->execute([':id' => $id]);
        $idEncontrada = $stmt->fetchColumn();
        if (!$idEncontrada) {
            throw ExcepcionApi::noEncontrado('La patología seleccionada no existe.');
        }
        if (in_array((int) $idEncontrada, [1, 2], true)) {
            throw ExcepcionApi::validacion('Las patologías "Sin patología" no aplican a este registro.');
        }
    }

    private function esAdministrativo(): bool
    {
        return in_array($this->__get('tipo_empleado'), ['Administrador', 'Superusuario'], true);
    }
}
