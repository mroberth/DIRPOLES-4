<?php
/**
 * app/Views/transporte/crear.php
 * ---------------------------------------------------------------
 * Formulario hub de creación para Transporte (Rutas, Vehículos, Proveedores, Repuestos, Asignaciones, Mantenimientos).
 */
$titulo = "Crear Registro de Transporte";
include BASE_PATH . '/app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <!-- Cabecera de página -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-truck text-primary me-2"></i>Gestión de Transporte — Registrar
                        </h1>
                        <div>
                            <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-question-circle me-1"></i>Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>transporte/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i>Ver Registros
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de Estadísticas -->
                    <?php include BASE_PATH . '/app/Views/transporte/components/stats.php'; ?>

                    <!-- Pestañas del Hub -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <ul class="nav nav-tabs card-header-tabs" id="tabTransporteCrear" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active font-weight-bold" id="tab-rutas-tab" data-bs-toggle="tab" data-bs-target="#tab-rutas" type="button" role="tab">
                                        <i class="fas fa-route me-1"></i>Rutas
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-vehiculos-tab" data-bs-toggle="tab" data-bs-target="#tab-vehiculos" type="button" role="tab">
                                        <i class="fas fa-bus me-1"></i>Vehículos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-proveedores-tab" data-bs-toggle="tab" data-bs-target="#tab-proveedores" type="button" role="tab">
                                        <i class="fas fa-truck-field me-1"></i>Proveedores
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-repuestos-tab" data-bs-toggle="tab" data-bs-target="#tab-repuestos" type="button" role="tab">
                                        <i class="fas fa-gears me-1"></i>Repuestos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-asignaciones-tab" data-bs-toggle="tab" data-bs-target="#tab-asignaciones" type="button" role="tab">
                                        <i class="fas fa-id-card-clip me-1"></i>Asignaciones
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold" id="tab-mantenimiento-tab" data-bs-toggle="tab" data-bs-target="#tab-mantenimiento" type="button" role="tab">
                                        <i class="fas fa-wrench me-1"></i>Mantenimiento
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="tabTransporteCrearContent">

                                <!-- PESTAÑA 1: RUTAS -->
                                <div class="tab-pane fade show active" id="tab-rutas" role="tabpanel">
                                    <form id="form-ruta" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Nombre de la Ruta *</label>
                                                <input type="text" name="nombre_ruta" id="nombre_ruta" class="form-control" maxlength="100" placeholder="Ej: Ruta 1 - UPTAEB Central" required>
                                                <div id="nombre_rutaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Tipo de Ruta *</label>
                                                <select name="tipo_ruta" id="tipo_ruta" class="form-select select2" data-placeholder="Seleccione..." required>
                                                    <option value="Urbana">Urbana</option>
                                                    <option value="Inter-Urbana">Inter-Urbana</option>
                                                    <option value="Extraurbana">Extraurbana</option>
                                                    <option value="Institucional">Institucional</option>
                                                    <option value="Especial">Especial</option>
                                                </select>
                                                <div id="tipo_rutaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Estatus *</label>
                                                <select name="estatus" id="estatus_ruta" class="form-select">
                                                    <option value="Activa">Activa</option>
                                                    <option value="Inactiva">Inactiva</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Horario de Salida</label>
                                                <input type="time" name="horario_salida" id="horario_salida" class="form-control">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Horario de Llegada</label>
                                                <input type="time" name="horario_llegada" id="horario_llegada" class="form-control">
                                                <div id="horario_llegadaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Punto de Partida</label>
                                                <input type="text" name="punto_partida" id="punto_partida" class="form-control" maxlength="255" placeholder="Ej: Sede Central UPTAEB">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Punto de Destino</label>
                                                <input type="text" name="punto_destino" id="punto_destino" class="form-control" maxlength="255" placeholder="Ej: Terminal de Barquisimeto">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label font-weight-bold">Trayectoria / Descripción</label>
                                                <textarea name="trayectoria" id="trayectoria" class="form-control" rows="2" placeholder="Detalles de la ruta, paradas principales..."></textarea>
                                            </div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Ruta</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- PESTAÑA 2: VEHÍCULOS -->
                                <div class="tab-pane fade" id="tab-vehiculos" role="tabpanel">
                                    <form id="form-vehiculo" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Placa *</label>
                                                <input type="text" name="placa" id="placa" class="form-control text-uppercase" maxlength="20" placeholder="Ej: ABC1234" required>
                                                <div id="placaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Modelo</label>
                                                <input type="text" name="modelo" id="modelo_vehiculo" class="form-control" maxlength="50" placeholder="Ej: Yutong ZK6118HGH">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Tipo de Vehículo *</label>
                                                <select name="tipo" id="tipo_vehiculo" class="form-select select2" data-placeholder="Seleccione..." required>
                                                    <option value="Autobús">Autobús</option>
                                                    <option value="Camioneta">Camioneta</option>
                                                    <option value="Automóvil">Automóvil</option>
                                                </select>
                                                <div id="tipo_vehiculoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Fecha de Adquisición</label>
                                                <input type="date" name="fecha_adquisicion" id="fecha_adquisicion" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Estado del Vehículo *</label>
                                                <select name="estado" id="estado_vehiculo" class="form-select">
                                                    <option value="Activo">Activo</option>
                                                    <option value="Inactivo">Inactivo</option>
                                                    <option value="Mantenimiento">Mantenimiento</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Vehículo</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- PESTAÑA 3: PROVEEDORES -->
                                <div class="tab-pane fade" id="tab-proveedores" role="tabpanel">
                                    <form id="form-proveedor" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label font-weight-bold">Documento *</label>
                                                <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                                                    <option value="J">J (RIF)</option>
                                                    <option value="G">G (Gubernamental)</option>
                                                    <option value="V">V (Venezolano)</option>
                                                    <option value="E">E (Extranjero)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Número de Documento *</label>
                                                <input type="text" name="num_documento" id="num_documento" class="form-control" maxlength="20" placeholder="Ej: 123456789" inputmode="numeric" required>
                                                <div id="num_documentoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Nombre / Razón Social *</label>
                                                <input type="text" name="nombre" id="nombre_proveedor" class="form-control" maxlength="100" placeholder="Ej: Repuestos Barquisimeto C.A." required>
                                                <div id="nombre_proveedorError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Teléfono *</label>
                                                <input type="text" name="telefono" id="telefono_proveedor" class="form-control" maxlength="25" placeholder="Ej: 04121234567" required>
                                                <div id="telefono_proveedorError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Correo Electrónico *</label>
                                                <input type="email" name="correo" id="correo_proveedor" class="form-control" maxlength="100" placeholder="proveedor@ejemplo.com" required>
                                                <div id="correo_proveedorError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Estatus *</label>
                                                <select name="estatus" id="estatus_proveedor" class="form-select">
                                                    <option value="Activo">Activo</option>
                                                    <option value="Inactivo">Inactivo</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label font-weight-bold">Dirección *</label>
                                                <input type="text" name="direccion" id="direccion_proveedor" class="form-control" maxlength="100" placeholder="Ej: Av. Venezuela con Calle 25" required>
                                                <div id="direccion_proveedorError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Proveedor</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- PESTAÑA 4: REPUESTOS -->
                                <div class="tab-pane fade" id="tab-repuestos" role="tabpanel">
                                    <form id="form-repuesto" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Nombre del Repuesto *</label>
                                                <input type="text" name="nombre" id="nombre_repuesto" class="form-control" maxlength="100" placeholder="Ej: Filtro de Aceite Yutong" required>
                                                <div id="nombre_repuestoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Proveedor Asociado</label>
                                                <select name="id_proveedor" id="id_proveedor_repuesto" class="form-select select2" data-placeholder="Seleccione proveedor opcional...">
                                                    <option value="">Cargando proveedores...</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label font-weight-bold">Descripción del Repuesto</label>
                                                <input type="text" name="descripcion" id="descripcion_repuesto" class="form-control" maxlength="255" placeholder="Detalles técnicos, compatibilidad, código...">
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-info py-2 mb-0 small">
                                                    <i class="fas fa-info-circle me-1"></i> El repuesto se registrará con stock inicial 0 ('Agotado'). Para ingresar unidades, utiliza el botón de Entrada en la sección de consulta.
                                                </div>
                                            </div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Repuesto</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- PESTAÑA 5: ASIGNACIONES -->
                                <div class="tab-pane fade" id="tab-asignaciones" role="tabpanel">
                                    <form id="form-asignacion" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Ruta *</label>
                                                <select name="id_ruta" id="id_ruta_asig" class="form-select select2" data-placeholder="Seleccione ruta..." required>
                                                    <option value="">Cargando rutas...</option>
                                                </select>
                                                <div id="id_ruta_asigError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Vehículo *</label>
                                                <select name="id_vehiculo" id="id_vehiculo_asig" class="form-select select2" data-placeholder="Seleccione vehículo..." required>
                                                    <option value="">Cargando vehículos...</option>
                                                </select>
                                                <div id="id_vehiculo_asigError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-weight-bold">Chofer *</label>
                                                <select name="id_empleado" id="id_empleado_asig" class="form-select select2" data-placeholder="Seleccione chofer..." required>
                                                    <option value="">Cargando choferes...</option>
                                                </select>
                                                <div id="id_empleado_asigError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Fecha de Asignación *</label>
                                                <input type="date" name="fecha_asignacion" id="fecha_asignacion" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                <div id="fecha_asignacionError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Estatus *</label>
                                                <select name="estatus" id="estatus_asignacion" class="form-select">
                                                    <option value="Activa">Activa</option>
                                                    <option value="Inactiva">Inactiva</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar Asignación</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- PESTAÑA 6: MANTENIMIENTO -->
                                <div class="tab-pane fade" id="tab-mantenimiento" role="tabpanel">
                                    <form id="form-mantenimiento" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label font-weight-bold">Vehículo *</label>
                                                <select name="id_vehiculo" id="id_vehiculo_mant" class="form-select select2" data-placeholder="Seleccione vehículo..." required>
                                                    <option value="">Cargando vehículos...</option>
                                                </select>
                                                <div id="id_vehiculo_mantError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Tipo de Mantenimiento *</label>
                                                <select name="tipo" id="tipo_mantenimiento" class="form-select select2" data-placeholder="Seleccione..." required>
                                                    <option value="Preventivo">Preventivo</option>
                                                    <option value="Correctivo">Correctivo</option>
                                                </select>
                                                <div id="tipo_mantenimientoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label font-weight-bold">Fecha *</label>
                                                <input type="date" name="fecha" id="fecha_mantenimiento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                <div id="fecha_mantenimientoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label font-weight-bold">Descripción del Servicio / Falla</label>
                                                <textarea name="descripcion" id="descripcion_mantenimiento" class="form-control" rows="2" placeholder="Detalle de trabajos realizados, lubricantes, revisión..."></textarea>
                                            </div>

                                            <!-- Consumo de repuestos -->
                                            <div class="col-12 mt-3">
                                                <div class="card bg-light border">
                                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                        <span class="font-weight-bold text-gray-800"><i class="fas fa-gears me-1"></i>Repuestos Consumidos (Opcional)</span>
                                                        <button type="button" id="btn-agregar-repuesto-mant" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-plus me-1"></i>Agregar Repuesto
                                                        </button>
                                                    </div>
                                                    <div class="card-body py-2" id="contenedor-repuestos-mant">
                                                        <p class="text-muted small mb-0 text-center py-2" id="msg-sin-repuestos">No se han agregado repuestos consumidos.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 text-end mt-4">
                                                <button type="reset" class="btn btn-outline-secondary me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-wrench me-1"></i>Registrar Mantenimiento</button>
                                            </div>
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

    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/transporte/crear.js" defer></script>
</body>
</html>
