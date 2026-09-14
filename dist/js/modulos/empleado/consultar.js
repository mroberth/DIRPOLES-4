// dist/js/modulos/empleado/consultar.js
// ------------------------------------------------------------------
// Tabla de empleados: DataTables + exportar (Excel/PDF) + detalle,
// editar (modal) y eliminar.
// Consume api/empleados/* con apiFetch (contrato Respuesta).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyEmpleados');
    if (!tbody) return;

    let empleados = [];   // datos cargados (para el detalle)
    let tabla = null;     // instancia DataTable

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    function fila(e) {
        const activo = parseInt(e.estatus, 10) === 1;
        const badge = activo
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';

        return `
            <tr>
                <td>${escapar(e.tipo_cedula)}-${escapar(e.cedula)}</td>
                <td>${escapar(e.nombre)} ${escapar(e.apellido || '')}</td>
                <td>${escapar(e.correo)}</td>
                <td>${escapar(e.telefono || '—')}</td>
                <td>${escapar(e.nombre_tipo)}</td>
                <td>${badge}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-id="${escapar(e.id_empleado)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-id="${escapar(e.id_empleado)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-id="${escapar(e.id_empleado)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    function opcionesExport() {
        // Columnas 0..5 (sin la de Acciones).
        return {
            columns: [0, 1, 2, 3, 4, 5],
            format: {
                body: (data) => String(data).replace(/<[^>]*>/g, ''),
            },
        };
    }

    async function cargar() {
        try {
            empleados = await apiFetch(BASE_URL + 'api/empleados/listar');
            tbody.innerHTML = empleados.map(fila).join('');

            if (window.jQuery && $.fn && $.fn.DataTable) {
                if (tabla) {
                    tabla.destroy();
                }
                tabla = $('#tablaEmpleados').DataTable({
                    language: { url: BASE_URL + 'plugins/DataTables/js/languaje.json' },
                    order: [[1, 'asc']],
                    pageLength: 10,
                    autoWidth: false,
                    columnDefs: [
                        { targets: 0, width: '150px' },                                   // Cédula
                        { targets: 3, width: '100px' },                                   // Teléfono
                        { targets: 4, width: '160px' },                                   // Tipo
                        { targets: 5, width: '90px', className: 'text-center' },          // Estatus
                        { targets: 6, width: '130px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
                    ],
                    layout: {
                        topStart: {
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                                    className: 'btn btn-success btn-sm me-1',
                                    title: 'Empleados',
                                    exportOptions: opcionesExport(),
                                },
                                {
                                    extend: 'pdfHtml5',
                                    text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                                    className: 'btn btn-danger btn-sm',
                                    title: 'Empleados',
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    exportOptions: opcionesExport(),
                                },
                            ],
                        },
                    },
                });
            }
        } catch (error) {
            console.error('Empleados:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function porId(id) {
        return empleados.find((x) => String(x.id_empleado) === String(id));
    }

    function verDetalle(id) {
        const e = porId(id);
        if (!e) return;

        const activo = parseInt(e.estatus, 10) === 1;
        Swal.fire({
            title: `${e.nombre} ${e.apellido || ''}`,
            html: `
                <div class="text-start small">
                    <p class="mb-1"><strong>Cédula:</strong> ${escapar(e.tipo_cedula)}-${escapar(e.cedula)}</p>
                    <p class="mb-1"><strong>Correo:</strong> ${escapar(e.correo)}</p>
                    <p class="mb-1"><strong>Teléfono:</strong> ${escapar(e.telefono || '—')}</p>
                    <p class="mb-1"><strong>Tipo:</strong> ${escapar(e.nombre_tipo)}</p>
                    <p class="mb-1"><strong>Estatus:</strong> ${activo ? 'Activo' : 'Inactivo'}</p>
                    <p class="mb-0"><strong>Registrado:</strong> ${escapar(e.fecha_creacion)}</p>
                </div>`,
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
        });
    }

    async function eliminar(id) {
        const e = porId(id);
        const nombre = e ? `${e.nombre} ${e.apellido || ''}` : 'este empleado';

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar empleado?',
            `Se eliminará a ${nombre}. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/empleados/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_empleado: Number(id) }),
            });

            AlertManager.success('Eliminado', 'El empleado fue eliminado.');
            cargar();
            if (window.EmpleadoStats) window.EmpleadoStats.cargar();
        } catch (error) {
            if (error.codigo === 'IN_USE') {
                AlertManager.warning('No se puede eliminar', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                // Fila obsoleta: refrescar para que desaparezca.
                AlertManager.warning('Empleado no encontrado', 'Puede haber sido eliminado. Actualizando la lista…');
                cargar();
                if (window.EmpleadoStats) window.EmpleadoStats.cargar();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // Recargar
    const btn = document.getElementById('btn-recargar');
    if (btn) {
        btn.addEventListener('click', cargar);
    }

    // Acciones (delegado: funciona aunque la tabla se redibuje)
    document.addEventListener('click', function (ev) {
        const detalle = ev.target.closest('.btn-detalle');
        if (detalle) return verDetalle(detalle.getAttribute('data-id'));

        const editar = ev.target.closest('.btn-editar');
        if (editar) {
            if (window.EmpleadoEditar) {
                window.EmpleadoEditar.abrir(editar.getAttribute('data-id'));
            }
            return;
        }

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar) return eliminar(borrar.getAttribute('data-id'));
    });

    // Expuesto para que editar.js refresque la tabla tras guardar.
    window.EmpleadoConsultar = { recargar: cargar };

    cargar();
});
