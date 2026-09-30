<?php
/**
 * app/Views/inicio/dashboard.php
 * ---------------------------------------------------------------
 * Panel de Inicio. UNA sola vista (shell) para todos los roles:
 * el contenido se compone según el rol/permisos del usuario.
 *
 *   1. $titulo se define ANTES de incluir head.php.
 *   2. head.php abre <html><head> y carga los CSS.
 *   3. sidebar.php (menú dinámico) + header.php (topbar).
 *   4. Contenido: Hero Banner, Stat Cards (KPI), Gráfico de Operaciones, Feed de Actividad, Calendario.
 *   5. footer.php + script.php + JS del módulo.
 */
$titulo = "Inicio";
include 'app/Views/template/head.php';

$tipo    = $_SESSION['tipo_empleado'] ?? '';
$esAdmin = in_array($tipo, ['Administrador', 'Superusuario'], true);
?>

<body id="page-top">
    <div id="wrapper">

        <!-- ===== Menú lateral (dinámico según permisos) ===== -->
        <?php include 'app/Views/template/sidebar.php'; ?>

        <!-- ===== Contenido ===== -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <!-- Topbar (usuario + cerrar sesión) -->
                <?php include 'app/Views/template/header.php'; ?>

                <!-- Contenido de la página -->
                <div class="container-fluid">

                    <!-- Banner de Bienvenida Dinámico -->
                    <div class="card bg-gradient-primary text-white shadow mb-4 border-0 overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative" style="z-index: 2;">
                            <div class="row align-items-center">
                                <div class="col-lg-8 mb-3 mb-lg-0">
                                    <h2 class="font-weight-bold mb-2 text-white">
                                        <span id="saludo-tiempo">¡Bienvenido!</span>, <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']) ?> 👋
                                    </h2>
                                    <p class="mb-0 text-white-50">
                                        Sistema Integral de Gestión de Beneficiarios UPTAEB — Dirección de Políticas Estudiantiles (DIRPOLES).
                                    </p>
                                </div>
                                <div class="col-lg-4 text-lg-end">
                                    <div class="d-inline-flex align-items-center px-3 py-2 text-white shadow-sm mb-2" style="background: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 0.5rem;">
                                        <i class="fas fa-calendar-day fa-lg me-2 text-white"></i>
                                        <div class="text-start">
                                            <div class="small font-weight-bold text-uppercase opacity-75 text-white">Hoy es</div>
                                            <div class="font-weight-bold small text-white" id="fecha-hoy-banner"><?= date('d/m/Y') ?></div>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge bg-light text-primary font-weight-bold px-3 py-2 shadow-sm">
                                            <i class="fas fa-user-shield me-1"></i><?= htmlspecialchars($tipo) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Operativo KPI Cards -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="font-weight-bold text-gray-800 mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>Resumen Operativo
                        </h5>
                    </div>
                    <div class="row mb-4">
                        <?php if ($esAdmin): ?>
                            <?php include 'app/Views/inicio/components/stats_admin.php'; ?>
                        <?php else: ?>
                            <?php
                            $archivoRol = match ($tipo) {
                                'Psicologo'         => 'stats_psicologo.php',
                                'Medico'            => 'stats_medico.php',
                                'Orientador'        => 'stats_orientador.php',
                                'Trabajador Social' => 'stats_trabajador_social.php',
                                'Discapacidad'      => 'stats_discapacidad.php',
                                default             => 'stats_generico.php',
                            };
                            include 'app/Views/inicio/components/' . $archivoRol;
                            ?>
                        <?php endif; ?>
                    </div>

                    <!-- Sección Analytics + Feed Reciente -->
                    <div class="row mb-4">
                        <!-- Columna Izquierda: Gráfico Estadístico del Panel -->
                        <div class="col-lg-7 mb-4">
                            <div class="card shadow h-100 border-0">
                                <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-pie me-2"></i>Distribución de Atenciones
                                    </h6>
                                    <span class="badge bg-primary text-white">Tiempo Real</span>
                                </div>
                                <div class="card-body">
                                    <div style="position: relative; height:280px;">
                                        <canvas id="chartDashboardOperativo"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Feed de Notificaciones y Avisos -->
                        <div class="col-lg-5 mb-4">
                            <div class="card shadow h-100 border-0">
                                <div class="card-header py-3 bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-bell me-2"></i>Avisos & Actividad Reciente
                                    </h6>
                                    <span class="badge bg-info text-dark" id="badge-total-notif">0 avisos</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush" id="feed-notificaciones-dashboard">
                                        <div class="p-4 text-center text-muted">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Cargando actividad reciente...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ CALENDARIO (todos los roles) ============ -->
                    <div class="row">
                        <?php include 'app/Views/inicio/components/calendario.php'; ?>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>

            <!-- Pie de página -->
            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- Scripts globales (jQuery, Bootstrap, SweetAlert2, JWT refresh...) -->
    <?php include 'app/Views/template/script.php'; ?>

    <!-- Chart.js para el gráfico del panel -->
    <script src="<?= BASE_URL ?>dist/js/dashboard/Chart.min.js"></script>

    <!-- Módulo: Dashboard (stats por API, gráfico + calendario personal) -->
    <script src="<?= BASE_URL ?>dist/js/modulos/dashboard/dashboard_stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/calendario/calendario_personal.js" defer></script>
</body>

</html>
