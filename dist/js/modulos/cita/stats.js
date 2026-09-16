// dist/js/modulos/cita/stats.js
window.CitaStats = {
    async cargar() {
        try {
            const datos = await apiFetch(BASE_URL + 'api/citas/stats');
            document.querySelectorAll('[data-stat]').forEach((elemento) => {
                const clave = elemento.dataset.stat;
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    elemento.textContent = datos[clave];
                }
            });
        } catch (error) {
            console.error('No se cargaron las estadísticas de citas:', error);
        }
    },
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-stat]')) window.CitaStats.cargar();
});
