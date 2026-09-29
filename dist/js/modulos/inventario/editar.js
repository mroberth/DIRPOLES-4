// dist/js/modulos/inventario/editar.js
// ------------------------------------------------------------------
// Edición de insumo en un MODAL (sin página nueva).
// Reutiliza InventarioValidaciones (con idExcluir y fecha_vencimiento
// libre: un insumo ya registrado puede tener fecha pasada/vencido).
// SOLO edita nombre, tipo, presentación, fecha y descripción; la
// cantidad y el estatus cambian con entrada/salida.
//
//   window.InventarioEditar.abrir(id)   ← la llama consultar.js
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-editar');
    const modalEl = document.getElementById('modalEditar');
    if (!form || !modalEl) return;

    const modal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(modalEl) : null;

    // Opciones mutables: idExcluir cambia en cada apertura.
    const opciones = { idExcluir: 0, exigirFechaFutura: false };
    const validador = window.InventarioValidaciones.configurar(form, opciones);

    const setValor = (id, valor) => {
        const el = document.getElementById(id);
        if (el) el.value = valor == null ? '' : valor;
    };

    // 1) Catálogo de presentaciones (una sola vez)
    (async () => {
        try {
            const presentaciones = await apiFetch(BASE_URL + 'api/inventario/presentaciones');
            const sel = document.getElementById('id_presentacion');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            presentaciones.forEach((p) => {
                const opt = document.createElement('option');
                opt.value = p.id_presentacion;
                opt.textContent = p.nombre_presentacion;
                sel.appendChild(opt);
            });
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron las presentaciones:', e);
        }
    })();

    // Al mostrarse el modal, re-inicializar Select2 (ya es visible => ancho y
    // dropdown correctos).
    modalEl.addEventListener('shown.bs.modal', function () {
        if (window.initSelect2) window.initSelect2(modalEl);
    });

    // 2) API pública para abrir el modal
    window.InventarioEditar = {
        async abrir(id) {
            try {
                const i = await apiFetch(BASE_URL + 'api/inventario/obtener/' + encodeURIComponent(id));

                setValor('id_insumo', i.id_insumo);
                setValor('nombre_insumo', i.nombre_insumo);
                setValor('tipo_insumo', i.tipo_insumo);
                setValor('id_presentacion', i.id_presentacion);
                setValor('fecha_vencimiento', i.fecha_vencimiento);
                setValor('descripcion', i.descripcion);

                // Subtítulo del modal: nombre + presentación.
                const codigo = document.getElementById('inventarioCodigo');
                if (codigo) {
                    codigo.textContent = `${i.nombre_insumo || ''} · ${i.nombre_presentacion || ''}`;
                }

                // No marcar como duplicado su propio registro.
                opciones.idExcluir = Number(i.id_insumo);
                validador.limpiar();

                if (modal) modal.show();

                // Refresca Select2 con los valores recién asignados (el ancho
                // se corrige de nuevo en 'shown.bs.modal').
                if (window.initSelect2) window.initSelect2(modalEl);
            } catch (error) {
                console.error('Abrir edición:', error);
                if (error.codigo === 'NOT_FOUND') {
                    // La fila quedó obsoleta (el insumo se eliminó). Avisar y
                    // refrescar la tabla para que desaparezca.
                    AlertManager.warning('Insumo no encontrado',
                        'Puede haber sido eliminado. Actualizando la lista…');
                    if (window.InventarioConsultar) window.InventarioConsultar.recargar();
                } else {
                    AlertManager.error('Error', error.mensaje || 'No se pudo cargar el insumo.');
                }
            }
        }
    };

    // 3) Guardar cambios
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        const ok = await validador.validarTodo();
        if (!ok) {
            AlertManager.error('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = Object.fromEntries(new FormData(form).entries());
        datos.id_insumo = parseInt(datos.id_insumo, 10);
        datos.id_presentacion = parseInt(datos.id_presentacion, 10);

        try {
            await apiFetch(BASE_URL + 'api/inventario/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            if (modal) modal.hide();
            AlertManager.success('¡Actualizado!', 'Los datos del insumo se guardaron correctamente.');

            if (window.InventarioConsultar) window.InventarioConsultar.recargar();
            if (window.InventarioStats) window.InventarioStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
