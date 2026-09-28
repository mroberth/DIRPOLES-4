<?php

use App\Core\Router;

// ---------- Páginas (puerta HTML) ----------

Router::get('trabajo-social/crear', function () {
    load_controller('trabajoSocialController.php');
    showCrearTrabajoSocial();
});

Router::get('trabajo-social/consultar', function () {
    load_controller('trabajoSocialController.php');
    showConsultarTrabajoSocial();
});

// ---------- APIs (puerta JSON) ----------

Router::get('api/trabajo-social/catalogos', function () {
    load_controller('trabajoSocialController.php');
    apiCatalogosTrabajoSocial();
});

Router::post('api/trabajo-social/becas/crear', function () {
    load_controller('trabajoSocialController.php');
    apiCrearBeca();
});

Router::post('api/trabajo-social/exoneraciones/crear', function () {
    load_controller('trabajoSocialController.php');
    apiCrearExoneracion();
});

Router::post('api/trabajo-social/fames/crear', function () {
    load_controller('trabajoSocialController.php');
    apiCrearFames();
});

Router::post('api/trabajo-social/embarazadas/crear', function () {
    load_controller('trabajoSocialController.php');
    apiCrearEmbarazada();
});

Router::get('api/trabajo-social/exoneraciones/pendientes', function () {
    load_controller('trabajoSocialController.php');
    apiExoneracionesPendientes();
});

Router::get('api/trabajo-social/listar', function () {
    load_controller('trabajoSocialController.php');
    apiListarTrabajoSocial();
});

Router::post('api/trabajo-social/actualizar', function () {
    load_controller('trabajoSocialController.php');
    apiActualizarTrabajoSocial();
});

Router::post('api/trabajo-social/eliminar', function () {
    load_controller('trabajoSocialController.php');
    apiEliminarTrabajoSocial();
});

Router::post('api/trabajo-social/estudio/generar', function () {
    load_controller('trabajoSocialController.php');
    apiGenerarEstudioTrabajoSocial();
});

Router::get('api/trabajo-social/stats', function () {
    load_controller('trabajoSocialController.php');
    apiStatsTrabajoSocial();
});
