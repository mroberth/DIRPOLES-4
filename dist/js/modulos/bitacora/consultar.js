// dist/js/modulos/bitacora/consultar.js
// ------------------------------------------------------------------
// Bitácora: filtros + tabla DataTable con exportación (Excel/PDF).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tablaEl = document.getElementById('tabla_bitacora');
    if (!tablaEl) return;

    const tbody = tablaEl.querySelector('tbody');
    let tabla = null;
    let numeroSolicitud = 0;

    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.BitacoraTour) {
        btnAyuda.addEventListener('click', () => window.BitacoraTour.iniciar());
    }

    const escapar = (txt) => {
        const d = document.createElement('div');
        d.textContent = txt == null ? '' : String(txt);
        return d.innerHTML;
    };

    const COLORES = {
        'Registro': 'bg-success',
        'Lectura': 'bg-info',
        'Actualización': 'bg-primary',
        'Eliminación': 'bg-danger',
        'Inicio de sesión': 'bg-secondary',
        'Cierre de sesión': 'bg-dark',
    };

    const accionBadge = (accion) => {
        const color = COLORES[accion] || 'bg-secondary';
        return `<span class="badge ${color}">${escapar(accion)}</span>`;
    };

    function leerFiltros() {
        const params = new URLSearchParams();
        const val = (id) => (document.getElementById(id)?.value || '').trim();
        if (val('f-modulo')) params.set('modulo', val('f-modulo'));
        if (val('f-accion')) params.set('accion', val('f-accion'));
        if (val('f-empleado')) params.set('id_empleado', val('f-empleado'));
        if (val('f-buscar')) params.set('buscar', val('f-buscar'));
        if (val('f-desde')) params.set('desde', val('f-desde'));
        if (val('f-hasta')) params.set('hasta', val('f-hasta'));
        return params.toString();
    }

    async function cargar() {
        const solicitudActual = ++numeroSolicitud;
        try {
            const qs = leerFiltros();
            const filas = await apiFetch(BASE_URL + 'api/bitacora/listar' + (qs ? '?' + qs : ''));

            if (solicitudActual !== numeroSolicitud) return;

            if (tabla) {
                tabla.destroy();
                tabla = null;
            }

            tbody.innerHTML = filas.map((r) => `
                <tr>
                    <td>${escapar(r.modulo)}</td>
                    <td>${escapar(r.empleado)}<br><small class="text-muted">${escapar(r.cedula_empleado || '')}</small></td>
                    <td>${accionBadge(r.accion)}</td>
                    <td>${escapar(r.descripcion)}</td>
                    <td data-order="${escapar(r.fecha)}">${escapar(Formato.fechaHora(r.fecha))}</td>
                </tr>`).join('');

            tabla = DataTableHelper.inicializar('#tabla_bitacora', {
                titulo: 'Bitacora',
                orden: [[4, 'desc']],
                pageLength: 20,
                lengthMenu: [[10, 20, 50, -1], [10, 20, 50, 'Todos']],
                columnasExport: [0, 1, 2, 3, 4],
            });
        } catch (error) {
            console.error('Bitácora:', error);
            if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    }

    async function cargarFiltros() {
        try {
            const f = await apiFetch(BASE_URL + 'api/bitacora/filtros');

            const modulo = document.getElementById('f-modulo');
            (f.modulos || []).forEach((m) => {
                const o = document.createElement('option'); o.value = m; o.textContent = m; modulo.appendChild(o);
            });

            const accion = document.getElementById('f-accion');
            (f.acciones || []).forEach((a) => {
                const o = document.createElement('option'); o.value = a; o.textContent = a; accion.appendChild(o);
            });

            const empleado = document.getElementById('f-empleado');
            (f.empleados || []).forEach((e) => {
                const o = document.createElement('option'); o.value = e.id_empleado; o.textContent = e.empleado; empleado.appendChild(o);
            });

            if (window.initSelect2) window.initSelect2(document);
        } catch (e) {
            console.error('Filtros de bitácora:', e);
        }
    }

    // Eventos
    document.getElementById('btn-filtrar')?.addEventListener('click', cargar);
    document.getElementById('btn-limpiar')?.addEventListener('click', function () {
        ['f-modulo', 'f-accion', 'f-empleado', 'f-buscar', 'f-desde', 'f-hasta'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) {
                el.value = '';
                if (el.tagName === 'SELECT') el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
        cargar();
    });
    document.getElementById('f-buscar')?.addEventListener('keydown', (ev) => {
        if (ev.key === 'Enter') cargar();
    });

    // Init
    (async () => {
        await cargarFiltros();
        await cargar();
    })();
});
