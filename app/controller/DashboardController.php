<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\helpers\HtmlResponse;
use presupuestos\model\UserModel; 
use presupuestos\model\AnioFiscalModel;
use presupuestos\controller\role\RoleController;
use presupuestos\controller\role\PermisoController;

require __DIR__ . '/../../bootstrap.php';

class DashboardController {
    
    public function index($page = "dashboard") {
        Auth::check();       
        $title = ucfirst($page);
        $title= str_replace("/", " ", $title);

        $centroId = $_SESSION[APP_SESSION_NAME]['centro_id'];
        $subdirector = UserModel::getSubdirector($centroId);
        $anioFiscalActivo = AnioFiscalModel::getPresupuestoActivo($centroId);
        $hayAnioFiscal = !empty($anioFiscalActivo);

        //obtengo los roles para listarlos en la vista
        $roleController = new RoleController();
        $roles = $roleController->list();

        //Obtengo los permisos para listarlos en la lista que los necesite. 
        $permisoController = new PermisoController();
        $permisos = $permisoController->list();

        $views = [
            "dashboard"      => __DIR__ . '/../view/content/dashboard.php',
            "reportes"       => __DIR__ . '/../view/content/reports.php',
            "usuarios"=> __DIR__ . '/../view/content/role/manage.php',
            "page_not_found" => __DIR__ . '/../app/view/errors/404.php',
        ];

        $stylesByView = [
            //"dashboard" => ["css/dashboard/dashboard.css"],
            "reportes"  => ["css/reports/reports.css"],
            "usuarios" => ["css/role/manage.css"]
        ];

        $scriptsByView = [
            "dashboard" => ["js/dashboard/dashboard.js"],
            "usuarios"=> ["js/role/manage.js"]
        ];

        $view    = $views[$page] ?? $views["dashboard"];
        $styles  = $stylesByView[$page] ?? [];
        $scripts = $scriptsByView[$page] ?? [];

        require __DIR__ . '/../view/layout/layout.php';
    }
}
