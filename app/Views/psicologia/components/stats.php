<?php
$tarjetas = [
    ['clave' => 'total', 'texto' => 'Total consultas', 'color' => 'primary', 'icono' => 'fa-brain'],
    ['clave' => 'diagnosticos', 'texto' => 'Diagnósticos', 'color' => 'info', 'icono' => 'fa-file-medical'],
    ['clave' => 'retiros', 'texto' => 'Retiros temporales', 'color' => 'warning', 'icono' => 'fa-person-walking-arrow-right'],
    ['clave' => 'cambios', 'texto' => 'Cambios de carrera', 'color' => 'success', 'icono' => 'fa-right-left'],
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