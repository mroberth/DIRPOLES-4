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
 *   4. Contenido: cards de módulos (admin) o stats del rol, + calendario.
 *   5. footer.php + script.php + JS del módulo.
 *
 * El controlador (showInicio) ya dejó en sesión `modulosPermitidos`.
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

                    <!-- Encabezado de página -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']) ?>
                        </h1>
                        <span class="text-muted small d-none d-sm-inline">
                            <i class="fas fa-user-tag me-1"></i><?= htmlspecialchars($tipo) ?>
                        </span>
                    </div>

                    <?php if ($esAdmin): ?>
                        <!-- ============ ADMIN: control por módulos ============ -->
                        <h2 class="h5 mb-3 text-gray-700">Control por módulos</h2>
                        <div class="row">
                            <?php
                            $cardsModulo = require BASE_PATH . 'app/Config/dashboard_cards.php';
                            foreach ($_SESSION['modulosPermitidos'] as $idModulo => $infoModulo):
                                if (!isset($cardsModulo[$idModulo])) {
                                    continue;
                                }
                                $c = $cardsModulo[$idModulo];
                                include 'app/Views/inicio/components/card_modulo.php';
                            endforeach;
                            ?>
                        </div>

                        <h2 class="h5 my-4 text-gray-700">Resumen operativo</h2>
                        <div class="row">
                            <?php include 'app/Views/inicio/components/stats_admin.php'; ?>
                        </div>

                    <?php else: ?>
                        <!-- ============ EMPLEADO: stats de su módulo ============ -->
                        <?php
                        $archivoRol = match ($tipo) {
                            'Psicologo'         => 'stats_psicologo.php',
                            'Medico'            => 'stats_medico.php',
                            'Orientador'        => 'stats_orientador.php',
                            'Trabajador Social' => 'stats_trabajador_social.php',
                            'Discapacidad'      => 'stats_discapacidad.php',
                            default             => 'stats_generico.php',
                        };
                        ?>
                        <div class="row">
                            <?php include 'app/Views/inicio/components/' . $archivoRol; ?>
                        </div>
                    <?php endif; ?>

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

    <!-- Módulo: Dashboard (stats por API + calendario personal) -->
    <script src="<?= BASE_URL ?>dist/js/modulos/dashboard/dashboard_stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/calendario/calendario_personal.js" defer></script>
</body>

</html>
