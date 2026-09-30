/**
 * dist/js/modulos/reportes/jornadas.js
 * ---------------------------------------------------------------
 * Reporte de Jornadas Médicas. Filtros aplicados EN SERVIDOR.
 * El campo del lugar es `ubicacion` (no `lugar`).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_jornadas';
    let filas = [];
    let instanciaTabla = null;
    let chartAsistentes = null;
    let chartEstado = null;
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
                titulo: 'Reporte Estadístico — Jornadas Médicas',
                canvasIds: ['chartJorAsistentes', 'chartJorEstado'],
                selectorTabla: '#tabla_jornadas'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            filas = await apiFetch(BASE_URL + 'api/reportes/jornadas?' + params.toString());
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarTabla();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Jornadas', err);
        }
    }

    function pintarTabla() {
        const tbody = document.getElementById('tbodyJornadas');
        if (!tbody) return;
        if (instanciaTabla) instanciaTabla.destroy();

        tbody.innerHTML = filas.map(item => `
            <tr>
                <td><strong>${ReportesComunes.escapar(item.nombre_jornada)}</strong></td>
                <td>${ReportesComunes.escapar(item.tipo_jornada)}</td>
                <td>${ReportesComunes.escapar(item.ubicacion)}</td>
                <td>${Formato.fechaHora(item.fecha_inicio)}</td>
                <td>${Number(item.total_asistentes) || 0}</td>
                <td>${Number(item.total_diagnosticos) || 0}</td>
                <td>${badgeEstatus(item.estatus)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            instanciaTabla = DataTableHelper.inicializar('#tabla_jornadas', {
                titulo: 'Reporte de Jornadas Médicas',
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                orden: [[3, 'desc']],
            });
        }
    }

    function badgeEstatus(estatus) {
        const clases = {
            'Activa': 'bg-success',
            'Finalizada': 'bg-secondary',
            'Cancelada': 'bg-danger',
        };
        const clase = clases[estatus] || 'bg-secondary';
        return `<span class="badge ${clase}">${ReportesComunes.escapar(estatus || 'Sin estatus')}</span>`;
    }

    function pintarGraficos() {
        const etiquetas = filas.map(f => f.nombre_jornada || ('Jornada ' + f.id_jornada));
        const asistentes = filas.map(f => Number(f.total_asistentes) || 0);
        chartAsistentes = ReportesComunes.grafico(
            'chartJorAsistentes', chartAsistentes, etiquetas, asistentes,
            ReportesComunes.paleta(etiquetas.length), 'Asistentes por Jornada', tipoChart
        );

        const estados = ReportesComunes.contarPor(filas, f => f.estatus || 'Sin estatus');
        chartEstado = ReportesComunes.grafico(
            'chartJorEstado', chartEstado, Object.keys(estados), Object.values(estados),
            ReportesComunes.paleta(Object.keys(estados).length), 'Estado de Jornadas', tipoChart
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
