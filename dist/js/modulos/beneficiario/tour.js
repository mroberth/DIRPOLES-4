// dist/js/modulos/beneficiario/tour.js
// Tour guiado con Driver.js (exigencia de la universidad).
window.BeneficiarioTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#tipo_cedula', title: 'Tipo de cédula', description: 'Selecciona V (venezolano) o E (extranjero).' },
        { element: '#cedula',      title: 'Cédula',         description: 'Escribe el número de cédula (6 a 10 dígitos).' },
        { element: '#nombres',     title: 'Nombres',        description: 'Nombres del beneficiario.' },
        { element: '#apellidos',   title: 'Apellidos',      description: 'Apellidos del beneficiario.' },
        { element: '#correo',      title: 'Correo',         description: 'Correo válido (ej: correo@uptaeb.edu.ve).' },
        { element: '#telefono',    title: 'Teléfono',       description: '0412/0414/0416/0422/0424/0426 + 7 dígitos.' },
        { element: '#genero',      title: 'Género',         description: 'Masculino o Femenino.' },
        { element: '#id_pnf',      title: 'PNF',            description: 'Selecciona el programa nacional de formación.' },
        { element: '#seccion',     title: 'Sección',        description: 'Sección del beneficiario (ej: 3102-B).' },
        { element: '#fecha_nac',   title: 'Fecha de nacimiento', description: 'Selecciona la fecha de nacimiento.' },
        { element: '#estatus',     title: 'Estatus',        description: 'Activo o Inactivo.' },
        { element: '#direccion',   title: 'Dirección',      description: 'Dirección del beneficiario.' },
    ];

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

    /**
     * Devuelve el elemento visible a resaltar. Si es un <select> con Select2,
     * el <select> original está oculto (1x1 px), así que devolvemos su
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
