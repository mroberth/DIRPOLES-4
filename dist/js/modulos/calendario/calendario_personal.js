// dist/js/modulos/calendario/calendario_personal.js
// ------------------------------------------------------------------
// Calendario personal del empleado (FullCalendar + API del esqueleto).
//   - Eventos propios vía GET  api/calendario/eventos
//   - Crear/editar/eliminar vía POST api/calendario/{guardar,actualizar,eliminar}
// Usa apiFetch (contrato Respuesta) y distingue errores por error.codigo.
// ------------------------------------------------------------------
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        const api = (url, opciones = {}) =>
            apiFetch(window.BASE_URL + url, {
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                ...opciones,
            });

        const calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap5',
            locale: 'es',
            initialView: 'dayGridMonth',
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Lista' },
            headerToolbar: {
                start: 'title',
                center: 'prev,next today',
                end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            selectable: true,
            editable: false,
            dayMaxEvents: true,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',

            // Fuente única: eventos personales del usuario autenticado.
            events: function (info, successCallback, failureCallback) {
                api('api/calendario/eventos')
                    .then((eventos) => {
                        successCallback(eventos.map((e) => ({
                            id: String(e.id_evento),
                            title: e.titulo,
                            start: e.fecha,
                            color: '#6f42c1',
                            extendedProps: { descripcion: e.descripcion || '' }
                        })));
                    })
                    .catch(failureCallback);
            },

            dateClick: (info) => abrirModalEvento(info.dateStr),
            select: (info) => abrirModalEvento(info.startStr),
            eventClick: (info) => {
                info.jsEvent.preventDefault();
                abrirDetalleEvento(info.event);
            }
        });

        calendar.render();
        window.calendarioPersonal = calendar;

        // ---------- Crear ----------
        function abrirModalEvento(fechaInicio) {
            if (typeof Swal === 'undefined') return;

            let fecha = fechaInicio || '';
            if (fecha && !fecha.includes('T')) {
                const ahora = new Date();
                const hh = String(ahora.getHours()).padStart(2, '0');
                const mm = String(ahora.getMinutes()).padStart(2, '0');
                fecha = `${fecha}T${hh}:${mm}`;
            }

            Swal.fire({
                title: 'Nuevo evento personal',
                html: `
                    <div class="text-start">
                        <label class="form-label">Título *</label>
                        <input id="ev-titulo" class="form-control mb-3" maxlength="100" placeholder="Ej: Entrega de documentos">
                        <label class="form-label">Descripción</label>
                        <textarea id="ev-desc" class="form-control mb-3" rows="3"></textarea>
                        <label class="form-label">Fecha y hora *</label>
                        <input id="ev-fecha" type="datetime-local" class="form-control" value="${fecha}">
                    </div>`,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                focusConfirm: false,
                preConfirm: () => {
                    const titulo = document.getElementById('ev-titulo').value.trim();
                    const fechaVal = document.getElementById('ev-fecha').value;
                    if (!titulo || !fechaVal) {
                        Swal.showValidationMessage('Título y fecha son obligatorios');
                        return false;
                    }
                    return {
                        titulo,
                        descripcion: document.getElementById('ev-desc').value,
                        fecha: fechaVal
                    };
                }
            }).then((r) => {
                if (r.isConfirmed) {
                    api('api/calendario/guardar', { method: 'POST', body: JSON.stringify(r.value) })
                        .then(() => {
                            AlertManager.success('Evento creado', 'Se agregó a tu calendario.');
                            calendar.refetchEvents();
                        })
                        .catch((e) => AlertManager.error('Error', e.mensaje || 'No se pudo guardar.'));
                }
            });
        }

        // ---------- Editar / eliminar ----------
        function abrirDetalleEvento(evento) {
            if (typeof Swal === 'undefined') return;

            Swal.fire({
                title: evento.title,
                html: `<div class="text-start">
                        <p class="mb-1"><strong>Descripción:</strong> ${evento.extendedProps.descripcion || 'Sin descripción'}</p>
                        <p class="mb-0"><strong>Fecha:</strong> ${evento.start ? evento.start.toLocaleString('es-VE') : ''}</p>
                       </div>`,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Editar',
                denyButtonText: 'Eliminar',
                cancelButtonText: 'Cerrar',
                reverseButtons: true
            }).then((r) => {
                if (r.isConfirmed) editarEvento(evento);
                else if (r.isDenied) eliminarEvento(evento.id);
            });
        }

        function editarEvento(evento) {
            const fecha = evento.start
                ? new Date(evento.start.getTime() - evento.start.getTimezoneOffset() * 60000).toISOString().slice(0, 16)
                : '';

            Swal.fire({
                title: 'Editar evento',
                html: `
                    <div class="text-start">
                        <label class="form-label">Título *</label>
                        <input id="ev-titulo" class="form-control mb-3" maxlength="100" value="${evento.title.replace(/"/g, '&quot;')}">
                        <label class="form-label">Descripción</label>
                        <textarea id="ev-desc" class="form-control mb-3" rows="3">${evento.extendedProps.descripcion || ''}</textarea>
                        <label class="form-label">Fecha y hora *</label>
                        <input id="ev-fecha" type="datetime-local" class="form-control" value="${fecha}">
                    </div>`,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                focusConfirm: false,
                preConfirm: () => {
                    const titulo = document.getElementById('ev-titulo').value.trim();
                    const fechaVal = document.getElementById('ev-fecha').value;
                    if (!titulo || !fechaVal) {
                        Swal.showValidationMessage('Título y fecha son obligatorios');
                        return false;
                    }
                    return { id_evento: Number(evento.id), titulo, descripcion: document.getElementById('ev-desc').value, fecha: fechaVal };
                }
            }).then((r) => {
                if (r.isConfirmed) {
                    api('api/calendario/actualizar', { method: 'POST', body: JSON.stringify(r.value) })
                        .then(() => {
                            AlertManager.success('Evento actualizado');
                            calendar.refetchEvents();
                        })
                        .catch((e) => AlertManager.error('Error', e.mensaje || 'No se pudo actualizar.'));
                }
            });
        }

        async function eliminarEvento(id) {
            const confirmacion = await AlertManager.confirm('¿Eliminar evento?', 'Esta acción no se puede deshacer', 'Sí, eliminar', 'Cancelar');
            if (!confirmacion.isConfirmed) return;

            try {
                await api('api/calendario/eliminar', { method: 'POST', body: JSON.stringify({ id_evento: Number(id) }) });
                AlertManager.success('Evento eliminado');
                calendar.refetchEvents();
            } catch (e) {
                AlertManager.error('Error', e.mensaje || 'No se pudo eliminar.');
            }
        }
    });
})();
