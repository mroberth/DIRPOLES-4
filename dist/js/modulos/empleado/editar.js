// dist/js/modulos/empleado/editar.js
// ------------------------------------------------------------------
// Edición de empleado en un MODAL (sin página nueva).
// Reutiliza EmpleadoValidaciones (con idExcluir y claveOpcional) y el
// endpoint api/empleados/actualizar.
//
//   window.EmpleadoEditar.abrir(id)   ← la llama consultar.js
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-editar');
    const modalEl = document.getElementById('modalEditar');
    if (!form || !modalEl) return;

    const modal = (typeof bootstrap !== 'undefined') ? new bootstrap.Modal(modalEl) : null;

    // Opciones mutables: idExcluir cambia en cada apertura.
    const opciones = { idExcluir: 0, claveOpcional: true };
    const validador = window.EmpleadoValidaciones.configurar(form, opciones);

    const setValor = (id, valor) => {
        const el = document.getElementById(id);
        if (el) el.value = valor == null ? '' : valor;
    };

    // 1) Catálogo de tipos (una sola vez)
    (async () => {
        try {
            const tipos = await apiFetch(BASE_URL + 'api/empleados/tipos');
            const sel = document.getElementById('id_tipo_empleado');
            sel.innerHTML = '<option value="">Seleccione…</option>';
            tipos.forEach((t) => {
                const opt = document.createElement('option');
                opt.value = t.id_tipo_emp;
                opt.textContent = t.tipo;
                sel.appendChild(opt);
            });
            if (window.initSelect2) window.initSelect2(form);
        } catch (e) {
            console.error('No se cargaron los tipos:', e);
        }
    })();

    // Al mostrarse el modal, re-inicializar Select2 (ya es visible => ancho y
    // dropdown correctos).
    modalEl.addEventListener('shown.bs.modal', function () {
        if (window.initSelect2) window.initSelect2(modalEl);
    });

    // 2) API pública para abrir el modal
    window.EmpleadoEditar = {
        async abrir(id) {
            try {
                const e = await apiFetch(BASE_URL + 'api/empleados/obtener/' + encodeURIComponent(id));

                setValor('id_empleado', e.id_empleado);
                setValor('tipo_cedula', e.tipo_cedula);
                setValor('cedula', e.cedula);
                setValor('nombre', e.nombre);
                setValor('apellido', e.apellido);
                setValor('correo', e.correo);
                setValor('telefono', e.telefono);
                setValor('id_tipo_empleado', e.id_tipo_empleado);
                setValor('fecha_nacimiento', e.fecha_nacimiento);
                setValor('direccion', e.direccion);
                setValor('estatus', String(e.estatus));
                setValor('clave', ''); // no se cambia salvo que se escriba

                // Subtítulo del modal: cédula del empleado.
                const codigo = document.getElementById('empleadoCodigo');
                if (codigo) {
                    codigo.textContent = `${e.nombre || ''} ${e.apellido || ''} · ${e.tipo_cedula || ''}-${e.cedula || ''}`;
                }

                // No marcar como duplicado su propio correo/cédula.
                opciones.idExcluir = Number(e.id_empleado);
                validador.limpiar();

                if (modal) modal.show();

                // Refresca Select2 con los valores recién asignados (el ancho
                // se corrige de nuevo en 'shown.bs.modal').
                if (window.initSelect2) window.initSelect2(modalEl);
            } catch (error) {
                console.error('Abrir edición:', error);
                if (error.codigo === 'NOT_FOUND') {
                    // La fila quedó obsoleta (el empleado se eliminó). Avisar y
                    // refrescar la tabla para que desaparezca.
                    AlertManager.warning('Empleado no encontrado',
                        'Puede haber sido eliminado. Actualizando la lista…');
                    if (window.EmpleadoConsultar) window.EmpleadoConsultar.recargar();
                } else {
                    AlertManager.error('Error', error.mensaje || 'No se pudo cargar el empleado.');
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
        datos.id_empleado = parseInt(datos.id_empleado, 10);
        datos.id_tipo_empleado = parseInt(datos.id_tipo_empleado, 10);
        datos.estatus = parseInt(datos.estatus, 10);
        if (!datos.clave) delete datos.clave; // vacío = no cambiar

        try {
            await apiFetch(BASE_URL + 'api/empleados/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            if (modal) modal.hide();
            AlertManager.success('¡Actualizado!', 'Los datos del empleado se guardaron correctamente.');

            if (window.EmpleadoConsultar) window.EmpleadoConsultar.recargar();
            if (window.EmpleadoStats) window.EmpleadoStats.cargar();
        } catch (error) {
            if (error.codigo === 'ALREADY_EXISTS' || error.codigo === 'VALIDATION_ERROR') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });
});
