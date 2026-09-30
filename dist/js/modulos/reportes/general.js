/**
 * dist/js/modulos/reportes/general.js
 * ---------------------------------------------------------------
 * Reporte General (7 servicios). Los filtros se envían al servidor
 * (api/reportes/general?...) y las tarjetas usa stats.js.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_general';
    let filas = [];
    let instanciaTabla = null;
    let chartGenero = null;
    let chartPnf = null;
    let chartArea = null;
    let tipoChart = 'bar';

    const form = document.getElementById('form-reporte');
    const btnLimpiar = document.getElementById('btn-limpiar');
    const selectChart = document.getElementById('select-tipo-chart');
    const btnPdfCompleto = document.getElementById('btn-pdf-completo');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            generar();
        });
    }
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function () {
            if (form) form.reset();
        });
    }
    if (selectChart) {
        selectChart.addEventListener('change', function () {
            tipoChart = this.value;
            pintarGraficos();
        });
    }
    if (btnPdfCompleto) {
        btnPdfCompleto.addEventListener('click', function () {
            ReportesPdfCompleto.generar({
                titulo: 'Reporte General de Atenciones',
                canvasIds: ['chartG', 'chartP', 'chartGeneral'],
                selectorTabla: '#tabla_general'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/general?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte general', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyGeneral');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td><span class="badge ${item.genero === 'F' ? 'bg-danger' : 'bg-primary'}">${ReportesComunes.escapar(item.genero) || 'N/A'}</span></td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.area)}</span></td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_general', {
                titulo: 'Reporte General de Atenciones',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarGraficos() {
        const generos = ReportesComunes.contarPor(filas, f => (f.genero === 'F' ? 'Femenino' : 'Masculino'));
        const etiquetasGenero = ['Masculino', 'Femenino'].filter(e => generos[e] !== undefined);
        chartGenero = ReportesComunes.grafico(
            'chartG', chartGenero, etiquetasGenero,
            etiquetasGenero.map(e => generos[e]),
            ['#4e73df', '#e74a3b'], 'Distribución Género', tipoChart
        );

        const pnfs = ReportesComunes.contarPor(filas, f => f.nombre_pnf || 'Sin PNF');
        chartPnf = ReportesComunes.grafico(
            'chartP', chartPnf, Object.keys(pnfs), Object.values(pnfs),
            ReportesComunes.paleta(Object.keys(pnfs).length), 'Distribución PNF', tipoChart
        );

        const areas = ReportesComunes.contarPor(filas, f => f.area || 'General');
        chartArea = ReportesComunes.grafico(
            'chartGeneral', chartArea, Object.keys(areas), Object.values(areas),
            ReportesComunes.paleta(Object.keys(areas).length), 'Atenciones por Área', tipoChart
        );
    }

    (async function iniciar() {
        try {
            await ReportesComunes.cargarCatalogos();
        } catch (err) {
            ReportesComunes.error('No se pudieron cargar los catálogos', err);
        }
    })();
});
