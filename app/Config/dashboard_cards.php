<?php
/**
 * app/Config/dashboard_cards.php
 * ---------------------------------------------------------------
 * Define las CARDS de módulos del panel de Inicio.
 *
 * La clave es el `id_modulo` real de la tabla `modulo` (BD de seguridad).
 * El dashboard solo pinta las cards cuyo id_modulo esté en
 * `$_SESSION['modulosPermitidos']` (permiso Leer). Así el mismo arreglo
 * sirve para Administrador y para cualquier rol: cada quien ve lo suyo.
 *
 * Campos:
 *   icon       clase de Font Awesome
 *   color      primary | secondary | success | info | warning | danger  (BS/SB Admin)
 *   titulo     texto de la card
 *   url        ruta del sistema a la que navega la card (relativa a BASE_URL)
 *   stat       (opcional) clave que devuelve api/dashboard/stats para el contador
 *   disponible false => la card se muestra como "Próximamente" (módulo no construido)
 *
 * Cuando construyas un módulo, además de crear su ruta/controlador/modelo,
 * pon aquí `'disponible' => true` y ajusta `url`.
 */
return [
    1  => ['icon' => 'fa-users',              'color' => 'primary',   'titulo' => 'Empleados',        'url' => 'consultar_empleados',        'disponible' => false, 'stat' => 'admin_empleados_total'],
    2  => ['icon' => 'fa-person',             'color' => 'success',   'titulo' => 'Beneficiarios',    'url' => 'beneficiarios/consultar',    'disponible' => true,  'stat' => 'admin_beneficiarios_total'],
    3  => ['icon' => 'fa-calendar-check',     'color' => 'info',      'titulo' => 'Citas',            'url' => 'consultar_citas',            'disponible' => false, 'stat' => 'admin_citas_total'],
    4  => ['icon' => 'fa-brain',              'color' => 'secondary', 'titulo' => 'Psicología',       'url' => 'diagnostico_psicologia',     'disponible' => false, 'stat' => 'admin_psicologia_total'],
    5  => ['icon' => 'fa-stethoscope',        'color' => 'danger',    'titulo' => 'Medicina',         'url' => 'diagnostico_medicina',       'disponible' => false, 'stat' => 'admin_medicina_total'],
    6  => ['icon' => 'fa-comments',           'color' => 'warning',   'titulo' => 'Orientación',      'url' => 'diagnostico_orientacion',    'disponible' => false, 'stat' => 'admin_orientacion_total'],
    7  => ['icon' => 'fa-hand-holding-heart', 'color' => 'success',   'titulo' => 'Trabajo Social',   'url' => 'diagnostico_trabajo_social', 'disponible' => false, 'stat' => 'admin_ts_total'],
    8  => ['icon' => 'fa-wheelchair',         'color' => 'primary',   'titulo' => 'Discapacidad',     'url' => 'diagnostico_discapacidad',   'disponible' => false, 'stat' => 'admin_discapacidad_total'],
    9  => ['icon' => 'fa-pills',              'color' => 'info',      'titulo' => 'Inventario Médico','url' => 'consultar_inventario',       'disponible' => false, 'stat' => 'admin_insumos_total'],
    10 => ['icon' => 'fa-share-nodes',        'color' => 'secondary', 'titulo' => 'Referencias',      'url' => 'consultar_referencias',      'disponible' => false, 'stat' => 'admin_referidos_total'],
    11 => ['icon' => 'fa-briefcase-medical',  'color' => 'success',   'titulo' => 'Jornadas',         'url' => 'consultar_jornadas',         'disponible' => false, 'stat' => 'admin_jornadas_total'],
    12 => ['icon' => 'fa-chair',              'color' => 'warning',   'titulo' => 'Mobiliario',       'url' => 'consultar_inventario_mob',   'disponible' => false, 'stat' => 'admin_mobiliario_total'],
    13 => ['icon' => 'fa-truck',              'color' => 'dark',      'titulo' => 'Transporte',       'url' => 'transporte_consulta',        'disponible' => false, 'stat' => 'admin_vehiculos_total'],
    14 => ['icon' => 'fa-gear',               'color' => 'secondary', 'titulo' => 'Configuración',    'url' => 'configuracion/crear',        'disponible' => true],
    16 => ['icon' => 'fa-clipboard-list',     'color' => 'dark',      'titulo' => 'Bitácora',         'url' => 'bitacora/consultar',         'disponible' => true,  'stat' => 'admin_bitacora_total'],
    17 => ['icon' => 'fa-user-shield',        'color' => 'danger',    'titulo' => 'Permisos',         'url' => 'permisos/gestionar',         'disponible' => true,  'stat' => 'admin_permisos_total'],
    18 => ['icon' => 'fa-clock',              'color' => 'info',      'titulo' => 'Horarios',         'url' => 'horarios',                   'disponible' => false, 'stat' => 'admin_horarios_total'],
];
