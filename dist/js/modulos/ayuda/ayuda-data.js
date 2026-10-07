/**
 * dist/js/modulos/ayuda/ayuda-data.js
 * ---------------------------------------------------------------
 * Dataset local cliente de respaldo para el Centro de Ayuda de DIRPOLES-4.
 */
window.HELP_GUIDES_LOCAL = {
    categorias: {
        inicio: {
            titulo: 'Inicio de Sesión y Dashboard',
            descripcion: 'Acceso al sistema, panel principal e indicadores.',
            icono: 'fa-sign-in-alt',
            color: 'primary',
            grupo: 'general',
            keywords: 'login sesion acceso contraseña correo dashboard calendario inicio',
        },
        empleados: {
            titulo: 'Gestión de Empleados',
            descripcion: 'Registro de personal, asignación de roles y permisos.',
            icono: 'fa-users',
            color: 'success',
            grupo: 'admin',
            keywords: 'empleados personal nomina registro crear editar eliminar rol',
        },
        beneficiarios: {
            titulo: 'Gestión de Beneficiarios',
            descripcion: 'Registro de estudiantes, datos PNF y ficha personal.',
            icono: 'fa-user-graduate',
            color: 'info',
            grupo: 'general',
            keywords: 'beneficiarios estudiantes cedula pnf registro ficha',
        },
        citas: {
            titulo: 'Gestión de Citas',
            descripcion: 'Programación y seguimiento de citas de Psicología.',
            icono: 'fa-calendar-check',
            color: 'warning',
            grupo: 'psicologia',
            keywords: 'citas calendario agenda psicologo horario agendar disponibilidad',
        },
        diagnosticos: {
            titulo: 'Diagnósticos y Atenciones',
            descripcion: 'Atención especializada (Psicología, Medicina, Orientación, Trabajo Social, Discapacidad).',
            icono: 'fa-file-medical',
            color: 'danger',
            grupo: 'salud',
            keywords: 'diagnostico psicologia medicina orientacion trabajo social discapacidad constancia recipe',
        },
        inventario: {
            titulo: 'Inventario Médico',
            descripcion: 'Control de medicamentos, insumos, entradas y kardex.',
            icono: 'fa-pills',
            color: 'info',
            grupo: 'salud',
            keywords: 'inventario medico insumos medicamentos stock farmacia entrada salida kardex',
        },
        referencias: {
            titulo: 'Referencias entre Áreas',
            descripcion: 'Derivaciones interdepartamentales y seguimiento de casos.',
            icono: 'fa-share-nodes',
            color: 'secondary',
            grupo: 'general',
            keywords: 'referencias derivadas traslado canalizar modulo area origen destino',
        },
        jornadas: {
            titulo: 'Jornadas Médicas',
            descripcion: 'Eventos especiales, control de aforo y atención masiva.',
            icono: 'fa-briefcase-medical',
            color: 'success',
            grupo: 'salud',
            keywords: 'jornadas medicina eventos masiva asistencia operativo insumos aforo',
        },
        mobiliario: {
            titulo: 'Mobiliario y Equipos',
            descripcion: 'Censo de activos fijos, seriales y Fichas Técnicas.',
            icono: 'fa-chair',
            color: 'warning',
            grupo: 'admin',
            keywords: 'mobiliario equipos fichas tecnicas seriales asignacion activos bienes',
        },
        transporte: {
            titulo: 'Gestión de Transporte',
            descripcion: 'Flota vehicular, choferes, rutas, repuestos y mantenimientos.',
            icono: 'fa-truck',
            color: 'dark',
            grupo: 'admin',
            keywords: 'transporte vehiculo ruta chofer proveedor repuestos mantenimiento asignaciones',
        },
        reportes: {
            titulo: 'Reportes Estadísticos',
            descripcion: 'Análisis de datos, filtros por fecha/área y exportación.',
            icono: 'fa-chart-pie',
            color: 'primary',
            grupo: 'general',
            keywords: 'reportes graficos estadisticas filtros general medicina psicologia excel pdf',
        },
        seguridad: {
            titulo: 'Seguridad y Auditoría',
            descripcion: 'Bitácora de eventos, matriz de permisos y respaldos BD.',
            icono: 'fa-shield-halved',
            color: 'dark',
            grupo: 'admin',
            keywords: 'seguridad bitacora auditoria permisos roles respaldo base de datos',
        },
    },
    guias: {
        login_acceso: {
            category: 'Inicio de Sesión',
            categoria_key: 'inicio',
            title: '¿Cómo iniciar sesión en el sistema?',
            route: 'inicio',
            steps: [
                { icon: 'fa-envelope', text: 'Ingrese su <b>correo electrónico</b> institucional registrado.' },
                { icon: 'fa-lock', text: 'Introduzca su <b>contraseña</b>. El sistema protege las credenciales mediante encriptación RSA y bcrypt.' },
                { icon: 'fa-right-to-bracket', text: 'Haga clic en el botón <b>"Iniciar Sesión"</b> para acceder al panel principal.' }
            ],
            tip: 'Tras 3 intentos fallidos consecutivos, el sistema bloqueará temporalmente el acceso por seguridad.'
        },
        login_dashboard: {
            category: 'Inicio de Sesión',
            categoria_key: 'inicio',
            title: '¿Cómo funciona el Panel de Inicio?',
            route: 'inicio',
            steps: [
                { icon: 'fa-gauge-high', text: 'El panel muestra <b>tarjetas de acceso rápido</b> según los permisos asignados a su rol.' },
                { icon: 'fa-chart-line', text: 'Las tarjetas de estadísticas muestran contadores en tiempo real de su departamento.' },
                { icon: 'fa-calendar-days', text: 'En la parte inferior dispone de su <b>Calendario Personal</b> para programar tareas y recordatorios.' }
            ],
            tip: 'Puede hacer clic en cualquiera de las tarjetas de módulo disponibles para ir directamente a la pantalla de gestión.'
        },
        beneficiarios_crear: {
            category: 'Gestión de Beneficiarios',
            categoria_key: 'beneficiarios',
            title: '¿Cómo registrar un nuevo beneficiario (estudiante)?',
            route: 'beneficiarios/crear',
            steps: [
                { icon: 'fa-address-book', text: 'Vaya a <b>Gestionar Beneficiarios >> Crear</b>.' },
                { icon: 'fa-graduation-cap', text: 'Complete el tipo de documento, cédula, nombres, apellidos, PNF, trayecto y datos de contacto.' },
                { icon: 'fa-check-double', text: 'Haga clic en <b>"Registrar Beneficiario"</b>.' }
            ],
            tip: 'Verifique que la cédula sea correcta, ya que esta identificará al beneficiario en todos los diagnósticos y citas.'
        },
        citas_crear: {
            category: 'Gestión de Citas',
            categoria_key: 'citas',
            title: '¿Cómo agendar una cita médica o de psicología?',
            route: 'citas/crear',
            steps: [
                { icon: 'fa-calendar-plus', text: 'Acceda a <b>Gestionar Citas >> Crear</b>.' },
                { icon: 'fa-user-check', text: 'Seleccione el beneficiario y escoja al especialista responsable.' },
                { icon: 'fa-clock', text: 'Seleccione la fecha y hora deseada. El sistema validará automáticamente la disponibilidad en la agenda.' },
                { icon: 'fa-paper-plane', text: 'Presione <b>"Agendar Cita"</b>. El beneficiario recibirá la confirmación correspondiente.' }
            ],
            tip: 'Si eres Psicólogo, la pantalla priorizará automáticamente tus bloques de horario registrados.'
        },
        diagnosticos_psicologia: {
            category: 'Diagnósticos y Atenciones',
            categoria_key: 'diagnosticos',
            title: '¿Cómo registrar un diagnóstico de Psicología?',
            route: 'psicologia/crear',
            steps: [
                { icon: 'fa-brain', text: 'En el menú lateral, seleccione <b>Diagnósticos >> Psicología</b>.' },
                { icon: 'fa-magnifying-glass-chart', text: 'Busque y seleccione al beneficiario atendiéndose.' },
                { icon: 'fa-file-pen', text: 'Complete el motivo de consulta, evaluación psicológica, diagnóstico y plan terapéutico.' },
                { icon: 'fa-floppy-disk', text: 'Guarde la consulta. Podrá imprimir la <b>Constancia de Atención</b> en PDF desde el listado.' }
            ],
            tip: 'El sistema registra automáticamente la auditoría de cada atención realizada en la Bitácora.'
        },
        referencias_crear: {
            category: 'Referencias entre Áreas',
            categoria_key: 'referencias',
            title: '¿Cómo referir a un estudiante a otra especialidad?',
            route: 'referencias/crear',
            steps: [
                { icon: 'fa-share-nodes', text: 'Seleccione <b>Gestionar Referencias >> Crear</b>.' },
                { icon: 'fa-user', text: 'Indique el beneficiario a ser derivado.' },
                { icon: 'fa-building', text: 'Seleccione el servicio o área de destino (ej. Psicología, Medicina, Trabajo Social).' },
                { icon: 'fa-message', text: 'Escriba el motivo detallado de la referencia y las observaciones asociadas.' },
                { icon: 'fa-paper-plane', text: 'Guarde la referencia. El área de destino recibirá una notificación automática.' }
            ],
            tip: 'El especialista del área de destino podrá Aceptar o Rechazar la referencia desde su panel de consulta.'
        },
        reportes_psicologia: {
            category: 'Reportes Estadísticos',
            categoria_key: 'reportes',
            title: '¿Cómo consultar los Reportes de Psicología?',
            route: 'reportes/psicologia',
            steps: [
                { icon: 'fa-brain', text: 'En el menú lateral, seleccione <b>Reportes >> Psicología</b>.' },
                { icon: 'fa-filter', text: 'Filtre por rango de fechas, género, PNF o tipo de consulta.' },
                { icon: 'fa-chart-bar', text: 'Analice las gráficas de morbilidad y citas atendidas.' }
            ],
            tip: 'Puede hacer clic en "Ir al Módulo" para abrir directamente la pantalla de reportes de Psicología.'
        },
        reportes_referencias: {
            category: 'Reportes Estadísticos',
            categoria_key: 'reportes',
            title: '¿Cómo consultar los Reportes de Referencias?',
            route: 'reportes/referencias',
            steps: [
                { icon: 'fa-share-nodes', text: 'En el menú lateral, seleccione <b>Reportes >> Referencias</b>.' },
                { icon: 'fa-filter', text: 'Consulte las estadísticas de referencias emitidas, aceptadas y rechazadas.' }
            ],
            tip: 'Muestra la efectividad del flujo interdepartamental.'
        }
    }
};
