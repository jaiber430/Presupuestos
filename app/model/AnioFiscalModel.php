<?php
namespace presupuestos\model;

require_once __DIR__ . "/MainModel.php";
use PDO;

class AnioFiscalModel extends MainModel {

    public static function insert($params) {
        $query = "INSERT INTO anios_fiscales 
                 (subdirector_id, creado_por, anio_fiscal, valor_anio_fiscal, presupuesto_actual, fecha_inicio, fecha_cierre, estado, id_centro) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function updatePresupuesto($id, $nuevoMonto) {
        $query = "UPDATE anios_fiscales SET presupuesto_actual = ? WHERE id = ?";
        return self::executeQuery($query, [$nuevoMonto, $id]);
    }

    public static function getPresupuestoActual($id) {
        $query = "SELECT presupuesto_actual FROM anios_fiscales WHERE id = ?";
        $stmt  = self::executeQuery($query, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertModificacion($params) {
        $query = "INSERT INTO modificaciones_presupuesto 
                 (anio_fiscal_id, tipo_modificacion, monto_anterior, monto_modificacion, monto_nuevo, motivo, usuario_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function desactivarOtros($id_excluir, $year) {
        $query = "UPDATE anios_fiscales SET estado = 0 WHERE id != ? AND anio_fiscal != ? AND estado = 1";
        return self::executeQuery($query, [$id_excluir, $year]);
    }

    public static function getSubdirectores() {
        $query = "SELECT id, nombre, apellido FROM usuarios WHERE estado = 1 AND cargo LIKE '%subdirector%'";
        $stmt  = self::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Método público para obtener el último ID insertado =====
    public static function getLastInsertId() {
        return self::getConnection()->lastInsertId();
    }

    public static function getPresupuestoActivo($id_centro) {
        $query = "SELECT * FROM anios_fiscales WHERE estado = 1 AND id_centro = ? LIMIT 1";
        $stmt  = self::executeQuery($query, [$id_centro]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }



}
