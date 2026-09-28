<?php
$titulo = 'Consultar trabajo social';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top"><div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Trabajo social</h1>
                <div class="d-flex gap-2">
                    <button id="btn-recargar-trabajo-social" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-rotate-right me-1"></i>Recargar
                    </button>
                    <a href="<?= BASE_URL ?>trabajo-social/crear" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Nuevo registro
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/trabajo-social/components/stats.php'; ?>
            </div>

            <!-- ==================== PESTAÑAS POR SUB-REGISTRO ==================== -->
            <ul class="nav nav-tabs mb-0" id="tsConsultaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-success" id="cons-becas-tab"
                            data-bs-toggle="tab" data-bs-target="#cons-becas" type="button"
                            role="tab" aria-controls="cons-becas" aria-selected="true">
                        <i class="fas fa-graduation-cap me-1"></i>Becas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-warning" id="cons-exoneracion-tab"
                            data-bs-toggle="tab" data-bs-target="#cons-exoneracion" type="button"
                            role="tab" aria-controls="cons-exoneracion" aria-selected="false">
                        <i class="fas fa-file-contract me-1"></i>Exoneraciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-info" id="cons-fames-tab"
                            data-bs-toggle="tab" data-bs-target="#cons-fames" type="button"
                            role="tab" aria-controls="cons-fames" aria-selected="false">
                        <i class="fas fa-hand-holding-heart me-1"></i>FAMES
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-primary" id="cons-embarazadas-tab"
                            data-bs-toggle="tab" data-bs-target="#cons-embarazadas" type="button"
                            role="tab" aria-controls="cons-embarazadas" aria-selected="false">
                        <i class="fas fa-baby me-1"></i>Embarazadas
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom shadow-sm bg-white p-3"
                 id="tsConsultaContenido">

                <!-- BECAS -->
                <div class="tab-pane fade show active" id="cons-becas" role="tabpanel"
                     aria-labelledby="cons-becas-tab">
                    <div class="table-responsive">
                        <table id="tablaBecas" class="table table-bordered table-hover align-middle" width="100%">
                            <thead class="table-light"><tr>
                                <th>Fecha</th><th>Beneficiario</th><th>Banco</th>
                                <th>Cuenta BCV</th><th class="text-center">Acciones</th>
                            </tr></thead>
                            <tbody id="tbodyBecas"></tbody>
                        </table>
                    </div>
                </div>

                <!-- EXONERACIONES -->
                <div class="tab-pane fade" id="cons-exoneracion" role="tabpanel"
                     aria-labelledby="cons-exoneracion-tab">
                    <div class="table-responsive">
                        <table id="tablaExoneraciones" class="table table-bordered table-hover align-middle" width="100%">
                            <thead class="table-light"><tr>
                                <th>Fecha</th><th>Beneficiario</th><th>Motivo</th>
                                <th>Carnet</th><th class="text-center">Acciones</th>
                            </tr></thead>
                            <tbody id="tbodyExoneraciones"></tbody>
                        </table>
                    </div>
                </div>

                <!-- FAMES -->
                <div class="tab-pane fade" id="cons-fames" role="tabpanel"
                     aria-labelledby="cons-fames-tab">
                    <div class="table-responsive">
                        <table id="tablaFames" class="table table-bordered table-hover align-middle" width="100%">
                            <thead class="table-light"><tr>
                                <th>Fecha</th><th>Beneficiario</th><th>Patología</th>
                                <th>Tipo de ayuda</th><th class="text-center">Acciones</th>
                            </tr></thead>
                            <tbody id="tbodyFames"></tbody>
                        </table>
                    </div>
                </div>

                <!-- EMBARAZADAS -->
                <div class="tab-pane fade" id="cons-embarazadas" role="tabpanel"
                     aria-labelledby="cons-embarazadas-tab">
                    <div class="table-responsive">
                        <table id="tablaEmbarazadas" class="table table-bordered table-hover align-middle" width="100%">
                            <thead class="table-light"><tr>
                                <th>Fecha</th><th>Beneficiario</th><th>Patología</th>
                                <th>Semanas</th><th>Estado</th><th class="text-center">Acciones</th>
                            </tr></thead>
                            <tbody id="tbodyEmbarazadas"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>

<!-- ==================== MODAL EDITAR BECA (solo banco y cuenta) ==================== -->
<div class="modal fade" id="modal-editar-beca" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-success text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar beca</h5>
                    <small class="opacity-75" id="editarBecaSubtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-editar-beca" novalidate>
                <input type="hidden" id="editar_id_beca" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editarBecaBeneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editarBecaBeneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editarBecaEmpleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editarBecaEmpleado" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario, el empleado que atendió y la planilla no pueden modificarse
                        (integridad de auditoría).
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_tipo_banco" class="form-label">Tipo de Banco *</label>
                            <select id="edit_tipo_banco" name="tipo_banco"
                                    class="form-select select2"
                                    data-placeholder="Seleccione tipo de banco…">
                                <option value="" selected disabled>Seleccione tipo de banco</option>
                                <option value="0102">BANCO DE VENEZUELA</option>
                                <option value="0156">100% BANCO</option>
                                <option value="0172">BANCAMIGA BANCO MICROFINANCIERO C.A</option>
                                <option value="0114">BANCARIBE</option>
                                <option value="0171">BANCO ACTIVO</option>
                                <option value="0166">BANCO AGRICOLA DE VENEZUELA</option>
                                <option value="0175">BANCO DIGITAL DE LOS TRABAJADORES</option>
                                <option value="0128">BANCO CARONI</option>
                                <option value="0163">BANCO DEL TESORO</option>
                                <option value="0115">BANCO EXTERIOR</option>
                                <option value="0151">BANCO FONDO COMUN</option>
                                <option value="0173">BANCO INTERNACIONAL DE DESARROLLO</option>
                                <option value="0105">BANCO MERCANTIL</option>
                                <option value="0191">BANCO NACIONAL DE CREDITO</option>
                                <option value="0138">BANCO PLAZA</option>
                                <option value="0137">BANCO SOFITASA</option>
                                <option value="0104">BANCO VENEZOLANO DE CREDITO</option>
                                <option value="0168">BANCRECER</option>
                                <option value="0134">BANESCO</option>
                                <option value="0177">BANFANB</option>
                                <option value="0146">BANGENTE</option>
                                <option value="0174">BANPLUS</option>
                                <option value="0108">BBVA PROVINCIAL</option>
                                <option value="0157">DELSUR BANCO UNIVERSAL</option>
                                <option value="0169">MI BANCO</option>
                                <option value="0178">N58 BANCO DIGITAL BANCO MICROFINANCIERO S.A</option>
                            </select>
                            <div id="edit_tipo_bancoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_cta_bcv" class="form-label">Cuenta BCV *</label>
                            <input type="text" id="edit_cta_bcv" name="cta_bcv"
                                   class="form-control" maxlength="16" inputmode="numeric"
                                   placeholder="Ej: 0021200000002121">
                            <div id="edit_cta_bcvError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Número de cuenta bancaria (16 dígitos).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDITAR EXONERACIÓN ==================== -->
<div class="modal fade" id="modal-editar-exoneracion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-warning text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar exoneración</h5>
                    <small class="opacity-75" id="editarExoneracionSubtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-editar-exoneracion" novalidate>
                <input type="hidden" id="editar_id_exoneracion" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editarExoneracionBeneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editarExoneracionBeneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editarExoneracionEmpleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editarExoneracionEmpleado" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario, el empleado que atendió y los PDFs (carta y estudio) no pueden
                        modificarse desde aquí (integridad de auditoría).
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_motivo" class="form-label">Motivo *</label>
                            <select id="edit_motivo" name="motivo" class="form-select">
                                <option value="" selected disabled>Seleccione motivo</option>
                                <option value="Inscripción">Inscripción</option>
                                <option value="Paquete de Grado">Paquete de Grado</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div id="edit_motivoError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_carnet_discapacidad" class="form-label">Carnet de Discapacidad *</label>
                            <input type="text" id="edit_carnet_discapacidad" name="carnet_discapacidad"
                                   class="form-control" maxlength="100" placeholder="Ej: D-0000000000">
                            <div id="edit_carnet_discapacidadError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-12" id="editOtroMotivoContainer" hidden>
                            <label for="edit_otro_motivo" class="form-label">Detalle del motivo *</label>
                            <textarea id="edit_otro_motivo" name="otro_motivo" class="form-control"
                                      rows="2" maxlength="100"
                                      placeholder="Describe el motivo de la exoneración…"></textarea>
                            <div id="edit_otro_motivoError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Obligatorio cuando el motivo es "Otro" (máx. 100 caracteres).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDITAR FAMES ==================== -->
<div class="modal fade" id="modal-editar-fames" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-info text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar FAMES</h5>
                    <small class="opacity-75" id="editarFamesSubtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-editar-fames" novalidate>
                <input type="hidden" id="editar_id_fames" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editarFamesBeneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" id="editarFamesBeneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editarFamesEmpleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editarFamesEmpleado" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario y el empleado que atendió no pueden modificarse (integridad de auditoría).
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_patologia_fames" class="form-label">Patología *</label>
                            <select id="edit_patologia_fames" name="id_patologia"
                                    class="form-select select2"
                                    data-placeholder="Seleccione una patología…">
                                <option value="" selected disabled>Seleccione una patología</option>
                            </select>
                            <div id="edit_patologia_famesError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Patología asociada al caso (no incluye "Sin patología").</small>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_tipo_ayuda" class="form-label">Tipo de Ayuda *</label>
                            <select id="edit_tipo_ayuda" name="tipo_ayuda" class="form-select">
                                <option value="" selected disabled>Seleccione tipo</option>
                                <option value="Económica">Económica</option>
                                <option value="Operaciones">Operaciones</option>
                                <option value="Exámenes">Exámenes</option>
                                <option value="Otros">Otros</option>
                            </select>
                            <div id="edit_tipo_ayudaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-12" id="editOtroTipoContainer" hidden>
                            <label for="edit_otro_tipo" class="form-label">Detalle del tipo de ayuda *</label>
                            <input type="text" id="edit_otro_tipo" name="otro_tipo" class="form-control"
                                   maxlength="100" placeholder="Describe el tipo de ayuda…">
                            <div id="edit_otro_tipoError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Obligatorio cuando el tipo es "Otros" (máx. 100 caracteres).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDITAR EMBARAZADAS ==================== -->
<div class="modal fade" id="modal-editar-embarazadas" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white py-3">
                <div>
                    <h5 class="modal-title mb-0 fw-bold">Editar gestión de embarazo</h5>
                    <small class="opacity-75" id="editarEmbarazadasSubtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-editar-embarazadas" novalidate>
                <input type="hidden" id="editar_id_embarazadas" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editarEmbarazadasBeneficiario" class="form-label text-muted small mb-1">Beneficiaria</label>
                            <input type="text" id="editarEmbarazadasBeneficiario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editarEmbarazadasEmpleado" class="form-label text-muted small mb-1">Empleado que atendió</label>
                            <input type="text" id="editarEmbarazadasEmpleado" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="alert alert-info small py-2 mt-3 mb-3">
                        <i class="fas fa-lock me-1"></i>
                        El beneficiario y el empleado que atendió no pueden modificarse (integridad de auditoría).
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_patologia_embarazada" class="form-label">Patología (Embarazo) *</label>
                            <select id="edit_patologia_embarazada" name="id_patologia"
                                    class="form-select select2"
                                    data-placeholder="Seleccione patología de embarazo…">
                                <option value="" selected disabled>Seleccione patología de embarazo</option>
                            </select>
                            <div id="edit_patologia_embarazadaError" class="form-text text-danger"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_semanas_gest" class="form-label">Semanas de Gestación *</label>
                            <input type="number" id="edit_semanas_gest" name="semanas_gest"
                                   class="form-control" min="1" max="45" placeholder="Ej: 28">
                            <div id="edit_semanas_gestError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Entre 1 y 45 semanas.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_codigo_patria" class="form-label">Código Patria</label>
                            <input type="text" id="edit_codigo_patria" name="codigo_patria"
                                   class="form-control" inputmode="numeric" maxlength="10"
                                   placeholder="Código del carnet de la Patria">
                            <div id="edit_codigo_patriaError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Opcional (solo números, máx. 10 dígitos).</small>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_serial_patria" class="form-label">Serial Patria</label>
                            <input type="text" id="edit_serial_patria" name="serial_patria"
                                   class="form-control" inputmode="numeric" maxlength="10"
                                   placeholder="Serial del carnet de la Patria">
                            <div id="edit_serial_patriaError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Opcional (solo números, máx. 10 dígitos).</small>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_estado" class="form-label">Estado *</label>
                            <select id="edit_estado" name="estado" class="form-select">
                                <option value="" selected disabled>Seleccione estado</option>
                                <option value="En Proceso">En Proceso</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Rechazado">Rechazado</option>
                            </select>
                            <div id="edit_estadoError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">Solo puede haber una gestión "En Proceso" por beneficiaria.</small>
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
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/consultar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/editar.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/stats.js" defer></script>
</body></html>
