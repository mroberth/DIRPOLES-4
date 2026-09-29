<?php
// app/Views/jornadas/consultar.php
$titulo = "Consultar Jornadas Médicas";
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
                        <h1 class="h3 mb-0 text-gray-800">Gestionar Jornadas Médicas</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <?php if ($puedeCrear): ?>
                                <a href="<?= BASE_URL ?>jornadas/crear" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-plus me-1"></i> Nueva jornada
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include 'app/Views/jornadas/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaJornadas" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Jornada</th>
                                            <th>Fechas</th>
                                            <th>Ubicación</th>
                                            <th class="text-center">Aforo</th>
                                            <th class="text-center">Estatus</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyJornadas"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- ==================== MODAL EDITAR ==================== -->
    <div class="modal fade" id="modalJornada" tabindex="-1" aria-labelledby="modalJornadaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-briefcase-medical text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalJornadaLabel">Editar jornada</h5>
                            <small class="opacity-75" id="editarReferencia"></small>
                        </div>
                    </div>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-circle-question me-1"></i> Ayuda
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>

                <form id="form-jornada" novalidate>
                    <input type="hidden" name="id_jornada" id="id_jornada">

                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre_jornada" id="nombre_jornada" class="form-control"
                                       maxlength="100" required>
                                <div id="nombre_jornadaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Tipo de jornada *</label>
                                <select name="tipo_jornada" id="tipo_jornada" class="form-select select2"
                                        data-placeholder="Seleccione el tipo…" required>
                                    <option value="">Cargando…</option>
                                </select>
                                <div id="tipo_jornadaError" class="form-text text-danger"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Inicio *</label>
                                <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                                <div id="fecha_inicioError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cierre *</label>
                                <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control" required>
                                <div id="fecha_finError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Aforo máximo *</label>
                                <input type="number" name="aforo_maximo" id="aforo_maximo" class="form-control"
                                       min="1" max="5000" step="1" required>
                                <div id="aforo_maximoError" class="form-text text-danger"></div>
                                <div id="notaAforo" class="form-text text-muted">No puede bajar de las personas ya registradas.</div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Ubicación *</label>
                                <input type="text" name="ubicacion" id="ubicacion" class="form-control" maxlength="255" required>
                                <div id="ubicacionError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estatus *</label>
                                <select name="estatus" id="estatus" class="form-select select2"
                                        data-placeholder="Seleccione…" required>
                                    <option value="Activa">Activa</option>
                                    <option value="Cancelada">Cancelada</option>
                                    <option value="Finalizada">Finalizada</option>
                                </select>
                                <div id="estatusError" class="form-text text-danger"></div>
                                <div class="form-text text-muted">Solo una <strong>Activa</strong> admite nuevos registros.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="2" maxlength="2000"></textarea>
                                <div id="descripcionError" class="form-text text-danger"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'app/Views/template/script.php'; ?>

    <script>
        window.JORNADAS_PUEDE_CREAR    = <?= $puedeCrear ? 'true' : 'false' ?>;
        window.JORNADAS_PUEDE_EDITAR   = <?= $puedeEditar ? 'true' : 'false' ?>;
        window.JORNADAS_PUEDE_ELIMINAR = <?= $puedeEliminar ? 'true' : 'false' ?>;
    </script>

    <!-- Módulo Jornadas: stats + edición modal + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/consultar.js" defer></script>
</body>

</html>
