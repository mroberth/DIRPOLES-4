// dist/js/modulos/horario/crear.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-horario');
    if (!form) return;
    document.getElementById('btn-ayuda')?.addEventListener('click', () => window.HorarioTour?.iniciar());
    const empleado = document.getElementById('id_empleado');
    const validador = window.HorarioValidaciones.configurar(form);

    function texto(psicologo) {
        return `${psicologo.nombre} ${psicologo.apellido || ''} (${psicologo.tipo_cedula}-${psicologo.cedula})`;
    }
    async function cargarPsicologos() {
        const psicologos = await apiFetch(BASE_URL + 'api/horarios/psicologos');
        empleado.innerHTML = '<option value="">Seleccione un psicólogo...</option>';
        psicologos.forEach((psicologo) => {
            const opcion = document.createElement('option');
            opcion.value = psicologo.id_empleado;
            opcion.textContent = texto(psicologo);
            empleado.appendChild(opcion);
        });
        if (window.initSelect2) window.initSelect2(form);
    }
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (!validador.validarTodo()) {
            AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
            return;
        }
        try {
            await apiFetch(BASE_URL + 'api/horarios/crear', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
            });
            AlertManager.success('Horario registrado', 'El horario se guardó correctamente.');
            form.reset(); validador.limpiar();
            window.HorarioStats?.cargar();
        } catch (error) {
            AlertManager.error('No se pudo registrar', error.mensaje || 'Error inesperado.');
        }
    });
    cargarPsicologos().catch((error) => AlertManager.error('Error', error.mensaje || 'No se cargaron los psicólogos.'));
});
