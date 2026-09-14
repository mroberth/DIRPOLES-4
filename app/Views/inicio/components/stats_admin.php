<?php
// Resumen operativo global (Administrador / Superusuario).
$cards = [
    ['color' => 'secondary', 'titulo' => 'Psicología',     'stat' => 'admin_psicologia_total',   'icon' => 'fa-brain',        'suffix' => ' Cons.'],
    ['color' => 'danger',    'titulo' => 'Medicina',       'stat' => 'admin_medicina_total',     'icon' => 'fa-stethoscope',  'suffix' => ' Cons.'],
    ['color' => 'warning',   'titulo' => 'Orientación',    'stat' => 'admin_orientacion_total',  'icon' => 'fa-comments',     'suffix' => ' Cons.'],
    ['color' => 'success',   'titulo' => 'Trabajo Social', 'stat' => 'admin_ts_total',           'icon' => 'fa-hand-holding-heart', 'suffix' => ' Casos'],
    ['color' => 'primary',   'titulo' => 'Discapacidad',   'stat' => 'admin_discapacidad_total', 'icon' => 'fa-wheelchair',   'suffix' => ' Casos'],
    ['color' => 'info',      'titulo' => 'Referencias',    'stat' => 'admin_referidos_total',    'icon' => 'fa-share-nodes',  'suffix' => ' Ref.'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
