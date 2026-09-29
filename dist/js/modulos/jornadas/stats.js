// dist/js/modulos/jornadas/stats.js
// Rellena las tarjetas de resumen del módulo Jornadas Médicas ([data-stat]).
// Es reusable: sirve para crear y consultar.
window.JornadasStats = (function () {
    'use strict';

    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;

        try {
            const datos = await apiFetch(BASE_URL + 'api/jornadas/stats');
            nodos.forEach((nodo) => {
                const clave = nodo.getAttribute('data-stat');
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                    nodo.classList.add('fade-in');
                }
            });
        } catch (error) {
            console.error('No se pudieron cargar las estadísticas de jornadas:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', cargar);

    return { cargar };
})();
