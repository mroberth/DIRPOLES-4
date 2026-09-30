/**
 * dist/js/modulos/transporte/validaciones.js
 * ---------------------------------------------------------------
 * Capa de validación REUTILIZABLE para los formularios de Transporte.
 * Sintaxis y comportamiento 100% idénticos a BeneficiarioValidaciones:
 * - Validación sincrónica e instantánea al escribir (input).
 * - Verificación remota con debounce para campos con API de unicidad (placa, documento, teléfono, correo).
 * - Soporte para Select2 y formularios de creación y edición.
 */
window.TransporteValidaciones = (function () {
    'use strict';

    const RX = {
        nombre:    /^[A-Za-zÀ-ÿ\u00f1\u00d10-9\s.\-]{2,100}$/,
        correo:    /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        telefono:  /^(0412|0414|0416|0422|0424|0426|02\d{2})\d{7}$/,
        num_doc:   /^\d{6,10}$/,
        placa:     /^[A-Za-z0-9\-]{3,20}$/,
    };

    function marcarSelect2(campo, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const $selection = $(campo).next('.select2-container').find('.select2-selection');
        if (!$selection.length) return;
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
        if (!form) return;
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    function configurar(form, opciones = {}) {
        if (!form) return { validarTodo: async () => true, limpiar: () => {} };

        const validarRemoto = opciones.validarRemoto !== false;
        const idExcluirActual = () => Number(opciones.idExcluir || 0);

        const c = {
            // Rutas
            nombre_ruta: form.querySelector('#nombre_ruta, #edit_nombre_ruta'),
            tipo_ruta:   form.querySelector('#tipo_ruta, #edit_tipo_ruta'),

            // Vehículos
            placa:        form.querySelector('#placa, #edit_placa'),
            tipo_veh:     form.querySelector('#tipo_vehiculo, #edit_tipo_vehiculo'),

            // Proveedores
            tipo_doc:     form.querySelector('#tipo_documento, #edit_tipo_documento'),
            num_doc:      form.querySelector('#num_documento, #edit_num_documento'),
            nombre_prov:  form.querySelector('#nombre_proveedor, #edit_nombre_proveedor'),
            tel_prov:     form.querySelector('#telefono_proveedor, #edit_telefono_proveedor'),
            correo_prov:  form.querySelector('#correo_proveedor, #edit_correo_proveedor'),
            dir_prov:     form.querySelector('#direccion_proveedor, #edit_direccion_proveedor'),

            // Repuestos
            nombre_rep:   form.querySelector('#nombre_repuesto, #edit_nombre_repuesto'),

            // Asignaciones
            id_ruta_asig: form.querySelector('#id_ruta_asig, #edit_id_ruta_asig'),
            id_veh_asig:  form.querySelector('#id_vehiculo_asig, #edit_id_vehiculo_asig'),
            id_emp_asig:  form.querySelector('#id_empleado_asig, #edit_id_empleado_asig'),
            fecha_asig:   form.querySelector('#fecha_asignacion, #edit_fecha_asignacion'),

            // Horarios de la ruta
            hora_salida:  form.querySelector('#horario_salida, #edit_horario_salida'),
            hora_llegada: form.querySelector('#horario_llegada, #edit_horario_llegada'),

            // Mantenimiento
            id_veh_mant:  form.querySelector('#id_vehiculo_mant'),
            tipo_mant:    form.querySelector('#tipo_mantenimiento'),
            fecha_mant:   form.querySelector('#fecha_mantenimiento'),
        };

        async function consultar(url, datos) {
            return apiFetch(BASE_URL + url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });
        }

        // ==================== VALIDADORES ESPECÍFICOS ====================

        function validarNombreRuta() {
            if (!c.nombre_ruta) return true;
            const v = c.nombre_ruta.value.trim();
            if (v === '') {
                mostrarError(c.nombre_ruta, 'El nombre de la ruta es obligatorio');
                return false;
            }
            if (v.length < 2 || v.length > 100) {
                mostrarError(c.nombre_ruta, 'El nombre de la ruta debe tener entre 2 y 100 caracteres');
                return false;
            }
            limpiarError(c.nombre_ruta);
            return true;
        }

        function validarTipoRuta() {
            if (!c.tipo_ruta) return true;
            if (!c.tipo_ruta.value) {
                mostrarError(c.tipo_ruta, 'Selecciona el tipo de ruta');
                return false;
            }
            limpiarError(c.tipo_ruta);
            return true;
        }

        // Placa: Sincrónico instantáneo + Remoto debounced.
        // OJO: aquí NO se reescribe el valor (regla 10); la normalización
        // ocurre solo en validarTodo().
        function validarPlacaSync() {
            if (!c.placa) return true;
            const val = c.placa.value.trim().toUpperCase();

            if (val === '') {
                mostrarError(c.placa, 'La placa es obligatoria');
                return false;
            }
            if (!RX.placa.test(val)) {
                mostrarError(c.placa, 'La placa debe tener entre 3 y 20 caracteres alfanuméricos');
                return false;
            }
            limpiarError(c.placa);
            return true;
        }

        async function validarPlacaRemoto() {
            if (!validarRemoto || !c.placa) return true;
            const val = c.placa.value;
            try {
                const r = await consultar('api/transporte/vehiculos/validar_placa', {
                    placa: val, id_excluir: idExcluirActual(),
                });
                if (r && r.existe) {
                    mostrarError(c.placa, 'Esta placa ya está registrada en otro vehículo');
                    return false;
                }
            } catch (e) {
                mostrarError(c.placa, 'No se pudo verificar la placa con el servidor. Intenta de nuevo.');
                return false;
            }
            limpiarError(c.placa);
            return true;
        }

        function validarTipoVeh() {
            if (!c.tipo_veh) return true;
            if (!c.tipo_veh.value) {
                mostrarError(c.tipo_veh, 'Selecciona el tipo de vehículo');
                return false;
            }
            limpiarError(c.tipo_veh);
            return true;
        }

        // Documento Proveedor
        function validarNumDocSync() {
            if (!c.num_doc) return true;
            const num = c.num_doc.value.replace(/\D/g, '');

            if (num === '') {
                mostrarError(c.num_doc, 'El número de documento es obligatorio');
                return false;
            }
            if (!RX.num_doc.test(num)) {
                mostrarError(c.num_doc, 'El número de documento debe tener entre 6 y 10 dígitos');
                return false;
            }
            limpiarError(c.num_doc);
            return true;
        }

        async function validarNumDocRemoto() {
            if (!validarRemoto || !c.num_doc) return true;
            const num = c.num_doc.value;
            const tipo = c.tipo_doc ? c.tipo_doc.value : 'J';
            try {
                const r = await consultar('api/transporte/proveedores/validar_documento', {
                    tipo_documento: tipo, num_documento: num, id_excluir: idExcluirActual(),
                });
                if (r && r.existe) {
                    mostrarError(c.num_doc, 'Este documento ya está registrado en el sistema');
                    return false;
                }
            } catch (e) {
                mostrarError(c.num_doc, 'No se pudo verificar el documento con el servidor.');
                return false;
            }
            limpiarError(c.num_doc);
            return true;
        }

        function validarNombreProv() {
            if (!c.nombre_prov) return true;
            const v = c.nombre_prov.value.trim();
            if (v === '') {
                mostrarError(c.nombre_prov, 'El nombre o razón social es obligatorio');
                return false;
            }
            if (v.length < 2 || v.length > 100) {
                mostrarError(c.nombre_prov, 'El nombre debe tener entre 2 y 100 caracteres');
                return false;
            }
            limpiarError(c.nombre_prov);
            return true;
        }

        // Correo Proveedor
        function validarCorreoProvSync() {
            if (!c.correo_prov) return true;
            const correo = c.correo_prov.value.trim();
            if (correo === '') {
                mostrarError(c.correo_prov, 'El correo electrónico es obligatorio');
                return false;
            }
            if (!RX.correo.test(correo)) {
                mostrarError(c.correo_prov, 'Formato de correo electrónico inválido (ej: correo@dominio.com)');
                return false;
            }
            limpiarError(c.correo_prov);
            return true;
        }

        async function validarCorreoProvRemoto() {
            if (!validarRemoto || !c.correo_prov) return true;
            const correo = c.correo_prov.value.trim();
            try {
                const r = await consultar('api/transporte/proveedores/validar_correo', {
                    correo, id_excluir: idExcluirActual(),
                });
                if (r && r.existe) {
                    mostrarError(c.correo_prov, 'Este correo electrónico ya está registrado');
                    return false;
                }
            } catch (e) {
                mostrarError(c.correo_prov, 'No se pudo verificar el correo con el servidor.');
                return false;
            }
            limpiarError(c.correo_prov);
            return true;
        }

        // Teléfono Proveedor
        function validarTelefonoProvSync() {
            if (!c.tel_prov) return true;
            const telefono = c.tel_prov.value.replace(/\D/g, '');

            if (telefono === '') {
                mostrarError(c.tel_prov, 'El teléfono es obligatorio');
                return false;
            }
            if (telefono.length !== 11) {
                mostrarError(c.tel_prov, 'El teléfono debe tener 11 dígitos');
                return false;
            }
            if (!RX.telefono.test(telefono)) {
                mostrarError(c.tel_prov, 'Debe empezar por 0412, 0414, 0416, 0422, 0424, 0426 o 02xx y tener 11 dígitos');
                return false;
            }
            limpiarError(c.tel_prov);
            return true;
        }

        async function validarTelefonoProvRemoto() {
            if (!validarRemoto || !c.tel_prov) return true;
            const telefono = c.tel_prov.value;
            try {
                const r = await consultar('api/transporte/proveedores/validar_telefono', {
                    telefono, id_excluir: idExcluirActual(),
                });
                if (r && r.existe) {
                    mostrarError(c.tel_prov, 'Este teléfono ya está registrado');
                    return false;
                }
            } catch (e) {
                mostrarError(c.tel_prov, 'No se pudo verificar el teléfono con el servidor.');
                return false;
            }
            limpiarError(c.tel_prov);
            return true;
        }

        function validarDirProv() {
            if (!c.dir_prov) return true;
            const v = c.dir_prov.value.trim();
            if (v === '') {
                mostrarError(c.dir_prov, 'La dirección es obligatoria');
                return false;
            }
            limpiarError(c.dir_prov);
            return true;
        }

        function validarNombreRepuesto() {
            if (!c.nombre_rep) return true;
            const v = c.nombre_rep.value.trim();
            if (v === '') {
                mostrarError(c.nombre_rep, 'El nombre del repuesto es obligatorio');
                return false;
            }
            limpiarError(c.nombre_rep);
            return true;
        }

        function validarRutaAsig() {
            if (!c.id_ruta_asig) return true;
            if (!c.id_ruta_asig.value) {
                mostrarError(c.id_ruta_asig, 'Selecciona una ruta');
                return false;
            }
            limpiarError(c.id_ruta_asig);
            return true;
        }

        function validarVehAsig() {
            if (!c.id_veh_asig) return true;
            if (!c.id_veh_asig.value) {
                mostrarError(c.id_veh_asig, 'Selecciona un vehículo');
                return false;
            }
            limpiarError(c.id_veh_asig);
            return true;
        }

        function validarEmpAsig() {
            if (!c.id_emp_asig) return true;
            if (!c.id_emp_asig.value) {
                mostrarError(c.id_emp_asig, 'Selecciona un chofer');
                return false;
            }
            limpiarError(c.id_emp_asig);
            return true;
        }

        function validarFechaAsig() {
            if (!c.fecha_asig) return true;
            const v = c.fecha_asig.value;
            if (!v) {
                mostrarError(c.fecha_asig, 'La fecha de asignación es obligatoria');
                return false;
            }
            const hoy = new Date();
            const strHoy = `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, '0')}-${String(hoy.getDate()).padStart(2, '0')}`;
            if (v < strHoy) {
                mostrarError(c.fecha_asig, 'La fecha de asignación no puede ser anterior a hoy');
                return false;
            }
            limpiarError(c.fecha_asig);
            return true;
        }

        // Cruce: la hora de llegada debe ser posterior a la de salida.
        function validarHorariosRuta() {
            if (!c.hora_salida || !c.hora_llegada) return true;
            const salida = c.hora_salida.value;
            const llegada = c.hora_llegada.value;
            if (salida && llegada && llegada <= salida) {
                mostrarError(c.hora_llegada, 'La hora de llegada debe ser posterior a la de salida');
                return false;
            }
            if (llegada) limpiarError(c.hora_llegada);
            return true;
        }

        function validarVehMant() {
            if (!c.id_veh_mant) return true;
            if (!c.id_veh_mant.value) {
                mostrarError(c.id_veh_mant, 'Selecciona un vehículo para el mantenimiento');
                return false;
            }
            limpiarError(c.id_veh_mant);
            return true;
        }

        function validarTipoMant() {
            if (!c.tipo_mant) return true;
            if (!c.tipo_mant.value) {
                mostrarError(c.tipo_mant, 'Selecciona el tipo de mantenimiento');
                return false;
            }
            limpiarError(c.tipo_mant);
            return true;
        }

        // ==================== ATAR LISTENERS REACTIVOS ====================

        let timerPlaca, timerDoc, timerCorreo, timerTel;

        if (c.nombre_ruta) {
            c.nombre_ruta.addEventListener('input', validarNombreRuta);
            c.nombre_ruta.addEventListener('blur', validarNombreRuta);
        }

        if (c.tipo_ruta) {
            c.tipo_ruta.addEventListener('change', validarTipoRuta);
        }

        if (c.placa) {
            c.placa.addEventListener('input', () => {
                if (validarPlacaSync()) {
                    clearTimeout(timerPlaca);
                    timerPlaca = setTimeout(validarPlacaRemoto, 450);
                }
            });
            c.placa.addEventListener('blur', async () => {
                if (validarPlacaSync()) await validarPlacaRemoto();
            });
        }

        if (c.tipo_veh) {
            c.tipo_veh.addEventListener('change', validarTipoVeh);
        }

        if (c.num_doc) {
            c.num_doc.addEventListener('input', () => {
                if (validarNumDocSync()) {
                    clearTimeout(timerDoc);
                    timerDoc = setTimeout(validarNumDocRemoto, 450);
                }
            });
            c.num_doc.addEventListener('blur', async () => {
                if (validarNumDocSync()) await validarNumDocRemoto();
            });
        }

        if (c.tipo_doc) {
            c.tipo_doc.addEventListener('change', () => {
                if (c.num_doc && c.num_doc.value.trim() !== '') {
                    if (validarNumDocSync()) validarNumDocRemoto();
                }
            });
        }

        if (c.nombre_prov) {
            c.nombre_prov.addEventListener('input', validarNombreProv);
            c.nombre_prov.addEventListener('blur', validarNombreProv);
        }

        if (c.correo_prov) {
            c.correo_prov.addEventListener('input', () => {
                if (validarCorreoProvSync()) {
                    clearTimeout(timerCorreo);
                    timerCorreo = setTimeout(validarCorreoProvRemoto, 450);
                }
            });
            c.correo_prov.addEventListener('blur', async () => {
                if (validarCorreoProvSync()) await validarCorreoProvRemoto();
            });
        }

        if (c.tel_prov) {
            c.tel_prov.addEventListener('input', () => {
                if (validarTelefonoProvSync()) {
                    clearTimeout(timerTel);
                    timerTel = setTimeout(validarTelefonoProvRemoto, 450);
                }
            });
            c.tel_prov.addEventListener('blur', async () => {
                if (validarTelefonoProvSync()) await validarTelefonoProvRemoto();
            });
        }

        if (c.dir_prov) {
            c.dir_prov.addEventListener('input', validarDirProv);
            c.dir_prov.addEventListener('blur', validarDirProv);
        }

        if (c.nombre_rep) {
            c.nombre_rep.addEventListener('input', validarNombreRepuesto);
            c.nombre_rep.addEventListener('blur', validarNombreRepuesto);
        }

        if (c.fecha_asig) {
            c.fecha_asig.addEventListener('change', validarFechaAsig);
        }

        if (c.hora_salida) {
            c.hora_salida.addEventListener('change', validarHorariosRuta);
        }
        if (c.hora_llegada) {
            c.hora_llegada.addEventListener('change', validarHorariosRuta);
        }

        // Eventos para Select2
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            [
                [c.tipo_ruta, validarTipoRuta],
                [c.tipo_veh, validarTipoVeh],
                [c.id_ruta_asig, validarRutaAsig],
                [c.id_veh_asig, validarVehAsig],
                [c.id_emp_asig, validarEmpAsig],
                [c.id_veh_mant, validarVehMant],
                [c.tipo_mant, validarTipoMant],
            ].forEach(function (pair) {
                const campo = pair[0];
                const fn = pair[1];
                if (campo) {
                    $(campo).on('change select2:select select2:clear', fn);
                }
            });
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        // ==================== VALIDACIÓN GENERAL EN SUBMIT ====================

        async function validarTodo() {
            // Normalización SOLO al enviar (nunca en los listeners, regla 10).
            if (c.placa) c.placa.value = c.placa.value.trim().toUpperCase();
            if (c.num_doc) c.num_doc.value = c.num_doc.value.replace(/\D/g, '');
            if (c.tel_prov) c.tel_prov.value = c.tel_prov.value.replace(/\D/g, '');

            let ok = true;

            if (c.nombre_ruta && !validarNombreRuta()) ok = false;
            if (c.tipo_ruta && !validarTipoRuta()) ok = false;
            if (c.hora_llegada && !validarHorariosRuta()) ok = false;

            if (c.placa) {
                if (!validarPlacaSync() || !(await validarPlacaRemoto())) ok = false;
            }
            if (c.tipo_veh && !validarTipoVeh()) ok = false;

            if (c.num_doc) {
                if (!validarNumDocSync() || !(await validarNumDocRemoto())) ok = false;
            }
            if (c.nombre_prov && !validarNombreProv()) ok = false;
            if (c.correo_prov) {
                if (!validarCorreoProvSync() || !(await validarCorreoProvRemoto())) ok = false;
            }
            if (c.tel_prov) {
                if (!validarTelefonoProvSync() || !(await validarTelefonoProvRemoto())) ok = false;
            }
            if (c.dir_prov && !validarDirProv()) ok = false;

            if (c.nombre_rep && !validarNombreRepuesto()) ok = false;

            if (c.id_ruta_asig && !validarRutaAsig()) ok = false;
            if (c.id_veh_asig && !validarVehAsig()) ok = false;
            if (c.id_emp_asig && !validarEmpAsig()) ok = false;
            if (c.fecha_asig && !validarFechaAsig()) ok = false;

            if (c.id_veh_mant && !validarVehMant()) ok = false;
            if (c.tipo_mant && !validarTipoMant()) ok = false;

            return ok;
        }

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    return {
        configurar,
        mostrarError,
        limpiarError,
    };
})();
