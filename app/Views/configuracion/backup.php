<?php
// app/Views/configuracion/backup.php
$titulo = "Respaldo de Base de Datos";
include 'app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-database text-primary me-2"></i>Respaldo y Copias de Seguridad
                        </h1>
                        <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-circle-question me-1"></i> Ayuda
                        </button>
                    </div>

                    <!-- Aviso -->
                    <div class="card shadow mb-4 border-left-info">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                            <div>
                                <h5 class="font-weight-bold text-info mb-1">Información importante</h5>
                                <p class="text-gray-700 mb-0">
                                    Genera y descarga copias de seguridad completas (estructura + datos) de las
                                    bases de datos. Guárdalas en un lugar seguro fuera del servidor.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- BD Negocio -->
                        <div class="col-lg-6 mb-4" id="card-respaldo-negocio">
                            <div class="card shadow h-100 border-left-primary">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-briefcase me-2"></i>Base de Datos de Negocio
                                    </h6>
                                    <span class="badge bg-primary"><?= htmlspecialchars($dbNegocio) ?></span>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <p class="text-muted">Contiene la información operativa del sistema:</p>
                                        <ul class="text-gray-700 mb-4">
                                            <li>Beneficiarios y expedientes.</li>
                                            <li>Citas y consultas médicas, psicológicas y sociales.</li>
                                            <li>Inventarios de insumos y mobiliario.</li>
                                            <li>Transporte, jornadas y catálogos del sistema.</li>
                                        </ul>
                                    </div>
                                    <div class="text-end">
                                        <a href="<?= BASE_URL ?>respaldo/descargar?tipo=negocio"
                                           class="btn btn-primary btn-block py-2 btn-descargar-respaldo" data-tipo="negocio">
                                            <i class="fas fa-download me-2"></i>Descargar respaldo (.sql)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BD Seguridad -->
                        <div class="col-lg-6 mb-4" id="card-respaldo-seguridad">
                            <div class="card shadow h-100 border-left-success">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-shield-alt me-2"></i>Base de Datos de Seguridad
                                    </h6>
                                    <span class="badge bg-success"><?= htmlspecialchars($dbSeguridad) ?></span>
                                </div>
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <p class="text-muted">Contiene el control de acceso y la auditoría:</p>
                                        <ul class="text-gray-700 mb-4">
                                            <li>Cuentas y credenciales de acceso.</li>
                                            <li>Roles, permisos y tipos de empleados.</li>
                                            <li>Tokens, rate limits y notificaciones.</li>
                                            <li>Bitácora de auditoría completa.</li>
                                        </ul>
                                    </div>
                                    <div class="text-end">
                                        <a href="<?= BASE_URL ?>respaldo/descargar?tipo=seguridad"
                                           class="btn btn-success btn-block py-2 btn-descargar-respaldo" data-tipo="seguridad">
                                            <i class="fas fa-download me-2"></i>Descargar respaldo (.sql)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <script src="<?= BASE_URL ?>dist/js/modulos/configuracion/backup.js" defer></script>
</body>

</html>
