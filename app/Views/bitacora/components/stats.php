<?php
/**
 * app/Views/bitacora/components/stats.php
 * Tarjetas de resumen de la Bitácora.
 * Los valores los rellena dist/js/modulos/bitacora/stats.js desde [data-stat].
 */
$cards = [
    ['color' => 'primary', 'titulo' => 'Movimientos totales', 'stat' => 'bitacora_total',  'icon' => 'fa-clipboard-list'],
    ['color' => 'info',    'titulo' => 'Movimientos hoy',     'stat' => 'bitacora_hoy',    'icon' => 'fa-calendar-day'],
    ['color' => 'success', 'titulo' => 'Movimientos del mes', 'stat' => 'bitacora_mes',    'icon' => 'fa-calendar-alt'],
    ['color' => 'warning', 'titulo' => 'Módulo más activo',   'stat' => 'bitacora_modulo', 'icon' => 'fa-layer-group'],
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
