<?php
// app/Views/beneficiarios/crear.php
$titulo = "Crear Beneficiario";
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
                        <h1 class="h3 mb-0 text-gray-800">Crear Beneficiario</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>beneficiarios/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include 'app/Views/beneficiarios/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form id="form-beneficiario" novalidate>
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
                                        <label class="form-label">Nombres *</label>
                                        <input type="text" name="nombres" id="nombres" class="form-control" maxlength="100" required>
                                        <div id="nombresError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Apellidos *</label>
                                        <input type="text" name="apellidos" id="apellidos" class="form-control" maxlength="100">
                                        <div id="apellidosError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Correo electrónico *</label>
                                        <input type="email" name="correo" id="correo" class="form-control" maxlength="100" required>
                                        <div id="correoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Teléfono *</label>
                                        <input type="text" name="telefono" id="telefono" class="form-control"
                                               maxlength="11" inputmode="numeric" placeholder="0412-929-8008">
                                        <div id="telefonoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Género *</label>
                                        <select name="genero" id="genero" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Seleccione…</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                        <div id="generoError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">PNF *</label>
                                        <select name="id_pnf" id="id_pnf" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Cargando…</option>
                                        </select>
                                        <div id="id_pnfError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sección *</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="text" id="seccion_numero" class="form-control" placeholder="Ej: 3102" maxlength="4" required>
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
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de nacimiento *</label>
                                        <input type="date" name="fecha_nac" id="fecha_nac" class="form-control">
                                        <div id="fecha_nacError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Estatus *</label>
                                        <select name="estatus" id="estatus" class="form-select">
                                            <option value="1" selected>Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                        <div id="estatusError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" name="direccion" id="direccion" class="form-control" maxlength="255">
                                        <div id="direccionError" class="form-text text-danger"></div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar beneficiario
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

    <!-- Módulo Beneficiarios: stats, validaciones reutilizables, tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/beneficiario/crear.js" defer></script>
</body>

</html>
