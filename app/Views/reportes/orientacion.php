<?php
/**
 * app/Views/reportes/orientacion.php
 * ---------------------------------------------------------------
 * Reporte Estadístico de Orientación.
 */
$titulo = "Reportes Estadísticos — Orientación";
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
                            <i class="fas fa-compass text-primary me-2"></i>Reportes Estadísticos — Orientación
                        </h1>
                        <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-question-circle me-1"></i>Ayuda
                        </button>
                    </div>

                    <!-- Tarjetas de resumen (stats.js → api/reportes/stats) -->
                    <div class="row mb-4">
                        <?php
                        $tarjetasReporte = [
                            ['color' => 'primary', 'titulo' => 'Orientaciones totales', 'stat' => 'orientaciones_total', 'icon' => 'fa-compass'],
                            ['color' => 'info', 'titulo' => 'Orientaciones del mes', 'stat' => 'orientaciones_mes', 'icon' => 'fa-calendar-check'],
                            ['color' => 'danger', 'titulo' => 'Mujeres orientadas', 'stat' => 'mujeres', 'icon' => 'fa-venus'],
                            ['color' => 'primary', 'titulo' => 'Hombres orientados', 'stat' => 'hombres', 'icon' => 'fa-mars'],
                        ];
                        foreach ($tarjetasReporte as $card) {
                            include BASE_PATH . '/app/Views/inicio/components/stat_card.php';
                        }
                        ?>
                    </div>

                    <div class="card shadow mb-4 border-start border-primary border-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtros de Orientación</h6>
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
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Género:</label>
                                        <select id="genero" name="genero" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">PNF:</label>
                                        <select id="pnf" name="pnf" class="form-select form-select-sm">
                                            <option value="">Todos los PNF</option>
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

                    <div id="contenedor_orientacion" style="display: none;">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Atenciones de Orientación</h6>
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
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Orientación por PNF</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartOrPnf"></canvas></div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución por Género</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartOrGenero"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Detalle de Casos Atendidos</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla_orientacion" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Beneficiario</th>
                                                <th>Cédula</th>
                                                <th>PNF</th>
                                                <th>Motivo Consulta</th>
                                                <th>Indicaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyOrientacion"></tbody>
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
        window.REPORTES_TIPO = 'orientacion';
    </script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/comunes.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/pdf-completo.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/orientacion.js" defer></script>
</body>
</html>
