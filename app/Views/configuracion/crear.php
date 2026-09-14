<?php
// app/Views/configuracion/crear.php
$titulo = "Configuración";
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
                        <h1 class="h3 mb-0 text-gray-800">Gestionar Configuraciones</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>configuracion/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <div class="row" id="config-cards">
                        <?php foreach ($catalogos as $tipo => $cfg): ?>
                            <div class="col-lg-6 mb-4" id="card-config-<?= htmlspecialchars($tipo) ?>">
                                <div class="card shadow h-100 border-left-<?= htmlspecialchars($cfg['color']) ?>">
                                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-<?= htmlspecialchars($cfg['color']) ?>">
                                            <i class="fas <?= htmlspecialchars($cfg['icono']) ?> me-2"></i>
                                            Registro de <?= htmlspecialchars($cfg['titulo']) ?>
                                        </h6>
                                        <span class="badge bg-<?= htmlspecialchars($cfg['color']) ?>">
                                            <?= htmlspecialchars($cfg['badge']) ?>
                                        </span>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <form class="form-config d-flex flex-column h-100"
                                              data-tipo="<?= htmlspecialchars($tipo) ?>"
                                              data-unico="<?= htmlspecialchars(implode(',', $cfg['unico'] ?? [])) ?>"
                                              novalidate>
                                            <?php include BASE_PATH . '/app/Views/configuracion/components/form_campos.php'; ?>

                                            <div class="text-end mt-auto pt-2">
                                                <button type="submit" class="btn btn-<?= htmlspecialchars($cfg['color']) ?>">
                                                    <i class="fas fa-save me-1"></i> Registrar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <!-- Módulo Configuración: tour + validaciones + envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/configuracion/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/configuracion/crear.js" defer></script>
</body>

</html>
