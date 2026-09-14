<?php
// Estadísticas del rol Trabajador Social.
$cards = [
    ['color' => 'primary', 'titulo' => 'Gestión de Embarazadas', 'stat' => 'total_embarazadas',   'icon' => 'fa-person-pregnant'],
    ['color' => 'info',    'titulo' => 'Exoneraciones',          'stat' => 'total_exoneraciones', 'icon' => 'fa-file-contract'],
    ['color' => 'success', 'titulo' => 'Ayudas FAMES',           'stat' => 'total_fames',         'icon' => 'fa-hand-holding-dollar'],
    ['color' => 'warning', 'titulo' => 'Becas',                  'stat' => 'total_becas',         'icon' => 'fa-graduation-cap'],
    ['color' => 'primary', 'titulo' => 'Embarazadas (Mes)',      'stat' => 'embarazadas_mes',     'icon' => 'fa-calendar-plus'],
    ['color' => 'info',    'titulo' => 'Exoneraciones (Mes)',    'stat' => 'exoneraciones_mes',   'icon' => 'fa-calendar-plus'],
    ['color' => 'success', 'titulo' => 'FAMES (Mes)',            'stat' => 'fames_mes',           'icon' => 'fa-calendar-plus'],
    ['color' => 'warning', 'titulo' => 'Becas (Mes)',            'stat' => 'becas_mes',           'icon' => 'fa-calendar-plus'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
