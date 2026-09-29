<?php
// app/Views/referencias/consultar.php
$titulo = "Consultar Referencias";
$esAdmin = in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true);
$idEmpleadoSesion = (int) ($_SESSION['id_empleado'] ?? 0);
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
                        <h1 class="h3 mb-0 text-gray-800">Gestionar Referencias</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <a href="<?= BASE_URL ?>referencias/crear" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus me-1"></i> Nueva referencia
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include 'app/Views/referencias/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaReferencias" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Beneficiario</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Fecha</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyReferencias"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- ==================== MODAL RECHAZO ==================== -->
    <div class="modal fade" id="modalRechazo" tabindex="-1" aria-labelledby="modalRechazoLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-danger text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-ban text-danger"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalRechazoLabel">Rechazar referencia</h5>
                            <small class="opacity-75" id="rechazoReferencia"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-rechazo" novalidate>
                    <input type="hidden" name="rechazo_id_referencia" id="rechazo_id_referencia">

                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label for="rechazo_beneficiario" class="form-label text-muted small mb-1">Beneficiario</label>
                            <input type="text" class="form-control" id="rechazo_beneficiario" readonly>
                        </div>

                        <div class="mb-2">
                            <label for="rechazo_motivo" class="form-label text-muted small mb-1">
                                <i class="fas fa-comment-dots text-danger me-1"></i> Motivo del rechazo *
                            </label>
                            <textarea class="form-control" name="rechazo_motivo" id="rechazo_motivo"
                                      rows="3" maxlength="2000" placeholder="Explica por qué se rechaza la referencia…" required></textarea>
                            <div id="rechazo_motivoError" class="form-text text-danger"></div>
                        </div>
                        <div class="form-text text-muted">
                            <i class="fas fa-circle-info me-1"></i>
                            El motivo queda en el historial de la referencia y no puede quedar vacío.
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban me-1"></i> Rechazar referencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <script>
        window.REFERENCIAS_ES_ADMIN = <?= $esAdmin ? 'true' : 'false' ?>;
        window.REFERENCIAS_ID_EMPLEADO = <?= $idEmpleadoSesion ?>;
    </script>

    <!-- Módulo Referencias: stats + detalle/acciones + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/consultar.js" defer></script>
</body>

</html>
