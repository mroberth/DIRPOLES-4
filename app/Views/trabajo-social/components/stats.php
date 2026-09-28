<?php
$tarjetas = [
    ['clave' => 'total',               'texto' => 'Registros totales',       'color' => 'primary', 'icono' => 'fa-folder-open'],
    ['clave' => 'del_mes',             'texto' => 'Registros este mes',      'color' => 'info',    'icono' => 'fa-calendar-day'],
    ['clave' => 'pendientes_estudio',  'texto' => 'Exoneraciones sin estudio','color' => 'warning', 'icono' => 'fa-hourglass-half'],
    ['clave' => 'estudios_generados',  'texto' => 'Estudios generados',      'color' => 'success', 'icono' => 'fa-file-pdf'],
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
