<?php
$titulo = 'Trabajo Social';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
<div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestión de Trabajo Social</h1>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-estudio" class="btn btn-sm btn-warning shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#modalSeleccionarExoneracion">
                        <i class="fas fa-file-invoice-dollar me-1"></i>Registrar Estudio Socio-Económico
                    </button>
                    <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-circle-question me-1"></i>Ayuda
                    </button>
                    <a href="<?= BASE_URL ?>trabajo-social/consultar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Consultar
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/trabajo-social/components/stats.php'; ?>
            </div>

            <!-- ============ Beneficiario global (compartido por las pestañas) ============ -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-injured me-2"></i>Datos del Beneficiario
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <label for="id_beneficiario" class="form-label">Beneficiario *</label>
                            <select id="id_beneficiario" class="form-select select2"
                                    data-placeholder="Seleccione un beneficiario…">
                                <option value="">Cargando…</option>
                            </select>
                            <div id="id_beneficiarioError" class="form-text text-danger"></div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                El beneficiario seleccionado se aplica a todos los formularios de esta página.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ Pestañas de registros ============ -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="tsTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold text-success" id="becas-tab"
                                    data-bs-toggle="tab" data-bs-target="#becas" type="button"
                                    role="tab" aria-controls="becas" aria-selected="true">
                                <i class="fas fa-graduation-cap me-2"></i>Registro de Becas
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-warning" id="exoneracion-tab"
                                    data-bs-toggle="tab" data-bs-target="#exoneracion" type="button"
                                    role="tab" aria-controls="exoneracion" aria-selected="false">
                                <i class="fas fa-file-contract me-2"></i>Registro de Exoneración
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-info" id="fames-tab"
                                    data-bs-toggle="tab" data-bs-target="#fames" type="button"
                                    role="tab" aria-controls="fames" aria-selected="false">
                                <i class="fas fa-hand-holding-heart me-2"></i>Registro de FAMES
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-primary" id="embarazadas-tab"
                                    data-bs-toggle="tab" data-bs-target="#embarazadas" type="button"
                                    role="tab" aria-controls="embarazadas" aria-selected="false">
                                <i class="fas fa-baby me-2"></i>Registro de Embarazadas
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="tsTabsContent">

                        <!-- BECAS -->
                        <div class="tab-pane fade show active py-3" id="becas" role="tabpanel"
                             aria-labelledby="becas-tab">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <form id="form-becas" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="tipo_banco" class="form-label">Tipo de Banco *</label>
                                                <select id="tipo_banco" name="tipo_banco"
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
                                                <div id="tipo_bancoError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Código del banco receptora de la beca.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="cta_bcv" class="form-label">Cuenta BCV *</label>
                                                <input type="text" id="cta_bcv" name="cta_bcv"
                                                       class="form-control" maxlength="16" inputmode="numeric"
                                                       placeholder="Ej: 0021200000002121">
                                                <div id="cta_bcvError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Número de cuenta bancaria (16 dígitos).</small>
                                            </div>

                                            <div class="col-12">
                                                <label for="planilla" class="form-label">Planilla de Inscripción *</label>
                                                <input type="file" id="planilla" name="planilla"
                                                       class="form-control" accept="application/pdf">
                                                <div id="planillaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Archivo PDF obligatorio.</small>
                                            </div>
                                        </div>
                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="reset" class="btn btn-outline-secondary">
                                                    <i class="fas fa-eraser me-1"></i>Limpiar
                                                </button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-graduation-cap me-1"></i>Registrar Beca
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <!-- EXONERACIÓN -->
                        <div class="tab-pane fade py-3" id="exoneracion" role="tabpanel"
                             aria-labelledby="exoneracion-tab">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <form id="form-exoneracion" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="motivo" class="form-label">Motivo *</label>
                                                <select id="motivo" name="motivo" class="form-select" required>
                                                    <option value="" selected disabled>Seleccione motivo</option>
                                                    <option value="Inscripción">Inscripción</option>
                                                    <option value="Paquete de Grado">Paquete de Grado</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                                <div id="motivoError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Motivo de la exoneración.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="carnet_discapacidad" class="form-label">Carnet de Discapacidad *</label>
                                                <input type="text" id="carnet_discapacidad" name="carnet_discapacidad"
                                                       class="form-control" maxlength="100"
                                                       placeholder="Ej: D-0000000000" required>
                                                <div id="carnet_discapacidadError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Número del carnet (obligatorio).</small>
                                            </div>

                                            <div class="col-12" id="otroMotivoContainer" hidden>
                                                <label for="otro_motivo" class="form-label">Detalle del motivo *</label>
                                                <textarea id="otro_motivo" name="otro_motivo" class="form-control"
                                                          rows="2" maxlength="100"
                                                          placeholder="Describe el motivo de la exoneración…"></textarea>
                                                <div id="otro_motivoError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Obligatorio cuando el motivo es "Otro" (máx. 100 caracteres).</small>
                                            </div>

                                            <div class="col-12">
                                                <label for="carta" class="form-label">Carta de Exoneración *</label>
                                                <input type="file" id="carta" name="carta"
                                                       class="form-control" accept="application/pdf" required>
                                                <div id="cartaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Archivo PDF obligatorio.</small>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <button type="reset" class="btn btn-outline-secondary">
                                                <i class="fas fa-eraser me-1"></i>Limpiar
                                            </button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-file-contract me-1"></i>Registrar Exoneración
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- FAMES -->
                        <div class="tab-pane fade py-3" id="fames" role="tabpanel"
                             aria-labelledby="fames-tab">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <form id="form-fames" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="patologia_fames" class="form-label">Patología *</label>
                                                <select id="patologia_fames" name="id_patologia"
                                                        class="form-select select2"
                                                        data-placeholder="Seleccione una patología…">
                                                    <option value="" selected disabled>Seleccione una patología</option>
                                                </select>
                                                <div id="patologia_famesError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Patología asociada al caso (no incluye "Sin patología").</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="tipo_ayuda" class="form-label">Tipo de Ayuda *</label>
                                                <select id="tipo_ayuda" name="tipo_ayuda" class="form-select">
                                                    <option value="" selected disabled>Seleccione tipo</option>
                                                    <option value="Económica">Económica</option>
                                                    <option value="Operaciones">Operaciones</option>
                                                    <option value="Exámenes">Exámenes</option>
                                                    <option value="Otros">Otros</option>
                                                </select>
                                                <div id="tipo_ayudaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Tipo de ayuda otorgada.</small>
                                            </div>

                                            <div class="col-12" id="otroTipoContainer" hidden>
                                                <label for="otro_tipo" class="form-label">Detalle del tipo de ayuda *</label>
                                                <input type="text" id="otro_tipo" name="otro_tipo" class="form-control"
                                                       maxlength="100" placeholder="Describe el tipo de ayuda…">
                                                <div id="otro_tipoError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Obligatorio cuando el tipo es "Otros" (máx. 100 caracteres).</small>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <button type="reset" class="btn btn-outline-secondary">
                                                <i class="fas fa-eraser me-1"></i>Limpiar
                                            </button>
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-hand-holding-heart me-1"></i>Registrar FAMES
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- EMBARAZADAS -->
                        <div class="tab-pane fade py-3" id="embarazadas" role="tabpanel"
                             aria-labelledby="embarazadas-tab">
                            <div class="row justify-content-center">
                                <div class="col-lg-8">
                                    <form id="form-embarazadas" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="patologia_embarazada" class="form-label">Patología (Embarazo) *</label>
                                                <select id="patologia_embarazada" name="id_patologia"
                                                        class="form-select select2"
                                                        data-placeholder="Seleccione patología de embarazo…">
                                                    <option value="" selected disabled>Seleccione patología de embarazo</option>
                                                </select>
                                                <div id="patologia_embarazadaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Patología asociada a la gestación.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="semanas_gest" class="form-label">Semanas de Gestación *</label>
                                                <input type="number" id="semanas_gest" name="semanas_gest"
                                                       class="form-control" min="1" max="45"
                                                       placeholder="Ej: 28">
                                                <div id="semanas_gestError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Entre 1 y 45 semanas.</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="codigo_patria" class="form-label">Código Patria</label>
                                                <input type="text" id="codigo_patria" name="codigo_patria"
                                                       class="form-control" inputmode="numeric" maxlength="10"
                                                       placeholder="Código del carnet de la Patria">
                                                <div id="codigo_patriaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Opcional (solo números, máx. 10 dígitos).</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="serial_patria" class="form-label">Serial Patria</label>
                                                <input type="text" id="serial_patria" name="serial_patria"
                                                       class="form-control" inputmode="numeric" maxlength="10"
                                                       placeholder="Serial del carnet de la Patria">
                                                <div id="serial_patriaError" class="form-text text-danger"></div>
                                                <small class="form-text text-muted">Opcional (solo números, máx. 10 dígitos).</small>
                                            </div>
                                        </div>

                                        <div class="alert alert-info py-2 small mt-3 mb-0">
                                            <i class="fas fa-venus me-1"></i>
                                            La gestión de embarazo aplica solo a beneficiarias de género femenino y
                                            nace con estado "En Proceso". Solo puede haber una gestión en proceso
                                            por beneficiaria.
                                        </div>

                                        <div class="d-flex justify-content-end gap-2 mt-4">
                                            <button type="reset" class="btn btn-outline-secondary">
                                                <i class="fas fa-eraser me-1"></i>Limpiar
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-baby me-1"></i>Registrar Gestión
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        </div>

                    </div>
            </div>

            <!-- ============ Offcanvas: Estudio Socioeconómico ============ -->
            <?php include BASE_PATH . '/app/Views/trabajo-social/components/estudio-socioeconomico.php'; ?>

            <!-- ============ Modal: exoneraciones pendientes de estudio ============ -->
            <div class="modal fade" id="modalSeleccionarExoneracion" tabindex="-1"
                 aria-labelledby="modalExoneracionesLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title" id="modalExoneracionesLabel">
                                <i class="fas fa-tasks me-2"></i>Exoneraciones Pendientes por Estudio
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Exoneraciones registradas que todavía no tienen estudio socioeconómico.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-center" id="tablaExoneracionesPendientes" width="100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Beneficiario</th>
                                            <th>Cédula</th>
                                            <th>Motivo</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>
<?php include BASE_PATH . '/app/Views/template/script.php'; ?>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/crear.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/pendientes.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/validaciones-estudio.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/trabajo-social/estudio.js" defer></script>
</body></html>
