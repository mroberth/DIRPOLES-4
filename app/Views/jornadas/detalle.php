<?php
// app/Views/jornadas/detalle.php
// Detalle de una jornada: cabecera + aforo + asistentes + diagnósticos.
// El controlador ya validó la existencia (404 si no) y pasó $jornada.
$titulo = 'Jornada: ' . $jornada['nombre_jornada'];
include 'app/Views/template/head.php';

$formatoFecha = static function (?string $valor, string $formato = 'd/m/Y H:i'): string {
    $ts = $valor ? strtotime($valor) : false;
    return $ts ? date($formato, $ts) : '—';
};

$personas    = (int) $jornada['personas'];
$aforo       = (int) $jornada['aforo_maximo'];
$ocupacion   = (float) $jornada['ocupacion'];
$badgeEstatus = $jornada['estatus'] === 'Activa' ? 'success'
    : ($jornada['estatus'] === 'Cancelada' ? 'danger' : 'secondary');
?>

<body id="page-top">
    <div id="wrapper">
        <?php include 'app/Views/template/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'app/Views/template/header.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <?= htmlspecialchars($jornada['nombre_jornada']) ?>
                            <span id="jornadaEstatus" class="badge bg-<?= $badgeEstatus ?> align-middle ms-2">
                                <?= htmlspecialchars($jornada['estatus']) ?>
                            </span>
                        </h1>
                        <div class="d-flex">
                            <button type="button" id="btn-ayuda" class="btn btn-sm btn-info shadow-sm me-2">
                                <i class="fas fa-circle-question me-1"></i> Ayuda
                            </button>
                            <button type="button" id="btn-recargar" class="btn btn-sm btn-outline-secondary shadow-sm me-2">
                                <i class="fas fa-rotate-right me-1"></i> Recargar
                            </button>
                            <a href="<?= BASE_URL ?>jornadas/consultar" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-list me-1"></i> Consultar
                            </a>
                        </div>
                    </div>

                    <!-- ==================== CABECERA ==================== -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tipo</div>
                                            <div class="mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($jornada['tipo_jornada']) ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ubicación</div>
                                            <div class="mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($jornada['ubicacion']) ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Inicio</div>
                                            <div class="mb-0 text-gray-800"><?= htmlspecialchars($formatoFecha($jornada['fecha_inicio'])) ?></div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Cierre</div>
                                            <div class="mb-0 text-gray-800"><?= htmlspecialchars($formatoFecha($jornada['fecha_fin'])) ?></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Descripción</div>
                                            <div class="mb-0 text-gray-800">
                                                <?= $jornada['descripcion'] !== '' && $jornada['descripcion'] !== null
                                                    ? nl2br(htmlspecialchars($jornada['descripcion']))
                                                    : '<span class="text-muted">Sin descripción.</span>' ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase">Ocupación del aforo</div>
                                            <div class="small text-muted">
                                                <span id="jornadaDiagnosticos"><?= (int) $jornada['diagnosticos'] ?></span> diagnósticos
                                            </div>
                                        </div>
                                        <div class="h4 mb-2">
                                            <span id="jornadaPersonas"><?= $personas ?></span>
                                            <span class="text-muted h6">/ <?= $aforo ?> personas</span>
                                            <span id="jornadaOcupacion" class="badge bg-info text-dark ms-1"><?= number_format($ocupacion, 1) ?>%</span>
                                        </div>
                                        <div id="jornadaProgreso" class="progress" style="height: 14px;">
                                            <div id="jornadaBarra" class="progress-bar bg-success" role="progressbar"
                                                 style="width: <?= min(100, $ocupacion) ?>%"
                                                 aria-valuenow="<?= $ocupacion ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="form-text text-muted mt-2">
                                            El aforo protege la fila de la jornada con bloqueo (FOR UPDATE):
                                            dos registros simultáneos nunca lo rebasan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== ASISTENTES ==================== -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-users me-1"></i> Asistentes registrados
                            </h6>
                            <?php if ($puedeCrear): ?>
                                <button type="button" id="btn-nuevo-asistente" class="btn btn-sm btn-primary"
                                        data-bs-toggle="collapse" data-bs-target="#colapsoAsistente" aria-expanded="false">
                                    <i class="fas fa-user-plus me-1"></i> Nuevo asistente
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($puedeCrear): ?>
                            <!-- Formulario de alta de asistente -->
                            <div class="collapse" id="colapsoAsistente">
                                <div class="card-body border-bottom bg-light">
                                    <form id="form-asistente" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo de documento *</label>
                                                <select name="tipo_cedula" id="asis_tipo_cedula" class="form-select select2"
                                                        data-placeholder="Seleccione…" required>
                                                    <option value="V">V</option>
                                                    <option value="E">E</option>
                                                    <option value="J">J</option>
                                                    <option value="G">G</option>
                                                </select>
                                                <div id="asis_tipo_cedulaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Cédula / documento *</label>
                                                <div class="input-group">
                                                    <input type="text" name="cedula" id="asis_cedula" class="form-control"
                                                           inputmode="numeric" maxlength="12" placeholder="Ej: 12345678" required>
                                                    <button class="btn btn-outline-secondary" type="button" id="btn-buscar-persona"
                                                            title="Buscar y autocompletar los datos">
                                                        <i class="fas fa-magnifying-glass"></i>
                                                    </button>
                                                </div>
                                                <div id="asis_cedulaError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Nombres *</label>
                                                <input type="text" name="nombres" id="asis_nombres" class="form-control"
                                                       maxlength="100" required>
                                                <div id="asis_nombresError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Apellidos *</label>
                                                <input type="text" name="apellidos" id="asis_apellidos" class="form-control"
                                                       maxlength="100" required>
                                                <div id="asis_apellidosError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label">Fecha de nacimiento *</label>
                                                <input type="date" name="fecha_nacimiento" id="asis_fecha_nacimiento"
                                                       class="form-control" required>
                                                <div id="asis_fecha_nacimientoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Género *</label>
                                                <select name="genero" id="asis_genero" class="form-select select2"
                                                        data-placeholder="Seleccione…" required>
                                                    <option value="">Seleccione…</option>
                                                    <option value="Femenino">Femenino</option>
                                                    <option value="Masculino">Masculino</option>
                                                </select>
                                                <div id="asis_generoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tipo de paciente *</label>
                                                <select name="tipo_paciente" id="asis_tipo_paciente" class="form-select select2"
                                                        data-placeholder="Seleccione…" required>
                                                    <option value="">Cargando…</option>
                                                </select>
                                                <div id="asis_tipo_pacienteError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Teléfono *</label>
                                                <input type="text" name="telefono" id="asis_telefono" class="form-control"
                                                       inputmode="numeric" maxlength="12" placeholder="Ej: 04121234567" required>
                                                <div id="asis_telefonoError" class="form-text text-danger"></div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Correo</label>
                                                <input type="email" name="correo" id="asis_correo" class="form-control"
                                                       maxlength="100" placeholder="opcional@ejemplo.com">
                                                <div id="asis_correoError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Dirección</label>
                                                <input type="text" name="direccion" id="asis_direccion" class="form-control"
                                                       maxlength="255" placeholder="Sector, calle, casa…">
                                                <div id="asis_direccionError" class="form-text text-danger"></div>
                                            </div>
                                            <div class="col-md-3 d-flex align-items-end">
                                                <div class="d-grid gap-2 w-100">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-user-check me-1"></i> Registrar asistente
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div id="notaAsistente" class="form-text text-muted">
                                                    <i class="fas fa-circle-info me-1"></i>
                                                    La búsqueda por cédula solo <strong>sugiere</strong> los datos: quien
                                                    confirma eres tú. Una misma cédula no puede repetirse en ESTA jornada
                                                    (en otra sí) y solo se registra mientras la jornada esté
                                                    <strong>Activa</strong> y vigente.
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tablaAsistentes" class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Documento</th>
                                            <th>Persona</th>
                                            <th>Tipo de paciente</th>
                                            <th class="text-center">Edad</th>
                                            <th>Atención</th>
                                            <th class="text-center">Diagnósticos</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAsistentes"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'app/Views/template/footer.php'; ?>
        </div>
    </div>

    <!-- ==================== MODAL DIAGNÓSTICOS ==================== -->
    <div class="modal fade" id="modalDiagnosticos" tabindex="-1" aria-labelledby="modalDiagnosticosLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-success text-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                             style="width: 44px; height: 44px;">
                            <i class="fas fa-notes-medical text-success"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold" id="modalDiagnosticosLabel">Diagnósticos</h5>
                            <small class="opacity-75" id="diagnosticosPersona"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <!-- Lista existente -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-success mb-0">Diagnósticos registrados</h6>
                        <span class="badge bg-success" id="conteoDiagnosticos">0</span>
                    </div>
                    <div id="listaDiagnosticos" class="mb-4"></div>

                    <!-- Nuevo diagnóstico -->
                    <?php if ($puedeCrear): ?>
                        <div class="border rounded p-3 bg-white">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-plus-circle me-1"></i> Nuevo diagnóstico
                            </h6>
                            <form id="form-diagnostico" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Diagnóstico *</label>
                                        <textarea name="diagnostico" id="diag_diagnostico" class="form-control" rows="2"
                                                  maxlength="2000" placeholder="Ej: Hipertensión arterial leve" required></textarea>
                                        <div id="diag_diagnosticoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tratamiento *</label>
                                        <textarea name="tratamiento" id="diag_tratamiento" class="form-control" rows="2"
                                                  maxlength="2000" placeholder="Ej: Losartán 50 mg, 1 cada 24 h" required></textarea>
                                        <div id="diag_tratamientoError" class="form-text text-danger"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" id="diag_observaciones" class="form-control" rows="2"
                                                  maxlength="2000" placeholder="Indicaciones generales, control posterior…"></textarea>
                                        <div id="diag_observacionesError" class="form-text text-danger"></div>
                                    </div>

                                    <!-- Insumos -->
                                    <div class="col-12">
                                        <label class="form-label">Insumos (descuentan inventario)</label>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-6">
                                                <select id="diag_insumo" class="form-select select2"
                                                        data-placeholder="Seleccione un insumo…">
                                                    <option value="">Cargando…</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" id="diag_cantidad" class="form-control"
                                                       min="1" step="1" placeholder="Cantidad">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" id="btn-agregar-insumo" class="btn btn-outline-primary w-100">
                                                    <i class="fas fa-plus me-1"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                        <div id="diag_insumoError" class="form-text text-danger"></div>
                                        <div id="listaInsumos" class="mt-2"></div>
                                        <div class="form-text text-muted">
                                            Solo aparecen insumos <strong>Disponibles</strong>, con stock y sin vencer.
                                            El stock se descuenta con bloqueo y queda en el kardex; al borrar el
                                            diagnóstico NO se devuelve.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i> Guardar diagnóstico
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL EDITAR DIAGNÓSTICO ==================== -->
    <div class="modal fade" id="modalEditarDiagnostico" tabindex="-1" aria-labelledby="modalEditarDiagnosticoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title mb-0 fw-bold" id="modalEditarDiagnosticoLabel">
                        <i class="fas fa-pen me-1"></i> Corregir diagnóstico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-editar-diagnostico" novalidate>
                    <input type="hidden" name="id_jornada_diagnostico" id="edit_id_jornada_diagnostico">

                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Diagnóstico *</label>
                                <textarea name="diagnostico" id="edit_diagnostico" class="form-control" rows="2"
                                          maxlength="2000" required></textarea>
                                <div id="edit_diagnosticoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tratamiento *</label>
                                <textarea name="tratamiento" id="edit_tratamiento" class="form-control" rows="2"
                                          maxlength="2000" required></textarea>
                                <div id="edit_tratamientoError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea name="observaciones" id="edit_observaciones" class="form-control" rows="2"
                                          maxlength="2000"></textarea>
                                <div id="edit_observacionesError" class="form-text text-danger"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-text text-muted">
                                    <i class="fas fa-circle-info me-1"></i>
                                    Solo se corrigen los textos: ni la persona ni los insumos se pueden cambiar aquí
                                    (los insumos ya descontaron inventario).
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

    <script>
        window.JORNADAS_ID = <?= (int) $jornada['id_jornada'] ?>;
        window.JORNADAS_PUEDE_CREAR    = <?= $puedeCrear ? 'true' : 'false' ?>;
        window.JORNADAS_PUEDE_EDITAR   = <?= $puedeEditar ? 'true' : 'false' ?>;
        window.JORNADAS_PUEDE_ELIMINAR = <?= $puedeEliminar ? 'true' : 'false' ?>;
    </script>

    <!-- Módulo Jornadas: validaciones, tour, asistentes y diagnósticos -->
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/validaciones.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/tour.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/detalle.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/jornadas/diagnosticos.js" defer></script>
</body>

</html>
