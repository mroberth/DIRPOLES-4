// dist/js/modulos/mobiliario/consultar.js
// ------------------------------------------------------------------
// Consulta del módulo Mobiliario: TRES pestañas con su DataTable
// (Mobiliario, Equipos, Fichas), carga perezosa por pestaña, detalle,
// edición (editar.js), REUBICACIÓN, BAJA lógica, eliminación y
// HISTORIAL (kardex). Consume api/mobiliario/* con apiFetch.
//
// Reglas visibles (el backend las repite con transacción + FOR UPDATE):
//  - El estatus no se edita: solo cambia con Baja (a 'Inactivo').
//  - Baja bloqueada si el ítem está en una ficha activa (botón deshabilitado
//    cuando el backend ya lo sabe por el listado).
//  - Eliminar bloqueado si el ítem está en detalle_ficha_*.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbodyMob   = document.getElementById('tbodyMobiliario');
    const tbodyEq    = document.getElementById('tbodyEquipos');
    const tbodyFicha = document.getElementById('tbodyFichas');
    if (!tbodyMob && !tbodyEq && !tbodyFicha) return;

    const V = window.MobiliarioValidaciones;

    // Estado por sub-flujo: {registros, tabla, cargada}
    const estado = {
        mobiliario: { registros: [], tabla: null, cargada: false },
        equipo:     { registros: [], tabla: null, cargada: false },
        ficha:      { registros: [], tabla: null, cargada: false },
    };

    const contenedores = {
        mobiliario: { tbody: tbodyMob,   tabla: '#tablaMobiliario' },
        equipo:     { tbody: tbodyEq,    tabla: '#tablaEquipos' },
        ficha:      { tbody: tbodyFicha, tabla: '#tablaFichas' },
    };

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    /** Igual que escapar, pero seguro dentro de un atributo HTML con comillas. */
    const escaparAttr = (txt) => escapar(txt).replace(/"/g, '&quot;');

    const badge = (texto, color) => `<span class="badge bg-${color}">${escapar(texto)}</span>`;

    function badgeEstatus(estatus) {
        return estatus === 'Activo' ? badge('Activo', 'success') : badge('Inactivo', 'secondary');
    }

    function badgeFicha(estatus) {
        return Number(estatus) === 1 ? badge('Activa', 'success') : badge('Inactiva', 'secondary');
    }

    function badgeMovimiento(tipo) {
        const mapa = {
            asignacion:  ['Alta', 'primary'],
            reubicacion: ['Reubicación', 'warning'],
            modificacion: ['Modificación', 'info'],
            baja:        ['Baja', 'danger'],
        };
        const dato = mapa[tipo] || [tipo, 'secondary'];
        return badge(dato[0], dato[1]);
    }

    const inactivo = (x) => String(x.estatus || '') === 'Inactivo';

    // ---------------- filas ----------------

    function filaMobiliario(i) {
        const disponible = Number(i.disponible);
        const total = Number(i.cantidad);
        const enFicha = disponible < total;               // hay unidades asignadas
        const bajaDeshabilitada = inactivo(i) || enFicha;

        const btnBaja = bajaDeshabilitada
            ? `<button type="button" class="btn btn-sm btn-outline-warning btn-baja" data-tipo="mobiliario"
                       data-id="${escapar(i.id_mobiliario)}" disabled
                       title="${inactivo(i) ? 'Ya está dado de baja' : 'Tiene unidades en una ficha activa: quítalas primero'}">
                   <i class="fas fa-ban"></i>
               </button>`
            : `<button type="button" class="btn btn-sm btn-outline-warning btn-baja" data-tipo="mobiliario"
                       data-id="${escapar(i.id_mobiliario)}" title="Registrar baja lógica">
                   <i class="fas fa-ban"></i>
               </button>`;

        return `
            <tr>
                <td>
                    <div class="fw-bold">${escapar(i.tipo_mobiliario || 'Sin tipo')} #${escapar(i.id_mobiliario)}</div>
                    <small class="text-muted">${escapar([i.marca, i.modelo].filter(Boolean).join(' · '))}</small>
                </td>
                <td>${escapar(i.servicio || 'Sin ubicación')}</td>
                <td class="text-center">${escapar(i.cantidad)}</td>
                <td class="text-center">${escapar(i.disponible)}</td>
                <td class="text-center">${badge(i.estado || '—', 'secondary')}</td>
                <td class="text-center">${badgeEstatus(i.estatus)}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-tipo="mobiliario" data-id="${escapar(i.id_mobiliario)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-tipo="mobiliario" data-id="${escapar(i.id_mobiliario)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info btn-reubicar"
                            data-tipo="mobiliario" data-id="${escapar(i.id_mobiliario)}" title="Reubicar">
                        <i class="fas fa-arrows-up-down-left-right"></i>
                    </button>
                    ${btnBaja}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-tipo="mobiliario" data-id="${escapar(i.id_mobiliario)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    function filaEquipo(e) {
        const enFicha = e.ficha_activa != null && e.ficha_activa !== '';
        const bajaDeshabilitada = inactivo(e) || enFicha;

        const motivoBaja = inactivo(e)
            ? 'Ya está dado de baja'
            : (enFicha
                ? 'Está en la ficha activa ' + escaparAttr(e.ficha_activa) + ': quítalo primero'
                : 'Registrar baja lógica');

        const btnBaja = bajaDeshabilitada
            ? `<button type="button" class="btn btn-sm btn-outline-warning btn-baja" data-tipo="equipo"
                       data-id="${escaparAttr(e.id_equipo)}" disabled title="${motivoBaja}">
                   <i class="fas fa-ban"></i>
               </button>`
            : `<button type="button" class="btn btn-sm btn-outline-warning btn-baja" data-tipo="equipo"
                       data-id="${escaparAttr(e.id_equipo)}" title="${motivoBaja}">
                   <i class="fas fa-ban"></i>
               </button>`;

        return `
            <tr>
                <td>${escapar(e.tipo_equipo || 'Sin tipo')}</td>
                <td class="fw-bold">${escapar(e.serial || '—')}</td>
                <td>${escapar([e.marca, e.modelo].filter(Boolean).join(' · ') || '—')}</td>
                <td>${escapar(e.servicio || 'Sin ubicación')}</td>
                <td class="text-center">${badge(e.estado || '—', 'secondary')}</td>
                <td>${enFicha ? badge(e.ficha_activa, 'info') : '<span class="text-muted small">Sin ficha</span>'}</td>
                <td class="text-center">${badgeEstatus(e.estatus)}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-tipo="equipo" data-id="${escapar(e.id_equipo)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-tipo="equipo" data-id="${escapar(e.id_equipo)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info btn-reubicar"
                            data-tipo="equipo" data-id="${escapar(e.id_equipo)}" title="Reubicar">
                        <i class="fas fa-arrows-up-down-left-right"></i>
                    </button>
                    ${btnBaja}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-tipo="equipo" data-id="${escapar(e.id_equipo)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    function filaFicha(f) {
        return `
            <tr>
                <td>
                    <div class="fw-bold">${escapar(f.nombre_ficha)}</div>
                    <small class="text-muted">${escapar(f.descripcion || '')}</small>
                </td>
                <td>${escapar(f.servicio || '—')}</td>
                <td>${escapar(f.responsable || '—')}</td>
                <td class="text-center">${escapar(f.total_mobiliario)}</td>
                <td class="text-center">${escapar(f.total_equipos)}</td>
                <td data-order="${escapar(f.fecha_creacion)}">${escapar(Formato.fecha(f.fecha_creacion))}</td>
                <td class="text-center">${badgeFicha(f.estatus)}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-tipo="ficha" data-id="${escapar(f.id_ficha)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-tipo="ficha" data-id="${escapar(f.id_ficha)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-tipo="ficha" data-id="${escapar(f.id_ficha)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    // ---------------- carga de tablas ----------------

    function pintar(tipo) {
        const c = contenedores[tipo];
        const est = estado[tipo];
        if (!c || !c.tbody) return;

        if (est.tabla) {
            est.tabla.destroy();
            est.tabla = null;
        }

        const filas = tipo === 'mobiliario'
            ? est.registros.map(filaMobiliario)
            : tipo === 'equipo'
                ? est.registros.map(filaEquipo)
                : est.registros.map(filaFicha);

        c.tbody.innerHTML = filas.join('');

        const configs = {
            mobiliario: {
                titulo: 'Mobiliario',
                orden: [[0, 'asc']],
                columnasExport: [0, 1, 2, 3, 4, 5],
                columnDefs: [
                    { targets: 2, width: '90px', className: 'text-center' },
                    { targets: 3, width: '100px', className: 'text-center' },
                    { targets: 4, width: '120px', className: 'text-center' },
                    { targets: 5, width: '110px', className: 'text-center' },
                    { targets: 6, width: '230px', orderable: false, className: 'text-center text-nowrap' },
                ],
            },
            equipo: {
                titulo: 'Equipos',
                orden: [[1, 'asc']],
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                columnDefs: [
                    { targets: 4, width: '120px', className: 'text-center' },
                    { targets: 6, width: '110px', className: 'text-center' },
                    { targets: 7, width: '230px', orderable: false, className: 'text-center text-nowrap' },
                ],
            },
            ficha: {
                titulo: 'Fichas técnicas',
                orden: [[0, 'desc']],
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                columnDefs: [
                    { targets: 3, width: '110px', className: 'text-center' },
                    { targets: 4, width: '100px', className: 'text-center' },
                    { targets: 5, width: '120px' },
                    { targets: 6, width: '110px', className: 'text-center' },
                    { targets: 7, width: '160px', orderable: false, className: 'text-center text-nowrap' },
                ],
            },
        };

        est.tabla = DataTableHelper.inicializar(c.tabla, configs[tipo]);
        est.cargada = true;
    }

    async function cargar(tipo) {
        const c = contenedores[tipo];
        if (!c || !c.tbody) return;

        const rutas = {
            mobiliario: 'api/mobiliario/listar_mobiliario',
            equipo: 'api/mobiliario/listar_equipos',
            ficha: 'api/mobiliario/listar_fichas',
        };

        try {
            estado[tipo].registros = await apiFetch(BASE_URL + rutas[tipo]);
            pintar(tipo);
        } catch (error) {
            console.error('Mobiliario (' + tipo + '):', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function recargarTodo() {
        Object.keys(estado).forEach((tipo) => {
            if (estado[tipo].cargada) cargar(tipo);
        });
        if (window.MobiliarioStats) window.MobiliarioStats.cargar();
    }

    // ---------------- detalle ----------------

    function porId(tipo, id) {
        return estado[tipo].registros.find((x) => String(x['id_' + tipo]) === String(id));
    }

    function verDetalle(tipo, id) {
        const reg = porId(tipo, id);
        if (!reg) return;

        let html = '';
        if (tipo === 'mobiliario') {
            html = `
                <div class="text-start small">
                    <p class="mb-1"><strong>Tipo:</strong> ${escapar(reg.tipo_mobiliario)}</p>
                    <p class="mb-1"><strong>Ubicación:</strong> ${escapar(reg.servicio)}</p>
                    <p class="mb-1"><strong>Cantidad:</strong> ${escapar(reg.cantidad)} · <strong>Disponible:</strong> ${escapar(reg.disponible)}</p>
                    <p class="mb-1"><strong>Estado:</strong> ${escapar(reg.estado)} · <strong>Estatus:</strong> ${escapar(reg.estatus)}</p>
                    <p class="mb-1"><strong>Marca/Modelo:</strong> ${escapar([reg.marca, reg.modelo].filter(Boolean).join(' · ') || '—')}</p>
                    <p class="mb-1"><strong>Color:</strong> ${escapar(reg.color || '—')}</p>
                    <p class="mb-1"><strong>Adquisición:</strong> ${escapar(Formato.fecha(reg.fecha_adquisicion) || '—')}</p>
                    <p class="mb-1"><strong>Descripción:</strong> ${escapar(reg.descripcion_adicional || '—')}</p>
                    <p class="mb-0"><strong>Observaciones:</strong> ${escapar(reg.observaciones || '—')}</p>
                </div>`;
        } else if (tipo === 'equipo') {
            html = `
                <div class="text-start small">
                    <p class="mb-1"><strong>Serial:</strong> ${escapar(reg.serial)}</p>
                    <p class="mb-1"><strong>Tipo:</strong> ${escapar(reg.tipo_equipo)}</p>
                    <p class="mb-1"><strong>Ubicación:</strong> ${escapar(reg.servicio)}</p>
                    <p class="mb-1"><strong>Estado:</strong> ${escapar(reg.estado)} · <strong>Estatus:</strong> ${escapar(reg.estatus)}</p>
                    <p class="mb-1"><strong>Marca/Modelo:</strong> ${escapar([reg.marca, reg.modelo].filter(Boolean).join(' · ') || '—')}</p>
                    <p class="mb-1"><strong>Ficha activa:</strong> ${escapar(reg.ficha_activa || 'Sin ficha')}</p>
                    <p class="mb-1"><strong>Adquisición:</strong> ${escapar(Formato.fecha(reg.fecha_adquisicion) || '—')}</p>
                    <p class="mb-0"><strong>Observaciones:</strong> ${escapar(reg.observaciones || '—')}</p>
                </div>`;
        } else {
            html = `
                <div class="text-start small">
                    <p class="mb-1"><strong>Responsable:</strong> ${escapar(reg.responsable)}</p>
                    <p class="mb-1"><strong>Servicio:</strong> ${escapar(reg.servicio)}</p>
                    <p class="mb-1"><strong>Ítems:</strong> ${escapar(reg.total_mobiliario)} de mobiliario · ${escapar(reg.total_equipos)} de equipo</p>
                    <p class="mb-1"><strong>Creada:</strong> ${escapar(Formato.fecha(reg.fecha_creacion))}</p>
                    <p class="mb-1"><strong>Estatus:</strong> ${Number(reg.estatus) === 1 ? 'Activa' : 'Inactiva'}</p>
                    <p class="mb-0"><strong>Descripción:</strong> ${escapar(reg.descripcion || '—')}</p>
                </div>`;
        }

        const titulo = tipo === 'ficha'
            ? reg.nombre_ficha
            : (reg.serial || (reg.tipo_mobiliario || 'Registro') + ' #' + reg['id_' + tipo]);

        Swal.fire({
            title: escapar(titulo),
            html,
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
        });
    }

    // ---------------- eliminar ----------------

    async function eliminar(tipo, id) {
        const reg = porId(tipo, id);
        const nombre = escapar(reg
            ? (tipo === 'ficha' ? reg.nombre_ficha : (tipo === 'equipo' ? reg.serial : (reg.tipo_mobiliario || 'Mobiliario') + ' #' + reg.id_mobiliario))
            : 'este registro');

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar registro?',
            `Se eliminará "${nombre}". ${
                tipo === 'ficha'
                    ? 'Se borran también sus ítems asignados.'
                    : 'Solo es posible si no está asignado en ninguna ficha técnica.'
            } Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        const llave = tipo === 'ficha' ? 'id_ficha' : (tipo === 'equipo' ? 'id_equipo' : 'id_mobiliario');
        const cuerpo = {};
        cuerpo[llave] = Number(id);

        try {
            await apiFetch(BASE_URL + 'api/mobiliario/eliminar_' + tipo, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(cuerpo),
            });

            AlertManager.success('Eliminado', 'El registro fue eliminado.');
            recargarTodo();
        } catch (error) {
            if (error.codigo === 'IN_USE') {
                AlertManager.warning('No se puede eliminar', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado',
                    'Puede haber sido eliminado. Actualizando la lista…');
                recargarTodo();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ---------------- baja lógica ----------------

    async function baja(tipo, id) {
        const reg = porId(tipo, id);
        const nombre = escapar(reg
            ? (tipo === 'equipo' ? reg.serial : (reg.tipo_mobiliario || 'Mobiliario') + ' #' + reg.id_mobiliario)
            : 'este ítem');

        const confirmacion = await AlertManager.confirm(
            '¿Dar de baja?',
            `"${nombre}" quedará con estatus INACTIVO y no podrá asignarse a fichas nuevas. `
            + 'El movimiento queda registrado en el historial.',
            'Sí, dar de baja',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        const cuerpo = { tipo_item: tipo };
        cuerpo[tipo === 'equipo' ? 'id_equipo' : 'id_mobiliario'] = Number(id);

        try {
            const r = await apiFetch(BASE_URL + 'api/mobiliario/baja', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(cuerpo),
            });

            AlertManager.success('Baja registrada', `${r.etiqueta} quedó inactivo.`);
            recargarTodo();
        } catch (error) {
            if (error.codigo === 'IN_USE' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('No se pudo dar de baja', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Ítem no encontrado',
                    'Puede haber sido eliminado. Actualizando la lista…');
                recargarTodo();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ---------------- reubicar ----------------

    const modalReubicarEl = document.getElementById('modalReubicar');
    const modalReubicar = (modalReubicarEl && typeof bootstrap !== 'undefined')
        ? new bootstrap.Modal(modalReubicarEl) : null;
    const formReubicar = document.getElementById('form-reubicar');
    let serviciosReubicar = null;

    /**
     * Limpia los estados visuales de validación de un formulario.
     * `V` no expone `limpiar()` (viene del objeto que devuelve `configurar()`
     * y este formulario no está montado como validador), así que se limpia
     * directamente. Se difiere con setTimeout 0: el helper global de Select2
     * dispara `change` después del `reset()` y dejaría el select en rojo justo
     * al abrir el modal.
     */
    function limpiarValidaciones(form) {
        setTimeout(() => {
            form.querySelectorAll('.is-valid, .is-invalid')
                .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
            form.querySelectorAll('.form-text.text-danger')
                .forEach((el) => { el.textContent = ''; });
        }, 0);
    }

    async function abrirReubicar(tipo, id) {
        const reg = porId(tipo, id);
        if (!reg || !formReubicar) return;

        formReubicar.reset();
        limpiarValidaciones(formReubicar);

        document.getElementById('ru_tipo_item').value = tipo;
        document.getElementById('ru_id_item').value = id;
        document.getElementById('ru_item').value = tipo === 'equipo'
            ? `${reg.serial || ''} · ${reg.tipo_equipo || 'Equipo'}`
            : `${reg.tipo_mobiliario || 'Mobiliario'} #${reg.id_mobiliario}`;
        document.getElementById('ru_desde').value = reg.servicio || 'Sin ubicación';

        const codigo = document.getElementById('reubicarCodigo');
        if (codigo) codigo.textContent = reg.servicio || 'Sin ubicación';

        try {
            if (!serviciosReubicar) {
                const cats = await apiFetch(BASE_URL + 'api/mobiliario/catalogos');
                serviciosReubicar = cats.servicios || [];
            }
            const sel = document.getElementById('ru_id_servicios');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            serviciosReubicar.forEach((s) => {
                const opt = document.createElement('option');
                opt.value = s.id_servicios;
                opt.textContent = s.nombre_serv;
                sel.appendChild(opt);
            });
            if (window.initSelect2) window.initSelect2(modalReubicarEl);
        } catch (e) {
            console.error('No se cargaron los servicios:', e);
        }

        if (modalReubicar) modalReubicar.show();
    }

    if (formReubicar) {
        const selServ = document.getElementById('ru_id_servicios');
        if (selServ && typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            $(selServ).on('change select2:select select2:clear', () => {
                if (selServ.value === '') V && V.mostrarError(selServ, 'Selecciona la nueva ubicación');
                else V && V.limpiarError(selServ);
            });
        }

        formReubicar.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (selServ.value === '') {
                V && V.mostrarError(selServ, 'Selecciona la nueva ubicación');
                return;
            }

            const tipo = document.getElementById('ru_tipo_item').value;
            const id = document.getElementById('ru_id_item').value;
            const cuerpo = {
                tipo_item: tipo,
                id_servicios: parseInt(selServ.value, 10),
            };
            cuerpo[tipo === 'equipo' ? 'id_equipo' : 'id_mobiliario'] = Number(id);

            try {
                const r = await apiFetch(BASE_URL + 'api/mobiliario/reubicar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(cuerpo),
                });

                if (modalReubicar) modalReubicar.hide();
                AlertManager.success('Ítem reubicado', `${r.etiqueta}: de "${r.de}" a "${r.a}".`);
                recargarTodo();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('No se pudo reubicar', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    }

    // ---------------- historial (kardex) ----------------

    const modalHistorialEl = document.getElementById('modalHistorial');
    const modalHistorial = (modalHistorialEl && typeof bootstrap !== 'undefined')
        ? new bootstrap.Modal(modalHistorialEl) : null;
    let tablaHistorial = null;

    async function cargarHistorial() {
        const tbody = document.getElementById('tbodyHistorial');
        if (!tbody) return;

        try {
            const movs = await apiFetch(BASE_URL + 'api/mobiliario/historial');

            if (tablaHistorial) {
                tablaHistorial.destroy();
                tablaHistorial = null;
            }

            tbody.innerHTML = movs.map((m) => `
                <tr>
                    <td data-order="${escapar(m.fecha_movimiento)}">${escapar(Formato.fechaHora(m.fecha_movimiento))}</td>
                    <td>
                        <div class="fw-bold">${escapar(m.etiqueta_item)}</div>
                        <small class="text-muted">${escapar(m.nombre_tipo || '')}</small>
                    </td>
                    <td class="text-center">${badgeMovimiento(m.tipo_movimiento)}</td>
                    <td>${escapar(m.responsable)}</td>
                    <td>${escapar(m.servicio_anterior || '—')}</td>
                    <td>${escapar(m.servicio_nuevo || '—')}</td>
                    <td>${escapar(m.descripcion || '—')}</td>
                </tr>`).join('');

            tablaHistorial = DataTableHelper.inicializar('#tablaHistorial', {
                titulo: 'Historial de movimientos',
                orden: [[0, 'desc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                columnDefs: [
                    { targets: 2, width: '130px', className: 'text-center' },
                ],
            });
        } catch (error) {
            console.error('Historial:', error);
            AlertManager.error('Error', error.mensaje || 'No se pudo cargar el historial.');
        }
    }

    if (modalHistorialEl) {
        modalHistorialEl.addEventListener('shown.bs.modal', cargarHistorial);
    }

    const btnHistorial = document.getElementById('btn-historial');
    if (btnHistorial && modalHistorial) {
        btnHistorial.addEventListener('click', () => modalHistorial.show());
    }

    // ---------------- pestañas (carga perezosa) ----------------

    const mapaPestanas = {
        '#cpanelMobiliario': 'mobiliario',
        '#cpanelEquipos': 'equipo',
        '#cpanelFichas': 'ficha',
    };

    document.querySelectorAll('#mobiliarioConsultarTabs button[data-bs-toggle="tab"]')
        .forEach((boton) => {
            boton.addEventListener('shown.bs.tab', function () {
                const tipo = mapaPestanas[this.dataset.bsTarget];
                if (tipo && !estado[tipo].cargada) cargar(tipo);
            });
        });

    // ---------------- botones de cabecera ----------------

    const btnRecargar = document.getElementById('btn-recargar');
    if (btnRecargar) {
        btnRecargar.addEventListener('click', () => {
            Object.keys(estado).forEach((tipo) => cargar(tipo));
            if (window.MobiliarioStats) window.MobiliarioStats.cargar();
        });
    }

    // ---------------- acciones (delegación) ----------------

    document.addEventListener('click', function (ev) {
        const detalle = ev.target.closest('.btn-detalle');
        if (detalle) return verDetalle(detalle.dataset.tipo, detalle.getAttribute('data-id'));

        const editar = ev.target.closest('.btn-editar');
        if (editar && window.MobiliarioEditar) {
            const tipo = editar.dataset.tipo;
            const id = editar.getAttribute('data-id');
            if (tipo === 'mobiliario') window.MobiliarioEditar.abrirMobiliario(id);
            else if (tipo === 'equipo') window.MobiliarioEditar.abrirEquipo(id);
            else window.MobiliarioEditar.abrirFicha(id);
            return;
        }

        const reubicar = ev.target.closest('.btn-reubicar');
        if (reubicar && !reubicar.disabled) {
            return abrirReubicar(reubicar.dataset.tipo, reubicar.getAttribute('data-id'));
        }

        const btnBaja = ev.target.closest('.btn-baja');
        if (btnBaja && !btnBaja.disabled) {
            return baja(btnBaja.dataset.tipo, btnBaja.getAttribute('data-id'));
        }

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar) return eliminar(borrar.dataset.tipo, borrar.getAttribute('data-id'));
    });

    // ---------------- API pública ----------------

    window.MobiliarioConsultar = {
        recargar: recargarTodo,
        /** Errores comunes al abrir un modal de edición (fila obsoleta). */
        avisoError(error, nombre) {
            console.error('Abrir edición (' + nombre + '):', error);
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado',
                    'Puede haber sido eliminado. Actualizando la lista…');
                recargarTodo();
            } else {
                AlertManager.error('Error', error.mensaje || 'No se pudo cargar el registro.');
            }
        },
    };

    // Primera pestaña visible.
    cargar('mobiliario');
});
