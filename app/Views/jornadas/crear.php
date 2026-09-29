<?php
// app/Views/jornadas/crear.php
$titulo = "Crear Jornada Médica";
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
                        <h1 class="h3 mb-0 text-gray-800">Crear Jornada Médica</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>jornadas/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include 'app/Views/jornadas/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form id="form-jornada" novalidate>
                                <div class="row g-4">
                                    <!-- Identidad de la jornada -->
                                    <div class="col-12">
                                        <h6 class="fw-bold text-primary d-flex align-items-center">
                                            <i class="fas fa-briefcase-medical me-2"></i> Datos de la jornada
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label">Nombre *</label>
                                                <input type="text" name="nombre_jornada" id="nombre_jornada"
                                                       class="form-control" maxlength="100"
                                                       placeholder="Ej: Jornada Médica Comunitaria 2026" required>
                                                <div id="nombre_jornadaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo de jornada *</label>
                                                <select name="tipo_jornada" id="tipo_jornada"
                                                        class="form-select select2" data-placeholder="Seleccione el tipo…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="tipo_jornadaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Aforo máximo *</label>
                                                <input type="number" name="aforo_maximo" id="aforo_maximo"
                                                       class="form-control" min="1" max="5000" step="1"
                                                       placeholder="Ej: 150" required>
                                                <div id="aforo_maximoError" class="form-text text-danger"></div>
                                                <div class="form-text text-muted">Máximo 5000 personas.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fechas y lugar -->
                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Fecha y hora de inicio *</label>
                                                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio"
                                                       class="form-control" required>
                                                <div id="fecha_inicioError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Fecha y hora de cierre *</label>
                                                <input type="datetime-local" name="fecha_fin" id="fecha_fin"
                                                       class="form-control" required>
                                                <div id="fecha_finError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ubicación *</label>
                                                <input type="text" name="ubicacion" id="ubicacion"
                                                       class="form-control" maxlength="255"
                                                       placeholder="Ej: Polideportivo UPTAEB, Bloque 3" required>
                                                <div id="ubicacionError" class="form-text text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Descripción -->
                                    <div class="col-12">
                                        <label class="form-label">Descripción</label>
                                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3"
                                                  maxlength="2000"
                                                  placeholder="Objetivo de la jornada, servicios que se ofrecen, requisitos…"></textarea>
                                        <div id="descripcionError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-12">
                                        <div id="notaJornada" class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            La jornada nace <strong>Activa</strong>. Los asistentes y sus diagnósticos se
                                            registran desde <strong>Detalle</strong> (botón de la tabla de Consultar);
                                            el aforo no puede bajar por debajo de las personas ya registradas.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Crear jornada
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

    <!-- Módulo Jornadas: stats, validaciones reutilizables, tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/crear.js" defer></script>
</body>

</html>
