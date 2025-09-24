<?php
namespace presupuestos\model;

use presupuestos\model\MainModel;

class MenuModel extends MainModel{

    public function list($userRol){
        // Traer los permisos del usuario logueado
        $queryById = "
            SELECT 
                p.icon,
                p.url, 
                r.nombre AS nombre_rol, 
                p.nombre AS nombre_permiso
            FROM permisorol pr
            JOIN permiso p ON p.id = pr.permiso_id
            JOIN rol r ON r.id = pr.rol_id
            WHERE r.id = :userRol
              AND pr.estado = '1'
        ";

        $params = ["userRol" => $userRol];
        $stmtById = $this->executeQuery($queryById, $params);
        $dataPermisosByRol = $stmtById->fetchAll(\PDO::FETCH_ASSOC); 
        
        $query = "
            SELECT
                r.icon,
                r.id AS rol_id,
                r.nombre AS nombre_rol,
                p.id AS permiso_id,
                p.nombre AS permiso_nombre,
                pr.estado AS estado
            FROM permisorol pr
            JOIN permiso p ON p.id = pr.permiso_id
            JOIN rol r ON r.id = pr.rol_id
            ORDER BY r.id, p.nombre
        ";

        $stmt = $this->executeQuery($query); 
        $permissionsPerRol = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'state' => 1,
            'userRolePermissions' => $dataPermisosByRol,
            'allRolePermissions' => $permissionsPerRol
        ];
    }

    public function updatePermisses(string $rolNombre, string $permisoNombre, int $estado): array {
        try {
            // 1. Obtener ID del rol
            $rolQuery = "SELECT id FROM rol WHERE nombre = :rolNombre";
            $rolStmt = $this->executeQuery($rolQuery, ['rolNombre' => $rolNombre]);
            $rol = $rolStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$rol) {
                return ['success' => false, 'message' => 'Rol no encontrado'];
            }
            $rolId = $rol['id'];

            // 2. Obtener ID del permiso
            $permisoQuery = "SELECT id FROM permiso WHERE nombre = :permisoNombre";
            $permisoStmt = $this->executeQuery($permisoQuery, ['permisoNombre' => $permisoNombre]);
            $permiso = $permisoStmt->fetch(\PDO::FETCH_ASSOC);
            if (!$permiso) {
                return ['success' => false, 'message' => 'Permiso no encontrado'];
            }
            $permisoId = $permiso['id'];

            // 3. Insertar o actualizar en permisorol
            $insertQuery = "
                INSERT INTO permisorol (rol_id, permiso_id, estado)
                VALUES (:rolId, :permisoId, :estado)
                ON DUPLICATE KEY UPDATE estado = :estado
            ";
            $this->executeQuery($insertQuery, [
                'rolId' => $rolId,
                'permisoId' => $permisoId,
                'estado' => $estado
            ]);

            return ['success' => true];

        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar permiso: ' . $e->getMessage()];
        }
    }

}
