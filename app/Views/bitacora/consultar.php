<?php
// app/Views/bitacora/consultar.php
$titulo = "Consultar Bitácora";
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
                        <h1 class="h3 mb-0 text-gray-800">Bitácora del Sistema</h1>
                        <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-circle-question me-1"></i> Ayuda
                        </button>
                    </div>

                    <!-- Tarjetas de resumen -->
                    <?php include 'app/Views/bitacora/components/stats.php'; ?>

                    <!-- Filtros -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-filter me-2"></i>Filtros
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Módulo</label>
                                    <select id="f-modulo" class="form-select select2" data-placeholder="Todos">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Acción</label>
                                    <select id="f-accion" class="form-select select2" data-placeholder="Todas">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Empleado</label>
                                    <select id="f-empleado" class="form-select select2" data-placeholder="Todos">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Buscar</label>
                                    <input type="text" id="f-buscar" class="form-control" placeholder="Descripción, módulo, acción…">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Desde</label>
                                    <input type="date" id="f-desde" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Hasta</label>
                                    <input type="date" id="f-hasta" class="form-control">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" id="btn-filtrar" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                    <button type="button" id="btn-limpiar" class="btn btn-outline-secondary">
                                        <i class="fas fa-eraser me-1"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla_bitacora" class="table table-striped table-bordered align-middle" width="100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Módulo</th>
                                            <th>Empleado</th>
                                            <th>Acción</th>
                                            <th>Descripción</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <!-- Módulo Bitácora: stats + filtros + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/bitacora/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/bitacora/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/bitacora/consultar.js" defer></script>
</body>

</html>
