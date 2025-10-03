<?php
	$host = "localhost";
	$user = "root";
	$pass = "";
	$db = "presupuestosv3";
	$conn = new mysqli($host, $user, $pass, $db);

	if ($conn->connect_error) {	  die("Conexión fallida: " . $conn->connect_error);	}
	if (isset($_FILES['file'])) {
		
	  $fileTmpPath = $_FILES['file']['tmp_name'];
	  $destination = 'uploads/'.$_FILES['file']['name'];
	  move_uploaded_file($fileTmpPath, $destination);
	  $query = "LOAD DATA INFILE '" . realpath($destination) . "'
		SET NAMES 'utf8mb4'
		INTO TABLE cdptemporal
		CHARACTER SET utf8mb4
		FIELDS TERMINATED BY ';' 
		ENCLOSED BY '\"'
		LINES TERMINATED BY '\n'
		IGNORE 1 LINES";
	  if ($conn->query($query) === TRUE) { echo "Archivo cargado exitosamente." else {echo "Error al cargar: " . $conn->error;
	  }
	}

