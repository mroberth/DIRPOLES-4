<?php
// Estadísticas del rol Orientador.
$cards = [
    ['color' => 'primary', 'titulo' => 'Sesiones Totales',       'stat' => 'total_conteo',       'icon' => 'fa-user-graduate'],
    ['color' => 'info',    'titulo' => 'Sesiones del Mes',       'stat' => 'orientacion_mes',    'icon' => 'fa-calendar-check'],
    ['color' => 'warning', 'titulo' => 'Sin Indicaciones',       'stat' => 'sin_indicaciones',   'icon' => 'fa-circle-exclamation'],
    ['color' => 'success', 'titulo' => 'Con Observaciones',      'stat' => 'con_observaciones',  'icon' => 'fa-comment-dots'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
