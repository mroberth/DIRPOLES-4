<?php
// Resumen neutro para roles sin módulo clínico propio (Administrativo,
// Secretaría, Enfermero, Obreros, Chofer, Mecánico...).
$cards = [
    ['color' => 'success', 'titulo' => 'Beneficiarios Totales', 'stat' => 'total_beneficiarios', 'icon' => 'fa-person'],
    ['color' => 'info',    'titulo' => 'Citas de Hoy',          'stat' => 'citas_hoy',           'icon' => 'fa-calendar-day'],
];

foreach ($cards as $card) {
    include __DIR__ . '/stat_card.php';
}
