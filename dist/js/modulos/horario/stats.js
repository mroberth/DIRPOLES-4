// dist/js/modulos/horario/stats.js
window.HorarioStats = {
    async cargar() {
        try {
            const datos = await apiFetch(BASE_URL + 'api/horarios/stats');
            document.querySelectorAll('[data-stat]').forEach((elemento) => {
                if (Object.prototype.hasOwnProperty.call(datos, elemento.dataset.stat)) elemento.textContent = datos[elemento.dataset.stat];
            });
        } catch (error) {
            console.error('No se cargaron las estadísticas de horarios:', error);
        }
    },
};
document.addEventListener('DOMContentLoaded', () => { if (document.querySelector('[data-stat]')) HorarioStats.cargar(); });
