// dist/js/core/ModalManager.js
class ModalManager {
    constructor(modalId = 'modalGlobal') {
        this.modalId = modalId;
        this.$modal = $(`#${modalId}`);

        // Elementos del modal
        this.$modalBody = this.$modal.find('.modal-body');
        this.$modalTitle = this.$modal.find('.modal-title');
        this.$modalSubtitle = this.$modal.find('#beneficiarioCodigo'); // Ajusta según tu estructura

        // Estado
        this.isShown = false;

        // Inicializar eventos
        this._initEvents();
    }

    /**
     * Inicializar eventos del modal
     */
    _initEvents() {
        // Limpiar contenido al cerrar
        this.$modal.on('hidden.bs.modal', () => {
            this.$modalBody.html('');
            this.isShown = false;
        });

        this.$modal.on('shown.bs.modal', () => {
            this.isShown = true;
        });
    }

    /**
     * Mostrar modal con spinner de carga
     * @param {string} title - Título del modal
     * @param {string} message - Mensaje opcional
     */
    showLoading(title = 'Cargando...', message = 'Por favor espere') {
        this.setTitle(title);

        const loadingHtml = `
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-muted">${message}</p>
            </div>
        `;

        this.$modalBody.html(loadingHtml);
        this.$modal.modal('show');

        return this;
    }

    /**
     * Mostrar contenido en el modal
     * @param {string|jQuery} content - Contenido HTML o jQuery object
     * @param {boolean} clearExisting - Limpiar contenido existente
     */
    showContent(content, clearExisting = true) {
        if (clearExisting) {
            this.$modalBody.empty();
        }

        if (typeof content === 'string') {
            this.$modalBody.html(content);
        } else if (content instanceof jQuery) {
            this.$modalBody.append(content);
        } else {
            console.error('Tipo de contenido no soportado para ModalManager.showContent()');
            return this;
        }

        // Re-inicializar componentes Bootstrap dentro del modal
        this._initModalComponents();

        return this;
    }

    /**
     * Mostrar error en el modal
     * @param {string} message - Mensaje de error
     * @param {string} title - Título opcional
     */
    showError(message, title = 'Error') {
        this.setTitle(title);

        const errorHtml = `
            <div class="alert alert-danger m-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fa-2x"></i>
                    <div>
                        <h6 class="alert-heading mb-1">¡Algo salió mal!</h6>
                        <p class="mb-0">${message}</p>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        `;

        this.$modalBody.html(errorHtml);
        this.$modal.modal('show');

        return this;
    }

    /**
     * Mostrar modal de confirmación
     * @param {string} title - Título
     * @param {string} message - Mensaje
     * @param {Object} options - Opciones adicionales
     * @returns {Promise<boolean>}
     */
    async showConfirmation(title, message, options = {}) {
        return new Promise((resolve) => {
            this.setTitle(title);

            const {
                confirmText = 'Confirmar',
                cancelText = 'Cancelar',
                confirmClass = 'btn-primary',
                cancelClass = 'btn-outline-secondary'
            } = options;

            const confirmationHtml = `
                <div class="p-4">
                    <div class="alert alert-warning border-0">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-question-circle fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-0">${message}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn ${cancelClass} me-2" id="modalCancelBtn">
                            <i class="fas fa-times me-1"></i> ${cancelText}
                        </button>
                        <button type="button" class="btn ${confirmClass}" id="modalConfirmBtn">
                            <i class="fas fa-check me-1"></i> ${confirmText}
                        </button>
                    </div>
                </div>
            `;

            this.$modalBody.html(confirmationHtml);
            this.$modal.modal('show');

            // Manejar clics
            $('#modalConfirmBtn').on('click', () => {
                this.hide();
                resolve(true);
            });

            $('#modalCancelBtn').on('click', () => {
                this.hide();
                resolve(false);
            });

            // Cerrar con ESC o click fuera
            this.$modal.off('hidden.bs.modal.confirmation').on('hidden.bs.modal.confirmation', () => {
                resolve(false);
            });
        });
    }

    /**
     * Establecer título del modal
     * @param {string} title - Título
     */
    setTitle(title) {
        this.$modalTitle.text(title);
        return this;
    }

    /**
     * Establecer subtítulo del modal
     * @param {string} subtitle - Subtítulo
     */
    setSubtitle(subtitle) {
        if (this.$modalSubtitle.length) {
            this.$modalSubtitle.text(subtitle).show();
        }
        return this;
    }

    /**
     * Ocultar subtítulo
     */
    hideSubtitle() {
        if (this.$modalSubtitle.length) {
            this.$modalSubtitle.hide();
        }
        return this;
    }

    /**
     * Inicializar componentes Bootstrap dentro del modal
     */
    _initModalComponents() {
        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]', this.$modalBody).tooltip();

        // Inicializar popovers
        $('[data-bs-toggle="popover"]', this.$modalBody).popover();

        // Inicializar selects con Select2 (si existen)
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2', this.$modalBody).each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#modalGlobal')
                    });
                }
            });
        }
    }

    /**
     * Obtener el body del modal
     * @returns {jQuery}
     */
    getBody() {
        return this.$modalBody;
    }

    /**
     * Mostrar modal
     */
    show() {
        this.$modal.modal('show');
        return this;
    }

    /**
     * Ocultar modal
     */
    hide() {
        this.$modal.modal('hide');
        return this;
    }

    /**
     * Verificar si el modal está visible
     * @returns {boolean}
     */
    isVisible() {
        return this.$modal.hasClass('show') && this.isShown;
    }

    /**
     * Establecer tamaño del modal
     * @param {string} size - 'sm', 'lg', 'xl', o '' para default
     */
    setSize(size = '') {
        const sizes = ['sm', 'lg', 'xl'];
        const dialog = this.$modal.find('.modal-dialog');

        // Remover clases de tamaño existentes
        sizes.forEach(s => dialog.removeClass(`modal-${s}`));

        // Agregar nueva clase si es válida
        if (size && sizes.includes(size)) {
            dialog.addClass(`modal-${size}`);
        }

        return this;
    }
}

// Si estás usando módulos ES6:
// export default ModalManager;

// Si estás usando el sistema global:
if (typeof window !== 'undefined') {
    window.ModalManager = ModalManager;
}