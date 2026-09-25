<?php
$tarjetas = [
    ['clave' => 'total',                 'texto' => 'Consultas médicas',       'color' => 'primary', 'icono' => 'fa-stethoscope'],
    ['clave' => 'insumos_disponibles',   'texto' => 'Insumos disponibles',     'color' => 'info',    'icono' => 'fa-box-open'],
    ['clave' => 'insumos_bajo_stock',    'texto' => 'Insumos con bajo stock',  'color' => 'warning', 'icono' => 'fa-exclamation-triangle'],
    ['clave' => 'beneficiarios_atendidos', 'texto' => 'Beneficiarios atendidos', 'color' => 'success', 'icono' => 'fa-user-injured'],
];
foreach ($tarjetas as $tarjeta): ?>
<div class="col-xl-3 col-md-6 mb-3">
    <div class="card border-left-<?= $tarjeta['color'] ?> shadow h-100 py-2">
        <div class="card-body"><div class="row no-gutters align-items-center">
            <div class="col mr-2">
                <div class="text-xs font-weight-bold text-<?= $tarjeta['color'] ?> text-uppercase mb-1"><?= $tarjeta['texto'] ?></div>
                <div class="h5 mb-0 font-weight-bold text-gray-800" data-stat="<?= $tarjeta['clave'] ?>">0</div>
            </div>
            <div class="col-auto"><i class="fas <?= $tarjeta['icono'] ?> fa-2x text-gray-300"></i></div>
        </div></div>
    </div>
</div>
<?php endforeach; ?>
