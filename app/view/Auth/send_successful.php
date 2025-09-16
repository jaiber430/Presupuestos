<?php

if(!isset($_GET["email"])){
	header("location: page_not_found.php");
}
$email= $_GET["email"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <!--Css Bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
	
	<style>
		*{
			font-family: 'Inter', sans-serif
		}
	</style>
</head>
<body>
    <main>
		<div class="container mt-5">
			<div class="row justify-content-center">			
				<div class="col-12 col-sm-10 col-md-6 col-lg-4">
					<div class="p-3">
						<div class="text-center mt-5 mb-5">
							<h1 class="h2 text-sm-center fw-bold">Correo Enviado</h1>
							<p><em><small>Revisa tu bandeja de entrada para ver las instrucciones</small></em></p>
						</div>
						<div class="text-center">
							<i class="bi bi-envelope-check text-success" style="font-size: 5rem;"></i>
						</div>

						
						<div class="mt-5 mb-4">
							<a class="btn btn-success btn-lg w-100" href="../../index.php" role="button">Loguearse</a>
						</div>	
												
						<div class="mb-5 text-center">
							<span>¿No recibió el correo?</span> 
							<a class="link-success link-opacity-75 link-underline-light" id="resend-email" href="#">Reenviar</a>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>
	<script>
		let email= <?= json_encode($email)?>;
	</script>
	<script src="../../js/jquery-3.7.1.js"></script>
	<script src="../../js/reesend_email.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>