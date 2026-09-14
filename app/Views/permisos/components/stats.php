<?php
/**
 * app/Views/permisos/components/stats.php
 * Tarjetas de resumen del módulo Permisos.
 * Los valores los rellena dist/js/modulos/permisos/stats.js desde [data-stat].
 */
$cards = [
    ['color' => 'info',    'titulo' => 'Roles con permisos',  'stat' => 'permisos_roles',  'icon' => 'fa-users-gear'],
    ['color' => 'primary', 'titulo' => 'Permisos otorgados',  'stat' => 'permisos_total',  'icon' => 'fa-key'],
    ['color' => 'warning', 'titulo' => 'Módulo más usado',    'stat' => 'permisos_modulo', 'icon' => 'fa-layer-group'],
    ['color' => 'success', 'titulo' => 'Acción más frecuente','stat' => 'permisos_accion', 'icon' => 'fa-shield-halved'],
];
?>
<div class="row">
    <?php foreach ($cards as $card): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?= htmlspecialchars($card['color']) ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-<?= htmlspecialchars($card['color']) ?> text-uppercase mb-1">
                                <?= htmlspecialchars($card['titulo']) ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span data-stat="<?= htmlspecialchars($card['stat']) ?>">0</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas <?= htmlspecialchars($card['icon']) ?> fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
