// dist/js/modulos/jornadas/crear.js
// ------------------------------------------------------------------
// Solo la lógica de ESTA pantalla: catálogo de tipos de jornada, tour,
// validación y envío. Las validaciones y el tour viven en archivos aparte.
// Reglas del backend que repite la interfaz:
//  - la jornada nace 'Activa';
//  - el cierre no puede ser anterior al inicio ni estar ya en el pasado;
//  - el aforo es un entero entre 1 y 5000.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-jornada');
    if (!form) return;

    const V = window.JornadasValidaciones;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.JornadasTour) {
        btnAyuda.addEventListener('click', () => window.JornadasTour.iniciar());
    }

    const validador = V.configurar(form, { tipo: 'jornada' });

    const selTipo = document.getElementById('tipo_jornada');

    function llenarTipos(tipos) {
        selTipo.innerHTML = '<option value="">Seleccione…</option>';
        (tipos || []).forEach((tipo) => {
            const opcion = document.createElement('option');
            opcion.value = tipo;
            opcion.textContent = tipo;
            selTipo.appendChild(opcion);
        });
        if (window.initSelect2) window.initSelect2(form);
    }

    async function cargarCatalogos() {
        const catalogos = await apiFetch(BASE_URL + 'api/jornadas/catalogos');
        llenarTipos(catalogos.tipo_jornada);
    }

    // ---------- Reiniciar tras guardar ----------
    function reiniciar() {
        form.reset();
        validador.limpiar();
        if (window.initSelect2) window.initSelect2(form);
    }

    // ---------- Enviar ----------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (!validador.validarTodo()) {
            AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = {
            nombre_jornada: document.getElementById('nombre_jornada').value.trim(),
            tipo_jornada:   document.getElementById('tipo_jornada').value,
            aforo_maximo:   Number(document.getElementById('aforo_maximo').value),
            fecha_inicio:   document.getElementById('fecha_inicio').value,
            fecha_fin:      document.getElementById('fecha_fin').value,
            ubicacion:      document.getElementById('ubicacion').value.trim(),
            descripcion:    document.getElementById('descripcion').value.trim(),
        };

        try {
            const nueva = await apiFetch(BASE_URL + 'api/jornadas/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success(
                'Jornada creada',
                `La jornada "${nueva.nombre_jornada}" quedó ACTIVA con aforo para `
                + `${nueva.aforo_maximo} personas. Registra a los asistentes desde su detalle.`
            );
            reiniciar();
            if (window.JornadasStats) window.JornadasStats.cargar();
        } catch (error) {
            if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });

    cargarCatalogos().catch((error) => {
        console.error('Catálogos de jornadas:', error);
        AlertManager.error('No se cargaron los datos', error.mensaje || 'Intenta nuevamente.');
    });
});
