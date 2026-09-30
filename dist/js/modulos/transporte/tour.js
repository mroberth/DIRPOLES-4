/**
 * dist/js/modulos/transporte/tour.js
 * ---------------------------------------------------------------
 * Tour guiado interactivo para Transporte (Driver.js).
 */
window.TransporteTour = (function () {
    'use strict';

    function objetivo(selector) {
        const elemento = document.querySelector(selector);
        if (!elemento) return selector;
        if (elemento.tagName === 'SELECT' && elemento.classList.contains('select2')
            && elemento.nextElementSibling?.classList.contains('select2-container')) {
            return elemento.nextElementSibling;
        }
        return elemento;
    }

    const PASOS = [
        {
            element: '#tabTransporteCrear',
            popover: {
                title: 'Módulo de Transporte',
                description: 'Bienvenido al módulo de Transporte. Aquí podrás registrar Rutas, Vehículos, Proveedores, Repuestos, Asignaciones de choferes y Mantenimientos.',
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '#tab-rutas-tab',
            popover: {
                title: 'Gestión de Rutas',
                description: 'Crea rutas institucionales o urbanas con horarios de salida/llegada y trayectoria.',
                side: 'bottom',
            },
        },
        {
            element: '#tab-vehiculos-tab',
            popover: {
                title: 'Gestión de Vehículos',
                description: 'Registra la flota de autobuses, camionetas o automóviles con su placa única y fecha de adquisición.',
                side: 'bottom',
            },
        },
        {
            element: '#tab-proveedores-tab',
            popover: {
                title: 'Gestión de Proveedores',
                description: 'Registra proveedores de insumos y repuestos con validación única de RIF/Cédula, correo y teléfono.',
                side: 'bottom',
            },
        },
        {
            element: '#tab-repuestos-tab',
            popover: {
                title: 'Inventario de Repuestos',
                description: 'Control de repuestos de transporte. Se crean con stock 0 y el stock ingresa únicamente mediante las Entradas del Kardex.',
                side: 'bottom',
            },
        },
        {
            element: '#tab-asignaciones-tab',
            popover: {
                title: 'Asignación de Choferes',
                description: 'Asigna un vehículo y una ruta activa a un chofer registrado en la institución.',
                side: 'bottom',
            },
        },
        {
            element: '#tab-mantenimiento-tab',
            popover: {
                title: 'Mantenimiento de Vehículos',
                description: 'Registra servicios preventivos o correctivos a las unidades. Al registrarlo, el vehículo pasa a estado "Mantenimiento" y los repuestos seleccionados se descuentan del stock.',
                side: 'bottom',
            },
        },
    ];

    function iniciar() {
        if (!window.driver || !window.driver.js || !window.driver.js.driver) {
            console.warn('Driver.js no está disponible.');
            return;
        }

        const pasosOptimizados = PASOS.map(p => ({
            element: objetivo(p.element),
            popover: p.popover,
        }));

        const driverObj = window.driver.js.driver({
            showProgress: true,
            animate: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: pasosOptimizados,
        });

        driverObj.drive();
    }

    return { iniciar };
})();
