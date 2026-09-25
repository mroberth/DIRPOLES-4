// dist/js/modulos/medicina/tour.js
// Tour guiado con Driver.js del formulario de consulta médica.
window.MedicinaTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#id_beneficiario', title: 'Beneficiario', description: 'Selecciona el beneficiario activo al que corresponde la consulta médica.' },
        { element: '#id_patologia', title: 'Patología', description: 'Patología médica o general asociada al diagnóstico. Si no aplica ninguna, elige "Sin patología médica".' },
        { element: '#estatura', title: 'Datos antropométricos', description: 'Registra la estatura en metros (0.50 a 2.50) y el peso en kilogramos (2 a 300), con hasta dos decimales.' },
        { element: '#tipo_sangre', title: 'Tipo de sangre', description: 'Selecciona el tipo de sangre del beneficiario (A+, A-, B+, B-, AB+, AB-, O+ u O-).' },
        { element: '#motivo_visita', title: 'Motivo de visita', description: 'Describe brevemente el motivo de la consulta (obligatorio, máximo 255 caracteres).' },
        { element: '#diagnostico', title: 'Diagnóstico', description: 'Describe el diagnóstico médico (obligatorio, máximo 255 caracteres).' },
        { element: '#tratamiento', title: 'Tratamiento', description: 'Indica el tratamiento recomendado (obligatorio, máximo 255 caracteres).' },
        { element: '#observaciones', title: 'Observaciones', description: 'Información complementaria o pronóstico (opcional).' },
        { element: '#btnAgregarInsumo', title: 'Insumos del inventario', description: 'Opcional: si recetas un medicamento del inventario, agrégalo aquí con su cantidad. Solo aparecen insumos disponibles y no vencidos; el sistema descuenta el stock automáticamente.' },
        { element: 'button[type="submit"]', title: 'Guardar', description: 'Revisa los campos marcados en rojo y registra la consulta. Las estadísticas se actualizan solas.' },
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
