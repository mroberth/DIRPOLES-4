<?php
$titulo = 'Consultar orientación';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top"><div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">Orientaciones</h1><div class="d-flex gap-2"><button id="btn-recargar-orientacion" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Recargar</button><a href="<?= BASE_URL ?>orientacion/crear" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nueva orientación</a></div></div>
            <div class="row"><?php include BASE_PATH . '/app/Views/orientacion/components/stats.php'; ?></div>
            <div class="card shadow mb-4"><div class="card-body"><div class="table-responsive"><table id="tablaOrientacion" class="table table-bordered table-hover align-middle" width="100%"><thead class="table-light"><tr><th>Fecha</th><th>Beneficiario</th><th>Profesional</th><th>Motivo</th><th class="text-center">Acciones</th></tr></thead><tbody id="tbodyOrientacion"></tbody></table></div></div></div>
        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>

<!-- ==================== MODAL EDITAR (solo los 4 campos de texto) ==================== -->
<div class="modal fade" id="modal-editar-orientacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar orientación</h5>
                    <small class="opacity-75" id="editar_subtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="form-editar-orientacion" novalidate>
                <input type="hidden" id="editar_id_orientacion" name="id_orientacion">

                <div class="modal-body">

                    <!-- Datos NO editables (integridad de auditoría) -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar_beneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editar_beneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_empleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editar_empleado" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario y el empleado que atendió no pueden modificarse (integridad de auditoría).
                    </div>

                    <!-- Campos editables -->
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_motivo_orientacion" class="form-label">Motivo de la orientación *</label>
                            <textarea id="edit_motivo_orientacion" name="motivo_orientacion" class="form-control" rows="3" maxlength="5000"></textarea>
                            <div id="edit_motivo_orientacionError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-12">
                            <label for="edit_descripcion_orientacion" class="form-label">Descripción de la sesión *</label>
                            <textarea id="edit_descripcion_orientacion" name="descripcion_orientacion" class="form-control" rows="3" maxlength="5000"></textarea>
                            <div id="edit_descripcion_orientacionError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_indicaciones_orientacion" class="form-label">Indicaciones *</label>
                            <textarea id="edit_indicaciones_orientacion" name="indicaciones_orientacion" class="form-control" rows="3" maxlength="5000"></textarea>
                            <div id="edit_indicaciones_orientacionError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_obs_adic_orientacion" class="form-label">Observaciones adicionales *</label>
                            <textarea id="edit_obs_adic_orientacion" name="obs_adic_orientacion" class="form-control" rows="3" maxlength="5000"></textarea>
                            <div id="edit_obs_adic_orientacionError" class="form-text text-danger"></div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/template/script.php'; ?>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/consultar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/orientacion/editar.js" defer></script>
</body></html>
