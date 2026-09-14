document.addEventListener('DOMContentLoaded', function () {
    try {
        const currentPath = window.location.pathname; // Ruta actual sin query string
        const items = document.querySelectorAll('#accordionSidebar .collapse-item');

        let bestMatch = null;
        let maxLength = 0;

        items.forEach(a => {
            const href = a.getAttribute('href');
            if (!href) return;

            // Extraer solo el path del href (sin dominio, query string ni hash)
            let linkPath = href;

            // Si el href es una URL completa, extraer solo el path
            if (href.startsWith('http')) {
                try {
                    const url = new URL(href);
                    linkPath = url.pathname;
                } catch (e) {
                    console.error('Error parsing URL:', href);
                    return;
                }
            }

            // Eliminar query string y hash si existen
            linkPath = linkPath.split('?')[0].split('#')[0];

            // Normalizar ambas rutas (eliminar trailing slashes)
            const normalizedCurrent = currentPath.replace(/\/$/, '');
            const normalizedLink = linkPath.replace(/\/$/, '');

            // Comparación: el path actual debe contener el path del link
            // Esto permite que "diagnostico_trabajo_social_consultar" active "diagnostico_trabajo_social"
            if (normalizedCurrent.includes(normalizedLink) && normalizedLink !== '/' && normalizedLink.length > 1) {
                // Almacenar el match más largo para evitar colisiones 
                // entre "consultar_inventario" y "consultar_inventario_mob"
                if (normalizedLink.length > maxLength) {
                    maxLength = normalizedLink.length;
                    bestMatch = a;
                }
            }
        });

        if (bestMatch) {
            // Marcar activo
            bestMatch.classList.add('active');

            // Expandir el collapse padre si existe
            const collapse = bestMatch.closest('.collapse');
            if (collapse) {
                // Usar la API de Bootstrap 5 si está disponible
                if (window.bootstrap && bootstrap.Collapse) {
                    let inst = bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false });
                    inst.show();
                } else {
                    collapse.classList.add('show');
                }
            }

            // Opcional: marcar el nav-link padre como active
            const parentNavLink = bestMatch.closest('.nav-item')?.querySelector('.nav-link');
            if (parentNavLink) parentNavLink.classList.add('active');
        }
    } catch (e) {
        console.error('sidebar-active error', e);
    }
});
