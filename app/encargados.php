<?php /* 	opciones con modal 		https://getbootstrap.esdocu.com/docs/5.1/components/modal/    */
include_once('../../include/config.php');
date_default_timezone_set('America/Bogota');
include_once('../../include/parametros_index.php');
// header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: text/html; charset='.$charset);
session_name($session_name);
session_start();
if (isset($_SESSION['id_Usu'])){	
$fecha = date("Y-m-d"); 
$fecha_Banner = date("Y-m-d"); ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>RUPS-PERMISOS</title>
		<?php include('head.php');	?>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" integrity="sha512-mR/b5Y7FRsKqrYZou7uysnOdCIJib/7r5QeJMFvLNHNhtye3xJp1TdJVPLtetkukFn227nKpXD9OjUc09lx97Q==" crossorigin="anonymous	referrerpolicy="no-referrer" />
		<script src="../../herramientas/js/encargados01.js" type="text/javascript" ></script>
	</head>
	<body class="hold-transition sidebar-mini layout-fixed">
		<?php include('cabeceraMenu.php');?>
			<main>
				<div class="container"> 		<!--  permisos   -->
					<div class="modal fade" id="crearPermisos">
						<div class="modal-dialog">
							<div class="modal-content">						
								<!-- inicio cabecera del diálogo -->
								<div class="modal-header">
									<h6 class="modal-title">NUEVO ENCARGADO</h6>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>
							</div>

							<!-- inicio cuerpo del diálogo -->
							<div class="modal-body">
								<div style="margin-bottom:10px" class="input-group">
									<div class="row mt-1">
										<div class="col">
											<div class="col">
												<input type="hidden" name="id_rol" id="id_rol" title='id Rol' >
												<h6 class="modal-title">Nombre</h6>
												<input type="text" class="form-control" placeholder="Nombre" id="nombreRol" name="nombreRol" title="Nombre del rol" >
											</div>
										</div>
									</div>							
								</div>													
								<div id='divRespRol' class="col-sm-12 text-center" class='col-20' >
								</div>
							</div>					
								
							<!-- inicio pie del diálogo -->
							<div class="modal-footer">
								<button type='button'  id='btn_actualizar'  title='Boton actualizar datos del formulario' <?php echo $var_class_button_formulario; ?> >Actualizar</button>
								<button type='button'  id='btn_salir' title='Boton cerar formulario modal' data-dismiss="modal"   <?php echo $var_class_button_popup;  ?> >Cerrar</button>
							</div>
						</div>
					</div>
					
					<!--  editar   -->
					<div class="modal fade" id="editarRolM">
						<div class="modal-dialog">
							<div class="modal-content">						
								<!-- inicio cabecera del diálogo -->
								<div class="modal-header">
									<h6 class="modal-title">EDICION DE ROL</h6>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>


								<!-- inicio cuerpo del diálogo -->
								<div class="modal-body">
									<div class="row mt-1">
										<h6>Nombre</h6>
										<div class="col" >
											<input type="hidden" name="id_rolUpdate" id="id_rolUpdate" title='id permiso' >
											<input type="text" id="nombre_rolUpdt" name="nombre_rolUpdt" title="Nombre del permiso" class="form-control" placeholder="Nombre">						
										</div>
									</div>
									<div class="row mt-1">
										<div id='divRespRol' class="col-sm-12 text-center" class='col-20' ></div>
									</div>
								</div>							
					
								
								<!-- inicio pie del diálogo -->
								<div class="modal-footer">
									<button type='button'  id='btn_actualizar' name='btn_actualizar'   title='Boton actualizar datos del formulario' <?php echo $var_class_button_formulario; ?> >Actualizar</button>
									<button type='button'  id='btn_salir' name='btn_salir' title='Boton cerar formulario modal' data-dismiss="modal"   <?php echo $var_class_button_popup;  ?> >Cerrar</button>
								</div>	
								<!-- cierre pie del diálogo -->
							</div>
						</div>
					</div>				
					<!--    INICIO NUEVA REGISTRO    -->
					<div class="modal fade" id="ppNuevoRegistro" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">							
								<!-- cabecera del diálogo -->
								<div class="modal-header">
									<h6 class="modal-title">NUEVO ENCARGADO</h6>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>								
								<!-- cuerpo del diálogo -->								
								<div class="modal-body">								
									<div class="row mt-1">
										<div class="col"><h6 class="modal-title">Fecha</h6>
											<input type="date" class="form-control" placeholder="fecha hoy" id="fecha" name="fecha" value='<?php echo $fecha; ?>' readonly>
										</div>
										<div class="col"><h6 class="modal-title"></h6>
										</div>											
									</div>
									<div class="row mt-1">
										<div class="col"><h6 class="modal-title">Nombre</h6>
											<input type="text" class="form-control" placeholder="nombre" id="nombreRolNew" name="nombreRolNew" title="Nombre permiso"  >
										</div>									
									</div>								
									<div class="row mt-1">
										<div class='row' class="col-sm-12 text-center">
											<div id="divRespRegistRol" class="col-sm-12 text-center" class="col-20" ></div>
										</div>
									</div>									
								</div>
								
								<!-- pie del diálogo -->
								<div class="modal-footer">
									<button type="button" id="btn_guardar" name="btn_guardar" <?php echo $var_class_button_formulario; ?> >Guardar</button>
									<button type="button" name="btnCerrarGuardar" id="btnCerrarGuardar" <?php echo $var_class_button_popup ;  ?> data-dismiss="modal">Cerrar</button>
								</div>								
							</div>
						</div>
					</div>
					<!--    CIERRE NUEVO REGISTRO-->
					
					<!--    INICIO ELIMINAR -->
					<div class="modal fade" id="modalEncargadoDel"  >
						<div class="modal-dialog">
							<div class="modal-content">
							
								<!-- cabecera del diálogo -->
								<div class="modal-header">
									<h4 class="modal-title">ADMINISTRAR ROL</h4>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>

								<!-- cuerpo del diálogo -->
								<div class="modal-body">
									<div class="row mt-1">
										<h6 class="modal-title">¿Esta seguro de eliminar el registro?</h6>
										<div class='input-group' class='col-20' >										
											<input type="hidden" name="idRolDell" 	id="idRolDell" 		class="form-control" title='id permiso eliminar'     >
											<input type="text" name="nombreRolDel" 	id="nombreRolDel" 	class="form-control" title='nombre permiso eliminar' readonly/>
										</div>
									</div>									
									<div class="row mt-1" >
										<div class='row' class="col-sm-12 text-center">
											<div id='divRespDel' class="col-sm-12 text-center" class='col-20' ></div>
										</div>
									</div>
								</div>
								
								<!-- pie del diálogo -->
								<div class="modal-footer">
									<button type="button" id="btnEliminar" 	name="btnEliminar" <?php echo $var_class_button_formulario; ?> >Eliminar</button>
									<button type="button" id="btnCerrar" 	name="btnCerrar" <?php echo $var_class_button_popup ;  ?> data-dismiss="modal">Cerrar</button>
								</div>
							</div>
						</div>
					</div>						
					<!--    CIERRE ELIMINAR -->	


					<!--    INICIO PERMISOS -->
					<div class="modal fade" id="modalPermisos"  >
						<div class="modal-dialog modal-dialog-scrollable" role="document">
							<div class="modal-content">	
							
								<!-- cabecera del diálogo -->
								<div class="modal-header">
									<h4 class="modal-title">ASIGNAR PERMISOS</h4>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>

								<!-- cuerpo del diálogo -->
								<div class="modal-body">
									<div class="row mt-1" >
										<div class="col">										
											<div class="col"><h6 class="modal-title">ROL</h6>
												<input type="hidden" name="idRolPermiso" id="idRolPermiso" class="form-control" title='id asignar permiso '    >
												<input type="text" name="nombreRolPermiso" id="nombreRolPermiso" class="form-control" title='asignar permiso'  readonly>
												<br>
											</div>	
										</div>
									</div>
									<div class="row mt-1" >
										<div class="col">
											<div id="menuControl">										
											</div>
										</div>
									</div>
								</div>
																
								<!-- pie del diálogo -->
								<div class="modal-footer">
									<button type="button" id="btnCerrar" 	name="btnCerrar" <?php echo $var_class_button_popup ;  ?> data-dismiss="modal">Cerrar</button>
								</div>
							</div>
						</div>
					</div>						
					<!--    CIERRE PERMISOS -->	
					
					<!--    ASIGNAR PERMISOS -->	
					<div class="modal fade" id="editarPermisos" tabindex="-1"  aria-hidden="true">
						<div class="modal-dialog modal-dialog-scrollable" role="document">
							<div class="modal-content">		
								<div class="modal-header">
									<h6 class="modal-title">ASIGNAR PERMISOS</h6>
									<button type="button" class="close" data-dismiss="modal">X</button>
								</div>
								<!-- inicio cuerpo del diálogo -->
								<div class="modal-body">
									<div class="row mt-1" >
										<div class="col">										
											<div class="col"><h6 class="modal-title">MENU</h6>
												<input type="hidden" name="idMenuPermiso" id="idMenuPermiso" class="form-control" title='id asignar permiso '    >
												<input type="text" name="nombreMenuPermiso" id="nombreMenuPermiso" class="form-control" title='asignar permiso'  readonly>
												<br>
											</div>	
										</div>
									</div>
									<div class='row lg-16' >										
										<div id="menuCtrlSubMenu">										
										</div>
									</div>
								</div>
								<!-- inicio pie del diálogo -->
								<div class="modal-footer">
									<button type='button'  id='btnSalir' name='btnSalir' title='Boton cerar formulario modal' data-dismiss="modal"  <?php echo $var_class_button_popup;  ?> >Cerrar</button>
								</div>
							</div>
						</div>
					</div>					
                </div>
				
				<div class="card card-success">
					<div class="card-header">
						<h3 class="card-title"><b>FORMULARIO ENCARGADOS</b></h3>
					</div>
					<?php include('controlPanel.php');?>
					<!-- inicio tabla --->
					<div class="table-responsive">
						<table id="example"  data-order='[[ 2, "asc" ]]' data-page-length='10'  class="table table-sm table-striped table-hover table-bordered" >
							<thead>
								<tr>
									<th scope='col'>Nombre</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					<!-- cierre tabla --->
				</div>	
			</main>
			<?php include('pieMenu.php'); ?>
			<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
			<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js" integrity="sha512-FHZVRMUW9FsXobt+ONiix6Z0tIkxvQfxtCSirkKc5Sb4TKHmqq1dZa8DphF0XqKb3ldLu/wgMa8mT6uXiLlRlw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
			<!-- Bootstrap Switch -->
			<script src="../../herramientas/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
	</body>
</html>
 <?php  } else {	header("Location: ../../index.php");	}  ?>
