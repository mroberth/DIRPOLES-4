// dist/js/modulos/mobiliario/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES de los tres formularios del módulo
// Mobiliario (mobiliario, equipo y ficha técnica).
// El mismo archivo sirve para crear y para editar: los ids van prefijados
// por formulario y el prefijo se pasa en `opciones`:
//
//   const validador = MobiliarioValidaciones.configurar(form, {
//       prefijo: '',          // crear mobiliario
//       // prefijo: 'eq_'    // crear equipo
//       // prefijo: 'f_'      // crear ficha
//       // prefijo: 'em_'    // editar mobiliario
//       // prefijo: 'ee_'    // editar equipo
//       // prefijo: 'ef_'    // editar ficha
//       idExcluir: 0,         // id del propio registro en edición
//   });
//
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Si un campo no existe en el formulario, su validación se omite (devuelve
// true). Regla de oro: si la API remota FALLA, el campo queda en ROJO
// ("No se pudo verificar…"); jamás se queda en verde sin verificar.
//
// Expone además `detalle` para las filas dinámicas de la ficha técnica.
// ------------------------------------------------------------------
window.MobiliarioValidaciones = (function () {
    'use strict';

    const RX = {
        // Espejo de MobiliarioModel::RX_SERIO / RX_TEXTO.
        serial:      /^[A-Za-z0-9.\-\/_]{3,100}$/,
        texto:       /^[\p{L}\p{N}\s,.\-#¿¡!?:;()°%ºª\/]+$/u,
        nombreFicha: /^[\p{L}\p{N}\s.,\-#°%ºª\/]+$/u,
    };

    const LIMITE_INT = 2147483647;

    function hoyISO() {
        const d = new Date();
        return d.getFullYear() + '-'
            + String(d.getMonth() + 1).padStart(2, '0') + '-'
            + String(d.getDate()).padStart(2, '0');
    }

    function antiXSS(valor) {
        return String(valor == null ? '' : valor).replace(/<[^>]*>?/gm, '');
    }

    // =================== pintado de errores ===================

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const $selection = $(campo).next('.select2-container').find('.select2-selection');
        if (!$selection.length) return;
        $selection.toggleClass('is-invalid', conError).toggleClass('is-valid', !conError);
    }

    function mostrarError(campo, msg) {
        if (!campo) return;
        const el = document.getElementById(campo.id + 'Error');
        if (el) el.textContent = msg;
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        marcarSelect2(campo, true);
    }

    function limpiarError(campo) {
        if (!campo) return;
        const el = document.getElementById(campo.id + 'Error');
        if (el) el.textContent = '';
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        marcarSelect2(campo, false);
    }

    function limpiarTodo(form) {
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    // =================== filas dinámicas de la ficha ===================

    const DETALLE = {
        // Caché de ítems disponibles: {mobiliario: [...], equipos: [...]}.
        cache: { clave: null, datos: { mobiliario: [], equipos: [] } },

        /** Trae los ítems libres. id_excluir = ficha propia (0 al crear). */
        async cargarItems(idExcluir = 0) {
            const clave = String(Number(idExcluir) || 0);
            if (DETALLE.cache.clave === clave) return DETALLE.cache.datos;

            const datos = await apiFetch(
                BASE_URL + 'api/mobiliario/items_disponibles?id_excluir=' + encodeURIComponent(clave)
            );
            DETALLE.cache = { clave, datos };
            return datos;
        },

        invalidarCache() {
            DETALLE.cache = { clave: null, datos: { mobiliario: [], equipos: [] } };
        },

        contenedores(form) {
            return {
                mob: form.querySelector('#contenedorDetallesMobiliario'),
                eq: form.querySelector('#contenedorDetallesEquipo'),
            };
        },

        mensajeError(form, msg) {
            const caja = form.querySelector('#detallesError');
            if (caja) caja.textContent = msg || '';
        },

        /** Elimina el texto de "sin filas" y deja el contenedor limpio. */
        vaciar(contenedor, textoVacio) {
            if (!contenedor) return;
            contenedor.innerHTML = textoVacio
                ? `<div class="text-muted small">${textoVacio}</div>`
                : '';
        },

        opcion(item, tipo) {
            const opt = document.createElement('option');
            if (tipo === 'mobiliario') {
                opt.value = item.id_mobiliario;
                opt.textContent = `${item.tipo_mobiliario || 'Mobiliario'} #${item.id_mobiliario}`
                    + ` — ${item.servicio || 'Sin ubicación'} (disponible: ${item.disponible})`;
                opt.setAttribute('data-disponible', String(item.disponible));
            } else {
                opt.value = item.id_equipo;
                opt.textContent = `${item.tipo_equipo || 'Equipo'} · ${item.serial}`
                    + ` — ${item.servicio || 'Sin ubicación'}`;
            }
            return opt;
        },

        /** Construye una fila de mobiliario (sin ids: solo clases/data-attrs). */
        filaMobiliario(items) {
            const fila = document.createElement('div');
            fila.className = 'row g-2 align-items-end mb-2 fila-detalle-mob';

            const colSel = document.createElement('div');
            colSel.className = 'col-md-7';
            const sel = document.createElement('select');
            sel.className = 'form-select select2 fila-mobiliario';
            sel.setAttribute('data-placeholder', 'Seleccione el mobiliario…');
            sel.appendChild(new Option('Seleccione…', ''));
            items.forEach((item) => sel.appendChild(DETALLE.opcion(item, 'mobiliario')));
            colSel.appendChild(sel);

            const colCant = document.createElement('div');
            colCant.className = 'col-md-3';
            const cant = document.createElement('input');
            cant.type = 'number';
            cant.className = 'form-control fila-mobiliario-cantidad';
            cant.min = '1';
            cant.step = '1';
            cant.inputMode = 'numeric';
            cant.placeholder = 'Cantidad';
            colCant.appendChild(cant);

            const colBtn = document.createElement('div');
            colBtn.className = 'col-md-2 text-end';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-danger btn-quitar-fila';
            btn.title = 'Quitar fila';
            btn.innerHTML = '<i class="fas fa-minus"></i>';
            colBtn.appendChild(btn);

            fila.append(colSel, colCant, colBtn);
            return fila;
        },

        /** Construye una fila de equipo (sin ids: solo clases/data-attrs). */
        filaEquipo(items) {
            const fila = document.createElement('div');
            fila.className = 'row g-2 align-items-end mb-2 fila-detalle-eq';

            const colSel = document.createElement('div');
            colSel.className = 'col-md-10';
            const sel = document.createElement('select');
            sel.className = 'form-select select2 fila-equipo';
            sel.setAttribute('data-placeholder', 'Seleccione el equipo…');
            sel.appendChild(new Option('Seleccione…', ''));
            items.forEach((item) => sel.appendChild(DETALLE.opcion(item, 'equipo')));
            colSel.appendChild(sel);

            const colBtn = document.createElement('div');
            colBtn.className = 'col-md-2 text-end';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-danger btn-quitar-fila';
            btn.title = 'Quitar fila';
            btn.innerHTML = '<i class="fas fa-minus"></i>';
            colBtn.appendChild(btn);

            fila.append(colSel, colBtn);
            return fila;
        },

        /** Añade una fila al contenedor del tipo indicado ('mobiliario'|'equipo'). */
        agregar(form, tipo) {
            const { mob, eq } = DETALLE.contenedores(form);
            const items = DETALLE.cache.datos;
            if (tipo === 'mobiliario') {
                if (!mob) return;
                if (mob.querySelector('.text-muted.small')) mob.innerHTML = '';
                mob.appendChild(DETALLE.filaMobiliario(items.mobiliario || []));
            } else {
                if (!eq) return;
                if (eq.querySelector('.text-muted.small')) eq.innerHTML = '';
                eq.appendChild(DETALLE.filaEquipo(items.equipos || []));
            }
            if (window.initSelect2) window.initSelect2(form);
        },

        /** Vacía las filas y restaura el texto de estado vacío. */
        limpiar(form) {
            const { mob, eq } = DETALLE.contenedores(form);
            DETALLE.vaciar(mob,
                'Sin filas: usa <strong>Agregar fila</strong> para asignar mobiliario '
                + '(la cantidad no puede superar lo disponible).');
            DETALLE.vaciar(eq,
                'Sin filas: usa <strong>Agregar fila</strong> para asignar equipos '
                + '(cada equipo solo puede estar en UNA ficha activa).');
            DETALLE.mensajeError(form, '');
            if (window.initSelect2) window.initSelect2(form);
        },

        /** Rellena las filas con los ítems ya asignados (edición). */
        poblar(form, detalles) {
            const { mob, eq } = DETALLE.contenedores(form);
            DETALLE.limpiar(form);

            ((detalles && detalles.mobiliario) || []).forEach((fila) => {
                if (!mob) return;
                if (mob.querySelector('.text-muted.small')) mob.innerHTML = '';
                const nodo = DETALLE.filaMobiliario(DETALLE.cache.datos.mobiliario || []);
                const sel = nodo.querySelector('.fila-mobiliario');
                const cant = nodo.querySelector('.fila-mobiliario-cantidad');
                sel.value = String(fila.id_mobiliario);
                cant.value = String(fila.cantidad);
                mob.appendChild(nodo);
            });

            ((detalles && detalles.equipo) || []).forEach((idEquipo) => {
                if (!eq) return;
                if (eq.querySelector('.text-muted.small')) eq.innerHTML = '';
                const nodo = DETALLE.filaEquipo(DETALLE.cache.datos.equipos || []);
                nodo.querySelector('.fila-equipo').value = String(
                    typeof idEquipo === 'object' ? idEquipo.id_equipo : idEquipo
                );
                eq.appendChild(nodo);
            });

            if (window.initSelect2) window.initSelect2(form);
        },

        /** Lee las filas del formulario para armar el payload. */
        leer(form) {
            const { mob, eq } = DETALLE.contenedores(form);
            const detalles_mobiliario = [];
            const detalles_equipo = [];

            if (mob) {
                mob.querySelectorAll('.fila-detalle-mob').forEach((fila) => {
                    const id = fila.querySelector('.fila-mobiliario');
                    const cant = fila.querySelector('.fila-mobiliario-cantidad');
                    if (!id || !cant || id.value === '') return;
                    detalles_mobiliario.push({
                        id_mobiliario: parseInt(id.value, 10),
                        cantidad: parseInt(cant.value, 10),
                    });
                });
            }

            if (eq) {
                eq.querySelectorAll('.fila-detalle-eq').forEach((fila) => {
                    const id = fila.querySelector('.fila-equipo');
                    if (!id || id.value === '') return;
                    detalles_equipo.push(parseInt(id.value, 10));
                });
            }

            return { detalles_mobiliario, detalles_equipo };
        },

        /** Valida todas las filas; escribe el error en #detallesError. */
        validar(form) {
            const { detalles_mobiliario, detalles_equipo } = DETALLE.leer(form);

            if (!detalles_mobiliario.length && !detalles_equipo.length) {
                DETALLE.mensajeError(form,
                    'La ficha debe incluir al menos un ítem de mobiliario o un equipo.');
                return false;
            }

            const vistosMob = new Set();
            for (const fila of detalles_mobiliario) {
                if (!Number.isInteger(fila.id_mobiliario) || fila.id_mobiliario < 1) {
                    DETALLE.mensajeError(form, 'Selecciona el mobiliario de cada fila.');
                    return false;
                }
                if (!Number.isInteger(fila.cantidad) || fila.cantidad < 1) {
                    DETALLE.mensajeError(form,
                        'Indica una cantidad válida (entero mayor o igual a 1) en cada fila de mobiliario.');
                    return false;
                }
                if (vistosMob.has(fila.id_mobiliario)) {
                    DETALLE.mensajeError(form,
                        'No puedes repetir el mismo mobiliario: junta las cantidades en una sola fila.');
                    return false;
                }
                vistosMob.add(fila.id_mobiliario);

                const nodo = form.querySelector('#contenedorDetallesMobiliario');
                const opciones = nodo
                    ? nodo.querySelectorAll(`.fila-mobiliario option[value="${fila.id_mobiliario}"]`)
                    : [];
                const disponible = opciones.length
                    ? Number(opciones[0].getAttribute('data-disponible'))
                    : null;

                if (disponible !== null && fila.cantidad > disponible) {
                    DETALLE.mensajeError(form,
                        `La cantidad solicitada supera lo disponible para el mobiliario #${fila.id_mobiliario}`
                        + ` (disponible: ${disponible}).`);
                    return false;
                }
            }

            const vistosEq = new Set();
            for (const id of detalles_equipo) {
                if (!Number.isInteger(id) || id < 1) {
                    DETALLE.mensajeError(form, 'Selecciona el equipo de cada fila.');
                    return false;
                }
                if (vistosEq.has(id)) {
                    DETALLE.mensajeError(form, 'No puedes repetir el mismo equipo en la ficha.');
                    return false;
                }
                vistosEq.add(id);
            }

            DETALLE.mensajeError(form, '');
            return true;
        },
    };

    // =================== configuración del formulario ===================

    function configurar(form, opciones = {}) {
        // Se lee en cada validación (no se captura): editar.js puede cambiar
        // `opciones.idExcluir` al abrir otro registro sin re-inicializar.
        const idExcluirActual = () => Number(opciones.idExcluir || 0);
        const p = () => opciones.prefijo || '';
        const el = (campo) => form.querySelector('#'+ p() + campo);

        const c = {
            tipo_mobiliario:     el('id_tipo_mobiliario'),
            tipo_equipo:         el('id_tipo_equipo'),
            servicios:           el('id_servicios'),
            servicio:            el('id_servicio'),
            cantidad:            el('cantidad'),
            estado:              el('estado'),
            fecha_adquisicion:   el('fecha_adquisicion'),
            marca:               el('marca'),
            modelo:              el('modelo'),
            color:               el('color'),
            descripcion:         el('descripcion'),
            observaciones:       el('observaciones'),
            serial:              el('serial'),
            nombre_ficha:        el('nombre_ficha'),
            empleado:            el('id_empleado_responsable'),
        };

        async function consultar(url, datos) {
            return apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });
        }

        // ---------------- validadores locales ----------------

        function validarSelectRequerido(campo, mensaje) {
            if (!campo) return true;
            if (campo.value === '') {
                mostrarError(campo, mensaje);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTipoMobiliario() {
            return validarSelectRequerido(c.tipo_mobiliario, 'Selecciona el tipo de mobiliario');
        }

        function validarTipoEquipo() {
            return validarSelectRequerido(c.tipo_equipo, 'Selecciona el tipo de equipo');
        }

        function validarServicios() {
            return validarSelectRequerido(c.servicios, 'Selecciona la ubicación (servicio)');
        }

        function validarServicioFicha() {
            return validarSelectRequerido(c.servicio, 'Selecciona el servicio de la ficha');
        }

        function validarEstado() {
            return validarSelectRequerido(c.estado, 'Selecciona el estado del ítem');
        }

        function validarCantidad() {
            if (!c.cantidad) return true;
            const v = String(c.cantidad.value || '').trim();
            if (v === '') {
                mostrarError(c.cantidad, 'La cantidad es obligatoria');
                return false;
            }
            if (!/^\d+$/.test(v) || Number(v) < 1) {
                mostrarError(c.cantidad, 'Debe ser un número entero mayor o igual a 1');
                return false;
            }
            if (Number(v) > LIMITE_INT) {
                mostrarError(c.cantidad, 'La cantidad es demasiado grande');
                return false;
            }
            limpiarError(c.cantidad);
            return true;
        }

        function validarFecha() {
            if (!c.fecha_adquisicion) return true;
            const v = c.fecha_adquisicion.value;
            if (v === '') {                       // opcional
                limpiarError(c.fecha_adquisicion);
                return true;
            }
            if (isNaN(new Date(v + 'T00:00:00').getTime())) {
                mostrarError(c.fecha_adquisicion, 'La fecha no es válida');
                return false;
            }
            if (v > hoyISO()) {
                mostrarError(c.fecha_adquisicion, 'La fecha de adquisición no puede ser futura');
                return false;
            }
            limpiarError(c.fecha_adquisicion);
            return true;
        }

        /**
         * Textos opcionales: si vienen, se sanitizan y se acotan.
         * `escribir = false` es la validación EN VIVO: hay que validar sin
         * tocar `campo.value`, porque reescribirlo en cada tecla recorta los
         * espacios finales y el usuario no podría escribir "nada que agregar".
         * El recorte (trim) solo ocurre al enviar el formulario.
         */
        function validarTextoOpcional(campo, maximo, nombre, escribir = true) {
            if (!campo) return true;
            const v = antiXSS(campo.value).trim();
            if (v === '') {
                if (escribir) campo.value = '';
                limpiarError(campo);
                return true;
            }
            if (v.length > maximo) {
                mostrarError(campo, `${nombre} no puede superar ${maximo} caracteres`);
                return false;
            }
            if (!RX.texto.test(v)) {
                mostrarError(campo, `${nombre} contiene caracteres no permitidos`);
                return false;
            }
            if (escribir) campo.value = v;
            limpiarError(campo);
            return true;
        }

        function validarTextos() {
            const resultados = [
                validarTextoOpcional(c.marca, 100, 'La marca'),
                validarTextoOpcional(c.modelo, 100, 'El modelo'),
                validarTextoOpcional(c.color, 50, 'El color'),
                validarTextoOpcional(c.descripcion, 500, 'La descripción'),
                validarTextoOpcional(c.observaciones, 500, 'Las observaciones'),
            ];
            return resultados.every((r) => r === true);
        }

        function validarSerial(escribir = true) {
            if (!c.serial) return true;
            const v = antiXSS(c.serial.value).trim();
            if (v === '') {
                mostrarError(c.serial, 'El serial es obligatorio');
                return false;
            }
            if (v.length < 3 || v.length > 100) {
                mostrarError(c.serial, 'El serial debe tener entre 3 y 100 caracteres');
                return false;
            }
            if (!RX.serial.test(v)) {
                mostrarError(c.serial,
                    'El serial solo puede contener letras, números, puntos, guiones, barras y guiones bajos');
                return false;
            }
            if (escribir) c.serial.value = v;
            limpiarError(c.serial);
            return true;
        }

        function validarNombreFicha(escribir = true) {
            if (!c.nombre_ficha) return true;
            const v = antiXSS(c.nombre_ficha.value).trim();
            if (v === '') {
                mostrarError(c.nombre_ficha, 'El nombre de la ficha es obligatorio');
                return false;
            }
            if (v.length > 100) {
                mostrarError(c.nombre_ficha, 'El nombre de la ficha no puede superar 100 caracteres');
                return false;
            }
            if (!RX.nombreFicha.test(v)) {
                mostrarError(c.nombre_ficha, 'El nombre de la ficha contiene caracteres no permitidos');
                return false;
            }
            if (escribir) c.nombre_ficha.value = v;
            limpiarError(c.nombre_ficha);
            return true;
        }

        function validarEmpleado() {
            return validarSelectRequerido(c.empleado, 'Selecciona el empleado responsable');
        }

        // ---------------- validaciones remotas ----------------

        async function validarSerialRemoto() {
            if (!c.serial || !validarSerial(false)) return true;

            try {
                const r = await consultar('api/mobiliario/validar_serial', {
                    serial: c.serial.value.trim(),
                    id_excluir: idExcluirActual(),
                });
                if (r.existe) {
                    mostrarError(c.serial, 'Ya existe un equipo registrado con ese serial');
                    return false;
                }
            } catch (e) {
                console.error('validar serial:', e);
                mostrarError(c.serial,
                    'No se pudo verificar el serial con el servidor. Intenta de nuevo.');
                return false;
            }
            limpiarError(c.serial);
            return true;
        }

        async function validarFichaRemota() {
            if (!c.empleado || !validarEmpleado()) return true;

            try {
                const r = await consultar('api/mobiliario/validar_ficha_empleado', {
                    id_empleado_responsable: Number(c.empleado.value),
                    id_excluir: idExcluirActual(),
                });
                if (r.existe) {
                    mostrarError(c.empleado,
                        'Ese empleado ya tiene una ficha técnica activa');
                    return false;
                }
            } catch (e) {
                console.error('validar ficha del empleado:', e);
                mostrarError(c.empleado,
                    'No se pudo verificar la ficha del empleado con el servidor. Intenta de nuevo.');
                return false;
            }
            limpiarError(c.empleado);
            return true;
        }

        // ---------------- validación total ----------------

        async function validarTodo() {
            const locales = [
                validarTipoMobiliario(),
                validarTipoEquipo(),
                validarServicios(),
                validarServicioFicha(),
                validarEstado(),
                validarCantidad(),
                validarFecha(),
                validarTextos(),
                validarSerial(),
                validarNombreFicha(),
                validarEmpleado(),
            ];
            if (!locales.every((r) => r === true)) return false;

            // Solo se consultan las remotas cuyo campo existe en el formulario.
            if (c.serial) {
                if (!await validarSerialRemoto()) return false;
            }
            if (c.empleado) {
                if (!await validarFichaRemota()) return false;
            }

            // Filas dinámicas (solo si el formulario tiene contenedores).
            if (form.querySelector('#contenedorDetallesMobiliario')
                || form.querySelector('#contenedorDetallesEquipo')) {
                return DETALLE.validar(form);
            }
            return true;
        }

        // ---------------- eventos en vivo ----------------
        let timer;
        const debounce = (fn) => {
            clearTimeout(timer);
            timer = setTimeout(fn, 450);
        };

        // En vivo: NUNCA se reescribe el valor mientras el usuario escribe
        // (se pasa escribir=false); el trim/sanitizado ocurre al enviar.
        const enVivo = [
            [c.cantidad, validarCantidad, 'input'],
            [c.fecha_adquisicion, validarFecha, 'input'],
            [c.marca, () => validarTextoOpcional(c.marca, 100, 'La marca', false), 'input'],
            [c.modelo, () => validarTextoOpcional(c.modelo, 100, 'El modelo', false), 'input'],
            [c.color, () => validarTextoOpcional(c.color, 50, 'El color', false), 'input'],
            [c.descripcion, () => validarTextoOpcional(c.descripcion, 500, 'La descripción', false), 'input'],
            [c.observaciones, () => validarTextoOpcional(c.observaciones, 500, 'Las observaciones', false), 'input'],
            [c.serial, () => validarSerial(false), 'input'],
            [c.nombre_ficha, () => validarNombreFicha(false), 'input'],
        ];
        enVivo.forEach(([campo, fn, evento]) => {
            if (campo) campo.addEventListener(evento, fn);
        });

        // Remota con debounce (el nombre de ficha no tiene endpoint remoto).
        if (c.serial) c.serial.addEventListener('input', () => debounce(validarSerialRemoto));

        // Select2 no emite 'change' nativo: se engancha también por jQuery.
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.tipo_mobiliario, validarTipoMobiliario],
                [c.tipo_equipo, validarTipoEquipo],
                [c.servicios, validarServicios],
                [c.servicio, validarServicioFicha],
                [c.estado, validarEstado],
                [c.empleado, () => { if (validarEmpleado()) debounce(validarFichaRemota); }],
            ].forEach(function (par) {
                const campo = par[0];
                const fn = par[1];
                if (campo && $(campo).hasClass('select2')) {
                    $(campo).on('change select2:select select2:clear', fn);
                } else if (campo) {
                    campo.addEventListener('change', fn);
                }
            });
        }

        form.addEventListener('reset', () => {
            limpiarTodo(form);
            if (form.querySelector('#contenedorDetallesMobiliario')
                || form.querySelector('#contenedorDetallesEquipo')) {
                setTimeout(() => DETALLE.limpiar(form), 0);
            }
        });

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            limpiarDetalles: () => DETALLE.limpiar(form),
            campos: c,
        };
    }

    return {
        configurar,
        mostrarError,
        limpiarError,
        detalle: DETALLE,
    };
})();
