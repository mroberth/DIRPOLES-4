<?php
/**
 * app/Views/reportes/components/ia_card.php
 * ---------------------------------------------------------------
 * Componente UI para la generación de informes textuales con IA (DIRPOLES-IA).
 */
?>
<link rel="stylesheet" href="<?= BASE_URL ?>dist/css/modulos/reportes-ia.css?v=<?= file_exists(BASE_PATH . 'dist/css/modulos/reportes-ia.css') ? filemtime(BASE_PATH . 'dist/css/modulos/reportes-ia.css') : '2.2' ?>">
<script src="<?= BASE_URL ?>plugins/mermaid/mermaid.min.js"></script>

<div class="card shadow mb-4 border-start border-info border-4 card-informe-ia" data-base-url="<?= BASE_URL ?>">

    <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-info">
            <i class="fas fa-brain me-2"></i>Informe Inteligente con IA (DIRPOLES-IA)
        </h6>
        <span class="badge bg-info text-white">Gemini 3.5 Flash</span>
    </div>
    <div class="card-body">
        <form id="form-informe-ia" onsubmit="return false;">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-sm-6">
                    <label for="ia-intencion" class="form-label font-weight-bold small">Enfoque del Informe:</label>
                    <select id="ia-intencion" name="intencion" class="form-select form-select-sm">
                        <option value="resumen_ejecutivo">Resumen ejecutivo</option>
                        <option value="alertas_y_anomalias">Alertas y anomalías</option>
                        <option value="tendencias_y_patrones">Tendencias y patrones</option>
                        <option value="recomendaciones">Recomendaciones</option>
                    </select>
                </div>
                <div class="col-md-5 col-sm-6">
                    <label for="ia-observacion" class="form-label font-weight-bold small">Observación del usuario (opcional):</label>
                    <input type="text" id="ia-observacion" name="observacion" class="form-control form-control-sm" maxlength="1000" placeholder="Instrucciones adicionales para la IA (opcional)...">
                </div>
                <div class="col-md-3 col-sm-12 text-end">
                    <button type="button" id="btn-informe-ia" class="btn btn-info btn-sm shadow-sm w-100 text-white">
                        <i class="fas fa-magic me-1"></i>Generar informe con IA
                    </button>
                </div>
            </div>
        </form>

        <!-- Contenedor donde se inyecta el informe renderizado con su membrete y diagramas -->
        <div id="zona-informe-ia" class="mt-3"></div>

        <!-- Plantilla del Membrete Institucional y Distintivo IA para Informes Formales -->
        <template id="tpl-informe-ia-membrete">
            <div class="informe-ia-membrete mb-4">
                <div class="membrete-cintillo-superior d-flex align-items-center justify-content-between mb-2">
                    <div class="membrete-logo-box text-start">
                        <img src="<?= BASE_URL ?>dist/img/dirpoles.png" alt="Logo DIRPOLES" class="membrete-logo">
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

                <!-- Línea decorativa institucional con gradiente -->
                <div class="membrete-linea-gradiente"></div>

                <!-- Distintivo visual elegante exclusivo de Inteligencia Artificial y Machine Learning -->
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

                <!-- Cintillo de metadatos del informe -->
                <div class="membrete-meta-strip">
                    <div class="meta-item">
                        <i class="fas fa-layer-group me-1 text-primary"></i><strong>ÁREA / MÓDULO:</strong> <span class="meta-modulo-nombre">---</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-bullseye me-1 text-info"></i><strong>ENFOQUE:</strong> <span class="meta-intencion-nombre">---</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt me-1 text-secondary"></i><strong>EMISIÓN:</strong> <span class="meta-fecha-emision">---</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-fingerprint me-1 text-dark"></i><strong>CÓDIGO:</strong> <code class="meta-codigo-hash">---</code>
                    </div>
                </div>
                <hr class="membrete-separador-cuerpo mt-2 mb-3">
            </div>
        </template>
    </div>
</div>
