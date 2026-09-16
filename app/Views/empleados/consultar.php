<?php
// app/Views/empleados/consultar.php
$titulo = "Consultar Empleados";
$esAdmin = in_array($_SESSION['tipo_empleado'] ?? '', ['Administrador', 'Superusuario'], true);
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
                        <h1 class="h3 mb-0 text-gray-800">Empleados</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <a href="<?= BASE_URL ?>empleados/crear" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus me-1"></i> Nuevo empleado
                            </a>
                            <?php if ($esAdmin): ?>
                                <a href="<?= BASE_URL ?>horarios/consultar" class="btn btn-sm btn-info shadow-sm ms-2">
                                    <i class="fas fa-clock me-1"></i> Horarios de Psicología
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo (mismas del crear) -->
                    <?php include 'app/Views/empleados/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaEmpleados" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cédula</th>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Teléfono</th>
                                            <th>Tipo</th>
                                            <th>Estatus</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyEmpleados"></tbody>
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

                <!-- Header con degradado (estilo del sistema anterior) -->
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-user-pen text-primary"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalEditarLabel">Editar Empleado</h5>
                            <small class="opacity-75" id="empleadoCodigo"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar" novalidate>
                    <input type="hidden" name="id_empleado" id="id_empleado">

                    <div class="modal-body p-0">
                        <div class="card border-0 rounded-0 bg-light">
                            <div class="card-body p-4">
                                <div class="row g-4">

                                    <!-- ============ Columna izquierda: Información Personal ============ -->
                                    <div class="col-lg-6 border-end">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-user-circle me-2"></i> Información Personal
                                        </h6>

                                        <div class="mb-3">
                                            <label for="nombre" class="form-label text-muted small mb-1">
                                                <i class="fas fa-user text-primary me-1"></i> Nombre *
                                            </label>
                                            <input type="text" class="form-control" name="nombre" id="nombre" maxlength="100" required>
                                            <div id="nombreError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="apellido" class="form-label text-muted small mb-1">
                                                <i class="fas fa-user text-primary me-1"></i> Apellido *
                                            </label>
                                            <input type="text" class="form-control" name="apellido" id="apellido" maxlength="100">
                                            <div id="apellidoError" class="form-text text-danger"></div>
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
                                            <input type="email" class="form-control" name="correo" id="correo" maxlength="50" required>
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
                                    </div>

                                    <!-- ============ Columna derecha: Información Laboral ============ -->
                                    <div class="col-lg-6">
                                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                                            <i class="fas fa-briefcase me-2"></i> Información Laboral
                                        </h6>

                                        <div class="mb-3">
                                            <label for="id_tipo_empleado" class="form-label text-muted small mb-1">
                                                <i class="fas fa-user-tag text-primary me-1"></i> Tipo de Empleado *
                                            </label>
                                            <select name="id_tipo_empleado" id="id_tipo_empleado" class="form-select select2" data-placeholder="Seleccione…" required>
                                                <option value="">Cargando…</option>
                                            </select>
                                            <div id="id_tipo_empleadoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="fecha_nacimiento" class="form-label text-muted small mb-1">
                                                <i class="fas fa-cake-candles text-primary me-1"></i> Fecha de Nacimiento *
                                            </label>
                                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento">
                                            <div id="fecha_nacimientoError" class="form-text text-danger"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="clave" class="form-label text-muted small mb-1">
                                                <i class="fas fa-key text-primary me-1"></i> Contraseña
                                                <span class="text-muted fw-normal">(dejar vacío para no cambiar)</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="clave" id="clave" minlength="8" maxlength="72">
                                                <span class="input-group-text bg-white" id="btnTogglePassword" style="cursor:pointer;">
                                                    <i class="fa-solid fa-eye" id="icon-eye"></i>
                                                    <i class="fa-solid fa-eye-slash d-none" id="icon-eye-slash"></i>
                                                </span>
                                            </div>
                                            <div id="claveError" class="form-text text-danger"></div>
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
                                            <textarea class="form-control" name="direccion" id="direccion" rows="2" maxlength="500"></textarea>
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

    <!-- Módulo Empleados: stats + validaciones + edición (modal) + tabla -->
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/editar.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/consultar.js" defer></script>
</body>

</html>
