// dist/js/modulos/jornadas/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES de los 3 formularios del módulo Jornadas:
//
//   const v = JornadasValidaciones.configurar(form, { tipo: 'jornada' });
//   const ok = v.validarTodo();     // sanea + valida todo (al enviar)
//   v.limpiar();                    // reset visual
//
// Tipos: 'jornada' (cabecera, crear y editar), 'asistente' y 'diagnostico'.
// Opción { prefijo } para formularios con ids con prefijo (det. de detalle):
//   configurar(formAsistente, { tipo: 'asistente', prefijo: 'asis_' })
//
// REGLA FRONTAL 10 (GUIA-FRONTEND): la validación EN VIVO NUNCA reescribe
// el valor del campo (se permite escribir espacios); el saneado (trim +
// quitar '<'/'>') ocurre SOLO dentro de validarTodo(), es decir, al enviar.
// Si un campo no existe en el formulario, su validación se omite.
// ------------------------------------------------------------------
window.JornadasValidaciones = (function () {
    'use strict';

    // ---------------- catálogo de reglas por formulario ----------------
    const REGLAS = {
        jornada: [
            { campo: 'nombre_jornada', tipo: 'texto', min: 3, max: 100, obligatorio: true,
              msg: 'El nombre debe tener entre 3 y 100 caracteres.' },
            { campo: 'tipo_jornada', tipo: 'select', obligatorio: true,
              msg: 'Selecciona el tipo de jornada' },
            { campo: 'aforo_maximo', tipo: 'entero', min: 1, max: 5000, obligatorio: true,
              msg: 'El aforo debe ser un número entero entre 1 y 5000.' },
            { campo: 'fecha_inicio', tipo: 'fecha', obligatorio: true,
              msg: 'Indica la fecha y hora de inicio.' },
            { campo: 'fecha_fin', tipo: 'fecha', obligatorio: true, orden: 'fecha_inicio',
              msg: 'Indica la fecha y hora de cierre.', msgOrden: 'El cierre no puede ser anterior al inicio.' },
            { campo: 'ubicacion', tipo: 'texto', min: 3, max: 255, obligatorio: true,
              msg: 'La ubicación debe tener entre 3 y 255 caracteres.' },
            { campo: 'descripcion', tipo: 'texto', max: 2000, obligatorio: false,
              msg: 'La descripción no puede superar 2000 caracteres.' },
            { campo: 'estatus', tipo: 'select', obligatorio: true,
              msg: 'Selecciona el estatus' },
        ],

        asistente: [
            { campo: 'tipo_cedula', tipo: 'select', obligatorio: true,
              msg: 'Selecciona el tipo de documento' },
            { campo: 'cedula', tipo: 'digitos', min: 5, max: 12, obligatorio: true,
              msg: 'La cédula debe tener entre 5 y 12 dígitos.' },
            { campo: 'nombres', tipo: 'texto', min: 2, max: 100, obligatorio: true,
              msg: 'Los nombres deben tener entre 2 y 100 caracteres.' },
            { campo: 'apellidos', tipo: 'texto', min: 2, max: 100, obligatorio: true,
              msg: 'Los apellidos deben tener entre 2 y 100 caracteres.' },
            { campo: 'fecha_nacimiento', tipo: 'fecha', obligatorio: true, soloFecha: true,
              futuraProhibida: true, desde: '1900-01-01',
              msg: 'Indica la fecha de nacimiento.',
              msgRango: 'La fecha no puede ser futura ni anterior a 1900.' },
            { campo: 'genero', tipo: 'select', obligatorio: true,
              msg: 'Indica el género' },
            { campo: 'tipo_paciente', tipo: 'select', obligatorio: true,
              msg: 'Selecciona el tipo de paciente' },
            { campo: 'telefono', tipo: 'digitos', min: 7, max: 12, obligatorio: true,
              msg: 'El teléfono debe tener entre 7 y 12 dígitos.' },
            { campo: 'correo', tipo: 'correo', obligatorio: false,
              msg: 'El correo no tiene un formato válido.' },
            { campo: 'direccion', tipo: 'texto', max: 255, obligatorio: false,
              msg: 'La dirección no puede superar 255 caracteres.' },
        ],

        diagnostico: [
            { campo: 'diagnostico', tipo: 'texto', min: 2, max: 2000, obligatorio: true,
              msg: 'El diagnóstico debe tener entre 2 y 2000 caracteres.' },
            { campo: 'tratamiento', tipo: 'texto', min: 2, max: 2000, obligatorio: true,
              msg: 'El tratamiento debe tener entre 2 y 2000 caracteres.' },
            { campo: 'observaciones', tipo: 'texto', max: 2000, obligatorio: false,
              msg: 'Las observaciones no pueden superar 2000 caracteres.' },
        ],
    };

    // ---------------- pintado de estados ----------------

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const $seleccion = $(campo).next('.select2-container').find('.select2-selection');
        if (!$seleccion.length) return;
        $seleccion.toggleClass('is-invalid', conError).toggleClass('is-valid', !conError);
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

    /**
     * Quita el estado de error SIN pintar en verde: se usa para campos
     * opcionales que están vacíos (no están validados, solo no tienen error).
     */
    function limpiarNeutral(campo) {
        if (!campo) return;
        const el = document.getElementById(campo.id + 'Error');
        if (el) el.textContent = '';
        campo.classList.remove('is-invalid');
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            const $seleccion = $(campo).next('.select2-container').find('.select2-selection');
            $seleccion.removeClass('is-invalid is-valid');
        }
    }

    function limpiarTodo(form) {
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    // ---------------- motor ----------------

    function configurar(form, opciones) {
        const opts = opciones || {};
        const prefijo = opts.prefijo || '';
        const tipo = opts.tipo || 'jornada';
        const reglas = (REGLAS[tipo] || []).map((r) => Object.assign({}, r, { id: prefijo + r.campo }));

        /** Campo del formulario para una regla (null = no existe → se omite). */
        function campo(regla) {
            return form.querySelector('#' + regla.id);
        }

        function vacio(campo) {
            return String(campo.value || '').trim() === '';
        }

        /** Devuelve el mensaje de error o '' si el campo está bien. */
        function revisar(regla) {
            const c = campo(regla);
            if (!c) return '';

            if (regla.tipo === 'select') {
                if (regla.obligatorio && vacio(c)) return regla.msg;
                return '';
            }

            if (vacio(c)) {
                return regla.obligatorio ? regla.msg : '';
            }

            const valor = String(c.value || '').trim();

            switch (regla.tipo) {
                case 'texto': {
                    const limpio = valor.replace(/[<>]/g, '');
                    if (regla.obligatorio && limpio.trim().length < (regla.min || 1)) return regla.msg;
                    if (!regla.obligatorio && limpio.length === 0) return '';
                    if (regla.min && limpio.trim().length < regla.min) return regla.msg;
                    if (regla.max && limpio.length > regla.max) return regla.msg;
                    return '';
                }
                case 'entero': {
                    const n = Number(valor);
                    if (!Number.isInteger(n)) return regla.msg;
                    if (regla.min != null && n < regla.min) return regla.msg;
                    if (regla.max != null && n > regla.max) return regla.msg;
                    return '';
                }
                case 'digitos': {
                    const soloDigitos = valor.replace(/\D+/g, '');
                    if (soloDigitos.length < (regla.min || 1) || soloDigitos.length > (regla.max || 999)) {
                        return regla.msg;
                    }
                    return '';
                }
                case 'correo': {
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor)) return regla.msg;
                    if (valor.length > 100) return regla.msg;
                    return '';
                }
                case 'fecha': {
                    if (regla.soloFecha) {
                        if (!/^\d{4}-\d{2}-\d{2}$/.test(valor)) return regla.msg;
                        if (regla.futuraProhibida && valor > new Date().toISOString().slice(0, 10)) {
                            return regla.msgRango || regla.msg;
                        }
                        if (regla.desde && valor < regla.desde) return regla.msgRango || regla.msg;
                        return '';
                    }
                    if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(valor)) return regla.msg;
                    if (regla.orden) {
                        const otro = campo({ id: prefijo + regla.orden });
                        if (otro && otro.value && valor < otro.value) {
                            return regla.msgOrden || regla.msg;
                        }
                    }
                    return '';
                }
                default:
                    return '';
            }
        }

        /** Sanea SOLO al enviar: quita etiquetas y espacios sobrantes. */
        function sanitizar() {
            reglas.forEach((regla) => {
                const c = campo(regla);
                if (!c) return;
                if (c.tagName === 'SELECT') return;
                if (c.type && c.type !== 'text' && c.type !== 'textarea') return;
                c.value = String(c.value || '').replace(/[<>]/g, '').trim();
            });
        }

        function validarTodo() {
            sanitizar();
            let ok = true;
            reglas.forEach((regla) => {
                const c = campo(regla);
                if (!c) return;
                const mensaje = revisar(regla);
                if (mensaje) {
                    ok = false;
                    mostrarError(c, mensaje);
                } else {
                    limpiarError(c);
                }
            });
            return ok;
        }

        // ---------------- eventos en vivo (SIN reescribir el valor) ----------------
        reglas.forEach((regla) => {
            const c = campo(regla);
            if (!c) return;

            const evaluar = () => {
                // Tras un reset no se pinta en rojo: el helper global de Select2
                // dispara 'change' en los <select> 0 ms después del reset.
                if (form.dataset.reseteando === '1') return;

                const mensaje = revisar(regla);
                if (mensaje) {
                    mostrarError(c, mensaje);
                    return;
                }
                const vacio = String(c.value || '').trim() === '';
                if (vacio && !regla.obligatorio) limpiarNeutral(c);
                else limpiarError(c);
            };

            if (c.tagName === 'SELECT') {
                c.addEventListener('change', evaluar);
                if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2 && $(c).hasClass('select2')) {
                    $(c).on('change select2:select select2:clear', evaluar);
                }
            } else {
                c.addEventListener('input', evaluar);
                c.addEventListener('change', evaluar);
            }
        });

        // El orden de fechas también se reevalúa al tocar el inicio.
        const reglaFin = reglas.find((r) => r.orden);
        if (reglaFin) {
            const inicio = campo({ id: prefijo + reglaFin.orden });
            const fin = campo(reglaFin);
            if (inicio && fin) {
                const reevaluarFin = () => fin.dispatchEvent(new Event('change'));
                inicio.addEventListener('change', reevaluarFin);
                inicio.addEventListener('input', reevaluarFin);
            }
        }

        form.addEventListener('reset', function () {
            form.dataset.reseteando = '1';
            limpiarTodo(form);
            window.setTimeout(function () {
                form.dataset.reseteando = '0';
            }, 100);
        });

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: reglas.reduce((acc, regla) => {
                acc[regla.campo] = campo(regla);
                return acc;
            }, {}),
        };
    }

    return { configurar, mostrarError, limpiarError };
})();
