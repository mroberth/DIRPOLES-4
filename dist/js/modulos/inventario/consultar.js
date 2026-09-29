// dist/js/modulos/inventario/consultar.js
// ------------------------------------------------------------------
// Tabla de insumos: DataTables + exportar (Excel/PDF) + detalle,
// editar (modal), ENTRADA de stock, SALIDA de stock y HISTORIAL (kardex).
// Consume api/inventario/* con apiFetch (contrato Respuesta).
//
// Reglas visibles (las aplica el backend igualmente):
//  - Estatus mostrado: si la fecha ya pasó se muestra 'Vencido' aunque en
//    BD aún figure 'Disponible' (sin masivos UPDATE).
//  - Entrada deshabilitada si el insumo está vencido.
//  - Salida deshabilitada si la cantidad es 0.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyInsumos');
    if (!tbody) return;

    let insumos = [];   // datos cargados (para detalle/entrada/salida)
    let tabla = null;   // instancia DataTable de la tabla principal

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    const hoyISO = () => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    };

    /** ¿Está vencido según la fecha (aunque BD diga otra cosa)? */
    function estaVencido(i) {
        return String(i.fecha_vencimiento || '') < hoyISO() || i.estatus === 'Vencido';
    }

    /** Estatus mostrado en la tabla (efectivo). */
    function estatusEfectivo(i) {
        if (estaVencido(i)) return 'Vencido';
        return i.estatus || 'Agotado';
    }

    function fila(i) {
        const estatus = estatusEfectivo(i);
        const badges = {
            'Disponible': '<span class="badge bg-success">Disponible</span>',
            'Agotado':    '<span class="badge bg-secondary">Agotado</span>',
            'Vencido':    '<span class="badge bg-danger">Vencido</span>',
        };
        const badge = badges[estatus] || `<span class="badge bg-secondary">${escapar(estatus)}</span>`;

        const vencido = estaVencido(i);
        const sinStock = Number(i.cantidad) <= 0;

        const btnEntrada = vencido
            ? `<button type="button" class="btn btn-sm btn-outline-success btn-entrada" data-id="${escapar(i.id_insumo)}" disabled title="No admite entradas: está vencido"><i class="fas fa-arrow-down"></i></button>`
            : `<button type="button" class="btn btn-sm btn-outline-success btn-entrada" data-id="${escapar(i.id_insumo)}" title="Registrar entrada"><i class="fas fa-arrow-down"></i></button>`;

        const btnSalida = sinStock
            ? `<button type="button" class="btn btn-sm btn-outline-warning btn-salida" data-id="${escapar(i.id_insumo)}" disabled title="Sin stock: no hay nada que retirar"><i class="fas fa-arrow-up"></i></button>`
            : `<button type="button" class="btn btn-sm btn-outline-warning btn-salida" data-id="${escapar(i.id_insumo)}" title="Registrar salida"><i class="fas fa-arrow-up"></i></button>`;

        return `
            <tr>
                <td>${escapar(i.nombre_insumo)}</td>
                <td>${escapar(i.tipo_insumo)}</td>
                <td>${escapar(i.nombre_presentacion)}</td>
                <td class="text-center">${escapar(i.cantidad)}</td>
                <td class="text-center">${badge}</td>
                <td data-order="${escapar(i.fecha_vencimiento)}">${escapar(Formato.fecha(i.fecha_vencimiento))}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-id="${escapar(i.id_insumo)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-id="${escapar(i.id_insumo)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    ${btnEntrada}
                    ${btnSalida}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-id="${escapar(i.id_insumo)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    async function cargar() {
        try {
            insumos = await apiFetch(BASE_URL + 'api/inventario/listar');

            // Destruir SIEMPRE antes de repintar el tbody.
            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = insumos.map(fila).join('');

            tabla = DataTableHelper.inicializar('#tablaInsumos', {
                titulo: 'Inventario Medico',
                orden: [[0, 'asc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4, 5],
                columnDefs: [
                    { targets: 3, width: '90px', className: 'text-center' },                       // Cantidad
                    { targets: 4, width: '110px', className: 'text-center' },                      // Estatus
                    { targets: 5, width: '120px' },                                                // Vencimiento
                    { targets: 6, width: '210px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
                ],
            });
        } catch (error) {
            console.error('Inventario:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function porId(id) {
        return insumos.find((x) => String(x.id_insumo) === String(id));
    }

    function verDetalle(id) {
        const i = porId(id);
        if (!i) return;

        Swal.fire({
            title: i.nombre_insumo,
            html: `
                <div class="text-start small">
                    <p class="mb-1"><strong>Presentación:</strong> ${escapar(i.nombre_presentacion)}</p>
                    <p class="mb-1"><strong>Tipo:</strong> ${escapar(i.tipo_insumo)}</p>
                    <p class="mb-1"><strong>Descripción:</strong> ${escapar(i.descripcion)}</p>
                    <p class="mb-1"><strong>Cantidad:</strong> ${escapar(i.cantidad)}</p>
                    <p class="mb-1"><strong>Estatus:</strong> ${escapar(estatusEfectivo(i))}</p>
                    <p class="mb-1"><strong>Vencimiento:</strong> ${escapar(Formato.fecha(i.fecha_vencimiento))}</p>
                    <p class="mb-0"><strong>Registrado:</strong> ${escapar(Formato.fecha(i.fecha_creacion))}</p>
                </div>`,
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
        });
    }

    async function eliminar(id) {
        const i = porId(id);
        const nombre = i ? i.nombre_insumo : 'este insumo';

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar insumo?',
            `Se eliminará "${nombre}". Solo es posible si no tiene stock ni movimientos reales. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/inventario/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_insumo: Number(id) }),
            });

            AlertManager.success('Eliminado', 'El insumo fue eliminado.');
            recargarTodo();
        } catch (error) {
            if (error.codigo === 'IN_USE') {
                AlertManager.warning('No se puede eliminar', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Insumo no encontrado', 'Puede haber sido eliminado. Actualizando la lista…');
                recargarTodo();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function recargarTodo() {
        cargar();
        if (window.InventarioStats) window.InventarioStats.cargar();
    }

    // ==================================================================
    // Modales: editar (editar.js), entrada, salida e historial
    // ==================================================================
    const modalEntradaEl  = document.getElementById('modalEntrada');
    const modalSalidaEl   = document.getElementById('modalSalida');
    const modalHistorialEl = document.getElementById('modalHistorial');

    const modalEntrada  = (typeof bootstrap !== 'undefined' && modalEntradaEl)  ? new bootstrap.Modal(modalEntradaEl)  : null;
    const modalSalida   = (typeof bootstrap !== 'undefined' && modalSalidaEl)   ? new bootstrap.Modal(modalSalidaEl)   : null;
    const modalHistorial = (typeof bootstrap !== 'undefined' && modalHistorialEl) ? new bootstrap.Modal(modalHistorialEl) : null;

    /** Limpia errores/estados de un formulario de modal antes de abrirlo. */
    function limpiarForm(form) {
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    const V = window.InventarioValidaciones;

    // ---------- validación inline de los modales de movimiento ----------

    // Máximo de unidades que admite UNA entrada.
    const MAX_ENTRADA = 1000;

    function validarCantidad(campo, maximo) {
        const max = Number(maximo) || 2147483647;
        const v = String(campo.value || '').trim();
        if (v === '') { V.mostrarError(campo, 'La cantidad es obligatoria'); return false; }
        if (!/^\d+$/.test(v) || Number(v) < 1) {
            V.mostrarError(campo, 'Debe ser un número entero mayor o igual a 1');
            return false;
        }
        if (Number(v) > max) {
            V.mostrarError(campo, `La cantidad no puede ser mayor a ${max}`);
            return false;
        }
        V.limpiarError(campo);
        return true;
    }

    function validarDetalleTexto(campo) {
        let v = String(campo.value || '').replace(/<[^>]*>?/gm, '');
        if (v.trim() === '') { V.mostrarError(campo, 'Este detalle es obligatorio'); return false; }
        if (v.trim().length < 2 || v.trim().length > 250) {
            V.mostrarError(campo, 'Debe tener entre 2 y 250 caracteres');
            return false;
        }
        campo.value = v;
        V.limpiarError(campo);
        return true;
    }

    function validarMotivo(campo) {
        if (campo.value === '') { V.mostrarError(campo, 'Selecciona el motivo de la salida'); return false; }
        V.limpiarError(campo);
        return true;
    }

    // ---------- ENTRADA ----------

    function abrirEntrada(id) {
        const i = porId(id);
        if (!i) return;

        const form = document.getElementById('form-entrada');
        form.reset();
        limpiarForm(form);

        document.getElementById('entrada_id_insumo').value = i.id_insumo;
        document.getElementById('entrada_insumo').value = `${i.nombre_insumo} · ${i.nombre_presentacion} (stock actual: ${i.cantidad})`;
        const codigo = document.getElementById('entradaCodigo');
        if (codigo) codigo.textContent = `${i.nombre_insumo} · ${i.nombre_presentacion}`;

        if (modalEntrada) modalEntrada.show();
    }

    const formEntrada = document.getElementById('form-entrada');
    if (formEntrada) {
        const cEnt = document.getElementById('entrada_cantidad');
        const dEnt = document.getElementById('entrada_descripcion');
        cEnt.addEventListener('input', () => validarCantidad(cEnt, MAX_ENTRADA));
        dEnt.addEventListener('input', () => validarDetalleTexto(dEnt));

        formEntrada.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            const ok = [validarCantidad(cEnt, MAX_ENTRADA), validarDetalleTexto(dEnt)].every(Boolean);
            if (!ok) return;

            try {
                const r = await apiFetch(BASE_URL + 'api/inventario/entrada', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_insumo: Number(document.getElementById('entrada_id_insumo').value),
                        cantidad: Number(cEnt.value),
                        descripcion: dEnt.value.trim(),
                    }),
                });

                if (modalEntrada) modalEntrada.hide();
                AlertManager.success('Entrada registrada',
                    `Se ingresaron ${r.cantidad} unidades de "${r.nombre_insumo}". Stock total: ${r.stock_total}.`);
                recargarTodo();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('No se pudo registrar', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    }

    // ---------- SALIDA ----------

    function abrirSalida(id) {
        const i = porId(id);
        if (!i) return;

        const form = document.getElementById('form-salida');
        form.reset();
        limpiarForm(form);

        document.getElementById('salida_id_insumo').value = i.id_insumo;
        document.getElementById('salida_insumo').value = `${i.nombre_insumo} · ${i.nombre_presentacion} (stock actual: ${i.cantidad})`;
        const codigo = document.getElementById('salidaCodigo');
        if (codigo) codigo.textContent = `${i.nombre_insumo} · ${i.nombre_presentacion}`;

        if (modalSalida) modalSalida.show();
    }

    const formSalida = document.getElementById('form-salida');
    if (formSalida) {
        const cSal = document.getElementById('salida_cantidad');
        const mSal = document.getElementById('salida_motivo');
        const dSal = document.getElementById('salida_descripcion');

        // La validación de stock se hace al enviar (el valor es variable).
        cSal.addEventListener('input', () => validarCantidad(cSal));
        mSal.addEventListener('change', () => validarMotivo(mSal));
        dSal.addEventListener('input', () => validarDetalleTexto(dSal));

        formSalida.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            const ok = [
                validarCantidad(cSal),
                validarMotivo(mSal),
                validarDetalleTexto(dSal),
            ].every(Boolean);
            if (!ok) return;

            // Control de stock en el cliente (el backend lo repite con FOR UPDATE).
            const id = document.getElementById('salida_id_insumo').value;
            const i = porId(id);
            if (i && Number(cSal.value) > Number(i.cantidad)) {
                V.mostrarError(cSal, `Solo hay ${i.cantidad} unidades en stock`);
                return;
            }

            try {
                const r = await apiFetch(BASE_URL + 'api/inventario/salida', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        id_insumo: Number(id),
                        cantidad: Number(cSal.value),
                        motivo: mSal.value,
                        descripcion: dSal.value.trim(),
                    }),
                });

                if (modalSalida) modalSalida.hide();
                AlertManager.success('Salida registrada',
                    `Se retiraron ${r.cantidad} unidades de "${r.nombre_insumo}" (${r.motivo}). Stock total: ${r.stock_total}.`);
                recargarTodo();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('No se pudo registrar', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    }

    // ---------- HISTORIAL (kardex) ----------

    let tablaMov = null;

    async function cargarHistorial() {
        const tbodyMov = document.getElementById('tbodyMovimientos');
        if (!tbodyMov) return;

        try {
            const movs = await apiFetch(BASE_URL + 'api/inventario/movimientos');

            // Destruir SIEMPRE antes de repintar.
            if (tablaMov) {
                tablaMov.destroy();
                tablaMov = null;
            }

            const badge = (tipo) => {
                if (tipo === 'Entrada')  return '<span class="badge bg-success">Entrada</span>';
                if (tipo === 'Salida')   return '<span class="badge bg-danger">Salida</span>';
                return '<span class="badge bg-secondary">Registro</span>';
            };
            const cantidad = (m) => {
                if (m.tipo_movimiento === 'Entrada') return '+' + Number(m.cantidad);
                if (m.tipo_movimiento === 'Salida')  return '-' + Number(m.cantidad);
                return String(Number(m.cantidad));
            };

            tbodyMov.innerHTML = movs.map((m) => `
                <tr>
                    <td>${escapar(m.nombre_insumo)}</td>
                    <td>${escapar(m.responsable)}</td>
                    <td data-order="${escapar(m.fecha_movimiento)}">${escapar(Formato.fechaHora(m.fecha_movimiento))}</td>
                    <td class="text-center">${badge(m.tipo_movimiento)}</td>
                    <td class="text-center">${escapar(cantidad(m))}</td>
                    <td>${escapar(m.descripcion)}</td>
                </tr>`).join('');

            tablaMov = DataTableHelper.inicializar('#tablaMovimientos', {
                titulo: 'Historial de movimientos',
                orden: [[2, 'desc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4, 5],
                columnDefs: [
                    { targets: 3, width: '100px', className: 'text-center' },
                    { targets: 4, width: '90px', className: 'text-center' },
                ],
            });
        } catch (error) {
            console.error('Historial:', error);
            AlertManager.error('Error', error.mensaje || 'No se pudo cargar el historial.');
        }
    }

    // Carga perezosa: cada vez que el modal se muestra, con datos frescos.
    if (modalHistorialEl) {
        modalHistorialEl.addEventListener('shown.bs.modal', cargarHistorial);
    }

    const btnHistorial = document.getElementById('btn-historial');
    if (btnHistorial && modalHistorial) {
        btnHistorial.addEventListener('click', () => modalHistorial.show());
    }

    // ---------- Recargar ----------
    const btn = document.getElementById('btn-recargar');
    if (btn) btn.addEventListener('click', recargarTodo);

    // ---------- Acciones (delegado: funciona aunque la tabla se redibuje) ----------
    document.addEventListener('click', function (ev) {
        const detalle = ev.target.closest('.btn-detalle');
        if (detalle) return verDetalle(detalle.getAttribute('data-id'));

        const editar = ev.target.closest('.btn-editar');
        if (editar) {
            if (window.InventarioEditar) {
                window.InventarioEditar.abrir(editar.getAttribute('data-id'));
            }
            return;
        }

        const entrada = ev.target.closest('.btn-entrada');
        if (entrada && !entrada.disabled) return abrirEntrada(entrada.getAttribute('data-id'));

        const salida = ev.target.closest('.btn-salida');
        if (salida && !salida.disabled) return abrirSalida(salida.getAttribute('data-id'));

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar) return eliminar(borrar.getAttribute('data-id'));
    });

    // Expuesto para que editar.js refresque la tabla tras guardar.
    window.InventarioConsultar = { recargar: cargar };

    cargar();
});
