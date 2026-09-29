<?php
// app/Views/mobiliario/crear.php
// Hub de creación del módulo Mobiliario (id_modulo 12):
// TRES pestañas con su propio formulario (Mobiliario, Equipo, Ficha técnica).
// Los ids van prefijados por formulario (eq_ y f_) para que los mensajes de
// error de validación (campo.id + 'Error') no colisionen en el documento.
$titulo = 'Mobiliario';
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
                        <h1 class="h3 mb-0 text-gray-800">Registrar Mobiliario</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>mobiliario/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include BASE_PATH . '/app/Views/mobiliario/components/stats.php'; ?>

                    <!-- ============ Pestañas de creación ============ -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <ul class="nav nav-tabs card-header-tabs" id="mobiliarioTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active fw-bold text-primary" id="tabMobiliario"
                                            data-bs-toggle="tab" data-bs-target="#panelMobiliario" type="button"
                                            role="tab" aria-controls="panelMobiliario" aria-selected="true">
                                        <i class="fas fa-chair me-2"></i>Mobiliario
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-success" id="tabEquipo"
                                            data-bs-toggle="tab" data-bs-target="#panelEquipo" type="button"
                                            role="tab" aria-controls="panelEquipo" aria-selected="false">
                                        <i class="fas fa-laptop me-2"></i>Equipo
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link fw-bold text-info" id="tabFicha"
                                            data-bs-toggle="tab" data-bs-target="#panelFicha" type="button"
                                            role="tab" aria-controls="panelFicha" aria-selected="false">
                                        <i class="fas fa-file-signature me-2"></i>Ficha técnica
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="mobiliarioTabsContent">

                                <!-- ==================== MOBILIARIO ==================== -->
                                <div class="tab-pane fade show active py-3" id="panelMobiliario"
                                     role="tabpanel" aria-labelledby="tabMobiliario">
                                    <form id="form-mobiliario" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo de mobiliario *</label>
                                                <select name="id_tipo_mobiliario" id="id_tipo_mobiliario"
                                                        class="form-select select2" data-placeholder="Seleccione el tipo…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="id_tipo_mobiliarioError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ubicación (servicio) *</label>
                                                <select name="id_servicios" id="id_servicios"
                                                        class="form-select select2" data-placeholder="Seleccione la ubicación…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="id_serviciosError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Cantidad *</label>
                                                <input type="number" name="cantidad" id="cantidad" class="form-control"
                                                       min="1" step="1" inputmode="numeric" value="1" required>
                                                <div id="cantidadError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Estado *</label>
                                                <select name="estado" id="estado" class="form-select" required>
                                                    <option value="Nuevo">Nuevo</option>
                                                    <option value="Bueno" selected>Bueno</option>
                                                    <option value="Regular">Regular</option>
                                                    <option value="Malo">Malo</option>
                                                    <option value="En reparación">En reparación</option>
                                                </select>
                                                <div id="estadoError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Marca</label>
                                                <input type="text" name="marca" id="marca" class="form-control"
                                                       maxlength="100" placeholder="Ej: Ergo">
                                                <div id="marcaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Modelo</label>
                                                <input type="text" name="modelo" id="modelo" class="form-control"
                                                       maxlength="100" placeholder="Ej: T-200">
                                                <div id="modeloError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Color</label>
                                                <input type="text" name="color" id="color" class="form-control"
                                                       maxlength="50" placeholder="Ej: Negro">
                                                <div id="colorError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Fecha de adquisición</label>
                                                <input type="date" name="fecha_adquisicion" id="fecha_adquisicion"
                                                       class="form-control" max="<?= date('Y-m-d') ?>">
                                                <div id="fecha_adquisicionError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Descripción</label>
                                                <textarea name="descripcion" id="descripcion" class="form-control"
                                                          rows="2" maxlength="500"
                                                          placeholder="Ej: Escritorio con cajonera"></textarea>
                                                <div id="descripcionError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Observaciones</label>
                                                <textarea name="observaciones" id="observaciones" class="form-control"
                                                          rows="2" maxlength="500"
                                                          placeholder="Ej: Traído por donación"></textarea>
                                                <div id="observacionesError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-text text-muted">
                                                    <i class="fas fa-circle-info me-1"></i>
                                                    El estatus (Activo/Inactivo) no se elige aquí: nace
                                                    <strong>Activo</strong> y la baja se registra desde Consultar.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Guardar mobiliario
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- ==================== EQUIPO ==================== -->
                                <div class="tab-pane fade py-3" id="panelEquipo"
                                     role="tabpanel" aria-labelledby="tabEquipo">
                                    <form id="form-equipo" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo de equipo *</label>
                                                <select name="eq_id_tipo_equipo" id="eq_id_tipo_equipo"
                                                        class="form-select select2" data-placeholder="Seleccione el tipo…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="eq_id_tipo_equipoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ubicación (servicio) *</label>
                                                <select name="eq_id_servicios" id="eq_id_servicios"
                                                        class="form-select select2" data-placeholder="Seleccione la ubicación…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="eq_id_serviciosError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Serial *</label>
                                                <input type="text" name="eq_serial" id="eq_serial" class="form-control"
                                                       maxlength="100" placeholder="Ej: SN-2026-001" required>
                                                <div id="eq_serialError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Estado *</label>
                                                <select name="eq_estado" id="eq_estado" class="form-select" required>
                                                    <option value="Nuevo">Nuevo</option>
                                                    <option value="Bueno" selected>Bueno</option>
                                                    <option value="Regular">Regular</option>
                                                    <option value="Malo">Malo</option>
                                                    <option value="En reparación">En reparación</option>
                                                </select>
                                                <div id="eq_estadoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Marca</label>
                                                <input type="text" name="eq_marca" id="eq_marca" class="form-control"
                                                       maxlength="100" placeholder="Ej: HP">
                                                <div id="eq_marcaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Modelo</label>
                                                <input type="text" name="eq_modelo" id="eq_modelo" class="form-control"
                                                       maxlength="100" placeholder="Ej: ProBook 450">
                                                <div id="eq_modeloError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Color</label>
                                                <input type="text" name="eq_color" id="eq_color" class="form-control"
                                                       maxlength="50" placeholder="Ej: Plata">
                                                <div id="eq_colorError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Fecha de adquisición</label>
                                                <input type="date" name="eq_fecha_adquisicion" id="eq_fecha_adquisicion"
                                                       class="form-control" max="<?= date('Y-m-d') ?>">
                                                <div id="eq_fecha_adquisicionError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Descripción</label>
                                                <textarea name="eq_descripcion" id="eq_descripcion" class="form-control"
                                                          rows="2" maxlength="500"
                                                          placeholder="Ej: Laptop de administración"></textarea>
                                                <div id="eq_descripcionError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Observaciones</label>
                                                <textarea name="eq_observaciones" id="eq_observaciones" class="form-control"
                                                          rows="2" maxlength="500"
                                                          placeholder="Ej: Con cargador y funda"></textarea>
                                                <div id="eq_observacionesError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-text text-muted">
                                                    <i class="fas fa-circle-info me-1"></i>
                                                    El <strong>serial es único en todo el sistema</strong>: si ya existe
                                                    otro equipo con ese serial, no podrá guardarse.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-1"></i> Guardar equipo
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- ==================== FICHA TÉCNICA ==================== -->
                                <div class="tab-pane fade py-3" id="panelFicha"
                                     role="tabpanel" aria-labelledby="tabFicha">
                                    <form id="form-ficha" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Nombre de la ficha *</label>
                                                <input type="text" name="f_nombre_ficha" id="f_nombre_ficha"
                                                       class="form-control" maxlength="100"
                                                       placeholder="Ej: Ficha Psicología" required>
                                                <div id="f_nombre_fichaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Servicio *</label>
                                                <select name="f_id_servicio" id="f_id_servicio"
                                                        class="form-select select2" data-placeholder="Seleccione el servicio…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="f_id_servicioError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Empleado responsable *</label>
                                                <select name="f_id_empleado_responsable" id="f_id_empleado_responsable"
                                                        class="form-select select2" data-placeholder="Seleccione el empleado…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="f_id_empleado_responsableError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Descripción</label>
                                                <textarea name="f_descripcion" id="f_descripcion" class="form-control"
                                                          rows="2" maxlength="500"
                                                          placeholder="Ej: Mobiliario asignado al área de psicología"></textarea>
                                                <div id="f_descripcionError" class="form-text text-danger"></div>
                                            </div>

                                            <!-- ===== Detalles: mobiliario ===== -->
                                            <div class="col-12">
                                                <div class="card bg-light border-0">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="m-0 fw-bold text-primary">
                                                                <i class="fas fa-chair me-2"></i>Mobiliario de la ficha
                                                            </h6>
                                                            <button type="button" id="btnAgregarFilaMobiliario"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-plus me-1"></i> Agregar fila
                                                            </button>
                                                        </div>
                                                        <div id="contenedorDetallesMobiliario">
                                                            <div class="text-muted small">
                                                                Sin filas: usa <strong>Agregar fila</strong> para asignar
                                                                mobiliario (la cantidad no puede superar lo disponible).
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ===== Detalles: equipos ===== -->
                                            <div class="col-12">
                                                <div class="card bg-light border-0">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="m-0 fw-bold text-success">
                                                                <i class="fas fa-laptop me-2"></i>Equipos de la ficha
                                                            </h6>
                                                            <button type="button" id="btnAgregarFilaEquipo"
                                                                    class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-plus me-1"></i> Agregar fila
                                                            </button>
                                                        </div>
                                                        <div id="contenedorDetallesEquipo">
                                                            <div class="text-muted small">
                                                                Sin filas: usa <strong>Agregar fila</strong> para asignar
                                                                equipos (cada equipo solo puede estar en UNA ficha activa).
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div id="detallesError" class="form-text text-danger"></div>
                                                <div class="form-text text-muted">
                                                    <i class="fas fa-circle-info me-1"></i>
                                                    Cada empleado solo puede tener <strong>una ficha activa</strong> y la
                                                    ficha debe incluir al menos un ítem.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-save me-1"></i> Guardar ficha técnica
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>

    <!-- Módulo Mobiliario: stats, validaciones (incluye filas de detalle), tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/mobiliario/crear.js" defer></script>
</body>

</html>
