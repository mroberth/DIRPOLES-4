<?php
// app/Views/configuracion/consultar.php
$titulo = "Consultar Configuración";
include 'app/Views/template/head.php';

// Quita el artículo inicial para usar como encabezado de columna.
$encabezado = static function (string $label): string {
    return preg_replace('/^(La|El|Los|Las)\s+/u', '', trim($label));
};
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Configuraciones del Sistema</h1>
                        <a href="<?= BASE_URL ?>configuracion/crear" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus me-1"></i> Nuevo registro
                        </a>
                    </div>

                    <!-- Pestañas por catálogo -->
                    <ul class="nav nav-pills mb-3" id="config-tabs" role="tablist">
                        <?php $i = 0; foreach ($catalogos as $tipo => $cfg): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                                        id="tab-<?= htmlspecialchars($tipo) ?>"
                                        data-bs-toggle="pill" data-bs-target="#pane-<?= htmlspecialchars($tipo) ?>"
                                        data-tipo="<?= htmlspecialchars($tipo) ?>" type="button" role="tab">
                                    <i class="fas <?= htmlspecialchars($cfg['icono']) ?> me-1"></i>
                                    <?= htmlspecialchars($cfg['titulo']) ?>
                                </button>
                            </li>
                        <?php $i++; endforeach; ?>
                    </ul>

                    <div class="tab-content" id="config-tabs-content">
                        <?php $i = 0; foreach ($catalogos as $tipo => $cfg): ?>
                            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                                 id="pane-<?= htmlspecialchars($tipo) ?>" role="tabpanel"
                                 data-tipo="<?= htmlspecialchars($tipo) ?>">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="tabla-<?= htmlspecialchars($tipo) ?>"
                                                   class="table table-bordered table-hover align-middle" width="100%">
                                                <thead class="table-light">
                                                    <tr>
                                                        <?php foreach ($cfg['campos'] as $campo): ?>
                                                            <th><?= htmlspecialchars($encabezado($campo['label'])) ?></th>
                                                        <?php endforeach; ?>
                                                        <?php if (!empty($cfg['con_estatus'])): ?><th class="text-center">Estatus</th><?php endif; ?>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php $i++; endforeach; ?>
                    </div>

                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- Modales de edición (uno por catálogo) -->
    <?php foreach ($catalogos as $tipo => $cfg): ?>
        <div class="modal fade" id="modal-<?= htmlspecialchars($tipo) ?>" tabindex="-1"
             aria-labelledby="modalLabel-<?= htmlspecialchars($tipo) ?>" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-<?= htmlspecialchars($cfg['color']) ?> text-white py-3">
                        <h5 class="modal-title" id="modalLabel-<?= htmlspecialchars($tipo) ?>">
                            <i class="fas <?= htmlspecialchars($cfg['icono']) ?> me-2"></i>Editar <?= htmlspecialchars($cfg['titulo']) ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="form-edit-<?= htmlspecialchars($tipo) ?>"
                          data-tipo="<?= htmlspecialchars($tipo) ?>"
                          data-unico="<?= htmlspecialchars(implode(',', $cfg['unico'] ?? [])) ?>"
                          novalidate>
                        <input type="hidden" name="id" id="<?= htmlspecialchars($tipo) ?>_id">
                        <div class="modal-body">
                            <?php
                            $modo = 'editar';
                            include BASE_PATH . '/app/Views/configuracion/components/form_campos.php';
                            ?>
                        </div>
                        <div class="modal-footer bg-light py-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-<?= htmlspecialchars($cfg['color']) ?>">
                                <i class="fas fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php include 'app/Views/template/script.php'; ?>

    <script>window.CONFIG_CATALOGOS = <?= json_encode($catalogos, JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= BASE_URL ?>dist/js/modulos/configuracion/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/configuracion/consultar.js" defer></script>
</body>

</html>
