document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('tbodyMedicina');
    if (!tbody) return;
    let registros = [];
    let tabla = null;
    const escapar = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (caracter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caracter]));
    const resumen = (registro) => registro.diagnostico || '';

    function pintar() {
        tbody.innerHTML = registros.map((registro) => {
            const fecha = registro.fecha_creacion || '';
            const hora = Formato.hora((fecha.split(' ')[1]) || '');
            const insumos = registro.insumos_usados || '';
            return `<tr>
            <td data-order="${escapar(fecha)}">
                <div>${escapar(Formato.fecha(fecha))}</div>
                ${hora ? `<small class="text-muted">${escapar(hora)}</small>` : ''}
            </td>
            <td>${escapar(registro.beneficiario)}<br><small class="text-muted">${escapar(registro.cedula_beneficiario)}</small></td>
            <td>${escapar(registro.empleado)}<br><small class="text-muted">${escapar(registro.cedula_empleado || '')}</small></td>
            <td>${escapar(registro.patologia || 'No aplica')}</td>
            <td>${escapar(resumen(registro) || 'No registrado')}</td>
            <td><small>${insumos ? escapar(insumos) : '<span class="text-muted">Sin insumos</span>'}</small></td>
            <td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-primary js-detalle" data-id="${registro.id_consulta_med}" title="Detalle"><i class="fas fa-eye"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-editar" data-id="${registro.id_consulta_med}" title="Editar"><i class="fas fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger js-eliminar" data-id="${registro.id_consulta_med}" title="Eliminar"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        }).join('');
    }

    function iniciarTabla() {
        if (!window.DataTableHelper) return;
        tabla = DataTableHelper.inicializar('#tablaMedicina', {
            titulo: 'Consultas médicas',
            orden: [[0, 'desc']],
            pageLength: 10,
            columnasExport: [0, 1, 2, 3, 4, 5],
            columnDefs: [
                { targets: 0, width: '105px' },                                    // Fecha
                { targets: 3, width: '150px' },                                    // Patología
                { targets: 4, width: '200px' },                                    // Resumen
                { targets: 5, width: '170px' },                                    // Insumos
                { targets: 6, width: '130px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
            ],
        });
    }

    async function cargar() {
        try {
            if (tabla) { tabla.destroy(); tabla = null; }
            registros = await apiFetch(BASE_URL + 'api/medicina/listar');
            pintar();
            iniciarTabla();
            window.MedicinaStats?.cargar();
        } catch (error) {
            AlertManager.error('No se pudo cargar', error.mensaje || 'Error inesperado.');
        }
    }

    tbody.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('button[data-id]');
        if (!boton) return;
        const registro = registros.find((item) => String(item.id_consulta_med) === boton.dataset.id);
        if (!registro) return;

        if (boton.classList.contains('js-detalle')) {
            Swal.fire({
                title: 'Consulta médica',
                html: `<div class="text-start">
                    <p><b>Fecha:</b> ${escapar(Formato.fecha(registro.fecha_creacion || ''))}</p>
                    <p><b>Beneficiario:</b> ${escapar(registro.beneficiario)} (${escapar(registro.cedula_beneficiario)})</p>
                    <p><b>Atendió:</b> ${escapar(registro.empleado)} (${escapar(registro.cedula_empleado || 'sin cédula')})</p>
                    <p><b>Patología:</b> ${escapar(registro.patologia || 'No aplica')}</p>
                    <p><b>Estatura / Peso:</b> ${escapar(registro.estatura)} m / ${escapar(registro.peso)} kg</p>
                    <p><b>Tipo de sangre:</b> ${escapar(registro.tipo_sangre || 'No registrado')}</p>
                    <p><b>Motivo:</b> ${escapar(registro.motivo_visita || 'No registrado')}</p>
                    <p><b>Diagnóstico:</b> ${escapar(registro.diagnostico || 'No registrado')}</p>
                    <p><b>Tratamiento:</b> ${escapar(registro.tratamiento || 'No registrado')}</p>
                    <p><b>Observaciones:</b> ${escapar(registro.observaciones || 'No registradas')}</p>
                    <p><b>Insumos usados:</b> ${registro.insumos_usados ? escapar(registro.insumos_usados) : 'Ninguno'}</p>
                </div>`,
                confirmButtonText: 'Cerrar',
            });
            return;
        }

        if (boton.classList.contains('js-editar')) {
            window.MedicinaEditar?.abrir(registro);
            return;
        }

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar consulta?',
            `Se eliminará la consulta médica de ${registro.beneficiario || 'este beneficiario'}. Los insumos ya usados NO se devuelven al inventario. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (confirmacion.isConfirmed) {
            try {
                await apiFetch(BASE_URL + 'api/medicina/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_consulta_med: Number(registro.id_consulta_med) }),
                });
                AlertManager.success('Consulta eliminada', 'La consulta fue eliminada; el inventario queda intacto.');
                cargar();
                if (window.MedicinaStats) window.MedicinaStats.cargar();
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

    document.getElementById('btn-recargar-medicina')?.addEventListener('click', cargar);
    window.MedicinaConsultar = { cargar };
    cargar();
});
