<?php
// include "../Model/db_queries.php";
// include "../../conexion.php";
// include "../Model/process_forms.php";

// $link= conectar();
// mysqli_set_charset($link, "utf8mb4");

// $documents= new GetQuery("tipo_documento");
// $resultDocument= $documents->search($link);

// if(isset($_GET["email"]) && isset($_GET["token"])){
// 	$dateNow= date('Y-m-d H:i:s');
// 	$verificateUser= new ProcessRecovery($_GET, $link);
// 	$is_verificate= $verificateUser->verificateUser();
	
// 	if(mysqli_num_rows($is_verificate)> 0){
// 		$verificateToken= $verificateUser-> verificateToken($dateNow);
// 		if(mysqli_num_rows($verificateToken)){
// 			$updateToken = "
// 				UPDATE tokens_recuperacion tr
// 				JOIN user u ON tr.usuario_id = u.id
// 				SET tr.utilizado = 1
// 				WHERE u.email = '{$_GET['email']}' 
// 				AND tr.token = '{$_GET['token']}'
// 			";		
// 			mysqli_query($link, $updateToken);

// 			$updateUser= "
// 				UPDATE user u
// 				SET verificado= 1
// 				WHERE u.email= '{$_GET['email']}'
// 			";
// 			mysqli_query($link, $updateUser);
			
// 			$message= "Email verificado correctamente";
// 		}else{
// 			header("location: page_not_found.php");
// 		}
// 	}
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@100..700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
	<link href="./css/auth/login.css" rel="stylesheet">
</head>

<body>
	<header>
		
	</header>
	
	<main>
		<div class="alerts">
			<div class="container-alert">
				<p class="alert-text"><?= $message ?? ""?></p>
			</div>
		</div>
		<div class="login">			
			<div class="form-container">
			    <img src="" alt="logo" class="logo">
				<form action="" class="form-login">
					<label for="email" class="label">Correo Electrónico</label>
					<input type="text" id="email" placeholder="Usuario" required class="input input-username" value="<?= $_GET['email'] ?? ''?>">
					
					<label for="password"  class="label">Contraseña</label>
					<input type="password"  id="password" placeholder="*********" required class="input input-password">
					
					<button type="submit" class="primary-button login-button">Iniciar Sesión</button>
					
					<a href="#" class="remember_password">Olvidé mi contraseña</a>
				</form>
				
				<button type="button" class="secondary-button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
					Registrarse
				</button>
			</div>
		</div>		
	</main>
	
	<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	  <div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content">
		  <div class="modal-header">
			<h1 class="modal-title fs-5" id="staticBackdropLabel">Crea una cuenta</h1>
			<button type="button" class="btn-close btns-close" data-bs-dismiss="modal" aria-label="Close"></button>
		  </div>
		  <div class="modal-body">
			<div class="row">
				<div class="col-6">
				</div>
				<div class="col-6">
				</div>
			</div>
			<form action="" class="form-registro">
				<div class="mb-3">
					<label for="names" class="form-label">Nombres</label>
					<input type="text" class="form-control inputRegister" id="names">
				</div>
				<div class="mb-3">
					<label for="last-names" class="form-label">Apellidos</label>
					<input type="text" class="form-control inputRegister" id="last-names">
				</div>
				<div class="mb-3">
					<label for="type-document" class="form-label">Tipo de identificación</label>
					<select class="form-select inputRegister" id="type-document"> 
						<option value="">seleccione</option>
						
					</select>
				</div>
				<div class="mb-3">
					<label for="id_number" class="form-label">Número de identificación</label>
					<input type="text" class="form-control inputRegister" id="id-number">
				</div>				
				<div class="mb-3">
					<label for="number-phone" class="form-label">Celular</label>
					<input type="number" class="form-control inputRegister" id="number-phone">
 				</div>
				<div class="mb-3">
					<label for="address" class="form-label">Dirección</label>
					<input type="text" class="form-control inputRegister" id="address">
				</div>
				<div class="mb-3">
					<label for="email" class="form-label">Correo Eléctronico</label>
					<input type="email" class="form-control inputRegister" id="email-user">
				</div>
				<div>
					<label for="password" class="form-label">Contraseña</label>
					<input type="password" class="form-control inputRegister" id="password-user">
				</div>
				<div>
					<label for="re-password" class="form-label">Confirmar contraseña</label>
					<input type="password" class="form-control inputRegister" id="re-password">
				</div>
			</form>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary btns-close" data-bs-dismiss="modal" >Cerrar</button>
			<button type="button" class="btn btn-primary signup-button" id="signup-button">Registrar</button>
		  </div>
		</div>
	  </div>
	</div>	

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="js/auth/auth.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
	
</body>

</html>