// dist/js/modulos/trabajo-social/crear.js
// Solo la lógica de ESTA pantalla: catálogo global, pestañas y envío
// de los formularios (multipart: beca con planilla, exoneración con
// carta; FAMES y embarazadas van sin archivo). Las validaciones viven
// en validaciones.js.
document.addEventListener('DOMContentLoaded', function () {
    const formBecas = document.getElementById('form-becas');
    const formExoneracion = document.getElementById('form-exoneracion');
    const formFames = document.getElementById('form-fames');
    const formEmbarazadas = document.getElementById('form-embarazadas');
    if (!formBecas && !formExoneracion && !formFames && !formEmbarazadas) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.TrabajoSocialTour) {
        btnAyuda.addEventListener('click', () => window.TrabajoSocialTour.iniciar());
    }

    const vBecas = (formBecas && window.TrabajoSocialValidaciones)
        ? window.TrabajoSocialValidaciones.configurar(formBecas)
        : null;
    const vExoneracion = (formExoneracion && window.TrabajoSocialValidaciones)
        ? window.TrabajoSocialValidaciones.configurarExoneracion(formExoneracion)
        : null;
    const vFames = (formFames && window.TrabajoSocialValidaciones)
        ? window.TrabajoSocialValidaciones.configurarFames(formFames)
        : null;
    const vEmbarazada = (formEmbarazadas && window.TrabajoSocialValidaciones)
        ? window.TrabajoSocialValidaciones.configurarEmbarazada(formEmbarazadas)
        : null;

    // -----------------------------------------------------------------
    // 1) Catálogo GLOBAL en UNA llamada: beneficiarios (con genero,
    //    para validar la gestación) y patologías (para FAMES y
    //    embarazadas). Los select de patología viven en sus forms.
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
            // data-genero en cada beneficiario: lo lee la validación
            // de género femenino de la pestaña de embarazadas.
            if (it.genero !== undefined && it.genero !== null) {
                opt.dataset.genero = String(it.genero).trim();
            }
            sel.appendChild(opt);
        });
    }

    (async () => {
        try {
            const catalogo = await apiFetch(BASE_URL + 'api/trabajo-social/catalogos');

            pintarSelect(
                'id_beneficiario',
                catalogo.beneficiarios || [],
                'Seleccione…',
                (b) => ({
                    value: b.id_beneficiario,
                    texto: `${b.tipo_cedula || ''}-${b.cedula || ''} ${b.nombres || ''} ${b.apellidos || ''}`.trim(),
                })
            );

            const patologias = catalogo.patologias || [];
            pintarSelect('patologia_fames', patologias, 'Seleccione una patología…',
                (p) => ({ value: p.id_patologia, texto: p.nombre_patologia }));
            pintarSelect('patologia_embarazada', patologias, 'Seleccione patología de embarazo…',
                (p) => ({ value: p.id_patologia, texto: p.nombre_patologia }));

            // Recién después de insertar las opciones inicializamos Select2
            // en TODO el documento (el select global está fuera de los forms).
            if (window.initSelect2) window.initSelect2(document);
        } catch (e) {
            console.error('No se cargó el catálogo de trabajo social:', e);
            AlertManager.error('Catálogos', 'No se pudieron cargar los catálogos.');
        }
    })();

    // -----------------------------------------------------------------
    // 2) Envío compartido: multipart/form-data (incluye el PDF).
    //    apiFetch sin header Content-Type: fetch pone el boundary solo.
    // -----------------------------------------------------------------
    async function enviar(form, validador, url, mensajeExito) {
        if (validador) {
            const ok = validador.validarTodo();
            if (!ok) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }
        }

        const fd = new FormData(form);
        fd.set('id_beneficiario', document.getElementById('id_beneficiario').value);

        try {
            await apiFetch(url, { method: 'POST', body: fd });

            AlertManager.success('¡Registrado!', mensajeExito);
            if (window.TrabajoSocialStats) window.TrabajoSocialStats.cargar();

            form.reset();

            // form.reset() no dispara 'change' de Select2 por sí solo: el
            // helper global lo hace y aquí lo reforzamos tras el reset.
            if (window.initSelect2) window.initSelect2(form);
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Registro no encontrado', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    formBecas && formBecas.addEventListener('submit', function (ev) {
        ev.preventDefault();
        enviar(formBecas, vBecas, BASE_URL + 'api/trabajo-social/becas/crear',
            'La beca se registró correctamente.');
    });

    // -----------------------------------------------------------------
    // 3) Exoneración: motivo "Otro" muestra el campo de detalle.
    // -----------------------------------------------------------------
    const motivo = formExoneracion && formExoneracion.querySelector('#motivo');
    const otroContenedor = formExoneracion && formExoneracion.querySelector('#otroMotivoContainer');

    function refrescarDetalleMotivo() {
        if (!otroContenedor || !motivo) return;
        otroContenedor.hidden = motivo.value !== 'Otro';
    }

    motivo && motivo.addEventListener('change', refrescarDetalleMotivo);
    formExoneracion && formExoneracion.addEventListener('reset', () => {
        // El reset deja el select vacío tras un tick: ocultar el detalle.
        window.setTimeout(refrescarDetalleMotivo, 0);
    });

    formExoneracion && formExoneracion.addEventListener('submit', function (ev) {
        ev.preventDefault();
        enviar(formExoneracion, vExoneracion, BASE_URL + 'api/trabajo-social/exoneraciones/crear',
            'La exoneración se registró correctamente.');
    });

    // -----------------------------------------------------------------
    // 4) FAMES: tipo de ayuda "Otros" muestra el campo de detalle.
    // -----------------------------------------------------------------
    const tipoAyuda = formFames && formFames.querySelector('#tipo_ayuda');
    const otroTipoContenedor = formFames && formFames.querySelector('#otroTipoContainer');

    function refrescarDetalleTipoAyuda() {
        if (!otroTipoContenedor || !tipoAyuda) return;
        otroTipoContenedor.hidden = tipoAyuda.value !== 'Otros';
    }

    tipoAyuda && tipoAyuda.addEventListener('change', refrescarDetalleTipoAyuda);
    formFames && formFames.addEventListener('reset', () => {
        window.setTimeout(refrescarDetalleTipoAyuda, 0);
    });

    formFames && formFames.addEventListener('submit', function (ev) {
        ev.preventDefault();
        enviar(formFames, vFames, BASE_URL + 'api/trabajo-social/fames/crear',
            'El registro FAMES se guardó correctamente.');
    });

    // -----------------------------------------------------------------
    // 5) Embarazadas: solo el envío (el toggle de género lo valida
    //    validaciones.js contra el beneficiario global).
    // -----------------------------------------------------------------
    formEmbarazadas && formEmbarazadas.addEventListener('submit', function (ev) {
        ev.preventDefault();
        enviar(formEmbarazadas, vEmbarazada, BASE_URL + 'api/trabajo-social/embarazadas/crear',
            'La gestión de embarazo se registró correctamente.');
    });
});
