// dist/js/modulos/psicologia/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de consulta psicológica.
//
//   const validador = window.PsicologiaValidaciones.configurar(form);
//   const ok = validador.validarTodo();
//   validador.limpiar();
//
// Las reglas dependen del tipo de consulta seleccionado:
//   - Diagnóstico       → id_patologia y diagnostico obligatorios.
//   - Retiro temporal   → motivo_retiro y duracion_retiro obligatorios.
//   - Cambio de carrera → motivo_cambio obligatorio.
// Los campos que no aplican al tipo actual se limpian (no se marcan en rojo).
// ------------------------------------------------------------------
window.PsicologiaValidaciones = (function () {
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
            id_beneficiario: form.querySelector('#id_beneficiario'),
            tipo_consulta:   form.querySelector('#tipo_consulta'),
            id_patologia:    form.querySelector('#id_patologia'),
            diagnostico:     form.querySelector('#diagnostico'),
            tratamiento_gen: form.querySelector('#tratamiento_gen'),
            motivo_retiro:   form.querySelector('#motivo_retiro'),
            duracion_retiro: form.querySelector('#duracion_retiro'),
            motivo_cambio:   form.querySelector('#motivo_cambio'),
            observaciones:   form.querySelector('#observaciones'),
        };

        const tipoActual = () => (c.tipo_consulta ? c.tipo_consulta.value : '');

        function validarBeneficiario() {
            if (!c.id_beneficiario) return true;
            if (!c.id_beneficiario.value) {
                mostrarError(c.id_beneficiario, 'Selecciona un beneficiario');
                return false;
            }
            limpiarError(c.id_beneficiario);
            return true;
        }

        function validarTipo() {
            if (!c.tipo_consulta) return true;
            if (!c.tipo_consulta.value) {
                mostrarError(c.tipo_consulta, 'Selecciona el tipo de formulario');
                return false;
            }
            limpiarError(c.tipo_consulta);
            return true;
        }

        function validarPatologia() {
            if (!c.id_patologia) return true;
            if (tipoActual() !== 'Diagnóstico') {
                limpiarError(c.id_patologia);
                return true;
            }
            if (!c.id_patologia.value) {
                mostrarError(c.id_patologia, 'Selecciona una patología');
                return false;
            }
            limpiarError(c.id_patologia);
            return true;
        }

        function validarDiagnostico() {
            if (!c.diagnostico) return true;
            if (tipoActual() !== 'Diagnóstico') {
                limpiarError(c.diagnostico);
                return true;
            }
            if (c.diagnostico.value.trim() === '') {
                mostrarError(c.diagnostico, 'El diagnóstico es obligatorio');
                return false;
            }
            if (c.diagnostico.value.length > MAX_TEXTO) {
                mostrarError(c.diagnostico, 'Máximo 5000 caracteres');
                return false;
            }
            limpiarError(c.diagnostico);
            return true;
        }

        function validarTratamiento() {
            if (!c.tratamiento_gen) return true;
            if (c.tratamiento_gen.value.length > MAX_TEXTO) {
                mostrarError(c.tratamiento_gen, 'Máximo 5000 caracteres');
                return false;
            }
            limpiarError(c.tratamiento_gen);
            return true;
        }

        function validarMotivoRetiro() {
            if (!c.motivo_retiro) return true;
            if (tipoActual() !== 'Retiro temporal') {
                limpiarError(c.motivo_retiro);
                return true;
            }
            if (c.motivo_retiro.value.trim() === '') {
                mostrarError(c.motivo_retiro, 'El motivo del retiro es obligatorio');
                return false;
            }
            limpiarError(c.motivo_retiro);
            return true;
        }

        function validarDuracion() {
            if (!c.duracion_retiro) return true;
            if (tipoActual() !== 'Retiro temporal') {
                limpiarError(c.duracion_retiro);
                return true;
            }
            const valor = c.duracion_retiro.value.trim();
            if (valor === '') {
                mostrarError(c.duracion_retiro, 'La duración del retiro es obligatoria');
                return false;
            }
            if (valor.length > 50) {
                mostrarError(c.duracion_retiro, 'Máximo 50 caracteres');
                return false;
            }
            limpiarError(c.duracion_retiro);
            return true;
        }

        function validarMotivoCambio() {
            if (!c.motivo_cambio) return true;
            if (tipoActual() !== 'Cambio de carrera') {
                limpiarError(c.motivo_cambio);
                return true;
            }
            const valor = c.motivo_cambio.value.trim();
            if (valor === '') {
                mostrarError(c.motivo_cambio, 'El motivo del cambio es obligatorio');
                return false;
            }
            if (valor.length > 100) {
                mostrarError(c.motivo_cambio, 'Máximo 100 caracteres');
                return false;
            }
            limpiarError(c.motivo_cambio);
            return true;
        }

        function validarObservaciones() {
            if (!c.observaciones) return true;
            if (c.observaciones.value.length > MAX_TEXTO) {
                mostrarError(c.observaciones, 'Máximo 5000 caracteres');
                return false;
            }
            limpiarError(c.observaciones);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarBeneficiario(),
                validarTipo(),
                validarPatologia(),
                validarDiagnostico(),
                validarTratamiento(),
                validarMotivoRetiro(),
                validarDuracion(),
                validarMotivoCambio(),
                validarObservaciones(),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        c.id_beneficiario && c.id_beneficiario.addEventListener('change', validarBeneficiario);
        // Al cambiar el tipo se revalida todo: los campos que dejan de aplicar se limpian.
        c.tipo_consulta   && c.tipo_consulta.addEventListener('change', validarTodo);
        c.id_patologia    && c.id_patologia.addEventListener('change', validarPatologia);
        c.diagnostico     && c.diagnostico.addEventListener('input', validarDiagnostico);
        c.tratamiento_gen && c.tratamiento_gen.addEventListener('input', validarTratamiento);
        c.motivo_retiro   && c.motivo_retiro.addEventListener('input', validarMotivoRetiro);
        c.duracion_retiro && c.duracion_retiro.addEventListener('input', validarDuracion);
        c.motivo_cambio   && c.motivo_cambio.addEventListener('input', validarMotivoCambio);
        c.observaciones   && c.observaciones.addEventListener('input', validarObservaciones);

        // Select2 dispara 'change' como evento de jQuery (no llega a addEventListener).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.id_beneficiario, validarBeneficiario],
                [c.id_patologia, validarPatologia],
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
