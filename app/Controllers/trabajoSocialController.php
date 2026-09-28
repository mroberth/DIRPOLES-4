<?php

use App\Core\Autorizacion;
use App\Core\Bitacora;
use App\Core\ExcepcionApi;
use App\Core\Respuesta;
use App\Models\TrabajoSocialModel;

/**
 * Módulo Trabajo Social (id_modulo 7).
 * Controllers = SOLO funciones (regla del repositorio).
 */

function contextoTrabajoSocial(TrabajoSocialModel $modelo): void
{
    $modelo->__set('id_usuario', (int) ($_SESSION['id_empleado'] ?? 0));
    $modelo->__set('tipo_empleado', $_SESSION['tipo_empleado'] ?? '');
}

/** Lee el cuerpo JSON de una petición (puerta JSON). */
function entradaTrabajoSocialJson(): array
{
    $entrada = json_decode(file_get_contents('php://input'), true);
    return is_array($entrada) ? $entrada : [];
}

/** Nombre de cada sub-registro con su artículo (para Bitácora y avisos). */
function trabajoSocialEtiquetaTipo(string $tipo): string
{
    return match ($tipo) {
        'becas' => 'la beca',
        'exoneraciones' => 'la exoneración',
        'fames' => 'el FAMES',
        'embarazadas' => 'la gestión de embarazo',
        default => 'el registro',
    };
}

// ---------- Páginas (puerta HTML) ----------

function showCrearTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    require_once BASE_PATH . '/app/Views/trabajo-social/crear.php';
}

function showConsultarTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    require_once BASE_PATH . '/app/Views/trabajo-social/consultar.php';
}

// ---------- APIs (puerta JSON) ----------

/** Catálogo compartido de la página: beneficiarios activos y patologías. */
function apiCatalogosTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    Respuesta::exito([
        'beneficiarios' => $modelo->manejarAccion('beneficiarios'),
        'patologias' => $modelo->manejarAccion('patologias'),
    ]);
}

/**
 * Sube un PDF a uploads/trabajo_social/<subcarpeta>/ y devuelve la ruta
 * relativa (máx. 100 caracteres, límite de direccion_pdf/direccion_carta).
 * El endpoint es multipart/form-data: por eso se leen $_POST y $_FILES.
 */
function trabajoSocialSubirPdf(array $archivo, string $subcarpeta, string $prefijo): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($archivo['tmp_name'])) {
        throw ExcepcionApi::validacion('El archivo adjunto es obligatorio.');
    }
    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
    $mime = mime_content_type($archivo['tmp_name']);
    if ($extension !== 'pdf' || $mime !== 'application/pdf') {
        throw ExcepcionApi::validacion('El archivo adjunto debe ser un PDF válido.');
    }

    $rutaRelativa = 'uploads/trabajo_social/' . $subcarpeta;
    // BASE_PATH ya termina en '/': no añadir otra barra (evita la '//' doble).
    $rutaAbsoluta = BASE_PATH . $rutaRelativa;
    if (!is_dir($rutaAbsoluta) && !mkdir($rutaAbsoluta, 0777, true)) {
        throw ExcepcionApi::errorInterno('No se pudo crear la carpeta de subida.');
    }
    // El usuario del servidor web (p. ej. www-data) debe poder escribir en
    // la carpeta; si la creó otro usuario a mano, avisar con la ruta exacta.
    if (!is_writable($rutaAbsoluta)) {
        throw ExcepcionApi::errorInterno(
            'La carpeta de subida no tiene permisos de escritura: ' . $rutaRelativa
        );
    }

    $nombre = $prefijo . '_' . date('Ymd_His') . '_' . uniqid() . '.pdf';
    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta . '/' . $nombre)) {
        throw ExcepcionApi::errorInterno('No se pudo guardar el archivo adjunto.');
    }
    return $rutaRelativa . '/' . $nombre;
}

/** POST multipart api/trabajo-social/becas/crear */
function apiCrearBeca(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);

    $modelo->__set('id_beneficiario', $_POST['id_beneficiario'] ?? 0);
    $modelo->__set('tipo_banco', $_POST['tipo_banco'] ?? '');
    $modelo->__set('cta_bcv', $_POST['cta_bcv'] ?? '');
    // La subida va después de las validaciones de arriba para no dejar
    // archivos huérfanos cuando el formulario es inválido.
    $modelo->__set('direccion_pdf', trabajoSocialSubirPdf($_FILES['planilla'] ?? [], 'becas', 'planilla'));

    $nuevo = $modelo->manejarAccion('crear_beca');
    Bitacora::registrar('Trabajador Social', 'Registro', 'Registró una beca de trabajo social.');
    Respuesta::exito($nuevo, 201);
}

/** POST multipart api/trabajo-social/exoneraciones/crear */
function apiCrearExoneracion(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);

    $modelo->__set('id_beneficiario', $_POST['id_beneficiario'] ?? 0);
    $modelo->__set('motivo', $_POST['motivo'] ?? '');
    $modelo->__set('otro_motivo', $_POST['otro_motivo'] ?? '');
    $modelo->__set('carnet_discapacidad', $_POST['carnet_discapacidad'] ?? '');
    $modelo->__set('direccion_carta', trabajoSocialSubirPdf($_FILES['carta'] ?? [], 'exoneracion', 'carta'));

    $nuevo = $modelo->manejarAccion('crear_exoneracion');
    Bitacora::registrar('Trabajador Social', 'Registro', 'Registró una exoneración de trabajo social.');
    Respuesta::exito($nuevo, 201);
}

/** GET api/trabajo-social/exoneraciones/pendientes — sin estudio socioeconómico. */
function apiExoneracionesPendientes(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    Respuesta::exito($modelo->manejarAccion('exoneraciones_pendientes'));
}

/** GET api/trabajo-social/stats — tarjetas del hub y de la consulta. */
function apiStatsTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    Respuesta::exito($modelo->manejarAccion('stats'));
}

// ---------- Documentos (tercera puerta PDF) ----------

/** Constancia de Atención (GET trabajo-social/constancia/{tipo}/{id} + ?tramite=&hora=). */
function generarConstanciaTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $parametros = parametrosDocumento();
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    $modelo->__set('tipo', $_GET['tipo'] ?? '');
    $modelo->__set('id_registro', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Trabajador Social', 'Registro',
        'Generó la constancia de atención de ' . trabajoSocialEtiquetaTipo((string) ($_GET['tipo'] ?? ''))
        . ' de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/constancia/procesar.php';
    GenerarConstancia::generar($datos);
}

/** Referencia a otra área (GET trabajo-social/referencia/{tipo}/{id} + ?area= obligatoria). */
function generarReferenciaTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $parametros = parametrosDocumento(true);
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    $modelo->__set('tipo', $_GET['tipo'] ?? '');
    $modelo->__set('id_registro', (int) ($_GET['id'] ?? 0));
    $datos = array_merge($modelo->manejarAccion('datos_documento'), $parametros);
    Bitacora::registrar('Trabajador Social', 'Registro',
        'Generó la referencia al área ' . $datos['area'] . ' de '
        . trabajoSocialEtiquetaTipo((string) ($_GET['tipo'] ?? '')) . ' de ' . $datos['beneficiario'] . '.');
    require_once BASE_PATH . 'docs/PDF/referencia/procesar.php';
    GenerarReferencia::generar($datos);
}

/** POST api/trabajo-social/fames/crear */
function apiCrearFames(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);

    $modelo->__set('id_beneficiario', $_POST['id_beneficiario'] ?? 0);
    $modelo->__set('id_patologia', $_POST['id_patologia'] ?? 0);
    $modelo->__set('tipo_ayuda', $_POST['tipo_ayuda'] ?? '');
    $modelo->__set('otro_tipo', $_POST['otro_tipo'] ?? '');

    $nuevo = $modelo->manejarAccion('crear_fames');
    Bitacora::registrar('Trabajador Social', 'Registro', 'Registró un diagnóstico FAMES de trabajo social.');
    Respuesta::exito($nuevo, 201);
}

/** POST api/trabajo-social/embarazadas/crear */
function apiCrearEmbarazada(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);

    $modelo->__set('id_beneficiario', $_POST['id_beneficiario'] ?? 0);
    $modelo->__set('id_patologia', $_POST['id_patologia'] ?? 0);
    $modelo->__set('semanas_gest', $_POST['semanas_gest'] ?? '');
    $modelo->__set('codigo_patria', $_POST['codigo_patria'] ?? '');
    $modelo->__set('serial_patria', $_POST['serial_patria'] ?? '');

    $nuevo = $modelo->manejarAccion('crear_embarazada');
    Bitacora::registrar('Trabajador Social', 'Registro', 'Registró una gestión de embarazada de trabajo social.');
    Respuesta::exito($nuevo, 201);
}

/**
 * Edición restringida por sub-registro: SOLO los campos editables de
 * cada tipo. Beneficiario, empleado que atendió y archivos jamás se
 * aceptan desde el cliente (integridad de auditoría).
 */
function trabajoSocialAsignarEntradaEdicion(TrabajoSocialModel $modelo, array $entrada): void
{
    $campos = match ($entrada['tipo'] ?? '') {
        'becas' => ['tipo_banco', 'cta_bcv'],
        'exoneraciones' => ['motivo', 'otro_motivo', 'carnet_discapacidad'],
        'fames' => ['id_patologia', 'tipo_ayuda', 'otro_tipo'],
        'embarazadas' => ['id_patologia', 'semanas_gest', 'codigo_patria', 'serial_patria', 'estado'],
        default => throw ExcepcionApi::validacion('El tipo de registro no es válido.'),
    };
    foreach ($campos as $campo) {
        // Los opcionales ('' o null) también viajan: el modelo normaliza.
        if (array_key_exists($campo, $entrada)) {
            $modelo->__set($campo, $entrada[$campo]);
        }
    }
}

/** GET api/trabajo-social/listar?tipo=becas|exoneraciones|fames|embarazadas */
function apiListarTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'leer');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    $modelo->__set('tipo', $_GET['tipo'] ?? '');
    Respuesta::exito($modelo->manejarAccion('listar'));
}

/** POST api/trabajo-social/actualizar — JSON {tipo, id, ...campos} */
function apiActualizarTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'editar');
    $entrada = entradaTrabajoSocialJson();
    $tipo = (string) ($entrada['tipo'] ?? '');

    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    $modelo->__set('tipo', $tipo);
    $modelo->__set('id_registro', $entrada['id'] ?? 0);
    trabajoSocialAsignarEntradaEdicion($modelo, $entrada);

    $actualizado = $modelo->manejarAccion('actualizar');
    Bitacora::registrar(
        'Trabajador Social',
        'Actualización',
        'Actualizó ' . trabajoSocialEtiquetaTipo($tipo) . ' #'
            . $actualizado['id'] . ' de ' . ($actualizado['beneficiario'] ?: 'un beneficiario') . '.'
    );
    Respuesta::exito($actualizado);
}

/** POST api/trabajo-social/eliminar — JSON {tipo, id} */
function apiEliminarTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'eliminar');
    $entrada = entradaTrabajoSocialJson();
    $tipo = (string) ($entrada['tipo'] ?? '');

    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);
    $modelo->__set('tipo', $tipo);
    $modelo->__set('id_registro', $entrada['id'] ?? 0);

    $eliminado = $modelo->manejarAccion('eliminar');
    Bitacora::registrar(
        'Trabajador Social',
        'Eliminación',
        'Eliminó ' . trabajoSocialEtiquetaTipo($tipo) . ' #'
            . $eliminado['id'] . ' de ' . ($eliminado['beneficiario'] ?: 'un beneficiario') . '.'
    );
    Respuesta::exito($eliminado);
}

/**
 * POST multipart api/trabajo-social/estudio/generar
 * Genera el PDF del estudio socioeconómico (FPDF) y lo vincula a la
 * exoneración pendiente. Los ~100 campos del formulario NO se persisten:
 * el PDF es el registro (decisión del usuario). La foto es opcional
 * (jpg/jpeg/png/gif) y se incrusta en el documento.
 */
function apiGenerarEstudioTrabajoSocial(): void
{
    Autorizacion::verificar('trabajador social', 'crear');
    $modelo = new TrabajoSocialModel();
    contextoTrabajoSocial($modelo);

    // id_exoneracion viaja como campo POST (FormData).
    $modelo->__set('id_registro', $_POST['id_exoneracion'] ?? 0);
    $registro = $modelo->manejarAccion('validar_estudio');

    // Foto opcional: se valida ANTES de generar nada para no dejar huérfanos.
    $imagen = $_FILES['imagen'] ?? null;
    if ($imagen && ($imagen['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (($imagen['error'] ?? -1) !== UPLOAD_ERR_OK) {
            throw ExcepcionApi::validacion('La foto no se pudo recibir correctamente.');
        }
        $extension = strtolower(pathinfo($imagen['name'] ?? '', PATHINFO_EXTENSION));
        $mime = mime_content_type($imagen['tmp_name']);
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)
            || !str_starts_with((string) $mime, 'image/')) {
            throw ExcepcionApi::validacion('La foto debe ser una imagen JPG, JPEG, PNG o GIF válida.');
        }
    }

    // Carpeta de salida (mismo criterio de permisos que trabajoSocialSubirPdf).
    $rutaRelativa = 'uploads/trabajo_social/exoneracion/estudiose';
    $carpeta = BASE_PATH . $rutaRelativa;
    if (!is_dir($carpeta) && !mkdir($carpeta, 0777, true)) {
        throw ExcepcionApi::errorInterno('No se pudo crear la carpeta de estudios.');
    }
    if (!is_writable($carpeta)) {
        throw ExcepcionApi::errorInterno(
            'La carpeta de estudios no tiene permisos de escritura: ' . $rutaRelativa
        );
    }

    $nombre = uniqid() . '_estudioSE.pdf';
    $rutaAbsoluta = $carpeta . '/' . $nombre;
    $rutaFinal = $rutaRelativa . '/' . $nombre;

    // procesar.php (adaptado a este repo) dibuja el PDF leyendo $_POST/$_FILES.
    require_once BASE_PATH . 'docs/PDF/EstudioSE/procesar.php';

    try {
        if (!GenerarPDF::crearPDF($rutaAbsoluta)) {
            throw ExcepcionApi::errorInterno('No se pudo generar el PDF del estudio.');
        }
        $modelo->__set('direccion_estudiose', $rutaFinal);
        $modelo->manejarAccion('vincular_estudio');
    } catch (Throwable $e) {
        // Si falló el vínculo (o la generación), el PDF recién escrito
        // quedaría huérfano en disco: se retira y se relanza el error.
        if (is_file($rutaAbsoluta)) {
            @unlink($rutaAbsoluta);
        }
        throw $e;
    }

    Bitacora::registrar(
        'Trabajador Social',
        'Registro',
        'Generó el estudio socioeconómico de ' . ($registro['beneficiario'] ?: 'un beneficiario') . '.'
    );
    Respuesta::exito(['ruta' => $rutaFinal], 201);
}

