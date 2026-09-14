<?php
namespace App\Models;

use PDO;
use Throwable;
use App\Core\ExcepcionApi;

/**
 * app/Models/DashboardModel.php
 * ---------------------------------------------------------------
 * Estadísticas del panel de Inicio (BD de negocio).
 *
 * Cada método devuelve un mapa PLANO `clave => número` pensado para que el
 * frontend rellene nodos con `[data-stat="clave"]`. El controlador elige qué
 * bloque corresponde al rol de la sesión y lo responde con `Respuesta::exito`.
 *
 * CRITERIO "BEST-EFFORT": una estadística es información auxiliar. Si una
 * consulta falla (tabla ausente, columna cambiada), se registra en el log y
 * se devuelve 0 en lugar de tumbar todo el dashboard con un 500.
 *
 * Todas las consultas filtran por el `id_empleado` de la sesión (salvo el
 * resumen del administrador, que es global). Nunca se recibe del cliente.
 */
class DashboardModel extends BusinessModel
{
    private array $atributos = [];

    public function __set($nombre, $valor): void
    {
        $this->atributos[$nombre] = $valor;
    }

    public function __get($nombre): mixed
    {
        return $this->atributos[$nombre] ?? null;
    }

    /** Única puerta pública (despachador). */
    public function manejarAccion($accion): array
    {
        return match ($accion) {
            'stats_psicologia'    => $this->statsPsicologia(),
            'stats_medicina'      => $this->statsMedicina(),
            'stats_orientacion'   => $this->statsOrientacion(),
            'stats_trabajo_social' => $this->statsTrabajoSocial(),
            'stats_discapacidad'  => $this->statsDiscapacidad(),
            'stats_admin'         => $this->statsAdmin(),
            'stats_generico'      => $this->statsGenerico(),
            default => throw ExcepcionApi::errorInterno("Acción no válida en DashboardModel: '{$accion}'."),
        };
    }

    // ------------------------------------------------------------------
    // Bloques por rol
    // ------------------------------------------------------------------

    private function statsPsicologia(): array
    {
        $id = $this->idEmpleado();
        $base = "FROM consulta_psicologica cp
                 INNER JOIN solicitud_de_servicio sds ON cp.id_solicitud_serv = sds.id_solicitud_serv
                 WHERE sds.id_empleado = :id_empleado";

        return [
            'total_conteo'       => $this->contar("SELECT COUNT(*) $base", [':id_empleado' => $id]),
            'total_diagnosticos' => $this->contar("SELECT COUNT(*) $base AND cp.tipo_consulta = :tipo", [':id_empleado' => $id, ':tipo' => 'Diagnóstico']),
            'citas_mes'          => $this->contar(
                "SELECT COUNT(*) FROM cita
                 WHERE id_empleado = :id_empleado
                   AND MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE())",
                [':id_empleado' => $id]
            ),
            'retiros_activos'    => $this->contar("SELECT COUNT(*) $base AND cp.tipo_consulta = :tipo", [':id_empleado' => $id, ':tipo' => 'Retiro temporal']),
            'cambios_carrera'    => $this->contar("SELECT COUNT(*) $base AND cp.tipo_consulta = :tipo", [':id_empleado' => $id, ':tipo' => 'Cambio de carrera']),
        ];
    }

    private function statsMedicina(): array
    {
        $id = $this->idEmpleado();

        return [
            'total_conteo' => $this->contar(
                "SELECT COUNT(*) FROM solicitud_de_servicio WHERE id_servicios = 2 AND id_empleado = :id_empleado",
                [':id_empleado' => $id]
            ),
            'consultas_mes' => $this->contar(
                "SELECT COUNT(*) FROM consulta_medica cm
                 INNER JOIN solicitud_de_servicio sds ON cm.id_solicitud_serv = sds.id_solicitud_serv
                 WHERE sds.id_empleado = :id_empleado
                   AND MONTH(cm.fecha_creacion) = MONTH(CURRENT_DATE()) AND YEAR(cm.fecha_creacion) = YEAR(CURRENT_DATE())",
                [':id_empleado' => $id]
            ),
            'insumos_disponibles' => $this->contar(
                "SELECT COUNT(*) FROM insumos WHERE cantidad >= 1 AND estatus = 'Disponible'"
            ),
            'insumos_bajo_stock' => $this->contar(
                "SELECT COUNT(*) FROM insumos WHERE cantidad < 10"
            ),
        ];
    }

    private function statsOrientacion(): array
    {
        $id = $this->idEmpleado();
        $base = "FROM orientacion o
                 INNER JOIN solicitud_de_servicio sds ON o.id_solicitud_serv = sds.id_solicitud_serv
                 WHERE sds.id_empleado = :id_empleado";

        return [
            'total_conteo' => $this->contar("SELECT COUNT(*) $base", [':id_empleado' => $id]),
            'orientacion_mes' => $this->contar(
                "SELECT COUNT(*) $base AND MONTH(o.fecha_creacion) = MONTH(CURRENT_DATE()) AND YEAR(o.fecha_creacion) = YEAR(CURRENT_DATE())",
                [':id_empleado' => $id]
            ),
            'sin_indicaciones' => $this->contar(
                "SELECT COUNT(*) $base AND (o.indicaciones_orientacion IS NULL OR TRIM(o.indicaciones_orientacion) = '')",
                [':id_empleado' => $id]
            ),
            'con_observaciones' => $this->contar(
                "SELECT COUNT(*) $base AND (o.obs_adic_orientacion IS NOT NULL AND TRIM(o.obs_adic_orientacion) <> '')",
                [':id_empleado' => $id]
            ),
        ];
    }

    private function statsTrabajoSocial(): array
    {
        $id = $this->idEmpleado();

        return [
            'total_embarazadas'     => $this->contarPorServicio('gestion_emb', $id),
            'total_exoneraciones'   => $this->contarPorServicio('exoneracion', $id),
            'total_fames'           => $this->contarPorServicio('fames', $id),
            'total_becas'           => $this->contarPorServicio('becas', $id),
            'embarazadas_mes'       => $this->contarPorServicioMes('gestion_emb', $id, 'fecha_creacion'),
            'exoneraciones_mes'     => $this->contarPorServicioMes('exoneracion', $id, 'fecha_creacion'),
            'fames_mes'             => $this->contarPorServicioMes('fames', $id, 'fecha_creacion'),
            'becas_mes'             => $this->contarPorServicioMes('becas', $id, 'fecha_creacion'),
        ];
    }

    private function statsDiscapacidad(): array
    {
        $id = $this->idEmpleado();
        $base = "FROM discapacidad d
                 INNER JOIN solicitud_de_servicio sds ON d.id_solicitud_serv = sds.id_solicitud_serv
                 WHERE sds.id_empleado = :id_empleado";

        return [
            'total_discapacidades'  => $this->contar("SELECT COUNT(*) $base", [':id_empleado' => $id]),
            'discapacidades_mes'    => $this->contar(
                "SELECT COUNT(*) $base AND MONTH(d.fecha_creacion) = MONTH(CURRENT_DATE()) AND YEAR(d.fecha_creacion) = YEAR(CURRENT_DATE())",
                [':id_empleado' => $id]
            ),
            'discapacidades_graves' => $this->contar("SELECT COUNT(*) $base AND d.grado = :grado", [':id_empleado' => $id, ':grado' => 'Grave']),
            'con_carnet'            => $this->contar(
                "SELECT COUNT(*) $base AND (d.carnet_discapacidad IS NOT NULL AND TRIM(d.carnet_discapacidad) <> '')",
                [':id_empleado' => $id]
            ),
        ];
    }

    /** Resumen global para Administrador/Superusuario. */
    private function statsAdmin(): array
    {
        return [
            // Empleados vive en la BD de seguridad (otra conexión).
            'admin_empleados_total'    => $this->contarSeguridad("SELECT COUNT(*) FROM empleado"),
            'admin_bitacora_total'     => $this->contarSeguridad("SELECT COUNT(*) FROM bitacora"),
            'admin_permisos_total'     => $this->contarSeguridad("SELECT COUNT(*) FROM rol_modulo_permiso"),

            // Módulos de la BD de negocio.
            'admin_beneficiarios_total' => $this->contar("SELECT COUNT(*) FROM beneficiario"),
            'admin_citas_total'         => $this->contar("SELECT COUNT(*) FROM cita"),
            'admin_psicologia_total'    => $this->contar("SELECT COUNT(*) FROM consulta_psicologica"),
            'admin_medicina_total'      => $this->contar("SELECT COUNT(*) FROM consulta_medica"),
            'admin_orientacion_total'   => $this->contar("SELECT COUNT(*) FROM orientacion"),
            'admin_ts_total'            => $this->contar("SELECT COUNT(*) FROM solicitud_de_servicio WHERE id_servicios = 4"),
            'admin_discapacidad_total'  => $this->contar("SELECT COUNT(*) FROM solicitud_de_servicio WHERE id_servicios = 5"),
            'admin_insumos_total'       => $this->contar("SELECT COUNT(*) FROM insumos"),
            'admin_referidos_total'     => $this->contar("SELECT COUNT(*) FROM referencias"),
            'admin_jornadas_total'      => $this->contar("SELECT COUNT(*) FROM jornadas_medicas"),
            'admin_mobiliario_total'    => $this->contar("SELECT COUNT(*) FROM mobiliario"),
            'admin_vehiculos_total'     => $this->contar("SELECT COUNT(*) FROM vehiculos"),
            'admin_horarios_total'      => $this->contar("SELECT COUNT(*) FROM horario"),
        ];
    }

    /** Resumen neutro para roles sin módulo clínico propio. */
    private function statsGenerico(): array
    {
        return [
            'total_beneficiarios' => $this->contar("SELECT COUNT(*) FROM beneficiario"),
            'citas_hoy'           => $this->contar("SELECT COUNT(*) FROM cita WHERE fecha = CURRENT_DATE()"),
        ];
    }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------

    private function idEmpleado(): int
    {
        return (int) ($this->__get('id_empleado') ?? 0);
    }

    /** Cuenta registros de una tabla de servicio filtrando por el empleado. */
    private function contarPorServicio(string $tabla, int $id): int
    {
        // $tabla/$pk vienen de constantes internas (whitelist), no del cliente.
        return $this->contar(
            "SELECT COUNT(*) FROM `$tabla` t
             INNER JOIN solicitud_de_servicio sds ON t.id_solicitud_serv = sds.id_solicitud_serv
             WHERE sds.id_empleado = :id_empleado",
            [':id_empleado' => $id]
        );
    }

    /** Igual que contarPorServicio pero acotado al mes actual. */
    private function contarPorServicioMes(string $tabla, int $id, string $fechaCol): int
    {
        return $this->contar(
            "SELECT COUNT(*) FROM `$tabla` t
             INNER JOIN solicitud_de_servicio sds ON t.id_solicitud_serv = sds.id_solicitud_serv
             WHERE sds.id_empleado = :id_empleado
               AND MONTH(t.`$fechaCol`) = MONTH(CURRENT_DATE()) AND YEAR(t.`$fechaCol`) = YEAR(CURRENT_DATE())",
            [':id_empleado' => $id]
        );
    }

    /**
     * Ejecuta un SELECT COUNT(*) y devuelve el entero. Best-effort: ante
     * cualquier fallo técnico registra y devuelve 0 (no rompe el dashboard).
     */
    private function contar(string $sql, array $parametros = []): int
    {
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($parametros as $clave => $valor) {
                $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('DashboardModel::contar - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Igual que contar() pero contra la BD de seguridad (tablas como
     * `empleado`, `bitacora`, `rol_modulo_permiso`). Best-effort.
     */
    private function contarSeguridad(string $sql, array $parametros = []): int
    {
        try {
            $this->Security(); // abre $this->conn_security si no existe
            $stmt = $this->conn_security->prepare($sql);
            foreach ($parametros as $clave => $valor) {
                $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('DashboardModel::contarSeguridad - ' . $e->getMessage());
            return 0;
        }
    }
}
