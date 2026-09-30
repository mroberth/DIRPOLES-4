/**
 * dist/js/modulos/reportes/medicina.js
 * ---------------------------------------------------------------
 * Reporte de Medicina: consultas médicas + inventario de insumos
 * (dos colecciones en api/reportes/medicina).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_medicina';
    let consultas = [];
    let insumos = [];
    let tablaConsultas = null;
    let tablaInsumos = null;
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
                titulo: 'Reporte Estadístico — Medicina',
                canvasIds: ['chartMedPnf', 'chartMedGenero'],
                selectorTabla: '.table'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            const datos = await apiFetch(BASE_URL + 'api/reportes/medicina?' + params.toString());
            consultas = Array.isArray(datos.consultas) ? datos.consultas : [];
            insumos = Array.isArray(datos.insumos) ? datos.insumos : [];
            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';
            pintarConsultas();
            pintarInsumos();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de Medicina', err);
        }
    }

    function pintarConsultas() {
        const tbody = document.getElementById('tbodyMedicina');
        if (!tbody) return;
        if (tablaConsultas) tablaConsultas.destroy();

        tbody.innerHTML = consultas.map(item => `
            <tr>
                <td>${Formato.fecha(item.fecha)}</td>
                <td><strong>${ReportesComunes.escapar(item.nombres)} ${ReportesComunes.escapar(item.apellidos)}</strong></td>
                <td>${ReportesComunes.escapar(item.cedula)}</td>
                <td>${ReportesComunes.escapar(item.nombre_pnf || 'Sin PNF')}</td>
                <td>${ReportesComunes.escapar(item.motivo)}</td>
                <td>${ReportesComunes.escapar(item.diagnostico)}</td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            tablaConsultas = DataTableHelper.inicializar('#tabla_medicina', {
                titulo: 'Reporte de Consultas Médicas',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[0, 'desc']],
            });
        }
    }

    function pintarInsumos() {
        const tbody = document.getElementById('tbodyInsumos');
        if (!tbody) return;
        if (tablaInsumos) tablaInsumos.destroy();

        tbody.innerHTML = insumos.map(item => `
            <tr>
                <td><strong>${ReportesComunes.escapar(item.nombre_insumo)}</strong></td>
                <td>${ReportesComunes.escapar(item.tipo_insumo || 'Sin tipo')}</td>
                <td>${ReportesComunes.escapar(item.presentacion || 'Sin presentación')}</td>
                <td>${Number(item.cantidad) || 0}</td>
                <td>${Formato.fecha(item.fecha_vencimiento)}</td>
                <td><span class="badge ${item.estatus === 'Disponible' ? 'bg-success' : (item.estatus === 'Agotado' ? 'bg-secondary' : 'bg-danger')}">${ReportesComunes.escapar(item.estatus)}</span></td>
            </tr>
        `).join('');

        if (typeof DataTableHelper !== 'undefined') {
            tablaInsumos = DataTableHelper.inicializar('#tabla_insumos', {
                titulo: 'Inventario de Insumos Médicos',
                columnasExport: [0, 1, 2, 3, 4, 5],
                orden: [[4, 'asc']],
            });
        }
    }

    function pintarGraficos() {
        const pnfs = ReportesComunes.contarPor(consultas, f => f.nombre_pnf || 'Sin PNF');
        chartPnf = ReportesComunes.grafico(
            'chartMedPnf', chartPnf, Object.keys(pnfs), Object.values(pnfs),
            ReportesComunes.paleta(Object.keys(pnfs).length), 'Consultas por PNF', tipoChart
        );

        const generos = ReportesComunes.contarPor(consultas, f => (f.genero === 'F' ? 'Femenino' : 'Masculino'));
        const etiquetas = ['Masculino', 'Femenino'].filter(e => generos[e] !== undefined);
        chartGenero = ReportesComunes.grafico(
            'chartMedGenero', chartGenero, etiquetas,
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
