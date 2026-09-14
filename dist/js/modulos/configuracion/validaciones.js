// dist/js/modulos/configuracion/validaciones.js
// ------------------------------------------------------------------
// Validaciones genéricas de los formularios de Configuración.
// Las reglas vienen en los data-* de cada campo (required, min, max,
// regex, prefijo) y la unicidad se consulta a api/configuracion/validar.
//
//   const validador = ConfiguracionValidaciones.configurar(form, {
//       idExcluir: () => 0,   // en edición: id de la fila actual
//   });
//   validador.validarTodo();
// ------------------------------------------------------------------
window.ConfiguracionValidaciones = (function () {
    'use strict';

    const REGEX_CACHE = {};

    function regexDe(campo) {
        const patron = campo.dataset.regex;
        if (!patron) return null;
        const flags = campo.dataset.regexFlags || '';
        const clave = patron + '|' + flags;
        if (!REGEX_CACHE[clave]) {
            try { REGEX_CACHE[clave] = new RegExp(patron, flags); } catch (e) { return null; }
        }
        return REGEX_CACHE[clave];
    }

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const $sel = $(campo).next('.select2-container').find('.select2-selection');
        if ($sel.length) $sel.toggleClass('is-invalid', conError).toggleClass('is-valid', !conError);
    }

    function mostrarError(campo, msg) {
        const err = document.getElementById(campo.id + 'Error');
        if (err) err.textContent = msg;
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        marcarSelect2(campo, true);
    }

    function limpiarError(campo) {
        const err = document.getElementById(campo.id + 'Error');
        if (err) err.textContent = '';
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
        marcarSelect2(campo, false);
    }

    function validarCampo(campo) {
        const label = campo.dataset.label || 'El campo';
        const val = (campo.value || '').trim();
        const requerido = campo.dataset.required === '1';
        const min = parseInt(campo.dataset.min || '0', 10);
        const max = parseInt(campo.dataset.max || '0', 10);
        const re = regexDe(campo);

        if (requerido && val === '') { mostrarError(campo, label + ': campo obligatorio.'); return false; }
        if (val !== '' && min && val.length < min) { mostrarError(campo, label + ': mínimo ' + min + ' caracteres.'); return false; }
        if (val !== '' && max && val.length > max) { mostrarError(campo, label + ': máximo ' + max + ' caracteres.'); return false; }
        if (val !== '' && re && !re.test(val)) { mostrarError(campo, label + ': formato no válido.'); return false; }
        limpiarError(campo);
        return true;
    }

    function configurar(form, opciones = {}) {
        const campos = Array.from(form.querySelectorAll('[data-validar]'));
        const unicos = (form.dataset.unico || '').split(',').map((s) => s.trim()).filter(Boolean);
        const idExcluir = () => Number((typeof opciones.idExcluir === 'function' ? opciones.idExcluir() : opciones.idExcluir) || 0);
        let timer;

        campos.forEach((campo) => {
            const evento = campo.tagName === 'SELECT' ? 'change' : 'input';
            campo.addEventListener(evento, () => validarCampo(campo));
            if (campo.tagName === 'SELECT' && typeof jQuery !== 'undefined' && $(campo).hasClass('select2')) {
                $(campo).on('change select2:select select2:clear', () => validarCampo(campo));
            }
        });

        async function validarUnico() {
            const datos = Object.fromEntries(new FormData(form).entries());
            datos.catalogo = form.dataset.tipo;
            if (idExcluir() > 0) datos.id_excluir = idExcluir();
            try {
                const r = await apiFetch(BASE_URL + 'api/configuracion/validar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos),
                });
                unicos.forEach((name) => {
                    const campo = form.querySelector(`[name="${name}"]`);
                    if (!campo) return;
                    if (r.existe) {
                        mostrarError(campo, (campo.dataset.label || 'El campo') + ': ya existe un registro con esos datos.');
                    } else {
                        validarCampo(campo);
                    }
                });
            } catch (e) {
                /* silencioso: no bloquear por un fallo de la validación remota */
            }
        }

        if (unicos.length) {
            form.addEventListener('input', (ev) => {
                if (!unicos.includes(ev.target.name)) return;
                clearTimeout(timer);
                timer = setTimeout(validarUnico, 500);
            });
            form.addEventListener('change', (ev) => {
                if (!unicos.includes(ev.target.name)) return;
                clearTimeout(timer);
                timer = setTimeout(validarUnico, 300);
            });
        }

        return {
            validarTodo: () => campos.every((c) => validarCampo(c)),
            limpiar: () => {
                form.querySelectorAll('.is-valid, .is-invalid').forEach((c) => c.classList.remove('is-valid', 'is-invalid'));
                form.querySelectorAll('.form-text.text-danger').forEach((e) => (e.textContent = ''));
            },
        };
    }

    return { configurar, validarCampo, mostrarError, limpiarError };
})();
