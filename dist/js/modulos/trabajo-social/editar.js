// dist/js/modulos/trabajo-social/editar.js
// ------------------------------------------------------------------
// Edición de los 4 sub-registros de Trabajo Social en MODALES Bootstrap.
//
// Integridad de auditoría: el beneficiario y el empleado que atendió se
// muestran en SOLO LECTURA y NO se envían al backend. Cada tipo solo
// envía sus campos editables:
//   - becas:          tipo_banco, cta_bcv
//   - exoneraciones:  motivo, otro_motivo, carnet_discapacidad
//   - fames:          id_patologia, tipo_ayuda, otro_tipo
//   - embarazadas:    id_patologia, semanas_gest, codigo_patria,
//                     serial_patria, estado
// ------------------------------------------------------------------
window.TrabajoSocialEditar = (function () {
    'use strict';

    const CONFIG = {
        becas: {
            modal: 'modal-editar-beca',
            form: 'form-editar-beca',
            id: 'editar_id_beca',
            clave: 'id_becas',
            subtitulo: 'editarBecaSubtitulo',
            beneficiario: 'editarBecaBeneficiario',
            empleado: 'editarBecaEmpleado',
            titulo: 'Beca actualizada',
        },
        exoneraciones: {
            modal: 'modal-editar-exoneracion',
            form: 'form-editar-exoneracion',
            id: 'editar_id_exoneracion',
            clave: 'id_exoneracion',
            subtitulo: 'editarExoneracionSubtitulo',
            beneficiario: 'editarExoneracionBeneficiario',
            empleado: 'editarExoneracionEmpleado',
            titulo: 'Exoneración actualizada',
        },
        fames: {
            modal: 'modal-editar-fames',
            form: 'form-editar-fames',
            id: 'editar_id_fames',
            clave: 'id_fames',
            subtitulo: 'editarFamesSubtitulo',
            beneficiario: 'editarFamesBeneficiario',
            empleado: 'editarFamesEmpleado',
            titulo: 'FAMES actualizado',
        },
        embarazadas: {
            modal: 'modal-editar-embarazadas',
            form: 'form-editar-embarazadas',
            id: 'editar_id_embarazadas',
            clave: 'id_gestion',
            subtitulo: 'editarEmbarazadasSubtitulo',
            beneficiario: 'editarEmbarazadasBeneficiario',
            empleado: 'editarEmbarazadasEmpleado',
            titulo: 'Gestión actualizada',
        },
    };

    const modales = {};
    const formularios = {};
    let patologiasCargadas = false;

    const campo = (id) => document.getElementById(id);

    // ---------------- validación visual ----------------

    function marcarSelect2(el, conError) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        const $seleccion = $(el).next('.select2-container').find('.select2-selection');
        if (!$seleccion.length) return;
        $seleccion.toggleClass('is-invalid', !!conError).toggleClass('is-valid', !conError);
    }

    function mostrarError(id, msg) {
        const el = campo(id);
        if (!el) return;
        const err = campo(id + 'Error');
        if (err) err.textContent = msg;
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
        marcarSelect2(el, true);
    }

    function limpiarError(id) {
        const el = campo(id);
        if (!el) return;
        const err = campo(id + 'Error');
        if (err) err.textContent = '';
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
        marcarSelect2(el, false);
    }

    function limpiarTodo(form) {
        if (!form) return;
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
        form.querySelectorAll('.select2-selection')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
    }

    function validarSelectRequerido(id, mensaje) {
        const el = campo(id);
        if (!el) return true;
        if (!el.value) { mostrarError(id, mensaje); return false; }
        limpiarError(id);
        return true;
    }

    function validarTexto(id, { requerido = true, max = null, patron = null, mensajePatron = null } = {}) {
        const el = campo(id);
        if (!el) return true;
        const valor = el.value.trim();
        if (valor === '') {
            if (!requerido) { limpiarError(id); return true; }
            mostrarError(id, 'Este campo es obligatorio');
            return false;
        }
        if (max && valor.length > max) { mostrarError(id, `Máximo ${max} caracteres`); return false; }
        if (patron && !patron.test(valor)) { mostrarError(id, mensajePatron || 'Formato no válido'); return false; }
        limpiarError(id);
        return true;
    }

    // ---------------- validadores por tipo ----------------

    function validarBeca() {
        const okBanco = validarSelectRequerido('edit_tipo_banco', 'Selecciona el tipo de banco');
        const okCta = validarTexto('edit_cta_bcv', {
            patron: /^[0-9]{16}$/,
            mensajePatron: 'La cuenta BCV debe tener exactamente 16 dígitos',
        });
        return okBanco && okCta;
    }

    function validarExoneracion() {
        const motivo = campo('edit_motivo');
        const okMotivo = validarSelectRequerido('edit_motivo', 'Selecciona el motivo');
        const okCarnet = validarTexto('edit_carnet_discapacidad', { max: 100 });
        let okDetalle = true;
        if (motivo && motivo.value === 'Otro') {
            okDetalle = validarTexto('edit_otro_motivo', { max: 100 });
        } else {
            limpiarError('edit_otro_motivo');
        }
        return okMotivo && okCarnet && okDetalle;
    }

    function validarFames() {
        const tipoAyuda = campo('edit_tipo_ayuda');
        const okPatologia = validarSelectRequerido('edit_patologia_fames', 'Selecciona la patología');
        const okTipo = validarSelectRequerido('edit_tipo_ayuda', 'Selecciona el tipo de ayuda');
        let okDetalle = true;
        if (tipoAyuda && tipoAyuda.value === 'Otros') {
            okDetalle = validarTexto('edit_otro_tipo', { max: 100 });
        } else {
            limpiarError('edit_otro_tipo');
        }
        return okPatologia && okTipo && okDetalle;
    }

    function validarEmbarazadas() {
        const okPatologia = validarSelectRequerido('edit_patologia_embarazada', 'Selecciona la patología');
        const okSemanas = validarTexto('edit_semanas_gest', {
            patron: /^(?:[1-9]|[1-3][0-9]|4[0-5])$/,
            mensajePatron: 'Las semanas deben estar entre 1 y 45',
        });
        const okCodigo = validarTexto('edit_codigo_patria', {
            requerido: false, patron: /^[0-9]{1,10}$/, mensajePatron: 'Solo números (máx. 10 dígitos)',
        });
        const okSerial = validarTexto('edit_serial_patria', {
            requerido: false, patron: /^[0-9]{1,10}$/, mensajePatron: 'Solo números (máx. 10 dígitos)',
        });
        const okEstado = validarSelectRequerido('edit_estado', 'Selecciona el estado');
        return okPatologia && okSemanas && okCodigo && okSerial && okEstado;
    }

    const VALIDADORES = {
        becas: validarBeca,
        exoneraciones: validarExoneracion,
        fames: validarFames,
        embarazadas: validarEmbarazadas,
    };

    // ---------------- envío ----------------

    function construirDatos(tipo, id) {
        if (tipo === 'becas') {
            return { tipo, id, tipo_banco: campo('edit_tipo_banco').value, cta_bcv: campo('edit_cta_bcv').value.trim() };
        }
        if (tipo === 'exoneraciones') {
            return {
                tipo, id,
                motivo: campo('edit_motivo').value,
                otro_motivo: campo('edit_otro_motivo').value.trim(),
                carnet_discapacidad: campo('edit_carnet_discapacidad').value.trim(),
            };
        }
        if (tipo === 'fames') {
            return {
                tipo, id,
                id_patologia: Number(campo('edit_patologia_fames').value),
                tipo_ayuda: campo('edit_tipo_ayuda').value,
                otro_tipo: campo('edit_otro_tipo').value.trim(),
            };
        }
        return {
            tipo, id,
            id_patologia: Number(campo('edit_patologia_embarazada').value),
            semanas_gest: campo('edit_semanas_gest').value.trim(),
            codigo_patria: campo('edit_codigo_patria').value.trim(),
            serial_patria: campo('edit_serial_patria').value.trim(),
            estado: campo('edit_estado').value,
        };
    }

    async function enviar(tipo) {
        const config = CONFIG[tipo];
        const form = formularios[tipo];
        if (!config || !form) return;

        if (!VALIDADORES[tipo]()) {
            AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const id = Number(campo(config.id).value);
        try {
            await apiFetch(BASE_URL + 'api/trabajo-social/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(construirDatos(tipo, id)),
            });

            // Quita el foco antes de ocultar para evitar el aviso de
            // accesibilidad (aria-hidden sobre un ancestro con foco).
            if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
            modales[tipo].hide();
            AlertManager.success(config.titulo, 'Los cambios se guardaron correctamente.');
            if (window.TrabajoSocialConsultar) window.TrabajoSocialConsultar.cargar(tipo);
            if (window.TrabajoSocialStats) window.TrabajoSocialStats.cargar();
        } catch (error) {
            if (['VALIDATION_ERROR', 'NOT_FOUND', 'ALREADY_EXISTS'].includes(error.codigo)) {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.');
            }
        }
    }

    // ---------------- catálogo de patologías ----------------

    function pintarPatologias(select, patologias) {
        if (!select) return;
        const marcador = select.querySelector('option[value=""]');
        select.innerHTML = '';
        if (marcador) select.appendChild(marcador);
        patologias.forEach((patologia) => {
            const opcion = document.createElement('option');
            opcion.value = patologia.id_patologia;
            opcion.textContent = patologia.nombre_patologia;
            select.appendChild(opcion);
        });
        if (typeof window.initSelect2 === 'function') {
            // initSelect2 usa .find(): debe recibir un CONTENEDOR (el form),
            // no el propio <select>, o no lo encontraría.
            window.initSelect2(select.closest('form') || select.parentElement);
        }
    }

    async function cargarPatologias() {
        if (patologiasCargadas) return;
        try {
            const datos = await apiFetch(BASE_URL + 'api/trabajo-social/catalogos');
            const patologias = datos.patologias || [];
            pintarPatologias(campo('edit_patologia_fames'), patologias);
            pintarPatologias(campo('edit_patologia_embarazada'), patologias);
            patologiasCargadas = true;
        } catch (error) {
            AlertManager.error('No se pudieron cargar las patologías', error.mensaje || 'Error inesperado.');
        }
    }

    function fijarValor(select, valor) {
        if (!select) return;
        if (typeof jQuery !== 'undefined' && $.fn.select2 && $(select).data('select2')) {
            $(select).val(valor ?? '').trigger('change');
        } else {
            select.value = valor ?? '';
        }
    }

    // ---------------- apertura ----------------

    function refrescarDetalleMotivo() {
        const motivo = campo('edit_motivo');
        const contenedor = campo('editOtroMotivoContainer');
        if (!motivo || !contenedor) return;
        contenedor.hidden = motivo.value !== 'Otro';
    }

    function refrescarDetalleTipoAyuda() {
        const tipo = campo('edit_tipo_ayuda');
        const contenedor = campo('editOtroTipoContainer');
        if (!tipo || !contenedor) return;
        contenedor.hidden = tipo.value !== 'Otros';
    }

    async function abrir(tipo, registro) {
        const config = CONFIG[tipo];
        const form = formularios[tipo];
        const modal = modales[tipo];
        if (!config || !form || !modal || !registro) return;

        await cargarPatologias();

        campo(config.id).value = registro[config.clave] ?? registro.id ?? '';
        campo(config.beneficiario).value = `${registro.beneficiario || ''} (${registro.cedula_beneficiario || 'sin cédula'})`;
        campo(config.empleado).value = `${registro.empleado || ''} (${registro.cedula_empleado || 'sin cédula'})`;
        const subtitulo = campo(config.subtitulo);
        if (subtitulo) {
            subtitulo.textContent = `${registro.beneficiario || ''} · ${Formato.fecha(registro.fecha_creacion || '')}`;
        }

        if (tipo === 'becas') {
            fijarValor(campo('edit_tipo_banco'), registro.tipo_banco || '');
            campo('edit_cta_bcv').value = registro.cta_bcv || '';
        } else if (tipo === 'exoneraciones') {
            fijarValor(campo('edit_motivo'), registro.motivo || '');
            campo('edit_otro_motivo').value =
                registro.otro_motivo && registro.otro_motivo !== 'No aplica' ? registro.otro_motivo : '';
            campo('edit_carnet_discapacidad').value = registro.carnet_discapacidad || '';
            refrescarDetalleMotivo();
        } else if (tipo === 'fames') {
            fijarValor(campo('edit_patologia_fames'), registro.id_patologia || '');
            fijarValor(campo('edit_tipo_ayuda'), registro.tipo_ayuda || '');
            campo('edit_otro_tipo').value =
                registro.otro_tipo && registro.otro_tipo !== 'No aplica' ? registro.otro_tipo : '';
            refrescarDetalleTipoAyuda();
        } else {
            fijarValor(campo('edit_patologia_embarazada'), registro.id_patologia || '');
            campo('edit_semanas_gest').value = registro.semanas_gest ?? '';
            campo('edit_codigo_patria').value = registro.codigo_patria ?? '';
            campo('edit_serial_patria').value = registro.serial_patria ?? '';
            fijarValor(campo('edit_estado'), registro.estado || '');
        }

        limpiarTodo(form);
        modal.show();
    }

    // ---------------- inicialización ----------------

    document.addEventListener('DOMContentLoaded', function () {
        Object.keys(CONFIG).forEach((tipo) => {
            const config = CONFIG[tipo];
            const modalEl = document.getElementById(config.modal);
            const form = document.getElementById(config.form);
            if (!modalEl || !form) return;
            modales[tipo] = new bootstrap.Modal(modalEl);
            formularios[tipo] = form;
            form.addEventListener('submit', function (evento) {
                evento.preventDefault();
                enviar(tipo);
            });
        });

        // Validación en vivo de los campos comunes a todos los modales.
        const enVivo = [
            ['edit_cta_bcv', validarBeca],
            ['edit_carnet_discapacidad', validarExoneracion],
            ['edit_otro_motivo', validarExoneracion],
            ['edit_otro_tipo', validarFames],
            ['edit_semanas_gest', validarEmbarazadas],
            ['edit_codigo_patria', validarEmbarazadas],
            ['edit_serial_patria', validarEmbarazadas],
        ];
        enVivo.forEach(([id, validar]) => {
            const el = campo(id);
            if (!el) return;
            el.addEventListener('input', validar);
            el.addEventListener('change', validar);
        });

        // Selects con condición u obligatorios: validan al cambiar.
        const selects = [
            ['edit_motivo', validarExoneracion, refrescarDetalleMotivo],
            ['edit_tipo_ayuda', validarFames, refrescarDetalleTipoAyuda],
            ['edit_tipo_banco', validarBeca, null],
            ['edit_patologia_fames', validarFames, null],
            ['edit_patologia_embarazada', validarEmbarazadas, null],
            ['edit_estado', validarEmbarazadas, null],
        ];
        selects.forEach(([id, validar, refrescar]) => {
            const el = campo(id);
            if (!el) return;
            const reaccion = () => {
                if (refrescar) refrescar();
                validar();
            };
            el.addEventListener('change', reaccion);
            if (typeof jQuery !== 'undefined' && $.fn.select2 && $(el).hasClass('select2')) {
                $(el).on('change select2:select select2:clear', reaccion);
            }
        });
    });

    return { abrir };
})();
