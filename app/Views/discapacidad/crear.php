<?php
$titulo = 'Registrar discapacidad';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
<div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestionar diagnósticos: Discapacidad</h1>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-circle-question me-1"></i>Ayuda
                    </button>
                    <a href="<?= BASE_URL ?>discapacidad/consultar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Consultar
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/discapacidad/components/stats.php'; ?>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nuevo diagnóstico de discapacidad</h6>
                </div>
                <div class="card-body">

                    <form id="form-discapacidad" novalidate>

                        <!-- ============ Beneficiario ============ -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="id_beneficiario" class="form-label">Beneficiario *</label>
                                <select id="id_beneficiario" name="id_beneficiario"
                                        class="form-select select2" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div id="id_beneficiarioError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Tipo + Discapacidad específica ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="tipo_discapacidad" class="form-label">Tipo de discapacidad *</label>
                                <select id="tipo_discapacidad" name="tipo_discapacidad" class="form-select" required>
                                    <option value="" selected disabled>Seleccione un tipo</option>
                                    <option value="Física">Física</option>
                                    <option value="Sensorial">Sensorial</option>
                                    <option value="Intelectual">Intelectual</option>
                                    <option value="Múltiple">Múltiple</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <div id="tipo_discapacidadError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="disc_especifica" class="form-label">Discapacidad específica</label>
                                <input type="text" id="disc_especifica" name="disc_especifica"
                                       class="form-control" maxlength="200"
                                       placeholder="Ej: Parálisis cerebral, Autismo, Sordera...">
                                <div id="disc_especificaError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Diagnóstico ============ -->
                        <div class="mt-3">
                            <label for="diagnostico" class="form-label">Diagnóstico *</label>
                            <input type="text" id="diagnostico" name="diagnostico"
                                   class="form-control" maxlength="255" required
                                   placeholder="Diagnóstico clínico formal...">
                            <div id="diagnosticoError" class="form-text text-danger"></div>
                        </div>

                        <!-- ============ Grado + Medicamentos ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="grado" class="form-label">Grado *</label>
                                <select id="grado" name="grado" class="form-select" required>
                                    <option value="" selected disabled>Seleccione el grado</option>
                                    <option value="Leve">Leve</option>
                                    <option value="Moderado">Moderado</option>
                                    <option value="Grave">Grave</option>
                                </select>
                                <div id="gradoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="medicamentos" class="form-label">Medicamentos actuales</label>
                                <textarea id="medicamentos" name="medicamentos" class="form-control" rows="2"
                                          maxlength="255"
                                          placeholder="Medicamentos, dosis y frecuencia si se conoce..."></textarea>
                                <div id="medicamentosError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Habilidades + Requiere asistencia ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="habilidades_funcionales" class="form-label">Habilidades funcionales *</label>
                                <textarea id="habilidades_funcionales" name="habilidades_funcionales"
                                          class="form-control" rows="3" maxlength="255" required
                                          placeholder="Capacidades para realizar actividades diarias..."></textarea>
                                <div id="habilidades_funcionalesError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="requiere_asistencia" class="form-label">¿Requiere asistencia personal?</label>
                                <select id="requiere_asistencia" name="requiere_asistencia" class="form-select">
                                    <option value="" selected>Seleccione</option>
                                    <option value="Si">Sí</option>
                                    <option value="No">No</option>
                                </select>
                                <div id="requiere_asistenciaError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Dispositivo + Carnet ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="dispositivo_asistencia" class="form-label">Dispositivo de asistencia</label>
                                <input type="text" id="dispositivo_asistencia" name="dispositivo_asistencia"
                                       class="form-control" maxlength="255"
                                       placeholder="Ej: Silla de ruedas, audífonos, lentes...">
                                <div id="dispositivo_asistenciaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="carnet_discapacidad" class="form-label">Número de carnet</label>
                                <input type="text" id="carnet_discapacidad" name="carnet_discapacidad"
                                       class="form-control" maxlength="20"
                                       placeholder="Número del carnet de discapacidad">
                                <div id="carnet_discapacidadError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Observaciones + Recomendaciones ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="observaciones" class="form-label">Observaciones *</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" rows="4"
                                          placeholder="Observaciones adicionales sobre la discapacidad..."></textarea>
                                <div id="observacionesError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="recomendaciones" class="form-label">Recomendaciones</label>
                                <textarea id="recomendaciones" name="recomendaciones" class="form-control" rows="4"
                                          placeholder="Sugerencias para el beneficiario, familia o institución..."></textarea>
                                <div id="recomendacionesError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Registrar diagnóstico
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>
<?php include BASE_PATH . '/app/Views/template/script.php'; ?>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/discapacidad/crear.js" defer></script>
</body></html>
