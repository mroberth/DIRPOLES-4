// dist/js/modulos/referencias/tour.js
// ------------------------------------------------------------------
// Tour guiado con Driver.js para el formulario de creación de referencias.
// Reusable: crear.js solo tiene que llamar a:
//   window.ReferenciasTour.iniciar();
// Requiere que Driver.js esté cargado (lo hace script.php).
// ------------------------------------------------------------------
window.ReferenciasTour = (function () {
    'use strict';

    function esAdmin() {
        return !!window.REFERENCIAS_ES_ADMIN;
    }

    function pasos() {
        const esOrigenPropio = !esAdmin();
        return [
            {
                element: '#id_beneficiario',
                title: 'Beneficiario',
                description: 'Obligatorio: selecciona al beneficiario que será referido (solo aparecen los activos).',
            },
            {
                element: '#id_servicio_origen',
                title: 'Servicio de origen',
                description: esOrigenPropio
                    ? 'Tu servicio se fija automáticamente: tú eres quien refiere. No puedes cambiarlo.'
                    : 'Como administrador eliges QUIÉN refiere: primero el servicio de origen y luego el empleado de ese servicio.',
            },
            {
                element: '#id_empleado_origen',
                title: 'Empleado de origen',
                description: esOrigenPropio
                    ? 'Ese eres tú. El backend lo vuelve a forzar al enviar, aunque el formulario se manipule.'
                    : 'Selecciona el empleado activo que refiere dentro del servicio de origen elegido.',
            },
            {
                element: '#id_servicio_destino',
                title: 'Servicio destino',
                description: 'Debe ser DISTINTO al de origen: las referencias van entre áreas (ej: Psicología → Trabajo Social).',
            },
            {
                element: '#id_empleado_destino',
                title: 'Empleado destino',
                description: 'Elige al empleado activo que recibirá la referencia: él recibirá una notificación y deberá aceptarla o rechazarla.',
            },
            {
                element: '#motivo',
                title: 'Motivo',
                description: 'Obligatorio: de 2 a 255 caracteres. Resume por qué se refiere al beneficiario.',
            },
            {
                element: '#observaciones',
                title: 'Observaciones',
                description: 'Obligatorias: de 2 a 2000 caracteres. Detalla el caso (el sistema viejo también las exigía).',
            },
            {
                element: '#notaReferencia',
                title: 'Qué pasa al guardar',
                description: 'La referencia nace en "Pendiente", se notifica al destino y solo el destino (o un administrador) puede aceptarla o rechazarla.',
            },
        ];
    }

    function iniciar() {
        // El IIFE de Driver.js expone window.driver.js.driver(...)
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
            steps: pasos().map((p) => ({
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
     * Devuelve el elemento visible a resaltar. Si es un <select> con Select2,
     * el <select> original está oculto, así que devolvemos su
     * contenedor .select2-container para que el foco sea el select completo.
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
