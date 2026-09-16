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
    function exportOptions() { return { columns: [0, 1, 2, 3, 4], format: { body: (data) => String(data).replace(/<[^>]*>/g, '') } }; }
    function construirTabla() {
        if (!window.jQuery || !$.fn?.DataTable) return;
        tabla = $('#tablaHorarios').DataTable({
            language: { url: BASE_URL + 'plugins/DataTables/js/languaje.json' }, order: [[0, 'asc'], [2, 'asc']], pageLength: 10, autoWidth: false,
            columnDefs: [{ targets: 5, orderable: false, searchable: false, className: 'text-center text-nowrap' }],
            layout: { topStart: { buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel', className: 'btn btn-success btn-sm me-1', title: 'Horarios de Psicología', exportOptions: exportOptions() },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf me-1"></i> PDF', className: 'btn btn-danger btn-sm', title: 'Horarios de Psicología', orientation: 'landscape', pageSize: 'A4', exportOptions: exportOptions() },
            ] } },
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
