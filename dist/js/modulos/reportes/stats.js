/**
 * dist/js/modulos/reportes/stats.js
 * ---------------------------------------------------------------
 * Tarjetas de resumen [data-stat] de cada reporte.
 * UNA sola llamada a api/reportes/stats?reporte=<clave>, con los mismos
 * filtros que el formulario, al cargar y después de cada generación.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const tipo = window.REPORTES_TIPO;
    if (!tipo || typeof window.ReportesComunes === 'undefined') return;

    async function pintar() {
        try {
            const params = ReportesComunes.parametros({ reporte: tipo });
            const datos = await apiFetch(BASE_URL + 'api/reportes/stats?' + params.toString());
            document.querySelectorAll('[data-stat]').forEach(nodo => {
                const clave = nodo.getAttribute('data-stat');
                if (Object.prototype.hasOwnProperty.call(datos, clave)) {
                    nodo.textContent = datos[clave];
                }
            });
        } catch (err) {
            console.error('Error al cargar las estadísticas del reporte:', err);
        }
    }

    // pintar(); // Se ejecutará al presionar 'Generar Reporte' o 'Limpiar'

    const form = document.getElementById('form-reporte');
    if (form) {
        form.addEventListener('submit', () => setTimeout(pintar, 0));
    }
    const btnLimpiar = document.getElementById('btn-limpiar');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => setTimeout(pintar, 0));
    }
});
