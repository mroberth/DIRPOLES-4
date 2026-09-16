// dist/js/modulos/cita/tour.js
window.CitaTour = (function () {
    'use strict';

    const pasos = [
        { element: '#id_beneficiario', title: 'Beneficiario', description: 'Selecciona el beneficiario que será atendido.' },
        { element: '#id_empleado', title: 'Psicólogo', description: 'El administrador puede elegir el psicólogo. Cada psicólogo trabaja únicamente con sus propias citas.' },
        { element: '#fecha', title: 'Fecha', description: 'Selecciona una fecha actual o futura dentro del horario del psicólogo.' },
        { element: '#hora', title: 'Hora', description: 'Las citas duran una hora y deben iniciar en punto o a media hora.' },
        { element: '#horario-cita', title: 'Horario disponible', description: 'Aquí se muestra el horario registrado del psicólogo seleccionado.' },
    ];

    function objetivo(selector) {
        const elemento = document.querySelector(selector);
        if (!elemento) return selector;
        if (elemento.tagName === 'SELECT' && elemento.classList.contains('select2')
            && elemento.nextElementSibling?.classList.contains('select2-container')) {
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
            steps: pasos.map((paso) => ({
                element: objetivo(paso.element),
                popover: { title: paso.title, description: paso.description, side: 'bottom', align: 'start' },
            })),
        }).drive();
    }

    return { iniciar };
})();