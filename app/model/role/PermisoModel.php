<?php
namespace presupuestos\model\role;
use presupuestos\model\MainModel;
use PDO;

class PermisoModel extends MainModel
{
    public function getAll(): array {
        $query = "SELECT * FROM permiso ORDER BY nombre";
        $stmt = parent::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $nombre): bool {
        $query = "INSERT INTO permiso (nombre) VALUES (:nombre)";
        $stmt = parent::executeQuery($query, ['nombre' => $nombre]);
        return $stmt->rowCount() > 0;
    }
}
