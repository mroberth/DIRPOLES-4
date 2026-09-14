<?php
// Estadísticas del rol Discapacidad.
$cards = [
    ['color' => 'primary', 'titulo' => 'Discapacidades Totales', 'stat' => 'total_discapacidades',  'icon' => 'fa-wheelchair'],
    ['color' => 'info',    'titulo' => 'Discapacidades (Mes)',   'stat' => 'discapacidades_mes',    'icon' => 'fa-calendar-alt'],
    ['color' => 'danger',  'titulo' => 'Discapacidades Graves',  'stat' => 'discapacidades_graves', 'icon' => 'fa-triangle-exclamation'],
    ['color' => 'success', 'titulo' => 'Con Carnet',             'stat' => 'con_carnet',            'icon' => 'fa-id-card'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
