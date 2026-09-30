<?php
/**
 * app/Views/transporte/consultar.php
 * ---------------------------------------------------------------
 * Consulta tabular hub para Transporte (Rutas, Vehículos, Proveedores, Repuestos, Asignaciones, Mantenimientos).
 */
$titulo = "Consultar Registros de Transporte";
include BASE_PATH . '/app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <!-- Cabecera -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-truck text-primary me-2"></i>Gestión de Transporte — Registros
                        </h1>
                        <div>
                            <button id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-sync-alt me-1"></i>Recargar
                            </button>
                            <?php if ($puedeCrear): ?>
                                <a href="<?= BASE_URL ?>transporte/crear" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-plus me-1"></i>Nuevo Registro
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <?php include BASE_PATH . '/app/Views/transporte/components/stats.php'; ?>

                    <!-- Pestañas de Consulta -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <ul class="nav nav-tabs card-header-tabs" id="tabTransporteConsultar" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active font-weight-bold" id="tab-c-rutas-tab" data-bs-toggle="tab" data-bs-target="#tab-c-rutas" type="button" role="tab">
                                        <i class="fas fa-route me-1"></i>Rutas
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-c-vehiculos-tab" data-bs-toggle="tab" data-bs-target="#tab-c-vehiculos" type="button" role="tab">
                                        <i class="fas fa-bus me-1"></i>Vehículos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-c-proveedores-tab" data-bs-toggle="tab" data-bs-target="#tab-c-proveedores" type="button" role="tab">
                                        <i class="fas fa-truck-field me-1"></i>Proveedores
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-c-repuestos-tab" data-bs-toggle="tab" data-bs-target="#tab-c-repuestos" type="button" role="tab">
                                        <i class="fas fa-gears me-1"></i>Repuestos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-c-asignaciones-tab" data-bs-toggle="tab" data-bs-target="#tab-c-asignaciones" type="button" role="tab">
                                        <i class="fas fa-id-card-clip me-1"></i>Asignaciones
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-c-mantenimientos-tab" data-bs-toggle="tab" data-bs-target="#tab-c-mantenimientos" type="button" role="tab">
                                        <i class="fas fa-wrench me-1"></i>Mantenimientos
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="tabTransporteConsultarContent">

                                <!-- TAB 1: RUTAS -->
                                <div class="tab-pane fade show active" id="tab-c-rutas" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tablaRutas" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ruta</th>
                                                    <th>Tipo</th>
                                                    <th>Horario</th>
                                                    <th>Origen / Destino</th>
                                                    <th>Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyRutas"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB 2: VEHÍCULOS -->
                                <div class="tab-pane fade" id="tab-c-vehiculos" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tablaVehiculos" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Placa</th>
                                                    <th>Modelo</th>
                                                    <th>Tipo</th>
                                                    <th>Adquisición</th>
                                                    <th>Estado</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyVehiculos"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB 3: PROVEEDORES -->
                                <div class="tab-pane fade" id="tab-c-proveedores" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tablaProveedores" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Nombre / Razón Social</th>
                                                    <th>Teléfono</th>
                                                    <th>Correo</th>
                                                    <th>Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyProveedores"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB 4: REPUESTOS -->
                                <div class="tab-pane fade" id="tab-c-repuestos" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small">Inventario de repuestos y piezas de repuesto de vehículos</span>
                                        <button id="btn-historial-repuestos" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-history me-1"></i>Historial de Movimientos
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="tablaRepuestos" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Repuesto</th>
                                                    <th>Descripción</th>
                                                    <th>Proveedor</th>
                                                    <th>Stock</th>
                                                    <th>Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyRepuestos"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB 5: ASIGNACIONES -->
                                <div class="tab-pane fade" id="tab-c-asignaciones" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tablaAsignaciones" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ruta</th>
                                                    <th>Vehículo</th>
                                                    <th>Chofer</th>
                                                    <th>Fecha Asignación</th>
                                                    <th>Estatus</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyAsignaciones"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- TAB 6: MANTENIMIENTOS -->
                                <div class="tab-pane fade" id="tab-c-mantenimientos" role="tabpanel">
                                    <div class="table-responsive">
                                        <table id="tablaMantenimientos" class="table table-bordered table-hover align-middle" width="100%">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Vehículo</th>
                                                    <th>Tipo</th>
                                                    <th>Fecha</th>
                                                    <th>Descripción</th>
                                                    <th>Repuestos Usados</th>
                                                    <th class="text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyMantenimientos"></tbody>
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

    <!-- MODALES DE EDICIÓN Y GESTIÓN -->

    <!-- Modal Editar Ruta -->
    <div class="modal fade" id="modalEditarRuta" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit me-2"></i>Editar Ruta <small id="lblEditRutaId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar-ruta" novalidate>
                    <input type="hidden" name="id_ruta" id="edit_id_ruta">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label font-weight-bold">Nombre de la Ruta *</label>
                                <input type="text" name="nombre_ruta" id="edit_nombre_ruta" class="form-control" maxlength="100" required>
                                <div id="edit_nombre_rutaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Estatus *</label>
                                <select name="estatus" id="edit_estatus_ruta" class="form-select">
                                    <option value="Activa">Activa</option>
                                    <option value="Inactiva">Inactiva</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Tipo de Ruta *</label>
                                <select name="tipo_ruta" id="edit_tipo_ruta" class="form-select select2" data-placeholder="Seleccione..." required>
                                    <option value="Urbana">Urbana</option>
                                    <option value="Inter-Urbana">Inter-Urbana</option>
                                    <option value="Extraurbana">Extraurbana</option>
                                    <option value="Institucional">Institucional</option>
                                    <option value="Especial">Especial</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Salida</label>
                                <input type="time" name="horario_salida" id="edit_horario_salida" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Llegada</label>
                                <input type="time" name="horario_llegada" id="edit_horario_llegada" class="form-control">
                                <div id="edit_horario_llegadaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Partida</label>
                                <input type="text" name="punto_partida" id="edit_punto_partida" class="form-control" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Destino</label>
                                <input type="text" name="punto_destino" id="edit_punto_destino" class="form-control" maxlength="255">
                            </div>
                            <div class="col-12">
                                <label class="form-label font-weight-bold">Trayectoria</label>
                                <textarea name="trayectoria" id="edit_trayectoria" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Vehículo -->
    <div class="modal fade" id="modalEditarVehiculo" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-bus me-2"></i>Editar Vehículo <small id="lblEditVehiculoId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar-vehiculo" novalidate>
                    <input type="hidden" name="id_vehiculo" id="edit_id_vehiculo">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Placa *</label>
                                <input type="text" name="placa" id="edit_placa" class="form-control text-uppercase" maxlength="20" required>
                                <div id="edit_placaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Modelo</label>
                                <input type="text" name="modelo" id="edit_modelo_vehiculo" class="form-control" maxlength="50">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Tipo *</label>
                                <select name="tipo" id="edit_tipo_vehiculo" class="form-select select2" data-placeholder="Seleccione..." required>
                                    <option value="Autobús">Autobús</option>
                                    <option value="Camioneta">Camioneta</option>
                                    <option value="Automóvil">Automóvil</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Adquisición</label>
                                <input type="date" name="fecha_adquisicion" id="edit_fecha_adquisicion" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Estado *</label>
                                <select name="estado" id="edit_estado_vehiculo" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div class="modal fade" id="modalEditarProveedor" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-truck-field me-2"></i>Editar Proveedor <small id="lblEditProveedorId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar-proveedor" novalidate>
                    <input type="hidden" name="id_proveedor" id="edit_id_proveedor">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">Documento *</label>
                                <select name="tipo_documento" id="edit_tipo_documento" class="form-select">
                                    <option value="J">J (RIF)</option>
                                    <option value="G">G (Gubernamental)</option>
                                    <option value="V">V (Venezolano)</option>
                                    <option value="E">E (Extranjero)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Número *</label>
                                <input type="text" name="num_documento" id="edit_num_documento" class="form-control" maxlength="20" required>
                                <div id="edit_num_documentoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label font-weight-bold">Nombre / Razón Social *</label>
                                <input type="text" name="nombre" id="edit_nombre_proveedor" class="form-control" maxlength="100" required>
                                <div id="edit_nombre_proveedorError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Teléfono *</label>
                                <input type="text" name="telefono" id="edit_telefono_proveedor" class="form-control" maxlength="25" required>
                                <div id="edit_telefono_proveedorError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Correo *</label>
                                <input type="email" name="correo" id="edit_correo_proveedor" class="form-control" maxlength="100" required>
                                <div id="edit_correo_proveedorError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Estatus *</label>
                                <select name="estatus" id="edit_estatus_proveedor" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-weight-bold">Dirección *</label>
                                <input type="text" name="direccion" id="edit_direccion_proveedor" class="form-control" maxlength="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Repuesto -->
    <div class="modal fade" id="modalEditarRepuesto" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-gears me-2"></i>Editar Repuesto <small id="lblEditRepuestoId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar-repuesto" novalidate>
                    <input type="hidden" name="id_repuesto" id="edit_id_repuesto">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Nombre *</label>
                                <input type="text" name="nombre" id="edit_nombre_repuesto" class="form-control" maxlength="100" required>
                                <div id="edit_nombre_repuestoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Proveedor</label>
                                <select name="id_proveedor" id="edit_id_proveedor_repuesto" class="form-select select2" data-placeholder="Seleccione proveedor...">
                                    <option value="">Cargando proveedores...</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-weight-bold">Descripción</label>
                                <input type="text" name="descripcion" id="edit_descripcion_repuesto" class="form-control" maxlength="255">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Entrada de Repuesto -->
    <div class="modal fade" id="modalEntradaRepuesto" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle me-2"></i>Entrada de Stock de Repuesto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-entrada-repuesto" novalidate>
                    <input type="hidden" name="id_repuesto" id="entrada_id_repuesto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Repuesto</label>
                            <input type="text" id="entrada_nombre_repuesto" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Cantidad a Ingresar *</label>
                            <input type="number" name="cantidad" id="entrada_cantidad" class="form-control" min="1" required>
                            <div id="entrada_cantidadError" class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Motivo de Entrada *</label>
                            <select name="razon" id="entrada_razon" class="form-select" required>
                                <option value="">Seleccione motivo...</option>
                                <option value="Compra">Compra</option>
                                <option value="Devolución">Devolución</option>
                                <option value="Donación">Donación</option>
                                <option value="Ajuste de inventario">Ajuste de inventario</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i>Registrar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Salida de Repuesto -->
    <div class="modal fade" id="modalSalidaRepuesto" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-minus-circle me-2"></i>Salida de Stock de Repuesto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-salida-repuesto" novalidate>
                    <input type="hidden" name="id_repuesto" id="salida_id_repuesto">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Repuesto</label>
                            <input type="text" id="salida_nombre_repuesto" class="form-control bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Cantidad a Retirar *</label>
                            <input type="number" name="cantidad" id="salida_cantidad" class="form-control" min="1" required>
                            <div id="salida_cantidadError" class="form-text text-danger"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Motivo de Salida *</label>
                            <select name="razon" id="salida_razon" class="form-select" required>
                                <option value="">Seleccione motivo...</option>
                                <option value="Uso en mantenimiento">Uso en mantenimiento</option>
                                <option value="Vencimiento">Vencimiento</option>
                                <option value="Daño">Daño</option>
                                <option value="Pérdida">Pérdida</option>
                                <option value="Donación">Donación</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-minus-circle me-1"></i>Registrar Salida</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Historial Kardex Repuestos -->
    <div class="modal fade" id="modalHistorialRepuestos" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-history me-2"></i>Historial de Movimientos de Repuestos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tablaHistorialRepuestos" class="table table-sm table-striped table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th>Repuesto</th>
                                    <th>Tipo Movimiento</th>
                                    <th>Cantidad</th>
                                    <th>Razón / Motivo</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyHistorialRepuestos"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Asignación -->
    <div class="modal fade" id="modalEditarAsignacion" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-id-card-clip me-2"></i>Editar Asignación <small id="lblEditAsigId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form-editar-asignacion" novalidate>
                    <input type="hidden" name="id_asignacion" id="edit_id_asignacion">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Ruta *</label>
                                <select name="id_ruta" id="edit_id_ruta_asig" class="form-select select2" data-placeholder="Seleccione ruta..." required>
                                    <option value="">Cargando rutas...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Vehículo *</label>
                                <select name="id_vehiculo" id="edit_id_vehiculo_asig" class="form-select select2" data-placeholder="Seleccione vehículo..." required>
                                    <option value="">Cargando vehículos...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Chofer *</label>
                                <select name="id_empleado" id="edit_id_empleado_asig" class="form-select select2" data-placeholder="Seleccione chofer..." required>
                                    <option value="">Cargando choferes...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Fecha Asignación *</label>
                                <input type="date" name="fecha_asignacion" id="edit_fecha_asignacion" class="form-control" required>
                                <div id="edit_fecha_asignacionError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Estatus *</label>
                                <select name="estatus" id="edit_estatus_asignacion" class="form-select">
                                    <option value="Activa">Activa</option>
                                    <option value="Inactiva">Inactiva</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Mantenimiento -->
    <div class="modal fade" id="modalDetalleMantenimiento" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-wrench me-2"></i>Detalle de Mantenimiento <small id="lblDetalleMantId"></small></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bodyDetalleMantenimiento">
                    <!-- Rellenado por JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>

    <script>
        window.TRANSPORTE_PUEDE_CREAR    = <?= $puedeCrear ? 'true' : 'false' ?>;
        window.TRANSPORTE_PUEDE_EDITAR   = <?= $puedeEditar ? 'true' : 'false' ?>;
        window.TRANSPORTE_PUEDE_ELIMINAR = <?= $puedeEliminar ? 'true' : 'false' ?>;
    </script>

    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/consultar.js" defer></script>
</body>
</html>
