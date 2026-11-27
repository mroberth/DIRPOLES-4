-- =====================================================================
-- SEED DE EXPEDIENTES DE DISCAPACIDAD (TANDA 2) — 60 registros sintéticos
-- Ubicación: docs/bd/seed_nuevas/seed_discapacidad2.sql
-- Módulo: Diversidad Funcional y Discapacidad (id_modulo 8, id_servicios = 5)
--
-- Cobertura Temporal: 25/01/2024 al 17/11/2026 (Ideal para microservicio IA)
-- Tablas involucradas: solicitud_de_servicio, discapacidad
--
-- Distribución temporal:
--   * Cohorte 2024: 20 expedientes (enero a noviembre 2024)
--   * Cohorte 2025: 20 expedientes (enero a noviembre 2025)
--   * Cohorte 2026: 20 expedientes (enero al 17 de noviembre 2026)
--
-- Especialistas (en dirpoles_security.empleado, id_tipo_empleado = 5):
--   * Yhon Alexander Guerra Marcano      (Cédula: 15928374) - 30 expedientes
--   * Marielva Yulieth Hernández Briceño(Cédula: 24671583) - 30 expedientes
--
-- Tipos de Discapacidad (según ENUM estricto):
--   * 'Física'     : 24 casos (~40% - movilidad, paresias, secuelas ortopédicas)
--   * 'Sensorial'  : 18 casos (~30% - hipoacusia, anacusia, baja visión, visión monocular)
--   * 'Intelectual':  9 casos (~15% - dificultades procesales, espectro autista de alto funcionamiento)
--   * 'Múltiple'   :  6 casos (~10% - diplejías espásticas con afectación neurosensorial)
--   * 'Otro'       :  3 casos  (~5% - acondroplasia / talla baja, neuropatías)
--
-- Grados y Asistencia:
--   * Grados: Leve, Moderado, Grave.
--   * Requiere asistencia: 'Si' (~23%), 'No' (~77%).
--   * Carnet CONAPDIS: ~77% con carnet oficial y ~23% en trámite (NULL).
--
-- 100% IDEMPOTENTE: omite registros si ya existen para el beneficiario y fecha.
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_spanish2_ci;

-- =====================================================================
-- 1. PROCEDIMIENTO TEMPORAL DE CARGA TRANSACCIONAL E IDEMPOTENTE
-- =====================================================================
DELIMITER ;;
DROP PROCEDURE IF EXISTS registrar_discapacidad_seed;;
CREATE PROCEDURE registrar_discapacidad_seed(
    IN p_cedula_esp VARCHAR(15),
    IN p_cedula_ben VARCHAR(15),
    IN p_tipo ENUM('Física','Sensorial','Intelectual','Múltiple','Otro'),
    IN p_especifica VARCHAR(200),
    IN p_diagnostico VARCHAR(255),
    IN p_grado ENUM('Leve','Moderado','Grave'),
    IN p_medicamentos VARCHAR(255),
    IN p_habilidades VARCHAR(255),
    IN p_asistencia VARCHAR(2),
    IN p_dispositivo VARCHAR(255),
    IN p_observaciones TEXT,
    IN p_recomendaciones TEXT,
    IN p_carnet VARCHAR(20),
    IN p_fecha_creacion DATE
)
BEGIN
    DECLARE v_id_esp INT;
    DECLARE v_id_ben INT;
    DECLARE v_id_sol INT;

    -- 1. Resolver especialista en discapacidad
    SELECT id_empleado INTO v_id_esp
    FROM dirpoles_security.empleado
    WHERE cedula = p_cedula_esp COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- 2. Resolver beneficiario
    SELECT id_beneficiario INTO v_id_ben
    FROM beneficiario
    WHERE cedula = p_cedula_ben COLLATE utf8mb4_spanish2_ci LIMIT 1;

    -- Idempotencia: Verificar que especialista y beneficiario existan, y que no exista registro previo en esa fecha
    IF v_id_esp IS NOT NULL AND v_id_ben IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM discapacidad d
            JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
            WHERE ss.id_beneficiario = v_id_ben
              AND d.fecha_creacion = p_fecha_creacion
        ) THEN
            -- A) solicitud_de_servicio (id_servicios = 5 para Discapacidad)
            INSERT INTO solicitud_de_servicio (id_servicios, id_beneficiario, id_empleado)
            VALUES (5, v_id_ben, v_id_esp);
            SET v_id_sol = LAST_INSERT_ID();

            -- B) discapacidad
            INSERT INTO discapacidad (
                id_solicitud_serv, tipo_discapacidad, disc_especifica, diagnostico,
                grado, medicamentos, habilidades_funcionales, requiere_asistencia,
                dispositivo_asistencia, observaciones, recomendaciones,
                carnet_discapacidad, fecha_creacion
            ) VALUES (
                v_id_sol, p_tipo, p_especifica, p_diagnostico,
                p_grado, p_medicamentos, p_habilidades, p_asistencia,
                p_dispositivo, p_observaciones, p_recomendaciones,
                p_carnet, p_fecha_creacion
            );
        END IF;
    END IF;
END;;
DELIMITER ;

-- =====================================================================
-- 2. INSERTAR 60 EXPEDIENTES DE DISCAPACIDAD SINTÉTICOS
-- =====================================================================

-- ---------------------------------------------------------------------
-- COHORTE 2024 (20 Expedientes: Enero a Noviembre 2024)
-- ---------------------------------------------------------------------

-- Caso 1: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '28114201', 'Física',
    'Hemiparesia izquierda residual de etiología congénita.',
    'Dificultad moderada para la marcha prolongada y bipedestación sostenida.',
    'Moderado', 'Sin medicación fija indicada actualmente.',
    'Autonomía en desplazamiento a ritmo pausado, adecuada movilidad en miembro superior derecho.',
    'No', 'Bastón ortopédico monopodal para trayectos largos.',
    'Estudiante cursa trayecto académico regular en Administración con apoyo de ubicación en planta baja.',
    'Garantizar asignación de aulas en planta baja o con rampa de acceso habilitada.',
    'CONAPDIS-194820', '2024-01-25'
);

-- Caso 2: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29215302', 'Sensorial',
    'Hipoacusia neurosensorial bilateral moderada.',
    'Disminución de agudeza auditiva bilateral con comprensión mediante lectura labial.',
    'Moderado', 'Sin medicación requerida.',
    'Excelente lectura labiofacial, lectoescritura fluida y manejo de lengua de señas venezolana.',
    'Si', 'Auxiliares auditivos retroauriculares bilaterales.',
    'Requiere ubicación en los primeros pupitres de la sección asignada en Contaduría.',
    'Facilitar material educativo digitalizado e impreso de apoyo y ubicación frontal al docente.',
    'CONAPDIS-839201', '2024-02-08'
);

-- Caso 3: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '30316403', 'Sensorial',
    'Baja visión por miopía magna y astigmatismo no corregible totalmente.',
    'Restricción significativa del campo visual y baja agudeza visual focal.',
    'Grave', 'Lágrimas artificiales en gotas c/8h.',
    'Manejo autónomo de lectores de pantalla sintéticos y macrocristales en entorno digital.',
    'No', 'Lentes de alta graduación y software lector de pantalla NVDA.',
    'Requiere evaluaciones impresas con contraste de fuente ampliada (18pt+) en Informática.',
    'Proporcionar material evaluativo en formato digital accesible y permitir laptop personal.',
    'CONAPDIS-492018', '2024-02-22'
);

-- Caso 4: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '31417504', 'Intelectual',
    'Compromiso cognitivo leve en procesamiento secuencial abstracto.',
    'Dificultad leve en la abstracción de conceptos matemáticos complejos.',
    'Leve', 'No indica medicación.',
    'Adecuada interacción social, cumplimiento de tareas con guía estructurada y constante.',
    'No', 'Guías visuales estructuradas y esquemas de trabajo por pasos.',
    'Responde de forma positiva a la adaptación del ritmo evaluativo en Higiene Laboral.',
    'Desglosar las instrucciones escritas en pasos secuenciales breves y dar tiempo adicional.',
    NULL, '2024-03-07'
);

-- Caso 5: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '27518605', 'Múltiple',
    'Parálisis cerebral infantil de tipo diplejía espástica con disartria leve.',
    'Compromiso motor severo en miembros inferiores y dificultad leve de articulación verbal.',
    'Grave', 'Miorrelajantes según prescripción neuropediátrica periódica.',
    'Desplazamiento autónomo en silla de ruedas, excelente comprensión y sociabilidad.',
    'Si', 'Silla de ruedas manual ergonómica.',
    'Requiere adecuación de mesas y acceso preferencial en canchas deportivas y aulas.',
    'Asegurar rampa de acceso despejada y mesas de altura adaptada en talleres.',
    'CONAPDIS-721094', '2024-03-21'
);

-- Caso 6: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '26619706', 'Física',
    'Amputación traumática distal de antebrazo izquierdo.',
    'Ausencia funcional del segmento distal del miembro superior izquierdo.',
    'Moderado', 'No requiere medicamentos.',
    'Desplazamiento normal, compensación motora eficaz y autónoma con miembro superior derecho.',
    'No', 'Prótesis mecánica funcional de antebrazo.',
    'Estudiante plenamente adaptado al uso de teclado con una sola mano en Turismo.',
    'Permitir tiempo adicional en evaluaciones escritas extensas o de tipeo.',
    'CONAPDIS-305912', '2024-04-11'
);

-- Caso 7: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '25720807', 'Sensorial',
    'Hipoacusia conductiva unilateral derecha moderada por secuela ótica.',
    'Disminución discreta de la audición en oído derecho con oído izquierdo sano.',
    'Leve', 'No requiere medicación.',
    'Comunicación verbal normal en ambientes con bajo ruido de fondo.',
    'No', 'Filtro auditivo protector para ambientes de alta resonancia.',
    'Monitoreo preventivo semestral en áreas ruidosas de la universidad.',
    'Ubicar al estudiante en el costado izquierdo del aula para favorecer la audición.',
    NULL, '2024-04-25'
);

-- Caso 8: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '24821908', 'Otro',
    'Condición neuropática periférica motora de miembros inferiores.',
    'Disminución de sensibilidad profunda en miembros inferiores y fatigabilidad precoz.',
    'Moderado', 'Complejo B y analgésicos neuropáticos bajo prescripción.',
    'Marcha lenta independiente, autonomía plena en actividades teóricas de aula.',
    'No', 'Plantillas ortopédicas de descarga y calzado adaptado.',
    'Revisión semestral de la condición de movilidad en Calidad y Ambiente.',
    'Se aconsejan pausas breves de 5 minutos en clases prácticas de pie.',
    'CONAPDIS-612048', '2024-05-09'
);

-- Caso 9: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '23922019', 'Física',
    'Secuela motora de poliomielitis en miembro inferior derecho.',
    'Marcha claudicante con hipotrofia muscular y debilidad distal en pierna derecha.',
    'Moderado', 'No indica medicamentos.',
    'Independencia funcional con apoyo de órtesis pasiva antiequino.',
    'No', 'Órtesis corta de tobillo y pie tipo AFO.',
    'Se desplaza con autonomía por el recinto universitario en Agroalimentación.',
    'Evitar traslados entre edificios distantes durante los recesos cortos de clase.',
    'CONAPDIS-519203', '2024-05-23'
);

-- Caso 10: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29023120', 'Sensorial',
    'Retinosis pigmentaria / Restos visuales conservados en visión tubular.',
    'Disminución marcada de la visión periférica bilateral con conservación de campo central.',
    'Grave', 'Gotas hipotensoras oculares c/12h.',
    'Desplazamiento con bastón guía, orientación espacial adecuada en entornos conocidos.',
    'Si', 'Bastón blanco rígido de orientación.',
    'Acompañamiento en áreas de alta concurrencia, pasillos centrales y escaleras.',
    'Mantener pasillos libres de obstáculos físicos y señalización podotáctil.',
    'CONAPDIS-940215', '2024-06-13'
);

-- Caso 11: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '30124231', 'Física',
    'Malformación congénita de mano derecha (Oligodactilia).',
    'Limitación fina de prensión en mano derecha sin afectación proximal del brazo.',
    'Leve', 'No requiere medicamentos.',
    'Plena capacidad de lectoescritura con mano izquierda dominante compensada.',
    'No', 'Adaptador ergonómico para bolígrafo y útiles de dibujo.',
    'Estudiante con alto rendimiento en Materiales Industriales.',
    'Permitir uso de computadoras portátiles para toma de apuntes en talleres.',
    NULL, '2024-06-27'
);

-- Caso 12: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '31225342', 'Intelectual',
    'Dificultad de aprendizaje procedimental leve en lógica algorítmica.',
    'Retraso leve en la consolidación de secuencias abstractas complejas.',
    'Leve', 'No indica fármacos.',
    'Expresión verbal fluida, sociabilidad excelente y buena comprensión lectora.',
    'No', 'Calculadora programable y esquemas visuales de apoyo.',
    'Acompañamiento periódico de tutoría técnica en Procesos Químicos.',
    'Flexibilización en el tiempo de entrega de evaluaciones escritas cuantitativas.',
    'CONAPDIS-204918', '2024-07-11'
);

-- Caso 13: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '28326453', 'Sensorial',
    'Anacusia unilateral total derecha por parotiditis infantil.',
    'Pérdida completa de la audición en oído derecho con oído izquierdo funcional.',
    'Leve', 'No requiere medicamentos.',
    'Comprensión auditiva conservada mediante oído izquierdo sano.',
    'No', 'Sin dispositivo específico.',
    'Ubicación estratégica en el costado izquierdo de los laboratorios de informática.',
    'Garantizar que el docente se exprese desde el campo auditivo izquierdo del alumno.',
    NULL, '2024-07-25'
);

-- Caso 14: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '27427564', 'Múltiple',
    'Síndrome motor-sensorial post-traumatismo vertebromedular dorsal bajo.',
    'Monoparesia de miembro inferior izquierdo e hipoacusia neurosensorial leve.',
    'Grave', 'Analgésicos neuropáticos según necesidad.',
    'Movilidad con apoyo postural, uso de lengua de señas básica y lectoescritura fluida.',
    'Si', 'Silla de ruedas motorizada plegable.',
    'Se tramita carnet de estacionamiento preferencial en DIRPOLES.',
    'Ubicación obligatoria en planta baja y asistencia en laboratorios de cómputo.',
    'CONAPDIS-730192', '2024-08-08'
);

-- Caso 15: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '26528675', 'Física',
    'Artritis reumatoide juvenil en fase inactiva / remisa.',
    'Rigidez articular matutina de carácter leve en articulaciones interfalángicas distales.',
    'Leve', 'Antiinflamatorios de rescate ocasionales en crisis.',
    'Movilidad manual conservada sin deformidad articular evidente.',
    'No', 'Férulas nocturnas de reposo postural.',
    'Monitoreo periódico en épocas de bajas temperaturas y lluvia.',
    'Evitar mantener las manos en posturas estáticas prolongadas en digitación contable.',
    'CONAPDIS-118293', '2024-08-22'
);

-- Caso 16: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '25629786', 'Sensorial',
    'Estrabismo divergente con ambliopía binocular moderada.',
    'Dificultad en la percepción de profundidad y estereopsis visual reducida.',
    'Moderado', 'Tratamiento ortóptico periódico.',
    'Lectura a distancia corta adecuada, desplazamiento peatonal plenamente autónomo.',
    'No', 'Lentes con prismas correctores especiales.',
    'Se sugieren descansos visuales de 10 minutos cada hora de trabajo continuo.',
    'Ubicación en filas centrales del aula a distancia media del pizarrón.',
    NULL, '2024-09-12'
);

-- Caso 17: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '24730897', 'Física',
    'Espina bífida oculta con debilidad muscular distal en tobillos.',
    'Disminución leve del tono muscular y reflejos osteotendinosos en pies.',
    'Moderado', 'Suplementos vitamínicos del complejo B.',
    'Marcha independiente, lentitud moderada al subir gradas empinadas.',
    'No', 'Plantillas de soporte de arco plantar y calzado ortopédico.',
    'Uso de elevador en sedes de múltiples niveles para traslados intermedios.',
    'Asignar aulas accesibles mediante rampa o ascensor funcional.',
    'CONAPDIS-883019', '2024-09-26'
);

-- Caso 18: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29831908', 'Otro',
    'Acondroplasia / Displasia ósea con talla baja armónica.',
    'Restricción del crecimiento óseo longitudinal sin afectación de facultades cognitivas.',
    'Moderado', 'No indica medicamentos.',
    'Independencia plena, destreza motora fina excelente y agilidad en aula.',
    'No', 'Escalones portátiles y adaptaciones posturales de asiento.',
    'Se requiere adaptación de mobiliario en pupitres y mesones de Turismo.',
    'Facilitar apoya-pies y pupitre adaptado a su estatura antropométrica.',
    'CONAPDIS-449102', '2024-10-17'
);

-- Caso 19: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '30932019', 'Sensorial',
    'Hipoacusia neurosensorial profunda bilateral congénita.',
    'Ausencia funcional de la audición bilateral con habla signada como lengua materna.',
    'Grave', 'No indica fármacos.',
    'Excelente dominio de Lengua de Señas Venezolana (LSV) y lectura labiofacial.',
    'Si', 'Implante coclear unilateral en oído izquierdo.',
    'Cuenta con acompañamiento de intérprete en asignaturas teóricas complejas.',
    'Permitir el ingreso de intérprete de lengua de señas al aula de clases.',
    'CONAPDIS-992014', '2024-11-07'
);

-- Caso 20: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '31033120', 'Física',
    'Secuela postural de Escoliosis idiopática dorso-lumbar severa.',
    'Desviación lateral de columna vertebral que genera fatiga prematura al estar sentado.',
    'Moderado', 'Analgésicos de uso eventual bajo prescripción.',
    'Marcha y postura en bipedestación normales con limitación en sedestación prolongada.',
    'No', 'Corsé ortopédico de soporte dorsolumbar.',
    'Requiere silla ergonómica con soporte lumbar adecuado en aulas.',
    'Permitir que el estudiante se ponga de pie breves minutos durante clases de 2 horas.',
    NULL, '2024-11-28'
);

-- ---------------------------------------------------------------------
-- COHORTE 2025 (20 Expedientes: Enero a Noviembre 2025)
-- ---------------------------------------------------------------------

-- Caso 21: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '28134231', 'Intelectual',
    'Trastorno del espectro autista de alto funcionamiento (Nivel 1).',
    'Dificultad leve en la flexibilidad cognitiva y la interacción social grupal no estructurada.',
    'Leve', 'Sin medicación psicotrópica.',
    'Alta capacidad de concentración analítica en tareas individuales estructuradas.',
    'No', 'Audífonos de cancelación de ruido para ambientes altamente saturados.',
    'Estudiante muy metódica con alto rendimiento académico en Agroalimentación.',
    'Mantener ambiente predecible y notificar cronogramas evaluativos con anticipación.',
    'CONAPDIS-339102', '2025-01-16'
);

-- Caso 22: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '27235342', 'Física',
    'Distrofia muscular de cinturas en estadio funcional inicial.',
    'Debilidad moderada en musculatura proximal de miembros inferiores al subir gradas.',
    'Grave', 'Tratamiento fisioterapéutico continuo y suplementación.',
    'Desplazamiento autónomo a corta distancia con apoyo de andadera de aluminio.',
    'Si', 'Andadera de aluminio con ruedas frontales.',
    'Se gestiona apoyo de transporte institucional preferencial de DIRPOLES.',
    'Asignar aulas en planta baja cerca de los accesos principales de la institución.',
    'CONAPDIS-664019', '2025-01-30'
);

-- Caso 23: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '26336453', 'Sensorial',
    'Ceguera monocular total en ojo derecho por traumatismo contuso antiguo.',
    'Pérdida de la visión en ojo derecho con conservación íntegra del ojo izquierdo.',
    'Moderado', 'Gotas protectoras e hidratantes en ojo izquierdo.',
    'Visión monocular conservada, orientación espacial y desplazamiento normales.',
    'No', 'Lentes de policarbonato con protección antirreflejo y UV.',
    'Ubicación en el sector derecho del aula para ampliar campo visual izquierdo.',
    'Proteger el ojo funcional durante actividades prácticas en talleres de Materiales.',
    NULL, '2025-02-13'
);

-- Caso 24: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '25437564', 'Física',
    'Monoparesia espástica residual de miembro superior derecho.',
    'Disminución moderada de la fuerza de agarre y motricidad fina en mano derecha.',
    'Leve', 'Ejercicios de rehabilitación y terapia ocupacional continua.',
    'Independencia funcional general con compensación bimanual.',
    'No', 'Pelota ortopédica de fortalecimiento muscular.',
    'Estudiante con buena capacidad adaptativa en Procesos Químicos.',
    'Permitir tiempo adicional en pruebas escritas prolongadas o exámenes prácticos.',
    'CONAPDIS-551029', '2025-02-27'
);

-- Caso 25: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '29538675', 'Física',
    'Paraparesia espástica post-traumática leve con apoyo ortésico.',
    'Marcha con apoyo de muletas canadienses y ritmo pausado.',
    'Moderado', 'Relajantes musculares de rescate nocturno.',
    'Autonomía en traslados cortos, excelente dominio de herramientas informáticas.',
    'No', 'Par de muletas canadienses de aluminio regulables.',
    'Asignación en laboratorios de planta baja en Informática.',
    'Garantizar espacio suficiente entre filas de computadoras para el paso de muletas.',
    'CONAPDIS-772819', '2025-03-13'
);

-- Caso 26: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '30639786', 'Sensorial',
    'Hipoacusia bilateral moderada progresiva.',
    'Pérdida auditiva de frecuencias medias y altas bilateral.',
    'Moderado', 'Sin tratamiento farmacológico.',
    'Comprensión verbal optimizada con prótesis auditiva y apoyo labiofacial.',
    'Si', 'Audífonos retroauriculares digitales programados.',
    'Buen desempeño académico en Administración.',
    'Ubicación en primera fila frente al docente y evitar ruido de fondo excesivo.',
    'CONAPDIS-184920', '2025-03-27'
);

-- Caso 27: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '31740897', 'Sensorial',
    'Glaucoma congénito operado con reducción de campo visual bilateral.',
    'Visión central conservada con escotomas periféricos moderados.',
    'Moderado', 'Gotas hipotensoras oculares cada 12 horas estrictas.',
    'Lectura fluida con adecuada iluminación, marcha autónoma prudente.',
    'No', 'Lentes con filtro ámbar de alto contraste.',
    'Requiere pupitre cercano a ventanas con luz natural indirecta.',
    'Permitir exámenes impresos con tipografía Arial tamaño 16pt.',
    'CONAPDIS-829103', '2025-04-10'
);

-- Caso 28: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '28841908', 'Intelectual',
    'Trastorno por déficit de atención e hiperactividad (TDAH) tipo inatento con lentitud procesal.',
    'Dificultad marcada para mantener la atención sostenida en textos extensos.',
    'Leve', 'Tratamiento neuroconductual supervisado.',
    'Creatividad sobresaliente, destreza práctica y razonamiento lógico espacial.',
    'No', 'Temporizadores de estudio y mapas conceptuales esquemáticos.',
    'Acompañamiento psicopedagógico trimestral en Higiene Laboral.',
    'Dosificar evaluaciones extensas en bloques con breves pausas intermedias.',
    NULL, '2025-04-24'
);

-- Caso 29: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '27942019', 'Física',
    'Agenesia congénita de dedos 2 y 3 de mano izquierda.',
    'Ausencia anatómica parcial de falanges con pulgar funcional preservado.',
    'Leve', 'No amerita medicamentos.',
    'Movilidad motora fina adaptada en agarre palmar y desempeño deportivo.',
    'No', 'Dispositivo elástico de sujeción para implementos.',
    'Destacada participación en actividades de acondicionamiento físico.',
    'Adaptación en la evaluación de técnicas de agarre bimanual en cancha.',
    'CONAPDIS-619284', '2025-05-15'
);

-- Caso 30: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '26043120', 'Múltiple',
    'Secuela de traumatismo craneoencefálico con ataxia motora y disacusia leve.',
    'Incoordinación motora al caminar y disminución leve de la captación sonora.',
    'Grave', 'Estabilizadores neurológicos pautados por especialista.',
    'Marcha con bastón trípode, lectoescritura lenta pero legible.',
    'Si', 'Bastón trípode de base ancha.',
    'Requiere tiempo adicional en pasillos durante cambios de hora.',
    'Ubicar aulas cerca de rampas y baños accesibles institucionales.',
    'CONAPDIS-903812', '2025-05-29'
);

-- Caso 31: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '25144231', 'Física',
    'Genu valgo bilateral severo con artrosis femororrotuliana precoz.',
    'Dolor moderado a la marcha prolongada y dificultad para subir escalones altos.',
    'Moderado', 'Condroprotectores y analgésicos en crisis.',
    'Marcha independiente a paso lento en superficies planas.',
    'No', 'Rodilleras mecánicas con barras laterales de refuerzo.',
    'Buen aprovechamiento académico en Ciencias de la Información.',
    'Evitar que la estudiante deba subir escaleras entre módulos distantes.',
    'CONAPDIS-348192', '2025-06-12'
);

-- Caso 32: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29245342', 'Sensorial',
    'Sordera súbita unilateral izquierda no recuperada.',
    'Anacusia neurosensorial izquierda con audición normal en oído derecho.',
    'Leve', 'No requiere medicamentos.',
    'Orientación sonora y habla normales con leve dificultad en ambientes ruidosos.',
    'No', 'Sin dispositivo protésico indicado.',
    'Estudiante atento y disciplinado en Calidad y Ambiente.',
    'Ubicar en el pupitre del extremo derecho de la primera fila.',
    NULL, '2025-06-26'
);

-- Caso 33: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '30346453', 'Física',
    'Pie equinovaro congénito corregido quirúrgicamente con secuela funcional.',
    'Rigidez articular de tobillo y apoyo plantar externo claudicante.',
    'Moderado', 'No indica fármacos.',
    'Desplazamiento autónomo con calzado ortopédico de cuña externa.',
    'No', 'Calzado ortopédico con alza compensatoria.',
    'Cumple con normalidad sus prácticas de campo en Agroalimentación.',
    'Permitir traslados a paso moderado en recorridos por huertos y canteros.',
    'CONAPDIS-528190', '2025-07-10'
);

-- Caso 34: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '31447564', 'Sensorial',
    'Nistagmo congénito idiopático con disminución de agudeza visual binocular.',
    'Movimiento oscilatorio involuntario ocular con dificultad en fijación visual.',
    'Moderado', 'Tratamiento visual ortóptico.',
    'Lectura fluida con macrofuente o acercamiento del texto a corta distancia.',
    'No', 'Lentes correctores especiales con prismas de fijación.',
    'Estudiante perseverante con buena capacidad analítica en Distribución.',
    'Permitir evaluaciones con tipografía ampliada y descansos oculares breves.',
    'CONAPDIS-649102', '2025-07-24'
);

-- Caso 35: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '28548675', 'Física',
    'Contractura de Dupuytren en dedos 4 y 5 de mano derecha.',
    'Flexión fija irreversible en articulaciones interfalángicas de dedos anular y meñique.',
    'Leve', 'Terapia física ocupacional y ejercicios de estiramiento.',
    'Prensión palmar con dedos índice y pulgar conservada con destreza.',
    'No', 'Férula de extensión digital para uso nocturno.',
    'Desempeño técnico óptimo en Materiales Industriales.',
    'Facilitar herramientas de laboratorio con mangos anatómicos acolchados.',
    NULL, '2025-08-14'
);

-- Caso 36: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '27649786', 'Intelectual',
    'Dificultad del aprendizaje no verbal (TANV) de grado leve.',
    'Compromiso en la orientación espacial y lectura de gráficos complejos.',
    'Leve', 'No toma medicamentos.',
    'Excelente expresión verbal y memoria conceptual auditiva sobresaliente.',
    'No', 'Esquemas descriptivos textuales paso a paso.',
    'Muy buen rendimiento en asignaturas teóricas de Procesos Químicos.',
    'Complementar gráficos técnicos con explicaciones descriptivas en texto.',
    'CONAPDIS-193820', '2025-09-04'
);

-- Caso 37: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '26750897', 'Sensorial',
    'Hipoacusia mixta bilateral de grado moderado.',
    'Afectación conductiva y neurosensorial en ambos oídos.',
    'Moderado', 'Limpieza ótica periódica y mantenimiento de auxiliares.',
    'Uso competente de audífonos digitales y lectura labial de refuerzo.',
    'Si', 'Auxiliares auditivos intrauriculares bilaterales.',
    'Estudiante destacada en desarrollo de proyectos de software en Informática.',
    'Favorecer la comunicación directa mirando de frente al estudiante.',
    'CONAPDIS-718293', '2025-09-25'
);

-- Caso 38: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '25851908', 'Física',
    'Secuela de fractura compleja de fémur izquierdo con dismetría de 2.5 cm.',
    'Marcha con claudicación leve por acortamiento de miembro inferior izquierdo.',
    'Moderado', 'No requiere medicamentos continuos.',
    'Movilidad autónoma en terreno plano con alza compensatoria en calzado.',
    'No', 'Alza ortopédica de 2.5 cm en suela de calzado izquierdo.',
    'Excelente desenvolvimiento en el área de Administración.',
    'Ubicación en aulas con acceso sin desniveles pronunciados.',
    'CONAPDIS-402918', '2025-10-16'
);

-- Caso 39: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '29952019', 'Sensorial',
    'Queratocono bilateral moderado no corregible con anteojos convencionales.',
    'Astigmatismo irregular severo y distorsión de imágenes en visión lejana.',
    'Moderado', 'Lentes de contacto esclerales rígidos permeables al gas.',
    'Lectura a distancia media con apoyo de lentes esclerales y buena iluminación.',
    'No', 'Lentes de contacto esclerales terapéuticos y lubricantes oculares.',
    'Buen desempeño académico en Contaduría Pública.',
    'Procurar asientos con iluminación adecuada que eviten reflejos en el pizarrón.',
    'CONAPDIS-819203', '2025-11-06'
);

-- Caso 40: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '30053120', 'Física',
    'Luxación recidivante de hombro derecho con pérdida de abducción completa.',
    'Inestabilidad articular glenohumeral que impide levantar pesos sobre el hombro.',
    'Leve', 'Analgésicos de uso ocasional y fisioterapia preventiva.',
    'Uso funcional de mano y antebrazo intacto para escribir y operar equipos.',
    'No', 'Cabestrillo de inmovilización profiláctica en traslados largos.',
    'Activo en actividades académicas de Higiene y Seguridad Laboral.',
    'Exoneración de pruebas físicas que impliquen levantamiento de cargas sobre la cabeza.',
    NULL, '2025-11-27'
);

-- ---------------------------------------------------------------------
-- COHORTE 2026 (20 Expedientes: Enero al 17 de Noviembre 2026)
-- ---------------------------------------------------------------------

-- Caso 41: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '31154231', 'Física',
    'Amputación traumática de primer dedo (pulgar) de pie izquierdo.',
    'Alteración leve en la propulsión durante la fase final de la marcha.',
    'Leve', 'No toma medicamentos.',
    'Marcha equilibrada con plantilla de relleno ortopédico anterior.',
    'No', 'Plantilla ortopédica con relleno anterior de silicona.',
    'Estudiante matriculada en PNF Deporte con adecuación de modalidades de impacto.',
    'Adecuar pruebas atléticas a modalidades de bajo impacto en despegue podálico.',
    'CONAPDIS-918273', '2026-01-15'
);

-- Caso 42: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '28255342', 'Sensorial',
    'Coloboma macular congénito en ojo izquierdo con baja agudeza visual central.',
    'Pérdida de visión central en ojo izquierdo con visión periférica preservada.',
    'Moderado', 'Controles oftalmológicos semestrales.',
    'Excelente visión compensatoria en ojo derecho funcional.',
    'No', 'Lentes protectores con filtro de luz azul.',
    'Muy buen rendimiento en PNF Turismo.',
    'Ubicar al estudiante en el lado izquierdo del aula para privilegiar el ojo derecho.',
    'CONAPDIS-529182', '2026-01-29'
);

-- Caso 43: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '27356453', 'Física',
    'Monoparesia braquial obstétrica izquierda.',
    'Limitación para la elevación completa y rotación externa del brazo izquierdo.',
    'Moderado', 'Fisioterapia articular de mantenimiento.',
    'Habilidades manuales plenas con mano derecha dominante para archivo y digitalización.',
    'No', 'Férula posicional de reposo.',
    'Buen desempeño académico en Ciencias de la Información.',
    'Facilitar espacio amplio en el pupitre para apoyo ergonómico del brazo.',
    'CONAPDIS-384910', '2026-02-12'
);

-- Caso 44: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '26457564', 'Intelectual',
    'Trastorno del aprendizaje en cálculo y operaciones complejas (Discalculia).',
    'Dificultad marcada para procesar operaciones aritméticas mentales abstractas.',
    'Leve', 'No amerita medicamentos.',
    'Excelente comprensión verbal, retención de conceptos teóricos y redacción.',
    'No', 'Calculadora de bolsillo autorizada en todas las evaluaciones.',
    'Estudiante comprometido en Sistemas de Calidad y Ambiente.',
    'Autorizar uso permanente de calculadora y formularios matemáticos en pruebas.',
    NULL, '2026-02-26'
);

-- Caso 45: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '25558675', 'Física',
    'Secuela motora de Guillain-Barré con debilidad distal residual en manos.',
    'Fátiga muscular fina al escribir a mano de manera continuada.',
    'Moderado', 'Complejo multivitamínico neurotrópico.',
    'Desplazamiento normal, adecuada motricidad gruesa y uso de computadoras.',
    'No', 'Engrosadores ergonómicos para bolígrafos y lápices.',
    'Estudiante destacada en proyectos de Agroalimentación.',
    'Permitir presentación de informes en formato digital tipeado en lugar de manuscrito.',
    'CONAPDIS-847291', '2026-03-12'
);

-- Caso 46: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29659786', 'Sensorial',
    'Hipoacusia neurosensorial unilateral moderada derecha.',
    'Pérdida auditiva en oído derecho secundaria a traumatismo sonoro agudo.',
    'Leve', 'No toma medicación.',
    'Comprensión verbal nítida en aula mediante oído contralateral sano.',
    'No', 'Sin prótesis auditiva requerida por el momento.',
    'Activo en Distribución y Logística.',
    'Ubicación en el flanco izquierdo del salón para optimizar captación del sonido.',
    'CONAPDIS-194827', '2026-03-26'
);

-- Caso 47: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '30760897', 'Física',
    'Espondilitis anquilosante en fase temprana con rigidez axial lumbosacra.',
    'Disminución leve de la movilidad de flexión lumbar con dolor matutino.',
    'Moderado', 'Antiinflamatorios de acción prolongada prescritos.',
    'Marcha conservada, autonomía en todas las actividades de laboratorio.',
    'No', 'Cinturón elástico lumbosacro para jornadas extensas.',
    'Estudiante destacada en Materiales Industriales.',
    'Permitir cambios posturales breves durante clases magistrales prolongadas.',
    'CONAPDIS-639182', '2026-04-09'
);

-- Caso 48: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '31861908', 'Sensorial',
    'Miopía magna con degeneración coriorretiniana miópica.',
    'Agudeza visual de 20/200 en ambos ojos no susceptible de corrección total.',
    'Grave', 'Vitaminas antioxidantes oculares en cápsulas.',
    'Uso de ampliador de pantalla digital y tipografía grande en computadoras.',
    'No', 'Lentes de alta graduación con filtro antirreflejante y lupa digital.',
    'Estudiante muy dedicado en Procesos Químicos.',
    'Facilitar material didáctico digitalizado con antelación para lectura en pantalla.',
    'CONAPDIS-728193', '2026-04-23'
);

-- Caso 49: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '28962019', 'Múltiple',
    'Tetraparesia espástica leve con disartria motora compensada.',
    'Movilidad coordinada a paso lento con dificultad de articulación en fonemas complejos.',
    'Grave', 'Medicación antiespástica oral prescrita.',
    'Desplazamiento autónomo sin andadera, excelente manejo de teclado informático.',
    'Si', 'Pulsador ergonómico y software de accesibilidad por voz.',
    'Estudiante sobresaliente en arquitectura de software en Informática.',
    'Adaptación de evaluaciones orales permitiendo respuestas escritas o asistidas por PC.',
    'CONAPDIS-991824', '2026-05-14'
);

-- Caso 50: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '27063120', 'Física',
    'Secuela de luxación congénita de cadera bilateral con artrosis secundaria.',
    'Marcha de pato (Trendelenburg bilateral) con dolor tras caminatas largas.',
    'Moderado', 'Antiinflamatorios no esteroideos según dolor.',
    'Independencia en traslados cortos y bipedestación de hasta 20 minutos.',
    'No', 'Bastón de apoyo ergonómico para exteriores.',
    'Buen desempeño en el área de Administración.',
    'Garantizar aulas con acceso directo y evitar trayectos peatonales largos en campus.',
    'CONAPDIS-482910', '2026-05-28'
);

-- Caso 51: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '26164231', 'Intelectual',
    'Trastorno del espectro autista Grado 1 (Síndrome de Asperger).',
    'Rigidez en rutinas académicas y literalidad en la comprensión de consignas.',
    'Leve', 'No toma fármacos.',
    'Capacidad sobresaliente de análisis numérico, memoria prodigiosa y disciplina.',
    'No', 'Fichas organizadoras con pautas explícitas de evaluación.',
    'Rendimiento académico excelente en Contaduría Pública.',
    'Enunciar las preguntas evaluativas con redacción directa, unívoca y sin ambigüedades.',
    'CONAPDIS-819274', '2026-06-11'
);

-- Caso 52: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '25265342', 'Física',
    'Secuela de fractura bimaleolar de tobillo derecho con limitación de dorsiflexión.',
    'Claudicación al caminar distancias mayores a 300 metros.',
    'Leve', 'No requiere medicamentos continuos.',
    'Plena autonomía para todas las actividades teóricas de aula.',
    'No', 'Tobillera ortopédica elástica con refuerzos maleolares.',
    'Cumplimiento regular de materias en Higiene Laboral.',
    'Permitir pausas durante inspecciones de campo prolongadas.',
    NULL, '2026-06-25'
);

-- Caso 53: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '29366453', 'Sensorial',
    'Hipoacusia neurosensorial bilateral moderada adquirida.',
    'Dificultad de inteligibilidad de la palabra en ambientes con eco.',
    'Moderado', 'Sin fármacos requeridos.',
    'Uso de prótesis auditiva retroauricular derecha con lectura labiofacial.',
    'Si', 'Audífono digital retroauricular derecho.',
    'Estudiante motivada en Deporte adaptado.',
    'Ubicación en primera fila y dar indicaciones de espaldas a fuentes de ruido.',
    'CONAPDIS-518290', '2026-07-09'
);

-- Caso 54: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '30467564', 'Otro',
    'Cardiopatía congénita corregida con limitación funcional de esfuerzo físico.',
    'Fatiga precoz y disnea de medianos esfuerzos por restricción de capacidad aeróbica.',
    'Moderado', 'Antiarrítmico y betabloqueante bajo control cardiológico.',
    'Autonomía en actividades intelectuales, marcha pausada sin carga de peso.',
    'No', 'Oxímetro de pulso para autocontrol en jornadas extensas.',
    'Excelente desempeño en PNF Turismo.',
    'Exoneración de caminatas empinadas o excursiones de senderismo de alta exigencia.',
    'CONAPDIS-381920', '2026-07-22'
);

-- Caso 55: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '31568675', 'Sensorial',
    'Amaurosis en ojo izquierdo por retinoblastoma en la infancia operado.',
    'Prótesis ocular en cavidad anoftálmica izquierda con ojo derecho sano.',
    'Moderado', 'Gotas lubricantes oculares profilácticas.',
    'Visión monocular derecha excelente con adaptación espacial completa.',
    'No', 'Prótesis cosmética ocular izquierda y lentes protectores de policarbonato.',
    'Estudiante destacada en Ciencias de la Información.',
    'Ubicación en el sector derecho del aula para maximizar campo visual activo.',
    'CONAPDIS-948192', '2026-08-13'
);

-- Caso 56: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '28669786', 'Física',
    'Secuela de quemadura profunda con bridas retráctiles en axila y codo izquierdo.',
    'Limitación para la extensión completa del codo izquierdo y abducción del hombro.',
    'Moderado', 'Cremas hidratantes y terapia de estiramiento.',
    'Mano izquierda funcional preservada para apoyo en digitación y escritura.',
    'No', 'Manga de compresión elástica para control cicatricial.',
    'Estudiante activo en Sistemas de Calidad y Ambiente.',
    'Facilitar pupitre espacioso que permita apoyo cómodo del brazo izquierdo.',
    'CONAPDIS-274819', '2026-09-03'
);

-- Caso 57: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '27770897', 'Física',
    'Monoparesia de miembro inferior derecho secundaria a lesión de nervio ciático poplíteo externo.',
    'Pie caído (pie péndulo) que ocasiona marcha en estepaje.',
    'Moderado', 'Complejo neurotrópico vitamínico.',
    'Marcha segura con férula tipo Rancho Los Amigos.',
    'No', 'Férula antiequino dinámica de polipropileno.',
    'Buen desempeño académico en Agroalimentación.',
    'Asignación en laboratorios y aulas de planta baja.',
    'CONAPDIS-619482', '2026-09-24'
);

-- Caso 58: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '26871908', 'Intelectual',
    'Trastorno del aprendizaje procedimental y disgrafía motora leve.',
    'Escritura manuscrita lenta con fatiga rápida al redactar a mano.',
    'Leve', 'No toma medicamentos.',
    'Excelente comprensión auditiva, dicción clara y fluidez verbal notable.',
    'No', 'Computadora portátil personal con teclado ergonómico.',
    'Estudiante con buena capacidad organizativa en Distribución y Logística.',
    'Permitir entrega de exámenes e informes en formato digitalizado.',
    NULL, '2026-10-15'
);

-- Caso 59: Yhon Guerra
CALL registrar_discapacidad_seed(
    '15928374', '25972019', 'Sensorial',
    'Acúfenos severos continuos e hipoacusia neurosensorial bilateral leve.',
    'Percepción continua de zumbido de alta frecuencia que genera fatiga auditiva.',
    'Leve', 'Neuroprotectores auditivos pautados.',
    'Audición funcional conservada en aulas silenciosas sin eco.',
    'No', 'Generador portátil de ruido blanco para estudio.',
    'Estudiante con alto sentido de responsabilidad en Materiales Industriales.',
    'Ubicar en pupitre central alejado de ventiladores ruidosos o ventanas a la calle.',
    'CONAPDIS-194029', '2026-11-05'
);

-- Caso 60: Marielva Hernández
CALL registrar_discapacidad_seed(
    '24671583', '29173120', 'Física',
    'Hemiparesia derecha leve residual con conservación motora funcional para marcha.',
    'Marcha independiente a ritmo pausado con discreta claudicación en miembro inferior derecho.',
    'Leve', 'Fisioterapia de fortalecimiento periódico.',
    'Autonomía en actividades cotidianas, lectoescritura compensada y buen ánimo.',
    'No', 'Calzado deportivo con suela de absorción de impacto.',
    'Evaluación integral de cierre de expediente institucional satisfactoria.',
    'Garantizar permanencia de aulas en planta baja y rampas habilitadas en el nuevo trayecto.',
    'CONAPDIS-582910', '2026-11-17'
);

-- =====================================================================
-- 3. LIMPIEZA DEL PROCEDIMIENTO TEMPORAL
-- =====================================================================
DROP PROCEDURE IF EXISTS registrar_discapacidad_seed;

-- =====================================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- 1) Total de expedientes de discapacidad:
--    SELECT COUNT(*) AS total_discapacidad FROM discapacidad;
--
-- 2) Expedientes por especialista:
--    SELECT CONCAT(e.nombre, ' ', e.apellido) AS especialista, COUNT(*) AS total
--      FROM discapacidad d
--      JOIN solicitud_de_servicio ss ON ss.id_solicitud_serv = d.id_solicitud_serv
--      JOIN dirpoles_security.empleado e ON e.id_empleado = ss.id_empleado
--     GROUP BY especialista;
--
-- 3) Distribución por tipo de discapacidad:
--    SELECT tipo_discapacidad, COUNT(*) AS total
--      FROM discapacidad
--     GROUP BY tipo_discapacidad ORDER BY total DESC;
--
-- 4) Distribución por grado de discapacidad:
--    SELECT grado, COUNT(*) AS total
--      FROM discapacidad
--     GROUP BY grado ORDER BY total DESC;
--
-- 5) Distribución cronológica anual (2024, 2025, 2026):
--    SELECT YEAR(fecha_creacion) AS anio, COUNT(*) AS total
--      FROM discapacidad
--     GROUP BY anio ORDER BY anio;
--
-- 6) Beneficiarios que requieren asistencia vs autónomos:
--    SELECT requiere_asistencia, COUNT(*) AS total
--      FROM discapacidad
--     GROUP BY requiere_asistencia;
--
-- 7) Beneficiarios con carnet CONAPDIS registrado:
--    SELECT CASE WHEN carnet_discapacidad IS NOT NULL THEN 'Con carnet CONAPDIS' ELSE 'En trámite / Sin carnet' END AS estatus_carnet,
--           COUNT(*) AS total
--      FROM discapacidad
--     GROUP BY estatus_carnet;
-- =====================================================================
