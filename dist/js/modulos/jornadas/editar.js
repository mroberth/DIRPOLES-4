// dist/js/modulos/jornadas/editar.js
// ------------------------------------------------------------------
// Modal de EDICIÓN de la cabecera de una jornada (pantalla consultar).
// Solo edita los campos de la cabecera + estatus; jamás toca asistentes
// ni diagnósticos. El tour lo reutiliza desde el botón "Ayuda" del modal.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-jornada');
    if (!form) return;

    const V = window.JornadasValidaciones;
    const validador = V.configurar(form, { tipo: 'jornada' });

    const modalEl = document.getElementById('modalJornada');

    // Tour del modal (mismos pasos que crear; los ausentes se omiten).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.JornadasTour) {
        btnAyuda.addEventListener('click', () => window.JornadasTour.iniciar());
    }

    // Tipo de la jornada en edición: puede no estar en el catálogo fijo
    // (datos heredados, p. ej. 'Medica'), así que se conserva como opción.
    let tipoPendiente = '';

    /** Añade al <select> una opción que no venga del catálogo. */
    function asegurarOpcion(sel, valor) {
        if (!valor) return;
        const existe = Array.from(sel.options).some((o) => o.value === valor);
        if (!existe) {
            const opcion = document.createElement('option');
            opcion.value = valor;
            opcion.textContent = valor;
            sel.appendChild(opcion);
        }
    }

    function llenarTipos(tipos) {
        const sel = document.getElementById('tipo_jornada');
        const actual = tipoPendiente || sel.value;
        sel.innerHTML = '<option value="">Seleccione…</option>';
        (tipos || []).forEach((tipo) => {
            const opcion = document.createElement('option');
            opcion.value = tipo;
            opcion.textContent = tipo;
            sel.appendChild(opcion);
        });
        if (actual) {
            asegurarOpcion(sel, actual);
            sel.value = actual;
        }
        if (window.initSelect2) window.initSelect2(form);
    }

    apiFetch(BASE_URL + 'api/jornadas/catalogos')
        .then((catalogos) => llenarTipos(catalogos.tipo_jornada))
        .catch((error) => {
            console.error('Catálogos de jornadas:', error);
            AlertManager.error('No se cargaron los datos', error.mensaje || 'Intenta nuevamente.');
        });

    /** 'YYYY-MM-DD HH:mm:ss' → 'YYYY-MM-DDTHH:mm' (input datetime-local). */
    function aDatetimeLocal(valor) {
        if (!valor) return '';
        return String(valor).replace(' ', 'T').slice(0, 16);
    }

    function cerrar() {
        if (modalEl && typeof bootstrap !== 'undefined') {
            const instancia = bootstrap.Modal.getInstance(modalEl);
            if (instancia) instancia.hide();
        }
    }

    /** Abre el modal con los datos reales de la jornada. */
    async function abrir(id) {
        try {
            const j = await apiFetch(BASE_URL + 'api/jornadas/obtener/' + encodeURIComponent(id));

            form.reset();
            validador.limpiar();

            document.getElementById('id_jornada').value = j.id_jornada;
            document.getElementById('nombre_jornada').value = j.nombre_jornada || '';
            asegurarOpcion(document.getElementById('tipo_jornada'), j.tipo_jornada || '');
            document.getElementById('tipo_jornada').value = j.tipo_jornada || '';
            tipoPendiente = j.tipo_jornada || '';
            document.getElementById('fecha_inicio').value = aDatetimeLocal(j.fecha_inicio);
            document.getElementById('fecha_fin').value = aDatetimeLocal(j.fecha_fin);
            document.getElementById('aforo_maximo').value = j.aforo_maximo;
            document.getElementById('ubicacion').value = j.ubicacion || '';
            document.getElementById('estatus').value = j.estatus || '';
            document.getElementById('descripcion').value = j.descripcion || '';

            const ref = document.getElementById('editarReferencia');
            if (ref) ref.textContent = `Jornada #${j.id_jornada} — ${j.personas} de ${j.aforo_maximo} personas`;

            if (window.initSelect2) window.initSelect2(form);

            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        } catch (error) {
            if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('No encontrada', 'La jornada ya no existe. Actualizando la lista…');
                if (window.JornadasConsultar) window.JornadasConsultar.recargar();
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ---------- Guardar ----------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (!validador.validarTodo()) {
            AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = {
            id_jornada:      Number(document.getElementById('id_jornada').value),
            nombre_jornada:  document.getElementById('nombre_jornada').value.trim(),
            tipo_jornada:    document.getElementById('tipo_jornada').value,
            fecha_inicio:    document.getElementById('fecha_inicio').value,
            fecha_fin:       document.getElementById('fecha_fin').value,
            aforo_maximo:    Number(document.getElementById('aforo_maximo').value),
            ubicacion:       document.getElementById('ubicacion').value.trim(),
            estatus:         document.getElementById('estatus').value,
            descripcion:     document.getElementById('descripcion').value.trim(),
        };

        try {
            const r = await apiFetch(BASE_URL + 'api/jornadas/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('Jornada actualizada',
                `Se guardaron los cambios de "${r.nombre_jornada}" (estatus: ${r.estatus}).`);
            cerrar();
            if (window.JornadasConsultar) window.JornadasConsultar.recargar();
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

    window.JornadasEditar = { abrir };
});
