// dist/js/modulos/cita/validaciones.js
window.CitaValidaciones = (function () {
    'use strict';

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const seleccion = $(campo).next('.select2-container').find('.select2-selection');
        if (!seleccion.length) return;
        seleccion
            .toggleClass('is-invalid', conError)
            .toggleClass('is-valid', !conError);
    }

    function mostrarError(campo, mensaje) {
        if (!campo) return;
        const error = document.getElementById(campo.id + 'Error');
        if (error) error.textContent = mensaje;
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        marcarSelect2(campo, true);
    }

    function limpiarError(campo) {
        if (!campo) return;
        const error = document.getElementById(campo.id + 'Error');
        if (error) error.textContent = '';
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        marcarSelect2(campo, false);
    }

    function limpiar(form) {
        form.querySelectorAll('.is-valid, .is-invalid').forEach((campo) => {
            campo.classList.remove('is-valid', 'is-invalid');
        });
        form.querySelectorAll('.form-text.text-danger').forEach((error) => {
            error.textContent = '';
        });
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            $(form).find('.select2-selection').removeClass('is-valid is-invalid');
        }
    }

    function configurar(form, opciones = {}) {
        const campoEmpleado = form.querySelector(opciones.selectorEmpleado || '#id_empleado');
        const campoBeneficiario = form.querySelector(opciones.selectorBeneficiario || '#id_beneficiario');
        const campoFecha = form.querySelector(opciones.selectorFecha || '#fecha');
        const campoHora = form.querySelector(opciones.selectorHora || '#hora');
        const validarDisponibilidad = opciones.validarDisponibilidad;
        let timer;
        let ultimaConsulta = '';
        let disponibilidadValida = false;

        async function validarEmpleado() {
            if (!campoEmpleado || campoEmpleado.disabled && !campoEmpleado.value) return true;
            if (!campoEmpleado.value) {
                mostrarError(campoEmpleado, 'Selecciona un psicólogo.');
                return false;
            }
            limpiarError(campoEmpleado);
            return true;
        }

        function validarBeneficiario() {
            if (!campoBeneficiario.value) {
                mostrarError(campoBeneficiario, 'Selecciona un beneficiario.');
                return false;
            }
            limpiarError(campoBeneficiario);
            return true;
        }

        function validarFecha() {
            if (!campoFecha.value) {
                mostrarError(campoFecha, 'La fecha es obligatoria.');
                return false;
            }
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const fecha = new Date(campoFecha.value + 'T00:00:00');
            if (Number.isNaN(fecha.getTime()) || fecha < hoy) {
                mostrarError(campoFecha, 'La fecha no puede ser pasada.');
                return false;
            }
            limpiarError(campoFecha);
            return true;
        }

        function validarHora() {
            if (!campoHora.value) {
                mostrarError(campoHora, 'La hora es obligatoria.');
                return false;
            }
            if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(campoHora.value)) {
                mostrarError(campoHora, 'La hora no es válida.');
                return false;
            }
            if (Number(campoHora.value.slice(3)) % 30 !== 0) {
                mostrarError(campoHora, 'Las citas deben iniciar en punto o a media hora.');
                return false;
            }
            limpiarError(campoHora);
            return true;
        }

        async function validarDisponibilidadActual() {
            if (!validarDisponibilidad || !(await validarEmpleado()) || !validarFecha() || !validarHora()) {
                disponibilidadValida = false;
                return false;
            }
            const clave = [campoEmpleado.value, campoFecha.value, campoHora.value, opciones.idCita || ''].join('|');
            if (clave === ultimaConsulta && disponibilidadValida) return true;
            ultimaConsulta = clave;
            try {
                await validarDisponibilidad({
                    id_empleado: campoEmpleado.value,
                    fecha: campoFecha.value,
                    hora: campoHora.value,
                    id_cita: opciones.idCita || undefined,
                });
                limpiarError(campoHora);
                disponibilidadValida = true;
                return true;
            } catch (error) {
                mostrarError(campoHora, error.mensaje || 'La hora no está disponible.');
                disponibilidadValida = false;
                return false;
            }
        }

        function programarDisponibilidad() {
            clearTimeout(timer);
            disponibilidadValida = false;
            timer = setTimeout(() => validarDisponibilidadActual(), 400);
        }

        campoEmpleado?.addEventListener('change', () => {
            limpiarError(campoEmpleado);
            programarDisponibilidad();
        });
        campoBeneficiario.addEventListener('change', validarBeneficiario);
        campoFecha.addEventListener('change', () => {
            validarFecha();
            programarDisponibilidad();
        });
        campoHora.addEventListener('input', () => {
            validarHora();
            programarDisponibilidad();
        });

        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            $(campoEmpleado).on('change select2:select select2:clear', validarEmpleado);
            $(campoBeneficiario).on('change select2:select select2:clear', validarBeneficiario);
        }

        form.addEventListener('reset', () => {
            disponibilidadValida = false;
            ultimaConsulta = '';
            setTimeout(() => limpiar(form), 0);
        });

        return {
            async validarTodo() {
                const resultados = [
                    await validarEmpleado(),
                    validarBeneficiario(),
                    validarFecha(),
                    validarHora(),
                    await validarDisponibilidadActual(),
                ];
                return resultados.every(Boolean);
            },
            limpiar: () => limpiar(form),
            invalidarDisponibilidad: () => {
                disponibilidadValida = false;
                ultimaConsulta = '';
            },
        };
    }

    return { configurar };
})();
