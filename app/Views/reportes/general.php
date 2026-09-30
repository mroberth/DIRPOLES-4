<?php
/**
 * app/Views/reportes/general.php
 * ---------------------------------------------------------------
 * Reporte Estadístico General (Consolidado de Servicios).
 */
$titulo = "Reportes Estadísticos — General";
include BASE_PATH . '/app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <!-- Cabecera de Página -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-pie text-primary me-2"></i>Reportes Estadísticos — General
                        </h1>
                        <div>
                            <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-question-circle me-1"></i>Ayuda
                            </button>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen (stats.js → api/reportes/stats) -->
                    <div class="row mb-4">
                        <?php
                        $tarjetasReporte = [
                            ['color' => 'primary', 'titulo' => 'Atenciones totales', 'stat' => 'atenciones_total', 'icon' => 'fa-layer-group'],
                            ['color' => 'info', 'titulo' => 'Atenciones del mes', 'stat' => 'atenciones_mes', 'icon' => 'fa-calendar-check'],
                            ['color' => 'danger', 'titulo' => 'Mujeres atendidas', 'stat' => 'mujeres', 'icon' => 'fa-venus'],
                            ['color' => 'primary', 'titulo' => 'Hombres atendidos', 'stat' => 'hombres', 'icon' => 'fa-mars'],
                        ];
                        foreach ($tarjetasReporte as $card) {
                            include BASE_PATH . '/app/Views/inicio/components/stat_card.php';
                        }
                        ?>
                    </div>

                    <!-- Card de Filtros -->
                    <div class="card shadow mb-4 border-start border-primary border-4">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Parámetros del Reporte</h6>
                        </div>
                        <div class="card-body">
                            <form id="form-reporte" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Fecha Inicio:</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Fecha Fin:</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Género:</label>
                                        <select id="genero" name="genero" class="form-select form-select-sm">
                                            <option value="" selected>Todos</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">PNF / Programa:</label>
                                        <select id="pnf" name="pnf" class="form-select form-select-sm">
                                            <option value="">Todos los PNF</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Área / Servicio:</label>
                                        <select id="area" name="area" class="form-select form-select-sm">
                                            <option value="">Todas las Áreas</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <button type="button" id="btn-limpiar" class="btn btn-secondary btn-sm me-2">
                                            <i class="fas fa-undo me-1"></i>Limpiar Filtros
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                            <i class="fas fa-chart-line me-1"></i>Generar Reporte
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contenedor de Resultados (Gráficos + Tabla) -->
                    <div id="contenedor_general" style="display: none;">
                        <!-- Gráficas Estadísticas -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Gráficos Estadísticos</h6>
                                <div class="d-flex align-items-center">
                                    <button id="btn-pdf-completo" class="btn btn-danger btn-sm shadow-sm me-2" type="button">
                                        <i class="fas fa-file-pdf me-1"></i>PDF Completo
                                    </button>
                                    <label for="select-tipo-chart" class="small font-weight-bold me-2 mb-0">Tipo de Gráficos:</label>
                                    <select id="select-tipo-chart" class="form-select form-select-sm" style="width: 150px;">
                                        <option value="bar">Barras</option>
                                        <option value="pie">Torta (Pie)</option>
                                        <option value="doughnut">Anillo (Doughnut)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-lg-4" id="wrapper-chartG">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución por Género</h6>
                                        <div class="chart-container" style="position: relative; height:260px; width:100%">
                                            <canvas id="chartG"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4" id="wrapper-chartP">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución por PNF</h6>
                                        <div class="chart-container" style="position: relative; height:260px; width:100%">
                                            <canvas id="chartP"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4" id="wrapper-chartGeneral">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Atenciones por Área</h6>
                                        <div class="chart-container" style="position: relative; height:260px; width:100%">
                                            <canvas id="chartGeneral"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalle de Registros DataTable -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Detalle de Registros Atendidos</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla_general" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Beneficiario</th>
                                                <th>Cédula</th>
                                                <th>Género</th>
                                                <th>PNF</th>
                                                <th>Servicio / Área</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyGeneral"></tbody>
                                    </table>
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
        window.REPORTES_TIPO = 'general';
    </script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/comunes.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/pdf-completo.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/general.js" defer></script>
</body>
</html>
