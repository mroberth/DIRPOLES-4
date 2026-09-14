// dist/js/core/AlertManager.js
class AlertManager {
    static config = {
        timer: 3000,
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            popup: 'custom-swal-popup'
        }
    };

    static success(title, text = '', redirectUrl = null) {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            ...this.config
        }).then((result) => {
            if (redirectUrl && (result.isConfirmed || result.isDismissed)) {
                window.location.href = redirectUrl;
            }
            return result;
        });
    }

    static error(title, text = '') {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            customClass: this.config.customClass
        });
    }

    static warning(title, text = '') {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            customClass: this.config.customClass
        });
    }

    static info(title, text = '') {
        return Swal.fire({
            icon: 'info',
            title: title,
            text: text,
            ...this.config,
            showConfirmButton: true
        });
    }

    static confirm(title, text = '', confirmText = 'Sí', cancelText = 'No') {
        return Swal.fire({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            customClass: this.config.customClass
        });
    }

    static loading(title = 'Procesando...', text = 'Espere un momento por favor') {
        return Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    }

    static close() {
        Swal.close();
    }
}

// ==================== INTERCEPTORES GLOBALES DE PETICIONES (fetch / jQuery) ====================

// 1. Interceptor de fetch
(function() {
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        try {
            const response = await originalFetch(...args);
            if (response.status === 429) {
                let errorMsg = 'Demasiadas peticiones. Por favor, intente de nuevo.';
                try {
                    const clone = response.clone();
                    const contentType = clone.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        const data = await clone.json();
                        if (data && data.mensaje) {
                            errorMsg = data.mensaje;
                        }
                    }
                } catch (e) {
                    console.error('Error al parsear el JSON de 429:', e);
                }

                if (typeof AlertManager !== 'undefined') {
                    AlertManager.error('Límite de peticiones excedido', errorMsg);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Límite de peticiones excedido',
                        text: errorMsg,
                        confirmButtonText: 'Entendido'
                    });
                }
            }
            return response;
        } catch (error) {
            throw error;
        }
    };
})();

// 2. Interceptor de jQuery AJAX (si existe jQuery)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
        $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 429) {
                let errorMsg = 'Demasiadas peticiones. Por favor, intente de nuevo.';
                try {
                    const data = JSON.parse(jqXHR.responseText);
                    if (data && data.mensaje) {
                        errorMsg = data.mensaje;
                    }
                } catch (e) {
                    // No es JSON, usar default
                }

                if (typeof AlertManager !== 'undefined') {
                    AlertManager.error('Límite de peticiones excedido', errorMsg);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Límite de peticiones excedido',
                        text: errorMsg,
                        confirmButtonText: 'Entendido'
                    });
                }
            }
        });
    }
});