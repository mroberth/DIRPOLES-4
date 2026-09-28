// dist/js/modulos/trabajo-social/validaciones.js
// ------------------------------------------------------------------
// Validaciones REUTILIZABLES del módulo Trabajo Social.
//
//   const vBecas = window.TrabajoSocialValidaciones.configurar(formBecas);
//   const vExoneracion = window.TrabajoSocialValidaciones.configurarExoneracion(formExoneracion);
//   const ok = v.validarTodo();
//
// El beneficiario es GLOBAL (tarjeta fuera de los formularios, compartido
// por las pestañas), por eso se busca con document.getElementById.
// Etapa 1: becas — banco (26 códigos), cuenta BCV de 16 dígitos y
// planilla PDF. Etapa 2: exoneración — motivo, carnet, detalle del
// motivo "Otro" y carta PDF. Etapa 3: FAMES — patología, tipo de ayuda
// y detalle de "Otros"; embarazadas — género femenino, patología,
// semanas 1–45 y patria opcional.
// ------------------------------------------------------------------
window.TrabajoSocialValidaciones = (function () {
    'use strict';

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
        // Solo el formulario: el beneficiario es GLOBAL y no se resetea
        // con "Limpiar" (se comparte entre pestañas), así que su estado
        // se queda como está hasta que cambie.
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    // ---------------- helpers comunes ----------------

    function validarSelectRequerido(campo, mensaje) {
        if (!campo) return true;
        if (!campo.value) {
            mostrarError(campo, mensaje);
            return false;
        }
        limpiarError(campo);
        return true;
    }

    function validarArchivoPdf(campo, mensajeVacio) {
        if (!campo) return true;
        const archivo = campo.files && campo.files[0];
        if (!archivo) {
            mostrarError(campo, mensajeVacio);
            return false;
        }
        if (!/\.pdf$/i.test(archivo.name)) {
            mostrarError(campo, 'Debe ser un archivo PDF');
            return false;
        }
        limpiarError(campo);
        return true;
    }

    /** El select2 global solo se escucha UNA vez aunque se configuren varios formularios. */
    let beneficiarioGlobalListo = false;
    function escucharBeneficiarioGlobal() {
        const beneficiario = document.getElementById('id_beneficiario');
        if (!beneficiario || beneficiarioGlobalListo) return;
        beneficiarioGlobalListo = true;

        const validar = () => validarSelectRequerido(beneficiario, 'Selecciona un beneficiario');
        beneficiario.addEventListener('change', validar);
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2 && $(beneficiario).hasClass('select2')) {
            $(beneficiario).on('change select2:select select2:clear', validar);
        }
    }

    // ---------------- Etapa 1: BECAS ----------------

    function configurar(form) {
        escucharBeneficiarioGlobal();

        const c = {
            beneficiario: document.getElementById('id_beneficiario'),
            tipo_banco: form.querySelector('#tipo_banco'),
            cta_bcv: form.querySelector('#cta_bcv'),
            planilla: form.querySelector('#planilla'),
        };

        function validarCtaBcv() {
            const campo = c.cta_bcv;
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'La cuenta BCV es obligatoria');
                return false;
            }
            if (!/^[0-9]{16}$/.test(valor)) {
                mostrarError(campo, 'Debe tener exactamente 16 dígitos numéricos');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.beneficiario, 'Selecciona un beneficiario'),
                validarSelectRequerido(c.tipo_banco, 'Selecciona el tipo de banco'),
                validarCtaBcv(),
                validarArchivoPdf(c.planilla, 'Adjunta la planilla de inscripción (PDF obligatorio)'),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        c.tipo_banco && c.tipo_banco.addEventListener('change', () => validarSelectRequerido(c.tipo_banco, 'Selecciona el tipo de banco'));
        c.cta_bcv && c.cta_bcv.addEventListener('input', validarCtaBcv);
        c.planilla && c.planilla.addEventListener('change', () => validarArchivoPdf(c.planilla, 'Adjunta la planilla de inscripción (PDF obligatorio)'));

        // Select2 dispara 'change' como evento de jQuery (no llega a addEventListener).
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2
            && c.tipo_banco && $(c.tipo_banco).hasClass('select2')) {
            $(c.tipo_banco).on('change select2:select select2:clear',
                () => validarSelectRequerido(c.tipo_banco, 'Selecciona el tipo de banco'));
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    // ---------------- Etapa 2: EXONERACIÓN ----------------

    function configurarExoneracion(form) {
        escucharBeneficiarioGlobal();

        const c = {
            beneficiario: document.getElementById('id_beneficiario'),
            motivo: form.querySelector('#motivo'),
            otro_motivo: form.querySelector('#otro_motivo'),
            carnet: form.querySelector('#carnet_discapacidad'),
            carta: form.querySelector('#carta'),
        };

        function validarCarnet() {
            const campo = c.carnet;
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'El carnet de discapacidad es obligatorio');
                return false;
            }
            if (valor.length > 100) {
                mostrarError(campo, 'Máximo 100 caracteres');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarOtroMotivo() {
            const campo = c.otro_motivo;
            if (!campo) return true;
            const esOtro = c.motivo && c.motivo.value === 'Otro';
            if (!esOtro) {
                limpiarError(campo);
                return true;
            }
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'Detalla el motivo seleccionado');
                return false;
            }
            if (valor.length > 100) {
                mostrarError(campo, 'Máximo 100 caracteres');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.beneficiario, 'Selecciona un beneficiario'),
                validarSelectRequerido(c.motivo, 'Selecciona el motivo'),
                validarCarnet(),
                validarOtroMotivo(),
                validarArchivoPdf(c.carta, 'Adjunta la carta de exoneración (PDF obligatorio)'),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        c.motivo && c.motivo.addEventListener('change', () => {
            validarSelectRequerido(c.motivo, 'Selecciona el motivo');
            validarOtroMotivo();
        });
        c.otro_motivo && c.otro_motivo.addEventListener('input', validarOtroMotivo);
        c.carnet && c.carnet.addEventListener('input', validarCarnet);
        c.carta && c.carta.addEventListener('change', () => validarArchivoPdf(c.carta, 'Adjunta la carta de exoneración (PDF obligatorio)'));

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    // ---------------- Etapa 3: FAMES ----------------

    function configurarFames(form) {
        escucharBeneficiarioGlobal();

        const c = {
            beneficiario: document.getElementById('id_beneficiario'),
            patologia: form.querySelector('#patologia_fames'),
            tipo_ayuda: form.querySelector('#tipo_ayuda'),
            otro_tipo: form.querySelector('#otro_tipo'),
        };

        function validarOtroTipo() {
            const campo = c.otro_tipo;
            if (!campo) return true;
            const esOtros = c.tipo_ayuda && c.tipo_ayuda.value === 'Otros';
            if (!esOtros) {
                limpiarError(campo);
                return true;
            }
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'Detalla el tipo de ayuda seleccionado');
                return false;
            }
            if (valor.length > 100) {
                mostrarError(campo, 'Máximo 100 caracteres');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarSelectRequerido(c.beneficiario, 'Selecciona un beneficiario'),
                validarSelectRequerido(c.patologia, 'Selecciona la patología'),
                validarSelectRequerido(c.tipo_ayuda, 'Selecciona el tipo de ayuda'),
                validarOtroTipo(),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        c.patologia && c.patologia.addEventListener('change',
            () => validarSelectRequerido(c.patologia, 'Selecciona la patología'));
        c.tipo_ayuda && c.tipo_ayuda.addEventListener('change', () => {
            validarSelectRequerido(c.tipo_ayuda, 'Selecciona el tipo de ayuda');
            validarOtroTipo();
        });
        c.otro_tipo && c.otro_tipo.addEventListener('input', validarOtroTipo);

        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2
            && c.patologia && $(c.patologia).hasClass('select2')) {
            $(c.patologia).on('change select2:select select2:clear',
                () => validarSelectRequerido(c.patologia, 'Selecciona la patología'));
        }

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    // ---------------- Etapa 3: EMBARAZADAS ----------------

    function configurarEmbarazada(form) {
        escucharBeneficiarioGlobal();

        const c = {
            beneficiario: document.getElementById('id_beneficiario'),
            patologia: form.querySelector('#patologia_embarazada'),
            semanas: form.querySelector('#semanas_gest'),
            codigo: form.querySelector('#codigo_patria'),
            serial: form.querySelector('#serial_patria'),
        };

        function enPestanaEmbarazadas() {
            const panel = document.getElementById('embarazadas');
            return !!panel && panel.classList.contains('active');
        }

        /** El select es global: el chequeo en vivo solo corre si la pestaña está visible. */
        function validarGeneroFemenino() {
            const beneficiario = c.beneficiario;
            if (!beneficiario) return true;
            if (!beneficiario.value) {
                mostrarError(beneficiario, 'Selecciona un beneficiario');
                return false;
            }
            const opcion = beneficiario.selectedOptions && beneficiario.selectedOptions[0];
            const genero = ((opcion && opcion.dataset && opcion.dataset.genero) || '').trim().toUpperCase();
            // Acepta 'F' o 'Femenino' (char(10) con datos heredados).
            if (genero.charAt(0) !== 'F') {
                mostrarError(beneficiario, 'La gestión de embarazo aplica solo a beneficiarias de género femenino');
                return false;
            }
            limpiarError(beneficiario);
            return true;
        }

        function validarSemanas() {
            const campo = c.semanas;
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                mostrarError(campo, 'Las semanas de gestación son obligatorias');
                return false;
            }
            const semanas = Number(valor);
            if (!Number.isInteger(semanas) || semanas < 1 || semanas > 45) {
                mostrarError(campo, 'Debe estar entre 1 y 45 semanas');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarNumeroOpcional(campo) {
            if (!campo) return true;
            const valor = campo.value.trim();
            if (valor === '') {
                limpiarError(campo);
                return true;
            }
            if (!/^[0-9]{1,10}$/.test(valor) || Number(valor) < 1 || Number(valor) > 2147483647) {
                mostrarError(campo, 'Solo números (máx. 10 dígitos)');
                return false;
            }
            limpiarError(campo);
            return true;
        }

        function validarTodo() {
            const resultados = [
                validarGeneroFemenino(),
                validarSelectRequerido(c.patologia, 'Selecciona la patología de embarazo'),
                validarSemanas(),
                validarNumeroOpcional(c.codigo),
                validarNumeroOpcional(c.serial),
            ];
            return resultados.every((v) => v === true);
        }

        // ---------------- eventos en vivo ----------------
        // El género se revisa al cambiar de beneficiario SOLO si la
        // pestaña de embarazadas está visible (el select es global).
        // Se enlaza nativo y jQuery: Select2 dispara su propio 'change'.
        const revisarGenero = () => {
            if (enPestanaEmbarazadas()) validarGeneroFemenino();
        };
        c.beneficiario && c.beneficiario.addEventListener('change', revisarGenero);
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2
            && c.beneficiario && $(c.beneficiario).hasClass('select2')) {
            $(c.beneficiario).on('change select2:select select2:clear', revisarGenero);
        }

        c.patologia && c.patologia.addEventListener('change',
            () => validarSelectRequerido(c.patologia, 'Selecciona la patología de embarazo'));
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2
            && c.patologia && $(c.patologia).hasClass('select2')) {
            $(c.patologia).on('change select2:select select2:clear',
                () => validarSelectRequerido(c.patologia, 'Selecciona la patología de embarazo'));
        }

        c.semanas && c.semanas.addEventListener('input', validarSemanas);
        c.codigo && c.codigo.addEventListener('input', () => validarNumeroOpcional(c.codigo));
        c.serial && c.serial.addEventListener('input', () => validarNumeroOpcional(c.serial));

        form.addEventListener('reset', () => limpiarTodo(form));

        return {
            validarTodo,
            limpiar: () => limpiarTodo(form),
            campos: c,
        };
    }

    return { configurar, configurarExoneracion, configurarFames, configurarEmbarazada, mostrarError, limpiarError };
}());
