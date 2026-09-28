// dist/js/modulos/orientacion/consultar.js
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('tbodyOrientacion');
    if (!tbody) return;
    let registros = [];
    let tabla = null;
    const escapar = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (caracter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caracter]));

    function pintar() {
        tbody.innerHTML = registros.map((registro) => {
            const fecha = registro.fecha_creacion || '';
            return `<tr>
            <td data-order="${escapar(fecha)}">${escapar(Formato.fecha(fecha))}</td>
            <td>${escapar(registro.beneficiario)}<br><small class="text-muted">${escapar(registro.cedula_beneficiario)}</small></td>
            <td>${escapar(registro.empleado)}<br><small class="text-muted">${escapar(registro.cedula_empleado || '')}</small></td>
            <td>${escapar(registro.motivo_orientacion || 'No registrado')}</td>
            <td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-primary js-detalle" data-id="${registro.id_orientacion}" title="Detalle"><i class="fas fa-eye"></i></button>
            <button class="btn btn-sm btn-outline-success js-constancia" data-id="${registro.id_orientacion}" title="Constancia"><i class="fas fa-file-contract"></i></button>
            <button class="btn btn-sm btn-outline-info js-referencia" data-id="${registro.id_orientacion}" title="Referencia"><i class="fas fa-file-export"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-editar" data-id="${registro.id_orientacion}" title="Editar"><i class="fas fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger js-eliminar" data-id="${registro.id_orientacion}" title="Eliminar"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        }).join('');
    }

    function iniciarTabla() {
        if (!window.DataTableHelper) return;
        tabla = DataTableHelper.inicializar('#tablaOrientacion', {
            titulo: 'Orientaciones',
            orden: [[0, 'desc']],
            pageLength: 10,
            columnasExport: [0, 1, 2, 3],
            columnDefs: [
                { targets: 0, width: '105px' },                                    // Fecha
                { targets: 3, width: '250px' },                                    // Motivo
                { targets: 4, width: '210px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
            ],
        });
    }

    async function cargar() {
        try {
            if (tabla) { tabla.destroy(); tabla = null; }
            registros = await apiFetch(BASE_URL + 'api/orientacion/listar');
            pintar();
            iniciarTabla();
            window.OrientacionStats?.cargar();
        } catch (error) {
            AlertManager.error('No se pudo cargar', error.mensaje || 'Error inesperado.');
        }
    }

    tbody.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('button[data-id]');
        if (!boton) return;
        const registro = registros.find((item) => String(item.id_orientacion) === boton.dataset.id);
        if (!registro) return;

        if (boton.classList.contains('js-detalle')) {
            Swal.fire({
                title: 'Orientación',
                html: `<div class="text-start">
                    <p><b>Fecha:</b> ${escapar(Formato.fecha(registro.fecha_creacion || ''))}</p>
                    <p><b>Beneficiario:</b> ${escapar(registro.beneficiario)} (${escapar(registro.cedula_beneficiario)})</p>
                    <p><b>Atendió:</b> ${escapar(registro.empleado)} (${escapar(registro.cedula_empleado || 'sin cédula')})</p>
                    <p><b>Motivo:</b> ${escapar(registro.motivo_orientacion || 'No registrado')}</p>
                    <p><b>Descripción:</b> ${escapar(registro.descripcion_orientacion || 'No registrada')}</p>
                    <p><b>Indicaciones:</b> ${escapar(registro.indicaciones_orientacion || 'No registradas')}</p>
                    <p><b>Observaciones:</b> ${escapar(registro.obs_adic_orientacion || 'No registradas')}</p>
                </div>`,
                confirmButtonText: 'Cerrar',
            });
            return;
        }

        if (boton.classList.contains('js-constancia')) {
            window.DocumentosPDF?.abrir({ tipo: 'constancia', ruta: `orientacion/constancia/${registro.id_orientacion}`, registro, tramite: registro.motivo_orientacion || '' });
            return;
        }

        if (boton.classList.contains('js-referencia')) {
            window.DocumentosPDF?.abrir({ tipo: 'referencia', ruta: `orientacion/referencia/${registro.id_orientacion}`, registro, tramite: registro.motivo_orientacion || '' });
            return;
        }

        if (boton.classList.contains('js-editar')) {
            window.OrientacionEditar?.abrir(registro);
            return;
        }

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar orientación?',
            `Se eliminará la orientación de ${registro.beneficiario || 'este beneficiario'}. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (confirmacion.isConfirmed) {
            try {
                await apiFetch(BASE_URL + 'api/orientacion/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_orientacion: Number(registro.id_orientacion) }),
                });
                AlertManager.success('Orientación eliminada', 'La orientación fue eliminada.');
                cargar();
            } catch (error) {
                if (error.codigo === 'IN_USE') {
                    AlertManager.warning('No se puede eliminar', error.mensaje);
                } else if (error.codigo === 'NOT_FOUND') {
                    AlertManager.warning('Registro no encontrado', 'Puede haber sido eliminado. Actualizando la lista…');
                    cargar();
                } else {
                    AlertManager.error('No se pudo eliminar', error.mensaje || 'Error inesperado.');
                }
            }
        }
    });

    document.getElementById('btn-recargar-orientacion')?.addEventListener('click', cargar);
    window.OrientacionConsultar = { cargar };
    cargar();
});
