// dist/js/modulos/discapacidad/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de discapacidad.
//
//   const validador = window.DiscapacidadValidaciones.configurar(form);
//   const ok = validador.validarTodo();
//   validador.limpiar();
//
// Reglas: beneficiario requerido; tipo, diagnóstico, grado, habilidades
// funcionales y observaciones obligatorios; el resto opcional pero con
// las longitudes del esquema (200/255/20).
// ------------------------------------------------------------------
window.DiscapacidadValidaciones = (function () {
    'use strict';

    // Longitudes según el esquema de la tabla `discapacidad`.
    const LONGITUDES = {
        disc_especifica: 200,
        diagnostico: 255,
        medicamentos: 255,
        habilidades_funcionales: 255,
        dispositivo_asistencia: 255,
        carnet_discapacidad: 20,
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

    function configurar(form) {
        const c = {
            id_beneficiario:          form.querySelector('#id_beneficiario'),
            tipo_discapacidad:        form.querySelector('#tipo_discapacidad'),
            disc_especifica:          form.querySelector('#disc_especifica'),
            diagnostico:              form.querySelector('#diagnostico'),
            grado:                    form.querySelector('#grado'),
            medicamentos:             form.querySelector('#medicamentos'),
            habilidades_funcionales:  form.querySelector('#habilidades_funcionales'),
            dispositivo_asistencia:   form.querySelector('#dispositivo_asistencia'),
            carnet_discapacidad:      form.querySelector('#carnet_discapacidad'),
            observaciones:            form.querySelector('#observaciones'),
            recomendaciones:          form.querySelector('#recomendaciones'),
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

        /** Texto obligatorio con límite opcional (null = sin límite). */
        function validarTextoRequerido(campo, maximo) {
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'Este campo es obligatorio');
                return false;
            }
            if (maximo !== null && valor.length > maximo) {
                mostrarError(campo, `Máximo ${maximo} caracteres`);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        /** Texto opcional: vacío = válido; si tiene valor respeta su límite. */
        function validarTextoOpcional(campo, maximo) {
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                limpiarError(campo);
                return true;
            }
            if (maximo !== null && valor.length > maximo) {
                mostrarError(campo, `Máximo ${maximo} caracteres`);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario'),
                validarSelectRequerido(c.tipo_discapacidad, 'Selecciona el tipo de discapacidad'),
                validarTextoRequerido(c.diagnostico, LONGITUDES.diagnostico),
                validarSelectRequerido(c.grado, 'Selecciona el grado'),
                validarTextoRequerido(c.habilidades_funcionales, LONGITUDES.habilidades_funcionales),
                validarTextoRequerido(c.observaciones, null),
                validarTextoOpcional(c.disc_especifica, LONGITUDES.disc_especifica),
                validarTextoOpcional(c.medicamentos, LONGITUDES.medicamentos),
                validarTextoOpcional(c.dispositivo_asistencia, LONGITUDES.dispositivo_asistencia),
                validarTextoOpcional(c.carnet_discapacidad, LONGITUDES.carnet_discapacidad),
                validarTextoOpcional(c.recomendaciones, null),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        const validarBeneficiario = () => validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario');
        c.id_beneficiario && c.id_beneficiario.addEventListener('change', validarBeneficiario);
        c.tipo_discapacidad && c.tipo_discapacidad.addEventListener('change', () => validarSelectRequerido(c.tipo_discapacidad, 'Selecciona el tipo de discapacidad'));
        c.grado && c.grado.addEventListener('change', () => validarSelectRequerido(c.grado, 'Selecciona el grado'));
        c.diagnostico && c.diagnostico.addEventListener('input', () => validarTextoRequerido(c.diagnostico, LONGITUDES.diagnostico));
        c.habilidades_funcionales && c.habilidades_funcionales.addEventListener('input', () => validarTextoRequerido(c.habilidades_funcionales, LONGITUDES.habilidades_funcionales));
        c.observaciones && c.observaciones.addEventListener('input', () => validarTextoRequerido(c.observaciones, null));
        c.disc_especifica && c.disc_especifica.addEventListener('input', () => validarTextoOpcional(c.disc_especifica, LONGITUDES.disc_especifica));
        c.medicamentos && c.medicamentos.addEventListener('input', () => validarTextoOpcional(c.medicamentos, LONGITUDES.medicamentos));
        c.dispositivo_asistencia && c.dispositivo_asistencia.addEventListener('input', () => validarTextoOpcional(c.dispositivo_asistencia, LONGITUDES.dispositivo_asistencia));
        c.carnet_discapacidad && c.carnet_discapacidad.addEventListener('input', () => validarTextoOpcional(c.carnet_discapacidad, LONGITUDES.carnet_discapacidad));
        c.recomendaciones && c.recomendaciones.addEventListener('input', () => validarTextoOpcional(c.recomendaciones, null));

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
