/**
 * dist/js/modulos/reportes/orientacion.js
 * ---------------------------------------------------------------
 * Reporte de Orientación. Filtros aplicados EN SERVIDOR.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_orientacion';
    let filas = [];
    let instanciaTabla = null;
    let chartPnf = null;
    let chartGenero = null;
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
                titulo: 'Reporte Estadístico — Orientación',
                canvasIds: ['chartOrPnf', 'chartOrGenero'],
                selectorTabla: '#tabla_orientacion'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/orientacion?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Orientación', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyOrientacion');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td>${ReportesComunes.escapar(item.motivo_consulta)}</td>
                <td>${ReportesComunes.escapar(item.indicaciones)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_orientacion', {
                titulo: 'Reporte de Orientación',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarGraficos() {
        const pnfs = ReportesComunes.contarPor(filas, f => f.nombre_pnf || 'Sin PNF');
        chartPnf = ReportesComunes.grafico(
            'chartOrPnf', chartPnf, Object.keys(pnfs), Object.values(pnfs),
            ReportesComunes.paleta(Object.keys(pnfs).length), 'Orientación por PNF', tipoChart
        );

        const generos = ReportesComunes.contarPor(filas, f => (f.genero === 'F' ? 'Femenino' : 'Masculino'));
        const etiquetas = ['Masculino', 'Femenino'].filter(e => generos[e] !== undefined);
        chartGenero = ReportesComunes.grafico(
            'chartOrGenero', chartGenero, etiquetas,
            etiquetas.map(e => generos[e]),
            ['#4e73df', '#e74a3b'], 'Distribución por Género', tipoChart
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
