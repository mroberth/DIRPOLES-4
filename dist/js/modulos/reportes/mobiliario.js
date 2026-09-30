/**
 * dist/js/modulos/reportes/mobiliario.js
 * ---------------------------------------------------------------
 * Reporte de Mobiliario y Equipos. Filtros aplicados EN SERVIDOR
 * (tipo_bien y estado se validan en backend).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_mobiliario';
    let filas = [];
    let instanciaTabla = null;
    let chartTipo = null;
    let chartEstatus = null;
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
                titulo: 'Reporte Estadístico — Mobiliario y Equipos',
                canvasIds: ['chartMobTipo', 'chartMobEstatus'],
                selectorTabla: '#tabla_mobiliario'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/mobiliario?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Mobiliario', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyMobiliario');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td><strong>${ReportesComunes.escapar(item.nombre_item)}</strong></td>
                <td><span class="badge ${item.tipo_bien === 'Equipo' ? 'bg-info text-dark' : 'bg-primary'}">${ReportesComunes.escapar(item.tipo_bien)}</span></td>
                <td>${ReportesComunes.escapar(item.categoria || 'Sin categoría')}</td>
                <td><strong>${Number(item.cantidad) || 0}</strong></td>
                <td><span class="badge ${item.estatus === 'Activo' ? 'bg-success' : 'bg-secondary'}">${ReportesComunes.escapar(item.estatus)}</span></td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_mobiliario', {
                titulo: 'Reporte Mobiliario y Equipos',
                columnasExport: [0, 1, 2, 3, 4],
                orden: [[0, 'asc']],
            });
        }
    }

    function pintarGraficos() {
        const tipos = ReportesComunes.contarPor(filas, f => f.tipo_bien);
        chartTipo = ReportesComunes.grafico(
            'chartMobTipo', chartTipo, Object.keys(tipos), Object.values(tipos),
            ReportesComunes.paleta(Object.keys(tipos).length), 'Distribución por Tipo de Bien', tipoChart
        );

        const estatus = ReportesComunes.contarPor(filas, f => f.estatus || 'Sin estatus');
        chartEstatus = ReportesComunes.grafico(
            'chartMobEstatus', chartEstatus, Object.keys(estatus), Object.values(estatus),
            ReportesComunes.paleta(Object.keys(estatus).length), 'Estatus de Inventario', tipoChart
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
