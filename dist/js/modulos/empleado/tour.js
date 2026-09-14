// dist/js/modulos/empleado/tour.js
// ------------------------------------------------------------------
// Tour guiado con Driver.js (exigencia de la universidad).
// Reusable: crear.js y editar.js solo tienen que llamar a:
//   window.EmpleadoTour.iniciar();
// Requiere que Driver.js esté cargado (lo hace script.php) y que existan
// los elementos con los ids del formulario de empleado.
// ------------------------------------------------------------------
window.EmpleadoTour = (function () {
    'use strict';

    const PASOS = [
        { element: '#tipo_cedula',      title: 'Tipo de cédula',        description: 'Selecciona V (venezolano) o E (extranjero).' },
        { element: '#cedula',           title: 'Cédula',                description: 'Escribe el número de cédula (6 a 10 dígitos).' },
        { element: '#nombre',           title: 'Nombre',                description: 'Nombre del empleado.' },
        { element: '#apellido',         title: 'Apellido',              description: 'Apellido del empleado.' },
        { element: '#correo',           title: 'Correo electrónico',    description: 'Correo válido (ej: correo@uptaeb.edu.ve).' },
        { element: '#telefono',         title: 'Teléfono',              description: 'Número de teléfono (7 a 12 dígitos).' },
        { element: '#id_tipo_empleado', title: 'Tipo de empleado',      description: 'Selecciona el cargo de la lista.' },
        { element: '#fecha_nacimiento', title: 'Fecha de nacimiento',   description: 'Selecciona la fecha de nacimiento.' },
        { element: '#clave',            title: 'Contraseña',            description: 'Mínimo 8 caracteres: una letra, un número y un carácter especial.' },
        { element: '#estatus',          title: 'Estatus',               description: 'Activo para empleados vigentes, Inactivo si no.' },
        { element: '#direccion',        title: 'Dirección',             description: 'Dirección completa del empleado.' },
    ];

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
            steps: PASOS.map((p) => ({
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
