// dist/js/modulos/medicina/stats.js
window.MedicinaStats = (function () {
    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;
        try {
            const datos = await apiFetch(BASE_URL + 'api/medicina/stats');
            nodos.forEach((nodo) => { const clave = nodo.dataset.stat; if (Object.prototype.hasOwnProperty.call(datos, clave)) nodo.textContent = datos[clave]; });
        } catch (error) { console.error('Estadísticas de medicina:', error); }
    }
    document.addEventListener('DOMContentLoaded', cargar);
    return { cargar };
}());
