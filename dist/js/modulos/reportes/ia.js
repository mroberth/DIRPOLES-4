/**
 * dist/js/modulos/reportes/ia.js
 * ---------------------------------------------------------------
 * Módulo para la generación de informes con Inteligencia Artificial (DIRPOLES-IA).
 * Se integra con los 10 reportes estadísticos, renderiza membrete institucional,
 * distintivo de IA, Markdown sanitizado y diagramas Mermaid.js protegidos contra cortes.
 */
window.ReportesIa = (function () {
    'use strict';

    /**
     * Mapea el identificador técnico de módulo a su denominación formal académica.
     */
    function mapearNombreModulo(codigo) {
        const mapa = {
            'general': 'Consolidado General de Servicios',
            'medicina': 'Servicio Médico y Morbilidad',
            'psicologia': 'Servicio Psicológico y Salud Mental',
            'orientacion': 'Servicio de Orientación Vocacional',
            'trabajo_social': 'Coordinación de Trabajo Social (Becas y Ayudas)',
            'trabajo-social': 'Coordinación de Trabajo Social (Becas y Ayudas)',
            'discapacidad': 'Unidad de Atención a Personas con Discapacidad',
            'referencias': 'Gestión de Referencias Inter-Servicios',
            'jornadas': 'Jornadas Médicas y Comunitarias',
            'mobiliario': 'Bienes Nacionales y Mobiliario',
            'transporte': 'Rutas y Transporte Estudiantil'
        };
        const clave = String(codigo || '').trim().toLowerCase();
        return mapa[clave] || (clave ? clave.charAt(0).toUpperCase() + clave.slice(1) : 'General');
    }

    /**
     * Mapea la clave de enfoque/intención a su descripción en español.
     */
    function mapearNombreIntencion(codigo) {
        const mapa = {
            'resumen_ejecutivo': 'Resumen Ejecutivo',
            'alertas_y_anomalias': 'Alertas y Anomalías',
            'tendencias_y_patrones': 'Tendencias y Patrones',
            'recomendaciones': 'Recomendaciones Estratégicas'
        };
        const clave = String(codigo || '').trim().toLowerCase();
        return mapa[clave] || 'Resumen Ejecutivo';
    }

    /**
     * Genera el HTML del membrete institucional formal de la UPTAEB y DIRPOLES,
     * acompañado del distintivo visual elegante de Inteligencia Artificial.
     */
    function generarMembreteHtml(modulo, intencion, meta) {
        const urlBase = (typeof BASE_URL !== 'undefined')
            ? BASE_URL
            : (document.querySelector('.card-informe-ia')?.dataset.baseUrl || '');

        const nombreModulo = mapearNombreModulo(modulo);
        const nombreIntencion = mapearNombreIntencion(intencion);

        // Fecha de emisión formal en español venezolano
        const ahora = new Date();
        const opcionesFecha = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        let fechaEmision = '';
        try {
            fechaEmision = ahora.toLocaleString('es-VE', opcionesFecha);
        } catch (e) {
            fechaEmision = ahora.toLocaleDateString() + ' ' + ahora.toLocaleTimeString();
        }

        // Generar código único de auditoría para el informe
        const anioMesDia = ahora.getFullYear().toString() +
            String(ahora.getMonth() + 1).padStart(2, '0') +
            String(ahora.getDate()).padStart(2, '0');
        const prefijoMod = (modulo || 'GEN').toUpperCase().replace(/[^A-Z]/g, '').substring(0, 3) || 'GEN';
        const hashAleatorio = Math.random().toString(36).substring(2, 6).toUpperCase();
        const codigoAuditoria = `INF-IA-${anioMesDia}-${prefijoMod}-${hashAleatorio}`;

        // Intentar clonar la plantilla HTML declarada en la vista Blade/PHP si existe
        const tpl = document.getElementById('tpl-informe-ia-membrete');
        if (tpl && tpl.content) {
            const clone = tpl.content.cloneNode(true);
            const elMod = clone.querySelector('.meta-modulo-nombre');
            const elInt = clone.querySelector('.meta-intencion-nombre');
            const elFec = clone.querySelector('.meta-fecha-emision');
            const elCod = clone.querySelector('.meta-codigo-hash');

            if (elMod) elMod.textContent = nombreModulo;
            if (elInt) elInt.textContent = nombreIntencion;
            if (elFec) elFec.textContent = fechaEmision;
            if (elCod) elCod.textContent = codigoAuditoria;

            const temp = document.createElement('div');
            temp.appendChild(clone);
            return temp.innerHTML;
        }

        // Fallback robusto en caso de ausencia de la etiqueta <template>
        return `
            <div class="informe-ia-membrete mb-4">
                <div class="membrete-cintillo-superior d-flex align-items-center justify-content-between mb-2">
                    <div class="membrete-logo-box text-start">
                        <img src="${urlBase}dist/img/dirpoles.png" alt="Logo DIRPOLES" class="membrete-logo">
                    </div>
                    <div class="membrete-textos-institucionales text-center flex-grow-1 px-3">
                        <div class="institucion-pais">REPÚBLICA BOLIVARIANA DE VENEZUELA</div>
                        <div class="institucion-ministerio">MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN UNIVERSITARIA</div>
                        <div class="institucion-universidad">UNIVERSIDAD POLITÉCNICA TERRITORIAL DE LARA "ANDRÉS ELOY BLANCO"</div>
                        <div class="institucion-direccion">DIRECCIÓN DE POLÍTICAS ESTUDIANTILES (DIRPOLES)</div>
                        <div class="institucion-sistema">SISTEMA INTEGRADO DE GESTIÓN ADMINISTRATIVA — DIRPOLES 4</div>
                    </div>
                    <div class="membrete-sello-box text-end">
                        <div class="sello-tecnologico">
                            <i class="fas fa-microchip me-1 text-primary"></i>
                            <span class="sello-tecnologico-texto">DIRPOLES-IA</span>
                            <div class="sello-subtexto">UPTAEB &bull; LARA</div>
                        </div>
                    </div>
                </div>

                <div class="membrete-linea-gradiente"></div>

                <div class="distintivo-ia-wrapper text-center my-3">
                    <div class="distintivo-ia-badge">
                        <span class="distintivo-ia-icono">
                            <i class="fas fa-brain"></i>
                        </span>
                        <span class="distintivo-ia-texto">
                            INFORME ASISTIDO POR INTELIGENCIA ARTIFICIAL Y MACHINE LEARNING
                        </span>
                        <span class="distintivo-ia-tech">
                            <i class="fas fa-sparkles me-1"></i>GEMINI AI
                        </span>
                    </div>
                </div>

                <div class="membrete-meta-strip">
                    <div class="meta-item">
                        <i class="fas fa-layer-group me-1 text-primary"></i><strong>ÁREA / MÓDULO:</strong> <span class="meta-modulo-nombre">${nombreModulo}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-bullseye me-1 text-info"></i><strong>ENFOQUE:</strong> <span class="meta-intencion-nombre">${nombreIntencion}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt me-1 text-secondary"></i><strong>EMISIÓN:</strong> <span class="meta-fecha-emision">${fechaEmision}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-fingerprint me-1 text-dark"></i><strong>CÓDIGO:</strong> <code class="meta-codigo-hash">${codigoAuditoria}</code>
                    </div>
                </div>
                <hr class="membrete-separador-cuerpo mt-2 mb-3">
            </div>
        `;
    }

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
            const urlBase = (typeof BASE_URL !== 'undefined')
                ? BASE_URL
                : (document.querySelector('.card-informe-ia')?.dataset.baseUrl || '');

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

            let json;
            try {
                json = await resp.json();
            } catch (errJson) {
                console.error('El servidor devolvió una respuesta no JSON (HTTP ' + resp.status + '):', errJson);
                if (resp.status === 504 || resp.status === 408) {
                    mostrarError('El servicio de IA tardó demasiado tiempo en responder (Tiempo de espera agotado).');
                } else if (resp.status === 502 || resp.status === 503) {
                    mostrarError('El servicio de IA o la base de datos no están disponibles en este momento (HTTP ' + resp.status + ').');
                } else {
                    mostrarError('Error en el servidor al generar el informe con IA (HTTP ' + resp.status + '). Verifique que el microservicio esté encendido en el puerto 8000.');
                }
                return;
            }

            if (!json.exito) {
                const msg = (json.error && json.error.mensaje) ? json.error.mensaje : 'No se pudo generar el informe.';
                mostrarError(msg);
                return;
            }

            renderizarInforme(json.datos, modulo, intencion);

        } catch (e) {
            console.error('Error al generar informe IA:', e);
            mostrarError('El servicio de IA no está disponible o no se pudo conectar. Verifique que DIRPOLES-IA esté activo en http://127.0.0.1:8000.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = textoOriginalBoton;
        }
    }

    /**
     * Renderiza el resultado Markdown sanitizado con marked + DOMPurify,
     * inyecta el membrete institucional exclusivo con el distintivo de IA
     * y procesa diagramas Mermaid.js.
     */
    function renderizarInforme(datos, modulo, intencion) {
        const zona = document.getElementById('zona-informe-ia');
        if (!zona) return;

        const mod = modulo || (datos.meta && datos.meta.modulo) || window.REPORTES_TIPO || 'general';
        const inte = intencion || (datos.meta && datos.meta.intencion) || document.getElementById('ia-intencion')?.value || 'resumen_ejecutivo';

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

        const htmlMembrete = generarMembreteHtml(mod, inte, meta);

        zona.innerHTML = `
            <div class="card border-0 shadow-sm informe-ia-resultado-card">
                <div class="card-body p-4 p-md-5">
                    ${htmlMembrete}

                    <div class="markdown-body text-dark" style="line-height: 1.6;">
                        ${htmlMarkdown}
                    </div>

                    <hr class="my-4 informe-ia-divider">

                    <div class="d-flex justify-content-between align-items-center flex-wrap small text-muted informe-ia-footer">
                        <div class="mb-2 mb-md-0">
                            <i class="fas fa-database me-1"></i><strong>${registros}</strong> registros procesados
                            <span class="mx-2">•</span>
                            <i class="fas fa-clock me-1"></i><strong>${tiempo}s</strong> de respuesta
                            ${badgeModo}
                        </div>
                        <div class="informe-ia-acciones">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 shadow-sm" onclick="ReportesIa.imprimir()">
                                <i class="fas fa-print me-1"></i>Imprimir / PDF
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
     * Busca bloques \`\`\`mermaid y los convierte en gráficos dinámicos con Mermaid.js,
     * aplicando estilos para evitar que se corten entre páginas al imprimir.
     */
    function procesarBloquesMermaid(contenedor) {
        if (!contenedor || typeof mermaid === 'undefined') return;

        const bloquesCode = contenedor.querySelectorAll('pre code.language-mermaid, pre code.lang-mermaid, pre code.mermaid');
        if (bloquesCode.length === 0) return;

        bloquesCode.forEach(codeNode => {
            const preNode = codeNode.parentNode;
            const divMermaid = document.createElement('div');
            divMermaid.className = 'mermaid text-center my-4 p-3 bg-white rounded shadow-sm';
            divMermaid.style.display = 'block';
            divMermaid.style.pageBreakInside = 'avoid';
            divMermaid.style.breakInside = 'avoid';
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
     * Lanza la vista de impresión del informe en PDF del navegador.
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
