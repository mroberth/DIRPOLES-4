<?php
// app/Views/mobiliario/consultar.php
// Consulta del módulo Mobiliario (id_modulo 12): TRES pestañas con su
// DataTable (Mobiliario, Equipos, Fichas) + modal de reubicación, modal de
// baja, modal de historial y un modal de edición por sub-flujo.
// Los ids van prefijados por formulario (em_, ee_, ef_, ru_) para que los
// mensajes de error de validación (campo.id + 'Error') no colisionen.
$titulo = 'Consultar Mobiliario';
include BASE_PATH . '/app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Mobiliario y Equipos</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <button type="button" id="btn-historial" class="btn btn-sm btn-outline-dark shadow-sm me-2">
                                <i class="fas fa-clock-rotate-left me-1"></i> Historial
                            </button>
                            <a href="<?= BASE_URL ?>mobiliario/crear" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo registro
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include BASE_PATH . '/app/Views/mobiliario/components/stats.php'; ?>

                    <!-- ============ Pestañas de consulta ============ -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <ul class="nav nav-tabs card-header-tabs" id="mobiliarioConsultarTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold text-primary" id="ctabMobiliario"
                                            data-bs-toggle="tab" data-bs-target="#cpanelMobiliario" type="button"
                                            role="tab" aria-controls="cpanelMobiliario" aria-selected="true">
                                        <i class="fas fa-chair me-2"></i>Mobiliario
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-success" id="ctabEquipos"
                                            data-bs-toggle="tab" data-bs-target="#cpanelEquipos" type="button"
                                            role="tab" aria-controls="cpanelEquipos" aria-selected="false">
                                        <i class="fas fa-laptop me-2"></i>Equipos
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-info" id="ctabFichas"
                                            data-bs-toggle="tab" data-bs-target="#cpanelFichas" type="button"
                                            role="tab" aria-controls="cpanelFichas" aria-selected="false">
                                        <i class="fas fa-file-signature me-2"></i>Fichas técnicas
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="mobiliarioConsultarTabsContent">

                                <!-- ============ MOBILIARIO ============ -->
                                <div class="tab-pane fade show active py-3" id="cpanelMobiliario"
                                     role="tabpanel" aria-labelledby="ctabMobiliario">
                                    <div class="table-responsive">
                                        <table id="tablaMobiliario" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Ubicación</th>
                                                    <th class="text-center">Cantidad</th>
                                                    <th class="text-center">Disponible</th>
                                                    <th class="text-center">Estado</th>
                                                    <th class="text-center">Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyMobiliario"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ============ EQUIPOS ============ -->
                                <div class="tab-pane fade py-3" id="cpanelEquipos"
                                     role="tabpanel" aria-labelledby="ctabEquipos">
                                    <div class="table-responsive">
                                        <table id="tablaEquipos" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Serial</th>
                                                    <th>Marca / Modelo</th>
                                                    <th>Ubicación</th>
                                                    <th class="text-center">Estado</th>
                                                    <th>Ficha activa</th>
                                                    <th class="text-center">Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyEquipos"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- ============ FICHAS ============ -->
                                <div class="tab-pane fade py-3" id="cpanelFichas"
                                     role="tabpanel" aria-labelledby="ctabFichas">
                                    <div class="table-responsive">
                                        <table id="tablaFichas" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ficha</th>
                                                    <th>Servicio</th>
                                                    <th>Responsable</th>
                                                    <th class="text-center">Mobiliario</th>
                                                    <th class="text-center">Equipos</th>
                                                    <th>Creada</th>
                                                    <th class="text-center">Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyFichas"></tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- ==================== MODAL EDITAR MOBILIARIO ==================== -->
    <div class="modal fade" id="modalEditarMobiliario" tabindex="-1" aria-labelledby="modalEditarMobiliarioLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-chair text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarMobiliarioLabel">Editar Mobiliario</h5>
                            <small class="opacity-75" id="mobiliarioCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar-mobiliario" novalidate>
                    <input type="hidden" name="em_id_mobiliario" id="em_id_mobiliario">

                    <div class="modal-body p-0">
                        <div class="card border-0 rounded-0 bg-light">
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-lg-6 border-end">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="fas fa-chair me-2"></i> Datos del mobiliario
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Tipo de mobiliario *</label>
                                            <select name="em_id_tipo_mobiliario" id="em_id_tipo_mobiliario"
                                                    class="form-select select2" data-placeholder="Seleccione el tipo…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="em_id_tipo_mobiliarioError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Ubicación (servicio) *</label>
                                            <select name="em_id_servicios" id="em_id_servicios"
                                                    class="form-select select2" data-placeholder="Seleccione la ubicación…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="em_id_serviciosError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label text-muted small mb-1">Cantidad *</label>
                                                <input type="number" name="em_cantidad" id="em_cantidad" class="form-control"
                                                       min="1" step="1" inputmode="numeric" required>
                                                <div id="em_cantidadError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small mb-1">Estado *</label>
                                                <select name="em_estado" id="em_estado" class="form-select" required>
                                                    <option value="Nuevo">Nuevo</option>
                                                    <option value="Bueno">Bueno</option>
                                                    <option value="Regular">Regular</option>
                                                    <option value="Malo">Malo</option>
                                                    <option value="En reparación">En reparación</option>
                                                </select>
                                                <div id="em_estadoError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="fas fa-pen me-2"></i> Detalles
                                        </h6>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Marca</label>
                                                <input type="text" name="em_marca" id="em_marca" class="form-control" maxlength="100">
                                                <div id="em_marcaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Modelo</label>
                                                <input type="text" name="em_modelo" id="em_modelo" class="form-control" maxlength="100">
                                                <div id="em_modeloError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Color</label>
                                                <input type="text" name="em_color" id="em_color" class="form-control" maxlength="50">
                                                <div id="em_colorError" class="form-text text-danger"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Fecha de adquisición</label>
                                            <input type="date" name="em_fecha_adquisicion" id="em_fecha_adquisicion" class="form-control" max="<?= date('Y-m-d') ?>">
                                            <div id="em_fecha_adquisicionError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Descripción</label>
                                            <textarea name="em_descripcion" id="em_descripcion" class="form-control" rows="2" maxlength="500"></textarea>
                                            <div id="em_descripcionError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-1">
                                            <label class="form-label text-muted small mb-1">Observaciones</label>
                                            <textarea name="em_observaciones" id="em_observaciones" class="form-control" rows="2" maxlength="500"></textarea>
                                            <div id="em_observacionesError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            El estatus no se edita: la baja se registra con el botón
                                            <strong>Baja</strong> de la tabla.
                                        </div>
                                    </div>
                                </div>
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

    <!-- ==================== MODAL EDITAR EQUIPO ==================== -->
    <div class="modal fade" id="modalEditarEquipo" tabindex="-1" aria-labelledby="modalEditarEquipoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-success text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-laptop text-success"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarEquipoLabel">Editar Equipo</h5>
                            <small class="opacity-75" id="equipoCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar-equipo" novalidate>
                    <input type="hidden" name="ee_id_equipo" id="ee_id_equipo">

                    <div class="modal-body p-0">
                        <div class="card border-0 rounded-0 bg-light">
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-lg-6 border-end">
                                        <h6 class="fw-bold text-success mb-3">
                                            <i class="fas fa-laptop me-2"></i> Datos del equipo
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Tipo de equipo *</label>
                                            <select name="ee_id_tipo_equipo" id="ee_id_tipo_equipo"
                                                    class="form-select select2" data-placeholder="Seleccione el tipo…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="ee_id_tipo_equipoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Ubicación (servicio) *</label>
                                            <select name="ee_id_servicios" id="ee_id_servicios"
                                                    class="form-select select2" data-placeholder="Seleccione la ubicación…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="ee_id_serviciosError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label text-muted small mb-1">Serial *</label>
                                                <input type="text" name="ee_serial" id="ee_serial" class="form-control" maxlength="100" required>
                                                <div id="ee_serialError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small mb-1">Estado *</label>
                                                <select name="ee_estado" id="ee_estado" class="form-select" required>
                                                    <option value="Nuevo">Nuevo</option>
                                                    <option value="Bueno">Bueno</option>
                                                    <option value="Regular">Regular</option>
                                                    <option value="Malo">Malo</option>
                                                    <option value="En reparación">En reparación</option>
                                                </select>
                                                <div id="ee_estadoError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6 class="fw-bold text-success mb-3">
                                            <i class="fas fa-pen me-2"></i> Detalles
                                        </h6>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Marca</label>
                                                <input type="text" name="ee_marca" id="ee_marca" class="form-control" maxlength="100">
                                                <div id="ee_marcaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Modelo</label>
                                                <input type="text" name="ee_modelo" id="ee_modelo" class="form-control" maxlength="100">
                                                <div id="ee_modeloError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label text-muted small mb-1">Color</label>
                                                <input type="text" name="ee_color" id="ee_color" class="form-control" maxlength="50">
                                                <div id="ee_colorError" class="form-text text-danger"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Fecha de adquisición</label>
                                            <input type="date" name="ee_fecha_adquisicion" id="ee_fecha_adquisicion" class="form-control" max="<?= date('Y-m-d') ?>">
                                            <div id="ee_fecha_adquisicionError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">Descripción</label>
                                            <textarea name="ee_descripcion" id="ee_descripcion" class="form-control" rows="2" maxlength="500"></textarea>
                                            <div id="ee_descripcionError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-1">
                                            <label class="form-label text-muted small mb-1">Observaciones</label>
                                            <textarea name="ee_observaciones" id="ee_observaciones" class="form-control" rows="2" maxlength="500"></textarea>
                                            <div id="ee_observacionesError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            El serial es único en todo el sistema; el estatus no se edita.
                                        </div>
                                    </div>
                                </div>
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

    <!-- ==================== MODAL EDITAR FICHA ==================== -->
    <div class="modal fade" id="modalEditarFicha" tabindex="-1" aria-labelledby="modalEditarFichaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-info text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-file-signature text-info"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarFichaLabel">Editar Ficha Técnica</h5>
                            <small class="opacity-75" id="fichaCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar-ficha" novalidate>
                    <input type="hidden" name="ef_id_ficha" id="ef_id_ficha">

                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Nombre de la ficha *</label>
                                <input type="text" name="ef_nombre_ficha" id="ef_nombre_ficha" class="form-control" maxlength="100" required>
                                <div id="ef_nombre_fichaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Servicio *</label>
                                <select name="ef_id_servicio" id="ef_id_servicio"
                                        class="form-select select2" data-placeholder="Seleccione el servicio…" required>
                                    <option value="">Cargando…</option>
                                </select>
                                <div id="ef_id_servicioError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small mb-1">Empleado responsable *</label>
                                <select name="ef_id_empleado_responsable" id="ef_id_empleado_responsable"
                                        class="form-select select2" data-placeholder="Seleccione el empleado…" required>
                                    <option value="">Cargando…</option>
                                </select>
                                <div id="ef_id_empleado_responsableError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small mb-1">Descripción</label>
                                <textarea name="ef_descripcion" id="ef_descripcion" class="form-control" rows="2" maxlength="500"></textarea>
                                <div id="ef_descripcionError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <div class="card border-0 mb-2">
                            <div class="card-body pt-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-chair me-2"></i>Mobiliario de la ficha
                                    </h6>
                                    <button type="button" id="btnAgregarFilaMobiliario" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus me-1"></i> Agregar fila
                                    </button>
                                </div>
                                <div id="contenedorDetallesMobiliario"></div>
                            </div>
                        </div>

                        <div class="card border-0 mb-2">
                            <div class="card-body pt-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold text-success">
                                        <i class="fas fa-laptop me-2"></i>Equipos de la ficha
                                    </h6>
                                    <button type="button" id="btnAgregarFilaEquipo" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-plus me-1"></i> Agregar fila
                                    </button>
                                </div>
                                <div id="contenedorDetallesEquipo"></div>
                            </div>
                        </div>

                        <div id="detallesError" class="form-text text-danger"></div>
                    </div>

                    <div class="modal-footer py-3">
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

    <!-- ==================== MODAL REUBICAR ==================== -->
    <div class="modal fade" id="modalReubicar" tabindex="-1" aria-labelledby="modalReubicarLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-warning text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-arrows-up-down-left-right text-warning"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalReubicarLabel">Reubicar ítem</h5>
                            <small class="opacity-75" id="reubicarCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-reubicar" novalidate>
                    <input type="hidden" name="ru_tipo_item" id="ru_tipo_item">
                    <input type="hidden" name="ru_id_item" id="ru_id_item">

                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Ítem</label>
                            <input type="text" class="form-control" id="ru_item" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Ubicación actual</label>
                            <input type="text" class="form-control" id="ru_desde" readonly>
                        </div>

                        <div class="mb-2">
                            <label class="form-label text-muted small mb-1">
                                <i class="fas fa-location-dot text-warning me-1"></i> Nueva ubicación *
                            </label>
                            <select name="ru_id_servicios" id="ru_id_servicios"
                                    class="form-select select2" data-placeholder="Seleccione la nueva ubicación…" required>
                                <option value="">Cargando…</option>
                            </select>
                            <div id="ru_id_serviciosError" class="form-text text-danger"></div>
                        </div>

                        <div class="form-text text-muted">
                            <i class="fas fa-circle-info me-1"></i>
                            El movimiento queda registrado en el historial con la ubicación anterior y la nueva.
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-arrows-up-down-left-right me-1"></i> Reubicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL HISTORIAL (kardex) ==================== -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-clock-rotate-left text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalHistorialLabel">Historial de movimientos</h5>
                            <small class="opacity-75">Altas, reubicaciones, modificaciones y bajas</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table id="tablaHistorial" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Ítem</th>
                                    <th>Movimiento</th>
                                    <th>Responsable</th>
                                    <th>De</th>
                                    <th>A</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorial"></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>

    <!-- Módulo Mobiliario: stats + validaciones + edición/reubicación + tablas -->
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/consultar.js" defer></script>
</body>

</html>
