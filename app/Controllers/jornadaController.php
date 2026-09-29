<?php
// app/Controllers/jornadaController.php
// Módulo Jornadas Médicas (id_modulo 11 = 'Jornadas').
// Solo funciones: Autorizacion primera, Bitacora en cada escritura,
// Notificador al crear (a los demás empleados con permiso del módulo) y
// respuestas SIEMPRE por Respuesta.
// El nombre del módulo para RBAC es 'jornadas' (modulo.nombre en BD).

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\Notificador;
use App\Core\Respuesta;
use App\Models\JornadaModel;

// ==================== PUERTA HTML ====================

/** Página: formulario de creación de jornadas. */
function showCrearJornada(): void
{
    Autorizacion::verificar('jornadas', 'crear');
    require_once BASE_PATH . '/app/Views/jornadas/crear.php';
}

/** Página: consulta de jornadas (tabla + edición + acciones). */
function showConsultarJornadas(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    // La vista oculta los botones que el rol no puede usar (el backend
    // vuelve a verificar cada escritura).
    $puedeCrear    = Autorizacion::tiene('jornadas', 'crear');
    $puedeEditar   = Autorizacion::tiene('jornadas', 'editar');
    $puedeEliminar = Autorizacion::tiene('jornadas', 'eliminar');

    require_once BASE_PATH . '/app/Views/jornadas/consultar.php';
}

/**
 * Página: detalle de una jornada (cabecera + aforo + asistentes +
 * diagnósticos). El {id} de la ruta llega por $_GET['id']; si la jornada
 * no existe la excepción 404 la resuelve el handler global con la página 404.
 */
function showDetalleJornada(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada', $_GET['id'] ?? 0);
    $jornada = $modelo->manejarAccion('obtener');

    $puedeCrear    = Autorizacion::tiene('jornadas', 'crear');
    $puedeEditar   = Autorizacion::tiene('jornadas', 'editar');
    $puedeEliminar = Autorizacion::tiene('jornadas', 'eliminar');

    require_once BASE_PATH . '/app/Views/jornadas/detalle.php';
}

// ==================== PUERTA JSON (API) ====================

/** API: catálogos del módulo (estáticos: tipo de cédula, género, etc.). */
function apiCatalogosJornada(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    Respuesta::exito($modelo->manejarAccion('catalogos'));
}

/** API: lista de jornadas (DataTable de consultar). */
function apiListarJornadas(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** API: cabecera de una jornada + contadores (detalle y modal de edición). */
function apiObtenerJornada(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada', $_GET['id'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('obtener'));
}

/** API: tarjetas de resumen del módulo. */
function apiJornadasStats(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    Respuesta::exito($modelo->manejarAccion('stats'));
}

/** API: asistentes de una jornada (?id de la ruta = jornada). */
function apiAsistentesJornada(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada', $_GET['id'] ?? 0);
    if (isset($_GET['limit'])) {
        $modelo->__set('limit', $_GET['limit']);
    }
    if (isset($_GET['offset'])) {
        $modelo->__set('offset', $_GET['offset']);
    }
    Respuesta::exito($modelo->manejarAccion('asistentes'));
}

/** API: diagnósticos (con insumos) de un asistente (?id de la ruta). */
function apiDiagnosticosAsistente(): void
{
    Autorizacion::verificar('jornadas', 'leer');

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada_beneficiario', $_GET['id'] ?? 0);
    Respuesta::exito($modelo->manejarAccion('diagnosticos'));
}

/** API: autocompletado por cédula (?tipo=V&cedula=12345678). */
function apiBuscarPersonaJornada(): void
{
    Autorizacion::verificar('jornadas', 'crear');

    $modelo = new JornadaModel();
    $modelo->__set('tipo_documento', $_GET['tipo'] ?? '');
    $modelo->__set('cedula',         $_GET['cedula'] ?? '');
    Respuesta::exito($modelo->manejarAccion('buscar_persona'));
}

/** API: insumos usables en un diagnóstico (disponibles y sin vencer). */
function apiInsumosDisponiblesJornada(): void
{
    Autorizacion::verificar('jornadas', 'crear');

    $modelo = new JornadaModel();
    Respuesta::exito($modelo->manejarAccion('insumos_disponibles'));
}

/** API: crear jornada (nace 'Activa'). */
function apiCrearJornada(): void
{
    Autorizacion::verificar('jornadas', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('nombre_jornada', $entrada['nombre_jornada'] ?? '');
    $modelo->__set('tipo_jornada',   $entrada['tipo_jornada'] ?? '');
    $modelo->__set('aforo_maximo',   $entrada['aforo_maximo'] ?? 0);
    $modelo->__set('fecha_inicio',   $entrada['fecha_inicio'] ?? '');
    $modelo->__set('fecha_fin',      $entrada['fecha_fin'] ?? '');
    $modelo->__set('ubicacion',      $entrada['ubicacion'] ?? '');
    $modelo->__set('descripcion',    $entrada['descripcion'] ?? '');
    $modelo->__set('id_empleado',    (int) ($_SESSION['id_empleado'] ?? 0));

    $jornada = $modelo->manejarAccion('crear');

    Bitacora::registrar(
        'Jornadas',
        'Registro',
        'Creó la jornada "' . $jornada['nombre_jornada'] . '" (aforo '
        . $jornada['aforo_maximo'] . ').'
    );

    // Aviso a los demás empleados con permiso de lectura del módulo.
    $destinatarios = $modelo->manejarAccion('destinatarios');
    foreach ($destinatarios as $idReceptor) {
        Notificador::enviar(
            (int) $idReceptor,
            'Nueva Jornada Médica: ' . $jornada['nombre_jornada'],
            'jornadas/consultar',
            'jornada'
        );
    }

    Respuesta::exito($jornada, 201);
}

/** API: editar cabecera de la jornada (incluye cambio de estatus y aforo). */
function apiActualizarJornada(): void
{
    Autorizacion::verificar('jornadas', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada',    $entrada['id_jornada'] ?? 0);
    $modelo->__set('nombre_jornada', $entrada['nombre_jornada'] ?? '');
    $modelo->__set('tipo_jornada',   $entrada['tipo_jornada'] ?? '');
    $modelo->__set('aforo_maximo',   $entrada['aforo_maximo'] ?? 0);
    $modelo->__set('fecha_inicio',   $entrada['fecha_inicio'] ?? '');
    $modelo->__set('fecha_fin',      $entrada['fecha_fin'] ?? '');
    $modelo->__set('ubicacion',      $entrada['ubicacion'] ?? '');
    $modelo->__set('descripcion',    $entrada['descripcion'] ?? '');
    $modelo->__set('estatus',        $entrada['estatus'] ?? '');

    $resultado = $modelo->manejarAccion('actualizar');

    Bitacora::registrar(
        'Jornadas',
        'Actualización',
        'Actualizó la jornada "' . $resultado['nombre_jornada'] . '"'
        . ($resultado['estatus_anterior'] !== $resultado['estatus']
            ? ' (estatus ' . $resultado['estatus_anterior'] . ' → ' . $resultado['estatus'] . ')'
            : '')
        . '.'
    );

    Respuesta::exito($resultado);
}

/** API: eliminar jornada (solo si no tiene asistentes). */
function apiEliminarJornada(): void
{
    Autorizacion::verificar('jornadas', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada', $entrada['id_jornada'] ?? 0);

    $resultado = $modelo->manejarAccion('eliminar');

    Bitacora::registrar(
        'Jornadas',
        'Eliminación',
        'Eliminó la jornada "' . $resultado['nombre_jornada'] . '" (sin asistentes).'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: registrar un asistente (respeta aforo y duplicados). */
function apiAgregarAsistente(): void
{
    Autorizacion::verificar('jornadas', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada',        $entrada['id_jornada'] ?? 0);
    $modelo->__set('tipo_cedula',       $entrada['tipo_cedula'] ?? '');
    $modelo->__set('cedula',            $entrada['cedula'] ?? '');
    $modelo->__set('nombres',           $entrada['nombres'] ?? '');
    $modelo->__set('apellidos',         $entrada['apellidos'] ?? '');
    $modelo->__set('fecha_nacimiento',  $entrada['fecha_nacimiento'] ?? '');
    $modelo->__set('genero',            $entrada['genero'] ?? '');
    $modelo->__set('tipo_paciente',     $entrada['tipo_paciente'] ?? '');
    $modelo->__set('telefono',          $entrada['telefono'] ?? '');
    $modelo->__set('correo',            $entrada['correo'] ?? '');
    $modelo->__set('direccion',         $entrada['direccion'] ?? '');

    $resultado = $modelo->manejarAccion('agregar_asistente');

    Bitacora::registrar(
        'Jornadas',
        'Registro',
        'Registró a "' . $resultado['nombres'] . ' ' . $resultado['apellidos']
        . '" (CI: ' . $resultado['tipo_cedula'] . '-' . $resultado['cedula']
        . ') en la jornada "' . $resultado['nombre_jornada'] . '".'
    );

    Respuesta::exito($resultado, 201);
}

/** API: quitar un asistente (bloqueado si ya tiene diagnóstico). */
function apiEliminarAsistente(): void
{
    Autorizacion::verificar('jornadas', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada_beneficiario', $entrada['id_jornada_beneficiario'] ?? 0);

    $resultado = $modelo->manejarAccion('eliminar_asistente');

    Bitacora::registrar(
        'Jornadas',
        'Eliminación',
        'Eliminó de la jornada "' . $resultado['nombre_jornada'] . '" a "'
        . $resultado['nombre'] . '" (CI: ' . $resultado['cedula'] . ').'
    );

    Respuesta::exito(['eliminado' => true]);
}

/** API: agregar un diagnóstico (con insumos que descuentan stock). */
function apiAgregarDiagnostico(): void
{
    Autorizacion::verificar('jornadas', 'crear');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada_beneficiario', $entrada['id_jornada_beneficiario'] ?? 0);
    $modelo->__set('diagnostico',   $entrada['diagnostico'] ?? '');
    $modelo->__set('tratamiento',   $entrada['tratamiento'] ?? '');
    $modelo->__set('observaciones', $entrada['observaciones'] ?? '');
    $modelo->__set('insumos',       $entrada['insumos'] ?? []);
    $modelo->__set('id_empleado',   (int) ($_SESSION['id_empleado'] ?? 0));

    $resultado = $modelo->manejarAccion('agregar_diagnostico');

    $detalle = '';
    foreach ($resultado['insumos'] as $insumo) {
        $detalle .= ' ' . $insumo['nombre'] . ' ×' . $insumo['cantidad'] . ',';
    }
    $detalle = $detalle !== '' ? ' Insumos usados:' . rtrim($detalle, ',') . '.' : '';

    Bitacora::registrar(
        'Jornadas',
        'Registro',
        'Registró un diagnóstico para "' . $resultado['persona']
        . '" en la jornada "' . $resultado['nombre_jornada'] . '": '
        . $resultado['diagnostico'] . '.' . $detalle
    );

    Respuesta::exito($resultado, 201);
}

/** API: corregir los textos de un diagnóstico (nunca insumos ni persona). */
function apiActualizarDiagnostico(): void
{
    Autorizacion::verificar('jornadas', 'editar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada_diagnostico', $entrada['id_jornada_diagnostico'] ?? 0);
    $modelo->__set('diagnostico',   $entrada['diagnostico'] ?? '');
    $modelo->__set('tratamiento',   $entrada['tratamiento'] ?? '');
    $modelo->__set('observaciones', $entrada['observaciones'] ?? '');

    $resultado = $modelo->manejarAccion('actualizar_diagnostico');

    Bitacora::registrar(
        'Jornadas',
        'Actualización',
        'Actualizó el diagnóstico #' . $resultado['id_jornada_diagnostico']
        . ' de "' . $resultado['persona'] . '" en la jornada "'
        . $resultado['nombre_jornada'] . '".'
    );

    Respuesta::exito($resultado);
}

/** API: borrar diagnóstico (NO devuelve el stock ya descontado). */
function apiEliminarDiagnostico(): void
{
    Autorizacion::verificar('jornadas', 'eliminar');

    $entrada = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $modelo = new JornadaModel();
    $modelo->__set('id_jornada_diagnostico', $entrada['id_jornada_diagnostico'] ?? 0);

    $resultado = $modelo->manejarAccion('eliminar_diagnostico');

    Bitacora::registrar(
        'Jornadas',
        'Eliminación',
        'Eliminó el diagnóstico #' . $resultado['id_jornada_diagnostico']
        . ' de "' . $resultado['persona'] . '" en la jornada "'
        . $resultado['nombre_jornada'] . '".'
        . ($resultado['insumos'] > 0 ? ' Los insumos usados no se devuelven al inventario.' : '')
    );

    Respuesta::exito(['eliminado' => true]);
}
