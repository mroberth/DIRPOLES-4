// dist/js/modulos/trabajo-social/pendientes.js
// ------------------------------------------------------------------
// Modal "Exoneraciones Pendientes por Estudio": carga la lista cada vez
// que se abre. La columna "Acción" abre el offcanvas del estudio
// socioeconómico vía window.iniciarEstudio() (estudio.js).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalSeleccionarExoneracion');
    const tbody = document.querySelector('#tablaExoneracionesPendientes tbody');
    if (!modal || !tbody) return;

    const COLUMNAS = 5;
    let registros = [];

    function escapear(texto) {
        return String(texto ?? '').replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[m]));
    }

    function filaVacia(mensaje, clase) {
        tbody.innerHTML =
            `<tr><td colspan="${COLUMNAS}" class="text-center ${clase} py-3">${mensaje}</td></tr>`;
    }

    modal.addEventListener('show.bs.modal', async function () {
        filaVacia('Cargando…', 'text-muted');

        try {
            // { exito:true, datos:[...] } — apiFetch devuelve `datos`.
            registros = await apiFetch(BASE_URL + 'api/trabajo-social/exoneraciones/pendientes');

            if (!Array.isArray(registros) || registros.length === 0) {
                registros = [];
                filaVacia('No hay exoneraciones pendientes de estudio.', 'text-muted');
                return;
            }

            tbody.innerHTML = registros.map((f) => `
                <tr>
                    <td>${escapear(f.fecha_creacion)}</td>
                    <td class="text-start">${escapear(f.nombres)} ${escapear(f.apellidos)}</td>
                    <td>${escapear(f.tipo_cedula)}-${escapear(f.cedula)}</td>
                    <td class="text-start">${escapear(f.motivo)}</td>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-sm btn-primary js-estudio"
                                data-id="${Number(f.id_exoneracion)}">
                            <i class="fas fa-clipboard-check me-1"></i>Realizar Estudio
                        </button>
                    </td>
                </tr>`).join('');
        } catch (error) {
            console.error('Exoneraciones pendientes:', error);
            registros = [];
            filaVacia('No se pudieron cargar las exoneraciones.', 'text-danger');
        }
    });

    // Delegación: el botón "Realizar Estudio" abre el offcanvas con los
    // datos completos de la fila (precarga del beneficiario).
    tbody.addEventListener('click', function (evento) {
        const boton = evento.target.closest('button.js-estudio');
        if (!boton) return;
        const registro = registros.find(
            (item) => String(item.id_exoneracion) === boton.dataset.id
        );
        if (!registro) return;
        if (typeof window.iniciarEstudio !== 'function') {
            AlertManager.error('No se pudo abrir', 'El formulario del estudio no está disponible.');
            return;
        }
        window.iniciarEstudio(registro);
    });
});
