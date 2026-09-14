/**
 * JWT Auto-Renewal & Expiry Alert System
 * 
 * Este script se encarga de:
 * 1. Renovar el JWT automáticamente antes de que expire (5 min antes)
 * 2. Mostrar una alerta al usuario cuando queden 5 minutos
 * 3. Redirigir al login si el JWT expira y no se pudo renovar
 */

(function() {
    'use strict';

    // Configuración
    const JWT_EXPIRY_BUFFER = 5 * 60; // 5 minutos antes de expirar (en segundos)
    const CHECK_INTERVAL = 60; // Verificar cada 60 segundos

    // URL base del proyecto expuesta por el template (window.BASE_URL).
    // Usar rutas ABSOLUTAS evita que en páginas anidadas (ej. modulos/ver/5)
    // el navegador resuelva 'refresh_token' contra la ruta equivocada.
    function baseUrl() {
        return (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '/';
    }

    // Estado
    let jwtExpTimestamp = null; // Timestamp Unix de expiración del JWT
    let renewalTimer = null;
    let alertShown = false;

    /**
     * Inicializa el sistema de auto-renewal
     * Llamar desde la vista principal después del login
     * @param {number} jwtExp - Timestamp Unix de expiración del JWT
     */
    window.JWTRefresh = {
        init: function(jwtExp) {
            jwtExpTimestamp = jwtExp;
            alertShown = false;

            // Iniciar verificación periódica
            startPeriodicCheck();

            // Verificar inmediatamente al inicializar
            checkAndRenew();

            console.log('[JWT] Auto-renewal inicializado. Expira:', new Date(jwtExp * 1000).toLocaleString());
        },

        /**
         * Fuerza una renovación inmediata del JWT
         */
        forceRenewal: function() {
            renewJWT();
        },

        /**
         * Obtiene el tiempo restante hasta la expiración (en segundos)
         */
        getTimeRemaining: function() {
            if (!jwtExpTimestamp) return 0;
            return Math.max(0, jwtExpTimestamp - Math.floor(Date.now() / 1000));
        }
    };

    /**
     * Verifica periódicamente si es necesario renovar
     */
    function startPeriodicCheck() {
        if (renewalTimer) clearInterval(renewalTimer);
        renewalTimer = setInterval(function() {
            checkAndRenew();
        }, CHECK_INTERVAL * 1000);
    }

    /**
     * Verifica si el JWT está por expirar y renueva si es necesario
     */
    function checkAndRenew() {
        if (!jwtExpTimestamp) return;

        const now = Math.floor(Date.now() / 1000);
        const timeRemaining = jwtExpTimestamp - now;

        // Si quedan menos de 5 minutos, renovar
        if (timeRemaining <= JWT_EXPIRY_BUFFER && timeRemaining > 0 && !alertShown) {
            showExpiryAlert(timeRemaining);
        }

        // Si quedan menos de 2 minutos o ya expiró, renovar inmediatamente
        if (timeRemaining <= 120) {
            renewJWT();
        }
    }

    /**
     * Renueva el JWT haciendo una petición al endpoint refresh_token
     */
    function renewJWT() {
        console.log('[JWT] Intentando renovar JWT...');

        fetch(baseUrl() + 'refresh_token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            // Contrato JSON único: {exito:true, datos} | {exito:false, error:{codigo, estado, mensaje}}
            if (data && data.exito === true) {
                // Actualizar timestamp de expiración
                var expSeconds = parseInt((data.datos && data.datos.jwt_exp) || 0) || 3600; // Fallback a 1 hora
                jwtExpTimestamp = Math.floor(Date.now() / 1000) + expSeconds;
                alertShown = false;

                console.log('[JWT] Token renovado exitosamente. Expira en:', expSeconds, 'segundos');
                console.log('[JWT] Nueva expiración:', new Date(jwtExpTimestamp * 1000).toLocaleString());

                // Ocultar alerta si estaba visible
                hideExpiryAlert();
            } else {
                console.error('[JWT] Error al renovar:', (data && data.error && data.error.mensaje) || 'desconocido');
                redirectToLogin('Tu sesión ha expirado. Por favor, inicia sesión novamente.');
            }
        })
        .catch(function(error) {
            console.error('[JWT] Error de conexión:', error);
            // No redirigir inmediatamente por errores de red, puede ser temporal
        });
    }

    /**
     * Muestra una alerta al usuario indicando que la sesión está por expirar
     */
    function showExpiryAlert(secondsRemaining) {
        if (alertShown) return;
        alertShown = true;

        const minutes = Math.ceil(secondsRemaining / 60);

        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                icon: 'warning',
                title: '¡Tu sesión esta por expirar!',
                html: 'Tu sesión expirará en <strong id="countdown">' + minutes + '</strong> minuto(s).<br><br>Se renovará automáticamente, pero si prefieres puedes:',
                showDenyButton: true,
                confirmButtonText: '🔄 Renovar ahora',
                denyButtonText: '🚪 Cerrar sesión',
                timer: 10000, // Se cierra solo después de 10 segundos
                timerProgressBar: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() {
                    // Contador regresivo en la alerta
                    let remaining = secondsRemaining;
                    const countdownEl = document.getElementById('countdown');
                    if (countdownEl) {
                        const interval = setInterval(function() {
                            remaining--;
                            if (remaining <= 0) {
                                clearInterval(interval);
                                return;
                            }
                            if (countdownEl) {
                                countdownEl.textContent = Math.ceil(remaining / 60);
                            }
                        }, 1000);
                    }
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Renovar manualmente
                    renewJWT();
                } else if (result.isDenied) {
                    // Cerrar sesión
                    window.location.href = baseUrl() + 'logout';
                }
            });
        } else {
            // Fallback si no hay SweetAlert
            const message = 'Tu sesión expirará en ' + minutes + ' minuto(s). Se renovará automáticamente.';
            if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                new Notification('DIRPOLES - Sesión por expirar', {
                    body: message,
                    icon: 'dist/img/logo.png'
                });
            }
        }
    }

    /**
     * Oculta la alerta de expiración
     */
    function hideExpiryAlert() {
        if (typeof Swal !== 'undefined' && Swal.close) {
            Swal.close();
        }
    }

    /**
     * Redirige al login con un mensaje de error
     */
    function redirectToLogin(message) {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                icon: 'error',
                title: 'Sesión expirada',
                text: message,
                confirmButtonText: 'Ir al login',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function() {
                window.location.href = baseUrl() + 'login';
            });
        } else {
            alert(message);
            window.location.href = baseUrl() + 'login';
        }
    }

})();
