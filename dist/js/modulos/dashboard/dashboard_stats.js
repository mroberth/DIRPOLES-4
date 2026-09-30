// dist/js/modulos/dashboard/dashboard_stats.js
// ------------------------------------------------------------------
// Rellena las tarjetas del panel con `api/dashboard/stats`, renderiza
// el gráfico de operaciones y el feed de notificaciones recientes.
// ------------------------------------------------------------------
(function () {
    'use strict';

    let chartInstance = null;

    function inicializarSaludo() {
        const nodoSaludo = document.getElementById('saludo-tiempo');
        if (nodoSaludo) {
            const hora = new Date().getHours();
            if (hora < 12) {
                nodoSaludo.textContent = '¡Buenos días';
            } else if (hora < 18) {
                nodoSaludo.textContent = '¡Buenas tardes';
            } else {
                nodoSaludo.textContent = '¡Buenas noches';
            }
        }

        const nodoFecha = document.getElementById('fecha-hoy-banner');
        if (nodoFecha) {
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const fechaTxt = new Date().toLocaleDateString('es-ES', opciones);
            nodoFecha.textContent = fechaTxt.charAt(0).toUpperCase() + fechaTxt.slice(1);
        }
    }

    async function cargarEstadisticas() {
        const nodos = document.querySelectorAll('[data-stat]');

        try {
            const datos = await apiFetch(window.BASE_URL + 'api/dashboard/stats');

            if (nodos.length) {
                nodos.forEach((nodo) => {
                    const clave = nodo.getAttribute('data-stat');
                    if (datos && Object.prototype.hasOwnProperty.call(datos, clave)) {
                        nodo.textContent = datos[clave];
                        nodo.classList.add('fade-in');
                    }
                });
            }

            renderizarGrafico(datos || {});
        } catch (error) {
            console.error('No se pudieron cargar las estadísticas del panel:', error);
        }
    }

    function renderizarGrafico(datos) {
        const canvas = document.getElementById('chartDashboardOperativo');
        if (!canvas || typeof Chart === 'undefined') return;

        let etiquetas = [];
        let valores = [];
        let colores = ['#4e73df', '#e74a3b', '#f6c23e', '#1cc88a', '#36b9cc', '#858796'];

        // Si es Administrador / Superusuario
        if (datos.admin_psicologia_total !== undefined) {
            etiquetas = ['Psicología', 'Medicina', 'Orientación', 'Trabajo Social', 'Discapacidad'];
            valores = [
                datos.admin_psicologia_total || 0,
                datos.admin_medicina_total || 0,
                datos.admin_orientacion_total || 0,
                datos.admin_ts_total || 0,
                datos.admin_discapacidad_total || 0
            ];
        } else {
            // Empleado por área: tomamos todas las claves numéricas devueltas
            const entradas = Object.entries(datos).filter(([k, v]) => typeof v === 'number' && k !== 'id_empleado');
            etiquetas = entradas.map(([k]) => k.replace(/_/g, ' ').toUpperCase());
            valores = entradas.map(([, v]) => v);
        }

        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = canvas.getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: colores.slice(0, etiquetas.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontSize: 11
                    }
                },
                tooltips: {
                    backgroundColor: '#ffffff',
                    bodyFontColor: '#858796',
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 10,
                    yPadding: 10,
                    displayColors: false,
                    caretPadding: 10
                }
            }
        });
    }

    async function cargarNotificacionesRecientes() {
        const contenedor = document.getElementById('feed-notificaciones-dashboard');
        const badge = document.getElementById('badge-total-notif');
        if (!contenedor) return;

        try {
            const notifs = await apiFetch(window.BASE_URL + 'api/notificaciones/listar');
            const lista = Array.isArray(notifs) ? notifs : (notifs.notificaciones || []);

            if (badge) {
                badge.textContent = `${lista.length} aviso${lista.length !== 1 ? 's' : ''}`;
            }

            if (!lista.length) {
                contenedor.innerHTML = `
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                        <span class="small font-weight-bold">¡Todo al día! No tienes notificaciones pendientes.</span>
                    </div>
                `;
                return;
            }

            const ultimas = lista.slice(0, 4);
            contenedor.innerHTML = ultimas.map(item => `
                <a href="${item.url ? (window.BASE_URL + item.url) : '#'}" class="list-group-item list-group-item-action p-3">
                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 font-weight-bold text-dark small">
                            <i class="fas fa-bell text-primary me-2"></i>${escapar(item.titulo || 'Notificación')}
                        </h6>
                        <small class="text-muted" style="font-size: 0.7rem;">${item.fecha_creacion ? item.fecha_creacion.substring(0, 10) : ''}</small>
                    </div>
                    <p class="mb-1 text-muted small text-truncate">${escapar(item.mensaje || '')}</p>
                </a>
            `).join('');
        } catch (err) {
            console.warn('No se pudieron cargar las notificaciones para el feed:', err);
            contenedor.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    No se pudo obtener la actividad reciente.
                </div>
            `;
        }
    }

    function escapar(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        inicializarSaludo();
        cargarEstadisticas();
        cargarNotificacionesRecientes();
    });
})();
