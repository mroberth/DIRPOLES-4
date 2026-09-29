// dist/js/modulos/mobiliario/crear.js
// ------------------------------------------------------------------
// Solo la lógica de ESTA pantalla: catálogos, tour, filas de detalle de la
// ficha, validación y envío de los TRES formularios (mobiliario, equipo y
// ficha técnica). Las validaciones y el tour viven en archivos aparte.
// Los formularios van prefijados: '' (mobiliario), 'eq_' (equipo) y 'f_'
// (ficha); el prefijo se quita del payload antes de enviarlo.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const formMob    = document.getElementById('form-mobiliario');
    const formEq     = document.getElementById('form-equipo');
    const formFicha  = document.getElementById('form-ficha');
    if (!formMob && !formEq && !formFicha) return;

    const V = window.MobiliarioValidaciones;
    if (!V) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.MobiliarioTour) {
        btnAyuda.addEventListener('click', () => window.MobiliarioTour.iniciar());
    }

    // ---------------- catálogos ----------------

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

    (async () => {
        try {
            const cats = await apiFetch(BASE_URL + 'api/mobiliario/catalogos');

            llenarSelect(document.getElementById('id_tipo_mobiliario'),
                cats.tipos_mobiliario, 'id_tipo_mobiliario', 'nombre');
            llenarSelect(document.getElementById('eq_id_tipo_equipo'),
                cats.tipos_equipo, 'id_tipo_equipo', 'nombre');
            [document.getElementById('id_servicios'),
             document.getElementById('eq_id_servicios'),
             document.getElementById('f_id_servicio')].forEach((sel) =>
                llenarSelect(sel, cats.servicios, 'id_servicios', 'nombre_serv'));
            llenarSelect(document.getElementById('f_id_empleado_responsable'),
                cats.empleados, 'id_empleado', 'nombre_completo');

            [formMob, formEq, formFicha].forEach((form) => {
                if (form && window.initSelect2) window.initSelect2(form);
            });
        } catch (e) {
            console.error('No se cargaron los catálogos del módulo:', e);
        }
    })();

    // Select2 dentro de una pestaña oculta calcula mal el ancho: se
    // re-inicializa cuando la pestaña se hace visible.
    document.querySelectorAll('#mobiliarioTabs button[data-bs-toggle="tab"]')
        .forEach((boton) => {
            boton.addEventListener('shown.bs.tab', function () {
                const destino = document.querySelector(this.dataset.bsTarget);
                if (destino && window.initSelect2) window.initSelect2(destino);
            });
        });

    // ---------------- validadores ----------------

    const validadorMob   = formMob ? V.configurar(formMob, { prefijo: '' }) : null;
    const validadorEq    = formEq ? V.configurar(formEq, { prefijo: 'eq_' }) : null;
    const validadorFicha = formFicha ? V.configurar(formFicha, { prefijo: 'f_', idExcluir: 0 }) : null;

    // ---------------- filas de detalle de la ficha ----------------

    if (formFicha && V) {
        // Ítems libres (id_excluir = 0: sin ficha propia).
        V.detalle.cargarItems(0).then(() => {
            V.detalle.limpiar(formFicha);
        }).catch((e) => console.error('No se cargaron los ítems disponibles:', e));

        const btnMob = formFicha.querySelector('#btnAgregarFilaMobiliario');
        const btnEq  = formFicha.querySelector('#btnAgregarFilaEquipo');

        if (btnMob) btnMob.addEventListener('click', () => V.detalle.agregar(formFicha, 'mobiliario'));
        if (btnEq)  btnEq.addEventListener('click', () => V.detalle.agregar(formFicha, 'equipo'));

        // Quitar fila (delegación: las filas se crean y destruyen).
        formFicha.addEventListener('click', function (ev) {
            const btn = ev.target.closest('.btn-quitar-fila');
            if (!btn) return;
            const fila = btn.closest('.fila-detalle-mob, .fila-detalle-eq');
            if (fila) fila.remove();
            if (window.initSelect2) window.initSelect2(formFicha);
            V.detalle.mensajeError(formFicha, '');
        });
    }

    // ---------------- envíos ----------------

    async function enviar(validador, url, datos, alExito) {
        if (validador && !await validador.validarTodo()) {
            AlertManager.error('Formulario incompleto',
                'Corrige los campos resaltados antes de continuar.');
            return;
        }

        try {
            const nuevo = await apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            alExito(nuevo);
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function refrescarStats() {
        if (window.MobiliarioStats) window.MobiliarioStats.cargar();
    }

    function limpiarFormulario(form, validador) {
        form.reset();
        if (validador) validador.limpiar();
        if (window.initSelect2) window.initSelect2(form);
    }

    // ---- 1) Mobiliario ----
    if (formMob) {
        formMob.addEventListener('submit', function (ev) {
            ev.preventDefault();

            const datos = Object.fromEntries(new FormData(formMob).entries());
            datos.id_tipo_mobiliario = parseInt(datos.id_tipo_mobiliario, 10);
            datos.id_servicios = parseInt(datos.id_servicios, 10);
            datos.cantidad = parseInt(datos.cantidad, 10);

            enviar(validadorMob, 'api/mobiliario/crear_mobiliario', datos, (nuevo) => {
                AlertManager.success('¡Registrado!',
                    `Mobiliario guardado: ${nuevo.resumen}.`);
                limpiarFormulario(formMob, validadorMob);
                refrescarStats();
            });
        });
    }

    // ---- 2) Equipo ----
    if (formEq) {
        formEq.addEventListener('submit', function (ev) {
            ev.preventDefault();

            const datos = {};
            new FormData(formEq).forEach((valor, clave) => {
                datos[clave.replace(/^eq_/, '')] = valor;
            });
            datos.id_tipo_equipo = parseInt(datos.id_tipo_equipo, 10);
            datos.id_servicios = parseInt(datos.id_servicios, 10);

            enviar(validadorEq, 'api/mobiliario/crear_equipo', datos, (nuevo) => {
                AlertManager.success('¡Registrado!',
                    `Equipo guardado: ${nuevo.resumen}.`);
                limpiarFormulario(formEq, validadorEq);
                refrescarStats();
            });
        });
    }

    // ---- 3) Ficha técnica ----
    if (formFicha) {
        formFicha.addEventListener('submit', function (ev) {
            ev.preventDefault();

            const datos = {};
            new FormData(formFicha).forEach((valor, clave) => {
                datos[clave.replace(/^f_/, '')] = valor;
            });
            datos.id_servicio = parseInt(datos.id_servicio, 10);
            datos.id_empleado_responsable = parseInt(datos.id_empleado_responsable, 10);

            const detalles = V.detalle.leer(formFicha);
            datos.detalles_mobiliario = detalles.detalles_mobiliario;
            datos.detalles_equipo = detalles.detalles_equipo;

            enviar(validadorFicha, 'api/mobiliario/crear_ficha', datos, (nuevo) => {
                AlertManager.success('¡Ficha registrada!', nuevo.resumen);
                limpiarFormulario(formFicha, validadorFicha);
                V.detalle.limpiar(formFicha);
                // La disponibilidad cambió: se recargan los ítems libres.
                V.detalle.invalidarCache();
                V.detalle.cargarItems(0).catch(() => {});
                refrescarStats();
            });
        });
    }
});
