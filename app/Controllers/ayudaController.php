<?php
// app/Controllers/ayudaController.php

use App\Core\Respuesta;
use App\Models\AyudaModel;

// ==================== PUERTA HTML ====================

/** Página: Centro de Ayuda (accesible a todos los empleados autenticados). */
function showAyuda(): void
{
    // Solo exige sesión activa (validada previamente por el SessionAuthMiddleware).
    $nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
    $rol_usuario = $_SESSION['tipo_empleado'] ?? 'Empleado';

    require_once BASE_PATH . '/app/Views/ayuda/ayuda.php';
}

// ==================== PUERTA JSON (API) ====================

/** 
 * API: catálogo de guías y preguntas frecuentes filtrado dinámicamente
 * según los permisos reales del rol del usuario ($_SESSION['modulosPermitidos'])
 * y el nivel de administración ($_SESSION['tipo_empleado']).
 */
function apiObtenerGuias(): void
{
    $modelo = new AyudaModel();
    $modelo->__set('modulos_permitidos', $_SESSION['modulosPermitidos'] ?? []);
    
    $tipoEmp = strtolower((string) ($_SESSION['tipo_empleado'] ?? ''));
    $esAdmin = (strpos($tipoEmp, 'administrador') !== false || strpos($tipoEmp, 'superusuario') !== false);
    $modelo->__set('es_admin', $esAdmin);

    $guias = $modelo->manejarAccion('obtener_guias');

    Respuesta::exito($guias);
}
