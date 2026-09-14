/**
 * Sidebar Scroll Enhancement
 * Mejora la experiencia de scroll en el sidebar para pantallas pequeñas
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('accordionSidebar') || document.querySelector('.sidebar');
    if (!sidebar) return;

    // Función para detectar si el scroll es necesario
    function checkScrollNeeded() {
        const viewportHeight = window.innerHeight;
        const sidebarContentHeight = sidebar.scrollHeight;
        
        // Agregar o quitar clase basada en la necesidad de scroll
        if (sidebarContentHeight > viewportHeight) {
            sidebar.classList.add('sidebar-scroll-needed');
        } else {
            sidebar.classList.remove('sidebar-scroll-needed');
        }
    }

    // Verificar al cargar la página
    checkScrollNeeded();

    // Verificar cuando cambie el tamaño de la ventana
    window.addEventListener('resize', checkScrollNeeded);

    // Observar cambios en el contenido del sidebar
    const observer = new MutationObserver(function(mutations) {
        checkScrollNeeded();
    });

    observer.observe(sidebar, {
        childList: true,
        subtree: true,
        attributes: false,
        characterData: true
    });

    // Mejorar experiencia de scroll en móviles
    if ('ontouchstart' in window) {
        let startY = 0;
        let scrollTop = 0;

        sidebar.addEventListener('touchstart', function(e) {
            startY = e.touches[0].pageY;
            scrollTop = sidebar.scrollTop;
        }, { passive: true });

        sidebar.addEventListener('touchmove', function(e) {
            const y = e.touches[0].pageY;
            const isTop = scrollTop === 0;
            const isBottom = scrollTop + sidebar.clientHeight >= sidebar.scrollHeight;
            const isUp = y > startY;
            const isDown = y < startY;

            // Prevenir scroll del body cuando el sidebar puede hacer scroll
            if ((isTop && isUp) || (isBottom && isDown)) {
                e.preventDefault();
            }
        }, { passive: false });
    }

    // Auto-scroll al elemento activo si está fuera de vista
    function scrollToActive() {
        const activeItem = sidebar.querySelector('.nav-item.active .nav-link, .collapse-item.active');
        if (activeItem) {
            const itemRect = activeItem.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            
            // Si el elemento activo está parcialmente fuera de vista
            if (itemRect.bottom > sidebarRect.bottom || itemRect.top < sidebarRect.top) {
                activeItem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    }

    // Scroll al activo cuando se carga la página
    setTimeout(scrollToActive, 300);

    // Scroll al activo cuando se abre un collapse
    sidebar.addEventListener('shown.bs.collapse', function(e) {
        const activeItem = e.target.querySelector('.collapse-item.active');
        if (activeItem) {
            setTimeout(() => {
                activeItem.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }, 200);
        }
    });

    // Mantener posición del scroll cuando se cambia el tamaño de ventana
    let lastScrollTop = 0;
    window.addEventListener('resize', function() {
        if (sidebar.scrollTop > 0) {
            lastScrollTop = sidebar.scrollTop;
        }
    });

    // Restaurar scroll position después de resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (lastScrollTop > 0) {
                sidebar.scrollTop = lastScrollTop;
            }
        }, 250);
    });
});
