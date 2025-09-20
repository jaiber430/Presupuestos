<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;


require __DIR__ . '/../../bootstrap.php';

class ReportsController {
    public function index() {
        Auth::check();


        HtmlResponse::toast("Hubo un error cargando los datos", "danger", 7000);


        $view = __DIR__ . '/../view/content/reports.php';
        require __DIR__ . '/../view/layout/layout.php';
    }
}
