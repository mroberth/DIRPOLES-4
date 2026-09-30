<?php
/**
 * app/Views/reportes/transporte.php
 * ---------------------------------------------------------------
 * Reporte Estadístico de Transporte (Multi-sección expandido).
 */
$titulo = "Reportes Estadísticos — Transporte";
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-truck text-primary me-2"></i>Reportes Estadísticos — Transporte
                        </h1>
                        <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-question-circle me-1"></i>Ayuda
                        </button>
                    </div>

                    <!-- Tarjetas de resumen (stats.js → api/reportes/stats) -->
                    <div class="row mb-4">
                        <?php
                        $tarjetasReporte = [
                            ['color' => 'primary', 'titulo' => 'Vehículos Totales', 'stat' => 'vehiculos_total', 'icon' => 'fa-truck'],
                            ['color' => 'success', 'titulo' => 'Vehículos Activos', 'stat' => 'vehiculos_activos', 'icon' => 'fa-check-circle'],
                            ['color' => 'warning', 'titulo' => 'En Mantenimiento', 'stat' => 'vehiculos_mantenimiento', 'icon' => 'fa-wrench'],
                            ['color' => 'info', 'titulo' => 'Rutas Totales', 'stat' => 'rutas_total', 'icon' => 'fa-route'],
                            ['color' => 'success', 'titulo' => 'Rutas Activas', 'stat' => 'rutas_activas', 'icon' => 'fa-map-marked-alt'],
                            ['color' => 'secondary', 'titulo' => 'Proveedores', 'stat' => 'proveedores_total', 'icon' => 'fa-building'],
                            ['color' => 'dark', 'titulo' => 'Repuestos Totales', 'stat' => 'repuestos_total', 'icon' => 'fa-cogs'],
                            ['color' => 'danger', 'titulo' => 'Repuestos Bajo Stock', 'stat' => 'repuestos_bajo_stock', 'icon' => 'fa-exclamation-triangle'],
                            ['color' => 'primary', 'titulo' => 'Asignaciones Activas', 'stat' => 'asignaciones_activas', 'icon' => 'fa-tasks'],
                        ];
                        foreach ($tarjetasReporte as $card) {
                            include BASE_PATH . '/app/Views/inicio/components/stat_card.php';
                        }
                        ?>
                    </div>

                    <div class="card shadow mb-4 border-start border-primary border-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtros de Transporte</h6>
                        </div>
                        <div class="card-body">
                            <form id="form-reporte" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Fecha Inicio:</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Fecha Fin:</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Sección:</label>
                                        <select id="seccion_transporte" name="seccion_transporte" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                            <option value="Vehículos">Vehículos</option>
                                            <option value="Rutas">Rutas</option>
                                            <option value="Proveedores">Proveedores</option>
                                            <option value="Repuestos">Repuestos</option>
                                            <option value="Asignaciones">Asignaciones</option>
                                            <option value="Mantenimientos">Mantenimientos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Tipo Vehículo:</label>
                                        <select id="tipo_veh" name="tipo_vehiculo" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="Autobús">Autobús</option>
                                            <option value="Camioneta">Camioneta</option>
                                            <option value="Automóvil">Automóvil</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Estado/Estatus:</label>
                                        <select id="estado_veh" name="estado" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="Activo">Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                            <option value="Mantenimiento">Mantenimiento</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <button type="button" id="btn-limpiar" class="btn btn-secondary btn-sm me-2"><i class="fas fa-undo me-1"></i>Limpiar</button>
                                        <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-chart-line me-1"></i>Generar Reporte</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="contenedor_transporte" style="display: none;">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Gráficos Estadísticos de Transporte</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <button id="btn-pdf-completo" class="btn btn-danger btn-sm shadow-sm" type="button">
                                        <i class="fas fa-file-pdf me-1"></i>PDF Completo
                                    </button>
                                    <select id="select-tipo-chart" class="form-select form-select-sm" style="width: 130px;">
                                        <option value="bar">Barras</option>
                                        <option value="pie">Torta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Estado de Vehículos</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartTransEstado"></canvas></div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución de Registros por Sección</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartTransSeccion"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor con Pestañas por Sección -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <ul class="nav nav-tabs card-header-tabs" id="transporteTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active font-weight-bold" id="tab-vehiculos" data-bs-toggle="tab" data-bs-target="#pane-vehiculos" type="button" role="tab"><i class="fas fa-truck me-1"></i>Vehículos</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="tab-rutas" data-bs-toggle="tab" data-bs-target="#pane-rutas" type="button" role="tab"><i class="fas fa-route me-1"></i>Rutas</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="tab-proveedores" data-bs-toggle="tab" data-bs-target="#pane-proveedores" type="button" role="tab"><i class="fas fa-building me-1"></i>Proveedores</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="tab-repuestos" data-bs-toggle="tab" data-bs-target="#pane-repuestos" type="button" role="tab"><i class="fas fa-cogs me-1"></i>Repuestos</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="tab-asignaciones" data-bs-toggle="tab" data-bs-target="#pane-asignaciones" type="button" role="tab"><i class="fas fa-tasks me-1"></i>Asignaciones</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link font-weight-bold" id="tab-mantenimientos" data-bs-toggle="tab" data-bs-target="#pane-mantenimientos" type="button" role="tab"><i class="fas fa-wrench me-1"></i>Mantenimientos</button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="transporteTabsContent">
                                    <!-- 1. Vehículos -->
                                    <div class="tab-pane fade show active" id="pane-vehiculos" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_vehiculos" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Placa</th>
                                                        <th>Modelo</th>
                                                        <th>Tipo</th>
                                                        <th>Estado</th>
                                                        <th>Fecha Adquisición</th>
                                                        <th>Asignaciones Activas</th>
                                                        <th>Total Mantenimientos</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyVehiculos"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- 2. Rutas -->
                                    <div class="tab-pane fade" id="pane-rutas" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_rutas" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nombre Ruta</th>
                                                        <th>Tipo Ruta</th>
                                                        <th>Origen → Destino</th>
                                                        <th>Estado</th>
                                                        <th>Fecha Creación</th>
                                                        <th>Asignaciones Activas</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyRutas"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- 3. Proveedores -->
                                    <div class="tab-pane fade" id="pane-proveedores" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_proveedores" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nombre</th>
                                                        <th>Documento</th>
                                                        <th>Teléfono</th>
                                                        <th>Correo</th>
                                                        <th>Estado</th>
                                                        <th>Fecha Registro</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyProveedores"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- 4. Repuestos -->
                                    <div class="tab-pane fade" id="pane-repuestos" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_repuestos" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Repuesto</th>
                                                        <th>Proveedor</th>
                                                        <th>Stock Cantidad</th>
                                                        <th>Estado</th>
                                                        <th>Fecha Registro</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyRepuestos"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- 5. Asignaciones -->
                                    <div class="tab-pane fade" id="pane-asignaciones" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_asignaciones" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Ruta</th>
                                                        <th>Vehículo</th>
                                                        <th>Chofer</th>
                                                        <th>Cédula Chofer</th>
                                                        <th>Fecha Asignación</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbodyAsignaciones"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- 6. Mantenimientos -->
                                    <div class="tab-pane fade" id="pane-mantenimientos" role="tabpanel">
                                        <div class="table-responsive">
                                            <table id="tabla_transporte_mantenimientos" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Vehículo</th>
                                                        <th>Tipo Mantenimiento</th>
                                                        <th>Fecha</th>
                                                        <th>Descripción</th>
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
            </div>

            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>
    <script src="<?= BASE_URL ?>dist/js/dashboard/Chart.min.js"></script>
    <script>
        window.REPORTES_TIPO = 'transporte';
    </script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/comunes.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/pdf-completo.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/transporte.js" defer></script>
</body>
</html>
