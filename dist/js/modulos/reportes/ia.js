/**
 * dist/js/modulos/reportes/ia.js
 * ---------------------------------------------------------------
 * Módulo para la generación de informes con Inteligencia Artificial (DIRPOLES-IA).
 * Se integra con los 10 reportes estadísticos y soporta gráficos Mermaid.js.
 */
window.ReportesIa = (function () {
    'use strict';

    /**
     * Extrae los filtros actuales del #form-reporte como un objeto JSON key/value.
     */
    function obtenerFiltros() {
        const form = document.getElementById('form-reporte');
        const filtros = {};
        if (!form) return filtros;

        const formData = new FormData(form);
        formData.forEach((valor, clave) => {
            const texto = String(valor).trim();
            if (texto !== '') {
                if (clave === 'pnf' || clave === 'servicio_destino' || clave === 'limit') {
                    filtros[clave] = parseInt(texto, 10);
                } else {
                    filtros[clave] = texto;
                }
            }
        });
        return filtros;
    }

    /**
     * Genera el informe con IA enviando { modulo, intencion, filtros, observacion_usuario }
     * a la puerta JSON server-side: api/reportes/ia/informe.
     */
    async function generar(modulo) {
        const btn = document.getElementById('btn-informe-ia');
        const zona = document.getElementById('zona-informe-ia');
        const intencionSelect = document.getElementById('ia-intencion');
        const observacionInput = document.getElementById('ia-observacion');

        if (!btn || !zona) return;

        const intencion = intencionSelect ? intencionSelect.value : 'resumen_ejecutivo';
        const observacion = observacionInput ? observacionInput.value.trim() : '';
        const filtros = obtenerFiltros();

        // Deshabilitar botón y mostrar spinner
        btn.disabled = true;
        const textoOriginalBoton = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Generando...';

        zona.innerHTML = `
            <div class="alert alert-info d-flex align-items-center mb-0 shadow-sm" role="alert">
                <div class="spinner-border spinner-border-sm me-3 flex-shrink-0" role="status"></div>
                <div>
                    <strong>Analizando datos con IA...</strong><br>
                    <small class="text-muted">Este proceso puede tardar hasta 30 segundos mientras Gemini examina los registros y genera la síntesis.</small>
                </div>
            </div>
        `;

        try {
            const urlBase = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
            const resp = await fetch(urlBase + 'api/reportes/ia/informe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    modulo: modulo,
                    intencion: intencion,
                    filtros: filtros,
                    observacion_usuario: observacion || null
                })
            });

            const json = await resp.json();

            if (!json.exito) {
                const msg = (json.error && json.error.mensaje) ? json.error.mensaje : 'No se pudo generar el informe.';
                mostrarError(msg);
                return;
            }

            renderizarInforme(json.datos);

        } catch (e) {
            console.error('Error al generar informe IA:', e);
            mostrarError('El servicio de IA no está disponible en este momento. Los reportes tradicionales siguen funcionando con normalidad.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = textoOriginalBoton;
        }
    }

    /**
     * Renderiza el resultado Markdown sanitizado con marked + DOMPurify y procesa diagramas Mermaid.
     */
    function renderizarInforme(datos) {
        const zona = document.getElementById('zona-informe-ia');
        if (!zona) return;

        let htmlMarkdown = '';
        if (typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined') {
            const rawHtml = marked.parse(datos.informe_markdown || '');
            htmlMarkdown = DOMPurify.sanitize(rawHtml, {
                ADD_TAGS: ['foreignObject'],
                ADD_ATTR: ['target']
            });
        } else {
            const raw = (typeof ReportesComunes !== 'undefined' && ReportesComunes.escapar)
                ? ReportesComunes.escapar(datos.informe_markdown)
                : datos.informe_markdown;
            htmlMarkdown = `<pre class="mb-0" style="white-space: pre-wrap;">${raw}</pre>`;
        }

        const meta = datos.meta || {};
        const registros = meta.registros_procesados || 0;
        const tiempo = meta.tiempo_procesamiento_seg ? meta.tiempo_procesamiento_seg.toFixed(2) : '0';
        const modo = meta.modo_informe || 'gemini';

        const badgeModo = (modo !== 'gemini')
            ? '<span class="badge bg-secondary ms-2"><i class="fas fa-robot me-1"></i>modo simulado</span>'
            : '<span class="badge bg-success ms-2"><i class="fas fa-check-circle me-1"></i>Gemini IA</span>';

        zona.innerHTML = `
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-4">
                    <div class="markdown-body text-dark" style="line-height: 1.6;">
                        ${htmlMarkdown}
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap small text-muted">
                        <div>
                            <i class="fas fa-database me-1"></i><strong>${registros}</strong> registros procesados
                            <span class="mx-2">•</span>
                            <i class="fas fa-clock me-1"></i><strong>${tiempo}s</strong> de respuesta
                            ${badgeModo}
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1" onclick="ReportesIa.imprimir()">
                                <i class="fas fa-print me-1"></i>Imprimir
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="ReportesIa.limpiar()">
                                <i class="fas fa-times me-1"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Procesar diagramas Mermaid si están presentes en la respuesta
        procesarBloquesMermaid(zona);
    }

    /**
     * Busca bloques ```mermaid y los convierte en gráficos dinámicos con Mermaid.js.
     */
    function procesarBloquesMermaid(contenedor) {
        if (!contenedor || typeof mermaid === 'undefined') return;

        const bloquesCode = contenedor.querySelectorAll('pre code.language-mermaid, pre code.lang-mermaid, pre code.mermaid');
        if (bloquesCode.length === 0) return;

        bloquesCode.forEach(codeNode => {
            const preNode = codeNode.parentNode;
            const divMermaid = document.createElement('div');
            divMermaid.className = 'mermaid text-center my-4 p-3 bg-white rounded shadow-sm';
            divMermaid.textContent = codeNode.textContent;
            preNode.parentNode.replaceChild(divMermaid, preNode);
        });

        try {
            mermaid.initialize({
                startOnLoad: false,
                theme: 'default',
                securityLevel: 'loose',
                fontFamily: 'system-ui, -apple-system, sans-serif'
            });
            mermaid.run({
                nodes: contenedor.querySelectorAll('.mermaid')
            });
        } catch (e) {
            console.error('Error al renderizar diagramas Mermaid:', e);
        }
    }

    /**
     * Muestra un banner amigable de error en la zona del informe y un popup de AlertManager.
     */
    function mostrarError(mensaje) {
        const zona = document.getElementById('zona-informe-ia');
        const msgEscapado = (typeof ReportesComunes !== 'undefined' && ReportesComunes.escapar)
            ? ReportesComunes.escapar(mensaje)
            : mensaje;

        if (zona) {
            zona.innerHTML = `
                <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-0" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>No fue posible generar el informe:</strong> ${msgEscapado}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }

        if (typeof AlertManager !== 'undefined') {
            AlertManager.error('Informe con IA', mensaje);
        }
    }

    /**
     * Limpia la zona del informe.
     */
    function limpiar() {
        const zona = document.getElementById('zona-informe-ia');
        if (zona) zona.innerHTML = '';
    }

    /**
     * Lanza la vista de impresión del informe.
     */
    function imprimir() {
        window.print();
    }

    /**
     * Auto-inicialización en DOMContentLoaded usando window.REPORTES_TIPO.
     */
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btn-informe-ia');
        if (btn) {
            btn.addEventListener('click', function () {
                const modulo = window.REPORTES_TIPO || 'general';
                generar(modulo);
            });
        }
    });

    return { generar, renderizarInforme, mostrarError, limpiar, imprimir };
})();
