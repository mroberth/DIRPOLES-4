// dist/js/modulos/discapacidad/consultar.js
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('tbodyDiscapacidad');
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
            <td>${escapar(registro.tipo_discapacidad)}</td>
            <td>${escapar(registro.grado)}</td>
            <td>${escapar(registro.diagnostico)}</td>
            <td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-primary js-detalle" data-id="${registro.id_discapacidad}" title="Detalle"><i class="fas fa-eye"></i></button>
            <button class="btn btn-sm btn-outline-secondary js-editar" data-id="${registro.id_discapacidad}" title="Editar"><i class="fas fa-pen"></i></button>
            <button class="btn btn-sm btn-outline-danger js-eliminar" data-id="${registro.id_discapacidad}" title="Eliminar"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        }).join('');
    }

    function iniciarTabla() {
        if (!window.DataTableHelper) return;
        tabla = DataTableHelper.inicializar('#tablaDiscapacidad', {
            titulo: 'Diagnósticos de discapacidad',
            orden: [[0, 'desc']],
            pageLength: 10,
            columnasExport: [0, 1, 2, 3, 4, 5],
            columnDefs: [
                { targets: 0, width: '95px' },                                     // Fecha
                { targets: 3, width: '110px' },                                    // Tipo
                { targets: 4, width: '100px' },                                    // Grado
                { targets: 5, width: '220px' },                                    // Diagnóstico
                { targets: 6, width: '130px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
            ],
        });
    }

    async function cargar() {
        try {
            if (tabla) { tabla.destroy(); tabla = null; }
            registros = await apiFetch(BASE_URL + 'api/discapacidad/listar');
            pintar();
            iniciarTabla();
            window.DiscapacidadStats?.cargar();
        } catch (error) {
            AlertManager.error('No se pudo cargar', error.mensaje || 'Error inesperado.');
        }
    }

    tbody.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('button[data-id]');
        if (!boton) return;
        const registro = registros.find((item) => String(item.id_discapacidad) === boton.dataset.id);
        if (!registro) return;

        if (boton.classList.contains('js-detalle')) {
            Swal.fire({
                title: 'Diagnóstico de discapacidad',
                html: `<div class="text-start">
                    <p><b>Fecha:</b> ${escapar(Formato.fecha(registro.fecha_creacion || ''))}</p>
                    <p><b>Beneficiario:</b> ${escapar(registro.beneficiario)} (${escapar(registro.cedula_beneficiario)})</p>
                    <p><b>Atendió:</b> ${escapar(registro.empleado)} (${escapar(registro.cedula_empleado || 'sin cédula')})</p>
                    <p><b>Tipo:</b> ${escapar(registro.tipo_discapacidad)}${registro.disc_especifica ? ' — ' + escapar(registro.disc_especifica) : ''}</p>
                    <p><b>Diagnóstico:</b> ${escapar(registro.diagnostico)}</p>
                    <p><b>Grado:</b> ${escapar(registro.grado)}</p>
                    <p><b>Medicamentos:</b> ${escapar(registro.medicamentos || 'No registrados')}</p>
                    <p><b>Habilidades funcionales:</b> ${escapar(registro.habilidades_funcionales || 'No registradas')}</p>
                    <p><b>Requiere asistencia:</b> ${escapar(registro.requiere_asistencia || 'No especificado')}</p>
                    <p><b>Dispositivo:</b> ${escapar(registro.dispositivo_asistencia || 'No registrado')}</p>
                    <p><b>Carnet:</b> ${escapar(registro.carnet_discapacidad || 'Sin carnet')}</p>
                    <p><b>Observaciones:</b> ${escapar(registro.observaciones || 'No registradas')}</p>
                    <p><b>Recomendaciones:</b> ${escapar(registro.recomendaciones || 'No registradas')}</p>
                </div>`,
                confirmButtonText: 'Cerrar',
            });
            return;
        }

        if (boton.classList.contains('js-editar')) {
            window.DiscapacidadEditar?.abrir(registro);
            return;
        }

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar diagnóstico?',
            `Se eliminará el diagnóstico de discapacidad de ${registro.beneficiario || 'este beneficiario'} junto con su solicitud de servicio. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (confirmacion.isConfirmed) {
            try {
                await apiFetch(BASE_URL + 'api/discapacidad/eliminar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ id_discapacidad: Number(registro.id_discapacidad) }),
                });
                AlertManager.success('Diagnóstico eliminado', 'El diagnóstico de discapacidad fue eliminado.');
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

    document.getElementById('btn-recargar-discapacidad')?.addEventListener('click', cargar);
    window.DiscapacidadConsultar = { cargar };
    cargar();
});
