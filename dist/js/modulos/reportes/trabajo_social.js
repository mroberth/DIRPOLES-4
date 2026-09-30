/**
 * dist/js/modulos/reportes/trabajo_social.js
 * ---------------------------------------------------------------
 * Reporte de Trabajo Social (Becas, Exoneraciones, FAMES, Gestión de
 * Embarazo). Filtros aplicados EN SERVIDOR (submodulo, pnf, fechas).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_ts';
    let filas = [];
    let instanciaTabla = null;
    let chartSubmodulo = null;
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
                titulo: 'Reporte Estadístico — Trabajo Social',
                canvasIds: ['chartTsSubmodulo', 'chartTsPnf'],
                selectorTabla: '#tabla_trabajo_social'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/trabajo-social?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Trabajo Social', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyTrabajoSocial');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.submodulo)}</span></td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td>${ReportesComunes.escapar(item.detalle_extra)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_trabajo_social', {
                titulo: 'Reporte de Trabajo Social',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarGraficos() {
        const submodulos = ReportesComunes.contarPor(filas, f => f.submodulo);
        chartSubmodulo = ReportesComunes.grafico(
            'chartTsSubmodulo', chartSubmodulo, Object.keys(submodulos), Object.values(submodulos),
            ReportesComunes.paleta(Object.keys(submodulos).length), 'Atenciones por Beneficio', tipoChart
        );

        const pnfs = ReportesComunes.contarPor(filas, f => f.nombre_pnf || 'Sin PNF');
        chartPnf = ReportesComunes.grafico(
            'chartTsPnf', chartPnf, Object.keys(pnfs), Object.values(pnfs),
            ReportesComunes.paleta(Object.keys(pnfs).length), 'Beneficiarios por PNF', tipoChart
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
