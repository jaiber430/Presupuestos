<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/menustyles.css">

    <?php 
	if (!empty($styles)) {
		foreach ($styles as $css){
			echo "<link rel='stylesheet' href='$css'>";
		} 
	}	
	?>

    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/yearfiscal/yearfiscal.css">
    <?php if (!empty($pageStyles)) { echo $pageStyles; } ?>
</head>
<body>

<header class="header d-flex align-items-center justify-content-between px-4 py-3">

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
    <div class="accordion1 accordion-flush" id="menuAccordion">
        <div class="accordion-item">
                        <h2 class="accordion-header" id="headingInicio">
                            <a class="accordion-button single-link" href="<?= APP_URL ?>dashboard">Inicio</a>
            </h2>
        </div>

                <div class="accordion-item">
                        <h2 class="accordion-header" id="headingPresupuesto">
                            <a class="accordion-button single-link" href="#" data-bs-toggle="modal" data-bs-target="#modalAnioFiscal">Crear Año Fiscal</a>
                        </h2>
                </div>
        <!-- momentaneo hasta que se haga menu inteligente -->
         <!-- Reportes -->
        <!-- <div class="accordion-item">
                        <h2 class="accordion-header" id="headingReportes">
                            <a class="accordion-button single-link" href="<?= APP_URL ?>reports">Reportes</a>
            </h2>
        </div> -->
        <!-- momentaneo hasta que se haga menu inteligente -->
        
        <!-- introduccir menu aqui -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingPresupuesto">
                <div class='permissions'></div>
            </h2>
        </div>
        

        <!-- fin del menu por base de datos -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSalir">                
                <form action="<?= APP_URL ?>logout" method="POST" style="display:inline;">
                    <button type="submit" class="accordion-button single-link btn-link">Salir</button>
                </form>
            </h2>
        </div>
    </div>
    <!-- fin del menú -->
</div>

<div class="backdrop-sidebar" id="sidebarBackdrop"></div>


<!-- Contenedor principal para el contenido dinámico -->
<div class="page-wrapper">

    <?php 
	require $view;
	if(!empty($scripts)){
        foreach ($scripts as $script){
		echo "<script src='{$script}'></script>";
	    }
    }
	
	?>

</div>

<script type="text/javascript" src="<?= APP_URL ?>js/menuctr.js"></script>
<?php if (!empty($pageScripts)) { echo $pageScripts; } ?>


<!-- Modal Crear Año Fiscal (contenido integrado desde presupuesto.php) -->
<div class="modal fade modal-yearfiscal" id="modalAnioFiscal" tabindex="-1" aria-labelledby="modalAnioFiscalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content yearfiscal-content">
            <div class="modal-header yearfiscal-header">
                <h5 class="modal-title" id="modalAnioFiscalLabel">Crear Año Fiscal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body yearfiscal-body">
                <!-- Contenedor de alertas dinámicas -->
                <div class="yf-alerts" id="yf-alerts" role="alert" aria-live="polite" aria-atomic="true">
                    
                </div>
                <form action="" class="form-Fiscal yearfiscal-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="subdirector" class="form-label">Subdirector</label>
                                <select class="form-select inputFiscal" id="subdirector">
                                    <option selected disabled>Seleccione una opción</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valor_presupuesto" class="form-label">Valor Presupuesto</label>
                                <input type="number" step="0.01" class="form-control inputFiscal" id="valor_presupuesto">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="year_fiscal" class="form-label">Año Fiscal</label>
                                <select class="form-select inputFiscal" id="year_fiscal">
                                    <option selected disabled>Seleccione una opción</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select inputFiscal" id="estado">
                                    <option selected disabled>Seleccione una opción</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control inputFiscal" id="fecha_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                                <input type="date" class="form-control inputFiscal" id="fecha_cierre">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer yearfiscal-footer">
                <button type="button" name="cancel" class="btn btn-secondary yf-btn-cancel" id="cancel-button" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" name="create" class="btn btn-primary yf-btn-create" id="create-button">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Listar los roles -->
<div class="modal fade" id="rolesModal" tabindex="-1" aria-labelledby="rolesModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rolesModalLabel">Roles disponibles</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Listar los permisos -->
<div class="modal fade" id="modalManageRoles" tabindex="-1" aria-labelledby="modalManageRolesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-4">
      
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title fw-bold" id="modalManageRolesLabel">
          <i class="fas fa-user-shield me-2"></i> Gestionar Roles
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <div class="modal-body bg-light">

        <div class="accordion" id="accordionRoles">
         
        </div>      
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i> Cerrar
        </button>
        <button type="button" class="btn btn-primary">
          <i class="fas fa-save me-1"></i> Guardar cambios
        </button>
      </div>      
    </div>
  </div>
</div>

<script>
    const BASE_URL = "<?= APP_URL ?>";
</script>

<script src="./js/dashboard/dashboard.js"></script>
</body>
</html>