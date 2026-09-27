// dist/js/modulos/discapacidad/crear.js
// Solo la lógica de ESTA pantalla: catálogo, enviar y refrescar.
// Las validaciones viven en validaciones.js.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-discapacidad');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.DiscapacidadTour) {
        btnAyuda.addEventListener('click', () => window.DiscapacidadTour.iniciar());
    }

    // Validador reutilizable (si todavía no existe el archivo, seguimos sin él).
    const validador = window.DiscapacidadValidaciones
        ? window.DiscapacidadValidaciones.configurar(form)
        : null;

    // -----------------------------------------------------------------
    // 1) Catálogo (beneficiarios) en UNA llamada.
    //    GET api/discapacidad/catalogos
    //    → { exito:true, datos:{ beneficiarios:[...] } }
    // -----------------------------------------------------------------
    function pintarSelect(selectId, items, placeholder, mapear) {
        const sel = document.getElementById(selectId);
        if (!sel) return;

        sel.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach((it) => {
            const opt = document.createElement('option');
            const { value, texto } = mapear(it);
            opt.value = value;
            opt.textContent = texto;
            sel.appendChild(opt);
        });
    }

    (async () => {
        try {
            const catalogo = await apiFetch(BASE_URL + 'api/discapacidad/catalogos');

            pintarSelect(
                'id_beneficiario',
                catalogo.beneficiarios || [],
                'Seleccione…',
                (b) => ({
                    value: b.id_beneficiario,
                    texto: `${b.tipo_cedula || ''}-${b.cedula || ''} ${b.nombres || ''} ${b.apellidos || ''}`.trim(),
                })
            );

            // Recién después de insertar las opciones inicializamos Select2.
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargó el catálogo de discapacidad:', e);
            AlertManager.error('Catálogos', 'No se pudieron cargar los beneficiarios.');
        }
    })();

    // -----------------------------------------------------------------
    // 2) Enviar.
    // -----------------------------------------------------------------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (validador) {
            const ok = validador.validarTodo();
            if (!ok) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }
        }

        const datos = Object.fromEntries(new FormData(form).entries());
        datos.id_beneficiario = parseInt(datos.id_beneficiario, 10);

        try {
            await apiFetch(BASE_URL + 'api/discapacidad/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrado!', 'El diagnóstico de discapacidad se guardó correctamente.');

            form.reset();

            // form.reset() no dispara 'change' de Select2 por sí solo: el helper
            // global lo hace y aquí lo reforzamos tras el reset.
            if (window.initSelect2) window.initSelect2(form);

            if (window.DiscapacidadStats) window.DiscapacidadStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
