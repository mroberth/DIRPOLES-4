<?php
$titulo = 'Consultar Horarios';
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
                        <h1 class="h3 mb-0 text-gray-800">Horarios de Psicología</h1>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-recargar-horarios" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-right me-1"></i>Recargar</button>
                            <a href="<?= BASE_URL ?>horarios/crear" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nuevo horario</a>
                        </div>
                    </div>
                    <div class="row mb-4" id="horarios-stats">
                        <?php foreach ([
                            ['horarios_total', 'Horarios registrados', 'primary'],
                            ['psicologos_con_horario', 'Psicólogos con horario', 'success'],
                            ['horas_semanales', 'Horas semanales', 'info'],
                        ] as [$clave, $texto, $color]): ?>
                            <div class="col-xl-4 col-md-6 mb-3">
                                <div class="card border-left-<?= $color ?> shadow h-100 py-2"><div class="card-body py-2">
                                    <div class="text-xs font-weight-bold text-<?= $color ?> text-uppercase mb-1"><?= $texto ?></div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" data-stat="<?= $clave ?>">0</div>
                                </div></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card shadow mb-4"><div class="card-body"><div class="table-responsive">
                        <table id="tablaHorarios" class="table table-bordered table-hover align-middle w-100">
                            <thead class="table-light"><tr>
                                <th>Psicólogo</th><th>Cédula</th><th>Día</th><th>Hora de inicio</th><th>Hora final</th><th class="text-center">Acciones</th>
                            </tr></thead><tbody id="tbodyHorarios"></tbody>
                        </table>
                    </div></div></div>
                </div>
            </div>
            <?php include BASE_PATH . '/app/Views/template/footer.php'; ?>
        </div>
    </div>

    <div class="modal fade" id="modalEditarHorario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar horario</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <form id="form-editar-horario" novalidate><div class="modal-body">
                <input type="hidden" id="editar_id_horario" name="id_horario">
                <div class="row g-3">
                    <div class="col-md-6"><label for="editar_id_empleado" class="form-label">Psicólogo *</label><select id="editar_id_empleado" name="id_empleado" class="form-select select2" required></select><div id="editar_id_empleadoError" class="form-text text-danger"></div></div>
                    <div class="col-md-6"><label for="editar_dia_semana" class="form-label">Día *</label><select id="editar_dia_semana" name="dia_semana" class="form-select select2" required><?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $dia): ?><option value="<?= $dia ?>"><?= $dia ?></option><?php endforeach; ?></select><div id="editar_dia_semanaError" class="form-text text-danger"></div></div>
                    <div class="col-md-6"><label for="editar_hora_inicio" class="form-label">Inicio *</label><input type="time" id="editar_hora_inicio" name="hora_inicio" class="form-control" min="07:00" max="17:00" required><div id="editar_hora_inicioError" class="form-text text-danger"></div></div>
                    <div class="col-md-6"><label for="editar_hora_fin" class="form-label">Fin *</label><input type="time" id="editar_hora_fin" name="hora_fin" class="form-control" min="07:00" max="17:00" required><div id="editar_hora_finError" class="form-text text-danger"></div></div>
                </div>
            </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar cambios</button></div></form>
        </div></div>
    </div>
    <?php include BASE_PATH . '/app/Views/template/script.php'; ?>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/horario/consultar.js" defer></script>
</body>
</html>
