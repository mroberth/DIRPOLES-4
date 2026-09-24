<?php
$titulo = 'Consultar psicología';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top"><div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">Consultas psicológicas</h1><div class="d-flex gap-2"><button id="btn-recargar-psicologia" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Recargar</button><a href="<?= BASE_URL ?>psicologia/crear" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nueva consulta</a></div></div>
            <div class="row"><?php include BASE_PATH . '/app/Views/psicologia/components/stats.php'; ?></div>
            <div class="card shadow mb-4"><div class="card-body"><div class="table-responsive"><table id="tablaPsicologia" class="table table-bordered table-hover align-middle" width="100%"><thead class="table-light"><tr><th>Fecha</th><th>Beneficiario</th><th>Profesional</th><th>Tipo</th><th>Patología</th><th>Resumen</th><th class="text-center">Acciones</th></tr></thead><tbody id="tbodyPsicologia"></tbody></table></div></div></div>
        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>

<!-- ==================== MODAL EDITAR (solo datos del diagnóstico) ==================== -->
<div class="modal fade" id="modal-editar-psicologia" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar consulta psicológica</h5>
                    <small class="opacity-75" id="editar_subtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="form-editar-psicologia" novalidate>
                <input type="hidden" id="editar_id_psicologia" name="id_psicologia">
                <input type="hidden" id="editar_tipo_consulta" name="tipo_consulta">

                <div class="modal-body">

                    <!-- Datos NO editables (integridad de auditoría) -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar_beneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editar_beneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_profesional" class="form-label text-muted small mb-1">Profesional que atendió</label>
                            <input type="text" id="editar_profesional" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_tipo_consulta_texto" class="form-label text-muted small mb-1">Tipo de consulta</label>
                            <input type="text" id="editar_tipo_consulta_texto" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario, el profesional y el tipo de consulta no pueden modificarse (integridad de auditoría).
                    </div>

                    <!-- Diagnóstico -->
                    <div id="edit-campos-diagnostico" class="row g-3 d-none">
                        <div class="col-md-6">
                            <label for="edit_id_patologia" class="form-label">Patología *</label>
                            <select id="edit_id_patologia" name="id_patologia" class="form-select select2" data-placeholder="Seleccione…">
                                <option value="">Cargando…</option>
                            </select>
                            <div id="edit_id_patologiaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_diagnostico" class="form-label">Diagnóstico *</label>
                            <textarea id="edit_diagnostico" name="diagnostico" class="form-control" rows="3"></textarea>
                            <div id="edit_diagnosticoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-12">
                            <label for="edit_tratamiento_gen" class="form-label">Tratamiento general</label>
                            <textarea id="edit_tratamiento_gen" name="tratamiento_gen" class="form-control" rows="3"></textarea>
                            <div id="edit_tratamiento_genError" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <!-- Retiro temporal -->
                    <div id="edit-campos-retiro" class="row g-3 d-none">
                        <div class="col-md-8">
                            <label for="edit_motivo_retiro" class="form-label">Motivo del retiro *</label>
                            <textarea id="edit_motivo_retiro" name="motivo_retiro" class="form-control" rows="3"></textarea>
                            <div id="edit_motivo_retiroError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_duracion_retiro" class="form-label">Duración *</label>
                            <input id="edit_duracion_retiro" name="duracion_retiro" class="form-control" maxlength="50">
                            <div id="edit_duracion_retiroError" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <!-- Cambio de carrera -->
                    <div id="edit-campos-cambio" class="row g-3 d-none">
                        <div class="col-12">
                            <label for="edit_motivo_cambio" class="form-label">Motivo del cambio de carrera *</label>
                            <input id="edit_motivo_cambio" name="motivo_cambio" class="form-control" maxlength="100">
                            <div id="edit_motivo_cambioError" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <!-- Observaciones (común a los 3 tipos) -->
                    <div class="mt-3">
                        <label for="edit_observaciones" class="form-label">Observaciones</label>
                        <textarea id="edit_observaciones" name="observaciones" class="form-control" rows="3" maxlength="5000"></textarea>
                        <div id="edit_observacionesError" class="form-text text-danger"></div>
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
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/consultar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/editar.js" defer></script>
</body></html>