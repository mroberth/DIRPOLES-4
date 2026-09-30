/**
 * dist/js/modulos/reportes/tour.js
 * ---------------------------------------------------------------
 * Tour compartido de todos los reportes (Driver.js).
 * Cada vista declara window.REPORTES_TIPO y el botón #btn-ayuda;
 * los pasos describen las reglas REALES del módulo (filtros
 * server-side, generación, gráficos y tablas).
 */
window.ReportesTour = (function () {
    'use strict';

    const PASOS_COMUNES = [
        {
            element: '#fecha_inicio',
            popover: {
                title: 'Fecha Inicio',
                description: 'Desde esta fecha. El filtro se aplica en el servidor antes de traer los datos.',
            },
        },
        {
            element: '#fecha_fin',
            popover: {
                title: 'Fecha Fin',
                description: 'Hasta esta fecha. No puede ser anterior a la fecha de inicio.',
            },
        },
    ];

    const PASOS_POR_TIPO = {
        general: [
            { element: '#genero', popover: { title: 'Género', description: 'Filtra por género (M/F) del beneficiario.' } },
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra por programa académico; el catálogo lo carga la API.' } },
            { element: '#area', popover: { title: 'Área', description: 'Atenciones de un solo servicio: Becas, Exoneración, FAMES, Medicina, Orientación, Discapacidad o Psicología.' } },
        ],
        medicina: [
            { element: '#genero', popover: { title: 'Género', description: 'Filtra las consultas por género.' } },
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra por programa académico.' } },
        ],
        psicologia: [
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra morbilidad y citas por programa académico.' } },
            { element: '#tipo_consulta', popover: { title: 'Tipo de Consulta', description: 'Solo aplica a la morbilidad: Diagnóstico, Retiro temporal o Cambio de carrera.' } },
            { element: '#estado_cita', popover: { title: 'Estado de la Cita', description: 'Solo aplica a las citas: Pendiente, Confirmada, Atendida, Cancelada o No asistió.' } },
        ],
        orientacion: [
            { element: '#genero', popover: { title: 'Género', description: 'Filtra las orientaciones por género.' } },
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra por programa académico.' } },
        ],
        trabajo_social: [
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra por programa académico.' } },
            { element: '#submodulo', popover: { title: 'Submódulo', description: 'Becas, Exoneración, FAMES o Gestión de Embarazo.' } },
        ],
        discapacidad: [
            { element: '#pnf', popover: { title: 'PNF', description: 'Filtra por programa académico.' } },
            { element: '#tipo_discapacidad', popover: { title: 'Tipo de Discapacidad', description: 'Física, Sensorial, Intelectual, Múltiple u Otro (catálogo del sistema).' } },
            { element: '#grado', popover: { title: 'Grado', description: 'Leve, Moderado o Grave.' } },
        ],
        referencias: [
            { element: '#estado_ref', popover: { title: 'Estado', description: 'Pendiente, Aceptada o Rechazada.' } },
            { element: '#servicio_destino', popover: { title: 'Servicio Destino', description: 'Área que recibe la referencia; catálogo cargado por la API.' } },
        ],
        jornadas: [
            { element: '#estatus_jornada', popover: { title: 'Estatus', description: 'Activa, Cancelada o Finalizada.' } },
        ],
        mobiliario: [
            { element: '#tipo_bien', popover: { title: 'Tipo de Bien', description: 'Mobiliario o Equipos.' } },
            { element: '#estatus_mob', popover: { title: 'Estatus', description: 'Solo ítems Activos o Inactivos.' } },
        ],
        transporte: [
            { element: '#seccion_transporte', popover: { title: 'Sección', description: 'Selecciona una sub-entidad (Vehículos, Rutas, Proveedores, Repuestos, Asignaciones, Mantenimientos) o Todas.' } },
            { element: '#tipo_veh', popover: { title: 'Tipo de Vehículo', description: 'Autobús, Camioneta o Automóvil (aplica a vehículos).' } },
            { element: '#estado_veh', popover: { title: 'Estado/Estatus', description: 'Filtra por estado de la sub-entidad.' } },
        ],
    };

    const PASO_FINAL = {
        element: 'button[type="submit"]',
        popover: {
            title: 'Generar Reporte',
            description: 'Aplica los filtros en el servidor y refresca las tarjetas, los gráficos y la tabla.',
        },
    };

    function iniciar(tipo) {
        // El IIFE de Driver.js expone window.driver.js.driver(...) o
        // window.driver.js como función (según la versión instalada).
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);
        if (typeof factory !== 'function') return;

        const pasos = (PASOS_COMUNES || [])
            .concat(PASOS_POR_TIPO[tipo] || [])
            .concat([PASO_FINAL])
            .filter(paso => !paso.element || document.querySelector(paso.element));

        const driverObj = factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: pasos,
        });
        driverObj.drive();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const boton = document.getElementById('btn-ayuda');
        if (boton) {
            boton.addEventListener('click', () => iniciar(window.REPORTES_TIPO));
        }
    });

    return { iniciar };
})();
