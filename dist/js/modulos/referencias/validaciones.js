// dist/js/modulos/referencias/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de referencias (crear).
// Sin validación remota: todas son locales + reglas cruzadas.
//
//   const validador = ReferenciasValidaciones.configurar(form);
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Reglas del módulo (decisiones del usuario):
//  - obligatorios: beneficiario, origen (servicio+empleado), destino
//    (servicio+empleado), motivo (2-255) y observaciones (2-2000);
//  - servicio destino DISTINTO del origen y empleado destino distinto
//    del de origen.
// Si un campo no existe en el formulario, su validación se omite.
// ------------------------------------------------------------------
window.ReferenciasValidaciones = (function () {
    'use strict';

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) {
            return;
        }
        // El contenedor de Select2 se inserta justo después del <select>.
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

    function configurar(form) {
        const c = {
            id_beneficiario:      form.querySelector('#id_beneficiario'),
            id_servicio_origen:   form.querySelector('#id_servicio_origen'),
            id_empleado_origen:   form.querySelector('#id_empleado_origen'),
            id_servicio_destino:  form.querySelector('#id_servicio_destino'),
            id_empleado_destino:  form.querySelector('#id_empleado_destino'),
            motivo:               form.querySelector('#motivo'),
            observaciones:        form.querySelector('#observaciones'),
        };

        // ---------------- validadores locales ----------------

        function validarRequerido(campo, mensaje) {
            if (!campo) return true;
            if (String(campo.value || '').trim() === '') {
                mostrarError(campo, mensaje);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarServicios() {
            const okOrigen = validarRequerido(c.id_servicio_origen, 'Selecciona el servicio de origen');
            const okDestino = validarRequerido(c.id_servicio_destino, 'Selecciona el servicio destino');
            if (!okOrigen || !okDestino) return false;

            if (c.id_servicio_origen.value === c.id_servicio_destino.value) {
                mostrarError(c.id_servicio_destino, 'El servicio destino debe ser distinto al de origen');
                return false;
            }
            limpiarError(c.id_servicio_destino);
            return true;
        }

        function validarEmpleados() {
            const okOrigen = validarRequerido(c.id_empleado_origen, 'Selecciona el empleado de origen');
            const okDestino = validarRequerido(c.id_empleado_destino, 'Selecciona el empleado destino');
            if (!okOrigen || !okDestino) return false;

            if (c.id_empleado_origen.value === c.id_empleado_destino.value) {
                mostrarError(c.id_empleado_destino, 'El empleado destino debe ser distinto del de origen');
                return false;
            }
            limpiarError(c.id_empleado_destino);
            return true;
        }

        function validarMotivo() {
            if (!c.motivo) return true;
            const v = String(c.motivo.value || '').replace(/<[^>]*>?/gm, ''); // anti-XSS
            if (v.trim() === '') {
                mostrarError(c.motivo, 'El motivo es obligatorio');
                return false;
            }
            if (v.trim().length < 2 || v.trim().length > 255) {
                mostrarError(c.motivo, 'Debe tener entre 2 y 255 caracteres');
                return false;
            }
            c.motivo.value = v;
            limpiarError(c.motivo);
            return true;
        }

        function validarObservaciones() {
            if (!c.observaciones) return true;
            const v = String(c.observaciones.value || '').replace(/<[^>]*>?/gm, ''); // anti-XSS
            if (v.trim() === '') {
                mostrarError(c.observaciones, 'Las observaciones son obligatorias');
                return false;
            }
            if (v.trim().length < 2 || v.trim().length > 2000) {
                mostrarError(c.observaciones, 'Debe tener entre 2 y 2000 caracteres');
                return false;
            }
            c.observaciones.value = v;
            limpiarError(c.observaciones);
            return true;
        }

        function validarTodo() {
            return [
                validarRequerido(c.id_beneficiario, 'Selecciona un beneficiario'),
                validarServicios(),
                validarEmpleados(),
                validarMotivo(),
                validarObservaciones(),
            ].every((r) => r === true);
        }

        // ---------------- eventos en vivo ----------------
        c.id_beneficiario     && c.id_beneficiario.addEventListener('change', () =>
            validarRequerido(c.id_beneficiario, 'Selecciona un beneficiario'));
        c.id_servicio_destino && c.id_servicio_destino.addEventListener('change', validarServicios);
        c.id_empleado_destino && c.id_empleado_destino.addEventListener('change', validarEmpleados);
        c.motivo              && c.motivo.addEventListener('input', validarMotivo);
        c.observaciones       && c.observaciones.addEventListener('input', validarObservaciones);

        // Select2 dispara 'change' como evento de jQuery, que NO llega a los
        // addEventListener nativos: enlazamos también por jQuery.
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.id_beneficiario,     () => validarRequerido(c.id_beneficiario, 'Selecciona un beneficiario')],
                [c.id_servicio_origen,  validarServicios],
                [c.id_empleado_origen,  validarEmpleados],
                [c.id_servicio_destino, validarServicios],
                [c.id_empleado_destino, validarEmpleados],
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
