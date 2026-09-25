<?php
$titulo = 'Registrar consulta médica';
include BASE_PATH . '/app/Views/template/head.php';
?>
<body id="page-top">
<div id="wrapper">
    <?php include BASE_PATH . '/app/Views/template/sidebar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column"><div id="content">
        <?php include BASE_PATH . '/app/Views/template/header.php'; ?>
        <div class="container-fluid">

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Gestionar diagnósticos: Medicina</h1>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-ayuda" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-circle-question me-1"></i>Ayuda
                    </button>
                    <a href="<?= BASE_URL ?>medicina/consultar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Consultar
                    </a>
                </div>
            </div>

            <div class="row">
                <?php include BASE_PATH . '/app/Views/medicina/components/stats.php'; ?>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nueva consulta médica</h6>
                </div>
                <div class="card-body">

                    <form id="form-medicina" novalidate>

                        <!-- ============ Beneficiario + Patología ============ -->
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
                                <label for="id_patologia" class="form-label">Patología *</label>
                                <select id="id_patologia" name="id_patologia"
                                        class="form-select select2" required>
                                    <option value="">Cargando...</option>
                                </select>
                                <div id="id_patologiaError" class="form-text text-danger"></div>
                                <!--
                                  La patología es obligatoria. Si la consulta no aplica
                                  ninguna, elige "Sin patología médica" (id_patologia = 1)
                                  del catálogo, igual que en Psicología.
                                -->
                            </div>
                        </div>

                        <!-- ============ Datos antropométricos ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <label for="estatura" class="form-label">Estatura (m) *</label>
                                <input type="number" class="form-control" id="estatura" name="estatura"
                                       step="0.01" min="0.50" max="2.50" placeholder="Ej: 1.70" required>
                                <div id="estaturaError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-3">
                                <label for="peso" class="form-label">Peso (kg) *</label>
                                <input type="number" class="form-control" id="peso" name="peso"
                                       step="0.01" min="2" max="300" placeholder="Ej: 72.50" required>
                                <div id="pesoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_sangre" class="form-label">Tipo de sangre *</label>
                                <select id="tipo_sangre" name="tipo_sangre" class="form-select" required>
                                    <option value="" selected disabled>Seleccione tipo de sangre</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                                <div id="tipo_sangreError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Motivo de visita ============ -->
                        <div class="mt-3">
                            <label for="motivo_visita" class="form-label">Motivo de visita *</label>
                            <textarea id="motivo_visita" name="motivo_visita"
                                      class="form-control" rows="2" maxlength="255"
                                      placeholder="Describa el motivo de la consulta..."></textarea>
                            <div id="motivo_visitaError" class="form-text text-danger"></div>
                        </div>

                        <!-- ============ Diagnóstico + Tratamiento ============ -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="diagnostico" class="form-label">Diagnóstico *</label>
                                <textarea id="diagnostico" name="diagnostico"
                                          class="form-control" rows="3" maxlength="255"
                                          placeholder="Describa el diagnóstico médico..."></textarea>
                                <div id="diagnosticoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="tratamiento" class="form-label">Tratamiento *</label>
                                <textarea id="tratamiento" name="tratamiento"
                                          class="form-control" rows="3" maxlength="255"
                                          placeholder="Describa el tratamiento recomendado..."></textarea>
                                <div id="tratamientoError" class="form-text text-danger"></div>
                            </div>
                        </div>

                        <!-- ============ Observaciones ============ -->
                        <div class="mt-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea id="observaciones" name="observaciones"
                                      class="form-control" rows="2" maxlength="255"
                                      placeholder="Observaciones adicionales, pronóstico, recomendaciones..."></textarea>
                            <div id="observacionesError" class="form-text text-danger"></div>
                        </div>

                        <!-- ============ Insumos del inventario (opcional) ============ -->
                        <div class="mt-4">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-first-aid-kit me-1"></i>Insumos del inventario médico
                                <span class="text-muted font-weight-normal">(opcional)</span>
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabla_insumos">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 60%;">Insumo</th>
                                            <th style="width: 20%;">Cantidad</th>
                                            <th style="width: 20%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lista_insumos">
                                        <!-- Filas dinámicas: las agrega JS (#btnAgregarInsumo).
                                             Solo aparecen insumos 'Disponible', con stock >= 1
                                             y fecha de vencimiento futura. -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div id="insumosError" class="form-text text-danger"></div>
                                <button type="button" class="btn btn-success btn-sm" id="btnAgregarInsumo">
                                    <i class="fas fa-plus me-1"></i>Agregar insumo
                                </button>
                            </div>
                            <div id="insumosDisponibles" class="d-none"
                                 data-insumos='[]'>
                                <!-- crear.js puebla este contenedor con el catálogo de
                                     insumos disponibles (api/medicina/catalogos) y lo usa
                                     para validar stock por fila. -->
                            </div>
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
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/tour.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/validaciones.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/stats.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/modulos/medicina/crear.js" defer></script>
</body></html>
