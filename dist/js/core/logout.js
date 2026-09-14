// dist/js/core/logout.js
document.addEventListener('DOMContentLoaded', function () {
    // selector flexible: enlaces o botones con data-logout attribute
    const logoutSelectors = 'a[data-logout], button[data-logout], .js-logout';
    const logoutElements = Array.from(document.querySelectorAll(logoutSelectors));

    logoutElements.forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            // texto opcional en atributo data-logout-message
            const message = el.getAttribute('data-logout-message') || '¿Estás seguro que deseas cerrar sesión?';
            AlertManager.confirm('Cerrar sesión', message, 'Sí', 'Cancelar')
                .then(result => {
                    if (result.isConfirmed) {
                        // Si el enlace tiene href, redirigir a esa URL; si no, usar ruta logout
                        const href = el.getAttribute('href') || (window.BASE_URL ? window.BASE_URL + 'logout' : '/DIRPOLES_4/logout');
                        // Redirigir (GET) para que tu ruta logout ejecute la función del controlador
                        window.location.href = href;
                    }
                });
        });
    });
});
