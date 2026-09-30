<?php
/**
 * app/Config/modulos_sidebar.php
 * ---------------------------------------------------------------
 * Configuración del menú lateral (sidebar).
 *
 * El sidebar se genera dinámicamente en app/Views/template/sidebar.php:
 *  - La CLAVE numérica es el ID del módulo en la BD (tabla `modulo`).
 *    Solo se muestra si el rol del usuario tiene el permiso 'Leer'
 *    sobre ese módulo (verificado contra la sesión `modulosPermitidos`).
 *  - La clave puede ser un string (ej: 'group_ayuda') para grupos
 *    siempre visibles que no dependen de un módulo de la BD.
 *  - 'solo_admin' => true oculta el subitem salvo para Administrador/Superusuario.
 *
 * AGREGA TUS MÓDULOS AQUÍ al crearlos. Ejemplo (descomenta y adapta):
 *
 *  1 => [
 *      'key'    => 'productos',
 *      'icon'   => 'fa-box',            // clase de Font Awesome
 *      'titulo' => 'Gestionar Productos',
 *      'subitems' => [
 *          ['url' => 'crear_producto',        'texto' => 'Crear',     'permiso' => 2],
 *          ['url' => 'consultar_productos',   'texto' => 'Consultar', 'permiso' => 2],
 *      ],
 *  ],
 *
 * NOTA: el 'url' de cada subitem debe existir como ruta en app/routes/.
 */
return [
    1 => [ // id_modulo real = 1 (Empleados)
        'key'    => 'empleados',
        'icon'   => 'fa-users',
        'titulo' => 'Gestionar Empleados',
        'subitems' => [
            ['url' => 'empleados/crear',     'texto' => 'Crear',     'permiso' => 2],
            ['url' => 'empleados/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    2 => [ // id_modulo real = 2 (Beneficiarios)
        'key'    => 'beneficiarios',
        'icon'   => 'fa-user-graduate',
        'titulo' => 'Gestionar Beneficiarios',
        'subitems' => [
            ['url' => 'beneficiarios/crear',     'texto' => 'Crear',     'permiso' => 2],
            ['url' => 'beneficiarios/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    3 => [ // id_modulo real = 3 (Citas)
        'key'    => 'citas',
        'icon'   => 'fa-calendar-check',
        'titulo' => 'Gestionar Citas',
        'subitems' => [
            ['url' => 'citas/crear',     'texto' => 'Crear',     'permiso' => 1],
            ['url' => 'citas/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    9 => [ // id_modulo real = 9 (Inventario Médico)
        'key'    => 'inventario',
        'icon'   => 'fa-pills',
        'titulo' => 'Inventario Médico',
        'subitems' => [
            ['url' => 'inventario/crear',     'texto' => 'Crear',     'permiso' => 2],
            ['url' => 'inventario/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    10 => [ // id_modulo real = 10 (Referencias)
        'key'    => 'referencias',
        'icon'   => 'fa-share-nodes',
        'titulo' => 'Gestionar Referencias',
        'subitems' => [
            ['url' => 'referencias/crear',     'texto' => 'Crear',     'permiso' => 1],
            ['url' => 'referencias/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    11 => [ // id_modulo real = 11 (Jornadas Médicas)
        'key'    => 'jornadas',
        'icon'   => 'fa-briefcase-medical',
        'titulo' => 'Gestionar Jornadas',
        'subitems' => [
            ['url' => 'jornadas/crear',     'texto' => 'Crear',     'permiso' => 1],
            ['url' => 'jornadas/consultar', 'texto' => 'Consultar', 'permiso' => 2,
             'activo' => ['jornadas/detalle']],
        ],
    ],
    12 => [ // id_modulo real = 12 (Mobiliario)
        'key'    => 'mobiliario',
        'icon'   => 'fa-chair',
        'titulo' => 'Gestionar Mobiliario',
        'subitems' => [
            ['url' => 'mobiliario/crear',     'texto' => 'Crear',     'permiso' => 1],
            ['url' => 'mobiliario/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    13 => [ // id_modulo real = 13 (Transporte)
        'key'    => 'transporte',
        'icon'   => 'fa-truck',
        'titulo' => 'Gestionar Transporte',
        'subitems' => [
            ['url' => 'transporte/crear',     'texto' => 'Crear',     'permiso' => 1],
            ['url' => 'transporte/consultar', 'texto' => 'Consultar', 'permiso' => 2],
        ],
    ],
    4 => [ // id_modulo real = 4 (Psicología)
        'key'    => 'psicologia',
        'icon'   => 'fa-brain',
        'titulo' => 'Gestionar Diagnósticos',
        'subitems' => [
            // 'activo' => rutas extra que deben mantener este item marcado y su
            // dropdown abierto (NO son entradas visibles, solo coincidencias).
            ['url' => 'psicologia/crear',     'texto' => 'Psicología',     'permiso' => 2, 'activo' => ['psicologia/consultar']],
            // Medicina valida contra su propio módulo (id 5), como Bitácora/Permisos:
            // un Médico no necesita permiso en Psicología (módulo 4) para verlo.
            ['url' => 'medicina/crear',       'texto' => 'Medicina',       'permiso' => 2, 'id_modulo' => 5, 'activo' => ['medicina/consultar']],
            // Orientación valida contra su propio módulo (id 6), como Medicina:
            // un Orientador no necesita permiso en Psicología (módulo 4).
            ['url' => 'orientacion/crear',    'texto' => 'Orientación',    'permiso' => 2, 'id_modulo' => 6, 'activo' => ['orientacion/consultar']],
            // Discapacidad valida contra su propio módulo (id 8): el tipo de
            // empleado 'Discapacidad' (5) no tiene permiso en Psicología (4).
            ['url' => 'discapacidad/crear',    'texto' => 'Discapacidad',    'permiso' => 2, 'id_modulo' => 8, 'activo' => ['discapacidad/consultar']],
            // Trabajo Social valida contra su propio módulo (id 7): el tipo
            // de empleado 'Trabajador Social' (3) no tiene permiso en
            // Psicología (4).
            ['url' => 'trabajo-social/crear', 'texto' => 'Trabajo Social', 'permiso' => 2, 'id_modulo' => 7, 'activo' => ['trabajo-social/consultar']],
        ],
    ],
    15 => [ // id_modulo real = 15 (Reportes)
        'key'    => 'reportes',
        'icon'   => 'fa-chart-pie',
        'titulo' => 'Reportes Estadísticos',
        'subitems' => [
            ['url' => 'reportes/general',        'texto' => 'General',        'permiso' => 2],
            ['url' => 'reportes/medicina',       'texto' => 'Medicina',       'permiso' => 2],
            ['url' => 'reportes/psicologia',     'texto' => 'Psicología',     'permiso' => 2],
            ['url' => 'reportes/orientacion',    'texto' => 'Orientación',    'permiso' => 2],
            ['url' => 'reportes/trabajo-social', 'texto' => 'Trabajo Social', 'permiso' => 2],
            ['url' => 'reportes/discapacidad',   'texto' => 'Discapacidad',   'permiso' => 2],
            ['url' => 'reportes/referencias',    'texto' => 'Referencias',    'permiso' => 2],
            ['url' => 'reportes/jornadas',       'texto' => 'Jornadas',       'permiso' => 2],
            ['url' => 'reportes/mobiliario',     'texto' => 'Mobiliario',     'permiso' => 2],
            ['url' => 'reportes/transporte',     'texto' => 'Transporte',     'permiso' => 2],
        ],
    ],
    18 => [ // id_modulo real = 18 (Horarios)
        'key'    => 'horarios',
        'icon'   => 'fa-clock',
        'titulo' => 'Horarios de Psicología',
        'subitems' => [
            ['url' => 'horarios/crear',     'texto' => 'Crear',     'permiso' => 1, 'solo_admin' => true],
            ['url' => 'horarios/consultar', 'texto' => 'Consultar', 'permiso' => 2, 'solo_admin' => true],
        ],
    ],
    14 => [ // id_modulo real = 14 (Configuración)
        'key'    => 'configuracion',
        'icon'   => 'fa-gear',
        'titulo' => 'Configuraciones',
        'subitems' => [
            ['url' => 'configuracion/crear',     'texto' => 'Crear',     'permiso' => 2],
            ['url' => 'configuracion/consultar', 'texto' => 'Consultar', 'permiso' => 2],
            // Bitácora valida contra el módulo 16.
            ['url' => 'bitacora/consultar', 'texto' => 'Bitácora', 'permiso' => 2, 'id_modulo' => 16],
            // Permisos valida contra el módulo 17 (por eso el id_modulo explícito).
            ['url' => 'permisos/gestionar', 'texto' => 'Permisos Empleados', 'permiso' => 2, 'id_modulo' => 17],
            ['url' => 'respaldo', 'texto' => 'Respaldo BD', 'permiso' => 2, 'solo_admin' => true],
        ],
    ],
];
