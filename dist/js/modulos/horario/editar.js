// dist/js/modulos/horario/editar.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-editar-horario');
    const modalElemento = document.getElementById('modalEditarHorario');
    if (!form || !modalElemento) return;
    const modal = new bootstrap.Modal(modalElemento);
    const empleado = document.getElementById('editar_id_empleado');
    const psicologos = {};
    const validador = window.HorarioValidaciones.configurar(form, { empleado: '#editar_id_empleado', dia: '#editar_dia_semana', inicio: '#editar_hora_inicio', fin: '#editar_hora_fin' });
    async function cargarPsicologos() {
        const lista = await apiFetch(BASE_URL + 'api/horarios/psicologos');
        empleado.innerHTML = '';
        lista.forEach((psicologo) => { const opcion = document.createElement('option'); opcion.value = psicologo.id_empleado; opcion.textContent = `${psicologo.nombre} ${psicologo.apellido || ''} (${psicologo.tipo_cedula}-${psicologo.cedula})`; empleado.appendChild(opcion); psicologos[psicologo.id_empleado] = true; });
        if (window.initSelect2) window.initSelect2(form);
    }
    window.HorarioEditar = {
        async abrir(horario) {
            if (!horario) return;
            try {
                await cargarPsicologos();
                document.getElementById('editar_id_horario').value = horario.id_horario;
                empleado.value = horario.id_empleado;
                document.getElementById('editar_dia_semana').value = horario.dia_semana;
                document.getElementById('editar_hora_inicio').value = horario.hora_inicio;
                document.getElementById('editar_hora_fin').value = horario.hora_fin;
                $(empleado).trigger('change'); $('#editar_dia_semana').trigger('change');
                validador.limpiar(); modal.show();
            } catch (error) { AlertManager.error('No se pudo abrir', error.mensaje || 'No se cargaron los datos.'); }
        },
    };
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (!validador.validarTodo()) { AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.'); return; }
        try {
            await apiFetch(BASE_URL + 'api/horarios/actualizar', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(Object.fromEntries(new FormData(form).entries())) });
            modal.hide(); AlertManager.success('Horario actualizado', 'Los cambios se guardaron correctamente.'); window.HorariosConsultar?.cargar(); window.HorarioStats?.cargar();
        } catch (error) { AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.'); }
    });
});
