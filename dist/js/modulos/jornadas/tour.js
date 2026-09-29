// dist/js/modulos/jornadas/tour.js
// ------------------------------------------------------------------
// Tour guiado con Driver.js del módulo Jornadas:
//   window.JornadasTour.iniciar();         → formulario de CREAR jornada
//   window.JornadasTour.iniciarDetalle();  → página de DETALLE de jornada
// Los pasos con elementos ausentes (p. ej. sin permiso) se omiten solos.
// Requiere Driver.js (lo incluye template/script.php).
// ------------------------------------------------------------------
window.JornadasTour = (function () {
    'use strict';

    /**
     * Devuelve el elemento visible a resaltar. Si es un <select> con Select2,
     * el <select> original está oculto, así que devolvemos su contenedor
     * .select2-container para que el foco sea el select completo.
     */
    function objetivo(selector) {
        const el = document.querySelector(selector);
        if (!el) return null;
        if (el.tagName === 'SELECT' && el.classList.contains('select2')
            && el.nextElementSibling && el.nextElementSibling.classList.contains('select2-container')) {
            return el.nextElementSibling;
        }
        return el;
    }

    function fabricaDriver() {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);
        return typeof factory === 'function' ? factory : null;
    }

    function correr(pasos) {
        const factory = fabricaDriver();
        if (!factory) {
            console.warn('Driver.js no está disponible.');
            return;
        }

        const visibles = pasos
            .map((p) => ({ paso: p, elemento: objetivo(p.element) }))
            .filter((x) => x.elemento !== null);

        if (!visibles.length) {
            console.warn('Tour: ningún paso tiene elementos visibles.');
            return;
        }

        const driverObj = factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: visibles.map((x) => ({
                element: x.elemento,
                popover: {
                    title: x.paso.title,
                    description: x.paso.description,
                    side: x.paso.side || 'bottom',
                    align: 'start',
                },
            })),
        });

        driverObj.drive();
    }

    // ---------------- Tour: crear jornada ----------------
    function pasosCrear() {
        return [
            {
                element: '#nombre_jornada',
                title: 'Nombre de la jornada',
                description: 'Obligatorio (3 a 100 caracteres). Es como se identificará en la tabla de consulta: p. ej. "Jornada Médica Comunitaria 2026".',
            },
            {
                element: '#tipo_jornada',
                title: 'Tipo de jornada',
                description: 'Catálogo fijo del sistema (Vacunación, Odontológica, Revisión Médica…). Debe elegirse alguno.',
            },
            {
                element: '#aforo_maximo',
                title: 'Aforo máximo',
                description: 'Entre 1 y 5000 personas. El sistema lo protege con bloqueo de fila: dos registros simultáneos nunca lo rebasan. Al editarlo no puede bajar por debajo de los ya registrados.',
            },
            {
                element: '#fecha_inicio',
                title: 'Inicio',
                description: 'Fecha y hora en que empieza la jornada.',
            },
            {
                element: '#fecha_fin',
                title: 'Cierre',
                description: 'No puede ser anterior al inicio, y al crear no puede estar en el pasado. Mientras no pase esta fecha y el estatus sea "Activa", la jornada acepta asistentes.',
            },
            {
                element: '#ubicacion',
                title: 'Ubicación',
                description: 'Obligatoria (3 a 255 caracteres): dónde se realiza la jornada.',
            },
            {
                element: '#descripcion',
                title: 'Descripción',
                description: 'Opcional (hasta 2000 caracteres): objetivo, servicios que se ofrecen o requisitos.',
            },
            {
                element: '#notaJornada',
                title: 'Qué pasa al guardar',
                description: 'La jornada nace ACTIVA y se avisa a los demás empleados con permiso en el módulo. Los asistentes y sus diagnósticos se registran desde el botón "Detalle" de la tabla de consulta.',
            },
        ];
    }

    function iniciar() {
        correr(pasosCrear());
    }

    // ---------------- Tour: detalle de la jornada ----------------
    function pasosDetalle() {
        return [
            {
                element: '#jornadaEstatus',
                title: 'Estatus',
                description: 'Solo una jornada "Activa" y vigente por fecha admite nuevos asistentes o diagnósticos. El estatus se cambia desde Editar en la tabla de consulta.',
            },
            {
                // El objetivo es el CONTENEDOR .progress (ancho completo):
                // apuntar al relleno #jornadaBarra deja un recuadro de ancho
                // cero cuando la ocupación es 0%.
                element: '#jornadaProgreso',
                title: 'Ocupación del aforo',
                description: 'Personas registradas sobre el aforo máximo (la barra se llena a medida que se registran). El registro falla con un mensaje claro si el aforo se llena.',
            },
            {
                element: '#btn-nuevo-asistente',
                title: 'Nuevo asistente',
                description: 'Abre el formulario de alta. Los datos pueden escribirse a mano o autocompletarse con la búsqueda por cédula.',
            },
            {
                element: '#asis_tipo_cedula',
                title: 'Tipo de documento',
                description: 'V, E, J o G: la combinación tipo + cédula define a la persona DENTRO de esta jornada.',
            },
            {
                element: '#asis_cedula',
                title: 'Cédula y búsqueda',
                description: '5 a 12 dígitos. El botón de la lupa busca en beneficiarios y empleados y rellena los campos, pero quien CONFIRMA los datos eres tú. Una misma cédula no se repite en esta jornada.',
            },
            {
                element: '#notaAsistente',
                title: 'Reglas del alta',
                description: 'Si la cédula ya está en esta jornada el backend responde 409, y si el aforo está lleno o la jornada no está activa, bloquea el registro.',
            },
            {
                element: '#tablaAsistentes',
                title: 'Asistentes',
                description: 'Cada fila tiene su edad, cuántos diagnósticos lleva y los botones de diagnósticos y de eliminación (esta última solo si aún no tiene diagnósticos). Pulsa el ícono de notas para ver o agregar los diagnósticos de esa persona.',
            },
        ];
    }

    /**
     * Abre el colapso del formulario de asistente (si existe) y lanza el tour
     * del detalle: así los pasos del formulario siempre tienen elemento visible.
     */
    function iniciarDetalle() {
        const colapso = document.getElementById('colapsoAsistente');
        if (colapso && !colapso.classList.contains('show')
            && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            bootstrap.Collapse.getOrCreateInstance(colapso, { toggle: false }).show();
            setTimeout(() => correr(pasosDetalle()), 450);
            return;
        }
        correr(pasosDetalle());
    }

    return { iniciar, iniciarDetalle };
})();
