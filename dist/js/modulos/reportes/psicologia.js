/**
 * dist/js/modulos/reportes/psicologia.js
 * ---------------------------------------------------------------
 * Reporte de Psicología: morbilidad (consulta_psicologica) + citas
 * (dos colecciones en api/reportes/psicologia).
 * Filtros: tipo_consulta → morbilidad; estado → citas.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_psicologia';
    let morbilidad = [];
    let citas = [];
    let tablaMorbilidad = null;
    let tablaCitas = null;
    let chartEstado = null;
    let chartTipo = null;
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
                titulo: 'Reporte Estadístico — Psicología',
                canvasIds: ['chartPsEstado', 'chartPsTipo'],
                selectorTabla: '.table'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            const datos = await apiFetch(BASE_URL + 'api/reportes/psicologia?' + params.toString());
            morbilidad = Array.isArray(datos.morbilidad) ? datos.morbilidad : [];
            citas = Array.isArray(datos.citas) ? datos.citas : [];
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarMorbilidad();
            pintarCitas();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Psicología', err);
        }
    }

    function pintarMorbilidad() {
        const tbody = document.getElementById('tbodyMorbilidad');
        if (!tbody) return;
        if (tablaMorbilidad) tablaMorbilidad.destroy();

        tbody.innerHTML = morbilidad.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.tipo_consulta)}</span></td>
                <td>${ReportesComunes.escapar(item.diagnostico)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            tablaMorbilidad = DataTableHelper.inicializar('#tabla_morbilidad', {
                titulo: 'Morbilidad Psicológica',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarCitas() {
        const tbody = document.getElementById('tbodyPsicologia');
        if (!tbody) return;
        if (tablaCitas) tablaCitas.destroy();

        tbody.innerHTML = citas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td>${ReportesComunes.escapar(item.hora)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td>${ReportesComunes.escapar(item.psicologo || 'Sin asignar')}</td>
                <td>${badgeEstado(item.estado)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            tablaCitas = DataTableHelper.inicializar('#tabla_psicologia', {
                titulo: 'Citas de Psicología',
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                orden: [[0, 'desc']],
            });
        }
    }

    function badgeEstado(estado) {
        const clases = {
            'Atendida': 'bg-success',
            'Confirmada': 'bg-primary',
            'Pendiente': 'bg-warning text-dark',
            'Cancelada': 'bg-secondary',
            'No asistió': 'bg-danger',
        };
        const clase = clases[estado] || 'bg-secondary';
        return `<span class="badge ${clase}">${ReportesComunes.escapar(estado || 'Sin estado')}</span>`;
    }

    function pintarGraficos() {
        const estados = ReportesComunes.contarPor(citas, f => f.estado || 'Sin estado');
        chartEstado = ReportesComunes.grafico(
            'chartPsEstado', chartEstado, Object.keys(estados), Object.values(estados),
            ReportesComunes.paleta(Object.keys(estados).length), 'Estado de Citas', tipoChart
        );

        const tipos = ReportesComunes.contarPor(morbilidad, f => f.tipo_consulta || 'Sin tipo');
        chartTipo = ReportesComunes.grafico(
            'chartPsTipo', chartTipo, Object.keys(tipos), Object.values(tipos),
            ReportesComunes.paleta(Object.keys(tipos).length), 'Morbilidad por Tipo', tipoChart
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
