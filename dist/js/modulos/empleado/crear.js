// dist/js/modulos/empleado/crear.js
// ------------------------------------------------------------------
// Solo la lógica de ESTA pantalla: cargar el catálogo, validar,
// enviar y refrescar. Las validaciones y el tour viven aparte y se
// reutilizan en editar.js.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-empleado');
    if (!form) return;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.EmpleadoTour) {
        btnAyuda.addEventListener('click', () => window.EmpleadoTour.iniciar());
    }

    // Validaciones reutilizables (el mismo archivo sirve para editar).
    const validador = window.EmpleadoValidaciones.configurar(form, { idExcluir: 0 });

    // 1) Cargar el catálogo de tipos en el <select>
    (async () => {
        try {
            const tipos = await apiFetch(BASE_URL + 'api/empleados/tipos');
            const sel = document.getElementById('id_tipo_empleado');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            tipos.forEach((t) => {
                const opt = document.createElement('option');
                opt.value = t.id_tipo_emp;
                opt.textContent = t.tipo;
                sel.appendChild(opt);
            });
            // Re-inicializa Select2 para que lea las nuevas opciones.
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron los tipos:', e);
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
        datos.estatus = parseInt(datos.estatus, 10);
        datos.id_tipo_empleado = parseInt(datos.id_tipo_empleado, 10);

        try {
            const nuevo = await apiFetch(BASE_URL + 'api/empleados/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success('¡Registrado!', nuevo.nombre + ' ' + (nuevo.apellido || '') + ' se creó correctamente.');
            form.reset();
            // Select2 no se limpia con form.reset(): re-inicializamos.
            if (window.initSelect2) window.initSelect2(form);

            // Refrescar las tarjetas de resumen sin recargar la página.
            if (window.EmpleadoStats) window.EmpleadoStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
