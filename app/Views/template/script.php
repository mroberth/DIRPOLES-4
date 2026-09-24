<!-- jQuery primero (importante para Bootstrap) -->
<script src="<?= BASE_URL ?>plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap con defer para no bloquear el renderizado -->
<script src="<?= BASE_URL ?>plugins/bootstrap/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- URL base del proyecto disponible para todo el JS -->
<script>window.BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Carga selectiva de librerías pesadas -->
<?php if (!isset($titulo) || $titulo !== "Login"): ?>
    <!-- FullCalendar CORE -->
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/core/index.global.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/core/locales/es.global.min.js" defer></script>

    <!-- FullCalendar PLUGINS -->
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/bootstrap5/index.global.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/daygrid/index.global.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/interaction/index.global.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/timegrid/index.global.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/@fullcalendar/list/index.global.min.js" defer></script>

    <!-- Otros plugins pesados -->
    <script src="<?= BASE_URL ?>plugins/select2/js/select2.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/DataTables/js/datatables.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/moment/moment.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/jspdf/jspdf.umd.min.js" defer></script>
    <script src="<?= BASE_URL ?>plugins/html2canvas/html2canvas.min.js" defer></script>

    <!-- Scripts de la interfaz interna -->
    <script src="<?= BASE_URL ?>dist/js/dashboard/sb-admin5.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/dashboard/sidebar-active.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/dashboard/sidebar-scroll-enhancement.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/core/select-2-init.js" defer></script>

    <!-- Helpers de formato y de tablas (DataTables) compartidos por los módulos -->
    <script src="<?= BASE_URL ?>dist/js/core/formato.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/core/datatable.js" defer></script>

    <!-- Driver.js -->
    <script src="<?= BASE_URL ?>plugins/driver.js/driver.js.iife.js" defer></script>

    <!-- JWT Auto-Renewal -->
    <script src="<?= BASE_URL ?>dist/js/jwt-refresh.js" defer></script>

    <!-- Módulo: Notificaciones (helper API + campana SSE) -->
    <script src="<?= BASE_URL ?>dist/js/core/apiFetch.js" defer></script>
    <script src="<?= BASE_URL ?>dist/js/modulos/notificaciones/control.js" defer></script>
<?php endif; ?>

<!-- Plugins esenciales para el Login y alertas -->
<script src="<?= BASE_URL ?>plugins/fontawesome/js/all.min.js" defer></script>
<script src="<?= BASE_URL ?>plugins/sweetalert2/dist/sweetalert2.min.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/core/AlertManager.js" defer></script>
<script src="<?= BASE_URL ?>dist/js/core/logout.js" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }

        // Inicializar JWT Auto-Renewal (excepto en login)
        if (typeof JWTRefresh !== 'undefined' && typeof Swal !== 'undefined') {
            var jwtExp = Math.floor(Date.now() / 1000) + parseInt('<?= (int) env('JWT_EXPIRATION', '3600') ?>') || 3600;
            JWTRefresh.init(jwtExp);
        }
    });
</script>