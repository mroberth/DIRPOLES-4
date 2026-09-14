// dist/js/modulos/dashboard/dashboard_stats.js
// ------------------------------------------------------------------
// Rellena TODAS las tarjetas del panel con una sola petición a
// `api/dashboard/stats`. El backend devuelve un mapa plano
// { clave: numero } y aquí se busca el nodo [data-stat="clave"].
// ------------------------------------------------------------------
(function () {
    'use strict';

    async function cargarEstadisticas() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;

        try {
            const datos = await apiFetch(window.BASE_URL + 'api/dashboard/stats');

            nodos.forEach((nodo) => {
                const clave = nodo.getAttribute('data-stat');
                if (datos && Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                    nodo.classList.add('fade-in');
                }
            });
        } catch (error) {
            console.error('No se pudieron cargar las estadísticas del panel:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', cargarEstadisticas);
})();
