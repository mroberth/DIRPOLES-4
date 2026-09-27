// dist/js/modulos/orientacion/stats.js
window.OrientacionStats = (function () {
    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;
        try {
            const datos = await apiFetch(BASE_URL + 'api/orientacion/stats');
            nodos.forEach((nodo) => { const clave = nodo.dataset.stat; if (Object.prototype.hasOwnProperty.call(datos, clave)) nodo.textContent = datos[clave]; });
        } catch (error) { console.error('Estadísticas de orientación:', error); }
    }
    document.addEventListener('DOMContentLoaded', cargar);
    return { cargar };
}());
