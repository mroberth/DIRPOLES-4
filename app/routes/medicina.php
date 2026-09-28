<?php

use App\Core\Router;

// ---------- Páginas (puerta HTML) ----------

Router::get('medicina/crear', function () {
    load_controller('medicinaController.php');
    showCrearMedicina();
});

Router::get('medicina/consultar', function () {
    load_controller('medicinaController.php');
    showConsultarMedicina();
});

// ---------- Documentos (tercera puerta PDF: neither HTML nor JSON) ----------

Router::get('medicina/constancia/{id}', function () {
    load_controller('medicinaController.php');
    generarConstanciaMedicina();
});

Router::get('medicina/referencia/{id}', function () {
    load_controller('medicinaController.php');
    generarReferenciaMedicina();
});

Router::get('medicina/recipe/{id}', function () {
    load_controller('medicinaController.php');
    generarRecipeMedicina();
});

// ---------- APIs (puerta JSON) ----------

Router::get('api/medicina/listar', function () {
    load_controller('medicinaController.php');
    apiListarMedicina();
});

Router::get('api/medicina/obtener/{id}', function () {
    load_controller('medicinaController.php');
    apiObtenerMedicina();
});

Router::get('api/medicina/catalogos', function () {
    load_controller('medicinaController.php');
    apiCatalogosMedicina();
});

Router::get('api/medicina/stats', function () {
    load_controller('medicinaController.php');
    apiStatsMedicina();
});

Router::post('api/medicina/crear', function () {
    load_controller('medicinaController.php');
    apiCrearMedicina();
});

Router::post('api/medicina/actualizar', function () {
    load_controller('medicinaController.php');
    apiActualizarMedicina();
});

Router::post('api/medicina/eliminar', function () {
    load_controller('medicinaController.php');
    apiEliminarMedicina();
});
