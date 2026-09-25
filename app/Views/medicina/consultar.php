<?php
$titulo = 'Consultar medicina';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top"><div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">Consultas médicas</h1><div class="d-flex gap-2"><button id="btn-recargar-medicina" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Recargar</button><a href="<?= BASE_URL ?>medicina/crear" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nueva consulta</a></div></div>
            <div class="row"><?php include BASE_PATH . '/app/Views/medicina/components/stats.php'; ?></div>
            <div class="card shadow mb-4"><div class="card-body"><div class="table-responsive"><table id="tablaMedicina" class="table table-bordered table-hover align-middle" width="100%"><thead class="table-light"><tr><th>Fecha</th><th>Beneficiario</th><th>Profesional</th><th>Patología</th><th>Resumen</th><th>Insumos</th><th class="text-center">Acciones</th></tr></thead><tbody id="tbodyMedicina"></tbody></table></div></div></div>
        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>

<!-- ==================== MODAL EDITAR (solo datos textuales del diagnóstico) ==================== -->
<div class="modal fade" id="modal-editar-medicina" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar consulta médica</h5>
                    <small class="opacity-75" id="editar_subtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="form-editar-medicina" novalidate>
                <input type="hidden" id="editar_id_consulta_med" name="id_consulta_med">

                <div class="modal-body">

                    <!-- Datos NO editables (integridad de auditoría e inventario) -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editar_beneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editar_beneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_empleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editar_empleado" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label for="editar_insumos" class="form-label text-muted small mb-1">Insumos usados</label>
                            <textarea id="editar_insumos" class="form-control" rows="2" readonly></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario, el empleado que atendió y los insumos usados no pueden modificarse (integridad de auditoría e inventario).
                    </div>

                    <!-- Campos editables -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_id_patologia" class="form-label">Patología *</label>
                            <select id="edit_id_patologia" name="id_patologia" class="form-select select2" data-placeholder="Seleccione…">
                                <option value="">Cargando…</option>
                            </select>
                            <div id="edit_id_patologiaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_tipo_sangre" class="form-label">Tipo de sangre *</label>
                            <select id="edit_tipo_sangre" name="tipo_sangre" class="form-select" required>
                                <option value="" selected disabled>Seleccione tipo de sangre</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                            <div id="edit_tipo_sangreError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_estatura" class="form-label">Estatura (m) *</label>
                            <input type="number" class="form-control" id="edit_estatura" name="estatura"
                                   step="0.01" min="0.50" max="2.50" placeholder="Ej: 1.70" required>
                            <div id="edit_estaturaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_peso" class="form-label">Peso (kg) *</label>
                            <input type="number" class="form-control" id="edit_peso" name="peso"
                                   step="0.01" min="2" max="300" placeholder="Ej: 72.50" required>
                            <div id="edit_pesoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_motivo_visita" class="form-label">Motivo de visita *</label>
                            <textarea id="edit_motivo_visita" name="motivo_visita" class="form-control" rows="2" maxlength="255"></textarea>
                            <div id="edit_motivo_visitaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_diagnostico" class="form-label">Diagnóstico *</label>
                            <textarea id="edit_diagnostico" name="diagnostico" class="form-control" rows="3" maxlength="255"></textarea>
                            <div id="edit_diagnosticoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_tratamiento" class="form-label">Tratamiento *</label>
                            <textarea id="edit_tratamiento" name="tratamiento" class="form-control" rows="3" maxlength="255"></textarea>
                            <div id="edit_tratamientoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_observaciones" class="form-label">Observaciones</label>
                            <textarea id="edit_observaciones" name="observaciones" class="form-control" rows="3" maxlength="255"></textarea>
                            <div id="edit_observacionesError" class="form-text text-danger"></div>
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
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/consultar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/editar.js" defer></script>
</body></html>
