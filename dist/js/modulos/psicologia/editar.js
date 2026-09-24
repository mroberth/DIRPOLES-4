// dist/js/modulos/psicologia/editar.js
// ------------------------------------------------------------------
// Edición de una consulta psicológica en un MODAL Bootstrap (patrón de Citas).
//
// Integridad de auditoría: el beneficiario, el profesional que atendió y el
// tipo de consulta se muestran en SOLO LECTURA y NO se envían al backend.
// Solo se editan los datos del diagnóstico/retiro/cambio y las observaciones.
// ------------------------------------------------------------------
window.PsicologiaEditar = (function () {
    'use strict';

    let modal = null;
    let form = null;
    let registroActual = null;
    let patologiasCargadas = false;

    const campo = (id) => document.getElementById(id);
    const tipoActual = () => (campo('editar_tipo_consulta') ? campo('editar_tipo_consulta').value : '');

    const esDiagnostico = (tipo) => tipo === 'Diagnóstico';
    const esRetiro = (tipo) => tipo === 'Retiro temporal';
    const esCambio = (tipo) => tipo === 'Cambio de carrera';

    function seccion(id, visible) {
        const el = campo(id);
        if (el) el.classList.toggle('d-none', !visible);
    }

    function mostrarError(id, msg) {
        const el = campo(id);
        if (!el) return;
        const err = campo(id + 'Error');
        if (err) err.textContent = msg;
        el.classList.add('is-invalid');
        el.classList.remove('is-valid');
    }

    function limpiarError(id) {
        const el = campo(id);
        if (!el) return;
        const err = campo(id + 'Error');
        if (err) err.textContent = '';
        el.classList.remove('is-invalid');
        el.classList.add('is-valid');
    }

    function limpiarTodo() {
        if (!form) return;
        form.querySelectorAll('.is-valid, .is-invalid')
            .forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
        form.querySelectorAll('.form-text.text-danger')
            .forEach((el) => (el.textContent = ''));
    }

    function requerido(id, msg) {
        const el = campo(id);
        if (!el) return true;
        if (String(el.value || '').trim() === '') { mostrarError(id, msg); return false; }
        limpiarError(id);
        return true;
    }

    function maximo(id, max, msg) {
        const el = campo(id);
        if (!el) return true;
        if (el.value.length > max) { mostrarError(id, msg); return false; }
        limpiarError(id);
        return true;
    }

    // ---------------- validadores por campo ----------------

    function validarPatologia() {
        if (!esDiagnostico(tipoActual())) { limpiarError('edit_id_patologia'); return true; }
        return requerido('edit_id_patologia', 'Selecciona una patología');
    }

    function validarDiagnostico() {
        if (!esDiagnostico(tipoActual())) { limpiarError('edit_diagnostico'); return true; }
        return requerido('edit_diagnostico', 'El diagnóstico es obligatorio');
    }

    function validarTratamiento() {
        return maximo('edit_tratamiento_gen', 5000, 'Máximo 5000 caracteres');
    }

    function validarMotivoRetiro() {
        if (!esRetiro(tipoActual())) { limpiarError('edit_motivo_retiro'); return true; }
        return requerido('edit_motivo_retiro', 'El motivo del retiro es obligatorio');
    }

    function validarDuracion() {
        if (!esRetiro(tipoActual())) { limpiarError('edit_duracion_retiro'); return true; }
        return requerido('edit_duracion_retiro', 'La duración del retiro es obligatoria')
            && maximo('edit_duracion_retiro', 50, 'Máximo 50 caracteres');
    }

    function validarMotivoCambio() {
        if (!esCambio(tipoActual())) { limpiarError('edit_motivo_cambio'); return true; }
        return requerido('edit_motivo_cambio', 'El motivo del cambio es obligatorio')
            && maximo('edit_motivo_cambio', 100, 'Máximo 100 caracteres');
    }

    function validarObservaciones() {
        return maximo('edit_observaciones', 5000, 'Máximo 5000 caracteres');
    }

    function validarTodo() {
        const resultados = [
            validarPatologia(),
            validarDiagnostico(),
            validarTratamiento(),
            validarMotivoRetiro(),
            validarDuracion(),
            validarMotivoCambio(),
            validarObservaciones(),
        ];
        return resultados.every((v) => v === true);
    }

    // ---------------- utilidades ----------------

    /** Asigna el valor a un <select>, respetando Select2 si está inicializado. */
    function setSelect(id, valor) {
        const el = campo(id);
        if (!el) return;
        const v = valor == null ? '' : String(valor);
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2 && $(el).hasClass('select2')) {
            $(el).val(v).trigger('change');
        } else {
            el.value = v;
        }
    }

    async function cargarPatologias() {
        if (patologiasCargadas || !form) return;
        const sel = campo('edit_id_patologia');
        if (!sel) return;
        try {
            const catalogo = await apiFetch(BASE_URL + 'api/psicologia/catalogos');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            (catalogo.patologias || []).forEach((p) => {
                const opt = document.createElement('option');
                opt.value = p.id_patologia;
                opt.textContent = p.nombre_patologia || p.nombre || '';
                sel.appendChild(opt);
            });
            patologiasCargadas = true;
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron las patologías para edición:', e);
        }
    }

    function construirDatos() {
        const tipo = tipoActual();
        const previo = registroActual || {};

        // Se conservan los campos del tipo que no aplica para no perder su valor.
        const datos = {
            id_psicologia: Number(campo('editar_id_psicologia').value),
            tipo_consulta: tipo,
            observaciones: campo('edit_observaciones').value,
            diagnostico: previo.diagnostico ?? null,
            tratamiento_gen: previo.tratamiento_gen ?? null,
            motivo_retiro: previo.motivo_retiro ?? null,
            duracion_retiro: previo.duracion_retiro ?? null,
            motivo_cambio: previo.motivo_cambio ?? null,
        };

        if (esDiagnostico(tipo)) {
            datos.id_patologia = Number(campo('edit_id_patologia').value);
            datos.diagnostico = campo('edit_diagnostico').value;
            datos.tratamiento_gen = campo('edit_tratamiento_gen').value;
        } else if (esRetiro(tipo)) {
            datos.motivo_retiro = campo('edit_motivo_retiro').value;
            datos.duracion_retiro = campo('edit_duracion_retiro').value;
        } else if (esCambio(tipo)) {
            datos.motivo_cambio = campo('edit_motivo_cambio').value;
        }

        return datos;
    }

    // ---------------- inicialización ----------------

    document.addEventListener('DOMContentLoaded', function () {
        form = document.getElementById('form-editar-psicologia');
        const modalEl = document.getElementById('modal-editar-psicologia');
        if (!form || !modalEl) return;

        modal = new bootstrap.Modal(modalEl);

        // Validación en vivo.
        const enVivo = [
            ['edit_id_patologia', validarPatologia],
            ['edit_diagnostico', validarDiagnostico],
            ['edit_tratamiento_gen', validarTratamiento],
            ['edit_motivo_retiro', validarMotivoRetiro],
            ['edit_duracion_retiro', validarDuracion],
            ['edit_motivo_cambio', validarMotivoCambio],
            ['edit_observaciones', validarObservaciones],
        ];
        enVivo.forEach(function (par) {
            const el = campo(par[0]);
            if (!el) return;
            el.addEventListener('input', par[1]);
            el.addEventListener('change', par[1]);
        });

        modalEl.addEventListener('shown.bs.modal', function () {
            if (window.initSelect2) window.initSelect2(form);
        });

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validarTodo()) {
                AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            try {
                await apiFetch(BASE_URL + 'api/psicologia/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(construirDatos()),
                });

                // Quita el foco antes de ocultar para evitar el aviso de
                // accesibilidad (aria-hidden sobre un ancestro con foco).
                if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
                modal.hide();
                AlertManager.success('Consulta actualizada', 'Los cambios se guardaron correctamente.');
                if (window.PsicologiaConsultar) window.PsicologiaConsultar.cargar();
                if (window.PsicologiaStats) window.PsicologiaStats.cargar();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'ALREADY_EXISTS') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.');
                }
            }
        });
    });

    async function abrir(registro) {
        if (!registro || !form || !modal) return;
        registroActual = registro;

        await cargarPatologias();

        const tipo = registro.tipo_consulta;

        campo('editar_id_psicologia').value = registro.id_psicologia;
        campo('editar_tipo_consulta').value = tipo;
        campo('editar_tipo_consulta_texto').value = tipo;
        campo('editar_beneficiario').value = `${registro.beneficiario || ''} (${registro.cedula_beneficiario || 'sin cédula'})`;
        campo('editar_profesional').value = `${registro.empleado || ''} (${registro.cedula_empleado || 'sin cédula'})`;

        const subtitulo = campo('editar_subtitulo');
        if (subtitulo) subtitulo.textContent = `${registro.beneficiario || ''} · ${tipo}`;

        // Solo se precargan los campos editables.
        campo('edit_diagnostico').value = registro.diagnostico || '';
        campo('edit_tratamiento_gen').value = registro.tratamiento_gen || '';
        campo('edit_motivo_retiro').value = registro.motivo_retiro || '';
        campo('edit_duracion_retiro').value = registro.duracion_retiro || '';
        campo('edit_motivo_cambio').value = registro.motivo_cambio || '';
        campo('edit_observaciones').value = registro.observaciones || '';

        setSelect('edit_id_patologia', registro.id_patologia);

        seccion('edit-campos-diagnostico', esDiagnostico(tipo));
        seccion('edit-campos-retiro', esRetiro(tipo));
        seccion('edit-campos-cambio', esCambio(tipo));

        limpiarTodo();
        if (window.initSelect2) window.initSelect2(form);
        modal.show();
    }

    return { abrir };
})();
