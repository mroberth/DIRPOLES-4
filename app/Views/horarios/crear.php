<?php
$titulo = 'Crear Horario';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
    <div id="wrapper">
        <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Crear horario</h1>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info">
                                <i class="fas fa-circle-question me-1"></i>Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>horarios/consultar" class="btn btn-sm btn-secondary">
                                <i class="fas fa-list me-1"></i>Consultar
                            </a>
                        </div>
                    </div>

                    <div class="row mb-4" id="horarios-stats">
                        <?php foreach ([
                            ['horarios_total', 'Horarios registrados', 'primary'],
                            ['psicologos_con_horario', 'Psicólogos con horario', 'success'],
                            ['horas_semanales', 'Horas semanales', 'info'],
                        ] as [$clave, $texto, $color]): ?>
                            <div class="col-xl-4 col-md-6 mb-3">
                                <div class="card border-left-<?= $color ?> shadow h-100 py-2">
                                    <div class="card-body py-2">
                                        <div class="text-xs font-weight-bold text-<?= $color ?> text-uppercase mb-1"><?= $texto ?></div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" data-stat="<?= $clave ?>">0</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Registrar horario de psicólogo</h6>
                        </div>
                        <div class="card-body">
                            <form id="form-horario" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="id_empleado" class="form-label">Psicólogo *</label>
                                        <select id="id_empleado" name="id_empleado" class="form-select select2" data-placeholder="Seleccione un psicólogo" required>
                                            <option value="">Cargando...</option>
                                        </select>
                                        <div id="id_empleadoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="dia_semana" class="form-label">Día *</label>
                                        <select id="dia_semana" name="dia_semana" class="form-select select2" data-placeholder="Seleccione un día" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dia): ?>
                                                <option value="<?= $dia ?>"><?= $dia ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="dia_semanaError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="hora_inicio" class="form-label">Inicio *</label>
                                        <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" min="07:00" max="17:00" required>
                                        <div id="hora_inicioError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="hora_fin" class="form-label">Fin *</label>
                                        <input type="time" id="hora_fin" name="hora_fin" class="form-control" min="07:00" max="17:00" required>
                                        <div id="hora_finError" class="form-text text-danger"></div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-4 mb-0">
                                    <i class="fas fa-circle-info me-1"></i> Solo se permite un horario por psicólogo y día. El rango válido es de 07:00 a 17:00.
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar horario</button>
                                    <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>
    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/crear.js" defer></script>
</body>
</html>
