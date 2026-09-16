// dist/js/modulos/horario/tour.js
window.HorarioTour = (function () {
    'use strict';

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
        const factory = window.driver && window.driver.js && (window.driver.js.driver || window.driver.js);
        if (typeof factory !== 'function') return;
        factory({
            showProgress: true,
            nextBtnText: 'Siguiente', prevBtnText: 'Anterior', doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: [
                { element: objetivo('#id_empleado'), popover: { title: 'Psicólogo', description: 'Selecciona el psicólogo al que pertenece el horario.' } },
                { element: objetivo('#dia_semana'), popover: { title: 'Día', description: 'Registra un único horario por día de lunes a sábado.' } },
                { element: '#hora_inicio', popover: { title: 'Hora inicial', description: 'El rango permitido comienza a las 07:00.' } },
                { element: '#hora_fin', popover: { title: 'Hora final', description: 'Debe ser posterior a la hora inicial y no superar las 17:00.' } },
            ],
        }).drive();
    }
    return { iniciar };
})();
