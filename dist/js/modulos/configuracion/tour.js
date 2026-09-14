// dist/js/modulos/configuracion/tour.js
// Tour guiado con Driver.js para la pantalla de Configuración.
// Recorre, uno por uno, los catálogos presentes en la página.
window.ConfiguracionTour = (function () {
    'use strict';

    function iniciar() {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);

        if (typeof factory !== 'function') {
            console.warn('Driver.js no está disponible.');
            return;
        }

        const cards = document.querySelectorAll('[id^="card-config-"]');
        const steps = Array.from(cards).map((card) => {
            const titleEl = card.querySelector('.card-header h6');
            return {
                element: card,
                popover: {
                    title: titleEl ? titleEl.textContent.trim() : 'Configuración',
                    description: 'Completa el formulario y pulsa Registrar.',
                    side: 'top',
                    align: 'start',
                },
            };
        });

        factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps,
        }).drive();
    }

    return { iniciar };
})();
