// dist/js/modulos/psicologia/crear.js
// Solo la lógica de ESTA pantalla: catálogos, toggle por tipo de consulta,
// enviar y refrescar. Las validaciones viven en validaciones.js.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-psicologia');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.PsicologiaTour) {
        btnAyuda.addEventListener('click', () => window.PsicologiaTour.iniciar());
    }

    // Validador reutilizable (si todavía no existe el archivo, seguimos sin él).
    const validador = window.PsicologiaValidaciones
        ? window.PsicologiaValidaciones.configurar(form)
        : null;

    // -----------------------------------------------------------------
    // 1) Catálogos (beneficiarios + patologías) en UNA sola llamada.
    //    GET api/psicologia/catalogos
    //    → { exito:true, datos:{ patologias:[...], beneficiarios:[...] } }
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
            const catalogo = await apiFetch(BASE_URL + 'api/psicologia/catalogos');

            pintarSelect(
                'id_beneficiario',
                catalogo.beneficiarios || [],
                'Seleccione…',
                (b) => ({
                    value: b.id_beneficiario,
                    texto: `${b.tipo_cedula || ''}-${b.cedula || ''} ${b.nombres || ''} ${b.apellidos || ''}`.trim(),
                })
            );

            pintarSelect(
                'id_patologia',
                catalogo.patologias || [],
                'Seleccione…',
                (p) => ({
                    value: p.id_patologia,
                    texto: p.nombre_patologia || p.nombre || '',
                })
            );

            // Recién después de insertar las opciones inicializamos Select2.
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron los catálogos de psicología:', e);
            AlertManager.error('Catálogos', 'No se pudieron cargar beneficiarios ni patologías.');
        }
    })();

    // -----------------------------------------------------------------
    // 2) Toggle por tipo_consulta.
    //
    //  Cada campo opcional del formulario tiene DOS inputs con el mismo name:
    //    - El visible  (id="campo")     → activo cuando el tipo SÍ aplica.
    //    - El hidden   (id="campo_na")  → value="No aplica", activo cuando NO aplica.
    //
    //  Solo uno de los dos queda habilitado a la vez, así FormData envía
    //  siempre un valor (real o "No aplica") y nunca dos por el mismo name.
    // -----------------------------------------------------------------
    const tipoSelect = document.getElementById('tipo_consulta');

    // Mapa: id de campo visible → bandera que indica si aplica al tipo actual.
    function aplicarTipo() {
        if (!tipoSelect) return;
        const tipo = tipoSelect.value;

        const esDiagnostico = tipo === 'Diagnóstico';
        const esRetiro      = tipo === 'Retiro temporal';
        const esCambio      = tipo === 'Cambio de carrera';

        // Mostrar/ocultar secciones completas.
        toggleSeccion('campos-diagnostico', esDiagnostico);
        toggleSeccion('campos-retiro',      esRetiro);
        toggleSeccion('campos-cambio',      esCambio);

        // Diagnóstico
        togglePar('id_patologia',    esDiagnostico);
        togglePar('diagnostico',     esDiagnostico);
        togglePar('tratamiento_gen', esDiagnostico);

        // Retiro temporal
        togglePar('motivo_retiro',   esRetiro);
        togglePar('duracion_retiro', esRetiro);

        // Cambio de carrera
        togglePar('motivo_cambio',   esCambio);
    }

    function toggleSeccion(id, visible) {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('d-none', !visible);
    }

    /**
     * Habilita el input visible (si activo=true) y deshabilita su gemelo _na,
     * o viceversa. Los campos con Select2 requieren notificarle al plugin
     * para que repinte su estado deshabilitado.
     */
    function togglePar(idVisible, activo) {
        const visible = document.getElementById(idVisible);
        const na      = document.getElementById(idVisible + '_na');

        if (visible) {
            visible.disabled = !activo;
            refrescarSelect2(visible);
        }
        if (na) {
            na.disabled = activo;
        }
    }

    function refrescarSelect2(el) {
        if (typeof jQuery === 'undefined' || !$.fn || !$.fn.select2) return;
        if ($(el).hasClass('select2')) {
            // 'change.select2' notifica a Select2 sin disparar el 'change' del formulario.
            $(el).trigger('change.select2');
        }
    }

    if (tipoSelect) {
        // Evento nativo (por si acaso) + eventos de Select2.
        tipoSelect.addEventListener('change', aplicarTipo);
        if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
            $(tipoSelect).on('change select2:select select2:clear', aplicarTipo);
        }
        // Estado inicial (por defecto: Diagnóstico).
        aplicarTipo();
    }

    // -----------------------------------------------------------------
    // 3) Enviar.
    // -----------------------------------------------------------------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (validador) {
            const ok = await validador.validarTodo();
            if (!ok) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }
        }

        const datos = Object.fromEntries(new FormData(form).entries());

        // Normalizar tipos numéricos conocidos (el backend igual valida).
        if (datos.id_beneficiario && datos.id_beneficiario !== 'No aplica') {
            datos.id_beneficiario = parseInt(datos.id_beneficiario, 10);
        }
        if (datos.id_patologia && datos.id_patologia !== 'No aplica') {
            datos.id_patologia = parseInt(datos.id_patologia, 10);
        }

        try {
            await apiFetch(BASE_URL + 'api/psicologia/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrado!', 'La consulta psicológica se guardó correctamente.');

            form.reset();

            // form.reset() no dispara 'change' de Select2 por sí solo y tampoco
            // reinicia el estado disabled. El helper global emite 'change' sobre
            // los Select2 al reset; aun así lo forzamos por seguridad.
            if (window.initSelect2) window.initSelect2(form);
            aplicarTipo();

            if (window.PsicologiaStats) window.PsicologiaStats.cargar();
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