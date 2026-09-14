// dist/js/modulos/beneficiario/crear.js
// Solo la lógica de ESTA pantalla: catálogo, validar, enviar y refrescar.
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-beneficiario');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.BeneficiarioTour) {
        btnAyuda.addEventListener('click', () => window.BeneficiarioTour.iniciar());
    }

    // Validaciones reutilizables (el mismo archivo sirve para editar).
    const validador = window.BeneficiarioValidaciones.configurar(form, { idExcluir: 0 });

    // 1) Catálogo de PNF
    (async () => {
        try {
            const pnfs = await apiFetch(BASE_URL + 'api/beneficiarios/pnfs');
            const sel = document.getElementById('id_pnf');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            pnfs.forEach((p) => {
                const opt = document.createElement('option');
                opt.value = p.id_pnf;
                opt.textContent = p.nombre_pnf;
                sel.appendChild(opt);
            });
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron los PNF:', e);
        }
    })();

    // 2) Enviar
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        const ok = await validador.validarTodo();
        if (!ok) {
            AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = Object.fromEntries(new FormData(form).entries());
        datos.estatus = parseInt(datos.estatus, 10);
        datos.id_pnf = parseInt(datos.id_pnf, 10);

        try {
            const nuevo = await apiFetch(BASE_URL + 'api/beneficiarios/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrado!', nuevo.nombres + ' ' + (nuevo.apellidos || '') + ' se creó correctamente.');
            form.reset();
            if (window.initSelect2) window.initSelect2(form);
            if (window.BeneficiarioStats) window.BeneficiarioStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
