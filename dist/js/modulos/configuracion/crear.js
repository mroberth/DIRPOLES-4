// dist/js/modulos/configuracion/crear.js
// Envío de los formularios de creación de catálogos.
document.addEventListener('DOMContentLoaded', function () {
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.ConfiguracionTour) {
        btnAyuda.addEventListener('click', () => window.ConfiguracionTour.iniciar());
    }

    document.querySelectorAll('form.form-config').forEach((form) => {
        const validador = window.ConfiguracionValidaciones.configurar(form, {});

        form.addEventListener('submit', async (ev) => {
            ev.preventDefault();

            if (!validador.validarTodo()) {
                AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
                return;
            }

            const datos = Object.fromEntries(new FormData(form).entries());
            datos.catalogo = form.dataset.tipo;

            try {
                await apiFetch(BASE_URL + 'api/configuracion/crear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos),
                });

                AlertManager.success('¡Registrado!', 'El registro se guardó correctamente.');
                form.reset();
                if (window.initSelect2) window.initSelect2(form);
                validador.limpiar();
            } catch (error) {
                if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                    AlertManager.warning('Revisa los datos', error.mensaje);
                } else {
                    AlertManager.error('Error', error.mensaje);
                }
            }
        });
    });
});
