// dist/js/modulos/jornadas/consultar.js
// ------------------------------------------------------------------
// Tabla de jornadas: DataTables + exportar (Excel/PDF) + detalle,
// edición (modal de editar.js) y eliminación.
// Consume api/jornadas/* con apiFetch (contrato Respuesta).
//
// Reglas visibles (las aplica el backend igualmente):
//  - Eliminar solo si la jornada NO tiene asistentes (si no, cancelarla);
//  - la edición no deja bajar el aforo por debajo de los registrados;
//  - los botones de escritura se ocultan sin el permiso correspondiente.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbodyJornadas');
    if (!tbody) return;

    const puedeEditar   = window.JORNADAS_PUEDE_EDITAR   === true;
    const puedeEliminar = window.JORNADAS_PUEDE_ELIMINAR === true;

    let jornadas = [];
    let tabla = null;

    const escapar = (txt) => {
        const div = document.createElement('div');
        div.textContent = txt == null ? '' : String(txt);
        return div.innerHTML;
    };

    function badgeEstatus(estatus) {
        if (estatus === 'Activa')     return '<span class="badge bg-success">Activa</span>';
        if (estatus === 'Cancelada')  return '<span class="badge bg-danger">Cancelada</span>';
        return '<span class="badge bg-secondary">Finalizada</span>';
    }

    function aforoCelda(j) {
        const personas = Number(j.personas) || 0;
        const aforo = Number(j.aforo_maximo) || 0;
        const pct = aforo > 0 ? Math.min(100, Math.round((personas / aforo) * 100)) : 0;
        return `
            <div class="small text-center">
                <strong>${personas}</strong> / ${aforo}
                <div class="progress mt-1" style="height: 8px;">
                    <div class="progress-bar ${pct >= 100 ? 'bg-danger' : 'bg-success'}"
                         style="width: ${pct}%" role="progressbar"></div>
                </div>
            </div>`;
    }

    function fila(j) {
        const btnEditar = puedeEditar
            ? `<button type="button" class="btn btn-sm btn-outline-primary btn-editar"
                       data-id="${escapar(j.id_jornada)}" title="Editar jornada">
                   <i class="fas fa-pen"></i>
               </button>`
            : '';

        const btnEliminar = puedeEliminar
            ? `<button type="button" class="btn btn-sm btn-outline-dark btn-eliminar"
                       data-id="${escapar(j.id_jornada)}" title="Eliminar jornada">
                   <i class="fas fa-trash"></i>
               </button>`
            : '';

        return `
            <tr>
                <td>${escapar(j.nombre_jornada)}<br><small class="text-muted">${escapar(j.tipo_jornada)}</small></td>
                <td data-order="${escapar(j.fecha_inicio)}">
                    ${escapar(Formato.fechaHora(j.fecha_inicio))}<br>
                    <small class="text-muted">al ${escapar(Formato.fechaHora(j.fecha_fin))}</small>
                </td>
                <td>${escapar(j.ubicacion)}</td>
                <td class="text-center">${aforoCelda(j)}</td>
                <td class="text-center">${badgeEstatus(j.estatus)}</td>
                <td class="text-center text-nowrap">
                    <a href="${BASE_URL}jornadas/detalle/${escapar(j.id_jornada)}"
                       class="btn btn-sm btn-outline-success" title="Ver detalle y asistentes">
                        <i class="fas fa-eye"></i>
                    </a>
                    ${btnEditar}
                    ${btnEliminar}
                </td>
            </tr>`;
    }

    async function cargar() {
        try {
            jornadas = await apiFetch(BASE_URL + 'api/jornadas/listar');

            // Destruir SIEMPRE antes de repintar el tbody.
            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = jornadas.map(fila).join('');

            tabla = DataTableHelper.inicializar('#tablaJornadas', {
                titulo: 'Jornadas médicas',
                orden: [[1, 'desc']],
                pageLength: 10,
                columnasExport: [0, 1, 2, 3, 4],
                columnDefs: [
                    { targets: 1, width: '170px' },
                    { targets: 3, width: '130px' },
                    { targets: 4, width: '110px', className: 'text-center' },
                    { targets: 5, width: '150px', orderable: false, className: 'text-center text-nowrap' },
                ],
            });
        } catch (error) {
            console.error('Jornadas:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    function porId(id) {
        return jornadas.find((x) => String(x.id_jornada) === String(id));
    }

    function recargarTodo() {
        cargar();
        if (window.JornadasStats) window.JornadasStats.cargar();
    }

    async function eliminar(id) {
        const j = porId(id);
        if (!j) return;

        const personas = Number(j.personas) || 0;
        if (personas > 0) {
            AlertManager.warning(
                'No se puede eliminar',
                `La jornada tiene ${personas} personas registradas: cámbiala a "Cancelada" desde Editar.`
            );
            return;
        }

        const confirmacion = await AlertManager.confirm(
            '¿Eliminar jornada?',
            `Se eliminará la jornada "${j.nombre_jornada}" (sin asistentes registrados). Esta acción no se puede deshacer.`,
            'Sí, eliminar',
            'Cancelar'
        );
        if (!confirmacion.isConfirmed) return;

        try {
            await apiFetch(BASE_URL + 'api/jornadas/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_jornada: Number(id) }),
            });
            AlertManager.success('Jornada eliminada', 'La jornada fue eliminada.');
            recargarTodo();
        } catch (error) {
            if (error.codigo === 'IN_USE') {
                AlertManager.warning('Jornada en uso', error.mensaje);
                recargarTodo();
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else if (error.codigo === 'NOT_FOUND') {
                AlertManager.warning('No encontrada', 'La jornada ya no existe. Actualizando…');
                recargarTodo();
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    // ---------- Recargar ----------
    const btn = document.getElementById('btn-recargar');
    if (btn) btn.addEventListener('click', recargarTodo);

    // ---------- Acciones (delegado: funciona aunque la tabla se redibuje) ----------
    document.addEventListener('click', function (ev) {
        const editar = ev.target.closest('.btn-editar');
        if (editar && !editar.disabled) {
            if (window.JornadasEditar) window.JornadasEditar.abrir(editar.getAttribute('data-id'));
            return;
        }

        const borrar = ev.target.closest('.btn-eliminar');
        if (borrar && !borrar.disabled) return eliminar(borrar.getAttribute('data-id'));
    });

    // Expuesto para refrescar la tabla desde editar.js.
    window.JornadasConsultar = { recargar: cargar };

    cargar();
});
