// dist/js/modulos/inventario/tour.js
// ------------------------------------------------------------------
// Tour guiado con Driver.js (exigencia de la universidad).
// Reusable: crear.js solo tiene que llamar a:
//   window.InventarioTour.iniciar();
// Requiere que Driver.js esté cargado (lo hace script.php) y que existan
// los elementos con los ids del formulario de insumo.
// ------------------------------------------------------------------
window.InventarioTour = (function () {
    'use strict';

    const PASOS = [
        {
            element: '#nombre_insumo',
            title: 'Nombre del insumo',
            description: 'Obligatorio: 2 a 100 caracteres (letras, números, espacios, puntos y guiones). Debe ser único junto con presentación, tipo y fecha de vencimiento.',
        },
        {
            element: '#tipo_insumo',
            title: 'Tipo de insumo',
            description: 'Selecciona Medicamento, Material o Quirúrgico.',
        },
        {
            element: '#id_presentacion',
            title: 'Presentación',
            description: 'Se carga desde el catálogo "Presentación de Insumo" de Configuración (ej: Tableta, Jarabe). Si no hay opciones, créalas primero allí.',
        },
        {
            element: '#fecha_vencimiento',
            title: 'Fecha de vencimiento',
            description: 'Obligatoria y NO puede ser pasada: no se crean insumos ya vencidos. El rango de entrada posterior se decide con esta fecha.',
        },
        {
            element: '#descripcion',
            title: 'Descripción',
            description: 'Obligatoria: 2 a 250 caracteres. Ej: "Pastillas de acetaminofen".',
        },
        {
            element: '#notaStock',
            title: 'La cantidad no va aquí',
            description: 'El insumo nace en 0 (Agotado). El stock se registra con el botón Entrada en la pantalla Consultar; las salidas se hacen con el botón Salida.',
        },
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
