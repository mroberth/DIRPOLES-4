// dist/js/modulos/bitacora/tour.js
// Tour guiado (Driver.js) de la pantalla de Bitácora.
window.BitacoraTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#f-modulo',   title: 'Módulo',   description: 'Filtra los movimientos por módulo.' },
        { element: '#f-accion',   title: 'Acción',   description: 'Filtra por tipo de acción (Registro, Edición...).' },
        { element: '#f-empleado', title: 'Empleado', description: 'Filtra por el empleado que realizó la acción.' },
        { element: '#f-buscar',   title: 'Buscar',   description: 'Búsqueda libre en la descripción, módulo o acción.' },
        { element: '#f-desde',    title: 'Desde',    description: 'Fecha inicial del rango.' },
        { element: '#f-hasta',    title: 'Hasta',    description: 'Fecha final del rango.' },
        { element: '#btn-filtrar', title: 'Filtrar', description: 'Aplica los filtros seleccionados.' },
        { element: '#tabla_bitacora', title: 'Bitácora', description: 'Resultados. Puedes exportar a Excel o PDF.' },
    ];

    function objetivo(selector) {
        const el = document.querySelector(selector);
        if (!el) return selector;
        if (el.tagName === 'SELECT' && el.classList.contains('select2')
            && el.nextElementSibling && el.nextElementSibling.classList.contains('select2-container')) {
            return el.nextElementSibling;
        }
        return el;
    }

    function iniciar() {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);
        if (typeof factory !== 'function') {
            console.warn('Driver.js no está disponible.');
            return;
        }

        factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: PASOS.map((p) => ({
                element: objetivo(p.element),
                popover: { title: p.title, description: p.description, side: 'bottom', align: 'start' },
            })),
        }).drive();
    }

    return { iniciar };
})();
