// dist/js/core/select-2-init.js
// ------------------------------------------------------------------
// Inicialización GLOBAL de Select2 por CLASE: cualquier <select class="select2">
// se inicializa solo. No hay que llamar a select2() en cada módulo.
//
//   <select class="select2" data-placeholder="Seleccione…"> ... </select>
//
// Si el <select> se llena DESPUÉS de cargar la página (opciones por AJAX),
// vuelve a llamar a window.initSelect2() (o initSelect2('#form-x')).
// ------------------------------------------------------------------
(function () {
    'use strict';

    function opciones($el) {
        const opts = {
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: $el.data('allow-clear') !== false,
            placeholder: $el.attr('data-placeholder') || 'Seleccione una opción',
            language: {
                noResults: function () { return 'Sin resultados'; },
                searching: function () { return 'Buscando…'; },
            },
        };

        // Si el select vive dentro de un modal, el desplegable debe colgar del
        // modal (si no, queda detrás por el z-index de Bootstrap).
        const $modal = $el.closest('.modal');
        if ($modal.length) {
            opts.dropdownParent = $modal;
        }

        return opts;
    }

    /**
     * Inicializa (o re-inicializa) todos los .select2 dentro de `scope`.
     * @param {string|Element|jQuery} [scope] selector o elemento contenedor.
     */
    function initSelect2(scope) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) {
            return;
        }

        const $scope = scope ? $(scope) : $(document);

        $scope.find('.select2').each(function () {
            const $el = $(this);

            // Si ya estaba inicializado lo destruimos para releer las opciones
            // (necesario cuando se agregaron <option> por AJAX).
            if ($el.data('select2')) {
                $el.select2('destroy');
            }
            $el.select2(opciones($el));
        });
    }

    window.initSelect2 = initSelect2;

    document.addEventListener('DOMContentLoaded', function () {
        initSelect2(document);
    });
})();
