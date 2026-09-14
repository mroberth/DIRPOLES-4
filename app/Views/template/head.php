<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" href="<?= BASE_URL ?>dist/img/dirpoles.ico" type="image/x-icon">
  <title>DIRPOLES 4 - <?= $titulo ?></title>

  <!-- Pre-carga de recursos críticos (Fuentes, CSS, Imagen LCP) -->
  <link rel="preload" href="<?= BASE_URL ?>plugins/fonts/inter/files/inter-400.ttf" as="font" type="font/ttf" crossorigin>
  <link rel="preload" href="<?= BASE_URL ?>plugins/fonts/inter/files/inter-600.ttf" as="font" type="font/ttf" crossorigin>
  <link rel="preload" href="<?= BASE_URL ?>plugins/bootstrap/dist/css/bootstrap.min.css" as="style">
  
  <?php if (isset($titulo) && $titulo === "Login"): ?>
    <link rel="preload" href="<?= BASE_URL ?>dist/img/fondo.jpg" as="image">
    <link rel="preload" href="<?= BASE_URL ?>dist/css/etc/login.css" as="style">
    <!-- Estilos Críticos en línea para evitar FOUC y bloqueo -->
    <style>
      :root{--dirpoles-blue:#004a99;--dirpoles-blue-dark:#003366;}
      body,html{height:100%;margin:0;font-family:'Inter',sans-serif;}
      .login-page{display:flex;min-height:100vh;}
      .login-left{background:#fff;z-index:10;}
      .login-right{flex:1;background-color:#f0f2f5;display:none;}
      @media(min-width:768px){.login-right{display:block;}}
      .bg-image{height:100%;background-size:cover;background-position:center;}
    </style>
  <?php endif; ?>

  <link href="<?= BASE_URL ?>plugins/fonts/inter/inter.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>plugins/bootstrap/dist/css/bootstrap.min.css">
  
  <!-- Solo cargar librerías pesadas si no estamos en el Login -->
  <?php if (!isset($titulo) || $titulo !== "Login"): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/DataTables/css/datatables.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>dist/css/select-2-bootstrap-5/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>dist/css/etc/select2-validacion.css">
    <link href="<?= BASE_URL ?>dist/css/dashboard/sb-admin-2.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>dist/css/etc/sidebar.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>dist/css/dashboard/dashboard.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>plugins/driver.js/driver.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>dist/css/driver.js-personalizado/temaDriverPersonalizado.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/sweetalert2/dist/sweetalert2.min.css">
    <!-- Módulo: Notificaciones (campana del topbar) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>dist/css/modulos/notificaciones.css">
  <?php else: ?>
    <!-- En Login: Carga Asíncrona de estilos no críticos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/fontawesome/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= BASE_URL ?>plugins/sweetalert2/dist/sweetalert2.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= BASE_URL ?>dist/css/etc/login.css">
  <?php endif; ?>
</head>