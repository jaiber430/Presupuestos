<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;


require __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');
class ReportsController {
    public function index() {
        Auth::check();
        //HtmlResponse::toast("Hubo un error cargando los datos", "danger", 7000);

        if(empty($_POST['cdp']) || empty($_POST['rp']) || empty($_POST['pagos']) || empty($_POST['week'])) {
            $alerta = [
                "tipo"   => "simple",
                "titulo" => "Ocurrió un error inesperado, Joven",
                "texto"  => "Debes seleccionarasdfasdf todos los archivos antes de guardar",
                "icono"  => "error"
            ];

            header('Content-Type: application/json');
            echo json_encode($alerta);
            exit;
        }

        $alerta = [
            "tipo"   => "simple",
            "titulo" => "¡Éxito!",
            "texto"  => "El reporte de la semana fue subido correctamente",
            "icono"  => "success"
        ];

        
        echo json_encode($alerta);
        exit;


    }
}
