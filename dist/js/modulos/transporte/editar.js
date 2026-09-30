/**
 * dist/js/modulos/transporte/editar.js
 * ---------------------------------------------------------------
 * Lógica de modales de edición y movimientos de stock para Transporte.
 */
window.TransporteEditar = (function () {
    'use strict';

    function escapar(txt) {
        if (txt === null || txt === undefined) return '';
        const div = document.createElement('div');
        div.textContent = txt;
        return div.innerHTML;
    }

    // Instancias de Modales Bootstrap
    let modalRuta, modalVehiculo, modalProveedor, modalRepuesto, modalEntrada, modalSalida, modalAsignacion, modalDetalleMant;

    // Opciones mutables: idExcluir cambia en cada apertura (patrón empleado/editar.js).
    const opcionesVehiculo = { idExcluir: 0 };
    const opcionesProveedor = { idExcluir: 0 };

    document.addEventListener('DOMContentLoaded', function () {
        const elR = document.getElementById('modalEditarRuta');
        if (elR) modalRuta = new bootstrap.Modal(elR);

        const elV = document.getElementById('modalEditarVehiculo');
        if (elV) modalVehiculo = new bootstrap.Modal(elV);

        const elP = document.getElementById('modalEditarProveedor');
        if (elP) modalProveedor = new bootstrap.Modal(elP);

        const elRep = document.getElementById('modalEditarRepuesto');
        if (elRep) modalRepuesto = new bootstrap.Modal(elRep);

        const elEnt = document.getElementById('modalEntradaRepuesto');
        if (elEnt) modalEntrada = new bootstrap.Modal(elEnt);

        const elSal = document.getElementById('modalSalidaRepuesto');
        if (elSal) modalSalida = new bootstrap.Modal(elSal);

        const elAsig = document.getElementById('modalEditarAsignacion');
        if (elAsig) modalAsignacion = new bootstrap.Modal(elAsig);

        const elMant = document.getElementById('modalDetalleMantenimiento');
        if (elMant) modalDetalleMant = new bootstrap.Modal(elMant);

        // Submits de formularios de modales
        configurarSubmits();
    });

    // ------------------------------------------------------------------
    // ABRIR MODALES
    // ------------------------------------------------------------------

    async function abrirRuta(id) {
        try {
            const r = await apiFetch(BASE_URL + 'api/transporte/rutas/obtener/' + id);
            document.getElementById('edit_id_ruta').value = r.id_ruta;
            document.getElementById('edit_nombre_ruta').value = r.nombre_ruta;
            document.getElementById('edit_tipo_ruta').value = r.tipo_ruta;
            document.getElementById('edit_horario_salida').value = r.horario_salida || '';
            document.getElementById('edit_horario_llegada').value = r.horario_llegada || '';
            document.getElementById('edit_punto_partida').value = r.punto_partida || '';
            document.getElementById('edit_punto_destino').value = r.punto_destino || '';
            document.getElementById('edit_trayectoria').value = r.trayectoria || '';
            document.getElementById('edit_estatus_ruta').value = r.estatus;
            document.getElementById('lblEditRutaId').textContent = '#' + r.id_ruta;

            if (window.initSelect2) window.initSelect2(document.getElementById('modalEditarRuta'));
            modalRuta.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar la ruta.');
        }
    }

    async function abrirVehiculo(id) {
        try {
            const v = await apiFetch(BASE_URL + 'api/transporte/vehiculos/obtener/' + id);
            document.getElementById('edit_id_vehiculo').value = v.id_vehiculo;
            document.getElementById('edit_placa').value = v.placa;
            document.getElementById('edit_modelo_vehiculo').value = v.modelo || '';
            document.getElementById('edit_tipo_vehiculo').value = v.tipo;
            document.getElementById('edit_fecha_adquisicion').value = v.fecha_adquisicion || '';
            document.getElementById('edit_estado_vehiculo').value = v.estado;
            document.getElementById('lblEditVehiculoId').textContent = '#' + v.id_vehiculo;
            opcionesVehiculo.idExcluir = Number(v.id_vehiculo) || 0;

            if (window.initSelect2) window.initSelect2(document.getElementById('modalEditarVehiculo'));
            modalVehiculo.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el vehículo.');
        }
    }

    async function abrirProveedor(id) {
        try {
            const p = await apiFetch(BASE_URL + 'api/transporte/proveedores/obtener/' + id);
            document.getElementById('edit_id_proveedor').value = p.id_proveedor;
            document.getElementById('edit_tipo_documento').value = p.tipo_documento;
            document.getElementById('edit_num_documento').value = p.num_documento;
            document.getElementById('edit_nombre_proveedor').value = p.nombre;
            document.getElementById('edit_telefono_proveedor').value = p.telefono;
            document.getElementById('edit_correo_proveedor').value = p.correo;
            document.getElementById('edit_direccion_proveedor').value = p.direccion;
            document.getElementById('edit_estatus_proveedor').value = p.estatus;
            document.getElementById('lblEditProveedorId').textContent = '#' + p.id_proveedor;
            opcionesProveedor.idExcluir = Number(p.id_proveedor) || 0;

            modalProveedor.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el proveedor.');
        }
    }

    async function abrirRepuesto(id) {
        try {
            const rep = await apiFetch(BASE_URL + 'api/transporte/repuestos/obtener/' + id);
            document.getElementById('edit_id_repuesto').value = rep.id_repuesto;
            document.getElementById('edit_nombre_repuesto').value = rep.nombre;
            document.getElementById('edit_descripcion_repuesto').value = rep.descripcion || '';
            document.getElementById('lblEditRepuestoId').textContent = '#' + rep.id_repuesto;

            // Cargar proveedores en select
            const listaProv = await apiFetch(BASE_URL + 'api/transporte/proveedores/listar');
            const selProv = document.getElementById('edit_id_proveedor_repuesto');
            if (selProv) {
                selProv.innerHTML = '<option value="">Sin proveedor asignado</option>' +
                    listaProv.map(p => `<option value="${escapar(p.id_proveedor)}">${escapar(p.nombre)}</option>`).join('');
                selProv.value = rep.id_proveedor || '';
            }

            if (window.initSelect2) window.initSelect2(document.getElementById('modalEditarRepuesto'));
            modalRepuesto.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el repuesto.');
        }
    }

    async function abrirEntradaRepuesto(id) {
        try {
            const rep = await apiFetch(BASE_URL + 'api/transporte/repuestos/obtener/' + id);
            document.getElementById('entrada_id_repuesto').value = rep.id_repuesto;
            document.getElementById('entrada_nombre_repuesto').value = rep.nombre + ' (Stock actual: ' + rep.cantidad + ')';
            document.getElementById('entrada_cantidad').value = '';
            document.getElementById('entrada_razon').value = '';
            modalEntrada.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el repuesto.');
        }
    }

    async function abrirSalidaRepuesto(id) {
        try {
            const rep = await apiFetch(BASE_URL + 'api/transporte/repuestos/obtener/' + id);
            if (parseInt(rep.cantidad, 10) <= 0) {
                AlertManager.warning('Sin stock', 'Este repuesto no tiene unidades para retirar.');
                return;
            }
            document.getElementById('salida_id_repuesto').value = rep.id_repuesto;
            document.getElementById('salida_nombre_repuesto').value = rep.nombre + ' (Stock disponible: ' + rep.cantidad + ')';
            document.getElementById('salida_cantidad').value = '';
            document.getElementById('salida_razon').value = '';
            modalSalida.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el repuesto.');
        }
    }

    async function abrirAsignacion(id) {
        try {
            const a = await apiFetch(BASE_URL + 'api/transporte/asignaciones/obtener/' + id);
            const opc = await apiFetch(BASE_URL + 'api/transporte/asignaciones/opciones');

            document.getElementById('edit_id_asignacion').value = a.id_asignacion;
            document.getElementById('edit_fecha_asignacion').value = a.fecha_asignacion;
            document.getElementById('edit_estatus_asignacion').value = a.estatus;
            document.getElementById('lblEditAsigId').textContent = '#' + a.id_asignacion;

            const selR = document.getElementById('edit_id_ruta_asig');
            const selV = document.getElementById('edit_id_vehiculo_asig');
            const selE = document.getElementById('edit_id_empleado_asig');

            if (selR) {
                selR.innerHTML = opc.rutas.map(r => `<option value="${escapar(r.id_ruta)}">${escapar(r.nombre_ruta)}</option>`).join('');
                selR.value = a.id_ruta;
            }
            if (selV) {
                selV.innerHTML = opc.vehiculos.map(v => `<option value="${escapar(v.id_vehiculo)}">${escapar(v.placa)} - ${escapar(v.modelo || v.tipo)}</option>`).join('');
                selV.value = a.id_vehiculo;
            }
            if (selE) {
                selE.innerHTML = opc.empleados.map(e => `<option value="${escapar(e.id_empleado)}">${escapar(e.nombres)} ${escapar(e.apellidos)}</option>`).join('');
                selE.value = a.id_empleado;
                // Dato heredado: si el asignado ya no figura como chofer activo,
                // se conserva como opción extra para no romper el modal (el
                // backend rechaza guardarlo si sigue sin ser chofer).
                if (!selE.value && a.id_empleado) {
                    const extra = document.createElement('option');
                    extra.value = a.id_empleado;
                    extra.textContent = ((a.nombres_chofer || '') + ' ' + (a.apellidos_chofer || '')).trim() || ('Empleado #' + a.id_empleado);
                    selE.appendChild(extra);
                    selE.value = a.id_empleado;
                }
            }

            if (window.initSelect2) window.initSelect2(document.getElementById('modalEditarAsignacion'));
            modalAsignacion.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar la asignación.');
        }
    }

    async function abrirDetalleMantenimiento(id) {
        try {
            const m = await apiFetch(BASE_URL + 'api/transporte/mantenimientos/obtener/' + id);
            const body = document.getElementById('bodyDetalleMantenimiento');
            document.getElementById('lblDetalleMantId').textContent = '#' + m.id_mantenimiento;

            let repuestosHtml = '<p class="text-muted small">No se utilizaron repuestos en este servicio.</p>';
            if (m.repuestos && m.repuestos.length > 0) {
                repuestosHtml = `
                    <table class="table table-sm table-bordered mt-2">
                        <thead class="table-light">
                            <tr><th>Repuesto</th><th class="text-end">Cantidad Consumida</th></tr>
                        </thead>
                        <tbody>
                            ${m.repuestos.map(r => `<tr><td>${escapar(r.nombre_repuesto)}</td><td class="text-end font-weight-bold">${escapar(r.cantidad)}</td></tr>`).join('')}
                        </tbody>
                    </table>
                `;
            }

            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Vehículo:</strong> ${escapar(m.placa)} (${escapar(m.modelo || m.tipo_vehiculo)})
                    </div>
                    <div class="col-md-3">
                        <strong>Tipo:</strong> <span class="badge ${m.tipo === 'Preventivo' ? 'bg-info' : 'bg-warning'}">${escapar(m.tipo)}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong> ${escapar(Formato.fecha(m.fecha))}
                    </div>
                    <div class="col-12">
                        <strong>Descripción del Servicio / Falla:</strong>
                        <p class="border rounded p-2 bg-light text-dark mt-1">${escapar(m.descripcion || 'Sin descripción adicional.')}</p>
                    </div>
                    <div class="col-12 mt-2">
                        <h6 class="font-weight-bold text-gray-800"><i class="fas fa-gears me-1"></i>Repuestos Utilizados</h6>
                        ${repuestosHtml}
                    </div>
                </div>
            `;

            modalDetalleMant.show();
        } catch (err) {
            AlertManager.warning('Error', err.mensaje || 'No se pudo cargar el mantenimiento.');
        }
    }

    // ------------------------------------------------------------------
    // SUBMITS DE FORMULARIOS MODALES
    // ------------------------------------------------------------------

    function configurarSubmits() {
        // Edit Ruta
        const fRuta = document.getElementById('form-editar-ruta');
        if (fRuta) {
            const val = window.TransporteValidaciones.configurar(fRuta);
            fRuta.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                if (!(await val.validarTodo())) return;
                const payload = Object.fromEntries(new FormData(fRuta));
                try {
                    await apiFetch(BASE_URL + 'api/transporte/rutas/actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalRuta.hide();
                    AlertManager.success('Actualizado', 'La ruta fue actualizada con éxito.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarRutas();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Edit Vehículo
        const fVeh = document.getElementById('form-editar-vehiculo');
        if (fVeh) {
            const val = window.TransporteValidaciones.configurar(fVeh, opcionesVehiculo);
            fVeh.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                if (!(await val.validarTodo())) return;
                const payload = Object.fromEntries(new FormData(fVeh));
                try {
                    await apiFetch(BASE_URL + 'api/transporte/vehiculos/actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalVehiculo.hide();
                    AlertManager.success('Actualizado', 'El vehículo fue actualizado con éxito.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarVehiculos();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Edit Proveedor
        const fProv = document.getElementById('form-editar-proveedor');
        if (fProv) {
            const val = window.TransporteValidaciones.configurar(fProv, opcionesProveedor);
            fProv.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                if (!(await val.validarTodo())) return;
                const payload = Object.fromEntries(new FormData(fProv));
                try {
                    await apiFetch(BASE_URL + 'api/transporte/proveedores/actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalProveedor.hide();
                    AlertManager.success('Actualizado', 'El proveedor fue actualizado con éxito.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarProveedores();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Edit Repuesto
        const fRep = document.getElementById('form-editar-repuesto');
        if (fRep) {
            const val = window.TransporteValidaciones.configurar(fRep);
            fRep.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                if (!(await val.validarTodo())) return;
                const payload = Object.fromEntries(new FormData(fRep));
                payload.id_proveedor = parseInt(payload.id_proveedor, 10) || 0;
                try {
                    await apiFetch(BASE_URL + 'api/transporte/repuestos/actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalRepuesto.hide();
                    AlertManager.success('Actualizado', 'El repuesto fue actualizado con éxito.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarRepuestos();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Entrada Stock
        const fEnt = document.getElementById('form-entrada-repuesto');
        if (fEnt) {
            fEnt.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                const payload = {
                    id_repuesto: parseInt(document.getElementById('entrada_id_repuesto').value, 10),
                    cantidad: parseInt(document.getElementById('entrada_cantidad').value, 10),
                    razon: document.getElementById('entrada_razon').value.trim(),
                };
                if (!payload.cantidad || payload.cantidad <= 0 || !payload.razon) {
                    AlertManager.warning('Atención', 'Ingresa una cantidad mayor a 0 y la razón de entrada.');
                    return;
                }
                try {
                    await apiFetch(BASE_URL + 'api/transporte/repuestos/entrada', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalEntrada.hide();
                    AlertManager.success('Entrada Registrada', 'Stock actualizado correctamente.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarRepuestos();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Salida Stock
        const fSal = document.getElementById('form-salida-repuesto');
        if (fSal) {
            fSal.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                const payload = {
                    id_repuesto: parseInt(document.getElementById('salida_id_repuesto').value, 10),
                    cantidad: parseInt(document.getElementById('salida_cantidad').value, 10),
                    razon: document.getElementById('salida_razon').value.trim(),
                };
                if (!payload.cantidad || payload.cantidad <= 0 || !payload.razon) {
                    AlertManager.warning('Atención', 'Ingresa una cantidad mayor a 0 y la razón de salida.');
                    return;
                }
                try {
                    await apiFetch(BASE_URL + 'api/transporte/repuestos/salida', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalSalida.hide();
                    AlertManager.success('Salida Registrada', 'Stock actualizado correctamente.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarRepuestos();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }

        // Edit Asignación
        const fAsig = document.getElementById('form-editar-asignacion');
        if (fAsig) {
            const val = window.TransporteValidaciones.configurar(fAsig);
            fAsig.addEventListener('submit', async function (ev) {
                ev.preventDefault();
                if (!(await val.validarTodo())) return;
                const payload = Object.fromEntries(new FormData(fAsig));
                payload.id_ruta = parseInt(payload.id_ruta, 10);
                payload.id_vehiculo = parseInt(payload.id_vehiculo, 10);
                payload.id_empleado = parseInt(payload.id_empleado, 10);
                try {
                    await apiFetch(BASE_URL + 'api/transporte/asignaciones/actualizar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    modalAsignacion.hide();
                    AlertManager.success('Actualizado', 'La asignación fue actualizada con éxito.');
                    if (window.TransporteConsultar) window.TransporteConsultar.cargarAsignaciones();
                    if (window.TransporteStats) window.TransporteStats.cargar();
                } catch (err) { AlertManager.warning('Atención', err.mensaje); }
            });
        }
    }

    return {
        abrirRuta,
        abrirVehiculo,
        abrirProveedor,
        abrirRepuesto,
        abrirEntradaRepuesto,
        abrirSalidaRepuesto,
        abrirAsignacion,
        abrirDetalleMantenimiento,
    };
})();
