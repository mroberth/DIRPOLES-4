<?php
/**
 * Tarjeta de estadística reutilizable.
 * Espera `$card` con: color, titulo, stat, icon y opcionalmente suffix.
 * El valor lo rellena dist/js/modulos/dashboard/dashboard_stats.js
 * buscando el nodo [data-stat].
 *
 * @var array{color:string,titulo:string,stat:string,icon:string,suffix?:string} $card
 */
$suffix = $card['suffix'] ?? '';
?>
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-<?= htmlspecialchars($card['color']) ?> shadow h-100 py-2">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                    <div class="text-xs font-weight-bold text-<?= htmlspecialchars($card['color']) ?> text-uppercase mb-1">
                        <?= htmlspecialchars($card['titulo']) ?>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <span data-stat="<?= htmlspecialchars($card['stat']) ?>">0</span><?= htmlspecialchars($suffix) ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas <?= htmlspecialchars($card['icon']) ?> fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
