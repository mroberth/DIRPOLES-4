<?php
// Estadísticas del rol Médico.
$cards = [
    ['color' => 'primary', 'titulo' => 'Consultas Totales',    'stat' => 'total_conteo',        'icon' => 'fa-notes-medical'],
    ['color' => 'info',    'titulo' => 'Consultas del Mes',    'stat' => 'consultas_mes',       'icon' => 'fa-clock'],
    ['color' => 'success', 'titulo' => 'Insumos Disponibles',  'stat' => 'insumos_disponibles', 'icon' => 'fa-pills'],
    ['color' => 'danger',  'titulo' => 'Insumos Bajo Stock',   'stat' => 'insumos_bajo_stock',  'icon' => 'fa-triangle-exclamation'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
