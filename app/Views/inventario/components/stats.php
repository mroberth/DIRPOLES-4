<?php
/**
 * app/Views/inventario/components/stats.php
 * Tarjetas de resumen del módulo Inventario Médico.
 * Los valores los rellena dist/js/modulos/inventario/stats.js desde [data-stat].
 */
$cards = [
    ['color' => 'primary', 'titulo' => 'Insumos totales',   'stat' => 'insumos_total',       'icon' => 'fa-boxes-stacked'],
    ['color' => 'success', 'titulo' => 'Disponibles',       'stat' => 'insumos_disponibles', 'icon' => 'fa-box-open'],
    ['color' => 'warning', 'titulo' => 'Por vencer (30 días)', 'stat' => 'insumos_por_vencer', 'icon' => 'fa-calendar-minus'],
    ['color' => 'danger',  'titulo' => 'Stock crítico',     'stat' => 'insumos_criticos',    'icon' => 'fa-triangle-exclamation'],
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
