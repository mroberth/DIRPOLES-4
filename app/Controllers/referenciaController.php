<?php
// app/Controllers/referenciaController.php
// Módulo Referencias (id_modulo 10 = 'Referencias').
// Solo funciones: Autorizacion primera, Bitacora en cada escritura,
// Notificador a los empleados involucrados y respuestas SIEMPRE por Respuesta.
// El nombre del módulo para RBAC es 'referencias' (modulo.nombre en BD).

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Notificador;
use App\Core\Respuesta;
use App\Models\ReferenciaModel;

// ==================== PUERTA HTML ====================

/** Página: formulario de creación de referencias. */
function showCrearReferencia(): void
{
    Autorizacion::verificar('referencias', 'crear');

    $esAdmin = in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true);
    $idEmpleado = (int) ($_SESSION['id_empleado'] ?? 0);

    // El servicio propio fija el ORIGEN de los no administradores.
    $servicioPropio = null;
    if (!$esAdmin) {
        $modelo = new ReferenciaModel();
        $modelo->__set('id_empleado', $idEmpleado);
        $servicioPropio = $modelo->manejarAccion('servicio_empleado');
    }

    require_once BASE_PATH . '/app/Views/referencias/crear.php';
}

/** Página: consulta de referencias (tabla + detalle + acciones). */
function showConsultarReferencias(): void
{
    Autorizacion::verificar('referencias', 'leer');
    require_once BASE_PATH . '/app/Views/referencias/consultar.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: servicios activos (origen y destino). */
function apiServiciosReferencia(): void
{
    Autorizacion::verificar('referencias', 'crear');

    $modelo = new ReferenciaModel();
    Respuesta::exito($modelo->manejarAccion('servicios'));
}

/** API: empleados activos de un servicio (?id_servicio=N). */
function apiEmpleadosServicioReferencia(): void
{
    Autorizacion::verificar('referencias', 'crear');

    $modelo = new ReferenciaModel();
    $modelo->__set('id_servicio', $_GET['id_servicio'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('empleados_por_servicio'));
}

/** API: beneficiarios activos (select del formulario). */
function apiBeneficiariosReferencia(): void
{
    Autorizacion::verificar('referencias', 'crear');

    $modelo = new ReferenciaModel();
    Respuesta::exito($modelo->manejarAccion('beneficiarios'));
}

/** API: lista de referencias según alcance (DataTable de consultar). */
function apiListarReferencias(): void
{
    Autorizacion::verificar('referencias', 'leer');

    $modelo = new ReferenciaModel();
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));
    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: detalle de una referencia + historial del log. */
function apiObtenerReferencia(): void
{
    Autorizacion::verificar('referencias', 'leer');

    $modelo = new ReferenciaModel();
    $modelo->__set('id_referencia', $_GET['id'] ?? 0);
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: tarjetas de resumen del módulo (con alcance). */
function apiReferenciasStats(): void
{
    Autorizacion::verificar('referencias', 'leer');

    $modelo = new ReferenciaModel();
    $modelo->__set('id_empleado', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/**
 * API: crear referencia (estado 'Pendiente').
 * El modelo fuerza el ORIGEN al empleado de la sesión si no es admin y
 * exige servicio destino distinto del origen.
 */
function apiCrearReferencia(): void
{
    Autorizacion::verificar('referencias', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new ReferenciaModel();
    $modelo->__set('id_beneficiario',     $entrada['id_beneficiario'] ?? 0);
    $modelo->__set('id_servicio_origen',  $entrada['id_servicio_origen'] ?? 0);
    $modelo->__set('id_empleado_origen',  $entrada['id_empleado_origen'] ?? 0);
    $modelo->__set('id_servicio_destino', $entrada['id_servicio_destino'] ?? 0);
    $modelo->__set('id_empleado_destino', $entrada['id_empleado_destino'] ?? 0);
    $modelo->__set('motivo',              $entrada['motivo'] ?? '');
    $modelo->__set('observaciones',       $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',         (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));

    $nueva = $modelo->manejarAccion('crear');

    Bitacora::registrar(
        'Referencias',
        'Registro',
        'Creó la referencia #' . $nueva['id_referencia'] . ' del beneficiario "'
        . $nueva['beneficiario'] . '" hacia ' . $nueva['nombre_destino'] . '.'
    );

    // Aviso al empleado que recibe la referencia (regla del módulo).
    Notificador::enviar(
        (int) $nueva['id_empleado_destino'],
        'Nueva Referencia',
        'referencias/consultar',
        'referencia'
    );

    Respuesta::exito($nueva, 201);
}

/** API: aceptar referencia (solo destino o admin, solo desde 'Pendiente'). */
function apiAceptarReferencia(): void
{
    Autorizacion::verificar('referencias', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new ReferenciaModel();
    $modelo->__set('id_referencia', $entrada['id_referencia'] ?? 0);
    $modelo->__set('id_empleado',   (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));

    $resultado = $modelo->manejarAccion('aceptar');

    Bitacora::registrar(
        'Referencias',
        'Actualización',
        'Aceptó la referencia #' . $resultado['id_referencia'] . '.'
    );

    // Aviso al empleado que refirió (regla del módulo).
    Notificador::enviar(
        (int) $resultado['id_empleado_origen'],
        'Referencia aceptada',
        'referencias/consultar',
        'referencia'
    );

    Respuesta::exito($resultado);
}

/**
 * API: rechazar referencia con motivo obligatorio (se guarda en el log;
 * las observaciones originales de la referencia no se modifican).
 */
function apiRechazarReferencia(): void
{
    Autorizacion::verificar('referencias', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new ReferenciaModel();
    $modelo->__set('id_referencia',  $entrada['id_referencia'] ?? 0);
    $modelo->__set('observaciones',  $entrada['observaciones'] ?? '');
    $modelo->__set('id_empleado',    (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));

    $resultado = $modelo->manejarAccion('rechazar');

    Bitacora::registrar(
        'Referencias',
        'Actualización',
        'Rechazó la referencia #' . $resultado['id_referencia'] . '.'
    );

    Notificador::enviar(
        (int) $resultado['id_empleado_origen'],
        'Referencia rechazada',
        'referencias/consultar',
        'referencia'
    );

    Respuesta::exito($resultado);
}

/** API: eliminar referencia (solo origen o admin, solo 'Pendiente'). */
function apiEliminarReferencia(): void
{
    Autorizacion::verificar('referencias', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new ReferenciaModel();
    $modelo->__set('id_referencia', $entrada['id_referencia'] ?? 0);
    $modelo->__set('id_empleado',   (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('es_admin', in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true));

    $referencia = $modelo->manejarAccion('eliminar');

    Bitacora::registrar(
        'Referencias',
        'Eliminación',
        'Eliminó la referencia #' . $referencia['id_referencia']
        . ' (estado Pendiente).'
    );

    // El destino (que tenía la pendiente) se entera de la cancelación.
    if ((int) ($_SESSION['id_empleado'] ?? 0) !== (int) $referencia['id_empleado_destino']) {
        Notificador::enviar(
            (int) $referencia['id_empleado_destino'],
            'Referencia cancelada',
            'referencias/consultar',
            'referencia'
        );
    }

    Respuesta::exito(['eliminado' => true]);
}
