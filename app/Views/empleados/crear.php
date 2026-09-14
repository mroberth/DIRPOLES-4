<?php
// app/Views/empleados/crear.php
$titulo = "Crear Empleado";
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
                        <h1 class="h3 mb-0 text-gray-800">Crear Empleado</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>empleados/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include 'app/Views/empleados/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form id="form-empleado" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Tipo de cédula *</label>
                                        <select name="tipo_cedula" id="tipo_cedula" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Seleccione…</option>
                                            <option value="V">V</option>
                                            <option value="E">E</option>
                                            <option value="J">J</option>
                                            <option value="P">P</option>
                                            <option value="G">G</option>
                                        </select>
                                        <div id="tipo_cedulaError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Cédula *</label>
                                        <input type="text" name="cedula" id="cedula" class="form-control" maxlength="10" required>
                                        <div id="cedulaError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Nombre *</label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" maxlength="100" required>
                                        <div id="nombreError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Apellido *</label>
                                        <input type="text" name="apellido" id="apellido" class="form-control" maxlength="100">
                                        <div id="apellidoError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo electrónico *</label>
                                        <input type="email" name="correo" id="correo" class="form-control" maxlength="50" required>
                                        <div id="correoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Teléfono *</label>
                                        <input type="text" name="telefono" id="telefono" class="form-control"
                                               maxlength="11" inputmode="numeric" placeholder="0412-929-8008">
                                        <div id="telefonoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo de empleado *</label>
                                        <select name="id_tipo_empleado" id="id_tipo_empleado" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Cargando…</option>
                                        </select>
                                        <div id="id_tipo_empleadoError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de nacimiento *</label>
                                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                                        <div id="fecha_nacimientoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Contraseña *</label>
                                        <div class="input-group">
                                            <input type="password" name="clave" id="clave" class="form-control" minlength="8" maxlength="72" required>
                                            <span class="input-group-text bg-white" id="btnTogglePassword" style="cursor:pointer;">
                                                <i class="fa-solid fa-eye" id="icon-eye"></i>
                                                <i class="fa-solid fa-eye-slash d-none" id="icon-eye-slash"></i>
                                            </span>
                                        </div>
                                        <div id="claveError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Estatus *</label>
                                        <select name="estatus" id="estatus" class="form-select">
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <div id="estatusError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Dirección</label>
                                        <textarea name="direccion" id="direccion" class="form-control" rows="2" maxlength="500"></textarea>
                                        <div id="direccionError" class="form-text text-danger"></div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar empleado
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

    <!-- Módulo Empleados: stats, validaciones reutilizables, tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/crear.js" defer></script>
</body>

</html>
