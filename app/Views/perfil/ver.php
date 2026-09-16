<?php
// app/Views/perfil/ver.php
$titulo = "Mi Perfil";
include 'app/Views/template/head.php';
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">
                        <i class="fas fa-id-badge me-2"></i>Mi Perfil
                    </h1>

                    <div class="row">
                        <!-- Tarjeta de datos de solo lectura -->
                        <div class="col-lg-4 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Datos de tu cuenta</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-5">Empleado</dt>
                                        <dd class="col-7" id="perfil-nombre">—</dd>

                                        <dt class="col-5">Cédula</dt>
                                        <dd class="col-7" id="perfil-cedula">—</dd>

                                        <dt class="col-5">Rol</dt>
                                        <dd class="col-7" id="perfil-rol">—</dd>

                                        <dt class="col-5">Miembro desde</dt>
                                        <dd class="col-7" id="perfil-desde">—</dd>
                                    </dl>
                                    <hr>
                                    <p class="small text-muted mb-0">
                                        Estos datos solo puede modificarlos un administrador
                                        desde <strong>Gestionar Empleados</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario editable -->
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Editar mis datos</h6>
                                </div>
                                <div class="card-body">
                                    <form id="form-perfil" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Correo electrónico *</label>
                                                <input type="email" name="correo" id="correo" class="form-control"
                                                       maxlength="50" required>
                                                <div id="correoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Teléfono *</label>
                                                <input type="text" name="telefono" id="telefono" class="form-control"
                                                       maxlength="11" inputmode="numeric" placeholder="04129298008">
                                                <div id="telefonoError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Dirección</label>
                                                <textarea name="direccion" id="direccion" class="form-control"
                                                          rows="2" maxlength="500"></textarea>
                                                <div id="direccionError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-12">
                                                <hr class="mt-2 mb-3">
                                                <h6 class="text-gray-800 mb-3">
                                                    <i class="fas fa-lock me-1"></i>
                                                    Confirma tu identidad con tu contraseña actual
                                                </h6>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Contraseña actual *</label>
                                                <div class="input-group">
                                                    <input type="password" name="clave_actual" id="clave_actual"
                                                           class="form-control" maxlength="72" required>
                                                    <span class="input-group-text bg-white" data-toggle="clave_actual" style="cursor:pointer;">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </span>
                                                </div>
                                                <div id="clave_actualError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    Nueva contraseña <span class="text-muted">(opcional)</span>
                                                </label>
                                                <div class="input-group">
                                                    <input type="password" name="clave" id="clave"
                                                           class="form-control" minlength="8" maxlength="72"
                                                           placeholder="Mínimo 8 caracteres">
                                                    <span class="input-group-text bg-white" data-toggle="clave" style="cursor:pointer;">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </span>
                                                </div>
                                                <div id="claveError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Confirmar nueva contraseña</label>
                                                <input type="password" name="clave_confirmacion" id="clave_confirmacion"
                                                       class="form-control" maxlength="72" disabled>
                                                <div id="clave_confirmacionError" class="form-text text-danger"></div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Guardar cambios
                                            </button>
                                            <button type="reset" class="btn btn-outline-secondary" id="btn-reset">Limpiar</button>
                                        </div>
                                    </form>
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

    <!-- JS del módulo Perfil -->
    <script src="<?= BASE_URL ?>dist/js/modulos/empleado/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/perfil/perfil.js" defer></script>
</body>

</html>
