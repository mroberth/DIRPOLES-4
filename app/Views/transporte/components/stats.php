<?php
/**
 * app/Views/transporte/components/stats.php
 * ---------------------------------------------------------------
 * Tarjetas de estadísticas para el módulo de Transporte.
 */
$cards = [
    [
        'color'  => 'primary',
        'titulo' => 'Total Vehículos',
        'stat'   => 'total_vehiculos',
        'icon'   => 'fa-bus',
    ],
    [
        'color'  => 'success',
        'titulo' => 'Vehículos Activos',
        'stat'   => 'vehiculos_activos',
        'icon'   => 'fa-circle-check',
    ],
    [
        'color'  => 'warning',
        'titulo' => 'En Mantenimiento',
        'stat'   => 'vehiculos_mantenimiento',
        'icon'   => 'fa-wrench',
    ],
    [
        'color'  => 'info',
        'titulo' => 'Rutas Activas',
        'stat'   => 'rutas_activas',
        'icon'   => 'fa-route',
    ],
    [
        'color'  => 'secondary',
        'titulo' => 'Proveedores',
        'stat'   => 'total_proveedores',
        'icon'   => 'fa-truck-field',
    ],
    [
        'color'  => 'danger',
        'titulo' => 'Repuestos Bajo Stock',
        'stat'   => 'repuestos_bajo_stock',
        'icon'   => 'fa-triangle-exclamation',
    ],
];
?>
<div class="row g-3 mb-4">
    <?php foreach ($cards as $card): ?>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-left-<?= htmlspecialchars($card['color']) ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
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
