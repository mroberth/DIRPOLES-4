// dist/js/modulos/trabajo-social/tour.js
// Tour guiado con Driver.js de la página de Trabajo Social.
// Los pasos se arman al INICIAR el tour según la pestaña activa
// (#tsTabs .nav-link.active): nunca se muestran campos de otra pestaña
// y se omiten los elementos que no están visibles (p. ej. #otro_motivo
// solo aparece cuando el motivo es "Otro").
window.TrabajoSocialTour = (function () {
    'use strict';

    const PASOS = {
        comunes: [
            {
                element: '#id_beneficiario',
                title: 'Beneficiario global',
                description: 'Selecciona primero al beneficiario. Este dato se aplica a TODOS los formularios de la página (becas, exoneraciones, FAMES y gestaciones).',
            },
            {
                element: '#tsTabs',
                title: 'Pestañas',
                description: 'Cada pestaña registra un tipo de gestión de Trabajo Social: becas, exoneraciones, FAMES y embarazadas. El tour adapta sus pasos a la pestaña activa.',
            },
        ],
        becas: [
            {
                element: '#becas-tab',
                title: 'Registro de Becas',
                description: 'Pestaña activa: registro de becas. Tipo de banco, cuenta BCV y planilla de inscripción en PDF son obligatorios.',
            },
            {
                element: '#tipo_banco',
                title: 'Tipo de banco',
                description: 'Banco que recibirá la beca (obligatorio). Son los 26 bancos habilitados en el sistema.',
            },
            {
                element: '#cta_bcv',
                title: 'Cuenta BCV',
                description: 'Número de cuenta bancaria con exactamente 16 dígitos numéricos (obligatorio).',
            },
            {
                element: '#planilla',
                title: 'Planilla de inscripción',
                description: 'Archivo PDF obligatorio con la planilla de inscripción del beneficiario. Solo se aceptan archivos .pdf válidos.',
            },
            {
                element: '#form-becas button[type="submit"]',
                title: 'Registrar',
                description: 'Revisa los campos marcados en rojo y guarda la beca. El sistema sube la planilla y registra la auditoría automáticamente.',
            },
        ],
        exoneracion: [
            {
                element: '#exoneracion-tab',
                title: 'Registro de Exoneración',
                description: 'Pestaña activa: exoneraciones. Motivo (Inscripción, Paquete de Grado u Otro — si es "Otro" hay que detallarlo), carnet de discapacidad y carta en PDF obligatorios. Cada exoneración queda PENDIENTE de estudio socioeconómico.',
            },
            {
                element: '#motivo',
                title: 'Motivo',
                description: 'Motivo de la exoneración (obligatorio): Inscripción, Paquete de Grado u Otro.',
            },
            {
                element: '#otro_motivo',
                title: 'Detalle del motivo',
                description: 'Campo que aparece solo cuando el motivo es "Otro": describe el motivo en un máximo de 100 caracteres (obligatorio).',
            },
            {
                element: '#carnet_discapacidad',
                title: 'Carnet de discapacidad',
                description: 'Número del carnet de discapacidad del beneficiario (obligatorio).',
            },
            {
                element: '#carta',
                title: 'Carta de exoneración',
                description: 'Archivo PDF obligatorio con la carta de exoneración. Solo se aceptan archivos .pdf válidos.',
            },
            {
                element: '#form-exoneracion button[type="submit"]',
                title: 'Registrar',
                description: 'Revisa los campos marcados en rojo y guarda la exoneración. Quedará automáticamente pendiente de estudio socioeconómico.',
            },
        ],
        estudio: [
            {
                element: '#btn-estudio',
                title: 'Estudio Socio-Económico',
                description: 'Este botón abre la lista de exoneraciones que todavía no tienen estudio socioeconómico. Desde ahí se completa el estudio y se genera su PDF.',
            },
        ],
        // Pasos del offcanvas del estudio (se recorren con el botón de
        // ayuda DENTRO del offcanvas: requiere que esté abierto).
        estudioOffcanvas: [
            {
                element: '#navPasosEstudio',
                title: 'Cinco pasos',
                description: 'El estudio se completa en cinco pasos: Personales, Educativos, Grupo Familiar, Economía y Observaciones. Avanza con Siguiente/Anterior sin perder lo escrito.',
            },
            {
                element: '#imagen_se',
                title: 'Foto (opcional)',
                description: 'Si adjuntas una foto (JPG, JPEG, PNG o GIF) se incrusta en el PDF. Si no la llevas, el documento se genera igual.',
            },
            {
                element: '#fecha',
                title: 'Solicitud',
                description: 'Indica el tipo de solicitud (Renovación o Nueva), la fecha y el beneficio solicitado.',
            },
            {
                element: '#nombre',
                title: 'Datos precargados',
                description: 'Nombre, cédula, fecha de nacimiento, teléfono, correo, sección, PNF y edad vienen del beneficiario seleccionado. Completa los que falten (lugar de nacimiento, estado civil, etc.).',
            },
            {
                element: '#tab-paso2',
                title: 'Paso 2: Educativos',
                description: 'La especialidad/PNF queda en solo lectura si el beneficiario tiene PNF; registra semestre o trayecto, turno, sección, correo y redes sociales.',
            },
            {
                element: '#tab-paso3',
                title: 'Paso 3: Grupo Familiar',
                description: 'Hasta 5 integrantes con edad, parentesco, estado civil, instrucción, ocupación, sueldo y aporte al hogar.',
            },
            {
                element: '#tab-paso4',
                title: 'Paso 4: Economía',
                description: 'Ingresos, egresos, tenencia y tipo de vivienda. Los montos se escriben con el prefijo BsD.',
            },
            {
                element: '#tab-paso5',
                title: 'Paso 5: Observaciones',
                description: 'Observaciones finales del caso y botones para cerrar o generar el PDF.',
            },
            {
                element: '#btnGenerarPDF',
                title: 'Generar PDF',
                description: 'Crea el PDF con FPDF, lo guarda en uploads/trabajo_social/exoneracion/estudiose/ y lo vincula a la exoneración. Los campos NO se guardan en la BD: el PDF es el registro, y la acción queda en la Bitácora.',
            },
        ],
        fames: [
            {
                element: '#fames-tab',
                title: 'Registro de FAMES',
                description: 'Pestaña activa: FAMES. Patología del caso y tipo de ayuda obligatorios; si el tipo es "Otros" hay que detallarlo. La patología "Sin patología" (1 y 2) no está habilitada.',
            },
            {
                element: '#patologia_fames',
                title: 'Patología',
                description: 'Patología asociada al caso FAMES (obligatoria). Se cargan solo las patologías habilitadas del sistema.',
            },
            {
                element: '#tipo_ayuda',
                title: 'Tipo de ayuda',
                description: 'Tipo de ayuda otorgada (obligatorio): Económica, Operaciones, Exámenes u Otros.',
            },
            {
                element: '#otro_tipo',
                title: 'Detalle del tipo de ayuda',
                description: 'Campo que aparece solo cuando el tipo es "Otros": describe la ayuda en un máximo de 100 caracteres (obligatorio).',
            },
            {
                element: '#form-fames button[type="submit"]',
                title: 'Registrar',
                description: 'Revisa los campos marcados en rojo y guarda el registro FAMES con su auditoría.',
            },
        ],
        embarazadas: [
            {
                element: '#embarazadas-tab',
                title: 'Registro de Embarazadas',
                description: 'Pestaña activa: gestaciones. Aplica SOLO a beneficiarias de género femenino; nace con estado "En Proceso" y solo puede haber una gestión en proceso por beneficiaria.',
            },
            {
                element: '#patologia_embarazada',
                title: 'Patología (Embarazo)',
                description: 'Patología asociada a la gestación (obligatoria), con las mismas patologías habilitadas que FAMES.',
            },
            {
                element: '#semanas_gest',
                title: 'Semanas de gestación',
                description: 'Semanas de gestación (obligatorio): un número entero entre 1 y 45.',
            },
            {
                element: '#codigo_patria',
                title: 'Código Patria',
                description: 'Código del carnet de la Patria (opcional): solo números, máximo 10 dígitos.',
            },
            {
                element: '#serial_patria',
                title: 'Serial Patria',
                description: 'Serial del carnet de la Patria (opcional): solo números, máximo 10 dígitos.',
            },
            {
                element: '#form-embarazadas button[type="submit"]',
                title: 'Registrar',
                description: 'Revisa los campos marcados en rojo y guarda la gestión. Si la beneficiaria no es femenino o ya tiene una gestión en proceso, el sistema bloqueará el registro.',
            },
        ],
    };

    /** Pestaña activa del hub: clave del arreglo PASOS. */
    const PESTANAS = {
        'becas-tab': 'becas',
        'exoneracion-tab': 'exoneracion',
        'fames-tab': 'fames',
        'embarazadas-tab': 'embarazadas',
    };

    /**
     * Pestaña activa del hub. Si no hay ninguna marcada, se asume
     * becas (estado inicial de la página).
     */
    function pestanaActiva() {
        const activa = document.querySelector('#tsTabs .nav-link.active');
        if (activa && PESTANAS[activa.id]) {
            return PESTANAS[activa.id];
        }
        return 'becas';
    }

    /**
     * Devuelve el elemento real a resaltar. Si es un <select> con Select2,
     * el <select> original está oculto, así que devolvemos su contenedor
     * .select2-container para que el foco sea el select completo.
     * Devuelve null si el selector no existe en el DOM.
     */
    function objetivo(selector) {
        const elemento = document.querySelector(selector);
        if (!elemento) return null;
        if (elemento.tagName === 'SELECT' && elemento.classList.contains('select2')
            && elemento.nextElementSibling && elemento.nextElementSibling.classList.contains('select2-container')) {
            return elemento.nextElementSibling;
        }
        return elemento;
    }

    /** ¿El elemento resuelto está realmente visible (con tamaño) en pantalla? */
    function visible(selector) {
        const elemento = objetivo(selector);
        if (!elemento) return false;
        const rect = elemento.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    /** Construye y ejecuta un tour con los pasos visibles. */
    function ejecutar(pasos) {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);

        if (typeof factory !== 'function') {
            console.warn('Driver.js no está disponible.');
            return;
        }

        const visibles = pasos.filter((paso) => visible(paso.element));
        if (visibles.length === 0) {
            console.warn('TrabajoSocialTour: no hay pasos visibles.');
            return;
        }

        factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: visibles.map((paso) => ({
                element: objetivo(paso.element),
                popover: { title: paso.title, description: paso.description, side: 'bottom', align: 'start' },
            })),
        }).drive();
    }

    /** Tour de la página de creación (según la pestaña activa del hub). */
    function iniciar() {
        const clave = pestanaActiva();
        ejecutar(PASOS.comunes.concat(PASOS[clave]).concat(PASOS.estudio));
    }

    /** Tour del offcanvas del estudio (botón #btn-ayuda-estudio). */
    function iniciarEstudio() {
        ejecutar(PASOS.estudioOffcanvas);
    }

    return { iniciar, iniciarEstudio };
}());
