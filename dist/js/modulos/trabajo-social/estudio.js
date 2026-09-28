// dist/js/modulos/trabajo-social/estudio.js
// ------------------------------------------------------------------
// Offcanvas del Estudio Socioeconómico (creación, w-50, 5 pasos).
// Adaptado de ../DIRPOLES_4/.../estudio-socioe.php:
//   - El envío va por apiFetch a api/trabajo-social/estudio/generar
//     (contrato JSON de Respuesta, no el fetch suelto del viejo).
//   - Los ~100 campos NO se persisten: solo genera el PDF y lo vincula
//     a la exoneración pendiente (decisión del usuario).
//   - La validación de campos vive en validaciones-estudio.js.
//   - iniciarEstudio() lo invoca pendientes.js desde la columna Acción.
// Las funciones de navegación/filtros son GLOBALES porque el markup
// usa onclick/onkeypress inline (herencia del sistema viejo).
// ------------------------------------------------------------------

// ---------- Navegación entre pasos ----------
function cambiarPaso(targetId) {
    const triggerEl = document.querySelector(`#tab-${targetId}`);
    if (triggerEl) new bootstrap.Tab(triggerEl).show();
}

// ---------- Filtros de teclado (inline onkeypress) ----------
function soloNumeros(e) {
    const key = e.keyCode || e.which;
    const tecla = String.fromCharCode(key).toString();
    const numeros = '0123456789';
    const especiales = [8, 13];
    if (numeros.indexOf(tecla) === -1 && !especiales.includes(key)) {
        e.preventDefault();
    }
}

function soloLetras(e) {
    const key = e.keyCode || e.which;
    const tecla = String.fromCharCode(key).toString();
    const letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz ';
    const especiales = [8, 13, 32];
    if (letras.indexOf(tecla) === -1 && !especiales.includes(key)) {
        e.preventDefault();
    }
}

function soloTexto(e) {
    return soloLetras(e);
}

function soloSueldos(e) {
    const key = e.keyCode || e.which;
    const tecla = String.fromCharCode(key).toString();
    const validos = '0123456789.,';
    const especiales = [8, 13];
    if (validos.indexOf(tecla) === -1 && !especiales.includes(key)) {
        e.preventDefault();
    }
}

function mantenerPrefijo(event) {
    const input = event.target;
    const prefijo = 'BsD ';
    if (!input.value.startsWith(prefijo)) {
        input.value = prefijo + input.value.slice(prefijo.length).replace(prefijo, '');
    }
}

// ---------- Apertura del offcanvas desde la columna Acción ----------
function iniciarEstudio(datos) {
    // 1. Cerrar el modal de pendientes
    const modalEl = document.getElementById('modalSeleccionarExoneracion');
    const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
    if (modal) modal.hide();

    // 2. Abrir el offcanvas
    const offcanvasEl = document.getElementById('offcanvasEstudioSocioeconomico');
    if (!offcanvasEl) return;
    bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();

    // 3. Reset del formulario y vínculo con la exoneración
    const form = document.getElementById('formEstudioSocioeconomico');
    if (form) form.reset();
    const hiddenId = document.getElementById('id_exoneracion_estudio');
    if (hiddenId) hiddenId.value = datos.id_exoneracion;

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };

    // 4. Prellenado con los datos del beneficiario (viene del listado
    //    de pendientes: nombres, cédula, fecha nac., teléfono, correo,
    //    sección, PNF). setVal tolera campos ausentes.
    setVal('nombre', `${datos.nombres || ''} ${datos.apellidos || ''}`.trim());
    setVal('ci', datos.cedula || '');
    setVal('fecha_nacimiento', datos.fecha_nacimiento || '');
    setVal('telefono', datos.telefono || '');
    setVal('correo', datos.correo || '');
    setVal('seccion', datos.seccion || '');

    const elEspecialidad = document.getElementById('especialidad');
    if (elEspecialidad) {
        if (datos.pnf_nombre) {
            elEspecialidad.value = datos.pnf_nombre;
            elEspecialidad.readOnly = true;
        } else {
            elEspecialidad.value = '';
            elEspecialidad.readOnly = false;
        }
    }

    const elEdad = document.getElementById('edad');
    if (elEdad && datos.fecha_nacimiento) {
        const hoy = new Date();
        const nacimiento = new Date(datos.fecha_nacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const m = hoy.getMonth() - nacimiento.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
        if (edad >= 0) elEdad.value = edad;
    }
}

// ---------- Envío: genera el PDF ----------
document.addEventListener('DOMContentLoaded', function () {
    // Tour del propio offcanvas (Driver.js), distinto del tour de la
    // página (#btn-ayuda) porque requiere que el offcanvas esté abierto.
    const btnAyudaEstudio = document.getElementById('btn-ayuda-estudio');
    if (btnAyudaEstudio && window.TrabajoSocialTour) {
        btnAyudaEstudio.addEventListener('click', () => window.TrabajoSocialTour.iniciarEstudio());
    }

    const btnGenerarPDF = document.getElementById('btnGenerarPDF');
    if (!btnGenerarPDF) return;

    btnGenerarPDF.addEventListener('click', async function () {
        // Validación visual de campos (validaciones-estudio.js).
        if (typeof window.validarEstudioSocioeconomico === 'function'
            && !window.validarEstudioSocioeconomico()) {
            return;
        }

        const form = document.getElementById('formEstudioSocioeconomico');
        const formData = new FormData(form); // incluye imagen + id_exoneracion

        btnGenerarPDF.disabled = true;
        btnGenerarPDF.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generando…';

        try {
            // FormData: apiFetch NO debe fijar Content-Type (lo pone el navegador).
            const datos = await apiFetch(BASE_URL + 'api/trabajo-social/estudio/generar', {
                method: 'POST',
                body: formData,
            });

            AlertManager.success(
                '¡PDF generado!',
                'El estudio socioeconómico se generó y quedó vinculado a la exoneración.'
            );
            if (window.TrabajoSocialStats) window.TrabajoSocialStats.cargar();
            const offcanvasEl = document.getElementById('offcanvasEstudioSocioeconomico');
            if (offcanvasEl) bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).hide();
            // El listado de pendientes se recarga solo en la próxima apertura
            // del modal (pendientes.js escucha show.bs.modal).
        } catch (error) {
            if (['VALIDATION_ERROR', 'ALREADY_EXISTS'].includes(error.codigo)) {
                AlertManager.warning('No se pudo generar', error.mensaje);
            } else {
                AlertManager.error('No se pudo generar', error.mensaje || 'Error inesperado.');
            }
        } finally {
            btnGenerarPDF.disabled = false;
            btnGenerarPDF.innerHTML = '<i class="fas fa-file-pdf me-2"></i> Generar PDF';
        }
    });

    // Exclusividad de radios con nombres distintos (herencia del viejo):
    // tr_si/tr_no, cf_si/cf_no, renovacion/nueva, tenencia y tipo de vivienda.
    const grupos = [
        ['tr_si', 'tr_no'],
        ['cf_si', 'cf_no'],
        ['renovacion', 'nueva'],
        ['propia', 'opcion_compra', 'alquilada', 'prestada', 'hipoteca', 'pagando', 'tenencia_otros'],
        ['casa', 'quinta', 'apto', 'rural', 'inavi', 'r_r', 'r_u'],
    ];
    document.querySelectorAll('input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', (evento) => {
            const grupo = grupos.find((nombres) => nombres.includes(evento.target.name));
            if (!grupo) return;
            document.querySelectorAll(grupo.map((n) => `input[name="${n}"]`).join(', '))
                .forEach((r) => {
                    if (r !== evento.target) r.checked = false;
                });
        });
    });
});
