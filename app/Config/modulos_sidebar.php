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
    4 => [ // id_modulo real = 4 (Psicología)
        'key'    => 'psicologia',
        'icon'   => 'fa-brain',
        'titulo' => 'Gestionar Diagnósticos',
        'subitems' => [
            // 'activo' => rutas extra que deben mantener este item marcado y su
            // dropdown abierto (NO son entradas visibles, solo coincidencias).
            ['url' => 'psicologia/crear',     'texto' => 'Psicología',     'permiso' => 2, 'activo' => ['psicologia/consultar']],
            ['url' => 'medicina/crear',       'texto' => 'Medicina',       'permiso' => 2],
            ['url' => 'orientacion/crear',    'texto' => 'Orientación',    'permiso' => 2],
            ['url' => 'discapacidad/crear',   'texto' => 'Discapacidad',   'permiso' => 2],
            ['url' => 'trabajo-social/crear', 'texto' => 'Trabajo Social', 'permiso' => 2],
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
