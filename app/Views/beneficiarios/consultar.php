<?php
// app/Views/beneficiarios/consultar.php
$titulo = "Consultar Beneficiarios";
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
                        <h1 class="h3 mb-0 text-gray-800">Beneficiarios</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <a href="<?= BASE_URL ?>beneficiarios/crear" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo beneficiario
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include 'app/Views/beneficiarios/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaBeneficiarios" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Género</th>
                                            <th>PNF</th>
                                            <th>Estatus</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyBeneficiarios"></tbody>
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
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-user-graduate text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarLabel">Editar Beneficiario</h5>
                            <small class="opacity-75" id="beneficiarioCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar" novalidate>
                    <input type="hidden" name="id_beneficiario" id="id_beneficiario">

                    <div class="modal-body p-0">
                        <div class="card border-0 rounded-0 bg-light">
                            <div class="card-body p-4">
                                <div class="row g-4">

                                    <!-- Columna izquierda: Información Personal -->
                                    <div class="col-lg-6 border-end">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2"></i> Información Personal
                                        </h6>

                                        <div class="mb-3">
                                            <label for="nombres" class="form-label text-muted small mb-1">
                                                <i class="fas fa-user text-primary me-1"></i> Nombres *
                                            </label>
                                            <input type="text" class="form-control" name="nombres" id="nombres" maxlength="100" required>
                                            <div id="nombresError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="apellidos" class="form-label text-muted small mb-1">
                                                <i class="fas fa-user text-primary me-1"></i> Apellidos *
                                            </label>
                                            <input type="text" class="form-control" name="apellidos" id="apellidos" maxlength="100">
                                            <div id="apellidosError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">
                                                <i class="fas fa-id-card text-primary me-1"></i> Cédula de Identidad *
                                            </label>
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <select name="tipo_cedula" id="tipo_cedula" class="form-select select2" data-placeholder="Tipo" required>
                                                        <option value="">Tipo</option>
                                                        <option value="V">V</option>
                                                        <option value="E">E</option>
                                                        <option value="J">J</option>
                                                        <option value="P">P</option>
                                                        <option value="G">G</option>
                                                    </select>
                                                    <div id="tipo_cedulaError" class="form-text text-danger"></div>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" name="cedula" id="cedula" maxlength="10" required>
                                                    <div id="cedulaError" class="form-text text-danger"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="correo" class="form-label text-muted small mb-1">
                                                <i class="fas fa-envelope text-primary me-1"></i> Correo Electrónico *
                                            </label>
                                            <input type="email" class="form-control" name="correo" id="correo" maxlength="100" required>
                                            <div id="correoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="telefono" class="form-label text-muted small mb-1">
                                                <i class="fas fa-phone text-primary me-1"></i> Teléfono *
                                            </label>
                                            <input type="text" class="form-control" name="telefono" id="telefono"
                                                   maxlength="11" inputmode="numeric" placeholder="0412-929-8008">
                                            <div id="telefonoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="genero" class="form-label text-muted small mb-1">
                                                <i class="fas fa-venus-mars text-primary me-1"></i> Género *
                                            </label>
                                            <select name="genero" id="genero" class="form-select select2" data-placeholder="Seleccione…" required>
                                                <option value="">Seleccione…</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                            <div id="generoError" class="form-text text-danger"></div>
                                        </div>
                                    </div>

                                    <!-- Columna derecha: Información Académica -->
                                    <div class="col-lg-6">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-graduation-cap me-2"></i> Información Académica
                                        </h6>

                                        <div class="mb-3">
                                            <label for="id_pnf" class="form-label text-muted small mb-1">
                                                <i class="fas fa-book text-primary me-1"></i> PNF *
                                            </label>
                                            <select name="id_pnf" id="id_pnf" class="form-select select2" data-placeholder="Seleccione…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="id_pnfError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-muted small mb-1">
                                                <i class="fas fa-layer-group text-primary me-1"></i> Sección *
                                            </label>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="text" class="form-control" id="seccion_numero" placeholder="Ej: 3102" maxlength="4" required>
                                                    <div id="seccion_numeroError" class="form-text text-danger small"></div>
                                                </div>
                                                <div class="col-6">
                                                    <select id="seccion_sede" class="form-select select2" data-placeholder="Sede…" required>
                                                        <option value="">Sede…</option>
                                                        <option value="M">MORÁN</option>
                                                        <option value="C">CRESPO</option>
                                                        <option value="J">JIMÉNEZ</option>
                                                        <option value="U">URDANETA</option>
                                                        <option value="B">BARQUISIMETO</option>
                                                    </select>
                                                    <div id="seccion_sedeError" class="form-text text-danger small"></div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="seccion" id="seccion">
                                            <div id="seccionError" class="form-text text-danger small"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="fecha_nac" class="form-label text-muted small mb-1">
                                                <i class="fas fa-cake-candles text-primary me-1"></i> Fecha de Nacimiento *
                                            </label>
                                            <input type="date" class="form-control" name="fecha_nac" id="fecha_nac">
                                            <div id="fecha_nacError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="estatus" class="form-label text-muted small mb-1">
                                                <i class="fas fa-toggle-on text-primary me-1"></i> Estatus *
                                            </label>
                                            <select name="estatus" id="estatus" class="form-select">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                            <div id="estatusError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="direccion" class="form-label text-muted small mb-1">
                                                <i class="fas fa-location-dot text-primary me-1"></i> Dirección
                                            </label>
                                            <input type="text" class="form-control" name="direccion" id="direccion" maxlength="255">
                                            <div id="direccionError" class="form-text text-danger"></div>
                                        </div>
                                    </div>

                                </div>
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

    <!-- Módulo Beneficiarios: stats + validaciones + edición (modal) + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/consultar.js" defer></script>
</body>

</html>
