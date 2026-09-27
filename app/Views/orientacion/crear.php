<?php
$titulo = 'Registrar orientación';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
<div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestionar diagnósticos: Orientación</h1>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-circle-question me-1"></i>Ayuda
                    </button>
                    <a href="<?= BASE_URL ?>orientacion/consultar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Consultar
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/orientacion/components/stats.php'; ?>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nueva orientación</h6>
                </div>
                <div class="card-body">

                    <form id="form-orientacion" novalidate>

                        <!-- ============ Beneficiario ============ -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="id_beneficiario" class="form-label">Beneficiario *</label>
                                <select id="id_beneficiario" name="id_beneficiario"
                                        class="form-select select2" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div id="id_beneficiarioError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Motivo ============ -->
                        <div class="mt-3">
                            <label for="motivo_orientacion" class="form-label">Motivo de la orientación *</label>
                            <textarea id="motivo_orientacion" name="motivo_orientacion"
                                      class="form-control" rows="3" maxlength="5000"
                                      placeholder="Describa el motivo principal de la orientación..."></textarea>
                            <div id="motivo_orientacionError" class="form-text text-danger"></div>
                        </div>

                        <!-- ============ Descripción ============ -->
                        <div class="mt-3">
                            <label for="descripcion_orientacion" class="form-label">Descripción de la sesión *</label>
                            <textarea id="descripcion_orientacion" name="descripcion_orientacion"
                                      class="form-control" rows="3" maxlength="5000"
                                      placeholder="Describa el desarrollo de la sesión de orientación..."></textarea>
                            <div id="descripcion_orientacionError" class="form-text text-danger"></div>
                        </div>

                        <!-- ============ Indicaciones + Observaciones ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="indicaciones_orientacion" class="form-label">Indicaciones *</label>
                                <textarea id="indicaciones_orientacion" name="indicaciones_orientacion"
                                          class="form-control" rows="3" maxlength="5000"
                                          placeholder="Indicaciones y recomendaciones para el beneficiario..."></textarea>
                                <div id="indicaciones_orientacionError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="obs_adic_orientacion" class="form-label">Observaciones adicionales *</label>
                                <textarea id="obs_adic_orientacion" name="obs_adic_orientacion"
                                          class="form-control" rows="3" maxlength="5000"
                                          placeholder="Observaciones adicionales, pronóstico, seguimiento recomendado..."></textarea>
                                <div id="obs_adic_orientacionError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Registrar orientación
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>
<?php include BASE_PATH . '/app/Views/template/script.php'; ?>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/crear.js" defer></script>
</body></html>
