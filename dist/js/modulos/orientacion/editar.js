// dist/js/modulos/orientacion/editar.js
// ------------------------------------------------------------------
// Edición de una orientación en un MODAL Bootstrap (patrón de Psicología).
//
// Integridad de auditoría: el beneficiario y el empleado que atendió se
// muestran en SOLO LECTURA y NO se envían al backend. Solo se editan
// los 4 campos de texto.
// ------------------------------------------------------------------
window.OrientacionEditar = (function () {
    'use strict';

    const MAX_TEXTO = 5000;

    let modal = null;
    let form = null;

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

    function validarTexto(id) {
        const el = campo(id);
        if (!el) return true;
        const valor = el.value.trim();
        if (valor === '') { mostrarError(id, 'Este campo es obligatorio'); return false; }
        if (valor.length > MAX_TEXTO) { mostrarError(id, `Máximo ${MAX_TEXTO} caracteres`); return false; }
        limpiarError(id);
        return true;
    }

    function validarTodo() {
        const resultados = [
            validarTexto('edit_motivo_orientacion'),
            validarTexto('edit_descripcion_orientacion'),
            validarTexto('edit_indicaciones_orientacion'),
            validarTexto('edit_obs_adic_orientacion'),
        ];
        return resultados.every((v) => v === true);
    }

    /**
     * SOLO los 4 campos de texto. Beneficiario y empleado no viajan en
     * la petición (el backend los ignora de todos modos).
     */
    function construirDatos() {
        return {
            id_orientacion: Number(campo('editar_id_orientacion').value),
            motivo_orientacion: campo('edit_motivo_orientacion').value,
            descripcion_orientacion: campo('edit_descripcion_orientacion').value,
            indicaciones_orientacion: campo('edit_indicaciones_orientacion').value,
            obs_adic_orientacion: campo('edit_obs_adic_orientacion').value,
        };
    }

    // ---------------- inicialización ----------------

    document.addEventListener('DOMContentLoaded', function () {
        form = document.getElementById('form-editar-orientacion');
        const modalEl = document.getElementById('modal-editar-orientacion');
        if (!form || !modalEl) return;

        modal = new bootstrap.Modal(modalEl);

        const enVivo = [
            ['edit_motivo_orientacion', () => validarTexto('edit_motivo_orientacion')],
            ['edit_descripcion_orientacion', () => validarTexto('edit_descripcion_orientacion')],
            ['edit_indicaciones_orientacion', () => validarTexto('edit_indicaciones_orientacion')],
            ['edit_obs_adic_orientacion', () => validarTexto('edit_obs_adic_orientacion')],
        ];
        enVivo.forEach(function (par) {
            const el = campo(par[0]);
            if (!el) return;
            el.addEventListener('input', par[1]);
            el.addEventListener('change', par[1]);
        });

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validarTodo()) {
                AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            try {
                await apiFetch(BASE_URL + 'api/orientacion/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(construirDatos()),
                });

                // Quita el foco antes de ocultar para evitar el aviso de
                // accesibilidad (aria-hidden sobre un ancestro con foco).
                if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
                modal.hide();
                AlertManager.success('Orientación actualizada', 'Los cambios se guardaron correctamente.');
                if (window.OrientacionConsultar) window.OrientacionConsultar.cargar();
                if (window.OrientacionStats) window.OrientacionStats.cargar();
            } catch (error) {
                if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.');
                }
            }
        });
    });

    function abrir(registro) {
        if (!registro || !form || !modal) return;

        campo('editar_id_orientacion').value = registro.id_orientacion;
        campo('editar_beneficiario').value = `${registro.beneficiario || ''} (${registro.cedula_beneficiario || 'sin cédula'})`;
        campo('editar_empleado').value = `${registro.empleado || ''} (${registro.cedula_empleado || 'sin cédula'})`;

        const subtitulo = campo('editar_subtitulo');
        if (subtitulo) {
            subtitulo.textContent = `${registro.beneficiario || ''} · ${Formato.fecha(registro.fecha_creacion || '')}`;
        }

        // Solo se precargan los campos editables.
        campo('edit_motivo_orientacion').value = registro.motivo_orientacion || '';
        campo('edit_descripcion_orientacion').value = registro.descripcion_orientacion || '';
        campo('edit_indicaciones_orientacion').value = registro.indicaciones_orientacion || '';
        campo('edit_obs_adic_orientacion').value = registro.obs_adic_orientacion || '';

        limpiarTodo();
        modal.show();
    }

    return { abrir };
})();
