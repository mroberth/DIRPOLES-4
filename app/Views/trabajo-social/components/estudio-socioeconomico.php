<!-- Offcanvas de Estudio Socioeconómico (derecha, w-50).
     Copiado de ../DIRPOLES_4/.../estudio-socioe.php y adaptado:
     sus <script> vivían en dist/js/modulos/trabajo-social/estudio.js
     y validaciones-estudio.js (patrón del módulo). -->
<!-- Offcanvas de Estudio Socioeconómico (Right Side, w-50) -->
<div class="offcanvas offcanvas-end w-50" tabindex="-1" id="offcanvasEstudioSocioeconomico" aria-labelledby="offcanvasEstudioLabel">
    <div class="offcanvas-header text-primary">
        <h5 class="offcanvas-title" id="offcanvasEstudioLabel">
            <i class="fas fa-file-invoice-dollar me-2"></i>Formulario de Estudio Socioeconómico
        </h5>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-ayuda-estudio" class="btn btn-sm btn-outline-info">
                <i class="fas fa-circle-question me-1"></i>Ayuda
            </button>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
    </div>
    
    <!-- Barra de Progreso/Pasos -->
    <div class="bg-light border-bottom p-0">
        <nav class="nav nav-pills nav-fill w-100" id="navPasosEstudio" role="tablist">
            <button class="nav-link active rounded-0" id="tab-paso1" data-bs-toggle="pill" data-bs-target="#paso1" type="button" role="tab">
                <i class="fas fa-user-circle me-1"></i> Personales
            </button>
            <button class="nav-link rounded-0" id="tab-paso2" data-bs-toggle="pill" data-bs-target="#paso2" type="button" role="tab">
                <i class="fas fa-graduation-cap me-1"></i> Educativos
            </button>
            <button class="nav-link rounded-0" id="tab-paso3" data-bs-toggle="pill" data-bs-target="#paso3" type="button" role="tab">
                <i class="fas fa-users me-1"></i> Grupo Familiar
            </button>
            <button class="nav-link rounded-0" id="tab-paso4" data-bs-toggle="pill" data-bs-target="#paso4" type="button" role="tab">
                <i class="fas fa-home me-1"></i> Economía
            </button>
            <button class="nav-link rounded-0" id="tab-paso5" data-bs-toggle="pill" data-bs-target="#paso5" type="button" role="tab">
                <i class="fas fa-clipboard-check me-1"></i> Observ.
            </button>
        </nav>
    </div>
    
    <!-- Cuerpo del Offcanvas -->
    <div class="offcanvas-body bg-light">
        <form id="formEstudioSocioeconomico" class="h-100">
            <input type="hidden" name="id_exoneracion" id="id_exoneracion_estudio">
            <div class="container-fluid p-0 h-100">
                <div class="tab-content h-100" id="pasosContent">
                    
                    <!-- PASO 1: Datos Personales y Solicitud -->
                    <div class="tab-pane fade show active" id="paso1" role="tabpanel">
                        <div class="row">
                            <!-- Foto -->
                            <div class="col-md-4 mb-3">
                                <div class="card shadow-sm h-100 border-left-primary">
                                    <div class="card-header text-primary py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-camera me-2"></i>Foto</h6>
                                    </div>
                                    <div class="card-body text-center p-2">
                                        <div class="mb-2">
                                            <i class="fas fa-user-circle fa-4x text-muted mb-2"></i>
                                            <input type="file" class="form-control form-control-sm" name="imagen" id="imagen_se" accept="image/*">
                                        </div>
                                        <div id="image_seError" class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Solicitud -->
                            <div class="col-md-8 mb-3">
                                <div class="card shadow-sm h-100 border-left-primary">
                                    <div class="card-header text-primary py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-file-alt me-2"></i>Solicitud</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row align-items-center">
                                            <div class="col-md-6 mb-2">
                                                <label class="fw-bold small text-gray-700">Tipo:</label>
                                                <div class="d-flex mt-1 small">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="renovacion" id="solicitud_renovacion" value="X">
                                                        <label class="form-check-label" for="solicitud_renovacion">Renovación</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="nueva" id="solicitud_nueva" value="X">
                                                        <label class="form-check-label" for="solicitud_nueva">Nueva</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label for="fecha" class="form-label small text-gray-700">Fecha:</label>
                                                <input type="date" class="form-control form-control-sm" name="fecha" id="fecha">
                                                <div id="fechaError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label for="beneficio" class="form-label small text-gray-700">Beneficio:</label>
                                                <input type="text" class="form-control form-control-sm" name="beneficio" id="beneficio" onkeypress="soloLetras(event)">
                                                <div id="beneficioError" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Identificación del Solicitante -->
                            <div class="col-12">
                                <div class="card shadow-sm border-left-primary">
                                    <div class="card-header text-primary py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-address-card me-2"></i>Datos Personales</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-7 mb-2">
                                                <label for="nombre" class="form-label small">Apellidos y Nombres:</label>
                                                <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" onkeypress="soloLetras(event)">
                                                <div id="nombreError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label for="ci" class="form-label small">Cédula:</label>
                                                <input type="text" class="form-control form-control-sm" name="ci" id="ci" onkeypress="soloNumeros(event)" maxlength="8">
                                                <div id="ciError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="fecha_nacimiento" class="form-label small">Fecha Nac:</label>
                                                <input type="date" class="form-control form-control-sm" name="fecha_nacimiento" id="fecha_nacimiento">
                                                <div id="fecha_nacimientoError" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label for="nacimiento" class="form-label small">Lugar de Nacimiento:</label>
                                                <input type="text" class="form-control form-control-sm" name="nacimiento" id="nacimiento">
                                                <div id="nacimientoError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label for="edad" class="form-label small">Edad:</label>
                                                <input type="text" class="form-control form-control-sm" name="edad" id="edad" onkeypress="soloNumeros(event)" maxlength="2">
                                                <div id="edadError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="estado_civil" class="form-label small">Edo. Civil:</label>
                                                <select class="form-select form-select-sm" name="estado_civil" id="estado_civil">
                                                    <option value="" disabled selected>...</option>
                                                    <option value="Soltero">Soltero</option>
                                                    <option value="Casado">Casado</option>
                                                    <option value="Divorciado">Divorciado</option>
                                                    <option value="Viudo">Viudo</option>
                                                </select>
                                                <div id="estado_civilError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="telefono" class="form-label small">Teléfono:</label>
                                                <input type="text" class="form-control form-control-sm" name="telefono" id="telefono" onkeypress="soloNumeros(event)" maxlength="11">
                                                <div id="telefonoError" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-2">
                                        
                                        <!-- Datos Laborales y Carga -->
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="fw-bold d-block small mb-1">¿Trabaja?</label>
                                                <div class="d-flex small">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="tr_si" id="trabaja_si" value="X">
                                                        <label class="form-check-label" for="trabaja_si">Sí</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="tr_no" id="trabaja_no" value="X">
                                                        <label class="form-check-label" for="trabaja_no">No</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="ocupacion" class="form-label small">Ocupación:</label>
                                                <input type="text" class="form-control form-control-sm" name="ocupacion" id="ocupacion" onkeypress="soloTexto(event)">
                                                <div id="ocupacionError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="lugar_trabajo" class="form-label small">Lugar Trabajo:</label>
                                                <input type="text" class="form-control form-control-sm" name="lugar_trabajo" id="lugar_trabajo">
                                                <div id="lugar_trabajoError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="sueldo" class="form-label small">Sueldo:</label>
                                                <input type="text" class="form-control form-control-sm" name="sueldo" id="sueldo" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)">
                                                <div id="sueldoError" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-3 mb-2">
                                                <label class="fw-bold d-block small mb-1">¿Carga Familiar?</label>
                                                <div class="d-flex small">
                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="cf_si" id="carga_familiar_si" value="X">
                                                        <label class="form-check-label" for="carga_familiar_si">Sí</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="cf_no" id="carga_familiar_no" value="X">
                                                        <label class="form-check-label" for="carga_familiar_no">No</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="hijos" class="form-label small">N° Hijos:</label>
                                                <input type="text" class="form-control form-control-sm" name="hijos" id="hijos" onkeypress="soloNumeros(event)" maxlength="2">
                                                <div id="hijosError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="dir_hab" class="form-label small">Dir. Habitación:</label>
                                                <input type="text" class="form-control form-control-sm" name="dir_hab" id="dir_hab">
                                                <div id="dir_habError" class="invalid-feedback"></div>
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <label for="dir_res" class="form-label small">Dir. Residencia:</label>
                                                <input type="text" class="form-control form-control-sm" name="dir_res" id="dir_res">
                                                <div id="dir_resError" class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-primary" onclick="cambiarPaso('paso2')">Siguiente <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- PASO 2: Datos Educativos -->
                    <div class="tab-pane fade" id="paso2" role="tabpanel">
                        <div class="card shadow-sm border-left-success">
                            <div class="card-header text-success py-2">
                                <h6 class="m-0"><i class="fas fa-university me-2"></i>Información Académica</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="especialidad" class="form-label">Especialidad:</label>
                                        <input type="text" class="form-control" name="especialidad" id="especialidad" onkeypress="soloLetras(event)">
                                        <div id="especialidadError" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sem_tra" class="form-label">Semestre o Trayecto:</label>
                                        <input type="text" class="form-control" name="sem_tra" id="sem_tra" onkeypress="soloNumeros(event)" maxlength="1">
                                        <div id="sem_traError" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="turno" class="form-label">Turno:</label>
                                        <select class="form-select" name="turno" id="turno">
                                            <option value="" selected disabled>Seleccione...</option>
                                            <option value="Diurno">Diurno</option>
                                            <option value="Nocturno">Nocturno</option>
                                        </select>
                                        <div id="turnoError" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="seccion" class="form-label">Sección:</label>
                                        <input type="text" class="form-control" name="seccion" id="seccion">
                                        <div id="seccionError" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="correo" class="form-label">Correo Electrónico:</label>
                                        <input type="email" class="form-control" name="correo" id="correo">
                                        <div id="correoError" class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="redes" class="form-label">Redes Sociales (FB/Twitter/Insta):</label>
                                        <input type="text" class="form-control" name="redes" id="redes">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="cambiarPaso('paso1')"><i class="fas fa-arrow-left"></i> Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="cambiarPaso('paso3')">Siguiente <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    
                    <!-- PASO 3: Grupo Familiar -->
                    <div class="tab-pane fade" id="paso3" role="tabpanel">
                        <div class="card shadow-sm border-left-info">
                            <div class="card-header text-info py-2">
                                <h6 class="m-0"><i class="fas fa-users me-2"></i>Grupo Familiar</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover align-middle text-center small" style="font-size: 0.8em;">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th style="width: 50px;">Edad</th>
                                                <th>Parentesco</th>
                                                <th>E.Civil</th>
                                                <th>Instrucción</th>
                                                <th>Ocupación</th>
                                                <th>Sueldo</th>
                                                <th>Aporte</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php for($i=1; $i<=5; $i++): ?>
                                            <tr>
                                                <td class="fw-bold"><?= $i ?></td>
                                                <td><input type="text" name="nombre<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloLetras(event)"></td>
                                                <td><input type="text" name="edad<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloNumeros(event)" maxlength="3"></td>
                                                <td><input type="text" name="parentesco<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloLetras(event)"></td>
                                                <td>
                                                    <select class="form-select form-select-sm p-1" name="edoCivil<?= $i ?>" id="edoCivil<?= $i ?>">
                                                        <option value="" selected></option>
                                                        <option value="Soltero">S</option>
                                                        <option value="Casado">C</option>
                                                        <option value="Divorciado">D</option>
                                                        <option value="Viudo">V</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" name="gradoInstruccion<?= $i ?>" class="form-control form-control-sm p-1"></td>
                                                <td><input type="text" name="ocupacion<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloLetras(event)"></td>
                                                <td><input type="text" name="sueldo<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></td>
                                                <td><input type="text" name="aporteHogar<?= $i ?>" class="form-control form-control-sm p-1" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></td>
                                            </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="cambiarPaso('paso2')"><i class="fas fa-arrow-left"></i> Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="cambiarPaso('paso4')">Siguiente <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- PASO 4: Ingresos y Egresos -->
                    <div class="tab-pane fade" id="paso4" role="tabpanel">
                        <div class="row">
                            <!-- Ingresos -->
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm h-100 border-left-success">
                                    <div class="card-header text-success py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-arrow-up me-2"></i>Ingresos</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Sueldo:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_sueldo" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Particul.:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_trabajos" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Renta:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_renta" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Pens/Jub:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_pensiones" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Ayudas:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_ayudas" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Otros:</label></div>
                                            <div class="col-7"><input type="text" name="ingreso_otros" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <hr class="my-1">
                                        <div class="row g-2 align-items-center fw-bold text-success">
                                            <div class="col-5"><label class="form-label small mb-0">TOTAL:</label></div>
                                            <div class="col-7"><input type="text" name="total_ingresos" class="form-control form-control-sm bg-light fw-bold text-success border-success" readonly onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Egresos -->
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm h-100 border-left-danger">
                                    <div class="card-header text-danger py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-arrow-down me-2"></i>Egresos</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Aliment.:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_alimentacion" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Vivienda:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_vivienda" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Servicios:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_servicios" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Educación:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_educacion" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Transporte:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_transporte" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <div class="row g-2 align-items-center mb-1">
                                            <div class="col-5"><label class="form-label small mb-0">Salud:</label></div>
                                            <div class="col-7"><input type="text" name="egreso_salud" class="form-control form-control-sm" onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                        <hr class="my-1">
                                        <div class="row g-2 align-items-center fw-bold text-danger">
                                            <div class="col-5"><label class="form-label small mb-0">TOTAL:</label></div>
                                            <div class="col-7"><input type="text" name="total_egresos" class="form-control form-control-sm bg-light fw-bold text-danger border-danger" readonly onkeypress="soloSueldos(event)" value="BsD " oninput="mantenerPrefijo(event)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Vivienda -->
                            <div class="col-12">
                                <div class="card shadow-sm border-left-warning">
                                    <div class="card-header text-warning py-2">
                                        <h6 class="m-0 font-size-sm"><i class="fas fa-home me-2"></i>Datos de Vivienda</h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="row">
                                            <div class="col-md-6 border-end">
                                                <h6 class="fw-bold mb-2 small border-bottom pb-1">Tenencia</h6>
                                                <div class="row small">
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="propia" value="Propia"><label class="form-check-label">Propia</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="opcion_compra" value="X"><label class="form-check-label">Opc. Compra</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="alquilada" value="X"><label class="form-check-label">Alquilada</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="prestada" value="X"><label class="form-check-label">Prestada</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="hipoteca" value="X"><label class="form-check-label">Hipotecada</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="pagando" value="X"><label class="form-check-label">Pagando</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="tenencia_otros" value="X"><label class="form-check-label">Otros</label></div></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ps-3">
                                                <h6 class="fw-bold mb-2 small border-bottom pb-1">Tipo</h6>
                                                <div class="row small">
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="casa" value="Casa"><label class="form-check-label">Casa</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="quinta" value="X"><label class="form-check-label">Quinta</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="apto" value="X"><label class="form-check-label">Apartamento</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="rural" value="X"><label class="form-check-label">Viv. Rural</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="inavi" value="X"><label class="form-check-label">Viv. INAVI</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="r_r" value="X"><label class="form-check-label">Rancho R.</label></div></div>
                                                    <div class="col-6 mb-1"><div class="form-check"><input class="form-check-input" type="radio" name="r_u" value="X"><label class="form-check-label">Rancho U.</label></div></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="cambiarPaso('paso3')"><i class="fas fa-arrow-left"></i> Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="cambiarPaso('paso5')">Siguiente <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    
                    <!-- PASO 5: Observaciones -->
                    <div class="tab-pane fade" id="paso5" role="tabpanel">
                        <div class="card shadow-sm border-left-secondary h-100">
                            <div class="card-header text-secondary py-2">
                                <h6 class="m-0 font-size-sm"><i class="fas fa-comment-alt me-2"></i>Observaciones Finales</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group h-100">
                                    <textarea class="form-control" name="observaciones" id="observaciones" rows="12" placeholder="Escriba aquí cualquier observación pertinente al caso..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                                            <button type="button" class="btn btn-secondary" id="btnCerrarOff" data-bs-dismiss="offcanvas"><i class="fas fa-times me-2"></i> Cerrar</button>
                                            <button type="button" class="btn btn-success btn-lg" id="btnGenerarPDF">
                                                <i class="fas fa-file-pdf me-2"></i> Generar PDF
                                            </button>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
        </form>
    </div>
</div>
