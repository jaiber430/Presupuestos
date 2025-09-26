<?php
namespace presupuestos\controller\role;
use presupuestos\controller\role\manage;
use presupuestos\model\role\RoleModel;
use presupuestos\model\role\PermisoModel;

class RoleController{
    public function showManage()    {
        //obtengo los roles para listarlos en la vista
        $roleModel = new RoleModel();
        $roles = $roleModel->getAll();

        // obtengo también los permisos para el formulario
        $permisoModel = new PermisoModel();
        $permisos = $permisoModel->getAll();

        require __DIR__ . '/../../view/role/manage.php';
    }
    
    public function list() {
        $roleModel = new RoleModel();
        $roles = $roleModel->getAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['state' => 1, 'data' => $roles]);
        exit;
    }

    public function create() {
        $nombre = $_POST['nombre'] ?? '';

        if (!$nombre) {
            echo json_encode(['state' => 0, 'message' => 'Nombre de rol requerido']);
            exit;
        }

        $roleModel = new RoleModel();
        $ok = $roleModel->create($nombre);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Rol creado correctamente' : 'Error al crear rol'
        ]);
        exit;
    }
}
