// dist/js/modulos/referencias/crear.js
// ------------------------------------------------------------------
// Solo la lógica de ESTA pantalla: catálogos con cascada
// servicio→empleados, origen fijo para no administradores, tour,
// validación y envío. Las validaciones y el tour viven en archivos
// aparte. Reglas del backend que repite la interfaz:
//  - el origen de un no administrador es él mismo (selects deshabilitados);
//  - el servicio destino debe ser distinto al de origen.
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-referencia');
    if (!form) return;

    const esAdmin = !!window.REFERENCIAS_ES_ADMIN;
    const V = window.ReferenciasValidaciones;

    // Tour guiado (Driver.js).
    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda && window.ReferenciasTour) {
        btnAyuda.addEventListener('click', () => window.ReferenciasTour.iniciar());
    }

    const validador = V.configurar(form);

    const selBeneficiario = document.getElementById('id_beneficiario');
    const selServOrigen  = document.getElementById('id_servicio_origen');
    const selEmpOrigen   = document.getElementById('id_empleado_origen');
    const selServDestino = document.getElementById('id_servicio_destino');
    const selEmpDestino  = document.getElementById('id_empleado_destino');

    let servicios = [];

    const textoBeneficiario = (b) =>
        `${b.nombres} ${b.apellidos} (${b.tipo_cedula}-${b.cedula})`;

    function llenar(select, items, texto, valor) {
        select.innerHTML = '<option value="">Seleccione…</option>';
        items.forEach((item) => {
            const opcion = document.createElement('option');
            opcion.value = item[valor];
            opcion.textContent = texto(item);
            select.appendChild(opcion);
        });
        if (window.initSelect2) window.initSelect2(form);
    }

    function llenarServicios(select, excluirId) {
        const idExcluir = String(excluirId || '');
        select.innerHTML = '<option value="">Seleccione…</option>';
        servicios
            .filter((s) => String(s.id_servicios) !== idExcluir)
            .forEach((s) => {
                const opcion = document.createElement('option');
                opcion.value = s.id_servicios;
                opcion.textContent = s.nombre_serv;
                select.appendChild(opcion);
            });
        if (window.initSelect2) window.initSelect2(form);
    }

    /** Empleados activos de un servicio; si la API falla → ROJO (nunca verde). */
    async function cargarEmpleados(select, idServicio, placeholder) {
        if (!idServicio) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            if (window.initSelect2) window.initSelect2(form);
            return;
        }
        try {
            const empleados = await apiFetch(
                BASE_URL + 'api/referencias/empleados?id_servicio=' + encodeURIComponent(idServicio)
            );
            llenar(select, empleados, (e) => e.nombre_completo, 'id_empleado');
        } catch (error) {
            console.error('Empleados del servicio:', error);
            select.innerHTML = '<option value="">Sin opciones</option>';
            V.mostrarError(select, 'No se pudo verificar con el servidor. Intenta de nuevo.');
            if (window.initSelect2) window.initSelect2(form);
        }
    }

    /** Origen fijo de los no administradores: mi servicio + yo. */
    function aplicarOrigenPropio() {
        const idPropio = window.REFERENCIAS_SERVICIO_ORIGEN || 0;
        const mio = servicios.find((s) => String(s.id_servicios) === String(idPropio));

        selServOrigen.innerHTML = '';
        const opcionServicio = document.createElement('option');
        opcionServicio.value = idPropio;
        opcionServicio.textContent = mio ? mio.nombre_serv : 'Mi servicio';
        selServOrigen.appendChild(opcionServicio);
        selServOrigen.value = idPropio;
        selServOrigen.disabled = true;

        selEmpOrigen.innerHTML = '';
        const opcionEmpleado = document.createElement('option');
        opcionEmpleado.value = window.REFERENCIAS_ID_EMPLEADO || 0;
        opcionEmpleado.textContent = window.REFERENCIAS_NOMBRE_ORIGEN || 'Yo';
        selEmpOrigen.appendChild(opcionEmpleado);
        selEmpOrigen.value = window.REFERENCIAS_ID_EMPLEADO || 0;
        selEmpOrigen.disabled = true;

        llenarServicios(selServDestino, idPropio);
        if (window.initSelect2) window.initSelect2(form);
    }

    async function cargarCatalogos() {
        const [serviciosApi, beneficiarios] = await Promise.all([
            apiFetch(BASE_URL + 'api/referencias/servicios'),
            apiFetch(BASE_URL + 'api/referencias/beneficiarios'),
        ]);
        servicios = serviciosApi;

        llenar(selBeneficiario, beneficiarios, textoBeneficiario, 'id_beneficiario');

        if (esAdmin) {
            llenarServicios(selServOrigen, null);
            llenarServicios(selServDestino, null);
        } else {
            if (!window.REFERENCIAS_SERVICIO_ORIGEN) {
                AlertManager.error(
                    'Sin servicio asignado',
                    'Tu perfil no tiene un servicio: no puedes crear referencias.'
                );
                return;
            }
            aplicarOrigenPropio();
        }
    }

    // ---------- Cascadas ----------
    async function alCambiarOrigen() {
        if (!esAdmin) return;
        llenarServicios(selServDestino, selServOrigen.value);
        await cargarEmpleados(selEmpOrigen, selServOrigen.value, 'Seleccione el servicio primero…');
        await cargarEmpleados(selEmpDestino, selServDestino.value, 'Seleccione el servicio primero…');
    }

    async function alCambiarDestino() {
        await cargarEmpleados(selEmpDestino, selServDestino.value, 'Seleccione el servicio primero…');
    }

    selServOrigen.addEventListener('change', alCambiarOrigen);
    selServDestino.addEventListener('change', alCambiarDestino);
    if (typeof jQuery !== 'undefined' && $.fn && $.fn.select2) {
        $(selServOrigen).on('change select2:select select2:clear', alCambiarOrigen);
        $(selServDestino).on('change select2:select select2:clear', alCambiarDestino);
    }

    // ---------- Reinicio tras guardar ----------
    function reiniciar() {
        form.reset();
        validador.limpiar();
        if (esAdmin) {
            llenarServicios(selServOrigen, null);
            llenarServicios(selServDestino, null);
            selEmpOrigen.innerHTML = '<option value="">Seleccione…</option>';
        } else {
            aplicarOrigenPropio();
        }
        selEmpDestino.innerHTML = '<option value="">Seleccione el servicio primero…</option>';
        if (window.initSelect2) window.initSelect2(form);
    }

    // ---------- Enviar ----------
    form.addEventListener('submit', async function (ev) {
        ev.preventDefault();

        if (!validador.validarTodo()) {
            AlertManager.warning('Formulario incompleto', 'Corrige los campos resaltados antes de continuar.');
            return;
        }

        const datos = {
            id_beneficiario:     Number(selBeneficiario.value),
            id_servicio_origen:  Number(selServOrigen.value),
            id_empleado_origen:  Number(selEmpOrigen.value),
            id_servicio_destino: Number(selServDestino.value),
            id_empleado_destino: Number(selEmpDestino.value),
            motivo:              document.getElementById('motivo').value.trim(),
            observaciones:       document.getElementById('observaciones').value.trim(),
        };

        try {
            const nueva = await apiFetch(BASE_URL + 'api/referencias/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos),
            });

            AlertManager.success(
                'Referencia enviada',
                `Se refirió a "${nueva.beneficiario}" hacia ${nueva.nombre_destino}. `
                + 'Recibirá una notificación y quedará en estado Pendiente.'
            );
            reiniciar();
            if (window.ReferenciasStats) window.ReferenciasStats.cargar();
        } catch (error) {
            if (error.codigo === 'VALIDATION_ERROR' || error.codigo === 'NOT_FOUND') {
                AlertManager.warning('Revisa los datos', error.mensaje);
            } else if (error.codigo === 'ACCESS_DENIED') {
                AlertManager.warning('Sin permiso', error.mensaje);
            } else {
                AlertManager.error('Error', error.mensaje);
            }
        }
    });

    cargarCatalogos().catch((error) => {
        console.error('Catálogos de referencias:', error);
        AlertManager.error('No se cargaron los datos', error.mensaje || 'Intenta nuevamente.');
    });
});
