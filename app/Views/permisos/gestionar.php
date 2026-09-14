<?php
// app/Views/permisos/gestionar.php
$titulo = "Permisos";
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
                        <h1 class="h3 mb-0 text-gray-800">Gestionar Permisos de Empleados</h1>
                        <span class="text-muted small d-none d-sm-inline">
                            <i class="fas fa-info-circle me-1"></i>Marca los permisos por rol y módulo
                        </span>
                    </div>

                    <!-- Tarjetas de resumen -->
                    <?php include 'app/Views/permisos/components/stats.php'; ?>

                    <!-- Barra de acciones -->
                    <div id="permisos-toolbar" class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button type="button" id="permisos-guardar" class="btn btn-primary me-2" disabled>
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                            <button type="button" id="permisos-cancelar" class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-undo me-1"></i> Cancelar cambios
                            </button>
                        </div>
                        <small class="text-muted">Cambios pendientes: <span id="permisos-pendientes">0</span></small>
                    </div>

                    <!-- La matriz la renderiza permisos_manager.js bajo demanda -->
                    <div class="row align-items-start" id="dashboard-permisos">
                        <div class="col-12 text-center text-muted py-5" id="permisos-loading">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2 mb-0">Cargando permisos…</p>
                        </div>
                    </div>

                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <!-- Módulo Permisos: stats + gestor por lote (render bajo demanda) -->
    <script src="<?= BASE_URL ?>dist/js/modulos/permisos/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/permisos/permisos_manager.js" defer></script>
</body>

</html>
