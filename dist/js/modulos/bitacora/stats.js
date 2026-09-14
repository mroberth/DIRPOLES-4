// dist/js/modulos/bitacora/stats.js
window.BitacoraStats = (function () {
    'use strict';

    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;

        try {
            const datos = await apiFetch(BASE_URL + 'api/bitacora/stats');
            nodos.forEach((nodo) => {
                const clave = nodo.getAttribute('data-stat');
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                    nodo.classList.add('fade-in');
                }
            });
        } catch (error) {
            console.error('No se pudieron cargar las estadísticas de la bitácora:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', cargar);

    return { cargar };
})();
