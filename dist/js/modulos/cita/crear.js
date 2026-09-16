// dist/js/modulos/cita/crear.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-cita');
    if (!form) return;

    document.getElementById('btn-ayuda')?.addEventListener('click', () => {
        if (window.CitaTour) window.CitaTour.iniciar();
    });

    const beneficiario = document.getElementById('id_beneficiario');
    const empleado = document.getElementById('id_empleado');
    const fecha = document.getElementById('fecha');
    const horario = document.getElementById('horario-cita');

    function textoPersona(persona) {
        return `${persona.nombre || persona.nombres || ''} ${persona.apellido || persona.apellidos || ''} (${persona.tipo_cedula}-${persona.cedula})`;
    }

    function cargarOpciones(select, elementos, texto, valor = 'id_beneficiario') {
        select.innerHTML = '<option value="">Seleccione...</option>';
        elementos.forEach((elemento) => {
            const opcion = document.createElement('option');
            opcion.value = elemento[valor];
            opcion.textContent = texto(elemento);
            select.appendChild(opcion);
        });
    }

    async function cargarCatalogos() {
        const [beneficiarios, psicologos] = await Promise.all([
            apiFetch(BASE_URL + 'api/citas/beneficiarios'),
            apiFetch(BASE_URL + 'api/citas/psicologos'),
        ]);
        cargarOpciones(beneficiario, beneficiarios, textoPersona);
        cargarOpciones(empleado, psicologos, textoPersona, 'id_empleado');
        if (window.initSelect2) window.initSelect2(form);
        if (!window.CITAS_ES_ADMIN && psicologos[0]) {
            empleado.value = psicologos[0].id_empleado;
            $(empleado).trigger('change');
        }
        await mostrarHorario();
    }

    async function mostrarHorario() {
        if (!empleado.value) {
            horario.textContent = 'Selecciona un psicólogo para consultar su horario disponible.';
            return;
        }
        try {
            const datos = await apiFetch(`${BASE_URL}api/citas/horario?id_empleado=${encodeURIComponent(empleado.value)}`);
            horario.innerHTML = datos.length
                ? '<strong>Horario:</strong> ' + datos.map((fila) => `${fila.dia_semana} ${fila.hora_inicio}-${fila.hora_fin}`).join(' · ')
                : '<span class="text-warning">El psicólogo no tiene horario registrado.</span>';
        } catch (error) {
            horario.textContent = error.mensaje || 'No se pudo consultar el horario.';
        }
    }

    const validador = window.CitaValidaciones.configurar(form, {
        validarDisponibilidad: (datos) => apiFetch(BASE_URL + 'api/citas/disponibilidad', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos),
        }),
    });

    empleado.addEventListener('change', mostrarHorario);
    if (typeof jQuery !== 'undefined' && $.fn?.select2) {
        $(empleado).on('change select2:select select2:clear', mostrarHorario);
    }
    form.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (!await validador.validarTodo()) {
            AlertManager.warning('Revisa los datos', 'Corrige los campos resaltados antes de continuar.');
            return;
        }
        try {
            await apiFetch(BASE_URL + 'api/citas/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id_beneficiario: beneficiario.value,
                    id_empleado: empleado.value,
                    fecha: fecha.value,
                    hora: document.getElementById('hora').value,
                }),
            });
            AlertManager.success('Cita registrada', 'La cita se guardó correctamente.');
            form.reset();
            validador.limpiar();
            await mostrarHorario();
            if (window.CitaStats) window.CitaStats.cargar();
        } catch (error) {
            AlertManager.error('No se pudo registrar', error.mensaje || 'Error inesperado.');
        }
    });

    cargarCatalogos().catch((error) => {
        AlertManager.error('No se cargaron los datos', error.mensaje || 'Intenta nuevamente.');
    });
});
