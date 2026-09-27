// dist/js/modulos/orientacion/tour.js
// Tour guiado con Driver.js del formulario de orientación.
window.OrientacionTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#id_beneficiario', title: 'Beneficiario', description: 'Selecciona el beneficiario activo al que corresponde la orientación.' },
        { element: '#motivo_orientacion', title: 'Motivo', description: 'Describe el motivo principal de la orientación (obligatorio, máximo 5000 caracteres).' },
        { element: '#descripcion_orientacion', title: 'Descripción', description: 'Describe el desarrollo de la sesión de orientación (obligatorio, máximo 5000 caracteres).' },
        { element: '#indicaciones_orientacion', title: 'Indicaciones', description: 'Indica las recomendaciones para el beneficiario (obligatorio, máximo 5000 caracteres).' },
        { element: '#obs_adic_orientacion', title: 'Observaciones', description: 'Observaciones adicionales, pronóstico o seguimiento recomendado (obligatorio, máximo 5000 caracteres).' },
        { element: 'button[type="submit"]', title: 'Guardar', description: 'Revisa los campos marcados en rojo y registra la orientación. Las estadísticas se actualizan solas.' },
    ];

    /**
     * Devuelve el elemento visible a resaltar. Si es un <select> con Select2,
     * el <select> original está oculto (1x1 px), así que devolvemos su
     * contenedor .select2-container para que el foco sea el select completo.
     */
    function objetivo(selector) {
        const elemento = document.querySelector(selector);
        if (!elemento) return selector;
        if (elemento.tagName === 'SELECT' && elemento.classList.contains('select2')
            && elemento.nextElementSibling && elemento.nextElementSibling.classList.contains('select2-container')) {
            return elemento.nextElementSibling;
        }
        return elemento;
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
            steps: PASOS.map((paso) => ({
                element: objetivo(paso.element),
                popover: { title: paso.title, description: paso.description, side: 'bottom', align: 'start' },
            })),
        }).drive();
    }

    return { iniciar };
}());
