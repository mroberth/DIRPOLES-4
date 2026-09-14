// dist/js/modulos/configuracion/backup.js
// ------------------------------------------------------------------
// Panel de respaldos: confirmación antes de descargar + tour (Driver.js).
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    function iniciarTour() {
        const factory = window.driver && window.driver.js
            && (window.driver.js.driver || window.driver.js);
        if (typeof factory !== 'function') {
            console.warn('Driver.js no está disponible.');
            return;
        }

        factory({
            showProgress: true,
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: 'Finalizar',
            popoverClass: 'mi-popover',
            steps: [
                {
                    element: '#card-respaldo-negocio',
                    popover: {
                        title: 'BD de Negocio',
                        description: 'Descarga estructura y datos de la base operativa (beneficiarios, citas, inventario...).',
                        side: 'top', align: 'start',
                    },
                },
                {
                    element: '#card-respaldo-seguridad',
                    popover: {
                        title: 'BD de Seguridad',
                        description: 'Descarga usuarios, permisos, tokens y la bitácora de auditoría.',
                        side: 'top', align: 'start',
                    },
                },
            ],
        }).drive();
    }

    const btnAyuda = document.getElementById('btn-ayuda');
    if (btnAyuda) btnAyuda.addEventListener('click', iniciarTour);

    document.querySelectorAll('.btn-descargar-respaldo').forEach((btn) => {
        btn.addEventListener('click', async function (ev) {
            ev.preventDefault();

            const seguridad = btn.dataset.tipo === 'seguridad';
            const nombre = seguridad ? 'la base de datos de seguridad' : 'la base de datos de negocio';

            const confirmacion = await AlertManager.confirm(
                'Descargar respaldo',
                `Se generará y descargará un archivo .sql de ${nombre}. ¿Deseas continuar?`,
                'Sí, descargar',
                'Cancelar'
            );
            if (!confirmacion.isConfirmed) return;

            // Navegación que dispara la descarga (Content-Disposition).
            window.location.href = btn.getAttribute('href');
        });
    });
});
