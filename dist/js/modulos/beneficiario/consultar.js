// dist/js/modulos/beneficiario/consultar.js
// Tabla de beneficiarios: DataTables + exportar (Excel/PDF) + detalle,
// editar (modal) y eliminar.
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyBeneficiarios');
    if (!tbody) return;

    let beneficiarios = [];
    let tabla = null;

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    const generoTexto = (g) => (g === 'M' ? 'Masculino' : (g === 'F' ? 'Femenino' : '—'));

    function fila(b) {
        const activo = parseInt(b.estatus, 10) === 1;
        const badge = activo
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-danger">Inactivo</span>';

        return `
            <tr>
                <td>${escapar(b.tipo_cedula)}-${escapar(b.cedula)}</td>
                <td>${escapar(b.nombres)} ${escapar(b.apellidos || '')}</td>
                <td>${escapar(b.correo)}</td>
                <td>${escapar(b.telefono || '—')}</td>
                <td>${generoTexto(b.genero)}</td>
                <td>${escapar(b.nombre_pnf || '—')}</td>
                <td>${badge}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-detalle"
                            data-id="${escapar(b.id_beneficiario)}" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-editar"
                            data-id="${escapar(b.id_beneficiario)}" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-id="${escapar(b.id_beneficiario)}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
    }

    async function cargar() {
        try {
            beneficiarios = await apiFetch(BASE_URL + 'api/beneficiarios/listar');

            // Destruir SIEMPRE antes de repintar el tbody: destroy() restaura el
            // DOM capturado al inicializar la tabla y borraría las filas nuevas.
            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = beneficiarios.map(fila).join('');

            tabla = DataTableHelper.inicializar('#tablaBeneficiarios', {
                titulo: 'Beneficiarios',
                orden: [[1, 'asc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4, 5, 6],
                columnDefs: [
                    { targets: 0, width: '110px' },                                 // Cédula
                    { targets: 3, width: '120px' },                                 // Teléfono
                    { targets: 4, width: '110px', className: 'text-center' },        // Género
                    { targets: 6, width: '90px', className: 'text-center' },         // Estatus
                    { targets: 7, width: '130px', orderable: false, className: 'text-center text-nowrap' }, // Acciones
                ],
            });
        } catch (error) {
            console.error('Beneficiarios:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    const porId = (id) => beneficiarios.find((x) => String(x.id_beneficiario) === String(id));

    function verDetalle(id) {
        const b = porId(id);
        if (!b) return;

        const activo = parseInt(b.estatus, 10) === 1;
        Swal.fire({
            title: `${b.nombres} ${b.apellidos || ''}`,
            html: `
                <div class="text-start small">
                    <p class="mb-1"><strong>Cédula:</strong> ${escapar(b.tipo_cedula)}-${escapar(b.cedula)}</p>
                    <p class="mb-1"><strong>Correo:</strong> ${escapar(b.correo)}</p>
                    <p class="mb-1"><strong>Teléfono:</strong> ${escapar(b.telefono || '—')}</p>
                    <p class="mb-1"><strong>Género:</strong> ${generoTexto(b.genero)}</p>
                    <p class="mb-1"><strong>PNF:</strong> ${escapar(b.nombre_pnf || '—')}</p>
                    <p class="mb-1"><strong>Sección:</strong> ${escapar(b.seccion || '—')}</p>
                    <p class="mb-1"><strong>Estatus:</strong> ${activo ? 'Activo' : 'Inactivo'}</p>
                    <p class="mb-0"><strong>Registrado:</strong> ${escapar(b.fecha_creacion)}</p>
                </div>`,
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
        });
    }

    async function eliminar(id) {
        const b = porId(id);
        const nombre = b ? `${b.nombres} ${b.apellidos || ''}` : 'este beneficiario';

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar beneficiario?',
            `Se eliminará a ${nombre}. Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/beneficiarios/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_beneficiario: Number(id) }),
            });

            AlertManager.success('Eliminado', 'El beneficiario fue eliminado.');
            cargar();
            if (window.BeneficiarioStats) window.BeneficiarioStats.cargar();
        } catch (error) {
            if (error.codigo === 'IN_USE') {
                AlertManager.warning('No se puede eliminar', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Beneficiario no encontrado', 'Puede haber sido eliminado. Actualizando la lista…');
                cargar();
                if (window.BeneficiarioStats) window.BeneficiarioStats.cargar();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    const btn = document.getElementById('btn-recargar');
    if (btn) btn.addEventListener('click', cargar);

    document.addEventListener('click', function (ev) {
        const detalle = ev.target.closest('.btn-detalle');
        if (detalle) return verDetalle(detalle.getAttribute('data-id'));

        const editar = ev.target.closest('.btn-editar');
        if (editar) {
            if (window.BeneficiarioEditar) {
                window.BeneficiarioEditar.abrir(editar.getAttribute('data-id'));
            }
            return;
        }

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar) return eliminar(borrar.getAttribute('data-id'));
    });

    window.BeneficiarioConsultar = { recargar: cargar };

    cargar();
});
