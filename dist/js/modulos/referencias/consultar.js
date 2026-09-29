// dist/js/modulos/referencias/consultar.js
// ------------------------------------------------------------------
// Tabla de referencias: DataTables + exportar (Excel/PDF) + detalle con
// historial, ACEPTAR, RECHAZAR (modal con motivo) y ELIMINAR.
// Consume api/referencias/* con apiFetch (contrato Respuesta).
//
// Reglas visibles (las aplica el backend igualmente):
//  - Aceptar/Rechazar: solo el empleado DESTINO (o admin) y solo 'Pendiente'.
//  - Eliminar: solo el ORIGEN (o admin) y solo 'Pendiente'.
//  - La lista ya viene con alcance: cada quien ve lo suyo (admin ve todo).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyReferencias');
    if (!tbody) return;

    const esAdmin    = !!window.REFERENCIAS_ES_ADMIN;
    const idEmpleado = Number(window.REFERENCIAS_ID_EMPLEADO || 0);

    let referencias = [];   // datos cargados (para detalle/acciones)
    let tabla = null;       // instancia DataTable de la tabla principal

    const V = window.ReferenciasValidaciones;

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    function badgeEstado(estado) {
        if (estado === 'Aceptada')  return '<span class="badge bg-success">Aceptada</span>';
        if (estado === 'Rechazada') return '<span class="badge bg-danger">Rechazada</span>';
        return '<span class="badge bg-warning text-dark">Pendiente</span>';
    }

    /** ¿Puede aceptar/rechazar? (destino o admin, y solo pendientes). */
    function puedeGestionar(r) {
        return r.estado === 'Pendiente'
            && (esAdmin || Number(r.id_empleado_destino) === idEmpleado);
    }

    /** ¿Puede eliminar? (origen o admin, y solo pendientes). */
    function puedeEliminar(r) {
        return r.estado === 'Pendiente'
            && (esAdmin || Number(r.id_empleado_origen) === idEmpleado);
    }

    function fila(r) {
        const btnAceptar = puedeGestionar(r)
            ? `<button type="button" class="btn btn-sm btn-outline-success btn-aceptar"
                       data-id="${escapar(r.id_referencia)}" title="Aceptar referencia">
                   <i class="fas fa-check"></i>
               </button>`
            : '';

        const btnRechazar = puedeGestionar(r)
            ? `<button type="button" class="btn btn-sm btn-outline-danger btn-rechazar"
                       data-id="${escapar(r.id_referencia)}" title="Rechazar referencia">
                   <i class="fas fa-ban"></i>
               </button>`
            : '';

        const btnEliminar = puedeEliminar(r)
            ? `<button type="button" class="btn btn-sm btn-outline-dark btn-eliminar"
                       data-id="${escapar(r.id_referencia)}" title="Eliminar referencia">
                   <i class="fas fa-trash"></i>
               </button>`
            : '';

        return `
            <tr>
                <td>${escapar(r.beneficiario)}<br><small class="text-muted">${escapar(r.cedula_beneficiario)}</small></td>
                <td>${escapar(r.empleado_origen)}<br><small class="text-muted">${escapar(r.servicio_origen)}</small></td>
                <td>${escapar(r.empleado_destino || '—')}<br><small class="text-muted">${escapar(r.servicio_destino || '')}</small></td>
                <td data-order="${escapar(r.fecha_referencia)}">${escapar(Formato.fecha(r.fecha_referencia))}</td>
                <td class="text-center">${badgeEstado(r.estado)}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-id="${escapar(r.id_referencia)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${btnAceptar}
                    ${btnRechazar}
                    ${btnEliminar}
                </td>
            </tr>`;
    }

    async function cargar() {
        try {
            referencias = await apiFetch(BASE_URL + 'api/referencias/listar');

            // Destruir SIEMPRE antes de repintar el tbody.
            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = referencias.map(fila).join('');

            tabla = DataTableHelper.inicializar('#tablaReferencias', {
                titulo: 'Referencias',
                orden: [[3, 'desc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4],
                columnDefs: [
                    { targets: 3, width: '110px' },                                              // Fecha
                    { targets: 4, width: '110px', className: 'text-center' },                    // Estado
                    { targets: 5, width: '160px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
                ],
            });
        } catch (error) {
            console.error('Referencias:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function porId(id) {
        return referencias.find((x) => String(x.id_referencia) === String(id));
    }

    function recargarTodo() {
        cargar();
        if (window.ReferenciasStats) window.ReferenciasStats.cargar();
    }

    // ==================================================================
    // Detalle con historial (línea de tiempo)
    // ==================================================================
    async function verDetalle(id) {
        try {
            const r = await apiFetch(BASE_URL + 'api/referencias/obtener/' + encodeURIComponent(id));

            const lineas = (r.historial || []).map((h) => {
                const color = h.estado_nuevo === 'Aceptada' ? 'success'
                    : (h.estado_nuevo === 'Rechazada' ? 'danger' : 'warning');
                const obs = h.observaciones
                    ? `<div class="text-muted small">${escapar(h.observaciones)}</div>`
                    : '';
                return `
                    <li class="mb-2">
                        <span class="badge bg-${color}">${escapar(h.estado_nuevo)}</span>
                        <strong class="ms-1">${escapar(h.empleado)}</strong>
                        <div class="text-muted small">${escapar(Formato.fechaHora(h.fecha_accion))}</div>
                        ${obs}
                    </li>`;
            }).join('');

            Swal.fire({
                title: 'Referencia #' + escapar(r.id_referencia),
                html: `
                    <div class="text-start small">
                        <p class="mb-1"><strong>Beneficiario:</strong> ${escapar(r.beneficiario)} (${escapar(r.cedula_beneficiario)})</p>
                        <p class="mb-1"><strong>Estado:</strong> ${badgeEstado(r.estado)}</p>
                        <p class="mb-1"><strong>Origen:</strong> ${escapar(r.empleado_origen)} — ${escapar(r.servicio_origen)}</p>
                        <p class="mb-1"><strong>Destino:</strong> ${escapar(r.empleado_destino || '—')} — ${escapar(r.servicio_destino || '')}</p>
                        <p class="mb-1"><strong>Fecha:</strong> ${escapar(Formato.fechaHora(r.fecha_referencia))}</p>
                        <p class="mb-1"><strong>Motivo:</strong> ${escapar(r.motivo)}</p>
                        <p class="mb-2"><strong>Observaciones:</strong> ${escapar(r.observaciones)}</p>
                        <hr class="my-2">
                        <h6 class="fw-bold mb-2">Historial de estados</h6>
                        ${lineas
                            ? `<ul class="list-unstyled mb-0">${lineas}</ul>`
                            : '<div class="text-muted small">Sin movimientos: la referencia sigue en su estado inicial.</div>'}
                    </div>`,
                showCloseButton: true,
                confirmButtonText: 'Cerrar',
                width: 640,
            });
        } catch (error) {
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('No encontrada', 'La referencia ya no existe. Actualizando la lista…');
                recargarTodo();
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin acceso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ==================================================================
    // Aceptar / Rechazar / Eliminar
    // ==================================================================
    function manejarErrorAccion(error) {
        if (error.codigo === 'ACCESS_DENIED') {
            AlertManager.warning('Sin permiso', error.mensaje);
            recargarTodo();
        } else if (error.codigo === 'NOT_FOUND') {
            AlertManager.warning('No encontrada', 'La referencia ya no existe. Actualizando la lista…');
            recargarTodo();
        } else if (error.codigo === 'VALIDATION_ERROR') {
            // p. ej. ya fue respondida por otra sesión: recarga con el estado real.
            AlertManager.warning('Referencia no disponible', error.mensaje);
            recargarTodo();
        } else {
            AlertManager.error('Error', error.mensaje);
        }
    }

    async function aceptar(id) {
        const r = porId(id);
        if (!r) return;

        const confirmacion = await AlertManager.confirm(
            '¿Aceptar referencia?',
            `Se marcará como aceptada la referencia de "${r.beneficiario}" y se avisará a quien la creó.`,
            'Sí, aceptar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/referencias/aceptar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_referencia: Number(id) }),
            });
            AlertManager.success('Referencia aceptada', 'La referencia fue aceptada correctamente.');
            recargarTodo();
        } catch (error) {
            manejarErrorAccion(error);
        }
    }

    async function rechazar(id) {
        const r = porId(id);
        if (!r) return;

        const form = document.getElementById('form-rechazo');
        form.reset();
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger').forEach((el) => (el.textContent = ''));

        document.getElementById('rechazo_id_referencia').value = r.id_referencia;
        document.getElementById('rechazo_beneficiario').value = r.beneficiario;
        document.getElementById('rechazoReferencia').textContent = `Referencia #${r.id_referencia}`;

        const modalEl = document.getElementById('modalRechazo');
        if (modalEl && typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(modalEl).show();
        }
    }

    function validarMotivoRechazo(campo) {
        const v = String(campo.value || '').replace(/<[^>]*>?/gm, ''); // anti-XSS
        if (v.trim() === '') {
            V.mostrarError(campo, 'El motivo del rechazo es obligatorio');
            return false;
        }
        if (v.trim().length < 2 || v.trim().length > 2000) {
            V.mostrarError(campo, 'Debe tener entre 2 y 2000 caracteres');
            return false;
        }
        campo.value = v;
        V.limpiarError(campo);
        return true;
    }

    const formRechazo = document.getElementById('form-rechazo');
    if (formRechazo) {
        const motivo = document.getElementById('rechazo_motivo');
        motivo.addEventListener('input', () => validarMotivoRechazo(motivo));

        formRechazo.addEventListener('submit', async function (ev) {
            ev.preventDefault();
            if (!validarMotivoRechazo(motivo)) return;

            try {
                await apiFetch(BASE_URL + 'api/referencias/rechazar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_referencia: Number(document.getElementById('rechazo_id_referencia').value),
                        observaciones: motivo.value.trim(),
                    }),
                });

                const modalEl = document.getElementById('modalRechazo');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(modalEl).hide();
                }
                AlertManager.success('Referencia rechazada',
                    'La referencia fue rechazada y quien la creó recibirá una notificación.');
                recargarTodo();
            } catch (error) {
                manejarErrorAccion(error);
            }
        });
    }

    async function eliminar(id) {
        const r = porId(id);
        if (!r) return;

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar referencia?',
            `Se eliminará la referencia de "${r.beneficiario}" (solo puede borrarse mientras esté Pendiente). Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/referencias/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_referencia: Number(id) }),
            });
            AlertManager.success('Referencia eliminada', 'La referencia fue eliminada.');
            recargarTodo();
        } catch (error) {
            manejarErrorAccion(error);
        }
    }

    // ---------- Recargar ----------
    const btn = document.getElementById('btn-recargar');
    if (btn) btn.addEventListener('click', recargarTodo);

    // ---------- Acciones (delegado: funciona aunque la tabla se redibuje) ----------
    document.addEventListener('click', function (ev) {
        const detalle = ev.target.closest('.btn-detalle');
        if (detalle) return verDetalle(detalle.getAttribute('data-id'));

        const aceptarBtn = ev.target.closest('.btn-aceptar');
        if (aceptarBtn && !aceptarBtn.disabled) return aceptar(aceptarBtn.getAttribute('data-id'));

        const rechazarBtn = ev.target.closest('.btn-rechazar');
        if (rechazarBtn && !rechazarBtn.disabled) return rechazar(rechazarBtn.getAttribute('data-id'));

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar && !borrar.disabled) return eliminar(borrar.getAttribute('data-id'));
    });

    // Expuesto para refrescar la tabla desde otros scripts si hiciera falta.
    window.ReferenciasConsultar = { recargar: cargar };

    cargar();
});
