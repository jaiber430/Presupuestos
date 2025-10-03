<?php

namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\helpers\HtmlResponse;
use presupuestos\controller\UserController;
use presupuestos\model\AnioFiscalModel;
use presupuestos\controller\role\RoleController;
use presupuestos\controller\role\PermisoController;

class DashboardController{

    public function index($page = "dashboard"){
        Auth::check();
        $title = ucfirst($page);
        $title = str_replace("/", " ", $title);

        $idCentroIdSession = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
        $subdirector = UserController::getSubdirector($idCentroIdSession);

        //obtengo los roles para listarlos en la vista
        $roleController = new RoleController();
        $roles = $roleController->list();

        //Obtengo todos los usuarios
        $users = UserController::listByCentro($idCentroIdSession);

        //Obtengo el año fiscal activo
        $anioFiscalActivo = AnioFiscalModel::getPresupuestoActivo($idCentroIdSession);
        $hayAnioFiscal = !empty($anioFiscalActivo);

        //Obtengo los permisos para listarlos en la lista que los necesite. 
        $permisoController = new PermisoController();
        $permisos = $permisoController->list();

        //Obtengo todas las semanas
        $semanas = AnioFiscalModel::obtenerSemanasPorCentro($idCentroIdSession);
        // echo "<pre>";
        // var_dump($_SESSION[APP_SESSION_NAME]);
        // exit;
        //Obtengo las semana por centro y qué está activa
        // echo "<pre>";
        // var_dump( $semanas);
        // exit;
        $semanaActiva = AnioFiscalController::getSemanaActiva($semanas);

        $views = [
            "dashboards"      => __DIR__ . '/../view/content/dashboard.php',
            "dashboard"       => __DIR__ . '/../view/content/reports.php',
            "usuarios"        => __DIR__ . '/../view/content/role/manage.php',
            "page_not_found"  => __DIR__ . '/../app/view/errors/404.php',
            "sin-rol"         => __DIR__ . '/../app/view/errors/sin_rol.php',
            "subirArchivo"=> __DIR__ . '/app/view/cargarExcel.php',
        ];

        $userRol = UserController::verifyAccount($_SESSION[APP_SESSION_NAME]['idUsuarioSession']);

        if (empty($userRol)) {
            require_once __DIR__ . '/../view/content/sin_rol.php';
            exit;
        }

        $stylesByView = [
            //"dashboard" => ["css/dashboard/dashboard.css"],
            "reportes"  => ["css/reports/reports.css"],
            "usuarios"  => ["css/role/manage.css"]
        ];

        $scriptsByView = [
            "dashboard" => ["js/dashboard/dashboard.js"],
            "usuarios"  => ["js/role/manage.js"]
        ];

       
        $view    = $views[$page] ?? $views["dashboard"];
        $styles  = $stylesByView[$page] ?? [];
        $scripts = $scriptsByView[$page] ?? [];

        require __DIR__ . '/../view/layout/layout.php';
    }
}
