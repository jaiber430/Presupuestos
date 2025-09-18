<?php
namespace presupuestos\controller;

use presupuestos\model\MenuModel;
use presupuestos\helpers\ResponseHelper;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require __DIR__ . '/../../bootstrap.php';
class MenuController
{
    public function getByRole()
    {
        $userRol = $_SESSION[APP_SESSION_NAME]['role'];
        if (!$userRol) {
            ResponseHelper::error("No se recibió el rol del usuario");
        }
        
        $menuModel = new MenuModel();
        $data = $menuModel->list($userRol);
        ResponseHelper::success("Permisos cargados", $data);

       
    }
}
