// dist/js/modulos/configuracion/consultar.js
// ------------------------------------------------------------------
// Lista de catálogos (DataTable) con edición por modal.
// NO hay eliminar: se mantiene la integridad del sistema.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const catalogos = window.CONFIG_CATALOGOS || {};
    const tablas = {};

    const escapar = (txt) => {
        const d = document.createElement('div');
        d.textContent = txt == null ? '' : String(txt);
        return d.innerHTML;
    };

    function filaHTML(tipo, cfg, r) {
        const celdas = cfg.campos.map((c) => `<td>${escapar(r[c.name])}</td>`).join('');
        let estatus = '';
        if (cfg.con_estatus) {
            estatus = parseInt(r.estatus, 10) === 1
                ? '<td class="text-center"><span class="badge bg-success">Activo</span></td>'
                : '<td class="text-center"><span class="badge bg-danger">Inactivo</span></td>';
        }
        const acciones = `
            <td class="text-center text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-config"
                        data-tipo="${escapar(tipo)}" data-id="${escapar(r[cfg.pk])}" title="Editar">
                    <i class="fas fa-pen"></i>
                </button>
            </td>`;
        return `<tr>${celdas}${estatus}${acciones}</tr>`;
    }

    function cargar(tipo) {
        const tbody = document.querySelector('#tabla-' + tipo + ' tbody');
        if (!tbody) return;

        return apiFetch(BASE_URL + 'api/configuracion/listar?tipo=' + encodeURIComponent(tipo))
            .then((filas) => {
                const cfg = catalogos[tipo];

                // Destruir antes de repintar (destroy() restaura el DOM viejo).
                if (tablas[tipo]) {
                    tablas[tipo].destroy();
                    tablas[tipo] = null;
                }

                tbody.innerHTML = filas.map((r) => filaHTML(tipo, cfg, r)).join('');

                tablas[tipo] = DataTableHelper.inicializar('#tabla-' + tipo, {
                    exportar: false,
                    columnDefs: [{ targets: -1, orderable: false, className: 'text-center text-nowrap', width: '80px' }],
                });
            })
            .catch((error) => {
                console.error('Configuración ' + tipo + ':', error);
                AlertManager.error('Error', error.mensaje || 'No se pudo cargar la lista.');
            });
    }

    // ---------- Editar ----------
    function prefijoDe(campo) {
        return campo.dataset.prefijo || '';
    }

    async function editar(tipo, id) {
        const cfg = catalogos[tipo];
        const form = document.getElementById('form-edit-' + tipo);
        const modalEl = document.getElementById('modal-' + tipo);
        if (!cfg || !form || !modalEl) return;

        try {
            const fila = await apiFetch(BASE_URL + 'api/configuracion/obtener/' + encodeURIComponent(tipo) + '/' + encodeURIComponent(id));

            form.querySelector('[name="id"]').value = fila[cfg.pk];

            cfg.campos.forEach((c) => {
                const campo = form.querySelector(`[name="${c.name}"]`);
                if (!campo) return;
                let val = fila[c.name] == null ? '' : String(fila[c.name]);
                const pref = c.prefijo || '';
                if (pref !== '' && val.indexOf(pref) === 0) {
                    val = val.slice(pref.length);
                } else if (pref !== '' && val.indexOf(pref.trim()) === 0) {
                    val = val.slice(pref.trim().length).trim();
                }
                campo.value = val;
            });

            if (cfg.con_estatus) {
                const est = form.querySelector('[name="estatus"]');
                if (est) est.value = String(fila.estatus);
            }

            // Excluir esta fila de la validación de unicidad.
            form._op.idExcluir = Number(fila[cfg.pk]);
            form._validador.limpiar();

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            modalEl.addEventListener('shown.bs.modal', function () {
                if (window.initSelect2) window.initSelect2(form);
            }, { once: true });
        } catch (error) {
            console.error('Editar configuración:', error);
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado', 'Puede haber sido modificado. Actualizando…');
                cargar(tipo);
            } else {
                AlertManager.error('Error', error.mensaje || 'No se pudo cargar el registro.');
            }
        }
    }

    // ---------- Configurar formularios de edición ----------
    Object.keys(catalogos).forEach((tipo) => {
        const form = document.getElementById('form-edit-' + tipo);
        if (!form) return;

        const op = { idExcluir: 0 };
        form._op = op;
        form._validador = window.ConfiguracionValidaciones.configurar(form, op);

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!(await form._validador.validarTodo())) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            const datos = Object.fromEntries(new FormData(form).entries());
            datos.catalogo = tipo;

            try {
                await apiFetch(BASE_URL + 'api/configuracion/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos),
                });

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-' + tipo)).hide();
                AlertManager.success('¡Actualizado!', 'El registro se guardó correctamente.');
                cargar(tipo);
            } catch (error) {
                if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    });

    // ---------- Pestañas: cargar la tabla al mostrarse ----------
    const activa = document.querySelector('#config-tabs .nav-link.active');
    if (activa) cargar(activa.dataset.tipo);

    document.querySelectorAll('#config-tabs [data-bs-toggle="pill"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', () => cargar(btn.dataset.tipo));
    });

    // ---------- Botón editar (delegado) ----------
    document.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.btn-editar-config');
        if (btn) editar(btn.dataset.tipo, btn.dataset.id);
    });
});
