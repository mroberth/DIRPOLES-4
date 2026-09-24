document.addEventListener('DOMContentLoaded', function () {
    try {
        const currentPath = window.location.pathname; // Ruta actual sin query string
        const items = document.querySelectorAll('#accordionSidebar .collapse-item');

        let bestMatch = null;
        let maxLength = 0;

        // Normaliza una ruta: deja solo el path, sin query string, hash ni slash final.
        const normalizar = (ruta) => {
            let linkPath = ruta;
            if (linkPath.startsWith('http')) {
                try {
                    linkPath = new URL(linkPath).pathname;
                } catch (e) {
                    return '';
                }
            }
            return linkPath.split('?')[0].split('#')[0].replace(/\/$/, '');
        };

        const normalizedCurrent = currentPath.replace(/\/$/, '');

        items.forEach((a) => {
            const href = a.getAttribute('href');
            if (!href) return;

            // Rutas candidatas: el href más los alias declarados en data-activo.
            // Esto permite que, por ejemplo, "psicologia/consultar" mantenga
            // activo el item de "psicologia/crear".
            const candidatas = [href].concat(
                (a.dataset.activo || '').split(/\s+/).filter(Boolean)
            );

            candidatas.forEach((candidata) => {
                const normalizedLink = normalizar(candidata);

                // El path actual debe contener el path del link. Se guarda el
                // match más largo para evitar colisiones entre rutas parecidas.
                if (
                    normalizedLink !== '/' &&
                    normalizedLink.length > 1 &&
                    normalizedCurrent.includes(normalizedLink) &&
                    normalizedLink.length > maxLength
                ) {
                    maxLength = normalizedLink.length;
                    bestMatch = a;
                }
            });
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
