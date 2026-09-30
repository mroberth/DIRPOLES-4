/**
 * dist/js/modulos/transporte/stats.js
 * ---------------------------------------------------------------
 * Carga de tarjetas de estadísticas para Transporte.
 */
window.TransporteStats = (function () {
    'use strict';

    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (nodos.length === 0) return;

        try {
            const data = await apiFetch(BASE_URL + 'api/transporte/stats');
            if (!data) return;

            nodos.forEach(nodo => {
                const clave = nodo.getAttribute('data-stat');
                if (data[clave] !== undefined) {
                    nodo.textContent = data[clave];
                    nodo.classList.add('fade-in');
                }
            });
        } catch (error) {
            console.error('Error al cargar stats de Transporte:', error);
        }
    }

    document.addEventListener('DOMContentLoaded', cargar);

    return { cargar };
})();
