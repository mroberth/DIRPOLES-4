// dist/js/modulos/perfil/perfil.js
// Módulo Perfil: carga los datos del empleado autenticado y guarda los cambios.
// Consume la API con apiFetch y programa contra error.codigo (estable).

let perfilValidador;
let inputClavePerfil;
let inputClaveConfirmPerfil;
let inputClaveActualPerfil;

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-perfil');
    if (!form) return; // esta página no es la del perfil

    inputClavePerfil = document.getElementById('clave');
    inputClaveConfirmPerfil = document.getElementById('clave_confirmacion');
    inputClaveActualPerfil = document.getElementById('clave_actual');
    perfilValidador = window.EmpleadoValidaciones.configurar(form, {
        claveOpcional: true,
        validarRemoto: true,
        urlValidarCorreo: 'api/perfil/validar_correo',
        urlValidarTelefono: 'api/perfil/validar_telefono',
    });

    // Mostrar/ocultar contraseñas (iconos del input-group).
    document.querySelectorAll('[data-toggle]').forEach((span) => {
        span.addEventListener('click', () => {
            const input = document.getElementById(span.dataset.toggle);
            const icono = span.querySelector('i');
            const esPassword = input.type === 'password';
            input.type = esPassword ? 'text' : 'password';
            icono.classList.toggle('fa-eye', !esPassword);
            icono.classList.toggle('fa-eye-slash', esPassword);
        });
    });

    // Confirmación de clave: se habilita solo si se escribe una nueva clave.
    inputClavePerfil.addEventListener('input', () => {
        const activo = inputClavePerfil.value.length > 0;
        inputClaveConfirmPerfil.disabled = !activo;
        if (!activo) {
            inputClaveConfirmPerfil.value = '';
            limpiarCampo(inputClaveConfirmPerfil);
        }
        validarConfirmacion();
    });

    inputClaveActualPerfil.addEventListener('input', validarClaveActual);
    inputClaveConfirmPerfil.addEventListener('input', validarConfirmacion);

    // Cargar datos actuales del perfil.
    cargarPerfil();

    form.addEventListener('submit', guardarPerfil);
    document.getElementById('btn-reset').addEventListener('click', cargarPerfil);
});

async function cargarPerfil() {
    try {
        const p = await apiFetch(BASE_URL + 'api/perfil/obtener');

        // Solo lectura (la vista los pinta; aquí se rellenan).
        setTexto('perfil-nombre', [p.nombre, p.apellido].filter(Boolean).join(' '));
        setTexto('perfil-cedula', p.cedula ? `${p.tipo_cedula}-${p.cedula}` : '—');
        setTexto('perfil-rol', p.nombre_tipo || '—');
        setTexto('perfil-desde', p.fecha_creacion || '—');

        // Editables.
        document.getElementById('correo').value = p.correo ?? '';
        document.getElementById('telefono').value = p.telefono ?? '';
        document.getElementById('direccion').value = p.direccion ?? '';

        // Contraseñas en blanco tras recargar.
        document.getElementById('clave_actual').value = '';
        document.getElementById('clave').value = '';
        const confirm = document.getElementById('clave_confirmacion');
        confirm.value = '';
        confirm.disabled = true;
        perfilValidador.limpiar();
        limpiarEstadoCampo(inputClaveActualPerfil);
        limpiarEstadoCampo(inputClaveConfirmPerfil);
    } catch (error) {
        AlertManager.show('error', error.mensaje || 'No se pudo cargar el perfil.');
    }
}

async function guardarPerfil(evento) {
    evento.preventDefault();
    const datosValidos = await perfilValidador.validarTodo();
    const claveActualValida = validarClaveActual();
    const confirmacionValida = validarConfirmacion();

    if (!datosValidos || !claveActualValida || !confirmacionValida) {
        AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
        return;
    }

    const clave = inputClavePerfil.value;

    const cuerpo = {
        correo: document.getElementById('correo').value.trim(),
        telefono: document.getElementById('telefono').value.trim(),
        direccion: document.getElementById('direccion').value.trim(),
        clave_actual: document.getElementById('clave_actual').value,
    };
    if (clave) {
        cuerpo.clave = clave;
        cuerpo.clave_confirmacion = document.getElementById('clave_confirmacion').value;
    }

    try {
        await apiFetch(BASE_URL + 'api/perfil/actualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(cuerpo),
        });

        const resultado = await Swal.fire({
            icon: 'success',
            title: '¡Perfil actualizado!',
            text: 'Tus cambios se guardaron correctamente.',
            confirmButtonText: 'Aceptar',
        });

        // Si cambió la contraseña conviene re-login: el token JWT viejo sigue
        // válido pero la contraseña almacenada ya no coincide con la memoria
        // del usuario. Recargamos el perfil en ambos casos.
        await cargarPerfil();
        document.getElementById('clave_actual').focus();
        return resultado;
    } catch (error) {
        if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'ALREADY_EXISTS') {
            AlertManager.show('warning', error.mensaje);
        } else {
            AlertManager.show('error', error.mensaje || 'No se pudo actualizar el perfil.');
        }
    }
}

// ---------- Helpers locales ----------

function setTexto(id, texto) {
    const el = document.getElementById(id);
    if (el) el.textContent = texto;
}

function validarClaveActual() {
    if (!inputClaveActualPerfil.value.trim()) {
        pintarCampoError(inputClaveActualPerfil, 'Debes escribir tu contraseña actual.');
        return false;
    }
    limpiarCampo(inputClaveActualPerfil);
    return true;
}

function validarConfirmacion() {
    if (!inputClavePerfil.value) {
        limpiarCampo(inputClaveConfirmPerfil);
        return true;
    }
    if (inputClaveConfirmPerfil.value !== inputClavePerfil.value) {
        pintarCampoError(inputClaveConfirmPerfil, 'Las contraseñas no coinciden.');
        return false;
    }
    limpiarCampo(inputClaveConfirmPerfil);
    return true;
}

function pintarCampoError(campo, mensaje) {
    const div = document.getElementById(campo.id + 'Error');
    if (div) div.textContent = mensaje;
    campo.classList.add('is-invalid');
    campo.classList.remove('is-valid');
}

function limpiarCampo(campo) {
    if (!campo) return;
    const div = document.getElementById(campo.id + 'Error');
    if (div) div.textContent = '';
    campo.classList.remove('is-invalid');
    campo.classList.add('is-valid');
}

function limpiarEstadoCampo(campo) {
    if (!campo) return;
    const div = document.getElementById(campo.id + 'Error');
    if (div) div.textContent = '';
    campo.classList.remove('is-invalid', 'is-valid');
}
