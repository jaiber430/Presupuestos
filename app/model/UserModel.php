<?php
namespace presupuestos\model;

use presupuestos\model\MainModel;
use PDO;
use PDOException;

class UserModel extends MainModel {	
    
    public function findByEmail(string $email) {
        $query= "SELECT r.nombre AS nombre_rol, u.*
            FROM user u
            JOIN rol r on u.rol_id=r.id 
            WHERE email = :email";

        $params= ["email" => $email];
        $stmt = parent::executeQuery($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): array {
		$query = "INSERT INTO user (nombres, apellidos, numero_documento, email, password, centro_id)
				VALUES (:names, :lastNames, :idNumber,  :email, :password, :centro_id)";
		
		$params = [
				'names' => $data['names'],
				'lastNames' => $data['lastNames'],
				'idNumber'=> $data['idNumber'],
				'email' => $data['email'],
				'password'=> $data['password'],
                'centro_id'=> $data['centroId']
			];

		try {
			$stmt = parent::executeQuery($query, $params);
			if($stmt->rowCount() > 0){
				return ['success' => true];
			} else {
				return ['success' => false, 'error' => 'No se insertó ningún registro'];
			}
		} catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return [
                    'success' => false,
                    'error'   => 'El número de documento ya está registrado'
                ];
            }

			return ['success' => false, 'error' => $e->getMessage()];
		}
	}


    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $query = "UPDATE user SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = parent::executeQuery($query, $params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function verifyAccount(int $userId): bool {
        $query = "UPDATE user SET es_verificado = 1 WHERE id = :id";
        $params = [':id' => $userId];
        $stmt = parent::executeQuery($query, $params);
        return $stmt->rowCount() > 0;
    }

    public static function getSubdirector($centroId) {
        try {
            $query = "SELECT id, nombres, apellidos
                    FROM user 
                    WHERE rol_id = 2
                    AND centro_id = ?
                    LIMIT 1";
            $stmt = parent::executeQuery($query, [$centroId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null; 
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function getAllByCentro($centroId) {
        try {
            $query = "SELECT u.id, u.nombres, u.apellidos, u.email, u.es_verificado, r.nombre as nombre_rol
                    FROM user u
                    JOIN rol r ON u.rol_id = r.id
                    WHERE centro_id = ?";
            $stmt = parent::executeQuery($query, [$centroId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }


}
