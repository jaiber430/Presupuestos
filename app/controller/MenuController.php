<?php
namespace presupuestos\controller;

use presupuestos\model\MenuModel;
use presupuestos\helpers\ResponseHelper;
use presupuestos\helpers\HtmlResponse;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

require __DIR__ . '/../../bootstrap.php';
class MenuController{
    
    public function getByRole(){
        $userRol = $_SESSION[APP_SESSION_NAME]['idROl'];
        if (!$userRol) {
            ResponseHelper::error("No se recibió el rol del usuario");
        }
        
        $menuModel = new MenuModel();
        $data = $menuModel->list($userRol);
        ResponseHelper::success("Permisos cargados", $data);

    }

   public function updatePermisses() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ResponseHelper::error('Método no permitido');
        }

        $rolNombre = $_POST['rol'] ?? null;
        $permisoNombre = $_POST['permiso'] ?? null;
        $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 0;

        if (!$rolNombre || !$permisoNombre) {
            ResponseHelper::error('Datos incompletos');
        }

        $menuModel = new \presupuestos\model\MenuModel();
        $success = $menuModel->updatePermisses($rolNombre, $permisoNombre, $estado);

        if ($success) {
            ResponseHelper::success('Permiso actualizado correctamente');
        } else {
            ResponseHelper::error('No se pudo actualizar el permiso');
        }
    }

}
