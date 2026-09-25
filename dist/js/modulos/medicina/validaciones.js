// dist/js/modulos/medicina/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de consulta médica.
//
//   const validador = window.MedicinaValidaciones.configurar(form);
//   validador.setInsumos(catalogoInsumos);   // tras cargar api/medicina/catalogos
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Reglas: beneficiario, patología, estatura (0.50–2.50 m), peso (2–300 kg),
// tipo de sangre, motivo/diagnóstico/tratamiento (requeridos, ≤255) y
// observaciones (opcional, ≤255). Los insumos son opcionales, pero cada fila
// agregada debe estar completa, sin duplicados y dentro del stock disponible.
// ------------------------------------------------------------------
window.MedicinaValidaciones = (function () {
    'use strict';

    const MAX_TEXTO = 255;
    const RANGO_ESTATURA = [0.50, 2.50];
    const RANGO_PESO = [2, 300];

    // Catálogo de insumos aptos (lo inyecta crear.js tras llamar a la API).
    let insumosCatalogo = [];

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
            id_patologia:    form.querySelector('#id_patologia'),
            estatura:        form.querySelector('#estatura'),
            peso:            form.querySelector('#peso'),
            tipo_sangre:     form.querySelector('#tipo_sangre'),
            motivo_visita:   form.querySelector('#motivo_visita'),
            diagnostico:     form.querySelector('#diagnostico'),
            tratamiento:     form.querySelector('#tratamiento'),
            observaciones:   form.querySelector('#observaciones'),
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

        function validarNumero(campo, mensajeVacio, rango, etiqueta) {
            if (!campo) return true;
            const crudo = campo.value.trim();
            if (crudo === '') {
                mostrarError(campo, mensajeVacio);
                return false;
            }
            // Aún incompleto (termina en separador decimal): no marcar al tipear.
            if (/[.,]$/.test(crudo)) return true;
            if (!/^\d+([.,]\d{1,2})?$/.test(crudo)) {
                mostrarError(campo, 'Usa solo números con hasta 2 decimales (ej: 1.70).');
                return false;
            }
            const valor = parseFloat(crudo.replace(',', '.'));
            if (Number.isNaN(valor) || valor < rango[0] || valor > rango[1]) {
                mostrarError(campo, `${etiqueta} debe estar entre ${rango[0]} y ${rango[1]}.`);
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTexto(campo, requerido) {
            if (!campo) return true;
            const valor = campo.value.trim();
            if (requerido && valor === '') {
                mostrarError(campo, 'Este campo es obligatorio');
                return false;
            }
            if (valor.length > MAX_TEXTO) {
                mostrarError(campo, 'Máximo 255 caracteres');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function mensajeErrorInsumos(msg) {
            const cont = document.getElementById('insumosError');
            if (cont) cont.textContent = msg;
            return false;
        }

        function limpiarErrorInsumos() {
            const cont = document.getElementById('insumosError');
            if (cont) cont.textContent = '';
            form.querySelectorAll('#lista_insumos .is-invalid')
                .forEach((el) => el.classList.remove('is-invalid'));
        }

        function validarInsumos() {
            const tbody = document.getElementById('lista_insumos');
            if (!tbody) return true;
            const filas = Array.from(tbody.querySelectorAll('tr'));
            if (!filas.length) {
                limpiarErrorInsumos();
                return true;
            }

            const vistos = new Set();
            for (const fila of filas) {
                const select = fila.querySelector('.select-insumo');
                const cantidad = fila.querySelector('.input-cantidad');
                if (!select || !cantidad) continue;

                const id = select.value;
                if (!id) {
                    select.classList.add('is-invalid');
                    marcarSelect2(select, true);
                    return mensajeErrorInsumos('Selecciona el insumo en cada fila o quítala.');
                }
                if (vistos.has(id)) {
                    select.classList.add('is-invalid');
                    marcarSelect2(select, true);
                    return mensajeErrorInsumos('No repitas el mismo insumo: indica la cantidad total en una sola fila.');
                }
                vistos.add(id);

                const n = parseInt(cantidad.value, 10);
                if (!Number.isInteger(n) || n < 1) {
                    cantidad.classList.add('is-invalid');
                    return mensajeErrorInsumos('La cantidad de cada insumo debe ser un entero mayor a 0.');
                }

                const info = insumosCatalogo.find((i) => String(i.id_insumo) === id);
                if (!info) {
                    select.classList.add('is-invalid');
                    marcarSelect2(select, true);
                    return mensajeErrorInsumos('El insumo seleccionado ya no está disponible.');
                }
                if (n > Number(info.cantidad)) {
                    cantidad.classList.add('is-invalid');
                    return mensajeErrorInsumos(`Stock insuficiente para "${info.nombre_insumo}" (disponible: ${info.cantidad}).`);
                }
            }

            limpiarErrorInsumos();
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario'),
                validarSelectRequerido(c.id_patologia, 'Selecciona una patología'),
                validarNumero(c.estatura, 'La estatura es obligatoria', RANGO_ESTATURA, 'La estatura'),
                validarNumero(c.peso, 'El peso es obligatorio', RANGO_PESO, 'El peso'),
                validarSelectRequerido(c.tipo_sangre, 'Selecciona el tipo de sangre'),
                validarTexto(c.motivo_visita, true),
                validarTexto(c.diagnostico, true),
                validarTexto(c.tratamiento, true),
                validarTexto(c.observaciones, false),
                validarInsumos(),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        c.id_beneficiario && c.id_beneficiario.addEventListener('change', () => validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario'));
        c.id_patologia    && c.id_patologia.addEventListener('change', () => validarSelectRequerido(c.id_patologia, 'Selecciona una patología'));
        c.estatura        && c.estatura.addEventListener('input', () => validarNumero(c.estatura, 'La estatura es obligatoria', RANGO_ESTATURA, 'La estatura'));
        c.peso            && c.peso.addEventListener('input', () => validarNumero(c.peso, 'El peso es obligatorio', RANGO_PESO, 'El peso'));
        c.tipo_sangre     && c.tipo_sangre.addEventListener('change', () => validarSelectRequerido(c.tipo_sangre, 'Selecciona el tipo de sangre'));
        c.motivo_visita   && c.motivo_visita.addEventListener('input', () => validarTexto(c.motivo_visita, true));
        c.diagnostico     && c.diagnostico.addEventListener('input', () => validarTexto(c.diagnostico, true));
        c.tratamiento     && c.tratamiento.addEventListener('input', () => validarTexto(c.tratamiento, true));
        c.observaciones   && c.observaciones.addEventListener('input', () => validarTexto(c.observaciones, false));

        // Select2 dispara 'change' como evento de jQuery (no llega a addEventListener).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.id_beneficiario, () => validarSelectRequerido(c.id_beneficiario, 'Selecciona un beneficiario')],
                [c.id_patologia, () => validarSelectRequerido(c.id_patologia, 'Selecciona una patología')],
            ].forEach(function (par) {
                const campo = par[0];
                const fn = par[1];
                if (campo && $(campo).hasClass('select2')) {
                    $(campo).on('change select2:select select2:clear', fn);
                }
            });
        }

        // Filas de insumos (delegación: las filas se crean y borran dinámicamente).
        const tbody = document.getElementById('lista_insumos');
        if (tbody) {
            // 'change' para el select (incluido Select2) y 'input' para la cantidad.
            tbody.addEventListener('change', validarInsumos);
            tbody.addEventListener('input', (ev) => {
                if (ev.target && ev.target.classList.contains('input-cantidad')) validarInsumos();
            });
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            validarInsumos,
            setInsumos: (lista) => { insumosCatalogo = Array.isArray(lista) ? lista : []; },
            campos: c,
        };
    }

    return { configurar, mostrarError, limpiarError };
})();
