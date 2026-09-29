<?php
// app/Views/inventario/consultar.php
$titulo = "Consultar Insumos";
include 'app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Inventario Médico</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <button type="button" id="btn-historial" class="btn btn-sm btn-outline-dark shadow-sm me-2">
                                <i class="fas fa-clock-rotate-left me-1"></i> Historial
                            </button>
                            <a href="<?= BASE_URL ?>inventario/crear" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo insumo
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include 'app/Views/inventario/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaInsumos" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Insumo</th>
                                            <th>Tipo</th>
                                            <th>Presentación</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Estatus</th>
                                            <th>Vencimiento</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyInsumos"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- ==================== MODAL EDITAR ==================== -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-pills text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarLabel">Editar Insumo</h5>
                            <small class="opacity-75" id="inventarioCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar" novalidate>
                    <input type="hidden" name="id_insumo" id="id_insumo">

                    <div class="modal-body p-0">
                        <div class="card border-0 rounded-0 bg-light">
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-lg-6 border-end">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-box me-2"></i> Datos del insumo
                                        </h6>

                                        <div class="mb-3">
                                            <label for="nombre_insumo" class="form-label text-muted small mb-1">
                                                <i class="fas fa-tag text-primary me-1"></i> Nombre del insumo *
                                            </label>
                                            <input type="text" class="form-control" name="nombre_insumo" id="nombre_insumo" maxlength="100" required>
                                            <div id="nombre_insumoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="tipo_insumo" class="form-label text-muted small mb-1">
                                                <i class="fas fa-layer-group text-primary me-1"></i> Tipo de insumo *
                                            </label>
                                            <select name="tipo_insumo" id="tipo_insumo" class="form-select select2" data-placeholder="Seleccione…" required>
                                                <option value="">Seleccione…</option>
                                                <option value="Medicamento">Medicamento</option>
                                                <option value="Material">Material</option>
                                                <option value="Quirúrgico">Quirúrgico</option>
                                            </select>
                                            <div id="tipo_insumoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="id_presentacion" class="form-label text-muted small mb-1">
                                                <i class="fas fa-prescription-bottle text-primary me-1"></i> Presentación *
                                            </label>
                                            <select name="id_presentacion" id="id_presentacion" class="form-select select2" data-placeholder="Seleccione…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="id_presentacionError" class="form-text text-danger"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-calendar-day me-2"></i> Detalles
                                        </h6>

                                        <div class="mb-3">
                                            <label for="fecha_vencimiento" class="form-label text-muted small mb-1">
                                                <i class="fas fa-calendar-xmark text-primary me-1"></i> Fecha de vencimiento *
                                            </label>
                                            <input type="date" class="form-control" name="fecha_vencimiento" id="fecha_vencimiento" required>
                                            <div id="fecha_vencimientoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label text-muted small mb-1">
                                                <i class="fas fa-align-left text-primary me-1"></i> Descripción *
                                            </label>
                                            <textarea class="form-control" name="descripcion" id="descripcion" rows="3" maxlength="250" required></textarea>
                                            <div id="descripcionError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            La cantidad y el estatus no se editan aquí: cambian con <strong>Entrada</strong> /
                                            <strong>Salida</strong>.
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

    <!-- ==================== MODAL ENTRADA ==================== -->
    <div class="modal fade" id="modalEntrada" tabindex="-1" aria-labelledby="modalEntradaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-arrow-down text-success"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEntradaLabel">Registrar Entrada</h5>
                            <small class="opacity-75" id="entradaCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-entrada" novalidate>
                    <input type="hidden" name="entrada_id_insumo" id="entrada_id_insumo">

                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label for="entrada_insumo" class="form-label text-muted small mb-1">Insumo</label>
                            <input type="text" class="form-control" id="entrada_insumo" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="entrada_cantidad" class="form-label text-muted small mb-1">
                                <i class="fas fa-boxes-stacked text-success me-1"></i> Cantidad a ingresar *
                            </label>
                            <input type="number" class="form-control" name="entrada_cantidad" id="entrada_cantidad"
                                   min="1" max="1000" step="1" inputmode="numeric" placeholder="Ej: 10" required>
                            <div id="entrada_cantidadError" class="form-text text-danger"></div>
                        </div>

                        <div class="mb-2">
                            <label for="entrada_descripcion" class="form-label text-muted small mb-1">
                                <i class="fas fa-align-left text-success me-1"></i> Descripción (detalle de la entrada) *
                            </label>
                            <textarea class="form-control" name="entrada_descripcion" id="entrada_descripcion"
                                      rows="2" maxlength="250" placeholder="Ej: Compra - lote 2026" required></textarea>
                            <div id="entrada_descripcionError" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-arrow-down me-1"></i> Registrar entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL SALIDA ==================== -->
    <div class="modal fade" id="modalSalida" tabindex="-1" aria-labelledby="modalSalidaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-arrow-up text-danger"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalSalidaLabel">Registrar Salida</h5>
                            <small class="opacity-75" id="salidaCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-salida" novalidate>
                    <input type="hidden" name="salida_id_insumo" id="salida_id_insumo">

                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label for="salida_insumo" class="form-label text-muted small mb-1">Insumo</label>
                            <input type="text" class="form-control" id="salida_insumo" readonly>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="salida_cantidad" class="form-label text-muted small mb-1">
                                    <i class="fas fa-boxes-stacked text-danger me-1"></i> Cantidad a retirar *
                                </label>
                                <input type="number" class="form-control" name="salida_cantidad" id="salida_cantidad"
                                       min="1" step="1" inputmode="numeric" placeholder="Ej: 1" required>
                                <div id="salida_cantidadError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-6">
                                <label for="salida_motivo" class="form-label text-muted small mb-1">
                                    <i class="fas fa-clipboard-list text-danger me-1"></i> Motivo *
                                </label>
                                <select name="salida_motivo" id="salida_motivo" class="form-select" required>
                                    <option value="">Seleccione…</option>
                                    <option value="Vencimiento">Vencimiento</option>
                                    <option value="Daño">Daño</option>
                                    <option value="Pérdida">Pérdida</option>
                                    <option value="Donación">Donación</option>
                                    <option value="Uso Interno">Uso Interno</option>
                                </select>
                                <div id="salida_motivoError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="salida_descripcion" class="form-label text-muted small mb-1">
                                <i class="fas fa-align-left text-danger me-1"></i> Detalle *
                            </label>
                            <textarea class="form-control" name="salida_descripcion" id="salida_descripcion"
                                      rows="2" maxlength="250" placeholder="Ej: Frasco roto en almacén" required></textarea>
                            <div id="salida_descripcionError" class="form-text text-danger"></div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-arrow-up me-1"></i> Registrar salida
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
                            <small class="opacity-75">Entradas, salidas y registros del inventario médico</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table id="tablaMovimientos" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th>Insumo</th>
                                    <th>Responsable</th>
                                    <th>Fecha y hora</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyMovimientos"></tbody>
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

    <?php include 'app/Views/template/script.php'; ?>

    <!-- Módulo Inventario Médico: stats + validaciones + edición/entrada/salida + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/consultar.js" defer></script>
</body>

</html>
