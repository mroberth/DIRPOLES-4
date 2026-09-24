// dist/js/modulos/horario/consultar.js
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('tbodyHorarios');
    if (!tbody) return;
    let horarios = [];
    let tabla = null;
    const escapar = (valor) => { const div = document.createElement('div'); div.textContent = valor ?? ''; return div.innerHTML; };
    function fila(horario) {
        return `<tr><td>${escapar(horario.psicologo)}</td><td>${escapar(horario.cedula)}</td><td>${escapar(horario.dia_semana)}</td><td>${escapar(horario.hora_inicio)}</td><td>${escapar(horario.hora_fin)}</td><td class="text-center text-nowrap"><button type="button" class="btn btn-sm btn-outline-secondary btn-editar-horario" data-id="${horario.id_horario}" title="Editar"><i class="fas fa-pen"></i></button> <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-horario" data-id="${horario.id_horario}" title="Eliminar"><i class="fas fa-trash"></i></button></td></tr>`;
    }
    function construirTabla() {
        if (!window.DataTableHelper) return;
        tabla = DataTableHelper.inicializar('#tablaHorarios', {
            titulo: 'Horarios de Psicología',
            orden: [[0, 'asc'], [2, 'asc']],
            pageLength: 10,
            columnasExport: [0, 1, 2, 3, 4],
            columnDefs: [{ targets: 5, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
        });
    }
    async function cargar() {
        try {
            if (tabla) { tabla.destroy(); tabla = null; }
            horarios = await apiFetch(BASE_URL + 'api/horarios/listar');
            tbody.innerHTML = horarios.map(fila).join(''); construirTabla();
        } catch (error) { tbody.innerHTML = ''; AlertManager.error('Error', error.mensaje || 'No se cargaron los horarios.'); }
    }
    const porId = (id) => horarios.find((horario) => String(horario.id_horario) === String(id));
    document.addEventListener('click', async (evento) => {
        const editar = evento.target.closest('.btn-editar-horario');
        if (editar) return window.HorarioEditar?.abrir(porId(editar.dataset.id));
        const eliminar = evento.target.closest('.btn-eliminar-horario');
        if (!eliminar) return;
        const confirmacion = await AlertManager.confirm('¿Eliminar horario?', 'Esta acción no se puede deshacer.', 'Sí, eliminar', 'Cancelar');
        if (!confirmacion.isConfirmed) return;
        try {
            await apiFetch(BASE_URL + 'api/horarios/eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ id_horario: eliminar.dataset.id }) });
            AlertManager.success('Horario eliminado', 'El horario fue eliminado correctamente.'); cargar(); window.HorarioStats?.cargar();
        } catch (error) { AlertManager.error('No se pudo eliminar', error.mensaje || 'Error inesperado.'); }
    });
    document.getElementById('btn-recargar-horarios')?.addEventListener('click', cargar);
    window.HorariosConsultar = { recargar: cargar, cargar };
    cargar();
});
