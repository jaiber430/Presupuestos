<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $title ?></title>
		<meta name="description" content="Sistema de gestión de presupuestos">
	</head>
	<body>  AQUI...
		<form id="uploadForm" enctype="multipart/form-data">
		  <input type="file" name="file" id="file" accept=".csv" required/>
		  <button type="submit">Subir archivo</button>
		</form>
		<script>
		  $('#uploadForm').on('submit', function(e) {
			e.preventDefault();
			var formData = new FormData(this);
			$.ajax({
			  url: 'upload.php',
			  type: 'POST',
			  data: formData,
			  contentType: false,
			  processData: false,
			  success: function(response) {
				alert(response);
			  }
			});
		  });
		</script>
	</body>
</html>