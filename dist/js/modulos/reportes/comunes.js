/**
 * dist/js/modulos/reportes/comunes.js
 * ---------------------------------------------------------------
 * Utilidades compartidas por los 10 reportes estadísticos:
 *  - parametros(): filtros del #form-reporte listos para la URL
 *    (el filtrado se hace EN SERVIDOR, nunca en el navegador).
 *  - cargarCatalogos(): llena los selects desde api/reportes/catalogos.
 *  - grafico(): creación/actualización de gráficos Chart.js 2.x local.
 */
window.ReportesComunes = (function () {
    'use strict';

    /** URLSearchParams con los filtros del formulario + pares extra. */
    function parametros(extras) {
        const params = new URLSearchParams();
        const form = document.getElementById('form-reporte');
        if (form) {
            new FormData(form).forEach((valor, clave) => {
                const texto = String(valor).trim();
                if (texto !== '') {
                    params.set(clave, texto);
                }
            });
        }
        Object.entries(extras || {}).forEach(([clave, valor]) => {
            if (valor !== null && valor !== undefined && String(valor).trim() !== '') {
                params.set(clave, String(valor).trim());
            }
        });
        return params;
    }

    /** Rellena un select conservando su primera opción (la de "Todos"). */
    function rellenar(id, opciones) {
        const select = document.getElementById(id);
        if (!select || !Array.isArray(opciones)) return;
        const previo = select.value;
        select.querySelectorAll('option:not([value=""])').forEach(op => op.remove());
        opciones.forEach(item => {
            const opcion = document.createElement('option');
            opcion.value = String(item.id);
            opcion.textContent = item.nombre;
            select.appendChild(opcion);
        });
        if (previo) select.value = previo;
    }

    /** Descarga los catálogos y llena los selects existentes en la página. */
    async function cargarCatalogos() {
        const datos = await apiFetch(BASE_URL + 'api/reportes/catalogos');
        rellenar('pnf', datos.pnfs);
        rellenar('servicio_destino', datos.servicios);
        rellenar('estado_cita', datos.estados_cita);
        rellenar('area', (datos.areas || []).map(area => ({ id: area, nombre: area })));
        return datos;
    }

    /**
     * Crea o reemplaza un gráfico. Usa la API de Chart.js 2.x
     * (options.legend, no options.plugins.legend): el archivo local es
     * dist/js/dashboard/Chart.min.js (v2.9.4), sin CDN.
     */
    function grafico(canvasId, instancia, labels, valores, colores, titulo, tipo) {
        const lienzo = document.getElementById(canvasId);
        if (!lienzo) return instancia;
        if (instancia) instancia.destroy();
        if (typeof window.Chart === 'undefined') {
            console.error('Chart.js local no está cargado.');
            return null;
        }
        return new Chart(lienzo, {
            type: tipo || 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: titulo,
                    data: valores,
                    backgroundColor: colores,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 14, fontColor: '#5a5c69' },
                },
            },
        });
    }

    /** Escapa texto antes de inyectarlo en el HTML. */
    function escapar(texto) {
        if (texto === null || texto === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    }

    /** Paleta de colores de los gráficos (cicla para etiquetas extra). */
    function paleta(cantidad) {
        const base = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#6f42c1'];
        const colores = [];
        for (let i = 0; i < cantidad; i++) {
            colores.push(base[i % base.length]);
        }
        return colores;
    }

    /** Devuelve un objeto {clave: cantidad} a partir de las filas. */
    function contarPor(filas, obtenerClave) {
        const conteos = {};
        (filas || []).forEach(fila => {
            const clave = obtenerClave(fila);
            if (clave) conteos[clave] = (conteos[clave] || 0) + 1;
        });
        return conteos;
    }

    /** Muestra un error de negocio del backend en pantalla. */
    function error(titulo, err) {
        console.error(titulo, err);
        if (typeof AlertManager !== 'undefined') {
            AlertManager.error(titulo, (err && err.mensaje) || 'No se pudo completar la operación.');
        }
    }

    return { parametros, rellenar, cargarCatalogos, grafico, escapar, paleta, contarPor, error };
})();
