// ==========================================================================
// dist/js/modulos/notificaciones/control.js
// Módulo de NOTIFICACIONES (campana del topbar) reconstruido para el
// esqueleto DIRPOLES-4:
//
//   - SSE (Server-Sent Events) en tiempo real: el servidor emite el evento
//     'nueva-notificacion' y aquí se muestra el toast + se actualiza el badge.
//   - Reanuda por ID: guarda en localStorage el último id recibido y se lo
//     pasa al servidor (?ultimoId=) para no perder notificaciones al recargar.
//   - Fallback: si el SSE se cae, hace polling del contador cada 2 minutos.
//   - API bajo api/notificaciones/* con apiFetch() y el contrato Respuesta.
//   - Los errores se distinguen por error.codigo (nunca por mensaje).
// ==========================================================================
(function () {
    'use strict';

    if (typeof BASE_URL === 'undefined' || typeof $ === 'undefined') {
        return; // solo corre en páginas autenticadas con el template
    }

    // ---------------- Configuración ----------------
    const CONFIG = {
        SSE: {
            RECONNECT_DELAY: 3000,   // reintento de conexión tras caída
            POLLING_INTERVAL: 120000, // fallback si el SSE está caído
            MAX_PROCESADAS: 100,      // tope del set anti-duplicados
        },
    };

    const ESTADO = {
        eventSource: null,
        sseConectado: false,
        ultimoId: parseInt(localStorage.getItem('dirpoles_ultimoNotifId') || '0', 10) || 0,
        procesadas: new Set(),
        dropdownVisible: false,
        descargando: false,
    };

    // ---------------- Mapas de iconos y colores por tipo ----------------
    const ICONOS = {
        empleado: 'fas fa-user',
        beneficiario: 'fas fa-person',
        sistema: 'fas fa-cog',
        alerta: 'fas fa-exclamation-triangle',
        diagnostico: 'fas fa-stethoscope',
        referencia: 'fas fa-users',
        jornada: 'fas fa-calendar-alt',
        inventario: 'fas fa-boxes',
        exito: 'fas fa-check-circle',
        horario: 'fas fa-clock',
        error: 'fas fa-times-circle',
        default: 'fas fa-bell',
    };

    const COLORES = {
        empleado: 'bg-primary',
        usuario: 'bg-info',
        sistema: 'bg-secondary',
        alerta: 'bg-warning',
        diagnostico: 'bg-success',
        referencia: 'bg-success',
        jornada: 'bg-success',
        exito: 'bg-success',
        error: 'bg-danger',
        default: 'bg-primary',
    };

    // ---------------- Utilidades ----------------
    function escapar(texto) {
        const div = document.createElement('div');
        div.textContent = texto == null ? '' : String(texto);
        return div.innerHTML;
    }

    function tiempoAgo(minutos) {
        const m = parseInt(minutos, 10) || 0;
        if (m < 1) return 'hace unos momentos';
        if (m < 60) return `hace ${m} minuto${m === 1 ? '' : 's'}`;
        if (m < 1440) {
            const h = Math.floor(m / 60);
            return `hace ${h} hora${h === 1 ? '' : 's'}`;
        }
        const d = Math.floor(m / 1440);
        return `hace ${d} día${d === 1 ? '' : 's'}`;
    }

    function nombreEmisor(notif) {
        if (notif.nombre_empleado === null || notif.nombre_empleado === undefined) return 'Sistema';
        return notif.nombre_empleado || 'Remitente desconocido';
    }

    function enlace(notif) {
        return notif.url ? BASE_URL + notif.url : '#';
    }

    // ---------------- Badge / contador ----------------
    function actualizarBadge(conteo) {
        const n = Math.max(0, parseInt(conteo, 10) || 0);
        const badge = $('#notificationCounter');
        const badgeDrop = $('#notificationCounterBadge');

        badge.text(n).toggle(n > 0);
        if (badgeDrop.length) badgeDrop.text(n);

        $('#notificationHeader').text(`${n} Notificación${n !== 1 ? 'es' : ''}`);

        if (n > 0 && !ESTADO.dropdownVisible) {
            badge.addClass('badge-pulse');
            setTimeout(() => badge.removeClass('badge-pulse'), 3000);
        }
    }

    // ---------------- Render del item ----------------
    function renderItem(notif) {
        const icono = ICONOS[notif.tipo] || ICONOS.default;
        const color = COLORES[notif.tipo] || COLORES.default;
        const noLeida = parseInt(notif.leido, 10) === 0;

        return `
            <div class="notification-item ${noLeida ? 'unread border-start border-primary border-3' : 'read'}"
                 data-id="${escapar(notif.id)}">
                <div class="d-flex align-items-start p-3 hover-bg-gray-100 rounded position-relative">
                    <div class="flex-shrink-0 me-3">
                        <div class="${color} text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 40px; height: 40px;">
                            <i class="${icono} fa-sm"></i>
                        </div>
                    </div>

                    <div class="flex-grow-1 me-2" style="min-width: 0;">
                        <a href="${enlace(notif)}" class="text-decoration-none text-reset stretched-link">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 text-truncate ${noLeida ? 'fw-bold' : 'fw-normal'}"
                                    style="max-width: 220px;">
                                    ${escapar(notif.titulo)}
                                </h6>
                                <small class="text-muted ms-2 flex-shrink-0">
                                    ${tiempoAgo(notif.time_ago)}
                                </small>
                            </div>
                            <p class="text-gray-600 mb-0 small text-truncate">
                                De: ${escapar(nombreEmisor(notif))}
                            </p>
                        </a>
                    </div>

                    <div class="flex-shrink-0">
                        <button class="btn btn-sm btn-outline-danger btn-delete-notification"
                                data-id="${escapar(notif.id)}"
                                title="Eliminar notificación"
                                style="z-index: 5; position: relative;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function htmlVacio() {
        return `
            <div class="text-center py-5 px-3">
                <div class="mb-3">
                    <i class="far fa-bell-slash fa-3x text-gray-400"></i>
                </div>
                <p class="text-muted mb-1">No hay notificaciones</p>
                <small class="text-gray-500">Te notificaremos cuando haya novedades</small>
            </div>
        `;
    }

    // ---------------- API (contrato Respuesta vía apiFetch) ----------------
    async function cargarNotificaciones() {
        try {
            const datos = await apiFetch(BASE_URL + 'api/notificaciones/listar');
            const notificaciones = datos.notifications || [];

            // Registrar los IDs ya conocidos y avanzar ultimoId del SSE.
            ESTADO.procesadas = new Set(notificaciones.map((n) => n.id));
            if (notificaciones.length) {
                ESTADO.ultimoId = Math.max(...notificaciones.map((n) => n.id));
                localStorage.setItem('dirpoles_ultimoNotifId', String(ESTADO.ultimoId));
            }

            actualizarBadge(datos.unread_count || 0);

            const contenedor = $('#notificationItems').empty();
            if (!notificaciones.length) {
                contenedor.html(htmlVacio());
                return;
            }
            notificaciones.forEach((n) => contenedor.append(renderItem(n)));
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
        }
    }

    // ---------------- SSE (tiempo real) ----------------
    function conectarSSE() {
        if (ESTADO.descargando) return;

        desconectarSSE();

        const url = BASE_URL + 'sse/notificaciones?ultimoId=' + ESTADO.ultimoId + '&_=' + Date.now();
        ESTADO.eventSource = new EventSource(url);

        ESTADO.eventSource.onopen = () => {
            ESTADO.sseConectado = true;
        };

        ESTADO.eventSource.onerror = () => {
            ESTADO.sseConectado = false;
            // CRÍTICO: cerrar SIEMPRE el EventSource roto antes de reintentar.
            // Si se deja abierto, el navegador TAMBIÉN auto-reconecta por su
            // cuenta y se multiplican las conexiones paralelas (colapso visto
            // en producción: una nueva conexión cada 3 segundos).
            if (!ESTADO.descargando) {
                desconectarSSE();
                setTimeout(conectarSSE, CONFIG.SSE.RECONNECT_DELAY);
            }
        };

        ESTADO.eventSource.addEventListener('nueva-notificacion', (evento) => {
            try {
                const notif = JSON.parse(evento.data);

                if (ESTADO.procesadas.has(notif.id)) return;
                ESTADO.procesadas.add(notif.id);
                if (ESTADO.procesadas.size > CONFIG.SSE.MAX_PROCESADAS) {
                    ESTADO.procesadas = new Set([...ESTADO.procesadas].slice(-50));
                }

                if (notif.id > ESTADO.ultimoId) {
                    ESTADO.ultimoId = notif.id;
                    localStorage.setItem('dirpoles_ultimoNotifId', String(notif.id));
                }

                procesarNueva(notif);
            } catch (error) {
                console.error('Error procesando notificación SSE:', error);
            }
        });
    }

    function desconectarSSE() {
        if (ESTADO.eventSource) {
            ESTADO.eventSource.close();
            ESTADO.eventSource = null;
            ESTADO.sseConectado = false;
        }
    }

    function procesarNueva(notif) {
        mostrarToast(notif);

        const actual = parseInt($('#notificationCounter').text()) || 0;
        actualizarBadge(actual + 1);

        if (ESTADO.dropdownVisible) {
            const contenedor = $('#notificationItems');
            if (contenedor.find('.text-center').length) {
                contenedor.empty();
            }
            contenedor.prepend(renderItem(notif));
        }
    }

    function mostrarToast(notif) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        Toast.fire({
            icon: 'info',
            title: notif.titulo,
            text: 'De: ' + nombreEmisor(notif),
        });
    }

    // ---------------- Acciones de la bandeja ----------------
    async function marcarLeida(id, item) {
        try {
            await apiFetch(BASE_URL + 'api/notificaciones/marcar_leida', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id: id }),
            });

            $(item)
                .removeClass('unread border-start border-primary border-3')
                .addClass('read');
            $(item).find('h6').removeClass('fw-bold');

            const actual = parseInt($('#notificationCounter').text()) || 0;
            actualizarBadge(actual - 1);
        } catch (error) {
            console.error('Error al marcar como leída:', error);
        }
    }

    async function eliminarNotificacion(id, boton) {
        try {
            const result = await AlertManager.confirm(
                '¿Eliminar notificación?',
                'Esta acción no se puede deshacer',
                'Sí, eliminar',
                'Cancelar'
            );
            if (!result.isConfirmed) return;

            await apiFetch(BASE_URL + 'api/notificaciones/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id: id }),
            });

            $(boton).closest('.notification-item').remove();
            const actual = parseInt($('#notificationCounter').text()) || 0;
            actualizarBadge(actual - 1);
            AlertManager.success('¡Eliminada!', 'La notificación ha sido eliminada.');
        } catch (error) {
            console.error('Error eliminando notificación:', error);
            AlertManager.error('Error', 'No se pudo eliminar la notificación');
        }
    }

    async function marcarTodasLeidas() {
        try {
            const result = await AlertManager.confirm(
                '¿Marcar todas como leídas?',
                'Se marcarán todas las notificaciones como leídas',
                'Sí, marcar',
                'Cancelar'
            );
            if (!result.isConfirmed) return;

            const datos = await apiFetch(BASE_URL + 'api/notificaciones/marcar_todas_leidas', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
            });

            const mensaje = datos.filas_afectadas === 0
                ? 'No había notificaciones pendientes por leer'
                : 'Todas las notificaciones fueron marcadas como leídas';
            AlertManager.success('¡Hecho!', mensaje);

            cargarNotificaciones();
        } catch (error) {
            console.error('Error marcando todas como leídas:', error);
            AlertManager.error('Error', 'No se pudo completar la operación');
        }
    }

    async function eliminarTodas() {
        try {
            const result = await AlertManager.confirm(
                '¿Eliminar todas las notificaciones?',
                'Esta acción no se puede deshacer. Se eliminarán todas tus notificaciones.',
                'Sí, eliminar todas',
                'Cancelar'
            );
            if (!result.isConfirmed) return;

            await apiFetch(BASE_URL + 'api/notificaciones/eliminar_todas', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
            });

            actualizarBadge(0);
            AlertManager.success('¡Hecho!', 'Todas las notificaciones han sido eliminadas');
            cargarNotificaciones();
        } catch (error) {
            console.error('Error eliminando todas las notificaciones:', error);
            AlertManager.error('Error', 'No se pudo completar la operación');
        }
    }

    // ---------------- Eventos del dropdown ----------------
    function inicializar() {
        // Navegador sin soporte SSE: nos quedamos solo con el polling.
        if (window.EventSource) {
            conectarSSE();
        } else {
            console.warn('Tu navegador no soporta SSE; se usará polling.');
        }

        // Carga inicial de la bandeja (diferida para no bloquear la página).
        setTimeout(cargarNotificaciones, 1000);

        // Fallback: si el SSE está caído, poll del listado.
        window.setInterval(() => {
            if (!ESTADO.sseConectado && !ESTADO.dropdownVisible) {
                cargarNotificaciones();
            }
        }, CONFIG.SSE.POLLING_INTERVAL);

        // Abrir/cerrar el dropdown.
        $('#notificationDropdown').on('click', function (e) {
            e.stopPropagation();
            ESTADO.dropdownVisible = !ESTADO.dropdownVisible;
            $('#notificationMenu')
                .toggleClass('show')
                .css('display', ESTADO.dropdownVisible ? 'block' : 'none');

            if (ESTADO.dropdownVisible) {
                cargarNotificaciones();
            }
        });

        // Cerrar al hacer clic fuera.
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#notificationDropdown, #notificationMenu').length) {
                $('#notificationMenu').removeClass('show').hide();
                ESTADO.dropdownVisible = false;
            }
        });

        // Eliminar una notificación.
        $(document).on('click', '.btn-delete-notification', function (e) {
            e.preventDefault();
            e.stopPropagation();
            eliminarNotificacion($(this).data('id'), $(this));
        });

        // Marcar todas como leídas.
        $(document).on('click', '#markAllRead', function (e) {
            e.preventDefault();
            marcarTodasLeidas();
        });

        // Eliminar todas.
        $(document).on('click', '#deleteAllNotifications', function (e) {
            e.preventDefault();
            eliminarTodas();
        });

        // Clic en un item: marcar leída y navegar al enlace.
        $(document).on('click', '.notification-item', function (e) {
            const id = $(this).data('id');
            const noLeida = $(this).hasClass('unread');

            if (noLeida) {
                marcarLeida(id, $(this));
            }

            if (!$(e.target).closest('.btn-delete-notification').length) {
                const link = $(this).find('a.stretched-link').attr('href');
                if (link && link !== '#') {
                    e.preventDefault();
                    window.location.href = link;
                }
            }
        });
    }

    // Cerrar el SSE al salir de la página (evita reconexiones fantasma).
    window.addEventListener('beforeunload', () => {
        ESTADO.descargando = true;
        desconectarSSE();
    });

    $(function () {
        inicializar();
    });
})();