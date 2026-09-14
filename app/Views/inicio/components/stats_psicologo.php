<?php
// Estadísticas del rol Psicólogo.
$cards = [
    ['color' => 'primary', 'titulo' => 'Diagnósticos Totales', 'stat' => 'total_diagnosticos', 'icon' => 'fa-file-medical'],
    ['color' => 'info',    'titulo' => 'Citas del Mes',        'stat' => 'citas_mes',          'icon' => 'fa-calendar-alt'],
    ['color' => 'warning', 'titulo' => 'Retiros Temporales',   'stat' => 'retiros_activos',    'icon' => 'fa-person-walking-arrow-right'],
    ['color' => 'success', 'titulo' => 'Cambios de Carrera',   'stat' => 'cambios_carrera',    'icon' => 'fa-right-left'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
