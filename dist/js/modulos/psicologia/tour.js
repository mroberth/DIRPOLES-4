// dist/js/modulos/psicologia/tour.js
// Tour guiado con Driver.js. Los pasos se arman según el Tipo de formulario
// seleccionado: Diagnóstico, Retiro temporal y Cambio de carrera tienen
// campos distintos, así que el tour solo describe los visibles.
window.PsicologiaTour = (function () {
    'use strict';

    // Pasos comunes a los tres tipos.
    const PASOS_COMUNES = [
        { element: '#id_beneficiario', title: 'Beneficiario', description: 'Selecciona el beneficiario activo al que corresponde la consulta.' },
        { element: '#tipo_consulta', title: 'Tipo de formulario', description: 'Diagnóstico, Retiro temporal o Cambio de carrera. El tipo determina qué campos son obligatorios.' },
    ];

    const PASOS_DIAGNOSTICO = [
        { element: '#id_patologia', title: 'Patología', description: 'Selecciona la patología psicológica o general asociada al diagnóstico.' },
        { element: '#diagnostico', title: 'Diagnóstico', description: 'Describe el diagnóstico (obligatorio para este tipo).' },
        { element: '#tratamiento_gen', title: 'Tratamiento general', description: 'Indica el tratamiento general recomendado (opcional).' },
    ];

    const PASOS_RETIRO = [
        { element: '#motivo_retiro', title: 'Motivo del retiro', description: 'Explica por qué el beneficiario se retira temporalmente (obligatorio).' },
        { element: '#duracion_retiro', title: 'Duración', description: 'Indica la duración estimada del retiro (obligatorio).' },
    ];

    const PASOS_CAMBIO = [
        { element: '#motivo_cambio', title: 'Motivo del cambio', description: 'Motivo del cambio de carrera (obligatorio).' },
    ];

    // Común a los tres tipos, siempre visible.
    const PASOS_FINALES = [
        { element: '#observaciones', title: 'Observaciones', description: 'Añade información complementaria si es necesario (aplica a los tres tipos).' },
    ];

    function pasosPorTipo(tipo) {
        if (tipo === 'Retiro temporal') {
            return [...PASOS_COMUNES, ...PASOS_RETIRO, ...PASOS_FINALES];
        }
        if (tipo === 'Cambio de carrera') {
            return [...PASOS_COMUNES, ...PASOS_CAMBIO, ...PASOS_FINALES];
        }
        return [...PASOS_COMUNES, ...PASOS_DIAGNOSTICO, ...PASOS_FINALES];
    }

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

        const tipoSelect = document.getElementById('tipo_consulta');
        const tipo = tipoSelect ? tipoSelect.value : 'Diagnóstico';

        factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: pasosPorTipo(tipo).map((paso) => ({
                element: objetivo(paso.element),
                popover: { title: paso.title, description: paso.description, side: 'bottom', align: 'start' },
            })),
        }).drive();
    }

    return { iniciar };
})();
