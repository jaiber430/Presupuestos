<?php
namespace presupuestos\helpers;
require __DIR__ . '/../../bootstrap.php';

class Auth{   
    public static function check(){               
        if (!isset($_SESSION[APP_SESSION_NAME])) {
            $_SESSION["message"]= "Debes Iniciar Sesión";
            header("Location: " . APP_URL);
            exit;
        }
    }
}