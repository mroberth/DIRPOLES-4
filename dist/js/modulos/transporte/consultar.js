/**
 * dist/js/modulos/transporte/consultar.js
 * ---------------------------------------------------------------
 * Lógica de las tablas DataTables para el módulo de Transporte (Rutas, Vehículos, Proveedores, Repuestos, Asignaciones, Mantenimientos).
 */
window.TransporteConsultar = (function () {
    'use strict';

    let dtRutas, dtVehiculos, dtProveedores, dtRepuestos, dtAsignaciones, dtMantenimientos;

    // Permisos visibles (se cargan en DOMContentLoaded; visibles para TODAS las
    // cargarX, que viven en este mismo scope del IIFE).
    let puedeCrear = false, puedeEditar = false, puedeEliminar = false;

    function escapar(txt) {
        if (txt === null || txt === undefined) return '';
        const div = document.createElement('div');
        div.textContent = txt;
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function () {
        puedeCrear    = window.TRANSPORTE_PUEDE_CREAR    === true;
        puedeEditar   = window.TRANSPORTE_PUEDE_EDITAR   === true;
        puedeEliminar = window.TRANSPORTE_PUEDE_ELIMINAR === true;

        const tbodyR = document.getElementById('tbodyRutas');
        if (!tbodyR) return;

        // Botón Recargar
        const btnRecargar = document.getElementById('btn-recargar');
        if (btnRecargar) {
            btnRecargar.addEventListener('click', recargarTodo);
        }

        // Kardex de Repuestos Modal
        const btnHistorial = document.getElementById('btn-historial-repuestos');
        if (btnHistorial) {
            btnHistorial.addEventListener('click', cargarHistorialRepuestos);
        }

        // Delegación de eventos en las tablas para acciones (Editar / Eliminar / Entrada / Salida / Detalle)
        configurarDelegacionEventos();

        // Cargar primera pestaña activa
        cargarRutas();
        cargarVehiculos();
        cargarProveedores();
        cargarRepuestos();
        cargarAsignaciones();
        cargarMantenimientos();
    });

    function recargarTodo() {
        cargarRutas();
        cargarVehiculos();
        cargarProveedores();
        cargarRepuestos();
        cargarAsignaciones();
        cargarMantenimientos();
        if (window.TransporteStats) window.TransporteStats.cargar();
    }

    // ==================== 1. TABLA RUTAS ====================

    async function cargarRutas() {
        const tbody = document.getElementById('tbodyRutas');
        if (!tbody) return;

        try {
            const rutas = await apiFetch(BASE_URL + 'api/transporte/rutas/listar');
            if (dtRutas) dtRutas.destroy();

            tbody.innerHTML = rutas.map(r => `
                <tr>
                    <td>
                        <strong class="text-primary">${escapar(r.nombre_ruta)}</strong>
                        ${r.trayectoria ? `<div class="small text-muted text-truncate" style="max-width: 250px;">${escapar(r.trayectoria)}</div>` : ''}
                    </td>
                    <td><span class="badge bg-secondary">${escapar(r.tipo_ruta)}</span></td>
                    <td>
                        <small><i class="fas fa-clock text-info me-1"></i>Salida: ${escapar(r.horario_salida || 'N/A')}</small><br>
                        <small><i class="fas fa-clock text-success me-1"></i>Llegada: ${escapar(r.horario_llegada || 'N/A')}</small>
                    </td>
                    <td>
                        <small><strong>De:</strong> ${escapar(r.punto_partida || 'N/A')}</small><br>
                        <small><strong>A:</strong> ${escapar(r.punto_destino || 'N/A')}</small>
                    </td>
                    <td>
                        <span class="badge ${r.estatus === 'Activa' ? 'bg-success' : 'bg-danger'}">${escapar(r.estatus)}</span>
                    </td>
                    <td class="text-center">
                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-primary btn-edit-ruta me-1" data-id="${r.id_ruta}" title="Editar Ruta">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-ruta" data-id="${r.id_ruta}" title="Eliminar Ruta">
                            <i class="fas fa-trash-alt"></i>
                        </button>` : ''}
                    </td>
                </tr>
            `).join('');

            dtRutas = DataTableHelper.inicializar('#tablaRutas', {
                titulo: 'Rutas de Transporte',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar rutas:', err);
        }
    }

    // ==================== 2. TABLA VEHÍCULOS ====================

    async function cargarVehiculos() {
        const tbody = document.getElementById('tbodyVehiculos');
        if (!tbody) return;

        try {
            const vehiculos = await apiFetch(BASE_URL + 'api/transporte/vehiculos/listar');
            if (dtVehiculos) dtVehiculos.destroy();

            tbody.innerHTML = vehiculos.map(v => {
                let badgeClass = 'bg-success';
                if (v.estado === 'Inactivo') badgeClass = 'bg-secondary';
                if (v.estado === 'Mantenimiento') badgeClass = 'bg-warning text-dark';

                return `
                    <tr>
                        <td><strong class="text-dark">${escapar(v.placa)}</strong></td>
                        <td>${escapar(v.modelo || 'Sin modelo registrado')}</td>
                        <td><span class="badge bg-info text-dark">${escapar(v.tipo)}</span></td>
                        <td data-order="${escapar(v.fecha_adquisicion || '')}">${v.fecha_adquisicion ? Formato.fecha(v.fecha_adquisicion) : 'N/A'}</td>
                        <td><span class="badge ${badgeClass}">${escapar(v.estado)}</span></td>
                        <td class="text-center">
                            ${puedeEditar ? `<button class="btn btn-sm btn-outline-primary btn-edit-vehiculo me-1" data-id="${v.id_vehiculo}" title="Editar Vehículo">
                                <i class="fas fa-edit"></i>
                            </button>` : ''}
                            ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-vehiculo" data-id="${v.id_vehiculo}" title="Eliminar Vehículo">
                                <i class="fas fa-trash-alt"></i>
                            </button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');

            dtVehiculos = DataTableHelper.inicializar('#tablaVehiculos', {
                titulo: 'Vehículos de Transporte',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar vehículos:', err);
        }
    }

    // ==================== 3. TABLA PROVEEDORES ====================

    async function cargarProveedores() {
        const tbody = document.getElementById('tbodyProveedores');
        if (!tbody) return;

        try {
            const proveedores = await apiFetch(BASE_URL + 'api/transporte/proveedores/listar');
            if (dtProveedores) dtProveedores.destroy();

            tbody.innerHTML = proveedores.map(p => `
                <tr>
                    <td><span class="badge bg-secondary me-1">${escapar(p.tipo_documento)}</span>${escapar(p.num_documento)}</td>
                    <td><strong class="text-primary">${escapar(p.nombre)}</strong><div class="small text-muted">${escapar(p.direccion)}</div></td>
                    <td>${escapar(p.telefono)}</td>
                    <td>${escapar(p.correo)}</td>
                    <td><span class="badge ${p.estatus === 'Activo' ? 'bg-success' : 'bg-secondary'}">${escapar(p.estatus)}</span></td>
                    <td class="text-center">
                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-primary btn-edit-proveedor me-1" data-id="${p.id_proveedor}" title="Editar Proveedor">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-proveedor" data-id="${p.id_proveedor}" title="Eliminar Proveedor">
                            <i class="fas fa-trash-alt"></i>
                        </button>` : ''}
                    </td>
                </tr>
            `).join('');

            dtProveedores = DataTableHelper.inicializar('#tablaProveedores', {
                titulo: 'Proveedores de Transporte',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar proveedores:', err);
        }
    }

    // ==================== 4. TABLA REPUESTOS ====================

    async function cargarRepuestos() {
        const tbody = document.getElementById('tbodyRepuestos');
        if (!tbody) return;

        try {
            const repuestos = await apiFetch(BASE_URL + 'api/transporte/repuestos/listar');
            if (dtRepuestos) dtRepuestos.destroy();

            tbody.innerHTML = repuestos.map(r => {
                let badgeClass = r.estatus === 'Disponible' ? 'bg-success' : (r.cantidad <= 0 ? 'bg-danger' : 'bg-warning');
                return `
                    <tr>
                        <td><strong class="text-dark">${escapar(r.nombre)}</strong></td>
                        <td>${escapar(r.descripcion || 'Sin descripción')}</td>
                        <td>${escapar(r.nombre_proveedor || 'Sin proveedor')}</td>
                        <td><strong class="h6 mb-0 text-primary">${r.cantidad}</strong> ${parseInt(r.cantidad, 10) === 1 ? 'unidad' : 'unidades'}</td>
                        <td><span class="badge ${badgeClass}">${escapar(r.estatus || (r.cantidad > 0 ? 'Disponible' : 'Agotado'))}</span></td>
                        <td class="text-center">
                            ${puedeCrear ? `<button class="btn btn-sm btn-outline-success btn-ent-repuesto me-1" data-id="${r.id_repuesto}" title="Entrada de Stock">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning btn-sal-repuesto me-1" data-id="${r.id_repuesto}" title="Salida de Stock">
                                <i class="fas fa-minus-circle"></i>
                            </button>` : ''}
                            ${puedeEditar ? `<button class="btn btn-sm btn-outline-primary btn-edit-repuesto me-1" data-id="${r.id_repuesto}" title="Editar Repuesto">
                                <i class="fas fa-edit"></i>
                            </button>` : ''}
                            ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-repuesto" data-id="${r.id_repuesto}" title="Eliminar Repuesto">
                                <i class="fas fa-trash-alt"></i>
                            </button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');

            dtRepuestos = DataTableHelper.inicializar('#tablaRepuestos', {
                titulo: 'Inventario de Repuestos',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar repuestos:', err);
        }
    }

    async function cargarHistorialRepuestos() {
        try {
            const historial = await apiFetch(BASE_URL + 'api/transporte/repuestos/historial');
            const tbody = document.getElementById('tbodyHistorialRepuestos');
            if (tbody) {
                tbody.innerHTML = historial.map(k => `
                    <tr>
                        <td>${escapar(k.nombre_repuesto)}</td>
                        <td>
                            <span class="badge ${k.tipo_movimiento === 'Entrada' ? 'bg-success' : (k.tipo_movimiento === 'Salida' ? 'bg-warning text-dark' : 'bg-info')}">
                                ${escapar(k.tipo_movimiento)}
                            </span>
                        </td>
                        <td><strong>${k.cantidad}</strong></td>
                        <td>${escapar(k.razon_movimiento)}</td>
                        <td>${escapar((k.nombres || '') + ' ' + (k.apellidos || ''))}</td>
                        <td>${Formato.fechaHora(k.fecha_movimiento)}</td>
                    </tr>
                `).join('');
            }
            const modalH = new bootstrap.Modal(document.getElementById('modalHistorialRepuestos'));
            modalH.show();
        } catch (err) {
            AlertManager.warning('Error', 'No se pudo cargar el Kardex de repuestos.');
        }
    }

    // ==================== 5. TABLA ASIGNACIONES ====================

    async function cargarAsignaciones() {
        const tbody = document.getElementById('tbodyAsignaciones');
        if (!tbody) return;

        try {
            const asignaciones = await apiFetch(BASE_URL + 'api/transporte/asignaciones/listar');
            if (dtAsignaciones) dtAsignaciones.destroy();

            tbody.innerHTML = asignaciones.map(a => `
                <tr>
                    <td><strong class="text-primary">${escapar(a.nombre_ruta)}</strong></td>
                    <td><span class="badge bg-secondary">${escapar(a.placa)}</span> ${escapar(a.modelo || '')}</td>
                    <td><i class="fas fa-id-badge text-info me-1"></i>${escapar((a.nombres_chofer || '') + ' ' + (a.apellidos_chofer || 'Chofer no asignado'))}</td>
                    <td data-order="${escapar(a.fecha_asignacion)}">${Formato.fecha(a.fecha_asignacion)}</td>
                    <td><span class="badge ${a.estatus === 'Activa' ? 'bg-success' : 'bg-secondary'}">${escapar(a.estatus)}</span></td>
                    <td class="text-center">
                        ${puedeEditar ? `<button class="btn btn-sm btn-outline-primary btn-edit-asig me-1" data-id="${a.id_asignacion}" title="Editar Asignación">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                        ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-asig" data-id="${a.id_asignacion}" title="Eliminar Asignación">
                            <i class="fas fa-trash-alt"></i>
                        </button>` : ''}
                    </td>
                </tr>
            `).join('');

            dtAsignaciones = DataTableHelper.inicializar('#tablaAsignaciones', {
                titulo: 'Asignaciones de Choferes y Rutas',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar asignaciones:', err);
        }
    }

    // ==================== 6. TABLA MANTENIMIENTOS ====================

    async function cargarMantenimientos() {
        const tbody = document.getElementById('tbodyMantenimientos');
        if (!tbody) return;

        try {
            const mantenimientos = await apiFetch(BASE_URL + 'api/transporte/mantenimientos/listar');
            if (dtMantenimientos) dtMantenimientos.destroy();

            tbody.innerHTML = mantenimientos.map(m => {
                const repList = m.repuestos && m.repuestos.length > 0
                    ? m.repuestos.map(r => `<span class="badge bg-light text-dark border me-1">${escapar(r.nombre_repuesto)} (${r.cantidad})</span>`).join('')
                    : '<span class="text-muted small">Sin repuestos</span>';

                return `
                    <tr>
                        <td><strong class="text-dark">${escapar(m.placa)}</strong> <small class="text-muted">(${escapar(m.tipo_vehiculo)})</small></td>
                        <td><span class="badge ${m.tipo === 'Preventivo' ? 'bg-info' : 'bg-warning'}">${escapar(m.tipo)}</span></td>
                        <td data-order="${escapar(m.fecha)}">${Formato.fecha(m.fecha)}</td>
                        <td><div class="text-truncate" style="max-width: 200px;">${escapar(m.descripcion || 'Sin descripción')}</div></td>
                        <td>${repList}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-info btn-det-mant me-1" data-id="${m.id_mantenimiento}" title="Ver Detalle Mantenimiento">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${puedeEliminar ? `<button class="btn btn-sm btn-outline-danger btn-del-mant" data-id="${m.id_mantenimiento}" title="Eliminar Mantenimiento">
                                <i class="fas fa-trash-alt"></i>
                            </button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');

            dtMantenimientos = DataTableHelper.inicializar('#tablaMantenimientos', {
                titulo: 'Mantenimientos de Vehículos',
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (err) {
            console.error('Error al cargar mantenimientos:', err);
        }
    }

    // ==================== DELEGACIÓN DE EVENTOS EN LAS TABLAS ====================

    function configurarDelegacionEventos() {
        document.addEventListener('click', async function (ev) {
            // Rutas
            const btnEditR = ev.target.closest('.btn-edit-ruta');
            if (btnEditR && window.TransporteEditar) {
                window.TransporteEditar.abrirRuta(btnEditR.getAttribute('data-id'));
                return;
            }
            const btnDelR = ev.target.closest('.btn-del-ruta');
            if (btnDelR) {
                eliminarEntidad('rutas', btnDelR.getAttribute('data-id'), 'Ruta', cargarRutas);
                return;
            }

            // Vehículos
            const btnEditV = ev.target.closest('.btn-edit-vehiculo');
            if (btnEditV && window.TransporteEditar) {
                window.TransporteEditar.abrirVehiculo(btnEditV.getAttribute('data-id'));
                return;
            }
            const btnDelV = ev.target.closest('.btn-del-vehiculo');
            if (btnDelV) {
                eliminarEntidad('vehiculos', btnDelV.getAttribute('data-id'), 'Vehículo', cargarVehiculos);
                return;
            }

            // Proveedores
            const btnEditP = ev.target.closest('.btn-edit-proveedor');
            if (btnEditP && window.TransporteEditar) {
                window.TransporteEditar.abrirProveedor(btnEditP.getAttribute('data-id'));
                return;
            }
            const btnDelP = ev.target.closest('.btn-del-proveedor');
            if (btnDelP) {
                eliminarEntidad('proveedores', btnDelP.getAttribute('data-id'), 'Proveedor', cargarProveedores);
                return;
            }

            // Repuestos
            const btnEntRep = ev.target.closest('.btn-ent-repuesto');
            if (btnEntRep && window.TransporteEditar) {
                window.TransporteEditar.abrirEntradaRepuesto(btnEntRep.getAttribute('data-id'));
                return;
            }
            const btnSalRep = ev.target.closest('.btn-sal-repuesto');
            if (btnSalRep && window.TransporteEditar) {
                window.TransporteEditar.abrirSalidaRepuesto(btnSalRep.getAttribute('data-id'));
                return;
            }
            const btnEditRep = ev.target.closest('.btn-edit-repuesto');
            if (btnEditRep && window.TransporteEditar) {
                window.TransporteEditar.abrirRepuesto(btnEditRep.getAttribute('data-id'));
                return;
            }
            const btnDelRep = ev.target.closest('.btn-del-repuesto');
            if (btnDelRep) {
                eliminarEntidad('repuestos', btnDelRep.getAttribute('data-id'), 'Repuesto', cargarRepuestos);
                return;
            }

            // Asignaciones
            const btnEditAsig = ev.target.closest('.btn-edit-asig');
            if (btnEditAsig && window.TransporteEditar) {
                window.TransporteEditar.abrirAsignacion(btnEditAsig.getAttribute('data-id'));
                return;
            }
            const btnDelAsig = ev.target.closest('.btn-del-asig');
            if (btnDelAsig) {
                eliminarEntidad('asignaciones', btnDelAsig.getAttribute('data-id'), 'Asignación', cargarAsignaciones);
                return;
            }

            // Mantenimientos
            const btnDetMant = ev.target.closest('.btn-det-mant');
            if (btnDetMant && window.TransporteEditar) {
                window.TransporteEditar.abrirDetalleMantenimiento(btnDetMant.getAttribute('data-id'));
                return;
            }
            const btnDelMant = ev.target.closest('.btn-del-mant');
            if (btnDelMant) {
                eliminarEntidad('mantenimientos', btnDelMant.getAttribute('data-id'), 'Mantenimiento', cargarMantenimientos);
                return;
            }
        });
    }

    async function eliminarEntidad(endpointSub, id, nombreEntidad, fnCallback) {
        const conf = await AlertManager.confirm(
            `¿Eliminar ${nombreEntidad}?`,
            `Esta acción eliminará el registro de ${nombreEntidad.toLowerCase()} del sistema.`
        );

        if (!conf) return;

        try {
            const payload = {};
            if (endpointSub === 'rutas') payload.id_ruta = parseInt(id, 10);
            if (endpointSub === 'vehiculos') payload.id_vehiculo = parseInt(id, 10);
            if (endpointSub === 'proveedores') payload.id_proveedor = parseInt(id, 10);
            if (endpointSub === 'repuestos') payload.id_repuesto = parseInt(id, 10);
            if (endpointSub === 'asignaciones') payload.id_asignacion = parseInt(id, 10);
            if (endpointSub === 'mantenimientos') payload.id_mantenimiento = parseInt(id, 10);

            await apiFetch(BASE_URL + `api/transporte/${endpointSub}/eliminar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });

            AlertManager.success('Eliminado', `${nombreEntidad} eliminado correctamente.`);
            if (fnCallback) fnCallback();
            if (window.TransporteStats) window.TransporteStats.cargar();
        } catch (err) {
            if (err.codigo === 'IN_USE') {
                AlertManager.warning('No se puede eliminar', err.mensaje);
            } else {
                AlertManager.error('Error', err.mensaje || `No se pudo eliminar el registro.`);
            }
        }
    }

    return {
        recargar: recargarTodo,
        cargarRutas,
        cargarVehiculos,
        cargarProveedores,
        cargarRepuestos,
        cargarAsignaciones,
        cargarMantenimientos,
    };
})();
