<?php
/**
 * app/Views/reportes/discapacidad.php
 * ---------------------------------------------------------------
 * Reporte Estadístico de Discapacidad.
 */
$titulo = "Reportes Estadísticos — Discapacidad";
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
                            <i class="fas fa-wheelchair text-primary me-2"></i>Reportes Estadísticos — Discapacidad
                        </h1>
                        <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-question-circle me-1"></i>Ayuda
                        </button>
                    </div>

                    <!-- Tarjetas de resumen (stats.js → api/reportes/stats) -->
                    <div class="row mb-4">
                        <?php
                        $tarjetasReporte = [
                            ['color' => 'primary', 'titulo' => 'Registros totales', 'stat' => 'discapacidad_total', 'icon' => 'fa-wheelchair'],
                            ['color' => 'info', 'titulo' => 'Registros del mes', 'stat' => 'discapacidad_mes', 'icon' => 'fa-calendar-check'],
                            ['color' => 'danger', 'titulo' => 'Grado grave', 'stat' => 'graves', 'icon' => 'fa-exclamation-triangle'],
                            ['color' => 'success', 'titulo' => 'Con carnet', 'stat' => 'con_carnet', 'icon' => 'fa-id-card'],
                        ];
                        foreach ($tarjetasReporte as $card) {
                            include BASE_PATH . '/app/Views/inicio/components/stat_card.php';
                        }
                        ?>
                    </div>

                    <div class="card shadow mb-4 border-start border-primary border-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtros de Discapacidad</h6>
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
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Tipo Discapacidad:</label>
                                        <select id="tipo_discapacidad" name="tipo_discapacidad" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="Física">Física</option>
                                            <option value="Sensorial">Sensorial</option>
                                            <option value="Intelectual">Intelectual</option>
                                            <option value="Múltiple">Múltiple</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Grado:</label>
                                        <select id="grado" name="grado" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="Leve">Leve</option>
                                            <option value="Moderado">Moderado</option>
                                            <option value="Grave">Grave</option>
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

                    <div id="contenedor_discapacidad" style="display: none;">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Estadísticas por Tipo y Grado</h6>
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
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución por Tipo</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartDiscTipo"></canvas></div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Distribución por PNF</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartDiscPnf"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Registros de Beneficiarios con Discapacidad</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla_discapacidad" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Beneficiario</th>
                                                <th>Cédula</th>
                                                <th>PNF</th>
                                                <th>Tipo Discapacidad</th>
                                                <th>Grado</th>
                                                <th>Asistencia</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyDiscapacidad"></tbody>
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
        window.REPORTES_TIPO = 'discapacidad';
    </script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/comunes.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/pdf-completo.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/discapacidad.js" defer></script>
</body>
</html>
