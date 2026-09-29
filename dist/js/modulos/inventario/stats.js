// dist/js/modulos/inventario/stats.js
// Rellena las tarjetas de resumen del módulo Inventario Médico ([data-stat]).
// Es reusable: sirve para crear, consultar y editar.
window.InventarioStats = (function () {
    'use strict';

    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;

        try {
            const datos = await apiFetch(BASE_URL + 'api/inventario/stats');
            nodos.forEach((nodo) => {
                const clave = nodo.getAttribute('data-stat');
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                    nodo.classList.add('fade-in');
                }
            });
        } catch (error) {
            console.error('No se pudieron cargar las estadísticas del inventario:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', cargar);

    return { cargar };
})();
