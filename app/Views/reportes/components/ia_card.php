<?php
/**
 * app/Views/reportes/components/ia_card.php
 * ---------------------------------------------------------------
 * Componente UI para la generación de informes textuales con IA (DIRPOLES-IA).
 */
?>
<link rel="stylesheet" href="<?= BASE_URL ?>dist/css/modulos/reportes-ia.css">
<script src="<?= BASE_URL ?>plugins/mermaid/mermaid.min.js"></script>

<div class="card shadow mb-4 border-start border-info border-4 card-informe-ia">

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

        <div id="zona-informe-ia" class="mt-3"></div>
    </div>
</div>
