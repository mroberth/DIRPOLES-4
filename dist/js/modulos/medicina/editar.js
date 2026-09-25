// dist/js/modulos/medicina/editar.js
// ------------------------------------------------------------------
// Edición de una consulta médica en un MODAL Bootstrap (patrón de Psicología).
//
// Integridad de auditoría e inventario: el beneficiario, el empleado que
// atendió y los insumos usados se muestran en SOLO LECTURA y NO se envían
// al backend. Solo se editan: patología, estatura, peso, tipo de sangre,
// motivo de visita, diagnóstico, tratamiento y observaciones.
// ------------------------------------------------------------------
window.MedicinaEditar = (function () {
    'use strict';

    let modal = null;
    let form = null;
    let patologiasCargadas = false;

    const campo = (id) => document.getElementById(id);

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

    function numeroEnRango(id, min, max, msgRequerido, msgRango) {
        const el = campo(id);
        if (!el) return true;
        const texto = String(el.value || '').trim();
        if (texto === '') { mostrarError(id, msgRequerido); return false; }
        const valor = Number(texto);
        if (isNaN(valor)) { mostrarError(id, 'Debe ser un número.'); return false; }
        if (valor < min || valor > max) { mostrarError(id, msgRango); return false; }
        limpiarError(id);
        return true;
    }

    // ---------------- validadores por campo ----------------

    function validarPatologia() { return requerido('edit_id_patologia', 'Selecciona una patología'); }

    function validarTipoSangre() { return requerido('edit_tipo_sangre', 'Selecciona el tipo de sangre'); }

    function validarEstatura() {
        return numeroEnRango('edit_estatura', 0.5, 2.5,
            'La estatura es obligatoria.', 'La estatura debe estar entre 0.5 y 2.5.');
    }

    function validarPeso() {
        return numeroEnRango('edit_peso', 2, 300,
            'El peso es obligatorio.', 'El peso debe estar entre 2 y 300.');
    }

    function validarMotivo() {
        return requerido('edit_motivo_visita', 'El motivo de visita es obligatorio')
            && maximo('edit_motivo_visita', 255, 'Máximo 255 caracteres');
    }

    function validarDiagnostico() {
        return requerido('edit_diagnostico', 'El diagnóstico es obligatorio')
            && maximo('edit_diagnostico', 255, 'Máximo 255 caracteres');
    }

    function validarTratamiento() {
        return requerido('edit_tratamiento', 'El tratamiento es obligatorio')
            && maximo('edit_tratamiento', 255, 'Máximo 255 caracteres');
    }

    function validarObservaciones() {
        return maximo('edit_observaciones', 255, 'Máximo 255 caracteres');
    }

    function validarTodo() {
        const resultados = [
            validarPatologia(),
            validarTipoSangre(),
            validarEstatura(),
            validarPeso(),
            validarMotivo(),
            validarDiagnostico(),
            validarTratamiento(),
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
            const catalogo = await apiFetch(BASE_URL + 'api/medicina/catalogos');
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
            AlertManager.error('No se pudo cargar', 'No se pudieron cargar las patologías médicas.');
        }
    }

    /**
     * SOLO campos editables. Beneficiario, empleado e insumos no viajan
     * en la petición (el backend los ignora de todos modos).
     */
    function construirDatos() {
        return {
            id_consulta_med: Number(campo('editar_id_consulta_med').value),
            id_patologia: Number(campo('edit_id_patologia').value),
            estatura: campo('edit_estatura').value,
            peso: campo('edit_peso').value,
            tipo_sangre: campo('edit_tipo_sangre').value,
            motivo_visita: campo('edit_motivo_visita').value,
            diagnostico: campo('edit_diagnostico').value,
            tratamiento: campo('edit_tratamiento').value,
            observaciones: campo('edit_observaciones').value,
        };
    }

    // ---------------- inicialización ----------------

    document.addEventListener('DOMContentLoaded', function () {
        form = document.getElementById('form-editar-medicina');
        const modalEl = document.getElementById('modal-editar-medicina');
        if (!form || !modalEl) return;

        modal = new bootstrap.Modal(modalEl);

        const enVivo = [
            ['edit_id_patologia', validarPatologia],
            ['edit_tipo_sangre', validarTipoSangre],
            ['edit_estatura', validarEstatura],
            ['edit_peso', validarPeso],
            ['edit_motivo_visita', validarMotivo],
            ['edit_diagnostico', validarDiagnostico],
            ['edit_tratamiento', validarTratamiento],
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
                await apiFetch(BASE_URL + 'api/medicina/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(construirDatos()),
                });

                // Quita el foco antes de ocultar para evitar el aviso de
                // accesibilidad (aria-hidden sobre un ancestro con foco).
                if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
                modal.hide();
                AlertManager.success('Consulta actualizada', 'Los cambios se guardaron correctamente.');
                if (window.MedicinaConsultar) window.MedicinaConsultar.cargar();
                if (window.MedicinaStats) window.MedicinaStats.cargar();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.');
                }
            }
        });
    });

    async function abrir(registro) {
        if (!registro || !form || !modal) return;

        await cargarPatologias();

        campo('editar_id_consulta_med').value = registro.id_consulta_med;
        campo('editar_beneficiario').value = `${registro.beneficiario || ''} (${registro.cedula_beneficiario || 'sin cédula'})`;
        campo('editar_empleado').value = `${registro.empleado || ''} (${registro.cedula_empleado || 'sin cédula'})`;
        campo('editar_insumos').value = registro.insumos_usados || 'Sin insumos registrados';

        const subtitulo = campo('editar_subtitulo');
        if (subtitulo) {
            subtitulo.textContent = `${registro.beneficiario || ''} · ${Formato.fecha(registro.fecha_creacion || '')}`;
        }

        // Solo se precargan los campos editables.
        campo('edit_estatura').value = registro.estatura ?? '';
        campo('edit_peso').value = registro.peso ?? '';
        campo('edit_motivo_visita').value = registro.motivo_visita || '';
        campo('edit_diagnostico').value = registro.diagnostico || '';
        campo('edit_tratamiento').value = registro.tratamiento || '';
        campo('edit_observaciones').value = registro.observaciones || '';

        setSelect('edit_tipo_sangre', registro.tipo_sangre || '');
        setSelect('edit_id_patologia', registro.id_patologia);

        limpiarTodo();
        if (window.initSelect2) window.initSelect2(form);
        modal.show();
    }

    return { abrir };
})();
