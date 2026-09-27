// dist/js/modulos/discapacidad/tour.js
// Tour guiado con Driver.js del formulario de discapacidad.
window.DiscapacidadTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#id_beneficiario', title: 'Beneficiario', description: 'Selecciona el beneficiario activo al que corresponde el diagnóstico de discapacidad.' },
        { element: '#tipo_discapacidad', title: 'Tipo de discapacidad', description: 'Selecciona el tipo principal: Física, Sensorial, Intelectual, Múltiple u Otro (obligatorio). Si eliges "Otro", puedes detallarlo en "Discapacidad específica".' },
        { element: '#diagnostico', title: 'Diagnóstico', description: 'Diagnóstico clínico formal (obligatorio, máximo 255 caracteres).' },
        { element: '#grado', title: 'Grado', description: 'Nivel de afectación: Leve, Moderado o Grave (obligatorio). Solo los casos "Grave" cuentan en la tarjeta de discapacidades graves.' },
        { element: '#habilidades_funcionales', title: 'Habilidades funcionales', description: 'Capacidades del beneficiario para las actividades diarias (obligatorio, máximo 255 caracteres).' },
        { element: '#observaciones', title: 'Observaciones', description: 'Notas y detalles adicionales sobre la discapacidad (obligatorio).' },
        { element: '#carnet_discapacidad', title: 'Carnet', description: 'Si el beneficiario posee carnet oficial, registra el número: se contará en la tarjeta "Con carnet".' },
        { element: 'button[type="submit"]', title: 'Guardar', description: 'Revisa los campos marcados en rojo y registra el diagnóstico. Las estadísticas se actualizan solas.' },
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
