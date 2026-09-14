<?php
/**
 * app/Config/configuracion_catalogos.php
 * ---------------------------------------------------------------
 * Catálogos gestionados desde el módulo Configuración.
 *
 * Claves:
 *   titulo, icono, color, badge  → presentación
 *   conexion                     → business | security
 *   tabla, pk                    → tabla real y su clave primaria
 *   con_estatus                  → true si la tabla tiene columna `estatus`
 *   unico                        → columnas de unicidad COMPUESTA
 *   fk                           → claves foráneas a validar
 *   campos[]                     → name, label, tipo, required, min, max,
 *                                  regex, regex_flags, prefijo, opciones(_de)
 *
 * NOTA: los catálogos NO se eliminan (integridad del sistema); se editan y,
 * si tienen `estatus`, se activan/desactivan.
 */
return [
    'patologia' => [
        'titulo' => 'Patología', 'icono' => 'fa-heartbeat', 'color' => 'danger', 'badge' => 'Salud',
        'conexion' => 'business', 'tabla' => 'patologia', 'pk' => 'id_patologia', 'con_estatus' => false,
        'unico' => ['nombre_patologia', 'tipo_patologia'],
        'campos' => [
            ['name' => 'nombre_patologia', 'label' => 'La patología', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 100, 'placeholder' => 'Ej: Gripe, Ansiedad...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
            ['name' => 'tipo_patologia', 'label' => 'El tipo de patología', 'tipo' => 'select', 'required' => true, 'opciones' => ['Medica' => 'Médica', 'Psicológica' => 'Psicológica', 'General' => 'General']],
        ],
    ],

    'pnf' => [
        'titulo' => 'PNF', 'icono' => 'fa-graduation-cap', 'color' => 'primary', 'badge' => 'Académico',
        'conexion' => 'business', 'tabla' => 'pnf', 'pk' => 'id_pnf', 'con_estatus' => true,
        'unico' => ['nombre_pnf'],
        'campos' => [
            ['name' => 'nombre_pnf', 'label' => 'El PNF', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 100, 'placeholder' => 'Ej: Informática, Administración...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$', 'regex_flags' => 'u', 'prefijo' => 'PNF '],
        ],
    ],

    'servicio' => [
        'titulo' => 'Servicio', 'icono' => 'fa-briefcase', 'color' => 'info', 'badge' => 'Servicios',
        'conexion' => 'business', 'tabla' => 'servicio', 'pk' => 'id_servicios', 'con_estatus' => true,
        'unico' => ['nombre_serv'],
        'campos' => [
            ['name' => 'nombre_serv', 'label' => 'El servicio', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 100, 'placeholder' => 'Ej: Psicología, Medicina...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$', 'regex_flags' => 'u'],
        ],
    ],

    'tipo_empleado' => [
        'titulo' => 'Tipo de Empleado', 'icono' => 'fa-user-tag', 'color' => 'success', 'badge' => 'Personal',
        'conexion' => 'security', 'tabla' => 'tipo_empleado', 'pk' => 'id_tipo_emp', 'con_estatus' => true,
        'unico' => ['tipo'],
        'fk' => ['id_servicios' => ['tabla' => 'servicio', 'col' => 'id_servicios', 'conexion' => 'business']],
        'campos' => [
            ['name' => 'tipo', 'label' => 'El tipo de empleado', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 30, 'placeholder' => 'Ej: Psicólogo, Enfermero...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
            ['name' => 'id_servicios', 'label' => 'El servicio', 'tipo' => 'select', 'required' => true, 'opciones_de' => 'servicios'],
        ],
    ],

    'tipo_mobiliario' => [
        'titulo' => 'Tipo de Mobiliario', 'icono' => 'fa-chair', 'color' => 'warning', 'badge' => 'Inventario',
        'conexion' => 'business', 'tabla' => 'tipo_mobiliario', 'pk' => 'id_tipo_mobiliario', 'con_estatus' => true,
        'unico' => ['nombre'],
        'campos' => [
            ['name' => 'nombre', 'label' => 'El tipo de mobiliario', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 50, 'placeholder' => 'Ej: Silla, Mesa...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
            ['name' => 'descripcion', 'label' => 'La descripción del mobiliario', 'tipo' => 'textarea', 'required' => true, 'min' => 3, 'max' => 50, 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
        ],
    ],

    'tipo_equipo' => [
        'titulo' => 'Tipo de Equipo', 'icono' => 'fa-desktop', 'color' => 'secondary', 'badge' => 'Inventario',
        'conexion' => 'business', 'tabla' => 'tipo_equipo', 'pk' => 'id_tipo_equipo', 'con_estatus' => true,
        'unico' => ['nombre'],
        'campos' => [
            ['name' => 'nombre', 'label' => 'El tipo de equipo', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 50, 'placeholder' => 'Ej: Laptop, Impresora...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
            ['name' => 'descripcion', 'label' => 'La descripción del equipo', 'tipo' => 'textarea', 'required' => true, 'min' => 3, 'max' => 50, 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
        ],
    ],

    'presentacion_insumo' => [
        'titulo' => 'Presentación de Insumo', 'icono' => 'fa-pills', 'color' => 'dark', 'badge' => 'Inventario',
        'conexion' => 'business', 'tabla' => 'presentacion_insumo', 'pk' => 'id_presentacion', 'con_estatus' => false,
        'unico' => ['nombre_presentacion'],
        'campos' => [
            ['name' => 'nombre_presentacion', 'label' => 'La presentación', 'tipo' => 'text', 'required' => true, 'min' => 3, 'max' => 100, 'placeholder' => 'Ej: Tableta, Jarabe...', 'regex' => '^[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]*$', 'regex_flags' => 'u'],
        ],
    ],
];
