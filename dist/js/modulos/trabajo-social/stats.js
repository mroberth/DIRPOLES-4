// dist/js/modulos/trabajo-social/stats.js
// ------------------------------------------------------------------
// Tarjetas [data-stat] del módulo Trabajo Social: UNA sola llamada a
// api/trabajo-social/stats, igual que los módulos canónicos. Se carga
// desde crear.php y consultar.php (si ambas están en el DOM, cada vista
// pinta solo sus propios nodos con la misma respuesta).
// ------------------------------------------------------------------
window.TrabajoSocialStats = (function () {
    async function cargar() {
        const nodos = document.querySelectorAll('[data-stat]');
        if (!nodos.length) return;
        try {
            const datos = await apiFetch(BASE_URL + 'api/trabajo-social/stats');
            nodos.forEach((nodo) => {
                const clave = nodo.dataset.stat;
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                }
            });
        } catch (error) {
            console.error('Estadísticas de trabajo social:', error);
        }
    }
    document.addEventListener('DOMContentLoaded', cargar);
    return { cargar };
}());
