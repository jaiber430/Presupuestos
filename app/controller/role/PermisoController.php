<?php
// app/controller/PermisoController.php

class PermisoController
{
    public function list() {
        $permisoModel = new PermisoModel();
        $permisos = $permisoModel->getAll();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['state' => 1, 'data' => $permisos]);
        exit;
    }

    public function create() {
        $nombre = $_POST['nombre'] ?? '';

        if (!$nombre) {
            echo json_encode(['state' => 0, 'message' => 'Nombre de permiso requerido']);
            exit;
        }

        $permisoModel = new PermisoModel();
        $ok = $permisoModel->create($nombre);

        echo json_encode([
            'state' => $ok ? 1 : 0,
            'message' => $ok ? 'Permiso creado correctamente' : 'Error al crear permiso'
        ]);
        exit;
    }
}
