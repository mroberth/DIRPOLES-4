// dist/js/modulos/inventario/crear.js
// ------------------------------------------------------------------
// Solo la lógica de ESTA pantalla: catálogo de presentaciones, tour,
// validación y envío. Las validaciones y el tour viven en archivos
// aparte para reutilizarse en editar.js.
// El insumo se crea SIN cantidad: nace en 0 (Agotado).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-insumo');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.InventarioTour) {
        btnAyuda.addEventListener('click', () => window.InventarioTour.iniciar());
    }

    // Validaciones reutilizables (crear exige fecha de vencimiento >= hoy).
    const validador = window.InventarioValidaciones.configurar(form, { idExcluir: 0 });

    // 1) Cargar el catálogo de presentaciones en el <select>
    (async () => {
        try {
            const presentaciones = await apiFetch(BASE_URL + 'api/inventario/presentaciones');
            const sel = document.getElementById('id_presentacion');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            presentaciones.forEach((p) => {
                const opt = document.createElement('option');
                opt.value = p.id_presentacion;
                opt.textContent = p.nombre_presentacion;
                sel.appendChild(opt);
            });
            // Re-inicializa Select2 para que lea las nuevas opciones.
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron las presentaciones:', e);
        }
    })();

    // 2) Enviar el formulario
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        const ok = await validador.validarTodo();
        if (!ok) {
            AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = Object.fromEntries(new FormData(form).entries());
        datos.id_presentacion = parseInt(datos.id_presentacion, 10);

        try {
            const nuevo = await apiFetch(BASE_URL + 'api/inventario/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrado!',
                `El insumo "${nuevo.nombre_insumo}" se creó con cantidad 0 (Agotado). Registra su stock con Entrada.`);
            form.reset();
            validador.limpiar();
            // Select2 no se limpia con form.reset(): re-inicializamos.
            if (window.initSelect2) window.initSelect2(form);

            // Refrescar las tarjetas de resumen sin recargar la página.
            if (window.InventarioStats) window.InventarioStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
