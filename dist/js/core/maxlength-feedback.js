// Muestra el límite de caracteres y evita excederlo en formularios habilitados.
window.MaxlengthFeedback = (function () {
    'use strict';

    function configurar(form) {
        if (!form || !form.hasAttribute('data-maxlength-feedback')) return;

        form.querySelectorAll('input[maxlength], textarea[maxlength]').forEach((campo) => {
            const maximo = Number(campo.maxLength);
            if (!Number.isInteger(maximo) || maximo < 0) return;

            let error = campo.id ? document.getElementById(campo.id + 'Error') : null;
            if (!error) {
                error = document.createElement('div');
                error.className = 'form-text text-danger';
                campo.insertAdjacentElement('afterend', error);
            }

            campo.addEventListener('input', () => {
                window.setTimeout(() => {
                    if (campo.value.length > maximo) {
                        campo.value = campo.value.slice(0, maximo);
                    }

                    if (campo.value.length === maximo) {
                        error.textContent = `Has alcanzado el límite de ${maximo} caracteres.`;
                        error.dataset.maxlengthWarning = '1';
                    } else if (error.dataset.maxlengthWarning === '1') {
                        error.textContent = '';
                        delete error.dataset.maxlengthWarning;
                    }
                }, 0);
            });
        });
    }

    return { configurar };
})();
