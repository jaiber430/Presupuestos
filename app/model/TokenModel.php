<?php
namespace presupuestos\model;

use presupuestos\model\MainModel;
use PDO;

class TokenModel extends MainModel {

    public function create(int $userId, string $token, string $type, string $expiresAt): bool {
        $query = "INSERT INTO tokens (user_id, token, type, expires_at) 
                  VALUES (:user_id, :token, :type, :expires_at)";
        $params = [
            ':user_id' => $userId,
            ':token' => $token,
            ':type' => $type,
            ':expires_at' => $expiresAt
        ];
        $stmt = $this->executeQuery($query, $params);
        return $stmt->rowCount() > 0;
    }

    public function findByToken(string $token): ?array {
        $query = "SELECT * FROM tokens WHERE token = :token LIMIT 1";
        $stmt = $this->executeQuery($query, [':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function deleteByToken(string $token): bool {
        $query = "DELETE FROM tokens WHERE token = :token";
        $stmt = $this->executeQuery($query, [':token' => $token]);
        return $stmt->rowCount() > 0;
    }

    public function deleteExpired(): bool {
        $query = "DELETE FROM tokens WHERE expires_at < NOW()";
        $stmt = $this->executeQuery($query);
        return $stmt->rowCount() > 0;
    }
}
