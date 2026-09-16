<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Botón de menú (móvil): abre/cierra el sidebar. Lo maneja sb-admin5.js -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3" type="button" aria-label="Abrir menú">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ms-auto">
        <!-- Nav Item - Notificaciones (módulo de notificaciones, campana con SSE) -->
        <li class="nav-item dropdown no-arrow mx-1 position-relative">
            <a class="nav-link dropdown-toggle p-0" href="#" id="notificationDropdown" role="button"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-lg text-gray-500"></i>
                <span id="notificationCounter" class="translate-middle badge rounded-pill bg-danger" style="display: none;">0</span>
            </a>
            <div id="notificationMenu" class="dropdown-menu dropdown-menu-end shadow animated--grow-in p-0"
                aria-labelledby="notificationDropdown" style="width: 360px; right: 0; left: auto;">
                <div class="dropdown-header d-flex justify-content-between align-items-center bg-primary text-white py-3 px-4 rounded-top">
                    <h6 class="mb-0 text-white" id="notificationHeader">
                        <i class="fas fa-bell me-2"></i>Notificaciones
                    </h6>
                    <span class="badge bg-light text-primary" id="notificationCounterBadge">0</span>
                </div>
                <div id="notificationScrollWrapper" style="max-height: 400px; overflow-y: auto;" class="py-2">
                    <div id="notificationItems" class="px-2">
                        <!-- Las notificaciones se cargan dinámicamente desde dist/js/modulos/notificaciones/control.js -->
                        <div class="text-center py-5 px-3">
                            <div class="mb-3">
                                <i class="far fa-bell-slash fa-3x text-gray-400"></i>
                            </div>
                            <p class="text-muted mb-0">Cargando...</p>
                        </div>
                    </div>
                </div>
                <div class="dropdown-footer d-flex justify-content-between py-2 border-top px-3">
                    <a href="#" id="markAllRead" class="text-decoration-none small text-primary">
                        <i class="fas fa-check-circle me-1"></i>Marcar leídas
                    </a>
                    <a href="#" id="deleteAllNotifications" class="text-decoration-none small text-danger">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar todas
                    </a>
                </div>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="me-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']) ?></span>
                <img class="img-profile rounded-circle"
                    src="<?= BASE_URL. '/dist/img/empleado.png'; ?>">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                aria-labelledby="userDropdown" style="min-width: 240px;">
                <a class="dropdown-item py-2" href="<?= BASE_URL ?>perfil/ver">
                    <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                    Mi Perfil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item py-2 js-logout" href="<?= BASE_URL ?>logout" data-logout>
                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                    Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>
</nav>

