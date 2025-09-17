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
				echo "Hola Admin";
				break;
			case 2:
				echo "Hola Abogado";
				break;
			case 3:
				echo "Hola Funcionario";
				break;
			default:
				echo "Para dónde crees que vas";
				break;
		}
    }
}
