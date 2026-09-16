// dist/js/modulos/horario/validaciones.js
window.HorarioValidaciones = (function () {
    'use strict';
    const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    function marcarSelect2(campo, error) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        $(campo).next('.select2-container').find('.select2-selection')
            .toggleClass('is-invalid', error).toggleClass('is-valid', !error);
    }
    function mostrarError(campo, mensaje) {
        const error = document.getElementById(campo.id + 'Error');
        if (error) error.textContent = mensaje;
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        marcarSelect2(campo, true);
    }
    function limpiarError(campo) {
        const error = document.getElementById(campo.id + 'Error');
        if (error) error.textContent = '';
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        marcarSelect2(campo, false);
    }
    function limpiar(form) {
        form.querySelectorAll('.is-valid, .is-invalid').forEach((campo) => campo.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger').forEach((error) => { error.textContent = ''; });
        if (typeof jQuery !== 'undefined' && $.fn?.select2) $(form).find('.select2-selection').removeClass('is-valid is-invalid');
    }
    function configurar(form, selectores = {}) {
        const empleado = form.querySelector(selectores.empleado || '#id_empleado');
        const dia = form.querySelector(selectores.dia || '#dia_semana');
        const inicio = form.querySelector(selectores.inicio || '#hora_inicio');
        const fin = form.querySelector(selectores.fin || '#hora_fin');

        function validarEmpleado() {
            if (!empleado.value) { mostrarError(empleado, 'Selecciona un psicólogo.'); return false; }
            limpiarError(empleado); return true;
        }
        function validarDia() {
            if (!dias.includes(dia.value)) { mostrarError(dia, 'Selecciona un día válido.'); return false; }
            limpiarError(dia); return true;
        }
        function minutos(valor) { return Number(valor.slice(0, 2)) * 60 + Number(valor.slice(3, 5)); }
        function validarHoras() {
            if (!inicio.value || !fin.value) {
                if (!inicio.value) mostrarError(inicio, 'La hora inicial es obligatoria.');
                if (!fin.value) mostrarError(fin, 'La hora final es obligatoria.');
                return false;
            }
            const inicioMin = minutos(inicio.value);
            const finMin = minutos(fin.value);
            if (inicioMin < 420 || finMin < 420 || inicioMin > 1020 || finMin > 1020) {
                mostrarError(inicio, 'El horario debe estar entre 07:00 y 17:00.');
                mostrarError(fin, 'El horario debe estar entre 07:00 y 17:00.');
                return false;
            }
            if (finMin <= inicioMin) {
                mostrarError(fin, 'La hora final debe ser posterior a la inicial.');
                return false;
            }
            limpiarError(inicio); limpiarError(fin); return true;
        }
        [empleado, dia].forEach((campo, indice) => {
            campo.addEventListener('change', indice === 0 ? validarEmpleado : validarDia);
        });
        [empleado, dia].forEach((campo, indice) => {
            if (typeof jQuery !== 'undefined' && $.fn?.select2) $(campo).on('change select2:select select2:clear', indice === 0 ? validarEmpleado : validarDia);
        });
        [inicio, fin].forEach((campo) => campo.addEventListener('input', validarHoras));
        form.addEventListener('reset', () => setTimeout(() => limpiar(form), 0));
        return {
            validarTodo() { return validarEmpleado() && validarDia() && validarHoras(); },
            limpiar: () => limpiar(form),
        };
    }
    return { configurar };
})();
