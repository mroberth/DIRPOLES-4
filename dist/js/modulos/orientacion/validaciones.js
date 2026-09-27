// dist/js/modulos/orientacion/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de orientación.
//
//   const validador = window.OrientacionValidaciones.configurar(form);
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Reglas: beneficiario requerido; los 4 campos de texto (motivo,
// descripción, indicaciones y observaciones) son obligatorios y
// admiten hasta 5000 caracteres.
// ------------------------------------------------------------------
window.OrientacionValidaciones = (function () {
    'use strict';

    const MAX_TEXTO = 5000;

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

    function configurar(form) {
        const c = {
            id_beneficiario:        form.querySelector('#id_beneficiario'),
            motivo_orientacion:     form.querySelector('#motivo_orientacion'),
            descripcion_orientacion: form.querySelector('#descripcion_orientacion'),
            indicaciones_orientacion: form.querySelector('#indicaciones_orientacion'),
            obs_adic_orientacion:   form.querySelector('#obs_adic_orientacion'),
        };

        function validarSelectRequerido(campo, mensaje) {
            if (!campo) return true;
            if (!campo.value) {
                mostrarError(campo, mensaje);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTexto(campo) {
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'Este campo es obligatorio');
                return false;
            }
            if (valor.length > MAX_TEXTO) {
                mostrarError(campo, `Máximo ${MAX_TEXTO} caracteres`);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario'),
                validarTexto(c.motivo_orientacion),
                validarTexto(c.descripcion_orientacion),
                validarTexto(c.indicaciones_orientacion),
                validarTexto(c.obs_adic_orientacion),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        const validarBeneficiario = () => validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario');
        c.id_beneficiario && c.id_beneficiario.addEventListener('change', validarBeneficiario);
        c.motivo_orientacion && c.motivo_orientacion.addEventListener('input', () => validarTexto(c.motivo_orientacion));
        c.descripcion_orientacion && c.descripcion_orientacion.addEventListener('input', () => validarTexto(c.descripcion_orientacion));
        c.indicaciones_orientacion && c.indicaciones_orientacion.addEventListener('input', () => validarTexto(c.indicaciones_orientacion));
        c.obs_adic_orientacion && c.obs_adic_orientacion.addEventListener('input', () => validarTexto(c.obs_adic_orientacion));

        // Select2 dispara 'change' como evento de jQuery (no llega a addEventListener).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2
            && c.id_beneficiario && $(c.id_beneficiario).hasClass('select2')) {
            $(c.id_beneficiario).on('change select2:select select2:clear', validarBeneficiario);
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    return { configurar, mostrarError, limpiarError };
}());
