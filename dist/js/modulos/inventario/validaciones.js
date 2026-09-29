// dist/js/modulos/inventario/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de insumo.
// El mismo archivo sirve para crear y para editar:
//
//   const validador = InventarioValidaciones.configurar(form, {
//       idExcluir: 0,            // editar: id del insumo (para no marcarlo duplicado)
//       exigirFechaFutura: true, // crear: la fecha no puede ser pasada (editar: false)
//   });
//
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Si un campo no existe en el formulario, su validación se omite (devuelve true).
// Regla de oro: si la API de unicidad FALLA, el campo queda en ROJO
// ("No se pudo verificar…"); jamás se queda en verde sin verificar.
// ------------------------------------------------------------------
window.InventarioValidaciones = (function () {
    'use strict';

    const RX = {
        nombre:      /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\.\-]{2,100}$/,
        descripcion: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s,.\-#]{2,250}$/,
    };

    function hoyISO() {
        const d = new Date();
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const dia = String(d.getDate()).padStart(2, '0');
        return d.getFullYear() + '-' + mes + '-' + dia;
    }

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

    function configurar(form, opciones = {}) {
        // Se lee en cada validación (no se captura): así editar.js puede
        // fijar `opciones.idExcluir` al abrir el modal con otro insumo.
        const idExcluirActual = () => Number(opciones.idExcluir || 0);
        const exigirFechaFutura = () => opciones.exigirFechaFutura !== false;

        const c = {
            nombre_insumo:     form.querySelector('#nombre_insumo'),
            tipo_insumo:       form.querySelector('#tipo_insumo'),
            id_presentacion:   form.querySelector('#id_presentacion'),
            fecha_vencimiento: form.querySelector('#fecha_vencimiento'),
            descripcion:       form.querySelector('#descripcion'),
        };

        async function consultar(url, datos) {
            return apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });
        }

        // ---------------- validadores locales ----------------

        function validarNombreInsumo() {
            if (!c.nombre_insumo) return true;
            let v = c.nombre_insumo.value.replace(/<[^>]*>?/gm, ''); // anti-XSS
            if (v.trim() === '') {
                mostrarError(c.nombre_insumo, 'El nombre del insumo es obligatorio');
                return false;
            }
            if (!RX.nombre.test(v)) {
                mostrarError(c.nombre_insumo, 'Solo letras, números, espacios, puntos y guiones (2 a 100 caracteres)');
                return false;
            }
            c.nombre_insumo.value = v;
            limpiarError(c.nombre_insumo);
            return true;
        }

        function validarTipoInsumo() {
            if (!c.tipo_insumo) return true;
            if (c.tipo_insumo.value === '') {
                mostrarError(c.tipo_insumo, 'Selecciona un tipo de insumo');
                return false;
            }
            limpiarError(c.tipo_insumo);
            return true;
        }

        function validarPresentacion() {
            if (!c.id_presentacion) return true;
            if (c.id_presentacion.value === '') {
                mostrarError(c.id_presentacion, 'Selecciona la presentación');
                return false;
            }
            limpiarError(c.id_presentacion);
            return true;
        }

        function validarFechaVencimiento() {
            if (!c.fecha_vencimiento) return true;
            const v = c.fecha_vencimiento.value;
            if (v === '') {
                mostrarError(c.fecha_vencimiento, 'La fecha de vencimiento es obligatoria');
                return false;
            }
            const f = new Date(v + 'T00:00:00');
            if (isNaN(f.getTime())) {
                mostrarError(c.fecha_vencimiento, 'La fecha no es válida');
                return false;
            }
            if (exigirFechaFutura() && v < hoyISO()) {
                mostrarError(c.fecha_vencimiento, 'La fecha de vencimiento no puede ser pasada');
                return false;
            }
            limpiarError(c.fecha_vencimiento);
            return true;
        }

        function validarDescripcion() {
            if (!c.descripcion) return true;
            let v = c.descripcion.value.replace(/<[^>]*>?/gm, ''); // anti-XSS
            if (v.trim() === '') {
                mostrarError(c.descripcion, 'La descripción es obligatoria');
                return false;
            }
            if (!RX.descripcion.test(v)) {
                mostrarError(c.descripcion, 'Solo letras, números, espacios, comas, puntos, guiones y # (2 a 250 caracteres)');
                return false;
            }
            c.descripcion.value = v;
            limpiarError(c.descripcion);
            return true;
        }

        // ---------------- validación remota (unicidad) ----------------

        async function validarUnicidad() {
            if (!c.nombre_insumo || !c.tipo_insumo || !c.id_presentacion || !c.fecha_vencimiento) {
                return true;
            }

            // Si algún campo local está incompleto/inválido, sus validadores ya
            // lo marcaron: no se consulta la API (y aquí no se afirma nada).
            const localOk =
                RX.nombre.test(c.nombre_insumo.value.trim()) &&
                c.tipo_insumo.value !== '' &&
                c.id_presentacion.value !== '' &&
                c.fecha_vencimiento.value !== '';
            if (!localOk) {
                return true;
            }

            try {
                const r = await consultar('api/inventario/validar_insumo', {
                    nombre_insumo: c.nombre_insumo.value.trim(),
                    tipo_insumo: c.tipo_insumo.value,
                    id_presentacion: Number(c.id_presentacion.value),
                    fecha_vencimiento: c.fecha_vencimiento.value,
                    id_excluir: idExcluirActual(),
                });
                if (r.existe) {
                    mostrarError(c.nombre_insumo,
                        'Ya existe un insumo con esa presentación, nombre, tipo y fecha de vencimiento');
                    return false;
                }
            } catch (e) {
                console.error('validar insumo:', e);
                // API caída → NO se verificó nada: jamás dejar verde.
                mostrarError(c.nombre_insumo,
                    'No se pudo verificar el insumo con el servidor. Intenta de nuevo.');
                return false;
            }
            limpiarError(c.nombre_insumo);
            return true;
        }

        async function validarTodo() {
            const locales = [
                validarNombreInsumo(),
                validarTipoInsumo(),
                validarPresentacion(),
                validarFechaVencimiento(),
                validarDescripcion(),
            ];
            if (!locales.every((r) => r === true)) {
                return false;
            }
            return validarUnicidad();
        }

        // ---------------- eventos en vivo ----------------
        // Debounce solo para la validación remota (evita saturar la API).
        let timer;
        const debounce = (fn) => {
            clearTimeout(timer);
            timer = setTimeout(fn, 450);
        };
        // Los campos que participan en el duplicado disparan la remota.
        const dispararRemota = () => debounce(validarUnicidad);

        c.nombre_insumo     && c.nombre_insumo.addEventListener('input', () => {
            validarNombreInsumo();
            dispararRemota();
        });
        c.tipo_insumo       && c.tipo_insumo.addEventListener('change', () => {
            validarTipoInsumo();
            dispararRemota();
        });
        c.id_presentacion   && c.id_presentacion.addEventListener('change', () => {
            validarPresentacion();
            dispararRemota();
        });
        c.fecha_vencimiento && c.fecha_vencimiento.addEventListener('input', () => {
            validarFechaVencimiento();
            dispararRemota();
        });
        c.descripcion       && c.descripcion.addEventListener('input', validarDescripcion);

        // Select2 dispara 'change' como evento de jQuery, que NO llega a los
        // addEventListener nativos. Enlazamos también por jQuery, y añadimos
        // sus eventos propios (select2:select / select2:clear).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.tipo_insumo, validarTipoInsumo],
                [c.id_presentacion, validarPresentacion],
            ].forEach(function (par) {
                const campo = par[0];
                const fn = par[1];
                if (campo && $(campo).hasClass('select2')) {
                    $(campo).on('change select2:select select2:clear', function () {
                        fn();
                        dispararRemota();
                    });
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
