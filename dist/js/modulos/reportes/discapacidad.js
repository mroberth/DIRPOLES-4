/**
 * dist/js/modulos/reportes/discapacidad.js
 * ---------------------------------------------------------------
 * Reporte de Discapacidad. Filtros aplicados EN SERVIDOR
 * (tipo_discapacidad y grado se validan contra el ENUM en backend).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_discapacidad';
    let filas = [];
    let instanciaTabla = null;
    let chartTipo = null;
    let chartPnf = null;
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
                titulo: 'Reporte Estadístico — Discapacidad',
                canvasIds: ['chartDiscTipo', 'chartDiscPnf'],
                selectorTabla: '#tabla_discapacidad'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/discapacidad?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Discapacidad', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyDiscapacidad');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.tipo_discapacidad)}</span></td>
                <td><span class="badge ${item.grado === 'Grave' ? 'bg-danger' : (item.grado === 'Moderado' ? 'bg-warning text-dark' : 'bg-success')}">${ReportesComunes.escapar(item.grado)}</span></td>
                <td><span class="badge ${item.requiere_asistencia === 'Si' ? 'bg-primary' : 'bg-secondary'}">${item.requiere_asistencia === 'Si' ? 'Sí' : 'No'}</span></td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_discapacidad', {
                titulo: 'Reporte de Discapacidad',
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarGraficos() {
        const tipos = ReportesComunes.contarPor(filas, f => f.tipo_discapacidad || 'Sin tipo');
        chartTipo = ReportesComunes.grafico(
            'chartDiscTipo', chartTipo, Object.keys(tipos), Object.values(tipos),
            ReportesComunes.paleta(Object.keys(tipos).length), 'Distribución por Tipo', tipoChart
        );

        const pnfs = ReportesComunes.contarPor(filas, f => f.nombre_pnf || 'Sin PNF');
        chartPnf = ReportesComunes.grafico(
            'chartDiscPnf', chartPnf, Object.keys(pnfs), Object.values(pnfs),
            ReportesComunes.paleta(Object.keys(pnfs).length), 'Distribución por PNF', tipoChart
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
