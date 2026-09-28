<?php
$titulo = 'Consultar discapacidad';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top"><div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">Diagnósticos de discapacidad</h1><div class="d-flex gap-2"><button id="btn-recargar-discapacidad" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Recargar</button><a href="<?= BASE_URL ?>discapacidad/crear" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nuevo diagnóstico</a></div></div>
            <div class="row"><?php include BASE_PATH . '/app/Views/discapacidad/components/stats.php'; ?></div>
            <div class="card shadow mb-4"><div class="card-body"><div class="table-responsive"><table id="tablaDiscapacidad" class="table table-bordered table-hover align-middle" width="100%"><thead class="table-light"><tr><th>Fecha</th><th>Beneficiario</th><th>Profesional</th><th>Tipo</th><th>Grado</th><th>Diagnóstico</th><th class="text-center">Acciones</th></tr></thead><tbody id="tbodyDiscapacidad"></tbody></table></div></div></div>
        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>

<!-- ==================== MODAL EDITAR (solo los 11 campos de la tabla) ==================== -->
<div class="modal fade" id="modal-editar-discapacidad" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-xl modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar diagnóstico de discapacidad</h5>
                    <small class="opacity-75" id="editar_subtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="form-editar-discapacidad" novalidate>
                <input type="hidden" id="editar_id_discapacidad" name="id_discapacidad">

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
                        <div class="col-md-6">
                            <label for="edit_tipo_discapacidad" class="form-label">Tipo de discapacidad *</label>
                            <select id="edit_tipo_discapacidad" name="tipo_discapacidad" class="form-select" required>
                                <option value="" selected disabled>Seleccione un tipo</option>
                                <option value="Física">Física</option>
                                <option value="Sensorial">Sensorial</option>
                                <option value="Intelectual">Intelectual</option>
                                <option value="Múltiple">Múltiple</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div id="edit_tipo_discapacidadError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_disc_especifica" class="form-label">Discapacidad específica</label>
                            <input type="text" id="edit_disc_especifica" name="disc_especifica" class="form-control" maxlength="200">
                            <div id="edit_disc_especificaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-12">
                            <label for="edit_diagnostico" class="form-label">Diagnóstico *</label>
                            <input type="text" id="edit_diagnostico" name="diagnostico" class="form-control" maxlength="255" required>
                            <div id="edit_diagnosticoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_grado" class="form-label">Grado *</label>
                            <select id="edit_grado" name="grado" class="form-select" required>
                                <option value="" selected disabled>Seleccione el grado</option>
                                <option value="Leve">Leve</option>
                                <option value="Moderado">Moderado</option>
                                <option value="Grave">Grave</option>
                            </select>
                            <div id="edit_gradoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_medicamentos" class="form-label">Medicamentos actuales</label>
                            <textarea id="edit_medicamentos" name="medicamentos" class="form-control" rows="2" maxlength="255"></textarea>
                            <div id="edit_medicamentosError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_habilidades_funcionales" class="form-label">Habilidades funcionales *</label>
                            <textarea id="edit_habilidades_funcionales" name="habilidades_funcionales" class="form-control" rows="3" maxlength="255" required></textarea>
                            <div id="edit_habilidades_funcionalesError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_requiere_asistencia" class="form-label">¿Requiere asistencia personal?</label>
                            <select id="edit_requiere_asistencia" name="requiere_asistencia" class="form-select">
                                <option value="" selected>Seleccione</option>
                                <option value="Si">Sí</option>
                                <option value="No">No</option>
                            </select>
                            <div id="edit_requiere_asistenciaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_dispositivo_asistencia" class="form-label">Dispositivo de asistencia</label>
                            <input type="text" id="edit_dispositivo_asistencia" name="dispositivo_asistencia" class="form-control" maxlength="255">
                            <div id="edit_dispositivo_asistenciaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_carnet_discapacidad" class="form-label">Número de carnet</label>
                            <input type="text" id="edit_carnet_discapacidad" name="carnet_discapacidad" class="form-control" maxlength="20">
                            <div id="edit_carnet_discapacidadError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_observaciones" class="form-label">Observaciones *</label>
                            <textarea id="edit_observaciones" name="observaciones" class="form-control" rows="4" required></textarea>
                            <div id="edit_observacionesError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_recomendaciones" class="form-label">Recomendaciones</label>
                            <textarea id="edit_recomendaciones" name="recomendaciones" class="form-control" rows="4"></textarea>
                            <div id="edit_recomendacionesError" class="form-text text-danger"></div>
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
<script src="<?= BASE_URL ?>dist/js/core/documentos.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/consultar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/editar.js" defer></script>
</body></html>
