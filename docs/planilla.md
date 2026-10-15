# Formato de casos de pruebas funcionales

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-001 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-EMP-01 Registrar Empleado | **MÓDULO DEL SISTEMA** | Empleados (id_modulo 1) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del proceso de registro de nuevos empleados en el sistema (`dirpoles_security.empleado`), verificando la creación exitosa con datos válidos, validación de obligatoriedad de campos, prevención de documentos y teléfonos duplicados, formato móvil venezolano y cumplimiento de la edad mínima laboral (>= 15 años).</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Base de datos de seguridad (`dirpoles_security`) activa y accesible mediante socket/conexión PDO.
* Catálogo de tipos de empleado precargado en la tabla `tipo_empleado`.
* Usuario con permisos de creación en el módulo Empleados (`empleados.crear`).

### Pasos de la prueba
1. Instanciar `App\Models\EmpleadoModel` y asignar atributos válidos mediante `__set`: nombre, apellido, tipo_cedula ('V'), cedula única, correo corporativo, teléfono móvil (0412/0414/0416/0424/0426), id_tipo_empleado, fecha_nacimiento (>= 15 años), direccion, clave (>= 8 caracteres) y estatus (1).
2. Invocar `$modelo->manejarAccion('crear')` y constatar la generación del identificador `id_empleado` y la persistencia en base de datos.
3. Evaluar asignación de campo obligatorio vacío (nombre con espacios en blanco).
4. Evaluar inserción de un nuevo registro con cédula ya registrada en la base de datos.
5. Evaluar asignación de número telefónico sin formato móvil nacional venezolano (ej: código fijo de área).
6. Evaluar asignación de fecha de nacimiento correspondiente a una edad menor a 15 años.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `nombre, apellido, tipo_cedula, cedula, correo, telefono, clave` | Datos íntegros válidos (Cédula: V-99XXXXXX, Tel: 0412XXXXXXX, Clave: ClaveSegura123, Edad: 36 años) | Positivo | Registrar el nuevo empleado en BD y retornar array con `id_empleado` generado y datos básicos (HTTP 201) | [X] | [ ] | Retorna `['id_empleado' => N, 'nombre' => 'Carlos', 'apellido' => 'Auditor', 'correo' => '...']`. Inserción en `empleado` verificada. |
| `nombre` | `'   '` (Cadena vacía o solo espacios) | Negativo | Rechazar la asignación arrojando error de campo obligatorio | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El nombre es obligatorio." |
| `cedula` | Cédula preexistente en BD (ej: V-98XXXXXX) | Negativo | Bloquear la operación por detección de duplicidad documental | [X] | [ ] | Lanza `ExcepcionApi::yaExiste` (HTTP 409): "Esa cédula ya está registrada en el sistema." |
| `telefono` | `'02511234567'` (Formato fijo no móvil) | Negativo | Rechazar la asignación exigiendo formato móvil venezolano de 11 dígitos | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más..." |
| `fecha_nacimiento` | Fecha correspondiente a 12 años de edad (`-12 years`) | Negativo | Bloquear el registro por incumplimiento de edad mínima laboral (15 años) | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El empleado debe tener al menos 15 años." |

### Post condiciones
* Registro persistido en la tabla `empleado` con contraseña cifrada mediante `PASSWORD_BCRYPT`.
* Sin registros huérfanos ni alteraciones en la base de datos tras las pruebas negativas.
* Registro de auditoría administrativa preparado para asentar en `bitacora`.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. El modelo `EmpleadoModel` y su capa mutadora validan estrictamente formatos, unicidad y reglas de negocio antes de tocar la base de datos. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\EmpleadoTest`). Se verificó la unicidad de teléfono y cédula a nivel global en la BD de seguridad. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-002 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-EMP-02 Actualizar Empleado | **MÓDULO DEL SISTEMA** | Empleados (id_modulo 1) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del proceso de modificación de datos de empleados registrados, comprobando la actualización exitosa de atributos mutables, la exclusión del propio ID en la verificación de unicidad, el rechazo ante identificadores inexistentes y el bloqueo por usurpación de cédula ajena.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Empleado registrado previamente en la tabla `dirpoles_security.empleado`.
* Conexión a la base de datos de seguridad disponible.
* Usuario con permisos de edición en el módulo Empleados (`empleados.editar`).

### Pasos de la prueba
1. Instanciar `App\Models\EmpleadoModel` asignando el `id_empleado` existente junto con los nuevos valores de nombre, apellido, correo y dirección.
2. Invocar `$modelo->manejarAccion('actualizar')` y verificar la actualización efectiva del registro en la base de datos.
3. Intentar actualizar asignando un identificador de empleado inexistente (`id_empleado = 999999`).
4. Intentar actualizar un empleado asignándole la cédula de identidad que pertenece a otro empleado distinto.
5. Intentar asignar una propiedad inexistente al modelo mediante `$modelo->__set('campo_inexistente', ...)`.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `id_empleado, nombre, direccion, correo` | ID válido existente, `'NombreModificado'`, `'Nueva Direccion Actualizada'` | Positivo | Actualizar los campos en BD y retornar array confirmando `id_empleado`, `nombre` y `apellido` | [X] | [ ] | Retorna `['id_empleado' => N, 'nombre' => 'NombreModificado', 'apellido' => '...']`. Consulta posterior en BD confirma la modificación. |
| `id_empleado` | `999999` (ID inexistente en BD) | Negativo | Rechazar la actualización informando que el recurso no fue encontrado (HTTP 404) | [X] | [ ] | Lanza `ExcepcionApi::noEncontrado` (HTTP 404): "El empleado no existe." |
| `cedula` | Cédula asignada a otro empleado registrado en el sistema | Negativo | Bloquear la operación para prevenir colisión o usurpación de identidad documental | [X] | [ ] | Lanza `ExcepcionApi::yaExiste` (HTTP 409): "Esa cédula ya está registrada en el sistema." |
| `campo_inexistente` | `'valor'` (Propiedad no contemplada en el esquema) | Negativo | Bloquear la asignación impidiendo inyección de atributos no reconocidos | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "Atributo no reconocido: 'campo_inexistente'." |

### Post condiciones
* Fila del empleado actualizada en `dirpoles_security.empleado`.
* La contraseña anterior permanece inalterada si no se remite una nueva clave en el payload de actualización.
* Consistencia documental preservada ante intentos de duplicidad.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. El modelo utiliza consultas preparadas seguras y aplica la cláusula de exclusión de ID (`id_excluir`) de manera correcta durante la validación de unicidad. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\EmpleadoTest`). Se constató que el método `actualizar()` mantiene intacto el hash bcrypt si la clave viene vacía. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-003 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-BEN-01 Registrar Beneficiario | **MÓDULO DEL SISTEMA** | Beneficiarios (id_modulo 2) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del registro de estudiantes beneficiarios en `dirpoles_business.beneficiario`, validando la asignación obligatoria a un PNF académico, formato de cédula nacional, género binario (M/F), teléfono móvil nacional y coherencia cronológica en fecha de nacimiento.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Base de datos de negocio (`dirpoles_business`) activa.
* Catálogo de PNF con registros vigentes en la tabla `pnf`.
* Usuario con permisos de creación en el módulo Beneficiarios (`beneficiarios.crear`).

### Pasos de la prueba
1. Instanciar `App\Models\BeneficiarioModel` y parametrizar los datos obligatorios del estudiante (id_pnf, seccion, nombres, apellidos, tipo_cedula, cedula única, fecha_nac, telefono móvil, correo, genero 'M'/'F').
2. Invocar `$modelo->manejarAccion('crear')` y comprobar el retorno del ID autoincremental y los datos del beneficiario.
3. Evaluar intento de registro duplicado utilizando una cédula preexistente en la base de datos.
4. Evaluar asignación de un valor de género inválido (distinto a 'M' o 'F', ej: 'X').
5. Evaluar asignación de teléfono con formato no móvil venezolano.
6. Evaluar asignación de una fecha de nacimiento futura respecto a la fecha del sistema.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `id_pnf, seccion, cedula, correo, genero, fecha_nac` | PNF: 1, Sección: 'INFO-3101', Cédula: V-88XXXXXX, Género: 'M', Fecha: '2002-04-10' | Positivo | Registrar al beneficiario con estatus activo (1) y retornar array con `id_beneficiario`, `nombres` y `cedula` | [X] | [ ] | Retorna `['id_beneficiario' => N, 'nombres' => 'Estudiante', 'apellidos' => '...', 'cedula' => '...']`. Guardado en BD verificado. |
| `cedula` | Cédula ya existente registrada en la tabla `beneficiario` | Negativo | Bloquear el registro informando duplicidad de cédula en el sistema (HTTP 409) | [X] | [ ] | Lanza `ExcepcionApi::yaExiste` (HTTP 409): "Esa cédula ya está registrada en el sistema." |
| `genero` | `'X'` (Carácter no reconocido en el catálogo ENUM M/F) | Negativo | Rechazar la asignación con mensaje normativo de dominio | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El género debe ser M (Masculino) o F (Femenino)." |
| `telefono` | `'01234567890'` (Prefijo inválido no perteneciente a operadoras móviles) | Negativo | Exigir prefijos móviles nacionales válidos (0412, 0414, 0416, 0422, 0424, 0426) | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El teléfono debe empezar por 0412, 0414, 0416, 0422, 0424 o 0426 y tener 7 dígitos más..." |
| `fecha_nac` | Fecha futura respecto al día de hoy (`+1 year`) | Negativo | Rechazar la fecha por inconsistencia cronológica biológica | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "La fecha de nacimiento no puede ser futura." |

### Post condiciones
* Registro creado en la tabla `beneficiario` con fecha de creación `CURDATE()` y estatus 1 (Activo).
* La cédula queda protegida para evitar colisiones transversales con otros perfiles.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. La validación en `BeneficiarioModel::__set` sanitiza y tipifica exhaustivamente cada atributo previo a la ejecución del SQL. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\BeneficiarioTest`). Validación de unicidad cruzada activa. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-004 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-BEN-02 Eliminación y Restricción de Integridad Referencial | **MÓDULO DEL SISTEMA** | Beneficiarios (id_modulo 2) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del principio de integridad referencial y prevención de registros huérfanos, verificando el bloqueo de eliminación física de beneficiarios que poseen historial clínico, citas o solicitudes asociadas, admitiendo eliminación exclusivamente en registros sin dependencias.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Beneficiario ID 1 con citas y servicios asociados en la base de datos de negocio.
* Beneficiario de prueba recién creado sin ningún registro hijo asociado.
* Usuario con permisos de eliminación en el módulo Beneficiarios (`beneficiarios.eliminar`).

### Pasos de la prueba
1. Instanciar `App\Models\BeneficiarioModel` y asignar `id_beneficiario = 1` (registro con citas vinculadas).
2. Invocar `$modelo->manejarAccion('eliminar')` y verificar que el sistema capture la restricción de integridad referencial emitiendo HTTP 409 (Conflicto / En Uso).
3. Evaluar eliminación de beneficiario indicando un ID inexistente (`id_beneficiario = 999999`).
4. Crear un beneficiario de prueba sin dependencias en servicios ni citas.
5. Invocar `$modelo->manejarAccion('eliminar')` sobre el beneficiario libre de dependencias.
6. Verificar mediante consulta SQL que el registro fue borrado físicamente de la tabla `beneficiario`.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `id_beneficiario` | `1` (Beneficiario con citas y atenciones asociadas en BD) | Negativo | Bloquear la eliminación física impidiendo la destrucción de evidencia y orfandad de datos clínicos | [X] | [ ] | Lanza `ExcepcionApi::enUso` (HTTP 409): "No se puede eliminar: el beneficiario tiene registros asociados (citas, consultas, jornadas, etc.). Puedes desactivarlo." |
| `id_beneficiario` | `999999` (ID inexistente en BD) | Negativo | Informar que el registro solicitado no existe (HTTP 404) | [X] | [ ] | Lanza `ExcepcionApi::noEncontrado` (HTTP 404): "El beneficiario no existe." |
| `id_beneficiario` | ID de beneficiario recién creado sin registros asociados | Positivo | Eliminar físicamente el registro de la tabla `beneficiario` y devolver `true` | [X] | [ ] | Retorna `true`. Verificación posterior mediante `SELECT COUNT(*)` arroja 0 registros. |

### Post condiciones
* Expedientes e historiales médicos/psicológicos resguardados contra borrado accidental o malintencionado.
* Depuración efectiva de registros transitorios creados por error sin dependencias.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. La captura de excepciones SQLSTATE 23000 de clave foránea previene caídas técnicas y las transforma en excepciones de negocio controladas (`ExcepcionApi::enUso`). | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\BeneficiarioTest`). Cumplimiento cabal de normativas de auditoría de retención de datos. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-005 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-CIT-01 Agendamiento y Validación de Solapamiento Temporal de Citas | **MÓDULO DEL SISTEMA** | Citas (id_modulo 3) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del algoritmo de reserva y control de disponibilidad temporal para citas psicológicas, certificando el bloqueo de fechas pasadas, control de intervalos en punto o media hora, correspondencia con el horario laboral asignado al psicólogo y prevención de sobreventa/solapamiento de citas.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Empleado con rol Psicólogo (id_empleado = 3, Iris Alvarez) con horario laboral registrado (Lunes 08:00 a 13:00).
* Beneficiario activo en el sistema.
* Usuario con permisos de creación de citas (`citas.crear`).

### Pasos de la prueba
1. Instanciar `App\Models\CitaModel` configurando fecha de lunes hábil futuro a las 09:00 (dentro de jornada laboral) para el psicólogo ID 3 y beneficiario ID 1.
2. Invocar `$modelo->manejarAccion('crear')` y verificar el registro con estatus 1 (Pendiente).
3. Evaluar intento de agendamiento con fecha en el pasado ('2022-01-10').
4. Evaluar agendamiento con minutos que no inicien en punto ni a media hora (ej: '09:15').
5. Evaluar agendamiento fuera del horario laboral configurado del especialista (ej: 16:00).
6. Intentar agendar una segunda cita para el mismo psicólogo en la misma fecha y exactamente en la misma hora (10:00).

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `fecha, hora, id_empleado, id_beneficiario` | Lunes hábil futuro, `'09:00'`, Psicólogo ID: 3, Beneficiario ID: 1 | Positivo | Agendar cita con estatus inicial 1 (Pendiente) y retornar identificador autoincremental `id_cita` | [X] | [ ] | Retorna `['id_cita' => N, 'id_empleado' => 3, 'estatus' => 1]`. Inserción en `cita` confirmada. |
| `fecha` | `'2022-01-10'` (Fecha anterior al día de hoy) | Negativo | Bloquear la reserva en fechas pretéritas | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "La fecha de la cita no puede ser pasada." |
| `hora` | `'09:15'` (Minutos no divisibles entre 30) | Negativo | Exigir turnos estándar de consulta que inicien en punto (:00) o a media hora (:30) | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "Las citas deben iniciar en punto o a media hora." |
| `hora` | `'16:00'` (Fuera del rango laboral 08:00 - 13:00 de los lunes) | Negativo | Bloquear agendamiento por incompatibilidad con el horario del profesional | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "La hora seleccionada está fuera del horario del psicólogo." |
| `fecha, hora, id_empleado` | Misma fecha hábil y misma hora `'10:00'` de una cita activa ya reservada | Negativo | Prevenir colisión o solapamiento temporal en la agenda del especialista (HTTP 409) | [X] | [ ] | Lanza `ExcepcionApi::yaExiste` (HTTP 409): "El psicólogo ya tiene una cita en ese horario." |

### Post condiciones
* Registro persistido en la tabla `cita` con estatus Pendiente (1).
* Garantía de concurrencia y agenda libre de solapamientos para el profesional de la salud.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. El motor de validación en `validarDisponibilidad` coteja tanto la tabla `horario` como la existencia de citas no canceladas (`estatus <> 4`) con intervalos de 1 hora. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\CitasTest`). Se comprobó la precisión matemática del control temporal. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-006 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-CIT-02 Transición de Estados y Control de Alcance de Citas | **MÓDULO DEL SISTEMA** | Citas (id_modulo 3) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del ciclo de vida y transición de estados de las citas psicológicas (Pendiente, Confirmada, Atendida, Cancelada, No asistió) y del control de acceso por alcance de usuario (Row-Level Security), impidiendo la alteración de citas de terceros por psicólogos no asignados.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Cita creada en estado Pendiente (1) perteneciente al psicólogo ID 3.
* Conexión a la base de datos de negocio activa.
* Usuario con permisos de edición de citas (`citas.editar`).

### Pasos de la prueba
1. Instanciar `App\Models\CitaModel` sobre una cita existente y asignar nuevo estado `estatus = 2` (Confirmada) bajo sesión administrativa o del psicólogo titular.
2. Invocar `$modelo->manejarAccion('actualizar_estado')` y comprobar la persistencia del nuevo estado en la base de datos.
3. Evaluar asignación de un código de estado fuera del catálogo (`estatus = 99`).
4. Configurar el modelo simulando una sesión de psicólogo ajeno (`tipo_empleado = 'Psicologo'`, `id_usuario = 99`) e intentar modificar una cita que pertenece al psicólogo ID 3.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `id_cita, estatus` | Cita ID válida, nuevo `estatus: 2` (Confirmada) | Positivo | Actualizar el estado de la cita en BD y devolver array con confirmación de `id_cita` y nuevo `estatus` | [X] | [ ] | Retorna `['id_cita' => N, 'estatus' => 2]`. En BD el campo `estatus` pasa a 2. |
| `estatus` | `99` (Estado inválido no perteneciente a `[1, 2, 3, 4, 5]`) | Negativo | Bloquear la asignación por violación de integridad de catálogo de estados | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El estado de la cita no es válido." |
| `id_usuario, tipo_empleado, id_cita` | Psicólogo ID 99 intentando operar sobre cita asignada al Psicólogo ID 3 | Negativo | Denegar la operación por violación de alcance de usuario no administrador (HTTP 404) | [X] | [ ] | Lanza `ExcepcionApi::noEncontrado` (HTTP 404): "La cita no existe o no está disponible para tu usuario." |

### Post condiciones
* Estado de la cita actualizado congruentemente en la tabla `cita`.
* Los psicólogos solo visualizan y modifican sus propias consultas, preservando el secreto profesional y la privacidad del paciente.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. El método `asegurarAlcanceCita` inyecta automáticamente la cláusula `AND c.id_empleado = :id_usuario` cuando el solicitante no es Administrador o Superusuario. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\CitasTest`). El control de acceso basado en roles y alcance opera sin fallas. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-007 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-INV-01 Registro de Insumo Médico y Control de Alta en Kardex | **MÓDULO DEL SISTEMA** | Inventario Médico (id_modulo 9) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del proceso de creación de insumos médicos en almacén (`dirpoles_business.insumos`), verificando que los insumos nazcan con cantidad 0 y estatus 'Agotado', rechazo de fechas de vencimiento pasadas, validación de catálogo de tipos y generación simultánea del movimiento 'Registro' en el kardex (`inventario_medico`).</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Tablas `insumos`, `inventario_medico` y `presentacion_insumo` creadas en `dirpoles_business`.
* Empleado responsable autenticado con permisos de creación (`inventario.crear`).

### Pasos de la prueba
1. Instanciar `App\Models\InventarioModel` con datos válidos de insumo (nombre con caracteres permitidos, tipo 'Medicamento', presentación 1, fecha de vencimiento futura, descripción, id_empleado 1).
2. Invocar `$modelo->manejarAccion('crear')` y constatar la respuesta exitosa.
3. Verificar en la base de datos que el insumo se guarde con `cantidad = 0` y `estatus = 'Agotado'`.
4. Verificar en la tabla `inventario_medico` la inserción atómica del movimiento con `tipo_movimiento = 'Registro'` y `cantidad = 0`.
5. Evaluar intento de alta con fecha de vencimiento caducada / pretérita.
6. Evaluar asignación de un tipo de insumo no existente en el catálogo (ej: 'Comestible').
7. Evaluar intento de registro duplicado con idéntica presentación, nombre, tipo y fecha de vencimiento.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `nombre_insumo, tipo_insumo, id_presentacion, fecha_vencimiento` | 'Amoxicilina XXXXmg', 'Medicamento', Pres: 1, Fecha: +1 año, Desc: 'Antibiotico QA' | Positivo | Crear insumo con stock 0, estatus 'Agotado' y asentar movimiento inicial 'Registro' en el kardex | [X] | [ ] | Retorna `['id_insumo' => N, 'nombre_insumo' => '...', ...]`. BD: `cantidad=0`, `estatus='Agotado'`, asiento en kardex verificado. |
| `fecha_vencimiento` | `'2023-01-01'` (Fecha caducada en el pasado) | Negativo | Bloquear el alta de insumos o medicamentos ya caducados | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "La fecha de vencimiento no puede ser una fecha pasada." |
| `tipo_insumo` | `'Comestible'` (Valor no perteneciente a Medicamento, Material o Quirúrgico) | Negativo | Rechazar la asignación con mensaje de tipo no válido | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El tipo de insumo no es válido." |
| `insumo` | Mismo nombre, tipo, presentación y fecha de un insumo ya existente | Negativo | Bloquear duplicidad de insumo en inventario (HTTP 409) | [X] | [ ] | Lanza `ExcepcionApi::yaExiste` (HTTP 409): "Ese insumo ya está registrado en el inventario médico." |

### Post condiciones
* Insumo persistido en `insumos` con existencia cero, esperando su formalización de ingreso mediante documento de Entrada.
* Movimiento de auditoría 'Registro' registrado en `inventario_medico` dentro de la misma transacción.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. Las operaciones de inserción en maestro y kardex se ejecutan bajo transacción PDO (`beginTransaction` / `commit`), asegurando atomicidad absoluta. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\InventarioMedicoTest`). Se garantiza que ningún stock inicial sea inventado en el alta. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |

<br><br>

---

## INFORMACIÓN GLOBAL DEL CASO DE PRUEBA
| Campo | Valor | Campo | Valor |
| :--- | :--- | :--- | :--- |
| **CASO DE PRUEBA No.** | CP-008 | **FECHA EJECUCIÓN** | 15/10/2026 |
| **CASO DE USO:** | CU-INV-02 Salida de Insumos, Kardex y Prevención de Stock Negativo | **MÓDULO DEL SISTEMA** | Inventario Médico (id_modulo 9) |
| **Descripción del caso de prueba:** | <td colspan="3">Auditoría del egreso de stock de almacén médico, certificando la prevención rigurosa de existencias negativas, el descuento concurrente seguro (`FOR UPDATE`), la exigencia de motivo formal de salida, el registro inmutable en kardex y la transición automática del estatus a 'Agotado' al vaciarse el stock.</td> |

---

## CASO DE PRUEBA

### Precondiciones
* Insumo médico creado con saldo de stock positivo disponible mediante una 'Entrada' previa de almacén.
* Conexión a `dirpoles_business` activa con soporte para transacciones y bloqueos pesimistas.
* Empleado responsable autenticado con permisos de inventario (`inventario.crear` o `inventario.editar`).

### Pasos de la prueba
1. Registrar un insumo de prueba e ingresar 20 unidades de stock mediante la acción 'entrada'.
2. Instanciar `App\Models\InventarioModel`, parametrizar salida de 5 unidades con motivo justificado 'Uso Interno' e invocar `$modelo->manejarAccion('salida')`.
3. Verificar que el saldo de stock en `insumos` disminuya exactamente a 15 y que se genere un registro con `tipo_movimiento = 'Salida'` en `inventario_medico`.
4. Evaluar intento de retiro de una cantidad mayor al stock disponible (ej: retirar 25 unidades cuando solo hay 10).
5. Evaluar intento de salida asignando un motivo fuera del catálogo ('MotivoInvalido').
6. Retirar la totalidad de las unidades remanentes de un insumo y constatar que el stock final sea 0 y que el estatus se actualice automáticamente a 'Agotado'.

### Datos y Escenarios de Prueba
| CAMPO | VALOR | TIPO ESCENARIO (Válido/Inválido) | RESPUESTA ESPERADA DE LA APLICACIÓN | COINCIDE (SI) | COINCIDE (NO) | RESPUESTA DEL SISTEMA |
| :--- | :--- | :--- | :--- | :---: | :---: | :--- |
| `id_insumo, cantidad, motivo` | Insumo con stock de 20 unidades, `cantidad: 5`, `motivo: 'Uso Interno'` | Positivo | Descontar stock a 15, mantener estatus 'Disponible' y asentar salida en kardex con justificación | [X] | [ ] | Retorna `['id_insumo' => N, 'cantidad' => 5, 'stock_total' => 15]`. BD: `cantidad=15`, estatus 'Disponible' y fila en `inventario_medico` confirmada. |
| `cantidad` | `25` unidades (Existencia física en almacén: 10 unidades) | Negativo | Bloquear la salida impidiendo el sobregiro o existencias negativas en almacén | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "Stock insuficiente para \"...\" (disponible: 10)." |
| `motivo` | `'MotivoInvalido'` (Fuera de Vencimiento, Daño, Pérdida, Donación o Uso Interno) | Negativo | Exigir obligatoriamente una causa tipificada y justificada para la salida de inventario | [X] | [ ] | Lanza `ExcepcionApi::validacion` (HTTP 400): "El motivo de la salida no es válido." |
| `cantidad` | Retiro total del saldo disponible (8 de 8 unidades disponibles) | Positivo | Vaciado total de existencia: saldo final 0 y estatus actualizado automáticamente a 'Agotado' | [X] | [ ] | Retorna `['stock_total' => 0]`. BD: `cantidad = 0` y `estatus = 'Agotado'` comprobados. |

### Post condiciones
* Stock actualizado congruentemente en la tabla `insumos`.
* Asiento contable de movimiento asentado de forma indeleble en `inventario_medico`.
* Prevención absoluta de saldos negativos o inconsistencias en los libros de farmacia.

---

## RESULTADOS DE LA PRUEBA

| Defectos y desviaciones | Veredicto (Aprobado / Fallido) |
| :--- | :--- |
| Ninguno. La lectura previa con `FOR UPDATE` y el recálculo aritmético previenen colisiones de concurrencia y sobregiros de stock. | Aprobado |

| Observaciones | Probador |
| :--- | :--- |
| Ejecutado automatizadamente en consola mediante PHPUnit 12.5.39 (`Tests\InventarioMedicoTest`). Control clave en auditoría financiera y operativa de almacén. | **Firma:** Ing. Auditor QA<br>**Nombre:** Auditor de Sistemas / QA<br>**Fecha:** 15/10/2026 |
