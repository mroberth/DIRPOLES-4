// dist/js/modulos/empleado/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del formulario de empleado.
// El mismo archivo sirve para crear y para editar:
//
//   const validador = EmpleadoValidaciones.configurar(form, {
//       idExcluir: 0,          // editar: id del empleado que se edita (para no marcarlo duplicado)
//       claveOpcional: false,  // editar: si la clave puede quedar vacía (no cambiarla)
//       validarRemoto: true,   // false = no consultar la API de unicidad
//   });
//
//   const ok = await validador.validarTodo();
//   validador.limpiar();
//
// Si un campo no existe en el formulario, su validación se omite (devuelve true).
// ------------------------------------------------------------------
window.EmpleadoValidaciones = (function () {
    'use strict';

    const RX = {
        nombre:    /^[A-Za-zÀ-ÿ\u00f1\u00d1\s]{2,50}$/,
        correo:    /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        // Teléfono móvil Venezuela: 0412/0414/0416/0422/0424/0426 + 7 dígitos (11 en total).
        telefono:  /^(0412|0414|0416|0422|0424|0426)\d{7}$/,
        clave:     /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*.,]).{8,}$/,
        direccion: /^[A-Za-zÀ-ÿ0-9 ,.\-#]+$/,
    };

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
        const validarRemoto = opciones.validarRemoto !== false;
        const urlValidarCorreo = opciones.urlValidarCorreo || 'api/empleados/validar_correo';
        const urlValidarTelefono = opciones.urlValidarTelefono || 'api/empleados/validar_telefono';

        // Se leen en cada validación (no se capturan): así editar.js puede
        // fijar `opciones.idExcluir` al abrir el modal con un empleado distinto.
        const idExcluirActual = () => Number(opciones.idExcluir || 0);
        const claveEsOpcional = () => opciones.claveOpcional === true;

        const c = {
            tipo_cedula:      form.querySelector('#tipo_cedula'),
            cedula:           form.querySelector('#cedula'),
            nombre:           form.querySelector('#nombre'),
            apellido:         form.querySelector('#apellido'),
            correo:           form.querySelector('#correo'),
            telefono:         form.querySelector('#telefono'),
            id_tipo_empleado: form.querySelector('#id_tipo_empleado'),
            fecha_nacimiento: form.querySelector('#fecha_nacimiento'),
            clave:            form.querySelector('#clave'),
            estatus:          form.querySelector('#estatus'),
            direccion:        form.querySelector('#direccion'),
        };

        async function consultar(url, datos) {
            return apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });
        }

        // ---------------- validadores ----------------

        async function validarCedula() {
            if (!c.cedula || !c.tipo_cedula) return true;

            const cedula = (c.cedula.value || '').replace(/[^0-9]/g, '');
            c.cedula.value = cedula;
            const tipo = c.tipo_cedula.value;

            // Validar tipo y cédula por separado para marcar AMBOS si aplica.
            let tipoOk = true;
            if (tipo === '') {
                mostrarError(c.tipo_cedula, 'El tipo de cédula es obligatorio');
                tipoOk = false;
            } else {
                limpiarError(c.tipo_cedula);
            }

            let cedulaOk = true;
            if (cedula === '') {
                mostrarError(c.cedula, 'La cédula es obligatoria');
                cedulaOk = false;
            } else if (cedula.length < 6 || cedula.length > 10) {
                mostrarError(c.cedula, 'La cédula debe tener entre 6 y 10 dígitos');
                cedulaOk = false;
            }

            if (!tipoOk || !cedulaOk) {
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar('api/empleados/validar_cedula', {
                        tipo_cedula: tipo, cedula, id_excluir: idExcluirActual(),
                    });
                    if (r.existe) {
                        mostrarError(c.cedula, 'La cédula ya está registrada');
                        return false;
                    }
                } catch (e) {
                    console.error('validar cédula:', e);
                }
            }
            limpiarError(c.cedula);
            return true;
        }

        function validarNombre() {
            if (!c.nombre) return true;
            const v = c.nombre.value;
            if (v.trim() === '') {
                mostrarError(c.nombre, 'El nombre es obligatorio');
                return false;
            }
            if (!RX.nombre.test(v)) {
                mostrarError(c.nombre, 'Solo letras, acentos y espacios (2 a 50)');
                return false;
            }
            c.nombre.value = v.replace(/<[^>]*>?/gm, ''); // anti-XSS
            limpiarError(c.nombre);
            return true;
        }

        function validarApellido() {
            if (!c.apellido) return true;
            const v = c.apellido.value;
            if (v.trim() === '') { // obligatorio (aunque en BD sea nullable)
                mostrarError(c.apellido, 'El apellido es obligatorio');
                return false;
            }
            if (!RX.nombre.test(v)) {
                mostrarError(c.apellido, 'Solo letras, acentos y espacios (2 a 50)');
                return false;
            }
            c.apellido.value = v.replace(/<[^>]*>?/gm, '');
            limpiarError(c.apellido);
            return true;
        }

        async function validarCorreo() {
            if (!c.correo) return true;
            const correo = c.correo.value.trim();
            if (correo === '') {
                mostrarError(c.correo, 'El correo es obligatorio');
                return false;
            }
            if (!RX.correo.test(correo)) {
                mostrarError(c.correo, 'Formato de correo electrónico inválido');
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar(urlValidarCorreo, { correo, id_excluir: idExcluirActual() });
                    if (r.existe) {
                        mostrarError(c.correo, 'El correo ya está registrado');
                        return false;
                    }
                } catch (e) {
                    console.error('validar correo:', e);
                }
            }
            limpiarError(c.correo);
            return true;
        }

        async function validarTelefono() {
            if (!c.telefono) return true;

            // Solo dígitos, sin espacios ni guiones.
            const telefono = (c.telefono.value || '').replace(/\D/g, '');
            c.telefono.value = telefono;

            if (telefono === '') { // obligatorio (aunque en BD sea nullable)
                mostrarError(c.telefono, 'El teléfono es obligatorio');
                return false;
            }
            if (!RX.telefono.test(telefono)) {
                mostrarError(c.telefono, 'Debe ser 0412, 0414, 0416, 0422, 0424 o 0426 + 7 dígitos (ej: 04129298008)');
                return false;
            }

            if (validarRemoto) {
                try {
                    const r = await consultar(urlValidarTelefono, { telefono, id_excluir: idExcluirActual() });
                    if (r.existe) {
                        mostrarError(c.telefono, 'El teléfono ya está registrado');
                        return false;
                    }
                } catch (e) {
                    console.error('validar teléfono:', e);
                }
            }
            limpiarError(c.telefono);
            return true;
        }

        function validarTipoEmpleado() {
            if (!c.id_tipo_empleado) return true;
            if (!c.id_tipo_empleado.value) {
                mostrarError(c.id_tipo_empleado, 'Selecciona un tipo de empleado');
                return false;
            }
            limpiarError(c.id_tipo_empleado);
            return true;
        }

        function validarFechaNacimiento() {
            if (!c.fecha_nacimiento) return true;
            const v = c.fecha_nacimiento.value;
            if (!v) { // obligatoria (aunque en BD sea nullable)
                mostrarError(c.fecha_nacimiento, 'La fecha de nacimiento es obligatoria');
                return false;
            }
            const f = new Date(v + 'T00:00:00');
            if (isNaN(f.getTime())) {
                mostrarError(c.fecha_nacimiento, 'Fecha inválida');
                return false;
            }
            const hoy = new Date();
            let edad = hoy.getFullYear() - f.getFullYear();
            const m = hoy.getMonth() - f.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < f.getDate())) edad--;
            if (edad < 15) {
                mostrarError(c.fecha_nacimiento, 'Debe tener al menos 15 años');
                return false;
            }
            limpiarError(c.fecha_nacimiento);
            return true;
        }

        function validarClave() {
            if (!c.clave) return true;
            const v = c.clave.value;
            if (v === '' && claveEsOpcional()) { // editar sin cambiar la clave
                limpiarError(c.clave);
                return true;
            }
            if (v === '') {
                mostrarError(c.clave, 'La contraseña es obligatoria');
                return false;
            }
            if (!RX.clave.test(v)) {
                mostrarError(c.clave, 'Mínimo 8 caracteres, con letra, número y carácter especial');
                return false;
            }
            limpiarError(c.clave);
            return true;
        }

        function validarEstatus() {
            if (!c.estatus) return true;
            if (c.estatus.value === '') {
                mostrarError(c.estatus, 'El estatus es obligatorio');
                return false;
            }
            limpiarError(c.estatus);
            return true;
        }

        function validarDireccion() {
            if (!c.direccion) return true;
            let v = c.direccion.value.replace(/<[^>]*>?/gm, '');
            if (v.trim() === '') { // opcional
                limpiarError(c.direccion);
                return true;
            }
            if (/^\s|\s$/.test(v)) {
                mostrarError(c.direccion, 'La dirección no puede iniciar ni terminar con espacios');
                return false;
            }
            if (v.length < 5) {
                mostrarError(c.direccion, 'La dirección debe tener al menos 5 caracteres');
                return false;
            }
            if (v.length > 500) {
                mostrarError(c.direccion, 'La dirección debe tener máximo 500 caracteres');
                return false;
            }
            if (!RX.direccion.test(v)) {
                mostrarError(c.direccion, 'Solo letras, números, espacios, comas, puntos, guiones y #');
                return false;
            }
            c.direccion.value = v;
            limpiarError(c.direccion);
            return true;
        }

        async function validarTodo() {
            const resultados = [
                await validarCedula(),
                validarNombre(),
                validarApellido(),
                await validarCorreo(),
                await validarTelefono(),
                validarTipoEmpleado(),
                validarFechaNacimiento(),
                validarClave(),
                validarEstatus(),
                validarDireccion(),
            ];
            return resultados.every((r) => r === true);
        }

        // ---------------- eventos en vivo ----------------
        // Debounce solo para las validaciones remotas (evita saturar la API).
        let timer;
        const debounce = (fn) => {
            clearTimeout(timer);
            timer = setTimeout(fn, 450);
        };

        c.tipo_cedula      && c.tipo_cedula.addEventListener('change', validarCedula);
        c.cedula           && c.cedula.addEventListener('input', () => debounce(validarCedula));
        c.nombre           && c.nombre.addEventListener('input', validarNombre);
        c.apellido         && c.apellido.addEventListener('input', validarApellido);
        c.correo           && c.correo.addEventListener('input', () => debounce(validarCorreo));
        c.telefono         && c.telefono.addEventListener('input', () => debounce(validarTelefono));
        c.id_tipo_empleado && c.id_tipo_empleado.addEventListener('change', validarTipoEmpleado);
        c.fecha_nacimiento && c.fecha_nacimiento.addEventListener('input', validarFechaNacimiento);
        c.clave            && c.clave.addEventListener('input', validarClave);
        c.estatus          && c.estatus.addEventListener('change', validarEstatus);
        c.direccion        && c.direccion.addEventListener('input', validarDireccion);

        // Select2 dispara 'change' como evento de jQuery, que NO llega a los
        // addEventListener nativos (por eso un <select> normal sí valida y uno
        // con Select2 no). Enlazamos también por jQuery, y añadimos sus eventos
        // propios (select2:select / select2:clear).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.tipo_cedula, validarCedula],
                [c.id_tipo_empleado, validarTipoEmpleado],
                [c.estatus, validarEstatus],
            ].forEach(function (par) {
                const campo = par[0];
                const fn = par[1];
                if (campo && $(campo).hasClass('select2')) {
                    $(campo).on('change select2:select select2:clear', fn);
                }
            });
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        // Botón mostrar/ocultar contraseña (si existe en el formulario).
        const toggle = document.getElementById('btnTogglePassword');
        if (toggle && c.clave) {
            toggle.addEventListener('click', function () {
                const oculta = c.clave.type === 'password';
                c.clave.type = oculta ? 'text' : 'password';
                const eye = document.getElementById('icon-eye');
                const eyeSlash = document.getElementById('icon-eye-slash');
                if (eye && eyeSlash) {
                    eye.classList.toggle('d-none', oculta);
                    eyeSlash.classList.toggle('d-none', !oculta);
                }
            });
        }

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    return { configurar, mostrarError, limpiarError };
})();
