<?php
/**
 * app/Views/reportes/psicologia.php
 * ---------------------------------------------------------------
 * Reporte Estadístico de Psicología y Citas.
 */
$titulo = "Reportes Estadísticos — Psicología";
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
                            <i class="fas fa-brain text-primary me-2"></i>Reportes Estadísticos — Psicología
                        </h1>
                        <button id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-question-circle me-1"></i>Ayuda
                        </button>
                    </div>

                    <!-- Tarjetas de resumen (stats.js → api/reportes/stats) -->
                    <div class="row mb-4">
                        <?php
                        $tarjetasReporte = [
                            ['color' => 'primary', 'titulo' => 'Morbilidad psicológica', 'stat' => 'morbilidad_total', 'icon' => 'fa-brain'],
                            ['color' => 'info', 'titulo' => 'Citas registradas', 'stat' => 'citas_total', 'icon' => 'fa-calendar'],
                            ['color' => 'success', 'titulo' => 'Citas atendidas', 'stat' => 'citas_atendidas', 'icon' => 'fa-check'],
                            ['color' => 'warning', 'titulo' => 'Citas pendientes', 'stat' => 'citas_pendientes', 'icon' => 'fa-clock'],
                        ];
                        foreach ($tarjetasReporte as $card) {
                            include BASE_PATH . '/app/Views/inicio/components/stat_card.php';
                        }
                        ?>
                    </div>

                    <!-- Filtros -->
                    <div class="card shadow mb-4 border-start border-primary border-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i>Filtros de Citas y Atenciones Psicología</h6>
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
                                        <label class="form-label font-weight-bold small">PNF:</label>
                                        <select id="pnf" name="pnf" class="form-select form-select-sm">
                                            <option value="">Todos los PNF</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold small">Tipo de Consulta:</label>
                                        <select id="tipo_consulta" name="tipo_consulta" class="form-select form-select-sm">
                                            <option value="">Todas (morbilidad)</option>
                                            <option value="Diagnóstico">Diagnóstico</option>
                                            <option value="Retiro temporal">Retiro temporal</option>
                                            <option value="Cambio de carrera">Cambio de carrera</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-weight-bold small">Estado Cita:</label>
                                        <select id="estado_cita" name="estado" class="form-select form-select-sm">
                                            <option value="">Todas</option>
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

                    <!-- Resultados -->
                    <div id="contenedor_psicologia" style="display: none;">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Estadísticas de Citas Psicológicas</h6>
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
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Estado de Citas</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartPsEstado"></canvas></div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h6 class="text-center font-weight-bold text-primary mb-3">Morbilidad por Tipo de Consulta</h6>
                                        <div style="position: relative; height:260px;"><canvas id="chartPsTipo"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Registro Detallado de Morbilidad</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla_morbilidad" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Paciente</th>
                                                <th>Cédula</th>
                                                <th>PNF</th>
                                                <th>Tipo de Consulta</th>
                                                <th>Diagnóstico</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyMorbilidad"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Registro Detallado de Citas</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla_psicologia" class="table table-bordered table-striped table-hover align-middle text-center w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Paciente</th>
                                                <th>Cédula</th>
                                                <th>PNF</th>
                                                <th>Psicólogo</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyPsicologia"></tbody>
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
        window.REPORTES_TIPO = 'psicologia';
    </script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/comunes.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/pdf-completo.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/reportes/psicologia.js" defer></script>
</body>
</html>
