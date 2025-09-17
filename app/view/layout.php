<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/menustyles.css">
</head>
<body>

<!-- Header -->
<header class="header d-flex align-items-center justify-content-between px-4 py-3">
    <!-- Sección izquierda: botón menú, foto, rol y nombre de usuario -->
    <div class="user-section">
        <button class="btn btn-primary btn-menu-toggle me-3" id="toggleMenu" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
        <div class="user-photo">
            <img src="<?= APP_URL ?>assets/img/logoSena.png" alt="Foto de usuario" class="rounded-circle" width="45" height="45">
        </div>
        <div class="user-role">
            <span>Rol</span>
        </div>    
        <div class="user-info d-flex align-items-center">
            <span class="me-2">Usuario</span>
            <i class="fas fa-user-circle fa-lg"></i>
        </div>
    </div>
    
    <!-- Sección derecha: logo SENA y título -->
    <div class="sena-section">
        <div class="logo-sena">
            <img src="<?= APP_URL ?>assets/img/logoSena.png" alt="Logo SENA" height="45">
        </div>
        <div class="titulo">
            <h4>Presupuestos</h4>
        </div>
    </div>
</header>

<!-- dashboard -->
<div class="sidebar" id="sidebarMenu">
    <!-- menú -->
    <div class="accordion accordion-flush" id="menuAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingInicio">
              <a class="accordion-button single-link" href="#">Inicio</a>
            </h2>
        </div>
        <!-- introduccir menu aqui -->
        <?php
        ?>
        <!-- fin del menu por base de datos -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSalir">
              <a class="accordion-button single-link" href="#">Salir</a>
            </h2>
        </div>
    </div>
    <!-- fin del menú -->
</div>

<div class="backdrop-sidebar" id="sidebarBackdrop"></div>

<script type="text/javascript" src="<?= APP_URL ?>js/menuctr.js"></script>
</body>
</html>