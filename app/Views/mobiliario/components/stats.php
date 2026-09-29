<?php
/**
 * app/Views/mobiliario/components/stats.php
 * Tarjetas de resumen del módulo Mobiliario (id_modulo 12).
 * Los valores los rellena dist/js/modulos/mobiliario/stats.js desde [data-stat].
 */
$cards = [
    ['color' => 'primary', 'titulo' => 'Unidades de mobiliario', 'stat' => 'total_mobiliarios', 'icon' => 'fa-chair'],
    ['color' => 'success', 'titulo' => 'Equipos registrados',    'stat' => 'total_equipos',     'icon' => 'fa-laptop'],
    ['color' => 'info',    'titulo' => 'Fichas activas',         'stat' => 'fichas_activas',    'icon' => 'fa-file-signature'],
    ['color' => 'warning', 'titulo' => 'Altas del mes',          'stat' => 'inventario_mes',    'icon' => 'fa-calendar-plus'],
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
