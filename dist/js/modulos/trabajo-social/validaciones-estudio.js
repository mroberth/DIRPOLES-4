// dist/js/modulos/trabajo-social/validaciones-estudio.js
// ------------------------------------------------------------------
// Validación del offcanvas del Estudio Socioeconómico (5 pasos).
// Adaptado de ../DIRPOLES_4/dist/js/.../validar_estudio-socioe.js:
//   - La FOTO es OPCIONAL (decisión aprobada en la Etapa 5); si se
//     adjunta, debe ser JPG/JPEG/PNG/GIF.
//   - AlertManager en lugar de Swal suelto (patrón del repositorio).
//   - Exponen window.validarEstudioSocioeconomico(), que estudio.js
//     invoca antes de enviar.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formEstudioSocioeconomico');
    if (!form) return;

    const elements = {
        imagen_se: document.getElementById('imagen_se'),
        solicitud_renovacion: document.getElementById('solicitud_renovacion'),
        solicitud_nueva: document.getElementById('solicitud_nueva'),
        fecha: document.getElementById('fecha'),
        beneficio: document.getElementById('beneficio'),
        nombre: document.getElementById('nombre'),
        ci: document.getElementById('ci'),
        fecha_nacimiento: document.getElementById('fecha_nacimiento'),
        nacimiento: document.getElementById('nacimiento'),
        edad: document.getElementById('edad'),
        estado_civil: document.getElementById('estado_civil'),
        telefono: document.getElementById('telefono'),
        trabaja_si: document.getElementById('trabaja_si'),
        trabaja_no: document.getElementById('trabaja_no'),
        ocupacion: document.getElementById('ocupacion'),
        lugar_trabajo: document.getElementById('lugar_trabajo'),
        sueldo: document.getElementById('sueldo'),
        carga_familiar_si: document.getElementById('carga_familiar_si'),
        carga_familiar_no: document.getElementById('carga_familiar_no'),
        hijos: document.getElementById('hijos'),
        dir_hab: document.getElementById('dir_hab'),
        dir_res: document.getElementById('dir_res'),
        // Paso 2: educativos
        especialidad: document.getElementById('especialidad'),
        sem_tra: document.getElementById('sem_tra'),
        turno: document.getElementById('turno'),
        seccion: document.getElementById('seccion'),
        correo: document.getElementById('correo'),
    };

    const showError = (field, msg) => {
        if (!field) return;
        const errorElement = document.getElementById(`${field.id}Error`);
        if (errorElement) {
            errorElement.textContent = msg;
            errorElement.style.display = 'block';
        }
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        if (window.jQuery && $(field).hasClass('select2')) {
            $(field).next('.select2-container').find('.select2-selection')
                .addClass('is-invalid').removeClass('is-valid');
        }
    };

    const clearError = (field) => {
        if (!field) return;
        const errorElement = document.getElementById(`${field.id}Error`);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        if (window.jQuery && $(field).hasClass('select2')) {
            $(field).next('.select2-container').find('.select2-selection')
                .removeClass('is-invalid').addClass('is-valid');
        }
    };

    // La foto es OPCIONAL; si viene, debe ser imagen válida (el backend
    // vuelve a validar extensión + MIME antes de generar el PDF).
    function validar_imagen() {
        const valor = elements.imagen_se ? elements.imagen_se.value : '';
        if (!valor) {
            clearError(elements.imagen_se);
            return true;
        }
        const extension = (valor.split('.').pop() || '').toLowerCase();
        if (!['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            showError(elements.imagen_se, 'Solo imágenes JPG, JPEG, PNG o GIF.');
            return false;
        }
        clearError(elements.imagen_se);
        return true;
    }

    function validar_generales() {
        let valid = true;
        if (!elements.fecha.value) { showError(elements.fecha, 'La fecha es requerida'); valid = false; } else clearError(elements.fecha);
        if (!elements.beneficio.value.trim()) { showError(elements.beneficio, 'El beneficio es requerido'); valid = false; } else clearError(elements.beneficio);
        return valid;
    }

    function validar_personales() {
        let valid = true;
        ['nombre', 'ci', 'fecha_nacimiento', 'nacimiento', 'edad', 'estado_civil', 'telefono'].forEach((id) => {
            const el = elements[id];
            if (!el.value) {
                showError(el, 'Este campo es requerido');
                valid = false;
            } else {
                clearError(el);
            }
        });
        return valid;
    }

    function validar_laborales() {
        let valid = true;
        if (elements.trabaja_si.checked) {
            if (!elements.ocupacion.value.trim()) { showError(elements.ocupacion, 'Requerido'); valid = false; } else clearError(elements.ocupacion);
            if (!elements.lugar_trabajo.value.trim()) { showError(elements.lugar_trabajo, 'Requerido'); valid = false; } else clearError(elements.lugar_trabajo);
            if (!elements.sueldo.value.trim() || elements.sueldo.value === 'BsD') { showError(elements.sueldo, 'Requerido'); valid = false; } else clearError(elements.sueldo);
        } else {
            clearError(elements.ocupacion);
            clearError(elements.lugar_trabajo);
            clearError(elements.sueldo);
        }
        return valid;
    }

    function validar_carga() {
        let valid = true;
        if (elements.carga_familiar_si.checked) {
            if (!elements.hijos.value.trim()) { showError(elements.hijos, 'Requerido'); valid = false; } else clearError(elements.hijos);
        } else {
            clearError(elements.hijos);
        }
        if (!elements.dir_hab.value.trim()) { showError(elements.dir_hab, 'Requerido'); valid = false; } else clearError(elements.dir_hab);
        if (!elements.dir_res.value.trim()) { showError(elements.dir_res, 'Requerido'); valid = false; } else clearError(elements.dir_res);
        return valid;
    }

    function validar_educativos() {
        let valid = true;
        ['especialidad', 'sem_tra', 'turno', 'seccion', 'correo'].forEach((id) => {
            const el = elements[id];
            if (!el.value) {
                showError(el, 'Requerido');
                valid = false;
            } else {
                clearError(el);
            }
        });
        return valid;
    }

    // Exponer función de validación global (la llama estudio.js).
    window.validarEstudioSocioeconomico = function () {
        let isValid = true;
        if (!validar_imagen()) isValid = false;
        if (!validar_generales()) isValid = false;
        if (!validar_personales()) isValid = false;
        if (!validar_laborales()) isValid = false;
        if (!validar_carga()) isValid = false;
        if (!validar_educativos()) isValid = false;

        if (!isValid) {
            AlertManager.warning(
                'Campos incompletos',
                'Revisa los campos resaltados en rojo antes de generar el PDF.'
            );
        }
        return isValid;
    };

    // Limpiar errores al escribir/cambiar.
    Object.values(elements).forEach((el) => {
        if (el) {
            el.addEventListener('input', () => clearError(el));
            el.addEventListener('change', () => clearError(el));
        }
    });
});
