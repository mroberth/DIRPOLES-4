<?php
$titulo = 'Registrar consulta psicológica';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
<div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestionar diagnósticos: Psicología</h1>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-circle-question me-1"></i>Ayuda
                    </button>
                    <a href="<?= BASE_URL ?>psicologia/consultar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Consultar
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/psicologia/components/stats.php'; ?>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nueva consulta psicológica</h6>
                </div>
                <div class="card-body">

                    <!--
                      PATRÓN "No aplica":
                      Cada campo opcional (según tipo_consulta) tiene DOS inputs con el MISMO name:
                        - El visible  (id="campo")     → se habilita cuando el tipo SÍ aplica.
                        - El hidden   (id="campo_na")  → value="No aplica", se habilita cuando el tipo NO aplica.
                      El JS de crear.js/validaciones.js debe alternar `disabled` entre ambos para que
                      solo uno de los dos se envíe en el submit. Nunca los dos activos a la vez.
                    -->

                    <form id="form-psicologia" novalidate>

                        <!-- ============ Beneficiario + Tipo ============ -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="id_beneficiario" class="form-label">Beneficiario *</label>
                                <select id="id_beneficiario" name="id_beneficiario"
                                        class="form-select select2" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div id="id_beneficiarioError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_consulta" class="form-label">Tipo de formulario *</label>
                                <select id="tipo_consulta" name="tipo_consulta"
                                        class="form-select select2" required>
                                    <option value="Diagnóstico" selected>Diagnóstico general</option>
                                    <option value="Retiro temporal">Retiro temporal</option>
                                    <option value="Cambio de carrera">Cambio de carrera</option>
                                </select>
                                <div id="tipo_consultaError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Sección Diagnóstico ============ -->
                        <div id="campos-diagnostico" class="row g-3 mt-1">

                            <div class="col-md-6">
                                <label for="id_patologia" class="form-label">Patología *</label>
                                <select id="id_patologia" name="id_patologia"
                                        class="form-select select2">
                                    <option value="">Cargando...</option>
                                </select>
                                <div id="id_patologiaError" class="form-text text-danger"></div>
                                <!--
                                  Sin gemelo "No aplica": id_patologia es un FK entero.
                                  Cuando el tipo no es Diagnóstico el select queda disabled
                                  (no se envía) y el backend registra la patología general
                                  ya existente ("Sin patología general", id_patologia = 3).
                                -->
                            </div>

                            <div class="col-md-6">
                                <label for="diagnostico" class="form-label">Diagnóstico *</label>
                                <textarea id="diagnostico" name="diagnostico"
                                          class="form-control" rows="3"></textarea>
                                <div id="diagnosticoError" class="form-text text-danger"></div>
                                <input type="hidden" id="diagnostico_na" name="diagnostico"
                                       value="No aplica" disabled>
                            </div>

                            <div class="col-12">
                                <label for="tratamiento_gen" class="form-label">Tratamiento general</label>
                                <textarea id="tratamiento_gen" name="tratamiento_gen"
                                          class="form-control" rows="3"></textarea>
                                <div id="tratamiento_genError" class="form-text text-danger"></div>
                                <input type="hidden" id="tratamiento_gen_na" name="tratamiento_gen"
                                       value="No aplica" disabled>
                            </div>

                        </div>

                        <!-- ============ Sección Retiro temporal ============ -->
                        <div id="campos-retiro" class="row g-3 mt-1 d-none">

                            <div class="col-md-8">
                                <label for="motivo_retiro" class="form-label">Motivo del retiro *</label>
                                <textarea id="motivo_retiro" name="motivo_retiro"
                                          class="form-control" rows="3"></textarea>
                                <div id="motivo_retiroError" class="form-text text-danger"></div>
                                <input type="hidden" id="motivo_retiro_na" name="motivo_retiro"
                                       value="No aplica" disabled>
                            </div>

                            <div class="col-md-4">
                                <label for="duracion_retiro" class="form-label">Duración *</label>
                                <input id="duracion_retiro" name="duracion_retiro"
                                       class="form-control" maxlength="50">
                                <div id="duracion_retiroError" class="form-text text-danger"></div>
                                <input type="hidden" id="duracion_retiro_na" name="duracion_retiro"
                                       value="No aplica" disabled>
                            </div>

                        </div>

                        <!-- ============ Sección Cambio de carrera ============ -->
                        <div id="campos-cambio" class="row g-3 mt-1 d-none">

                            <div class="col-12">
                                <label for="motivo_cambio" class="form-label">Motivo del cambio de carrera *</label>
                                <input id="motivo_cambio" name="motivo_cambio"
                                       class="form-control" maxlength="100">
                                <div id="motivo_cambioError" class="form-text text-danger"></div>
                                <input type="hidden" id="motivo_cambio_na" name="motivo_cambio"
                                       value="No aplica" disabled>
                            </div>

                        </div>

                        <!-- ============ Observaciones (común a los 3 tipos) ============ -->
                        <div class="mt-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea id="observaciones" name="observaciones"
                                      class="form-control" rows="3" maxlength="5000"></textarea>
                            <div id="observacionesError" class="form-text text-danger"></div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Registrar consulta
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div><?php include BASE_PATH . '/app/Views/template/footer.php'; ?></div>
</div>
<?php include BASE_PATH . '/app/Views/template/script.php'; ?>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/psicologia/crear.js" defer></script>
</body></html>