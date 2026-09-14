// dist/js/modulos/bitacora/consultar.js
// ------------------------------------------------------------------
// Bitácora: filtros + tabla DataTable con exportación (Excel/PDF).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const tablaEl = document.getElementById('tabla_bitacora');
    if (!tablaEl) return;

    const tbody = tablaEl.querySelector('tbody');
    let tabla = null;

    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.BitacoraTour) {
        btnAyuda.addEventListener('click', () => window.BitacoraTour.iniciar());
    }

    const escapar = (txt) => {
        const d = document.createElement('div');
        d.textContent = txt == null ? '' : String(txt);
        return d.innerHTML;
    };

    const fechaBonita = (f) => {
        if (!f) return '';
        const d = new Date(String(f).replace(' ', 'T'));
        if (isNaN(d.getTime())) return f;
        const p = (n) => String(n).padStart(2, '0');
        return `${p(d.getDate())}/${p(d.getMonth() + 1)}/${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
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

    function opcionesExport() {
        return {
            columns: [0, 1, 2, 3, 4],
            format: { body: (data) => String(data).replace(/<[^>]*>/g, '') },
        };
    }

    async function cargar() {
        try {
            const qs = leerFiltros();
            const filas = await apiFetch(BASE_URL + 'api/bitacora/listar' + (qs ? '?' + qs : ''));

            tbody.innerHTML = filas.map((r) => `
                <tr>
                    <td>${escapar(r.modulo)}</td>
                    <td>${escapar(r.empleado)}</td>
                    <td>${accionBadge(r.accion)}</td>
                    <td>${escapar(r.descripcion)}</td>
                    <td data-order="${escapar(r.fecha)}">${fechaBonita(r.fecha)}</td>
                </tr>`).join('');

            if (window.jQuery && $.fn && $.fn.DataTable) {
                if (tabla) tabla.destroy();
                tabla = $('#tabla_bitacora').DataTable({
                    language: { url: BASE_URL + 'plugins/DataTables/js/languaje.json' },
                    order: [[4, 'desc']],
                    pageLength: 20,
                    lengthMenu: [[10, 20, 50, -1], [10, 20, 50, 'Todos']],
                    autoWidth: false,
                    layout: {
                        topStart: {
                            buttons: [
                                {
                                    extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel',
                                    className: 'btn btn-success btn-sm me-1', title: 'Bitacora', exportOptions: opcionesExport(),
                                },
                                {
                                    extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                                    className: 'btn btn-danger btn-sm', title: 'Bitacora',
                                    orientation: 'landscape', pageSize: 'A4', exportOptions: opcionesExport(),
                                },
                            ],
                        },
                    },
                });
            }
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
            if (el) el.value = '';
        });
        if (window.initSelect2) window.initSelect2(document);
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
