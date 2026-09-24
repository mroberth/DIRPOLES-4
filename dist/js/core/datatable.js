// dist/js/core/datatable.js
// ------------------------------------------------------------------
// Handler ÚNICO de DataTables del sistema (frontend).
//
// Centraliza lo que todos los módulos repetían:
//   - idioma local (plugins/DataTables/js/languaje.json)
//   - autoWidth: false
//   - botones Excel/PDF con título y columnas exportables
//   - orden inicial y pageLength por defecto
//
// Uso típico:
//
//   const tabla = DataTableHelper.inicializar('#tablaX', {
//       titulo: 'Consultas psicológicas',
//       orden: [[0, 'desc']],
//       columnasExport: [0, 1, 2, 3, 4, 5],   // excluye la última (Acciones)
//       columnDefs: [
//           { targets: 0, width: '115px' },
//           { targets: 6, width: '130px', orderable: false,
//             className: 'text-center text-nowrap' },
//       ],
//   });
//
// Para recargar: destruir la instancia previa antes de volver a pintar y
// llamar de nuevo a `inicializar` (los módulos ya lo hacen en su cargar()).
// Devuelve la instancia de DataTable o null si jQuery/DataTables no están.
// ------------------------------------------------------------------
window.DataTableHelper = (function () {
    'use strict';

    const URL_IDIOMA = () => BASE_URL + 'plugins/DataTables/js/languaje.json';

    /**
     * Limpia el HTML de las celdas al exportar (quita botones, badges, iconos…),
     * convirtiendo los saltos de línea en espacios para que fecha+hora queden
     * legibles en Excel/PDF.
     */
    const limpiarHtml = {
        body: (data) => String(data)
            .replace(/<br\s*\/?>/gi, ' ')
            .replace(/<\/(div|p|small|span|td)>/gi, ' ')
            .replace(/<[^>]*>/g, ' ')
            .replace(/\s+/g, ' ')
            .trim(),
    };

    function construirBotones(titulo, columnasExport, exportar) {
        if (exportar === false) return [];

        const exportOptions = columnasExport
            ? { columns: columnasExport, format: limpiarHtml }
            : { format: limpiarHtml };

        return [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm me-1',
                title: titulo || 'Reporte',
                exportOptions,
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: titulo || 'Reporte',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions,
            },
        ];
    }

    /**
     * Inicializa (o re-inicializa) un DataTable con la configuración común.
     * @param {string|Element|jQuery} selector  id/selector de la tabla.
     * @param {Object}   [opciones]
     * @param {string}   [opciones.titulo]         Título para Excel/PDF.
     * @param {Array}    [opciones.orden]          Ej: [[0, 'desc']].
     * @param {number}   [opciones.pageLength]     Por defecto 10.
     * @param {Array}    [opciones.lengthMenu]     Ej: [[10,20,50,-1],[10,20,50,'Todos']].
     * @param {Array}    [opciones.columnDefs]     Anchos y clases por columna.
     * @param {Array}    [opciones.columnasExport] Índices exportables (sin Acciones).
     * @param {boolean}  [opciones.exportar=true]  false = sin botones.
     * @param {boolean}  [opciones.responsive=false]
     * @returns {Object|null}
     */
    function inicializar(selector, opciones = {}) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.DataTable) {
            console.warn('DataTableHelper: jQuery o DataTables no están disponibles.');
            return null;
        }

        const config = {
            language: { url: URL_IDIOMA() },
            order: opciones.orden || [],
            pageLength: opciones.pageLength != null ? opciones.pageLength : 10,
            autoWidth: false,
            columnDefs: opciones.columnDefs || [],
            layout: {
                topStart: {
                    buttons: construirBotones(opciones.titulo, opciones.columnasExport, opciones.exportar),
                },
            },
        };

        if (opciones.lengthMenu) config.lengthMenu = opciones.lengthMenu;
        if (opciones.responsive) config.responsive = true;
        if (opciones.dom) config.dom = opciones.dom;

        return $(selector).DataTable(config);
    }

    return { inicializar, limpiarHtml };
})();
