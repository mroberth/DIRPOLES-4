/**
 * dist/js/modulos/reportes/referencias.js
 * ---------------------------------------------------------------
 * Reporte de Referencias. Filtros aplicados EN SERVIDOR
 * (estado y servicio_destino se validan en backend).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_referencias';
    let filas = [];
    let instanciaTabla = null;
    let chartEstado = null;
    let chartDestino = null;
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
                titulo: 'Reporte Estadístico — Referencias',
                canvasIds: ['chartRefEstado', 'chartRefDestino'],
                selectorTabla: '#tabla_referencias'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/referencias?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Referencias', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyReferencias');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres_benef)} ${ReportesComunes.escapar(item.apellidos_benef)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula_benef)}</td>
                <td><span class="badge bg-secondary">${ReportesComunes.escapar(item.servicio_origen)}</span></td>
                <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.servicio_destino)}</span></td>
                <td>${ReportesComunes.escapar(item.motivo)}</td>
                <td>${badgeEstado(item.estado)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_referencias', {
                titulo: 'Reporte de Referencias',
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                orden: [[0, 'desc']],
            });
        }
    }

    function badgeEstado(estado) {
        const clases = {
            'Aceptada': 'bg-success',
            'Pendiente': 'bg-warning text-dark',
            'Rechazada': 'bg-danger',
        };
        const clase = clases[estado] || 'bg-secondary';
        return `<span class="badge ${clase}">${ReportesComunes.escapar(estado || 'Sin estado')}</span>`;
    }

    function pintarGraficos() {
        const estados = ReportesComunes.contarPor(filas, f => f.estado || 'Sin estado');
        chartEstado = ReportesComunes.grafico(
            'chartRefEstado', chartEstado, Object.keys(estados), Object.values(estados),
            ReportesComunes.paleta(Object.keys(estados).length), 'Referencias por Estado', tipoChart
        );

        const destinos = ReportesComunes.contarPor(filas, f => f.servicio_destino || 'Sin destino');
        chartDestino = ReportesComunes.grafico(
            'chartRefDestino', chartDestino, Object.keys(destinos), Object.values(destinos),
            ReportesComunes.paleta(Object.keys(destinos).length), 'Por Servicio Destino', tipoChart
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
