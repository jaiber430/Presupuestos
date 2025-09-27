<?php
namespace presupuestos\controller;

use presupuestos\model\UserModel;

class UserController {

    /** Listar todos los usuarios de un centro */
    public static function listByCentro($centroId) {
        return UserModel::getAllByCentro($centroId);
    }

    /** Obtener un usuario por email */
    public static function findByEmail(string $email) {
        $userModel = new UserModel();
        return $userModel->findByEmail($email);
    }

    /** Crear un nuevo usuario */
    public static function create(array $data): array {
        $userModel = new UserModel();
        return $userModel->create($data);
    }

    /** Actualizar un usuario */
    public static function update(int $id, array $data): bool {
        $userModel = new UserModel();
        return $userModel->update($id, $data);
    }

    /** Verificar una cuenta de usuario */
    public static function verifyAccount(int $userId): bool {
        $userModel = new UserModel();
        return $userModel->verifyAccount($userId);
    }

    /** Obtener al subdirector de un centro */
    public static function getSubdirector($centroId) {
        return UserModel::getSubdirector($centroId);
    }
}
