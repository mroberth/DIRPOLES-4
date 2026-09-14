// dist/js/modulos/permisos/permisos_manager.js
// ------------------------------------------------------------------
// Gestor de permisos OPTIMIZADO:
//   1. Pide UN JSON compacto a api/permisos/matriz (roles, módulos, permisos, mapa).
//   2. Renderiza una card por rol (solo roles del sistema).
//   3. La matriz de un rol se renderiza BAJO DEMANDA al expandir su card.
//   4. Acumula cambios y los guarda en lote (api/permisos/guardar).
//
// Así el HTML inicial pesa KB en vez de MB.
// ------------------------------------------------------------------
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('dashboard-permisos');
        if (!root) return;

        const btnGuardar = document.getElementById('permisos-guardar');
        const btnCancelar = document.getElementById('permisos-cancelar');
        const lblPendientes = document.getElementById('permisos-pendientes');

        const ICONOS = { crear: 'fa-plus', leer: 'fa-eye', editar: 'fa-pen', eliminar: 'fa-trash' };

        let datos = null;             // { roles, modulos, permisos, mapa }
        const estadoInicial = {};     // estadoInicial[rol][modulo] = Set(permisos)
        const pendientes = new Map(); // 'rol-modulo' => { id_tipo_emp, id_modulo, permisos:[] }

        const esc = (t) => {
            const d = document.createElement('div');
            d.textContent = t == null ? '' : String(t);
            return d.innerHTML;
        };

        const togglesDe = (rol, modulo) =>
            root.querySelectorAll(`.permission-toggle[data-rol="${rol}"][data-modulo="${modulo}"]`);

        // ---------- utilidades de estado ----------
        function inicialDe(rol, modulo) {
            return (estadoInicial[rol] && estadoInicial[rol][modulo])
                ? Array.from(estadoInicial[rol][modulo])
                : [];
        }

        function permisosActuales(rol, modulo) {
            const out = [];
            togglesDe(rol, modulo).forEach((i) => { if (i.checked) out.push(parseInt(i.dataset.permiso, 10)); });
            return out.sort((a, b) => a - b);
        }

        const iguales = (a, b) => a.length === b.length && a.every((v, i) => v === b[i]);

        function actualizarToolbar() {
            const n = pendientes.size;
            if (lblPendientes) lblPendientes.textContent = n;
            if (btnGuardar) btnGuardar.disabled = n === 0;
            if (btnCancelar) btnCancelar.disabled = n === 0;
        }

        function actualizarPendiente(rol, modulo) {
            const actual = permisosActuales(rol, modulo);
            const inicial = inicialDe(rol, modulo).sort((a, b) => a - b);
            const clave = `${rol}-${modulo}`;

            if (!iguales(actual, inicial)) {
                pendientes.set(clave, {
                    id_tipo_emp: parseInt(rol, 10),
                    id_modulo: parseInt(modulo, 10),
                    permisos: actual,
                });
            } else {
                pendientes.delete(clave);
            }

            const badge = root.querySelector(`.permission-count[data-rol="${rol}"][data-modulo="${modulo}"]`);
            if (badge) {
                badge.textContent = actual.length;
                badge.classList.toggle('badge-dirty', pendientes.has(clave));
                badge.classList.toggle('bg-secondary', !pendientes.has(clave));
                badge.classList.toggle('bg-warning', pendientes.has(clave));
            }
            actualizarToolbar();
        }

        function sincronizarBadge(input) {
            const badge = input.closest('.form-check').querySelector('.badge');
            if (!badge) return;
            badge.classList.toggle('bg-success', input.checked);
            badge.classList.toggle('bg-light', !input.checked);
            badge.classList.toggle('text-dark', !input.checked);
        }

        // ---------- render ----------
        function moduloHTML(rol, mod) {
            const idRol = rol.id_tipo_emp;
            const idMod = mod.id_modulo;
            const asignados = inicialDe(idRol, idMod);

            const toggles = datos.permisos.map((p) => {
                const idPerm = p.id_permiso;
                const clave = String(p.clave).toLowerCase();
                const tiene = asignados.includes(Number(idPerm));
                return `
                    <div class="form-check form-switch">
                        <input class="form-check-input permission-toggle" type="checkbox" role="switch"
                               id="perm_${idRol}_${idMod}_${idPerm}" ${tiene ? 'checked' : ''}
                               data-rol="${idRol}" data-modulo="${idMod}" data-permiso="${idPerm}">
                        <label class="form-check-label" for="perm_${idRol}_${idMod}_${idPerm}">
                            <span class="badge ${tiene ? 'bg-success' : 'bg-light text-dark'}">
                                <i class="fas ${ICONOS[clave] || 'fa-circle'} me-1"></i>${esc(p.clave)}
                            </span>
                        </label>
                    </div>`;
            }).join('');

            return `
                <div class="accordion-item module-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#col${idRol}_${idMod}">
                            <i class="fas fa-folder me-2 text-warning"></i>${esc(mod.nombre)}
                            <span class="badge bg-secondary ms-2 permission-count"
                                  data-rol="${idRol}" data-modulo="${idMod}">${asignados.length}</span>
                        </button>
                    </h2>
                    <div id="col${idRol}_${idMod}" class="accordion-collapse collapse"
                         data-bs-parent="#accordionRol${idRol}">
                        <div class="accordion-body">
                            <div class="d-flex flex-wrap gap-3">${toggles}</div>
                            <div class="mt-3 pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-success select-all-perms"
                                        data-rol="${idRol}" data-modulo="${idMod}">
                                    <i class="fas fa-check-double me-1"></i>Seleccionar todos
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger deselect-all-perms"
                                        data-rol="${idRol}" data-modulo="${idMod}">
                                    <i class="fas fa-times me-1"></i>Limpiar todos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        function renderRol(rol) {
            const body = document.getElementById('cuerpoRol' + rol.id_tipo_emp);
            if (!body || body.dataset.rendered === '1') return;

            body.querySelector('.accordion').innerHTML = datos.modulos.map((m) => moduloHTML(rol, m)).join('');
            const ph = body.querySelector('.placeholder-cuerpo');
            if (ph) ph.remove();
            body.dataset.rendered = '1';
        }

        function renderRoles() {
            root.innerHTML = datos.roles.map((rol) => `
                <div class="col-xl-4 col-lg-6 mb-4">
                    <div class="card border-left-primary shadow">
                        <div class="card-header d-flex justify-content-between align-items-center"
                             role="button" data-bs-toggle="collapse" data-bs-target="#cuerpoRol${rol.id_tipo_emp}"
                             style="cursor:pointer;">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user-tag me-2"></i>${esc(rol.tipo)}
                            </h6>
                            <span class="badge bg-info">${datos.modulos.length} módulos
                                <i class="fas fa-chevron-down ms-1"></i></span>
                        </div>
                        <div id="cuerpoRol${rol.id_tipo_emp}" class="collapse cuerpo-rol" data-rol="${rol.id_tipo_emp}">
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control filter-modules"
                                           placeholder="Buscar módulo..." data-rol-id="${rol.id_tipo_emp}">
                                </div>
                                <div class="accordion accordion-flush" id="accordionRol${rol.id_tipo_emp}"></div>
                                <div class="text-center text-muted py-3 placeholder-cuerpo">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando módulos…
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <small class="text-muted"><i class="fas fa-sync-alt me-1"></i>Los cambios se guardan con el botón superior</small>
                        </div>
                    </div>
                </div>`).join('');
        }

        // ---------- guardar / cancelar ----------
        function payloadCambios() {
            return Array.from(pendientes.values()).map((v) => ({
                id_tipo_emp: v.id_tipo_emp,
                id_modulo: v.id_modulo,
                permisos: v.permisos,
            }));
        }

        async function guardarCambios() {
            if (pendientes.size === 0) return;

            const confirmacion = await AlertManager.confirm(
                'Guardar cambios',
                `Se guardarán ${pendientes.size} módulo(s) modificado(s). ¿Deseas continuar?`,
                'Sí, guardar',
                'Cancelar'
            );
            if (!confirmacion.isConfirmed) return;

            const cambios = payloadCambios();
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
            }

            try {
                await apiFetch(BASE_URL + 'api/permisos/guardar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ cambios }),
                });

                cambios.forEach((c) => {
                    const rol = String(c.id_tipo_emp);
                    const modulo = String(c.id_modulo);
                    if (!estadoInicial[rol]) estadoInicial[rol] = {};
                    estadoInicial[rol][modulo] = new Set(c.permisos);
                    pendientes.delete(`${rol}-${modulo}`);
                    const badge = root.querySelector(`.permission-count[data-rol="${rol}"][data-modulo="${modulo}"]`);
                    if (badge) badge.classList.remove('badge-dirty', 'bg-warning');
                });

                AlertManager.success('Guardado', 'Permisos actualizados correctamente.');
                if (window.PermisosStats) window.PermisosStats.cargar();
            } catch (error) {
                AlertManager.error('Error', error.mensaje || 'No se pudieron guardar los permisos.');
            } finally {
                if (btnGuardar) btnGuardar.innerHTML = '<i class="fas fa-save me-1"></i> Guardar cambios';
                actualizarToolbar();
            }
        }

        function rollback() {
            root.querySelectorAll('.cuerpo-rol[data-rendered="1"]').forEach((body) => {
                const rol = body.dataset.rol;
                body.querySelectorAll('.permission-toggle').forEach((i) => {
                    const permiso = parseInt(i.dataset.permiso, 10);
                    i.checked = estadoInicial[rol] && estadoInicial[rol][i.dataset.modulo]
                        ? estadoInicial[rol][i.dataset.modulo].has(permiso)
                        : false;
                    sincronizarBadge(i);
                });
                body.querySelectorAll('.permission-count').forEach((b) => {
                    b.textContent = inicialDe(rol, b.dataset.modulo).length;
                    b.classList.remove('badge-dirty', 'bg-warning');
                    b.classList.add('bg-secondary');
                });
            });
            pendientes.clear();
            actualizarToolbar();
        }

        async function cancelarCambios() {
            if (pendientes.size === 0) return;
            const confirmacion = await AlertManager.confirm(
                '¿Descartar cambios?',
                'Esto revertirá los cambios no guardados a su estado anterior.',
                'Sí, descartar',
                'Cancelar'
            );
            if (confirmacion.isConfirmed) rollback();
        }

        // ---------- eventos (delegados: sirven para lo renderizado luego) ----------
        root.addEventListener('change', function (ev) {
            const t = ev.target.closest('.permission-toggle');
            if (!t) return;
            sincronizarBadge(t);
            actualizarPendiente(t.dataset.rol, t.dataset.modulo);
        });

        root.addEventListener('click', function (ev) {
            const sel = ev.target.closest('.select-all-perms');
            if (sel) {
                togglesDe(sel.dataset.rol, sel.dataset.modulo).forEach((i) => { i.checked = true; sincronizarBadge(i); });
                actualizarPendiente(sel.dataset.rol, sel.dataset.modulo);
                return;
            }
            const des = ev.target.closest('.deselect-all-perms');
            if (des) {
                togglesDe(des.dataset.rol, des.dataset.modulo).forEach((i) => { i.checked = false; sincronizarBadge(i); });
                actualizarPendiente(des.dataset.rol, des.dataset.modulo);
            }
        });

        root.addEventListener('input', function (ev) {
            const filtro = ev.target.closest('.filter-modules');
            if (!filtro) return;
            const q = filtro.value.trim().toLowerCase();
            filtro.closest('.card').querySelectorAll('.module-item').forEach((item) => {
                item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        root.addEventListener('shown.bs.collapse', function (ev) {
            if (ev.target.classList.contains('cuerpo-rol')) {
                const rol = { id_tipo_emp: ev.target.dataset.rol };
                renderRol(rol);
            }
        });

        if (btnGuardar) btnGuardar.addEventListener('click', guardarCambios);
        if (btnCancelar) btnCancelar.addEventListener('click', cancelarCambios);

        // ---------- init ----------
        (async () => {
            try {
                datos = await apiFetch(BASE_URL + 'api/permisos/matriz');

                // estado inicial desde el JSON (no del DOM)
                datos.roles.forEach((rol) => {
                    const r = String(rol.id_tipo_emp);
                    estadoInicial[r] = {};
                    datos.modulos.forEach((mod) => {
                        const set = datos.mapa && datos.mapa[r] && datos.mapa[r][mod.id_modulo];
                        estadoInicial[r][String(mod.id_modulo)] = new Set((set || []).map(Number));
                    });
                });

                renderRoles();
                actualizarToolbar();
            } catch (error) {
                root.innerHTML = `<div class="col-12"><div class="alert alert-danger mb-0">
                    No se pudieron cargar los permisos: ${esc(error.mensaje || 'error')}</div></div>`;
            }
        })();
    });
})();
