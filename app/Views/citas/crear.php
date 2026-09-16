<?php
$titulo = 'Crear Cita';
$esAdmin = in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true);
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
                        <h1 class="h3 mb-0 text-gray-800">Crear cita</h1>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info" title="Ayuda">
                                <i class="fas fa-circle-question me-1"></i>Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>citas/consultar" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list me-1"></i>Consultar citas
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4" id="citas-stats">
                        <?php foreach ([
                            ['total', 'Total', 'primary', 'fa-calendar-check'],
                            ['pendientes', 'Pendientes', 'warning', 'fa-clock'],
                            ['confirmadas', 'Confirmadas', 'info', 'fa-circle-check'],
                            ['atendidas', 'Atendidas', 'success', 'fa-user-check'],
                        ] as [$clave, $texto, $color, $icono]): ?>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-<?= $color ?> shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-<?= $color ?> text-uppercase mb-1"><?= $texto ?></div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" data-stat="<?= $clave ?>">0</div>
                                            </div>
                                            <div class="col-auto"><i class="fas <?= $icono ?> fa-2x text-gray-300"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Registrar nueva cita</h6>
                        </div>
                        <div class="card-body">
                            <form id="form-cita" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="id_beneficiario" class="form-label">Beneficiario *</label>
                                        <select id="id_beneficiario" name="id_beneficiario" class="form-select select2" required>
                                            <option value="">Cargando beneficiarios...</option>
                                        </select>
                                        <div id="id_beneficiarioError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_empleado" class="form-label">Psicólogo *</label>
                                        <select id="id_empleado" name="id_empleado" class="form-select select2" required <?= $esAdmin ? '' : 'disabled' ?>>
                                            <option value="">Cargando psicólogos...</option>
                                        </select>
                                        <div id="id_empleadoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha" class="form-label">Fecha *</label>
                                        <input type="date" id="fecha" name="fecha" class="form-control" required>
                                        <div id="fechaError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hora" class="form-label">Hora de inicio *</label>
                                        <input type="time" id="hora" name="hora" class="form-control" step="1800" required>
                                        <div id="horaError" class="form-text text-danger"></div>
                                    </div>
                                </div>
                                <div id="horario-cita" class="alert alert-light border mt-4 mb-0">
                                    Selecciona un psicólogo para consultar su horario disponible.
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="reset" class="btn btn-outline-secondary"><i class="fas fa-eraser me-1"></i>Limpiar</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus me-1"></i>Registrar cita</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>
    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>
    <script>window.CITAS_ES_ADMIN = <?= $esAdmin ? 'true' : 'false' ?>;</script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/crear.js" defer></script>
</body>
</html>
