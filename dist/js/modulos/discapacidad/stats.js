// dist/js/modulos/discapacidad/stats.js
window.DiscapacidadStats = (function () {
    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;
        try {
            const datos = await apiFetch(BASE_URL + 'api/discapacidad/stats');
            nodos.forEach((nodo) => { const clave = nodo.dataset.stat; if (Object.prototype.hasOwnProperty.call(datos, clave)) nodo.textContent = datos[clave]; });
        } catch (error) { console.error('Estadísticas de discapacidad:', error); }
    }
    document.addEventListener('DOMContentLoaded', cargar);
    return { cargar };
}());
