// dist/js/modulos/cita/editar.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-editar-cita');
    const modalElemento = document.getElementById('modal-editar-cita');
    if (!form || !modalElemento) return;

    const modal = new bootstrap.Modal(modalElemento);
    const opciones = { idCita: 0 };
    const empleado = document.getElementById('editar_id_empleado');
    const beneficiario = document.getElementById('editar_id_beneficiario');
    const estados = document.getElementById('editar_estatus');

    function personaTexto(persona) {
        return `${persona.nombre || persona.nombres || ''} ${persona.apellido || persona.apellidos || ''} (${persona.tipo_cedula}-${persona.cedula})`;
    }

    function cargarSelect(select, items, texto, clave) {
        select.innerHTML = '<option value="">Seleccione...</option>';
        items.forEach((item) => {
            const opcion = document.createElement('option');
            opcion.value = item[clave];
            opcion.textContent = texto(item);
            select.appendChild(opcion);
        });
    }

    const validador = window.CitaValidaciones.configurar(form, {
        selectorEmpleado: '#editar_id_empleado',
        selectorBeneficiario: '#editar_id_beneficiario',
        selectorFecha: '#editar_fecha',
        selectorHora: '#editar_hora',
        validarDisponibilidad: (datos) => apiFetch(BASE_URL + 'api/citas/disponibilidad', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos),
        }),
        idCita: opciones.idCita,
    });

    async function cargarCatalogos() {
        const [beneficiarios, psicologos, estadosCita] = await Promise.all([
            apiFetch(BASE_URL + 'api/citas/beneficiarios'),
            apiFetch(BASE_URL + 'api/citas/psicologos'),
            apiFetch(BASE_URL + 'api/citas/estados'),
        ]);
        cargarSelect(beneficiario, beneficiarios, personaTexto, 'id_beneficiario');
        cargarSelect(empleado, psicologos, personaTexto, 'id_empleado');
        estados.innerHTML = '';
        estadosCita.forEach((estado) => {
            const opcion = document.createElement('option');
            opcion.value = estado.id_estado;
            opcion.textContent = estado.nombre;
            estados.appendChild(opcion);
        });
    }

    window.CitaEditar = {
        async abrir(cita) {
            try {
                await cargarCatalogos();
                opciones.idCita = Number(cita.id_cita);
                document.getElementById('editar_id_cita').value = cita.id_cita;
                beneficiario.value = cita.id_beneficiario;
                empleado.value = cita.id_empleado;
                document.getElementById('editar_fecha').value = cita.fecha;
                document.getElementById('editar_hora').value = String(cita.hora).slice(0, 5);
                estados.value = cita.estatus;
                validador.limpiar();
                modal.show();
            } catch (error) {
                AlertManager.error('No se pudo abrir', error.mensaje || 'No se cargaron los datos de edición.');
            }
        },
    };

    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (!await validador.validarTodo()) {
            AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
            return;
        }
        try {
            await apiFetch(BASE_URL + 'api/citas/actualizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id_cita: document.getElementById('editar_id_cita').value,
                    id_beneficiario: beneficiario.value,
                    id_empleado: empleado.value,
                    fecha: document.getElementById('editar_fecha').value,
                    hora: document.getElementById('editar_hora').value,
                    estatus: estados.value,
                }),
            });
            modal.hide();
            AlertManager.success('Cita actualizada', 'Los cambios se guardaron correctamente.');
            if (window.CitasConsultar) window.CitasConsultar.cargar();
            if (window.CitaStats) window.CitaStats.cargar();
        } catch (error) {
            AlertManager.error('No se pudo actualizar', error.mensaje || 'Error inesperado.');
        }
    });
});
