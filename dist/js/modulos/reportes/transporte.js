/**
 * dist/js/modulos/reportes/transporte.js
 * ---------------------------------------------------------------
 * Reporte Estadístico de Transporte (Multi-sección: Vehículos, Rutas,
 * Proveedores, Repuestos, Asignaciones, Mantenimientos).
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const CONTENEDOR = '#contenedor_transporte';
    let datosRespuesta = { vehiculos: [], rutas: [], proveedores: [], repuestos: [], asignaciones: [], mantenimientos: [] };
    let tablas = {};
    let chartEstado = null;
    let chartSeccion = null;
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
                titulo: 'Reporte Estadístico de Transporte',
                canvasIds: ['chartTransEstado', 'chartTransSeccion'],
                selectorTabla: '.table'
            });
        });
    }

    async function generar() {
        try {
            const params = ReportesComunes.parametros();
            const res = await apiFetch(BASE_URL + 'api/reportes/transporte?' + params.toString());
            datosRespuesta = {
                vehiculos: res.vehiculos || [],
                rutas: res.rutas || [],
                proveedores: res.proveedores || [],
                repuestos: res.repuestos || [],
                asignaciones: res.asignaciones || [],
                mantenimientos: res.mantenimientos || [],
            };

            const contenedor = document.querySelector(CONTENEDOR);
            if (contenedor) contenedor.style.display = 'block';

            pintarTablas();
            pintarGraficos();
        } catch (err) {
            ReportesComunes.error('No se pudo generar el reporte de transporte', err);
        }
    }

    function destruirTabla(clave) {
        if (tablas[clave]) {
            tablas[clave].destroy();
            tablas[clave] = null;
        }
    }

    function pintarTablas() {
        // 1. Vehículos
        const tbodyVeh = document.getElementById('tbodyVehiculos');
        if (tbodyVeh) {
            destruirTabla('vehiculos');
            tbodyVeh.innerHTML = datosRespuesta.vehiculos.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.placa)}</strong></td>
                    <td>${ReportesComunes.escapar(item.modelo)}</td>
                    <td><span class="badge bg-secondary">${ReportesComunes.escapar(item.tipo)}</span></td>
                    <td><span class="badge ${item.estado === 'Activo' ? 'bg-success' : (item.estado === 'Mantenimiento' ? 'bg-warning text-dark' : 'bg-danger')}">${ReportesComunes.escapar(item.estado)}</span></td>
                    <td>${Formato.fecha(item.fecha_adquisicion)}</td>
                    <td>${item.asignaciones_activas}</td>
                    <td>${item.total_mantenimientos}</td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.vehiculos = DataTableHelper.inicializar('#tabla_transporte_vehiculos', {
                    titulo: 'Reporte Transporte - Vehículos',
                    columnasExport: [0, 1, 2, 3, 4, 5, 6],
                    orden: [[0, 'asc']],
                });
            }
        }

        // 2. Rutas
        const tbodyRut = document.getElementById('tbodyRutas');
        if (tbodyRut) {
            destruirTabla('rutas');
            tbodyRut.innerHTML = datosRespuesta.rutas.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.nombre_ruta)}</strong></td>
                    <td><span class="badge bg-info text-dark">${ReportesComunes.escapar(item.tipo_ruta)}</span></td>
                    <td>${ReportesComunes.escapar(item.punto_partida)} → ${ReportesComunes.escapar(item.punto_destino)}</td>
                    <td><span class="badge ${item.estatus === 'Activa' ? 'bg-success' : 'bg-danger'}">${ReportesComunes.escapar(item.estatus)}</span></td>
                    <td>${Formato.fecha(item.fecha_creacion)}</td>
                    <td>${item.asignaciones_activas}</td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.rutas = DataTableHelper.inicializar('#tabla_transporte_rutas', {
                    titulo: 'Reporte Transporte - Rutas',
                    columnasExport: [0, 1, 2, 3, 4, 5],
                    orden: [[0, 'asc']],
                });
            }
        }

        // 3. Proveedores
        const tbodyProv = document.getElementById('tbodyProveedores');
        if (tbodyProv) {
            destruirTabla('proveedores');
            tbodyProv.innerHTML = datosRespuesta.proveedores.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.nombre)}</strong></td>
                    <td>${ReportesComunes.escapar(item.tipo_documento)}-${ReportesComunes.escapar(item.documento)}</td>
                    <td>${ReportesComunes.escapar(item.telefono)}</td>
                    <td>${ReportesComunes.escapar(item.correo)}</td>
                    <td><span class="badge ${item.estatus === 'Activo' ? 'bg-success' : 'bg-danger'}">${ReportesComunes.escapar(item.estatus)}</span></td>
                    <td>${Formato.fecha(item.fecha_creacion)}</td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.proveedores = DataTableHelper.inicializar('#tabla_transporte_proveedores', {
                    titulo: 'Reporte Transporte - Proveedores',
                    columnasExport: [0, 1, 2, 3, 4, 5],
                    orden: [[0, 'asc']],
                });
            }
        }

        // 4. Repuestos
        const tbodyRep = document.getElementById('tbodyRepuestos');
        if (tbodyRep) {
            destruirTabla('repuestos');
            tbodyRep.innerHTML = datosRespuesta.repuestos.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.nombre)}</strong></td>
                    <td>${ReportesComunes.escapar(item.proveedor || 'Sin proveedor')}</td>
                    <td><span class="badge ${item.cantidad < 5 ? 'bg-danger' : 'bg-primary'}">${item.cantidad}</span></td>
                    <td><span class="badge ${item.estatus === 'Disponible' ? 'bg-success' : 'bg-danger'}">${ReportesComunes.escapar(item.estatus)}</span></td>
                    <td>${Formato.fecha(item.fecha_creacion)}</td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.repuestos = DataTableHelper.inicializar('#tabla_transporte_repuestos', {
                    titulo: 'Reporte Transporte - Repuestos',
                    columnasExport: [0, 1, 2, 3, 4],
                    orden: [[0, 'asc']],
                });
            }
        }

        // 5. Asignaciones
        const tbodyAsig = document.getElementById('tbodyAsignaciones');
        if (tbodyAsig) {
            destruirTabla('asignaciones');
            tbodyAsig.innerHTML = datosRespuesta.asignaciones.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.nombre_ruta)}</strong></td>
                    <td>${ReportesComunes.escapar(item.placa)} (${ReportesComunes.escapar(item.modelo)})</td>
                    <td>${ReportesComunes.escapar(item.chofer)}</td>
                    <td>${ReportesComunes.escapar(item.cedula_chofer)}</td>
                    <td>${Formato.fecha(item.fecha_asignacion)}</td>
                    <td><span class="badge ${item.estatus === 'Activa' ? 'bg-success' : 'bg-secondary'}">${ReportesComunes.escapar(item.estatus)}</span></td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.asignaciones = DataTableHelper.inicializar('#tabla_transporte_asignaciones', {
                    titulo: 'Reporte Transporte - Asignaciones',
                    columnasExport: [0, 1, 2, 3, 4, 5],
                    orden: [[4, 'desc']],
                });
            }
        }

        // 6. Mantenimientos
        const tbodyMant = document.getElementById('tbodyMantenimientos');
        if (tbodyMant) {
            destruirTabla('mantenimientos');
            tbodyMant.innerHTML = datosRespuesta.mantenimientos.map(item => `
                <tr>
                    <td><strong>${ReportesComunes.escapar(item.placa)}</strong> (${ReportesComunes.escapar(item.modelo)})</td>
                    <td><span class="badge bg-warning text-dark">${ReportesComunes.escapar(item.tipo)}</span></td>
                    <td>${Formato.fecha(item.fecha)}</td>
                    <td>${ReportesComunes.escapar(item.descripcion)}</td>
                </tr>
            `).join('');
            if (typeof DataTableHelper !== 'undefined') {
                tablas.mantenimientos = DataTableHelper.inicializar('#tabla_transporte_mantenimientos', {
                    titulo: 'Reporte Transporte - Mantenimientos',
                    columnasExport: [0, 1, 2, 3],
                    orden: [[2, 'desc']],
                });
            }
        }
    }

    function pintarGraficos() {
        // Grafico 1: Estado de vehiculos
        const estadosVeh = ReportesComunes.contarPor(datosRespuesta.vehiculos, v => v.estado || 'Desconocido');
        chartEstado = ReportesComunes.grafico(
            'chartTransEstado', chartEstado, Object.keys(estadosVeh), Object.values(estadosVeh),
            ['#1cc88a', '#e74a3b', '#f6c23e'], 'Estado de Vehículos', tipoChart
        );

        // Grafico 2: Total por seccion
        const resumenSecciones = {
            'Vehículos': datosRespuesta.vehiculos.length,
            'Rutas': datosRespuesta.rutas.length,
            'Proveedores': datosRespuesta.proveedores.length,
            'Repuestos': datosRespuesta.repuestos.length,
            'Asignaciones': datosRespuesta.asignaciones.length,
            'Mantenimientos': datosRespuesta.mantenimientos.length,
        };
        chartSeccion = ReportesComunes.grafico(
            'chartTransSeccion', chartSeccion, Object.keys(resumenSecciones), Object.values(resumenSecciones),
            ReportesComunes.paleta(6), 'Registros por Sección', tipoChart
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
