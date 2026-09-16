<?php
$titulo = 'Consultar Citas';
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
                        <h1 class="h3 mb-0 text-gray-800">Citas</h1>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-recargar-citas" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-rotate-right me-1"></i>Recargar
                            </button>
                            <a href="<?= BASE_URL ?>citas/crear" class="btn btn-sm btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>Nueva cita
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4" id="citas-stats">
                        <?php foreach ([
                            ['total', 'Total', 'primary'], ['pendientes', 'Pendientes', 'warning'],
                            ['confirmadas', 'Confirmadas', 'info'], ['atendidas', 'Atendidas', 'success'],
                        ] as [$clave, $texto, $color]): ?>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-<?= $color ?> shadow h-100 py-2">
                                    <div class="card-body py-2">
                                        <div class="text-xs font-weight-bold text-<?= $color ?> text-uppercase mb-1"><?= $texto ?></div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" data-stat="<?= $clave ?>">0</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla-citas" class="table table-bordered table-hover align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th><th>Hora</th><th>Beneficiario</th><th>Psicólogo</th><th>Estado</th><th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <div class="modal fade" id="modal-editar-cita" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-editar-cita" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="editar_id_cita" name="id_cita">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editar_id_beneficiario" class="form-label">Beneficiario *</label>
                                <select id="editar_id_beneficiario" name="id_beneficiario" class="form-select" required <?= $esAdmin ? '' : 'disabled' ?>></select>
                                <div id="editar_id_beneficiarioError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_id_empleado" class="form-label">Psicólogo *</label>
                                <select id="editar_id_empleado" name="id_empleado" class="form-select" required <?= $esAdmin ? '' : 'disabled' ?>></select>
                                <div id="editar_id_empleadoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_fecha" class="form-label">Fecha *</label>
                                <input type="date" id="editar_fecha" name="fecha" class="form-control" required>
                                <div id="editar_fechaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_hora" class="form-label">Hora *</label>
                                <input type="time" id="editar_hora" name="hora" class="form-control" step="1800" required>
                                <div id="editar_horaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editar_estatus" class="form-label">Estado *</label>
                                <select id="editar_estatus" name="estatus" class="form-select" required></select>
                                <div id="editar_estatusError" class="form-text text-danger"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>
    <script>window.CITAS_ES_ADMIN = <?= $esAdmin ? 'true' : 'false' ?>;</script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/cita/consultar.js" defer></script>
</body>
</html>
