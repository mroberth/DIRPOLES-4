<?php
$titulo = "Acceso Denegado";
include BASE_PATH . '/app/Views/template/head.php';
?>

<?php require_once BASE_PATH . '/app/Views/template/header.php'; ?>

<div class="container-fluid">
    <!-- 403 Error Text -->
    <div class="text-center" style="margin-top: 10%;">
        <div class="error mx-auto" data-text="403">403</div>
        <p class="lead text-gray-800 mb-5">Acceso Denegado</p>
        <p class="text-gray-500 mb-0">Parece que no tienes permiso para acceder a esta página.</p>
        <a href="<?= BASE_URL ?>inicio">&larr; Volver al Inicio</a>
    </div>
</div>

<?php require_once BASE_PATH . '/app/Views/template/footer.php'; ?>
<?php require_once BASE_PATH . '/app/Views/template/script.php'; ?>