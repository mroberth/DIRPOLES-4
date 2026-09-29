<?php
// app/Views/inventario/crear.php
$titulo = "Crear Insumo";
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
                        <h1 class="h3 mb-0 text-gray-800">Crear Insumo</h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <a href="<?= BASE_URL ?>inventario/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- Tarjetas de resumen del módulo -->
                    <?php include 'app/Views/inventario/components/stats.php'; ?>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form id="form-insumo" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Nombre del insumo *</label>
                                        <input type="text" name="nombre_insumo" id="nombre_insumo" class="form-control"
                                               maxlength="100" placeholder="Ej: Acetaminofén 500MG" required>
                                        <div id="nombre_insumoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo de insumo *</label>
                                        <select name="tipo_insumo" id="tipo_insumo" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Seleccione…</option>
                                            <option value="Medicamento">Medicamento</option>
                                            <option value="Material">Material</option>
                                            <option value="Quirúrgico">Quirúrgico</option>
                                        </select>
                                        <div id="tipo_insumoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Presentación *</label>
                                        <select name="id_presentacion" id="id_presentacion" class="form-select select2" data-placeholder="Seleccione…" required>
                                            <option value="">Cargando…</option>
                                        </select>
                                        <div id="id_presentacionError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Fecha de vencimiento *</label>
                                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" required>
                                        <div id="fecha_vencimientoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Descripción *</label>
                                        <textarea name="descripcion" id="descripcion" class="form-control" rows="2"
                                                  maxlength="250" placeholder="Ej: Pastillas de acetaminofen" required></textarea>
                                        <div id="descripcionError" class="form-text text-danger"></div>
                                    </div>

                                    <div class="col-12">
                                        <div id="notaStock" class="form-text text-muted">
                                            <i class="fas fa-circle-info me-1"></i>
                                            La cantidad no se define aquí: el insumo nace en <strong>0 (Agotado)</strong> y el
                                            stock se registra con <strong>Entrada</strong> desde la pantalla Consultar.
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar insumo
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

    <!-- Módulo Inventario Médico: stats, validaciones reutilizables, tour y envío -->
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/stats.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/inventario/crear.js" defer></script>
</body>

</html>
