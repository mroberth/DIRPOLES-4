<?php
// app/Views/ayuda/ayuda.php

$titulo = "Centro de Ayuda";
include BASE_PATH . '/app/Views/template/head.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>dist/css/modulos/ayuda/ayuda.css">

<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>

                <!-- Hero Header Section -->
                <div class="container-fluid px-4">
                    <div class="help-hero text-center position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm fw-bold">
                                <i class="fa-solid fa-circle-info me-1"></i> Asistencia en línea
                            </span>
                            <button type="button" id="btnDescargarManual" class="btn btn-sm btn-light text-primary fw-bold px-3 py-2 rounded-pill shadow-sm">
                                <i class="fa-solid fa-file-pdf me-1"></i> Manual de Usuario
                            </button>
                        </div>
                        <h1 class="help-hero-title mb-2">Hola, <?= htmlspecialchars($nombre_usuario) ?>. 👋</h1>
                        <p class="help-hero-subtitle mb-0">¿En qué podemos ayudarte hoy en DIRPOLES-4?</p>

                        <div class="help-search-container">
                            <i class="fa-solid fa-magnifying-glass help-search-icon"></i>
                            <input type="text" id="helpSearch" class="form-control help-search-input" placeholder="Busca un módulo, pregunta o palabra clave (ej. citas, recetas, repuestos, bitácora)...">
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="container-fluid px-4 style-content-wrapper" style="margin-top: 4rem;">
                    
                    <!-- Chips de Filtrado por Área/Rol -->
                    <div class="help-filters-nav" id="helpFilters">
                        <button type="button" class="help-filter-chip active" data-filter="all">
                            <i class="fa-solid fa-border-all me-1"></i> Todos los Temas
                        </button>
                        <button type="button" class="help-filter-chip" data-filter="general">
                            <i class="fa-solid fa-compass me-1"></i> General e Inicio
                        </button>
                        <button type="button" class="help-filter-chip" data-filter="salud">
                            <i class="fa-solid fa-heart-pulse me-1"></i> Salud y Medicina
                        </button>
                        <button type="button" class="help-filter-chip" data-filter="psicologia">
                            <i class="fa-solid fa-brain me-1"></i> Psicología y Citas
                        </button>
                        <button type="button" class="help-filter-chip" data-filter="admin">
                            <i class="fa-solid fa-user-shield me-1"></i> Administración y Seguridad
                        </button>
                    </div>

                    <!-- Contenedor Dinámico de Tarjetas por Categoría -->
                    <div class="row" id="helpTopics">
                        <!-- Las tarjetas se renderizan dinámicamente desde dist/js/modulos/ayuda/ayuda.js -->
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando guía...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando temas de ayuda...</p>
                        </div>
                    </div>

                </div>
            </div>

            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- Modal para Renderizado de Guía Paso a Paso -->
    <div class="modal fade" id="modalGuia" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header guide-modal-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="guide-breadcrumb" id="guiaBreadcrumb">
                            <span>Centro de Ayuda</span>
                            <i class="fa-solid fa-chevron-right fa-xs mx-1"></i>
                            <span id="guiaCategory" class="text-primary">Categoría</span>
                        </div>
                        <h4 class="modal-title fw-bold text-dark mb-0" id="guiaTitle">Título de la Guía</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body guide-modal-body" id="guiaContent">
                    <!-- Pasos inyectados por JS -->
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4 px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary fw-bold px-4 rounded-pill" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button type="button" class="btn btn-primary fw-bold px-4 rounded-pill" id="btnIrModulo" style="display: none;">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ir al Módulo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>

    <!-- JS del Módulo -->
    <script src="<?= BASE_URL ?>dist/js/modulos/ayuda/ayuda-data.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/ayuda/ayuda.js" defer></script>
</body>
</html>
