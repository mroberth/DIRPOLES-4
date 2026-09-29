// dist/js/modulos/jornadas/diagnosticos.js
// ------------------------------------------------------------------
// Modal de DIAGNÓSTICOS de un asistente (página de detalle):
// lista, alta (con insumos que descuentan inventario), corrección de
// textos y eliminación. La persona y los insumos NO se editan después.
//
// Reglas visibles (las aplica el backend igualmente):
//  - un asistente puede tener VARIOS diagnósticos;
//  - la jornada debe estar 'Activa' y vigente por fecha;
//  - los insumos se eligen de los disponibles/sin vencer y su stock se
//    descuenta en la misma transacción (FOR UPDATE + kardex);
//  - borrar un diagnóstico NO devuelve el stock.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalDiagnosticos');
    if (!modalEl) return;

    const V = window.JornadasValidaciones;
    const puedeCrear = window.JORNADAS_PUEDE_CREAR === true;
    const puedeEditar = window.JORNADAS_PUEDE_EDITAR === true;
    const puedeEliminar = window.JORNADAS_PUEDE_ELIMINAR === true;

    let idAsistenteActual = null;
    let diagnosticos = [];        // lista actual (para editar/eliminar)
    let catalogoInsumos = [];     // insumos usables
    let seleccionados = [];       // insumos elegidos en el formulario

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    // ---------------- Formularios y validaciones ----------------
    const formDiagnostico = document.getElementById('form-diagnostico');
    const validadorNuevo = formDiagnostico
        ? V.configurar(formDiagnostico, { tipo: 'diagnostico', prefijo: 'diag_' })
        : null;

    const formEditar = document.getElementById('form-editar-diagnostico');
    const validadorEditar = formEditar
        ? V.configurar(formEditar, { tipo: 'diagnostico', prefijo: 'edit_' })
        : null;

    // ---------------- Catálogo de insumos ----------------
    function llenarInsumos() {
        const sel = document.getElementById('diag_insumo');
        if (!sel) return;
        sel.innerHTML = '<option value="">Seleccione un insumo…</option>';
        catalogoInsumos.forEach((i) => {
            const opcion = document.createElement('option');
            opcion.value = i.id_insumo;
            opcion.textContent = `${i.nombre_insumo} (${i.nombre_presentacion}) — stock ${i.cantidad}`;
            sel.appendChild(opcion);
        });
        if (window.initSelect2 && formDiagnostico) window.initSelect2(formDiagnostico);
    }

    async function cargarInsumos() {
        if (catalogoInsumos.length) return;
        try {
            catalogoInsumos = await apiFetch(BASE_URL + 'api/jornadas/insumos_disponibles');
            llenarInsumos();
        } catch (error) {
            console.error('Insumos disponibles:', error);
            const sel = document.getElementById('diag_insumo');
            if (sel) {
                sel.innerHTML = '<option value="">Sin opciones</option>';
                V.mostrarError(sel, 'No se pudo verificar el inventario. Intenta de nuevo.');
            }
        }
    }

    // ---------------- Lista de insumos elegidos ----------------
    function pintarSeleccionados() {
        const cont = document.getElementById('listaInsumos');
        if (!cont) return;

        if (!seleccionados.length) {
            cont.innerHTML = '<span class="form-text text-muted">Sin insumos: el diagnóstico puede guardarse sin ellos.</span>';
            return;
        }

        cont.innerHTML = seleccionados.map((s, i) => `
            <span class="badge bg-light text-dark border me-1 mb-1 p-2">
                ${escapar(s.nombre)} ×${escapar(s.cantidad)}
                <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-danger btn-quitar-insumo"
                        data-indice="${i}" title="Quitar insumo">
                    <i class="fas fa-xmark"></i>
                </button>
            </span>`).join('');
    }

    function stockDisponible(idInsumo) {
        const insumo = catalogoInsumos.find((i) => String(i.id_insumo) === String(idInsumo));
        if (!insumo) return 0;
        const yaElegido = seleccionados
            .filter((s) => String(s.id_insumo) === String(idInsumo))
            .reduce((acc, s) => acc + Number(s.cantidad), 0);
        return Math.max(0, Number(insumo.cantidad) - yaElegido);
    }

    function agregarInsumo() {
        const sel = document.getElementById('diag_insumo');
        const cant = document.getElementById('diag_cantidad');
        if (!sel || !cant) return;

        const idInsumo = Number(sel.value);
        const cantidad = Number(cant.value);

        if (!idInsumo) {
            V.mostrarError(sel, 'Selecciona un insumo');
            return;
        }
        if (!Number.isInteger(cantidad) || cantidad < 1) {
            V.mostrarError(sel, 'Indica una cantidad mayor que cero');
            return;
        }
        if (cantidad > stockDisponible(idInsumo)) {
            V.mostrarError(sel, `Stock insuficiente (disponible: ${stockDisponible(idInsumo)})`);
            return;
        }

        const insumo = catalogoInsumos.find((i) => String(i.id_insumo) === String(idInsumo));
        const existente = seleccionados.find((s) => String(s.id_insumo) === String(idInsumo));
        if (existente) {
            existente.cantidad += cantidad;
        } else {
            seleccionados.push({
                id_insumo: idInsumo,
                cantidad: cantidad,
                nombre: insumo ? insumo.nombre_insumo : ('Insumo #' + idInsumo),
            });
        }

        V.limpiarError(sel);
        sel.value = '';
        cant.value = '';
        if (window.initSelect2 && formDiagnostico) window.initSelect2(formDiagnostico);
        pintarSeleccionados();
    }

    const btnAgregarInsumo = document.getElementById('btn-agregar-insumo');
    if (btnAgregarInsumo) btnAgregarInsumo.addEventListener('click', agregarInsumo);

    // ---------------- Lista de diagnósticos ----------------
    function tarjeta(d) {
        const insumos = (d.insumos || []).length
            ? `<div class="mt-1">${d.insumos.map((i) =>
                `<span class="badge bg-light text-dark border me-1">${escapar(i.nombre_insumo)} ×${escapar(i.cantidad_usada)}</span>`
            ).join('')}</div>`
            : '<div class="form-text text-muted">Sin insumos.</div>';

        const btnEditar = puedeEditar
            ? `<button type="button" class="btn btn-sm btn-outline-primary btn-editar-diagnostico"
                       data-id="${escapar(d.id_jornada_diagnostico)}" title="Corregir textos">
                   <i class="fas fa-pen"></i>
               </button>`
            : '';

        const btnEliminar = puedeEliminar
            ? `<button type="button" class="btn btn-sm btn-outline-dark btn-eliminar-diagnostico"
                       data-id="${escapar(d.id_jornada_diagnostico)}" title="Eliminar diagnóstico">
                   <i class="fas fa-trash"></i>
               </button>`
            : '';

        return `
            <div class="border rounded p-3 mb-2 bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="small">
                        <strong class="text-success">Diagnóstico:</strong> ${escapar(d.diagnostico)}<br>
                        <strong class="text-primary">Tratamiento:</strong> ${escapar(d.tratamiento)}<br>
                        <strong class="text-muted">Observaciones:</strong> ${escapar(d.observaciones || '—')}<br>
                        <span class="text-muted">
                            <i class="fas fa-user-doctor me-1"></i>${escapar(d.medico || 'Sin empleado registrado')} —
                            ${escapar(Formato.fechaHora(d.fecha_diagnostico))}
                        </span>
                        ${insumos}
                    </div>
                    <div class="text-nowrap ms-2">${btnEditar} ${btnEliminar}</div>
                </div>
            </div>`;
    }

    async function cargarDiagnosticos() {
        const cont = document.getElementById('listaDiagnosticos');
        const conteo = document.getElementById('conteoDiagnosticos');
        if (!idAsistenteActual) return;

        try {
            diagnosticos = await apiFetch(
                BASE_URL + 'api/jornadas/diagnosticos/' + encodeURIComponent(idAsistenteActual)
            );
            cont.innerHTML = diagnosticos.length
                ? diagnosticos.map(tarjeta).join('')
                : '<div class="text-muted small mb-2">Esta persona aún no tiene diagnósticos en la jornada.</div>';
            if (conteo) conteo.textContent = diagnosticos.length;
        } catch (error) {
            console.error('Diagnósticos:', error);
            cont.innerHTML = '<div class="text-danger small">No se pudieron cargar los diagnósticos.</div>';
        }
    }

    // ---------------- Abrir el modal ----------------
    async function abrir(idAsistente, nombre) {
        idAsistenteActual = Number(idAsistente);

        const titulo = document.getElementById('diagnosticosPersona');
        if (titulo) titulo.textContent = nombre || ('Asistente #' + idAsistenteActual);

        seleccionados = [];
        pintarSeleccionados();

        if (formDiagnostico) {
            formDiagnostico.reset();
            validadorNuevo.limpiar();
        }

        if (typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        await Promise.all([cargarDiagnosticos(), puedeCrear ? cargarInsumos() : Promise.resolve()]);
    }

    // ---------------- Guardar diagnóstico ----------------
    if (formDiagnostico) {
        formDiagnostico.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validadorNuevo.validarTodo()) {
                AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            try {
                const nuevo = await apiFetch(BASE_URL + 'api/jornadas/agregar_diagnostico', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_jornada_beneficiario: idAsistenteActual,
                        diagnostico:   document.getElementById('diag_diagnostico').value.trim(),
                        tratamiento:   document.getElementById('diag_tratamiento').value.trim(),
                        observaciones: document.getElementById('diag_observaciones').value.trim(),
                        insumos: seleccionados.map((s) => ({ id_insumo: s.id_insumo, cantidad: s.cantidad })),
                    }),
                });

                AlertManager.success('Diagnóstico registrado',
                    `Se guardó el diagnóstico de "${nuevo.persona}" en la jornada "${nuevo.nombre_jornada}".`);

                formDiagnostico.reset();
                validadorNuevo.limpiar();
                seleccionados = [];
                pintarSeleccionados();
                catalogoInsumos = [];   // el stock cambió: recargar al próximo abrir
                await cargarDiagnosticos();
                await cargarInsumos();
                if (window.JornadasDetalle) window.JornadasDetalle.recargar();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND'
                    || error.codigo === 'ALREADY_EXISTS') {
                    AlertManager.warning('No se pudo guardar', error.mensaje);
                } else if (error.codigo === 'ACCESS_DENIED') {
                    AlertManager.warning('Sin permiso', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
                await cargarDiagnosticos();
            }
        });
    }

    // ---------------- Editar diagnóstico (solo textos) ----------------
    const modalEditar = document.getElementById('modalEditarDiagnostico');

    function abrirEdicion(id) {
        const d = diagnosticos.find((x) => String(x.id_jornada_diagnostico) === String(id));
        if (!d || !formEditar) return;

        formEditar.reset();
        validadorEditar.limpiar();

        document.getElementById('edit_id_jornada_diagnostico').value = d.id_jornada_diagnostico;
        document.getElementById('edit_diagnostico').value = d.diagnostico || '';
        document.getElementById('edit_tratamiento').value = d.tratamiento || '';
        document.getElementById('edit_observaciones').value = d.observaciones || '';

        if (typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEditar).show();
        }
    }

    if (formEditar) {
        formEditar.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validadorEditar.validarTodo()) {
                AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            try {
                await apiFetch(BASE_URL + 'api/jornadas/actualizar_diagnostico', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_jornada_diagnostico: Number(document.getElementById('edit_id_jornada_diagnostico').value),
                        diagnostico:   document.getElementById('edit_diagnostico').value.trim(),
                        tratamiento:   document.getElementById('edit_tratamiento').value.trim(),
                        observaciones: document.getElementById('edit_observaciones').value.trim(),
                    }),
                });

                AlertManager.success('Diagnóstico actualizado', 'Los textos del diagnóstico fueron corregidos.');
                if (typeof bootstrap !== 'undefined') {
                    const inst = bootstrap.Modal.getInstance(modalEditar);
                    if (inst) inst.hide();
                }
                await cargarDiagnosticos();
            } catch (error) {
                if (error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('No encontrado', error.mensaje);
                    await cargarDiagnosticos();
                } else if (error.codigo === 'ACCESS_DENIED') {
                    AlertManager.warning('Sin permiso', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    }

    // ---------------- Eliminar diagnóstico ----------------
    async function eliminarDiagnostico(id) {
        const d = diagnosticos.find((x) => String(x.id_jornada_diagnostico) === String(id));
        if (!d) return;

        const conInsumos = (d.insumos || []).length;
        const confirmacion = await AlertManager.confirm(
            '¿Eliminar diagnóstico?',
            `Se borrará el diagnóstico de "${d.diagnostico}"${conInsumos
                ? ' junto con sus ' + conInsumos + ' insumo(s): el stock ya descontado NO se devuelve'
                : ''}. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/jornadas/eliminar_diagnostico', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_jornada_diagnostico: Number(id) }),
            });
            AlertManager.success('Diagnóstico eliminado', 'El diagnóstico fue borrado.');
            await cargarDiagnosticos();
            if (window.JornadasDetalle) window.JornadasDetalle.recargar();
        } catch (error) {
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('No encontrado', error.mensaje);
                await cargarDiagnosticos();
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ---------------- Acciones (delegado) ----------------
    document.addEventListener('click', function (ev) {
        const quitar = ev.target.closest('.btn-quitar-insumo');
        if (quitar) {
            seleccionados.splice(Number(quitar.getAttribute('data-indice')), 1);
            pintarSeleccionados();
            return;
        }

        const editar = ev.target.closest('.btn-editar-diagnostico');
        if (editar && !editar.disabled) {
            abrirEdicion(editar.getAttribute('data-id'));
            return;
        }

        const borrar = ev.target.closest('.btn-eliminar-diagnostico');
        if (borrar && !borrar.disabled) {
            eliminarDiagnostico(borrar.getAttribute('data-id'));
        }
    });

    window.JornadasDiagnosticos = { abrir };
});
