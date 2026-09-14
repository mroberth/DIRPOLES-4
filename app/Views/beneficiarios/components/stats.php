<?php
/**
 * app/Views/beneficiarios/components/stats.php
 * Tarjetas de resumen del módulo Beneficiarios.
 * Los valores los rellena dist/js/modulos/beneficiario/stats.js desde [data-stat].
 */
$cards = [
    ['color' => 'primary', 'titulo' => 'Beneficiarios Totales', 'stat' => 'beneficiarios_total',      'icon' => 'fa-users'],
    ['color' => 'success', 'titulo' => 'Activos',               'stat' => 'beneficiarios_activos',    'icon' => 'fa-user-check'],
    ['color' => 'danger',  'titulo' => 'Inactivos',             'stat' => 'beneficiarios_inactivos',  'icon' => 'fa-user-slash'],
    ['color' => 'info',    'titulo' => 'Nuevos este mes',       'stat' => 'beneficiarios_nuevos_mes', 'icon' => 'fa-user-plus'],
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
