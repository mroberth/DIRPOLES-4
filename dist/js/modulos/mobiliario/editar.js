// dist/js/modulos/mobiliario/editar.js
// ------------------------------------------------------------------
// Edición de los TRES sub-flujos en MODAL (sin página nueva):
//   window.MobiliarioEditar.abrirMobiliario(id)
//   window.MobiliarioEditar.abrirEquipo(id)
//   window.MobiliarioEditar.abrirFicha(id)
// Los llama consultar.js desde la columna Acciones.
//
// Reglas permanentes:
//  - Mobiliario y equipo NUNCA cambian estatus desde aquí (la baja es otro
//    botón); la edición registra 'reubicacion' si cambia la ubicación o
//    'modificacion' en caso contrario (lo decide el backend).
//  - La ficha reemplaza sus ítems por los de las filas del modal.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const V = window.MobiliarioValidaciones;
    if (!V) return;

    const formMob   = document.getElementById('form-editar-mobiliario');
    const formEq    = document.getElementById('form-editar-equipo');
    const formFicha = document.getElementById('form-editar-ficha');
    if (!formMob && !formEq && !formFicha) return;

    const modalDe = (id) => document.getElementById(id);
    const instanciar = (el) => (el && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(el) : null;

    const modalMob   = instanciar(modalDe('modalEditarMobiliario'));
    const modalEq    = instanciar(modalDe('modalEditarEquipo'));
    const modalFicha = instanciar(modalDe('modalEditarFicha'));

    // Opciones mutables: idExcluir cambia en cada apertura.
    const opMob   = { prefijo: 'em_', idExcluir: 0 };
    const opEq    = { prefijo: 'ee_', idExcluir: 0 };
    const opFicha = { prefijo: 'ef_', idExcluir: 0 };

    const valMob   = formMob ? V.configurar(formMob, opMob) : null;
    const valEq    = formEq ? V.configurar(formEq, opEq) : null;
    const valFicha = formFicha ? V.configurar(formFicha, opFicha) : null;

    const setValor = (id, valor) => {
        const el = document.getElementById(id);
        if (el) el.value = valor == null ? '' : valor;
    };

    function llenarSelect(sel, registros, valor, texto) {
        if (!sel) return;
        sel.innerHTML = '<option value="">Seleccione…</option>';
        (registros || []).forEach((r) => {
            const opt = document.createElement('option');
            opt.value = r[valor];
            opt.textContent = r[texto];
            sel.appendChild(opt);
        });
    }

    // ---------------- catálogos (una sola vez) ----------------

    (async () => {
        try {
            const cats = await apiFetch(BASE_URL + 'api/mobiliario/catalogos');

            llenarSelect(document.getElementById('em_id_tipo_mobiliario'),
                cats.tipos_mobiliario, 'id_tipo_mobiliario', 'nombre');
            llenarSelect(document.getElementById('ee_id_tipo_equipo'),
                cats.tipos_equipo, 'id_tipo_equipo', 'nombre');
            [document.getElementById('em_id_servicios'),
             document.getElementById('ee_id_servicios'),
             document.getElementById('ef_id_servicio')].forEach((sel) =>
                llenarSelect(sel, cats.servicios, 'id_servicios', 'nombre_serv'));
            llenarSelect(document.getElementById('ef_id_empleado_responsable'),
                cats.empleados, 'id_empleado', 'nombre_completo');

            [modalDe('modalEditarMobiliario'),
             modalDe('modalEditarEquipo'),
             modalDe('modalEditarFicha')].forEach((el) => {
                if (el && window.initSelect2) window.initSelect2(el);
            });
        } catch (e) {
            console.error('No se cargaron los catálogos del módulo:', e);
        }
    })();

    // Select2 dentro de un modal cerrado calcula mal el ancho: se
    // re-inicializa cuando el modal termina de mostrarse.
    [modalDe('modalEditarMobiliario'), modalDe('modalEditarEquipo'), modalDe('modalEditarFicha')]
        .forEach((el) => {
            if (!el) return;
            el.addEventListener('shown.bs.modal', () => {
                if (window.initSelect2) window.initSelect2(el);
            });
        });

    // ---------------- filas de detalle (ficha) ----------------

    if (formFicha) {
        const btnMob = formFicha.querySelector('#btnAgregarFilaMobiliario');
        const btnEq  = formFicha.querySelector('#btnAgregarFilaEquipo');

        if (btnMob) btnMob.addEventListener('click', () => V.detalle.agregar(formFicha, 'mobiliario'));
        if (btnEq)  btnEq.addEventListener('click', () => V.detalle.agregar(formFicha, 'equipo'));

        formFicha.addEventListener('click', function (ev) {
            const btn = ev.target.closest('.btn-quitar-fila');
            if (!btn) return;
            const fila = btn.closest('.fila-detalle-mob, .fila-detalle-eq');
            if (fila) fila.remove();
            if (window.initSelect2) window.initSelect2(formFicha);
            V.detalle.mensajeError(formFicha, '');
        });
    }

    // ---------------- API pública ----------------

    window.MobiliarioEditar = {

        async abrirMobiliario(id) {
            try {
                const m = await apiFetch(
                    BASE_URL + 'api/mobiliario/obtener/mobiliario/' + encodeURIComponent(id));

                setValor('em_id_mobiliario', m.id_mobiliario);
                setValor('em_id_tipo_mobiliario', m.id_tipo_mobiliario);
                setValor('em_id_servicios', m.id_servicios);
                setValor('em_cantidad', m.cantidad);
                setValor('em_estado', m.estado);
                setValor('em_marca', m.marca);
                setValor('em_modelo', m.modelo);
                setValor('em_color', m.color);
                setValor('em_fecha_adquisicion', m.fecha_adquisicion);
                setValor('em_descripcion', m.descripcion_adicional);
                setValor('em_observaciones', m.observaciones);

                const codigo = document.getElementById('mobiliarioCodigo');
                if (codigo) {
                    codigo.textContent = `${m.tipo_mobiliario || 'Mobiliario'} #${m.id_mobiliario}`
                        + ` · ${m.servicio || 'Sin ubicación'}`;
                }

                opMob.idExcluir = Number(m.id_mobiliario);
                valMob && valMob.limpiar();

                if (modalMob) modalMob.show();
                if (window.initSelect2) window.initSelect2(modalDe('modalEditarMobiliario'));
            } catch (error) {
                window.MobiliarioConsultar && window.MobiliarioConsultar.avisoError(error, 'mobiliario');
            }
        },

        async abrirEquipo(id) {
            try {
                const e = await apiFetch(
                    BASE_URL + 'api/mobiliario/obtener/equipo/' + encodeURIComponent(id));

                setValor('ee_id_equipo', e.id_equipo);
                setValor('ee_id_tipo_equipo', e.id_tipo_equipo);
                setValor('ee_id_servicios', e.id_servicios);
                setValor('ee_serial', e.serial);
                setValor('ee_estado', e.estado);
                setValor('ee_marca', e.marca);
                setValor('ee_modelo', e.modelo);
                setValor('ee_color', e.color);
                setValor('ee_fecha_adquisicion', e.fecha_adquisicion);
                setValor('ee_descripcion', e.descripcion);
                setValor('ee_observaciones', e.observaciones);

                const codigo = document.getElementById('equipoCodigo');
                if (codigo) {
                    codigo.textContent = `${e.serial || ''} · ${e.tipo_equipo || 'Equipo'}`
                        + ` · ${e.servicio || 'Sin ubicación'}`;
                }

                opEq.idExcluir = Number(e.id_equipo);
                valEq && valEq.limpiar();

                if (modalEq) modalEq.show();
                if (window.initSelect2) window.initSelect2(modalDe('modalEditarEquipo'));
            } catch (error) {
                window.MobiliarioConsultar && window.MobiliarioConsultar.avisoError(error, 'equipo');
            }
        },

        async abrirFicha(id) {
            try {
                const f = await apiFetch(
                    BASE_URL + 'api/mobiliario/obtener/ficha/' + encodeURIComponent(id));

                setValor('ef_id_ficha', f.id_ficha);
                setValor('ef_nombre_ficha', f.nombre_ficha);
                setValor('ef_id_servicio', f.id_servicio);
                setValor('ef_id_empleado_responsable', f.id_empleado_responsable);
                setValor('ef_descripcion', f.descripcion);

                const codigo = document.getElementById('fichaCodigo');
                if (codigo) {
                    codigo.textContent = `${f.nombre_ficha || ''} · ${f.responsable || ''}`
                        + ` · ${f.servicio || ''}`;
                }

                opFicha.idExcluir = Number(f.id_ficha);
                valFicha && valFicha.limpiar();

                // Ítems libres EXCLUYendo la propia ficha (sus asignaciones
                // vuelven a estar disponibles) + filas ya asignadas.
                V.detalle.invalidarCache();
                await V.detalle.cargarItems(Number(f.id_ficha));
                V.detalle.poblar(formFicha, {
                    mobiliario: (f.detalles_mobiliario || []).map((d) => ({
                        id_mobiliario: Number(d.id_mobiliario),
                        cantidad: Number(d.cantidad),
                    })),
                    equipo: (f.detalles_equipo || []).map((d) => Number(d.id_equipo)),
                });

                if (modalFicha) modalFicha.show();
                if (window.initSelect2) window.initSelect2(modalDe('modalEditarFicha'));
            } catch (error) {
                window.MobiliarioConsultar && window.MobiliarioConsultar.avisoError(error, 'ficha');
            }
        },
    };

    // ---------------- guardado ----------------

    async function guardar(form, validador, url, datos, modal, mensaje) {
        if (validador && !await validador.validarTodo()) {
            AlertManager.error('Formulario incompleto',
                'Corrige los campos resaltados antes de continuar.');
            return;
        }

        try {
            await apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            if (modal) modal.hide();
            AlertManager.success('¡Actualizado!', mensaje);

            // Al cambiar una ficha cambia la disponibilidad de los ítems.
            V.detalle.invalidarCache();

            if (window.MobiliarioConsultar) window.MobiliarioConsultar.recargar();
            if (window.MobiliarioStats) window.MobiliarioStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    if (formMob) {
        formMob.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const datos = {};
            new FormData(formMob).forEach((valor, clave) => {
                datos[clave.replace(/^em_/, '')] = valor;
            });
            datos.id_mobiliario = parseInt(datos.id_mobiliario, 10);
            datos.id_tipo_mobiliario = parseInt(datos.id_tipo_mobiliario, 10);
            datos.id_servicios = parseInt(datos.id_servicios, 10);
            datos.cantidad = parseInt(datos.cantidad, 10);

            guardar(formMob, valMob, 'api/mobiliario/actualizar_mobiliario', datos, modalMob,
                'Los datos del mobiliario se guardaron correctamente.');
        });
    }

    if (formEq) {
        formEq.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const datos = {};
            new FormData(formEq).forEach((valor, clave) => {
                datos[clave.replace(/^ee_/, '')] = valor;
            });
            datos.id_equipo = parseInt(datos.id_equipo, 10);
            datos.id_tipo_equipo = parseInt(datos.id_tipo_equipo, 10);
            datos.id_servicios = parseInt(datos.id_servicios, 10);

            guardar(formEq, valEq, 'api/mobiliario/actualizar_equipo', datos, modalEq,
                'Los datos del equipo se guardaron correctamente.');
        });
    }

    if (formFicha) {
        formFicha.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const datos = {};
            new FormData(formFicha).forEach((valor, clave) => {
                datos[clave.replace(/^ef_/, '')] = valor;
            });
            datos.id_ficha = parseInt(datos.id_ficha, 10);
            datos.id_servicio = parseInt(datos.id_servicio, 10);
            datos.id_empleado_responsable = parseInt(datos.id_empleado_responsable, 10);

            const detalles = V.detalle.leer(formFicha);
            datos.detalles_mobiliario = detalles.detalles_mobiliario;
            datos.detalles_equipo = detalles.detalles_equipo;

            guardar(formFicha, valFicha, 'api/mobiliario/actualizar_ficha', datos, modalFicha,
                'La ficha técnica se guardó correctamente.');
        });
    }
});
