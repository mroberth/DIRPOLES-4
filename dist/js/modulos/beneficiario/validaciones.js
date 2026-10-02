// dist/js/modulos/beneficiario/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de beneficiario.
// El mismo archivo sirve para crear y para editar:
//
//   const validador = BeneficiarioValidaciones.configurar(form, {
//       idExcluir: 0,          // editar: id del beneficiario que se edita
//       validarRemoto: true,   // false = no consultar la API de unicidad
//   });
//
//   const ok = await validador.validarTodo();
//   validador.limpiar();
// ------------------------------------------------------------------
window.BeneficiarioValidaciones = (function () {
    'use strict';

    const RX = {
        nombre:         /^[A-Za-zÀ-ÿ\u00f1\u00d1\s]{2,100}$/,
        correo:         /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        telefono:       /^(0412|0414|0416|0422|0424|0426)\d{7}$/,
        seccion_numero: /^[1-4]\d{3}$/,
    };

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) {
            return;
        }
        const $selection = $(campo).next('.select2-container').find('.select2-selection');
        if (!$selection.length) {
            return;
        }
        $selection
            .toggleClass('is-invalid', conError)
            .toggleClass('is-valid', !conError);
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

    function configurar(form, opciones = {}) {
        const validarRemoto = opciones.validarRemoto !== false;
        const idExcluirActual = () => Number(opciones.idExcluir || 0);

        const c = {
            tipo_cedula:    form.querySelector('#tipo_cedula'),
            cedula:         form.querySelector('#cedula'),
            nombres:        form.querySelector('#nombres'),
            apellidos:      form.querySelector('#apellidos'),
            correo:         form.querySelector('#correo'),
            telefono:       form.querySelector('#telefono'),
            genero:         form.querySelector('#genero'),
            id_pnf:         form.querySelector('#id_pnf'),
            seccion_numero: form.querySelector('#seccion_numero'),
            seccion_sede:   form.querySelector('#seccion_sede'),
            seccion:        form.querySelector('#seccion'),
            fecha_nac:      form.querySelector('#fecha_nac'),
            estatus:        form.querySelector('#estatus'),
            direccion:      form.querySelector('#direccion'),
        };

        async function consultar(url, datos) {
            return apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });
        }

        // ---------------- validadores ----------------

        async function validarCedula() {
            if (!c.cedula || !c.tipo_cedula) return true;

            const cedula = (c.cedula.value || '').replace(/[^0-9]/g, '');
            c.cedula.value = cedula;
            const tipo = c.tipo_cedula.value;

            // Validar tipo y cédula por separado para marcar AMBOS si aplica.
            let tipoOk = true;
            if (tipo === '') {
                mostrarError(c.tipo_cedula, 'El tipo de cédula es obligatorio');
                tipoOk = false;
            } else {
                limpiarError(c.tipo_cedula);
            }

            let cedulaOk = true;
            if (cedula === '') {
                mostrarError(c.cedula, 'La cédula es obligatoria');
                cedulaOk = false;
            } else if (cedula.length < 6 || cedula.length > 10) {
                mostrarError(c.cedula, 'La cédula debe tener entre 6 y 10 dígitos');
                cedulaOk = false;
            }

            if (!tipoOk || !cedulaOk) {
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar('api/beneficiarios/validar_cedula', {
                        tipo_cedula: tipo, cedula, id_excluir: idExcluirActual(),
                    });
                    if (r.existe) {
                        mostrarError(c.cedula, 'La cédula ya está registrada');
                        return false;
                    }
                } catch (e) {
                    console.error('validar cédula:', e);
                    // API caída → NO se verificó nada: jamás dejar verde.
                    mostrarError(c.cedula, 'No se pudo verificar la cédula con el servidor. Intenta de nuevo.');
                    return false;
                }
            }
            limpiarError(c.cedula);
            return true;
        }

        function validarNombres() {
            if (!c.nombres) return true;
            const v = c.nombres.value;
            if (v.trim() === '') {
                mostrarError(c.nombres, 'El nombre es obligatorio');
                return false;
            }
            if (!RX.nombre.test(v)) {
                mostrarError(c.nombres, 'Solo letras, acentos y espacios (2 a 100)');
                return false;
            }
            c.nombres.value = v.replace(/<[^>]*>?/gm, '');
            limpiarError(c.nombres);
            return true;
        }

        function validarApellidos() {
            if (!c.apellidos) return true;
            const v = c.apellidos.value;
            if (v.trim() === '') {
                mostrarError(c.apellidos, 'El apellido es obligatorio');
                return false;
            }

            if (!RX.nombre.test(v)) {
                mostrarError(c.apellidos, 'Solo letras, acentos y espacios (2 a 100)');
                return false;
            }
            c.apellidos.value = v.replace(/<[^>]*>?/gm, '');
            limpiarError(c.apellidos);
            return true;
        }

        async function validarCorreo() {
            if (!c.correo) return true;
            const correo = c.correo.value.trim();
            if (correo === '') {
                mostrarError(c.correo, 'El correo es obligatorio');
                return false;
            }
            if (!RX.correo.test(correo)) {
                mostrarError(c.correo, 'Formato de correo electrónico inválido');
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar('api/beneficiarios/validar_correo', { correo, id_excluir: idExcluirActual() });
                    if (r.existe) {
                        mostrarError(c.correo, 'El correo ya está registrado');
                        return false;
                    }
                } catch (e) {
                    console.error('validar correo:', e);
                    // API caída → NO se verificó nada: jamás dejar verde.
                    mostrarError(c.correo, 'No se pudo verificar el correo con el servidor. Intenta de nuevo.');
                    return false;
                }
            }
            limpiarError(c.correo);
            return true;
        }

        async function validarTelefono() {
            if (!c.telefono) return true;
            const telefono = (c.telefono.value || '').replace(/\D/g, '');
            c.telefono.value = telefono;

            if (telefono === '') {
                mostrarError(c.telefono, 'El teléfono es obligatorio');
                return false;
            }
            if (telefono.length !== 11) {
                mostrarError(c.telefono, 'El teléfono debe tener 11 dígitos');
                return false;
            }

            if (!RX.telefono.test(telefono)) {
                mostrarError(c.telefono, 'Debe ser 0412, 0414, 0416, 0422, 0424 o 0426 + 7 dígitos');
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar('api/beneficiarios/validar_telefono', { telefono, id_excluir: idExcluirActual() });
                    if (r.existe) {
                        mostrarError(c.telefono, 'El teléfono ya está registrado');
                        return false;
                    }
                } catch (e) {
                    console.error('validar teléfono:', e);
                    // API caída → NO se verificó nada: jamás dejar verde.
                    mostrarError(c.telefono, 'No se pudo verificar el teléfono con el servidor. Intenta de nuevo.');
                    return false;
                }
            }
            limpiarError(c.telefono);
            return true;
        }

        function validarGenero() {
            if (!c.genero) return true;
            if (!c.genero.value) {
                mostrarError(c.genero, 'Selecciona el género');
                return false;
            }
            limpiarError(c.genero);
            return true;
        }

        function validarPnf() {
            if (!c.id_pnf) return true;
            if (!c.id_pnf.value) {
                mostrarError(c.id_pnf, 'Selecciona un PNF');
                return false;
            }
            limpiarError(c.id_pnf);
            return true;
        }

        function validarSeccion() {
            if (!c.seccion_numero || !c.seccion_sede || !c.seccion) return true;

            const num = (c.seccion_numero.value || '').trim();
            c.seccion_numero.value = num;
            const sede = (c.seccion_sede.value || '').trim().toUpperCase();

            let numOk = true;
            if (num === '') {
                mostrarError(c.seccion_numero, 'Número obligatorio');
                numOk = false;
            } else if (!RX.seccion_numero.test(num)) {
                mostrarError(c.seccion_numero, '4 dígitos (ej: 3102)');
                numOk = false;
            } else {
                limpiarError(c.seccion_numero);
            }

            let sedeOk = true;
            if (sede === '') {
                mostrarError(c.seccion_sede, 'Selecciona sede');
                sedeOk = false;
            } else {
                limpiarError(c.seccion_sede);
            }

            if (!numOk || !sedeOk) {
                c.seccion.value = '';
                return false;
            }

            c.seccion.value = `${num}-${sede}`;
            limpiarError(c.seccion);
            return true;
        }

        function validarFechaNac() {
            if (!c.fecha_nac) return true;
            const v = c.fecha_nac.value;
            if (!v) {
                mostrarError(c.fecha_nac, 'La fecha de nacimiento es obligatoria');
                return false;
            }
            const f = new Date(v + 'T00:00:00');
            if (isNaN(f.getTime())) {
                mostrarError(c.fecha_nac, 'Fecha inválida');
                return false;
            }
            if (f > new Date()) {
                mostrarError(c.fecha_nac, 'La fecha no puede ser futura');
                return false;
            }
            limpiarError(c.fecha_nac);
            return true;
        }

        function validarEstatus() {
            if (!c.estatus) return true;
            if (c.estatus.value === '') {
                mostrarError(c.estatus, 'El estatus es obligatorio');
                return false;
            }
            limpiarError(c.estatus);
            return true;
        }

        function validarDireccion() {
            if (!c.direccion) return true;
            let v = c.direccion.value.replace(/<[^>]*>?/gm, '');
            if (v.trim() === '') {
                limpiarError(c.direccion);
                return true;
            }
            if (v.length > 255) {
                mostrarError(c.direccion, 'La dirección no puede superar 255 caracteres');
                return false;
            }
            c.direccion.value = v;
            limpiarError(c.direccion);
            return true;
        }

        async function validarTodo() {
            const r = [
                await validarCedula(),
                validarNombres(),
                validarApellidos(),
                await validarCorreo(),
                await validarTelefono(),
                validarGenero(),
                validarPnf(),
                validarSeccion(),
                validarFechaNac(),
                validarEstatus(),
                validarDireccion(),
            ];
            return r.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        let timer;
        const debounce = (fn) => {
            clearTimeout(timer);
            timer = setTimeout(fn, 450);
        };

        c.tipo_cedula    && c.tipo_cedula.addEventListener('change', validarCedula);
        c.cedula         && c.cedula.addEventListener('input', () => debounce(validarCedula));
        c.nombres        && c.nombres.addEventListener('input', validarNombres);
        c.apellidos      && c.apellidos.addEventListener('input', validarApellidos);
        c.correo         && c.correo.addEventListener('input', () => debounce(validarCorreo));
        c.telefono       && c.telefono.addEventListener('input', () => debounce(validarTelefono));
        c.genero         && c.genero.addEventListener('change', validarGenero);
        c.id_pnf         && c.id_pnf.addEventListener('change', validarPnf);
        c.seccion_numero && c.seccion_numero.addEventListener('input', validarSeccion);
        c.seccion_sede   && c.seccion_sede.addEventListener('change', validarSeccion);
        c.fecha_nac      && c.fecha_nac.addEventListener('input', validarFechaNac);
        c.estatus        && c.estatus.addEventListener('change', validarEstatus);
        c.direccion      && c.direccion.addEventListener('input', validarDireccion);

        // Select2 dispara 'change' como evento de jQuery (no llega a addEventListener).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.tipo_cedula, validarCedula],
                [c.genero, validarGenero],
                [c.id_pnf, validarPnf],
                [c.seccion_sede, validarSeccion],
                [c.estatus, validarEstatus],
            ].forEach(function (par) {
                const campo = par[0];
                const fn = par[1];
                if (campo && $(campo).hasClass('select2')) {
                    $(campo).on('change select2:select select2:clear', fn);
                }
            });
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    return { configurar, mostrarError, limpiarError };
})();
