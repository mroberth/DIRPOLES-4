// dist/js/modulos/cita/consultar.js

document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.querySelector('#tabla-citas tbody');
    if (!tabla) return;

    const estados = {
        1: ['Pendiente', 'warning'],
        2: ['Confirmada', 'info'],
        3: ['Atendida', 'success'],
        4: ['Cancelada', 'danger'],
        5: ['No asistió', 'secondary'],
    };
    let citas = [];
    let tablaDataTable = null;

    function escapar(valor) {
        return String(valor ?? '').replace(/[&<>'"]/g, (caracter) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
        }[caracter]));
    }

    function pintar() {
        tabla.innerHTML = citas.length ? citas.map((cita) => {
            const estado = estados[cita.estatus] || ['Desconocido', 'secondary'];
            return `<tr>
                <td>${escapar(cita.fecha_formateada)}</td>
                <td>${escapar(cita.hora_formateada)}</td>
                <td>${escapar(`${cita.beneficiario_nombres} ${cita.beneficiario_apellidos}`)}<br><small class="text-muted">${escapar(cita.cedula_beneficiario)}</small></td>
                <td>${escapar(cita.psicologo)}<br><small class="text-muted">${escapar(cita.cedula_psicologo || '')}</small></td>
                <td><span class="badge bg-${estado[1]}">${estado[0]}</span></td>
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm btn-outline-primary js-editar-cita" data-id="${cita.id_cita}" title="Editar"><i class="fas fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-success js-estado-cita" data-id="${cita.id_cita}" title="Cambiar estado"><i class="fas fa-check"></i></button>
                    <button class="btn btn-sm btn-outline-danger js-eliminar-cita" data-id="${cita.id_cita}" title="Eliminar"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        }).join('') : '';
    }

    function inicializarDataTable() {
        if (!window.DataTableHelper) return;

        tablaDataTable = DataTableHelper.inicializar('#tabla-citas', {
            titulo: 'Citas',
            orden: [[0, 'desc'], [1, 'desc']],
            pageLength: 10,
            responsive: true,
            columnasExport: [0, 1, 2, 3, 4],
            columnDefs: [
                { targets: 0, width: '110px' },
                { targets: 1, width: '80px' },
                { targets: 4, width: '110px', className: 'text-center' },
                { targets: 5, width: '130px', orderable: false, searchable: false, className: 'text-center text-nowrap' },
            ],
        });
    }

    async function cargar() {
        try {
            if (tablaDataTable) {
                tablaDataTable.destroy();
                tablaDataTable = null;
            }
            citas = await apiFetch(BASE_URL + 'api/citas/listar');
            pintar();
            inicializarDataTable();
        } catch (error) {
            tabla.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${escapar(error.mensaje || 'No se pudieron cargar las citas.')}</td></tr>`;
        }
    }

    async function cambiarEstado(cita) {
        const opciones = {};
        Object.entries(estados).forEach(([id, datos]) => { opciones[id] = datos[0]; });
        const resultado = await Swal.fire({
            title: 'Cambiar estado',
            input: 'select',
            inputOptions: opciones,
            inputValue: String(cita.estatus),
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: (valor) => !valor && 'Selecciona un estado.',
        });
        if (!resultado.isConfirmed) return;
        try {
            await apiFetch(BASE_URL + 'api/citas/actualizar_estado', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_cita: cita.id_cita, estatus: resultado.value }),
            });
            await cargar();
            if (window.CitaStats) window.CitaStats.cargar();
        } catch (error) {
            AlertManager.error('No se pudo cambiar el estado', error.mensaje || 'Error inesperado.');
        }
    }

    async function eliminar(cita) {
        const confirmacion = await Swal.fire({
            icon: 'warning',
            title: '¿Eliminar esta cita?',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        });
        if (!confirmacion.isConfirmed) return;
        try {
            await apiFetch(BASE_URL + 'api/citas/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_cita: cita.id_cita }),
            });
            AlertManager.success('Cita eliminada', 'La cita fue eliminada correctamente.');
            await cargar();
            if (window.CitaStats) window.CitaStats.cargar();
        } catch (error) {
            AlertManager.error('No se pudo eliminar', error.mensaje || 'Error inesperado.');
        }
    }

    tabla.addEventListener('click', (evento) => {
        const boton = evento.target.closest('button[data-id]');
        if (!boton) return;
        const cita = citas.find((item) => String(item.id_cita) === boton.dataset.id);
        if (!cita) return;
        if (boton.classList.contains('js-editar-cita')) window.CitaEditar.abrir(cita);
        if (boton.classList.contains('js-estado-cita')) cambiarEstado(cita);
        if (boton.classList.contains('js-eliminar-cita')) eliminar(cita);
    });

    document.getElementById('btn-recargar-citas')?.addEventListener('click', cargar);
    window.CitasConsultar = { cargar };
    cargar();
});
