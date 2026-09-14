<?php
// app/Controllers/backupController.php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Models\BackupModel;

/**
 * Controlador del módulo RESPALDO de base de datos — SOLO funciones.
 *
 * Acceso restringido a Administrador/Superusuario. La descarga devuelve un
 * archivo .sql (no HTML ni JSON): es una excepción deliberada a las dos
 * puertas, igual que sse/notificaciones.
 */

/** Exige permiso de lectura de Configuración + rol administrador. */
function exigirAdminRespaldo(): void
{
    Autorizacion::verificar('configuracion', 'leer');

    $tipo = mb_strtolower((string) ($_SESSION['tipo_empleado'] ?? ''));
    if (!str_contains($tipo, 'administrador') && !str_contains($tipo, 'superusuario')) {
        throw ExcepcionApi::accesoDenegado('Solo un administrador puede gestionar respaldos.');
    }
}

/** Página: panel de respaldos. */
function showRespaldo(): void
{
    exigirAdminRespaldo();

    $dbNegocio   = (string) env('DB_NAME');
    $dbSeguridad = (string) env('DB_SECURITY_NAME');

    require_once BASE_PATH . '/app/Views/configuracion/backup.php';
}

/** Descarga el respaldo. GET ?tipo=negocio|seguridad */
function descargarRespaldo(): void
{
    exigirAdminRespaldo();

    $tipo = $_GET['tipo'] ?? '';
    if (!in_array($tipo, ['negocio', 'seguridad'], true)) {
        throw ExcepcionApi::validacion('Tipo de respaldo no válido.');
    }

    $modelo = new BackupModel();
    if ($tipo === 'negocio') {
        $contenido = $modelo->manejarAccion('backup_business');
        $db = (string) env('DB_NAME');
    } else {
        $contenido = $modelo->manejarAccion('backup_security');
        $db = (string) env('DB_SECURITY_NAME');
    }

    // Auditoría (acción 'Respaldo' del ENUM de bitacora).
    Bitacora::registrar('Configuracion', 'Respaldo', "Descargó un respaldo de la base de datos '{$db}'.");

    $fileName = 'backup_' . $tipo . '_' . date('Ymd_His') . '.sql';

    if (!headers_sent()) {
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: no-store');
    }
    echo $contenido;
    exit;
}
