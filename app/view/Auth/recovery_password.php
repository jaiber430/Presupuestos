<?php
include "../../conexion.php";
include "../Model/process_forms.php";

$link= conectar();
/*
	**Verifica que el token sea valido. 
*/

	
$dataUser= [
	"email"=> $_GET["email"],
	"token"=> $_GET["token"]
];

$dateNow= date('Y-m-d H:i:s');
$verificate= new ProcessRecovery($dataUser, $link);
$resultVerificate= $verificate->verificateToken($dateNow);

if(! mysqli_num_rows($resultVerificate)){
	header("location: invalid_token.php");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
	<link href="../../css/login.css" rel="stylesheet">
    <!--Css Bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
</head>
<body>
	<header>
		
	</header>
	<main>
		<div class="container mt-5">
			<div class="row justify-content-center">			
				<div class="col-12 col-sm-10 col-md-6 col-lg-4">
					<div class="p-3">
						<div class="text-center mb-5">
							<h1 class="h2 text-sm-center">Recuperar contraseña</h1>
							<p><em><small>Ingrese ya confirme su nueva contraseña</small></em></p>
						</div>

						<form action="" class="form-recovery">								
							<div class="mb-2">
								<label for="password" class="form-label">Contraseña</label>
								<input type="password" class="form-control"  id="password" name="password" placeholder="********" required>
							</div>
							<div class="mb-2">
								<label for="re-password" class="form-label">Confirmar Contraseña</label>
								<input type="password" class="form-control" id="re-password" name="re-password" placeholder="********" required>
							</div>
							<div class="mb-2">
								<div class="alert d-none alert-warning" role="alert">
									<p class="text"></p>
								</div>	
							</div>
							
							<div class="d-grid mb-4 mt-4">
								<button type="submit" class="btn btn-success w-100 btn-lg">Actualizar Contraseña</button>
							</div>
						</form>
						
						<div class="mb-5 text-center">
							<a class="link-success link-opacity-75 link-underline-light" href="/hiperAuto/index.php">Volver al inicio</a>
						</div>
					</div>
				</div>
			</div>			
		</div>
	</main>
	<script>		
		let token = <?= isset($_GET["token"]) ? json_encode($_GET["token"]) : 'null' ?>;
		let email = <?= isset($_GET["email"]) ? json_encode($_GET["email"]) : 'null' ?>;
	</script>
	<script src="../../js/jquery-3.7.1.js"></script>
	<script src="../../js/recovery_password.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	
</body>
</html>