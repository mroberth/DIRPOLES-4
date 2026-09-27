// dist/js/modulos/discapacidad/editar.js
// ------------------------------------------------------------------
// Edición de un diagnóstico de discapacidad en un MODAL Bootstrap
// (patrón de Psicología/Medicina/Orientación).
//
// Integridad de auditoría: el beneficiario y el empleado que atendió se
// muestran en SOLO LECTURA y NO se envían al backend. Solo se editan
// los 11 campos de la tabla `discapacidad`.
// ------------------------------------------------------------------
window.DiscapacidadEditar = (function () {
    'use strict';

    const LONGITUDES = {
        disc_especifica: 200,
        diagnostico: 255,
        medicamentos: 255,
        habilidades_funcionales: 255,
        dispositivo_asistencia: 255,
        carnet_discapacidad: 20,
    };

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

    function validarSelect(id) {
        const el = campo(id);
        if (!el) return true;
        if (!el.value) { mostrarError(id, 'Selecciona una opción'); return false; }
        limpiarError(id);
        return true;
    }

    function validarRequerido(id, maximo) {
        const el = campo(id);
        if (!el) return true;
        const valor = el.value.trim();
        if (valor === '') { mostrarError(id, 'Este campo es obligatorio'); return false; }
        if (maximo !== null && valor.length > maximo) {
            mostrarError(id, `Máximo ${maximo} caracteres`);
            return false;
        }
        limpiarError(id);
        return true;
    }

    function validarOpcional(id, maximo) {
        const el = campo(id);
        if (!el) return true;
        const valor = el.value.trim();
        if (valor === '') { limpiarError(id); return true; }
        if (maximo !== null && valor.length > maximo) {
            mostrarError(id, `Máximo ${maximo} caracteres`);
            return false;
        }
        limpiarError(id);
        return true;
    }

    function validarTodo() {
        const resultados = [
            validarSelect('edit_tipo_discapacidad'),
            validarRequerido('edit_diagnostico', LONGITUDES.diagnostico),
            validarSelect('edit_grado'),
            validarRequerido('edit_habilidades_funcionales', LONGITUDES.habilidades_funcionales),
            validarRequerido('edit_observaciones', null),
            validarOpcional('edit_disc_especifica', LONGITUDES.disc_especifica),
            validarOpcional('edit_medicamentos', LONGITUDES.medicamentos),
            validarOpcional('edit_dispositivo_asistencia', LONGITUDES.dispositivo_asistencia),
            validarOpcional('edit_carnet_discapacidad', LONGITUDES.carnet_discapacidad),
            validarOpcional('edit_recomendaciones', null),
        ];
        return resultados.every((v) => v === true);
    }

    /**
     * SOLO los 11 campos editables. Beneficiario y empleado no viajan en
     * la petición (el backend los ignora de todos modos).
     */
    function construirDatos() {
        return {
            id_discapacidad: Number(campo('editar_id_discapacidad').value),
            tipo_discapacidad: campo('edit_tipo_discapacidad').value,
            disc_especifica: campo('edit_disc_especifica').value,
            diagnostico: campo('edit_diagnostico').value,
            grado: campo('edit_grado').value,
            medicamentos: campo('edit_medicamentos').value,
            habilidades_funcionales: campo('edit_habilidades_funcionales').value,
            requiere_asistencia: campo('edit_requiere_asistencia').value,
            dispositivo_asistencia: campo('edit_dispositivo_asistencia').value,
            carnet_discapacidad: campo('edit_carnet_discapacidad').value,
            observaciones: campo('edit_observaciones').value,
            recomendaciones: campo('edit_recomendaciones').value,
        };
    }

    // ---------------- inicialización ----------------

    document.addEventListener('DOMContentLoaded', function () {
        form = document.getElementById('form-editar-discapacidad');
        const modalEl = document.getElementById('modal-editar-discapacidad');
        if (!form || !modalEl) return;

        modal = new bootstrap.Modal(modalEl);

        const enVivo = [
            ['edit_tipo_discapacidad', 'change', () => validarSelect('edit_tipo_discapacidad')],
            ['edit_grado', 'change', () => validarSelect('edit_grado')],
            ['edit_diagnostico', 'input', () => validarRequerido('edit_diagnostico', LONGITUDES.diagnostico)],
            ['edit_habilidades_funcionales', 'input', () => validarRequerido('edit_habilidades_funcionales', LONGITUDES.habilidades_funcionales)],
            ['edit_observaciones', 'input', () => validarRequerido('edit_observaciones', null)],
            ['edit_disc_especifica', 'input', () => validarOpcional('edit_disc_especifica', LONGITUDES.disc_especifica)],
            ['edit_medicamentos', 'input', () => validarOpcional('edit_medicamentos', LONGITUDES.medicamentos)],
            ['edit_dispositivo_asistencia', 'input', () => validarOpcional('edit_dispositivo_asistencia', LONGITUDES.dispositivo_asistencia)],
            ['edit_carnet_discapacidad', 'input', () => validarOpcional('edit_carnet_discapacidad', LONGITUDES.carnet_discapacidad)],
            ['edit_recomendaciones', 'input', () => validarOpcional('edit_recomendaciones', null)],
        ];
        enVivo.forEach(function (par) {
            const el = campo(par[0]);
            if (!el) return;
            el.addEventListener(par[1], par[2]);
        });

        form.addEventListener('submit', async function (ev) {
            ev.preventDefault();

            if (!validarTodo()) {
                AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            try {
                await apiFetch(BASE_URL + 'api/discapacidad/actualizar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(construirDatos()),
                });

                // Quita el foco antes de ocultar para evitar el aviso de
                // accesibilidad (aria-hidden sobre un ancestro con foco).
                if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
                modal.hide();
                AlertManager.success('Diagnóstico actualizado', 'Los cambios se guardaron correctamente.');
                if (window.DiscapacidadConsultar) window.DiscapacidadConsultar.cargar();
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

        campo('editar_id_discapacidad').value = registro.id_discapacidad;
        campo('editar_beneficiario').value = `${registro.beneficiario || ''} (${registro.cedula_beneficiario || 'sin cédula'})`;
        campo('editar_empleado').value = `${registro.empleado || ''} (${registro.cedula_empleado || 'sin cédula'})`;

        const subtitulo = campo('editar_subtitulo');
        if (subtitulo) {
            subtitulo.textContent = `${registro.beneficiario || ''} · ${Formato.fecha(registro.fecha_creacion || '')}`;
        }

        // Los 11 campos editables (jamás beneficiario/empleado).
        campo('edit_tipo_discapacidad').value = registro.tipo_discapacidad || '';
        campo('edit_disc_especifica').value = registro.disc_especifica || '';
        campo('edit_diagnostico').value = registro.diagnostico || '';
        campo('edit_grado').value = registro.grado || '';
        campo('edit_medicamentos').value = registro.medicamentos || '';
        campo('edit_habilidades_funcionales').value = registro.habilidades_funcionales || '';
        campo('edit_requiere_asistencia').value = registro.requiere_asistencia || '';
        campo('edit_dispositivo_asistencia').value = registro.dispositivo_asistencia || '';
        campo('edit_carnet_discapacidad').value = registro.carnet_discapacidad || '';
        campo('edit_observaciones').value = registro.observaciones || '';
        campo('edit_recomendaciones').value = registro.recomendaciones || '';

        limpiarTodo();
        modal.show();
    }

    return { abrir };
})();
