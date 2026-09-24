document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('tbodyPsicologia');
    if (!tbody) return;
    let registros = [];
    let tabla = null;
    const escapar = (valor) => String(valor ?? '').replace(/[&<>'"]/g, (caracter) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[caracter]));
    const resumen = (registro) => registro.tipo_consulta === 'Diagnóstico' ? registro.diagnostico : (registro.tipo_consulta === 'Retiro temporal' ? registro.motivo_retiro : registro.motivo_cambio);
    function pintar() {
        tbody.innerHTML = registros.map((registro) => {
            const fecha = registro.fecha_creacion || '';
            const hora = Formato.hora((fecha.split(' ')[1]) || '');

            return `<tr>
            <td data-order="${escapar(fecha)}">
                <div>${escapar(Formato.fecha(fecha))}</div>
                <small class="text-muted">${escapar(hora)}</small>
            </td>
            <td>${escapar(registro.beneficiario)}<br><small class="text-muted">${escapar(registro.cedula_beneficiario)}</small></td>
            <td>${escapar(registro.empleado)}<br><small class="text-muted">${escapar(registro.cedula_empleado || '')}</small></td><td>${escapar(registro.tipo_consulta)}</td>
            <td>${escapar(registro.patologia || 'No aplica')}</td><td>${escapar(resumen(registro) || 'No registrado')}</td>
            <td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-primary js-detalle" data-id="${registro.id_psicologia}" title="Detalle"><i class="fas fa-eye"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-editar" data-id="${registro.id_psicologia}" title="Editar"><i class="fas fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger js-eliminar" data-id="${registro.id_psicologia}" title="Eliminar"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        }).join('');
    }
    function iniciarTabla() {
        if (!window.DataTableHelper) return;
        tabla = DataTableHelper.inicializar('#tablaPsicologia', {
            titulo: 'Consultas psicológicas',
            orden: [[0, 'desc']],
            pageLength: 10,
            columnasExport: [0, 1, 2, 3, 4, 5],
            columnDefs: [
                { targets: 0, width: '115px' },                                    // Fecha
                { targets: 3, width: '140px' },                                    // Tipo
                { targets: 4, width: '150px' },                                    // Patología
                { targets: 5, width: '200px' },                                    // Resumen
                { targets: 6, width: '130px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
            ],
        });
    }
    async function cargar() { try { if (tabla) { tabla.destroy(); tabla = null; } registros = await apiFetch(BASE_URL + 'api/psicologia/listar'); pintar(); iniciarTabla(); window.PsicologiaStats?.cargar(); } catch (error) { AlertManager.error('No se pudo cargar', error.mensaje || 'Error inesperado.'); } }
    tbody.addEventListener('click', async (evento) => { const boton = evento.target.closest('button[data-id]'); if (!boton) return; const registro = registros.find((item) => String(item.id_psicologia) === boton.dataset.id); if (!registro) return; if (boton.classList.contains('js-detalle')) { Swal.fire({ title: registro.tipo_consulta, html: `<div class="text-start"><p><b>Beneficiario:</b> ${escapar(registro.beneficiario)}</p><p><b>Patología:</b> ${escapar(registro.patologia || 'No aplica')}</p><p><b>Detalle:</b> ${escapar(resumen(registro) || 'No registrado')}</p><p><b>Tratamiento:</b> ${escapar(registro.tratamiento_gen || 'No aplica')}</p><p><b>Observaciones:</b> ${escapar(registro.observaciones || 'No registradas')}</p></div>`, confirmButtonText: 'Cerrar' }); return; } if (boton.classList.contains('js-editar')) { window.PsicologiaEditar?.abrir(registro); return; } const confirmacion = await AlertManager.confirm('¿Eliminar consulta?', `Se eliminará la consulta de ${registro.beneficiario || 'este beneficiario'} (${registro.tipo_consulta}). Esta acción no se puede deshacer.`, 'Sí, eliminar', 'Cancelar'); if (confirmacion.isConfirmed) { try { await apiFetch(BASE_URL + 'api/psicologia/eliminar', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ id_psicologia: Number(registro.id_psicologia) }) }); AlertManager.success('Consulta eliminada', 'La consulta fue eliminada.'); cargar(); if (window.PsicologiaStats) window.PsicologiaStats.cargar(); } catch (error) { if (error.codigo === 'IN_USE') { AlertManager.warning('No se puede eliminar', error.mensaje); } else if (error.codigo === 'NOT_FOUND') { AlertManager.warning('Registro no encontrado', 'Puede haber sido eliminado. Actualizando la lista…'); cargar(); } else { AlertManager.error('No se pudo eliminar', error.mensaje || 'Error inesperado.'); } } } });
    document.getElementById('btn-recargar-psicologia')?.addEventListener('click', cargar);
    window.PsicologiaConsultar = { cargar };
    cargar();
});