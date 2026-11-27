-- =====================================================================
-- SEED DE ATENCIONES DE ORIENTACIÓN (TANDA 2) — 60 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_orientacion2.sql
-- Módulo: Orientación Vocacional y Socioeducativa (id_modulo 6, id_servicios = 3)
--
-- Cobertura Temporal: 24/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Tablas involucradas: solicitud_de_servicio, orientacion
--
-- Distribución temporal:
--   * Cohorte 2024: 20 atenciones (enero a noviembre 2024)
--   * Cohorte 2025: 20 atenciones (enero a noviembre 2025)
--   * Cohorte 2026: 20 atenciones (enero al 17 de noviembre 2026)
--
-- Especialistas (Orientadores en dirpoles_security.empleado):
--   * Jhan Guevara                      (Cédula: 13652666) - 20 atenciones
--   * Edward José Urdaneta Briceño      (Cédula: 18364725) - 20 atenciones
--   * Nohelia Alexandra Quintero Márquez(Cédula: 22159408) - 20 atenciones
--
-- Reglas de negocio (OrientacionModel):
--   * Los 4 campos de texto son OBLIGATORIOS (motivo, descripción, indicaciones, observaciones).
--   * No utiliza detalle_patologia ni inventario (módulo de texto simple).
--   * Resuelve claves foráneas cross-schema de orientadores y beneficiarios.
--
-- 100% IDEMPOTENTE: omite registros si ya existen para el beneficiario y fecha.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_spanish2_ci;

-- =====================================================================
-- 1. PROCEDIMIENTO TEMPORAL DE CARGA TRANSACCIONAL E IDEMPOTENTE
-- =====================================================================
DELIMITER ;;
DROP PROCEDURE IF EXISTS registrar_orientacion_seed;;
CREATE PROCEDURE registrar_orientacion_seed(
    IN p_cedula_ori VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_motivo VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_indicaciones TEXT,
    IN p_observaciones TEXT,
    IN p_fecha_creacion DATE
)
BEGIN
    DECLARE v_id_ori INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_sol INT;

    -- 1. Resolver orientador
    SELECT id_empleado INTO v_id_ori
    FROM dirpoles_security.empleado
    WHERE cedula = p_cedula_ori COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- 2. Resolver beneficiario
    SELECT id_beneficiario INTO v_id_ben
    FROM beneficiario
    WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- Idempotencia: Verificar que orientador y beneficiario existan, y que no exista orientación previa en esa fecha
    IF v_id_ori IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM orientacion o
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben
              AND o.fecha_creacion = p_fecha_creacion
        ) THEN
            -- A) solicitud_de_servicio (id_servicios = 3 para Orientación)
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (3, v_id_ben, v_id_ori);
            SET v_id_sol = LAST_INSERT_ID();

            -- B) orientacion
            INSERT INTO orientacion (
                id_solicitud_serv, motivo_orientacion, descripcion_orientacion,
                indicaciones_orientacion, obs_adic_orientacion, fecha_creacion
            ) VALUES (
                v_id_sol, p_motivo, p_descripcion,
                p_indicaciones, p_observaciones, p_fecha_creacion
            );
        END IF;
    END IF;
END;;
DELIMITER ;

-- =====================================================================
-- 2. INSERTAR 60 ATENCIONES DE ORIENTACIÓN SINTÉTICAS
-- =====================================================================

-- ---------------------------------------------------------------------
-- COHORTE 2024 (20 Atenciones: Enero a Noviembre 2024)
-- ---------------------------------------------------------------------

-- Orientación 1: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '28114201',
    'Orientación sobre hábitos de estudio y rendimiento académico en ciencias administrativas.',
    'La estudiante manifiesta vacíos metodológicos en la organización de su tiempo libre y estudio independiente para materias cuantitativas.',
    'Crear una rutina de estudio fija de 2 horas diarias, emplear la técnica Pomodoro y elaborar mapas conceptuales.',
    'Muestra excelente disposición para adoptar nuevas técnicas de lectura analítica y resolución de casos.',
    '2024-01-24'
);

-- Orientación 2: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '29215302',
    'Asesoría vocacional y clarificación de metas profesionales en contaduría.',
    'Consulta sobre opciones de desempeño profesional, áreas de auditoría fiscal y oportunidades de inserción laboral en la región Lara.',
    'Revisar el diseño curricular del PNF e investigar el perfil de egreso en el Colegio de Contadores del Estado Lara.',
    'Se acuerda una segunda sesión para evaluar áreas de preferencia profesional y tributaria.',
    '2024-02-08'
);

-- Orientación 3: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '30316403',
    'Orientación para el abordaje del proyecto sociointegrador y vinculación comunitaria.',
    'Dificultades en la selección de la comunidad y definición del diagnóstico participativo en materia de desarrollo de software.',
    'Coordinar reunión con el tutor institucional y solicitar los formatos oficiales de vinculación comunitaria del trayecto.',
    'La estudiante asistirá a la asamblea de ciudadanos de la comunidad el próximo fin de semana.',
    '2024-02-21'
);

-- Orientación 4: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '31417504',
    'Acompañamiento en la adaptación al entorno académico universitario.',
    'Estudiante de nuevo ingreso reporta desorientación sobre la dinámica de bloques horarios y trámites académicos institucionales.',
    'Asistir a las jornadas de inducción estudiantil e incorporarse al programa de tutorías entre pares en Higiene y Seguridad Laboral.',
    'Se hace entrega formal de la guía de procedimientos al estudiante y el reglamento disciplinario.',
    '2024-03-06'
);

-- Orientación 5: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '27518605',
    'Asesoría en preparación de síntesis curricular deportiva y proyección laboral.',
    'Búsqueda de orientación para optar a pasantías en clubes deportivos y centros de alto rendimiento regional.',
    'Estructurar el currículum resaltando certificaciones de entrenamiento, proyectos de acondicionamiento físico y competencias técnicas.',
    'Revisión final de la síntesis curricular programada para la siguiente semana.',
    '2024-03-20'
);

-- Orientación 6: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '26619706',
    'Orientación ante bajo rendimiento en evaluaciones continuas de turismo patrimonial.',
    'Acude remitido tras reprobar evaluaciones parciales de geografía turística y gestión de servicios.',
    'Diseñar cuadro comparativo de asignaturas críticas, asistir a círculos de estudio grupal y tutorías de cátedra.',
    'Compromiso firmado de asistencia regular a asesorías docentes y seguimiento quincenal.',
    '2024-04-11'
);

-- Orientación 7: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '25720807',
    'Orientación en la toma de decisiones sobre carga horaria y créditos académicos.',
    'Evaluación de cantidad de unidades curriculares inscritas frente a responsabilidades laborales vespertinas.',
    'Solicitar ajuste de sección para evitar solapamientos con su jornada laboral en biblioteca o archivo.',
    'Trámite canalizado satisfactoriamente con la coordinación del PNF y control de estudios.',
    '2024-04-25'
);

-- Orientación 8: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '24821908',
    'Seguimiento pedagógico y planificación académica en sistemas de calidad.',
    'Sesión de control periódica para evaluar el cumplimiento del plan de estudio individual en normas ISO y ambiente.',
    'Mantener el ritmo de entregas académicas, priorizar la redacción del marco metodológico del proyecto.',
    'Resultados académicos positivos con incremento del promedio ponderado respecto a la consulta inicial.',
    '2024-05-09'
);

-- Orientación 9: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '23922019',
    'Orientación para la reincorporación al trayecto académico en agroalimentación.',
    'Estudiante reincorporada solicita guía sobre normativa de evaluación y asignación a prácticas en unidades de producción.',
    'Visitar los laboratorios de suelos y química para solicitar el plan de contingencia de prácticas extemporáneas.',
    'Estudiante orientada con éxito sobre el reglamento de evaluación de aprendizajes.',
    '2024-05-23'
);

-- Orientación 10: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '29023120',
    'Asesoría pedagógica en metodologías de investigación aplicada a la logística.',
    'Dudas conceptuales en la delimitación del problema y alcance territorial en el proyecto de cadenas de distribución.',
    'Consultar bibliografía recomendada en el centro de documentación y coordinar asesoría técnica con el docente metodólogo.',
    'Muestra comprensión adecuada de la ruta metodológica y variables de estudio.',
    '2024-06-13'
);

-- Orientación 11: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '30124231',
    'Orientación sobre becas y apoyos socioacadémicos institucionales.',
    'Consulta de requisitos y plazos para postular a los programas de becas y exoneraciones de la UPTAEB.',
    'Consignar recaudos socioeconómicos actualizados en la unidad de Trabajo Social de DIRPOLES.',
    'Remisión interna realizada para inicio de trámite socioeconómico formal.',
    '2024-06-26'
);

-- Orientación 12: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '31225342',
    'Estrategias de afrontamiento del pánico escénico en defensas de procesos químicos.',
    'Temor infundado y bloqueos de comunicación al presentar informes técnicos orales ante comités evaluadores.',
    'Realizar simulaciones grupales de exposición con retroalimentación estructurada entre compañeros de equipo.',
    'Mejoría notable de la autoconfianza y manejo de diapositivas durante la simulación guiada.',
    '2024-07-11'
);

-- Orientación 13: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '28326453',
    'Orientación en la resolución de conflictos interpersonales en equipo de proyecto.',
    'Desacuerdos entre los integrantes del grupo de desarrollo sobre el cronograma y división de entregables.',
    'Establecer acta de acuerdos internos de trabajo, definir responsables por módulo y aplicar metodología ágil.',
    'El equipo logró consensuar compromisos claros y firmar acuerdos de convivencia técnica.',
    '2024-07-24'
);

-- Orientación 14: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '27427564',
    'Asesoría vocacional para egresados de TSU hacia la prosecución de Licenciatura.',
    'Incertidumbre sobre los requisitos de prosecución de estudios y líneas de investigación disponibles.',
    'Revisar el baremo de prosecución académica del PNF y seleccionar una línea de investigación comunitaria afín.',
    'El estudiante formaliza su decisión de continuar los estudios conducentes al título de Licenciado.',
    '2024-08-07'
);

-- Orientación 15: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '26528675',
    'Orientación pedagógica sobre comprensión lectora y análisis de normas tributarias.',
    'Dificultades para interpretar providencias administrativas del SENIAT y manuales contables avanzados.',
    'Utilizar técnicas de subrayado jerárquico, mapas mentales y fichas de síntesis normativa.',
    'Se entregan guías prácticas de análisis documental y lectura comprensiva.',
    '2024-08-21'
);

-- Orientación 16: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '25629786',
    'Acompañamiento en el cumplimiento del servicio comunitario universitario.',
    'Dudas sobre la contabilización de las 120 horas lectivas y llenado de bitácoras de campo en prevención de riesgos.',
    'Registrar actividades semanalmente en la bitácora física y solicitar la firma del vocero del consejo comunal.',
    'Proceso de vinculación comunitaria en marcha sin contratiempos administrativos.',
    '2024-09-12'
);

-- Orientación 17: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '24730897',
    'Orientación para la organización del tiempo entre alta competencia deportiva y estudios.',
    'Horarios de entrenamientos de atletismo que interfieren con las horas de estudio independiente y tareas.',
    'Rediseñar horario semanal con bloques intensivos en fines de semana y pausas activas matutinas.',
    'Se logra una distribución horaria equilibrada respaldada por la coordinación de deportes.',
    '2024-09-25'
);

-- Orientación 18: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '29831908',
    'Asesoría para la solicitud de traslados de sección por razones de transporte.',
    'Inconvenientes severos con las rutas de transporte público desde su residencia en el norte de Barquisimeto.',
    'Consignar carta de residencia y constancia ante la coordinación del PNF para pase a sección matutina.',
    'Trámite administrativo canalizado satisfactoriamente ante la unidad de admisión y control de estudios.',
    '2024-10-16'
);

-- Orientación 19: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '30932019',
    'Orientación en técnicas de resolución de exámenes analíticos y catalogación.',
    'Reporta bloqueo mental al enfrentarse a pruebas escritas con límite estricto de tiempo.',
    'Aplicar lecturas iniciales globales de la prueba, responder primero las preguntas de mayor dominio y dosificar minutos.',
    'La estudiante pondrá en práctica las recomendaciones en las evaluaciones finales del trimestre.',
    '2024-11-07'
);

-- Orientación 20: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '31033120',
    'Acompañamiento pedagógico post-recuperación de salud física.',
    'Reincorporación a clases tras reposo médico prolongado con acumulación de asignaciones en calidad ambiental.',
    'Coordinar con cada docente un cronograma de entregas extemporáneas justificadas con constancia médica.',
    'Docentes notificados y receptivos al plan de entrega progresivo.',
    '2024-11-28'
);

-- ---------------------------------------------------------------------
-- COHORTE 2025 (20 Atenciones: Enero a Noviembre 2025)
-- ---------------------------------------------------------------------

-- Orientación 21: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '28134231',
    'Orientación sobre canales institucionales de atención al estudiante y bienestar.',
    'Desconocimiento del catálogo de servicios transversales disponibles en la UPTAEB para estudiantes foráneos.',
    'Informar sobre las unidades de Psicología, Medicina, Trabajo Social, Comedor y Biblioteca institucional.',
    'Se hace entrega de folleto digital informativo y directorio de dependencias institucionales.',
    '2025-01-16'
);

-- Orientación 22: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '27235342',
    'Asesoría para la formulación de objetivos de investigación en distribución y logística.',
    'Redacción ambigua en los objetivos específicos del proyecto sociointegrador comunitario.',
    'Utilizar la taxonomía de Bloom para seleccionar verbos en infinitivo medibles y orientados al diagnóstico.',
    'Objetivos corregidos y alineados con la metodología del Programa Nacional de Formación.',
    '2025-01-30'
);

-- Orientación 23: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '26336453',
    'Orientación preventiva sobre permanencia estudiantil en ingeniería de materiales.',
    'Dudas sobre continuar la carrera debido a desmotivación temporal en física y química de polímeros.',
    'Reevaluar los logros alcanzados, ingresar a tutorías personalizadas y definir metas a corto plazo.',
    'Estudiante decide continuar matriculada con acompañamiento y seguimiento mensual de orientación.',
    '2025-02-13'
);

-- Orientación 24: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '25437564',
    'Evaluación de cierre de acompañamiento pedagógico en procesos químicos.',
    'Sesión de balance final para verificar la consolidación de las metas académicas del trayecto formativo.',
    'Mantener las metodologías de estudio consolidadas y continuar apoyando a compañeros en círculos de estudio.',
    'Cierre de caso por cumplimiento satisfactorio y exitoso de todos los objetivos trazados.',
    '2025-02-27'
);

-- Orientación 25: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '29538675',
    'Estrategias de organización en programación y desarrollo web en informática.',
    'Estudiante manifiesta sobrecarga de entregables en desarrollo de software y bases de datos relacionales.',
    'Aplicar metodologías ágiles personales (tableros Kanban) para priorizar tareas y evitar postergaciones.',
    'Excelente receptividad; implementa tablero de organización semanal con metas diarias medibles.',
    '2025-03-13'
);

-- Orientación 26: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '30639786',
    'Orientación vocacional y liderazgo en gestión administrativa pública.',
    'Interés en especializarse en administración pública y formulación de presupuestos participativos.',
    'Investigar ordenanzas municipales del Estado Lara y participar en foros institucionales de gestión comunal.',
    'Se canaliza su participación en seminarios de extensión académica de la universidad.',
    '2025-03-26'
);

-- Orientación 27: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '31740897',
    'Manejo del estrés académico en cierres de auditoría y presentaciones finales.',
    'Ansiedad ante la entrega de balances generales y defensa oral de casos de auditoría contable.',
    'Ejercicios de respiración previa a exposiciones y estructuración de fichas guía de apoyo memorístico.',
    'Muestra mayor serenidad y confianza al articular los conceptos técnicos en la simulación.',
    '2025-04-10'
);

-- Orientación 28: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '28841908',
    'Asesoría para la conformación de brigadas de emergencia en higiene laboral.',
    'Dudas en el diseño de planes de contingencia comunitarios para su proyecto de prevención de incendios.',
    'Coordinar enlace con el cuerpo de bomberos universitarios y aplicar normas COVENIN correspondientes.',
    'Enlace institucional gestionado oportunamente para la validación del protocolo de seguridad.',
    '2025-04-24'
);

-- Orientación 29: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '27942019',
    'Orientación en planificación de carrera y arbitraje deportivo universitario.',
    'Búsqueda de avales para realizar cursos de formación como juez y árbitro de voleibol regional.',
    'Presentar carta de solicitud ante el departamento de deportes y gestionar convalidación de horas.',
    'Solicitud avalada satisfactoriamente para potenciar su perfil profesional técnico.',
    '2025-05-15'
);

-- Orientación 30: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '26043120',
    'Asesoría en técnicas de atención al usuario y hospitalidad turística.',
    'Fortalecimiento de habilidades blandas y comunicación asertiva para prácticas de guiado turístico.',
    'Prácticas de expresión corporal, modulación de voz y manejo de dinámicas de grupo en espacios abiertos.',
    'Desempeño sobresaliente en la puesta en escena y manejo de grupos recreativos.',
    '2025-05-28'
);

-- Orientación 31: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '25144231',
    'Orientación en digitalización y preservación de documentos históricos.',
    'Dudas metodológicas sobre normas archivísticas internacionales aplicables a fondos documentales locales.',
    'Consultar la norma ISAD(G) y los manuales del Archivo General de la Nación disponibles en repositorio.',
    'Se entrega esquema conceptual y guía de buenas prácticas en archivología digital.',
    '2025-06-12'
);

-- Orientación 32: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '29245342',
    'Asesoría pedagógica en monitoreo de variables de impacto ambiental.',
    'Dificultad para estructurar la matriz de Leopold en el diagnóstico socioambiental de una quebrada local.',
    'Realizar trabajo de campo con cuadrículas de muestreo y consultar al docente especialista en ecología.',
    'El estudiante aclara el procedimiento cuantitativo de valoración de impactos ambientales.',
    '2025-06-25'
);

-- Orientación 33: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '30346453',
    'Orientación sobre soberanía alimentaria y proyectos de huertos escolares.',
    'Planificación de talleres comunitarios sobre bioinsumos y siembra urbana en escuelas primarias.',
    'Redactar guías didácticas ilustradas adaptadas a niños de educación básica y coordinar fechas de talleres.',
    'Material pedagógico revisado y aprobado para su difusión en comunidades del oeste de Barquisimeto.',
    '2025-07-10'
);

-- Orientación 34: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '31447564',
    'Orientación en optimización de rutas de transporte de carga y almacenes.',
    'Modelado de rutas de distribución para el proyecto sociointegrador de abastecimiento local.',
    'Utilizar diagramas de flujo de procesos logísticos y aplicar el método de ruta crítica (CPM).',
    'Comprensión satisfactoria de los modelos de optimización de transporte y logística.',
    '2025-07-23'
);

-- Orientación 35: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '28548675',
    'Asesoría en seguridad industrial dentro de laboratorios de fundición y metalurgia.',
    'Consulta de protocolos de protección personal y manejo seguro de hornos de inducción.',
    'Repasar normativas de seguridad en caliente y verificar el uso de equipos de protección individual obligatorios.',
    'Estudiante capacitada en la cultura de prevención de riesgos mecánicos e industriales.',
    '2025-08-14'
);

-- Orientación 36: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '27649786',
    'Orientación para la formulación de proyectos de química verde y sostenibilidad.',
    'Sustitución de solventes tóxicos en síntesis orgánica comunitaria dentro de su proyecto de grado.',
    'Revisar los 12 principios de la química verde y consultar artículos indexados en el repositorio de la universidad.',
    'Propuesta metodológica innovadora con excelente fundamento bibliográfico.',
    '2025-09-04'
);

-- Orientación 37: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '26750897',
    'Asesoría en preparación para pasantías en soporte técnico y redes.',
    'Orientación sobre expectativas de desempeño laboral en centros de cómputo del sector público.',
    'Reforzar conceptos de configuración de enrutadores, cableado estructurado y ética profesional informática.',
    'Estudiante bien preparada técnicamente; se refuerzan destrezas de comunicación con usuarios no técnicos.',
    '2025-09-24'
);

-- Orientación 38: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '25851908',
    'Orientación en técnicas de negociación y mediación comunal.',
    'Manejo de discrepancias en asambleas de ciudadanos durante el diagnóstico de proyectos participativos.',
    'Aplicar técnicas de escucha activa, parafraseo y formulación de preguntas abiertas para desescalar tensiones.',
    'Se realizaron simulaciones de mediación con resultados muy favorables en fluidez comunicativa.',
    '2025-10-15'
);

-- Orientación 39: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '29952019',
    'Acompañamiento en el cierre de ciclo contable y elaboración de memorias.',
    'Dificultades en la redacción formal de notas a los estados financieros de su proyecto contable.',
    'Consultar el modelo de notas establecido en las Normas Internacionales de Información Financiera (NIIF para PYMES).',
    'Se entrega plantilla de notas explicativas y se programa revisión de avance en dos semanas.',
    '2025-11-05'
);

-- Orientación 40: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '30053120',
    'Orientación pedagógica sobre diseño de mapas de riesgo laboral.',
    'Dudas en la simbología estandarizada y codificación de colores para planos de evacuación.',
    'Basarse estrictamente en la norma venezolana COVENIN 187 para colores y señales de seguridad.',
    'Planos corregidos conforme a los estándares de higiene y seguridad laboral vigentes.',
    '2025-11-26'
);

-- ---------------------------------------------------------------------
-- COHORTE 2026 (20 Atenciones: Enero al 17 de Noviembre 2026)
-- ---------------------------------------------------------------------

-- Orientación 41: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '31154231',
    'Orientación para la reincorporación deportiva tras reposo por lesión en tobillo.',
    'Ansiedad por reincorporarse a las prácticas de atletismo tras cumplimiento de reposo traumatológico.',
    'Coordinar con el entrenador ejercicios de bajo impacto, natación y fortalecimiento progresivo sin sobrecargas.',
    'Acompañamiento coordinado con el servicio médico para alta funcional deportiva paulatina.',
    '2026-01-15'
);

-- Orientación 42: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '28255342',
    'Asesoría vocacional en ecoturismo y diseño de rutas de senderismo en Lara.',
    'Interés en emprender iniciativas de turismo de naturaleza sustentable en el Parque Nacional Yacambú.',
    'Consultar la legislación ambiental de parques nacionales y diseñar manuales de conducta del visitante.',
    'Proyecto con alto potencial socioeconómico; se vincula con la cátedra de desarrollo endógeno.',
    '2026-01-29'
);

-- Orientación 43: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '27356453',
    'Orientación sobre conservación preventiva en archivos comunales y familiares.',
    'Planificación de jornadas de capacitación a voceros de consejos comunales sobre resguardo de documentos.',
    'Estructurar talleres prácticos de limpieza mecánica, control de humedad relativa y almacenamiento libre de ácido.',
    'Estudiante demuestra madurez metodológica en la divulgación de saberes archivísticos.',
    '2026-02-12'
);

-- Orientación 44: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '26457564',
    'Asesoría en auditorías ambientales comunitarias y manejo de desechos sólidos.',
    'Metodología para caracterizar residuos reciclables en comunidades urbanas de Barquisimeto.',
    'Aplicar técnicas de cuarteo de residuos y elaborar tablas de porcentaje en peso para reciclaje.',
    'Procedimiento de campo validado con éxito; estudiante lista para la fase de diagnóstico.',
    '2026-02-26'
);

-- Orientación 45: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '25558675',
    'Orientación en inocuidad alimentaria y buenas prácticas de manufactura (BPM).',
    'Dificultades para capacitar a elaboradores de alimentos artesanales en normas de higiene y manipulación.',
    'Diseñar material visual gráfico con infografías simples y demostraciones prácticas de lavado de manos.',
    'Estrategia didáctica muy acertada para el público comunitario destinatario.',
    '2026-03-12'
);

-- Orientación 46: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '29659786',
    'Asesoría en control de inventarios mediante software libre institucional.',
    'Integración de herramientas informáticas en la gestión de almacenes comunales de distribución.',
    'Emplear hojas de cálculo avanzadas y bases de datos relacionales abiertas para registro de entradas y salidas.',
    'El estudiante logra automatizar el kardex de prueba con fórmulas dinámicas.',
    '2026-03-25'
);

-- Orientación 47: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '30760897',
    'Orientación sobre caracterización de fallas en piezas mecánicas sometidas a fatiga.',
    'Dudas sobre la preparación metalográfica de probetas de acero para ensayos de microdureza.',
    'Seguir el protocolo estandarizado de desbaste con lijas de granulometría decreciente y pulido con alúmina.',
    'Procedimiento de laboratorio aclarado; se autoriza uso del microscopio óptico metalográfico.',
    '2026-04-09'
);

-- Orientación 48: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '31861908',
    'Asesoría en diseño de plantas de tratamiento de aguas residuales comunitarias.',
    'Balance de masa y dimensionamiento preliminar de sedimentadores secundarios.',
    'Revisar la bibliografía de ingeniería sanitaria y coordinar asesoría de cálculo con el profesor tutor.',
    'Comprensión clara de los balances hidráulicos y tiempos de retención requeridos.',
    '2026-04-23'
);

-- Orientación 49: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '28962019',
    'Orientación en ciberseguridad básica y protección de datos en sistemas académicos.',
    'Implementación de medidas de seguridad y cifrado en el proyecto de software comunitario.',
    'Investigar directrices de OWASP Top 10, hash seguro de contraseñas y validación estricta de formularios.',
    'Excelente criterio de seguridad informática defensiva demostrado por la estudiante.',
    '2026-05-14'
);

-- Orientación 50: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '27063120',
    'Asesoría en control interno y auditoría operativa en organizaciones públicas.',
    'Elaboración de flujogramas de procesos administrativos para detectar cuellos de botella en adquisiciones.',
    'Utilizar la notación estándar ANSI en los flujogramas y señalar los puntos de control clave.',
    'Flujogramas revisados y optimizados con criterios de transparencia administrativa.',
    '2026-05-27'
);

-- Orientación 51: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '26164231',
    'Orientación en redacción del informe de pasantías contables.',
    'Estructuración formal del informe técnico final de prácticas profesionales en empresas del sector agroindustrial.',
    'Seguir las normas de redacción institucional de la UPTAEB, cuidando citas en formato APA y anexos probatorios.',
    'Estructura capitular aprobada; se programan lecturas de avance metodológico.',
    '2026-06-11'
);

-- Orientación 52: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '25265342',
    'Asesoría en ergonomía en puestos de trabajo con pantallas de visualización de datos.',
    'Evaluación ergonómica mediante el método ROSA en oficinas administrativas de la universidad.',
    'Realizar mediciones de altura de planos de trabajo, distancia visual a monitores y ángulos de apoyo lumbar.',
    'Informe ergonómico preliminar muy completo y con recomendaciones viables de bajo costo.',
    '2026-06-24'
);

-- Orientación 53: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '29366453',
    'Orientación en metodologías de entrenamiento deportivo adaptado e inclusivo.',
    'Diseño de rutinas de actividad física comunitaria para personas de la tercera edad y movilidad reducida.',
    'Priorizar ejercicios de bajo impacto articular, movilidad dinámica y socialización grupal.',
    'Enfoque altamente humanista e integrador; se autoriza inicio del taller comunitario.',
    '2026-07-09'
);

-- Orientación 54: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '30467564',
    'Asesoría en comercialización digital de productos turísticos regionales.',
    'Estrategias de promoción en redes sociales para rutas gastronómicas del municipio Iribarren.',
    'Diseñar calendario de publicaciones, fotografía gastronómica de calidad y vinculación con emprendedores locales.',
    'Plan de marketing turístico digital evaluado y avalado por el comité académico.',
    '2026-07-22'
);

-- Orientación 55: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '31568675',
    'Orientación en catalogación de recursos digitales y metadatos en bibliotecas virtuales.',
    'Implementación de esquemas de metadatos Dublin Core para repositorios institucionales de acceso abierto.',
    'Estudiar los 15 elementos básicos de Dublin Core y realizar pruebas piloto de catalogación con tesis de grado.',
    'Dominio técnico notable de la taxonomía documental y recuperación de información digital.',
    '2026-08-13'
);

-- Orientación 56: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '28669786',
    'Asesoría en educación ambiental y concientización sobre el cambio climático.',
    'Diseño de campañas de sensibilización ecológica dirigidas a estudiantes de secundaria del municipio.',
    'Emplear dinámicas lúdicas participativas, juegos de roles ambientales y talleres de reciclaje vivencial.',
    'Propuesta educativa comunitaria innovadora, lista para su ejecución en el nuevo año escolar.',
    '2026-09-03'
);

-- Orientación 57: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '27770897',
    'Orientación en procesamiento agroindustrial de frutas autóctonas del semiárido larense.',
    'Métodos de conservación artesanal de pulpas de frutas (semeco, tuna y guayaba) sin aditivos químicos.',
    'Aplicar pasteurización lenta, medición de grados Brix y sellado al vacío en envases de vidrio esterilizados.',
    'Protocolo técnico verificado en laboratorios de bromatología con éxito.',
    '2026-09-23'
);

-- Orientación 58: Jhan Guevara
CALL registrar_orientacion_seed(
    '13652666', '26871908',
    'Asesoría en logística de distribución de insumos médicos para centros de salud comunitaria.',
    'Simulación de rutas críticas de entrega de medicamentos en dispensarios parroquiales de Barquisimeto.',
    'Integrar el análisis geográfico de distancias con la frecuencia de reposición de stock esencial.',
    'Se logró optimizar el tiempo de respuesta logístico en la propuesta teórica del proyecto.',
    '2026-10-15'
);

-- Orientación 59: Edward Urdaneta
CALL registrar_orientacion_seed(
    '18364725', '25972019',
    'Orientación sobre control de calidad en piezas soldadas por arco eléctrico.',
    'Criterios de inspección visual de cordones de soldadura conforme al código AWS D1.1.',
    'Elaborar planilla de verificación de defectos superficiales (porosidad, socavación y falta de penetración).',
    'Estudiante capacitada en la evaluación de calidad estructural de uniones soldadas.',
    '2026-11-05'
);

-- Orientación 60: Nohelia Quintero
CALL registrar_orientacion_seed(
    '22159408', '29173120',
    'Evaluación integral de cierre y orientación vocacional para prosecución a Ingeniería Química.',
    'Sesión final de asesoría sobre culminación exitosa de trayecto de TSU y transición al ciclo de ingeniería.',
    'Consignar récord académico actualizado, revisar oferta de electivas técnicas de profundización y mantener excelencia académica.',
    'Cierre de caso formal por cumplimiento sobresaliente de metas pedagógicas. Fecha de atención: 17 de noviembre de 2026.',
    '2026-11-17'
);

-- =====================================================================
-- 3. LIMPIEZA DEL PROCEDIMIENTO TEMPORAL
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_orientacion_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total de registros en orientación:
--    SELECT COUNT(*) AS total_orientaciones FROM orientacion;
--
-- 2) Atenciones por orientador:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS orientador, COUNT(*) AS total
--      FROM orientacion o
--      JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     GROUP BY orientador;
--
-- 3) Distribución cronológica anual (2024, 2025, 2026):
--    SELECT YEAR(fecha_creacion) AS anio, COUNT(*) AS total
--      FROM orientacion
--     GROUP BY anio ORDER BY anio;
--
-- 4) Muestra de las últimas atenciones registradas:
--    SELECT o.id_orientacion, o.fecha_creacion, CONCAT(b.nombres, ' ', b.apellidos) AS beneficiario,
--           CONCAT(e.nombre, ' ', e.apellido) AS orientador, o.motivo_orientacion
--      FROM orientacion o
--      JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = o.id_solicitud_serv
--      JOIN beneficiario b ON b.id_beneficiario = ss.id_beneficiario
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     ORDER BY o.fecha_creacion DESC LIMIT 5;
-- =====================================================================
