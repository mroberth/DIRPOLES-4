<?php
// app/Views/referencias/crear.php
$titulo = "Crear Referencia";
include 'app/Views/template/head.php';

$idEmpleadoSesion = (int) ($_SESSION['id_empleado'] ?? 0);
$nombreSesion = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Crear Referencia</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>referencias/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include 'app/Views/referencias/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if (!$esAdmin): ?>
                                <div class="alert alert-info py-2 small mb-4">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Como no eres administrador, el <strong>origen</strong> eres tú
                                    (<?= htmlspecialchars($nombreSesion) ?><?= $servicioPropio ? ' — ' . htmlspecialchars($servicioPropio['nombre_servicio']) : '' ?>):
                                    solo eliges a quién se refiere.
                                </div>
                            <?php endif; ?>

                            <form id="form-referencia" novalidate>
                                <div class="row g-4">
                                    <!-- Beneficiario -->
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary d-flex align-items-center">
                                            <i class="fas fa-user-graduate me-2"></i> Beneficiario a referir
                                        </h6>
                                        <label class="form-label">Beneficiario *</label>
                                        <select name="id_beneficiario" id="id_beneficiario" class="form-select select2"
                                                data-placeholder="Seleccione el beneficiario…" required>
                                            <option value="">Cargando…</option>
                                        </select>
                                        <div id="id_beneficiarioError" class="form-text text-danger"></div>
                                    </div>

                                    <!-- Origen -->
                                    <div class="col-lg-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="fw-bold text-primary d-flex align-items-center mb-3">
                                                <i class="fas fa-arrow-right-from-bracket me-2"></i> Quién refiere (origen)
                                            </h6>
                                            <div class="mb-3">
                                                <label class="form-label">Servicio de origen <?= $esAdmin ? '*' : '' ?></label>
                                                <select name="id_servicio_origen" id="id_servicio_origen"
                                                        class="form-select select2" data-placeholder="Seleccione…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="id_servicio_origenError" class="form-text text-danger"></div>
                                                <?php if (!$esAdmin): ?>
                                                    <div class="form-text text-muted">Tu servicio se fija automáticamente.</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label">Empleado de origen <?= $esAdmin ? '*' : '' ?></label>
                                                <select name="id_empleado_origen" id="id_empleado_origen"
                                                        class="form-select select2" data-placeholder="Seleccione…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="id_empleado_origenError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Destino -->
                                    <div class="col-lg-6">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="fw-bold text-success d-flex align-items-center mb-3">
                                                <i class="fas fa-arrow-right-to-bracket me-2"></i> A quién se refiere (destino)
                                            </h6>
                                            <div class="mb-3">
                                                <label class="form-label">Servicio destino *</label>
                                                <select name="id_servicio_destino" id="id_servicio_destino"
                                                        class="form-select select2" data-placeholder="Seleccione…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="id_servicio_destinoError" class="form-text text-danger"></div>
                                                <div class="form-text text-muted">Debe ser distinto al servicio de origen.</div>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label">Empleado destino *</label>
                                                <select name="id_empleado_destino" id="id_empleado_destino"
                                                        class="form-select select2" data-placeholder="Seleccione el servicio primero…" required>
                                                    <option value="">Seleccione el servicio primero…</option>
                                                </select>
                                                <div id="id_empleado_destinoError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Textos -->
                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Motivo *</label>
                                                <input type="text" name="motivo" id="motivo" class="form-control"
                                                       maxlength="255" placeholder="Ej: Evaluación socioeconómica" required>
                                                <div id="motivoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Observaciones *</label>
                                                <textarea name="observaciones" id="observaciones" class="form-control" rows="3"
                                                          maxlength="2000" placeholder="Detalle del caso que motiva la referencia…" required></textarea>
                                                <div id="observacionesError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div id="notaReferencia" class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            La referencia nace en <strong>Pendiente</strong>: el empleado destino recibirá una
                                            notificación y deberá aceptarla o rechazarla desde <strong>Consultar</strong>.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Enviar referencia
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <script>
        window.REFERENCIAS_ES_ADMIN = <?= $esAdmin ? 'true' : 'false' ?>;
        window.REFERENCIAS_ID_EMPLEADO = <?= $idEmpleadoSesion ?>;
        window.REFERENCIAS_SERVICIO_ORIGEN = <?= (int) ($servicioPropio['id_servicio'] ?? 0) ?>;
        window.REFERENCIAS_NOMBRE_ORIGEN = <?= json_encode($nombreSesion, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    </script>

    <!-- Módulo Referencias: stats, validaciones reutilizables, tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/referencias/crear.js" defer></script>
</body>

</html>
