<!-- archivo: error_token_expirado.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Token inválido o expirado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card border-danger text-center" style="max-width: 500px;">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">¡Enlace inválido o expirado!</h4>
        </div>
        <div class="card-body">
            <p class="card-text">El enlace de recuperación ya no es válido o ha caducado.</p>
            <a href="../../index.php" class="btn btn-outline-danger">
                Solicita uno nuevo
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
