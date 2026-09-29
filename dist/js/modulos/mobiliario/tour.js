// dist/js/modulos/mobiliario/tour.js
// ------------------------------------------------------------------
// Tour guiado con Driver.js (exigencia de la universidad).
// La pantalla crear tiene TRES formularios en pestañas, así que los pasos
// se arman según la pestaña activa. crear.js solo llama a:
//   window.MobiliarioTour.iniciar();
// Si un elemento no existe, `objetivo()` devuelve el selector tal cual y
// Driver.js lo omite con gracia.
// ------------------------------------------------------------------
window.MobiliarioTour = (function () {
    'use strict';

    const INTRO = {
        element: '#mobiliarioTabs',
        title: 'Tres formas de registrar',
        description: 'Mobiliario (piezas con cantidad), Equipo (con serial único) y '
            + 'Ficha técnica (asigna ítems a un empleado). Cada pestaña tiene su '
            + 'propio formulario y su propio botón de guardado.',
    };

    const PASOS_MOBILIARIO = [
        {
            element: '#id_tipo_mobiliario',
            title: 'Tipo de mobiliario',
            description: 'Obligatorio. Se carga desde el catálogo "Tipo de Mobiliario" de Configuración; '
                + 'solo aparecen los tipos activos.',
        },
        {
            element: '#id_servicios',
            title: 'Ubicación',
            description: 'Obligatorio: el servicio donde queda el mobiliario. Al guardarse se registra '
                + 'un movimiento "asignación" en el historial.',
        },
        {
            element: '#cantidad',
            title: 'Cantidad',
            description: 'Obligatoria: entero mayor o igual a 1. Es el total de unidades; la disponibilidad '
                + 'se calcula restando lo que esté asignado en fichas activas.',
        },
        {
            element: '#estado',
            title: 'Estado físico',
            description: 'Obligatorio: Nuevo, Bueno, Regular, Malo o En reparación. El estatus '
                + '(Activo/Inactivo) NO se elige aquí: nace Activo.',
        },
        {
            element: '#fecha_adquisicion',
            title: 'Fecha de adquisición',
            description: 'Opcional y no puede ser futura. Si la dejas vacía, cuenta para "Altas del mes" '
                + 'de otra forma: se registra solo lo que tengas fecha.',
        },
    ];

    const PASOS_EQUIPO = [
        {
            element: '#eq_id_tipo_equipo',
            title: 'Tipo de equipo',
            description: 'Obligatorio. Se carga desde el catálogo "Tipo de Equipo" de Configuración.',
        },
        {
            element: '#eq_serial',
            title: 'Serial único',
            description: 'Obligatorio: de 3 a 100 caracteres (letras, números, puntos, guiones, barras y '
                + 'guiones bajos). Es ÚNICO en todo el sistema: se verifica en vivo mientras escribes.',
        },
        {
            element: '#eq_id_servicios',
            title: 'Ubicación',
            description: 'Obligatoria: el servicio donde queda el equipo. Si lo cambias después desde '
                + 'Consultar, queda registrado como "reubicación".',
        },
        {
            element: '#eq_estado',
            title: 'Estado físico',
            description: 'Obligatorio: Nuevo, Bueno, Regular, Malo o En reparación. El estatus no se elige aquí.',
        },
    ];

    const PASOS_FICHA = [
        {
            element: '#f_nombre_ficha',
            title: 'Nombre de la ficha',
            description: 'Obligatorio: hasta 100 caracteres. Ej: "Ficha Psicología 2026".',
        },
        {
            element: '#f_id_servicio',
            title: 'Servicio',
            description: 'Obligatorio: el área a la que pertenece la ficha.',
        },
        {
            element: '#f_id_empleado_responsable',
            title: 'Empleado responsable',
            description: 'Obligatorio. Cada empleado solo puede tener UNA ficha activa: si ya tiene otra, '
                + 'se marcará en rojo mientras escribes.',
        },
        {
            element: '#btnAgregarFilaMobiliario',
            title: 'Asignar mobiliario',
            description: 'Usa "Agregar fila" para elegir piezas. En cada fila debes indicar la cantidad, '
                + 'que no puede superar la disponible (unidades totales menos lo asignado en otras fichas '
                + 'activas). No repitas el mismo mobiliario en dos filas.',
        },
        {
            element: '#btnAgregarFilaEquipo',
            title: 'Asignar equipos',
            description: 'Cada equipo solo puede estar en UNA ficha activa: si ya está en otra, no aparecerá '
                + 'en la lista de disponibles.',
        },
        {
            element: '#detallesError',
            title: 'Al menos un ítem',
            description: 'La ficha debe guardar con al menos un mobiliario o un equipo asignado.',
        },
    ];

    function pasosActivos() {
        const base = [INTRO];
        const panel = document.querySelector('#mobiliarioTabsContent .tab-pane.active');

        if (panel && panel.id === 'panelEquipo') return base.concat(PASOS_EQUIPO);
        if (panel && panel.id === 'panelFicha') return base.concat(PASOS_FICHA);
        return base.concat(PASOS_MOBILIARIO);
    }

    function iniciar() {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);

        if (typeof factory !== 'function') {
            console.warn('Driver.js no está disponible.');
            return;
        }

        const driverObj = factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: pasosActivos().map((p) => ({
                element: objetivo(p.element),
                popover: {
                    title: p.title,
                    description: p.description,
                    side: 'bottom',
                    align: 'start',
                },
            })),
        });

        driverObj.drive();
    }

    /**
     * Devuelve el elemento visible a resaltar. Un <select class="select2">
     * está oculto (1x1 px), así que se resalta su .select2-container.
     */
    function objetivo(selector) {
        const el = document.querySelector(selector);
        if (!el) return selector;

        if (el.tagName === 'SELECT' && el.classList.contains('select2')
            && el.nextElementSibling && el.nextElementSibling.classList.contains('select2-container')) {
            return el.nextElementSibling;
        }
        return el;
    }

    return { iniciar };
})();
