<?php
namespace presupuestos\controller;
use presupuestos\helpers\Auth;

require __DIR__ . '/../../bootstrap.php';

class DashboardController{
    public function index($page = "dashboard") {
        Auth::check();       
        $title = ucfirst($page);
        

        $views = [
            "dashboard" => __DIR__ . '/../view/content/dashboard.php',
            "reportes"   => __DIR__ . '/../view/content/reports.php',
			"page_not_found"=> __DIR__. '/../app/view/errors/404.php',
        ];

        $stylesByView = [
            "dashboard"     => ["css/dashboard/dashboard.css"],
            "reportes"      => ["css/reports/reports.css"],
            //"page_not_found"=> ["css/errors/404.css"],
        ];

        $scriptsByView = [
            "dashboard"     => ["js/dashboard/dashboard.js"],
            //"reportes"      => ["js/dashboard/reports.js"],
            //"page_not_found"=> ["js/errors/404.js"],
        ];

        $view = $views[$page] ?? $views["dashboard"];
        $styles = $stylesByView[$page] ?? [];
        $scripts = $scriptsByView[$page] ?? [];

        require __DIR__ . '/../view/layout/layout.php';
    }
}