<?php
namespace presupuestos\controller;
use presupuestos\helpers\Auth;

require __DIR__ . '/../../bootstrap.php';

class DashboardController{
    public function index() {
		
		Auth::check();       	
		$title= "Dashboard";
		
		switch($_SESSION[APP_SESSION_NAME]['role']){
			case 1:
				//$styles= ["css/dashboard/instructor.css"];
				$scripts= ["./js/dashboard/dashboard.js"];
				$view= __DIR__ . '/../view/content/dashboard.php';
				require __DIR__ . '/../view/layout/layout.php';
				break;
			case 2:
				$styles= ["css/dashboard/instructor.css"];
				$scripts= ["./js/dashboard/welcome.js"];
				$view= __DIR__ . '/../view/content/dashboard.php';
				break;
			case 3:
				//require __DIR__ . '/../view/layout.php';
				echo "Hola Funcionario";
				break;
			default:
				//require __DIR__ . '/../view/layout.php';
				break;
		}
    }
}
