<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\helpers\HtmlResponse;
use presupuestos\model\UserModel; 
use presupuestos\model\AnioFiscalModel;

require __DIR__ . '/../../bootstrap.php';

class DashboardController {
    public function index($page = "dashboard") {
        Auth::check();       
        $title = ucfirst($page);

        $centroId = $_SESSION[APP_SESSION_NAME]['centro_id'];
        $subdirector = UserModel::getSubdirector($centroId);
        $anioFiscalActivo = AnioFiscalModel::getPresupuestoActivo($centroId);
        $hayAnioFiscal = !empty($anioFiscalActivo);


        if ($page === "reportes") {
            HtmlResponse::toast("Bienvenido al módulo de reportes 📊", "info", 5000);
        }

        $views = [
            "dashboard"      => __DIR__ . '/../view/content/dashboard.php',
            "reportes"       => __DIR__ . '/../view/content/reports.php',
            "page_not_found" => __DIR__ . '/../app/view/errors/404.php',
        ];

        $stylesByView = [
            //"dashboard" => ["css/dashboard/dashboard.css"],
            "reportes"  => ["css/reports/reports.css"],
        ];

        $scriptsByView = [
            "dashboard" => ["js/dashboard/dashboard.js"],
        ];

        $view    = $views[$page] ?? $views["dashboard"];
        $styles  = $stylesByView[$page] ?? [];
        $scripts = $scriptsByView[$page] ?? [];

        require __DIR__ . '/../view/layout/layout.php';
    }
}
