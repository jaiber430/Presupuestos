<?php

namespace presupuestos\model;

require_once __DIR__ . "/MainModel.php";

use PDO;
use Exception;

class AnioFiscalModel extends MainModel{

    public static function insert($params){
        $query = "INSERT INTO aniosfiscales 
                 (subdirectorIdFk, creadoPorFk, anioFiscal, valorAnioFiscal, presupuestoActual, fechaInicio, fechaCierre, estado, centroIdFk) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function updatePresupuesto($id, $nuevoMonto){
        $query = "UPDATE aniosfiscales SET presupuestoActual = ? WHERE idAniFiscal = ?";
        return self::executeQuery($query, [$nuevoMonto, $id]);
    }

    public static function getPresupuestoActual($id){
        $query = "SELECT presupuestoActual FROM aniosfiscales WHERE idAniFiscal = ?";
        $stmt  = self::executeQuery($query, [$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertModificacion($params){
        $query = "INSERT INTO modificacionespresupuesto 
                 (anio_fiscal_id, tipo_modificacion, monto_anterior, monto_modificacion, monto_nuevo, motivo, usuario_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        return self::executeQuery($query, $params);
    }

    public static function desactivarOtros($id_excluir, $year){
        $query = "UPDATE aniosfiscales SET estado = 0 WHERE idAniFiscal != ? AND anioFiscal != ? AND estado = 1";
        return self::executeQuery($query, [$id_excluir, $year]);
    }

    public static function getSubdirectores(){
        $query = "SELECT idUser, nombres, apellidos FROM user WHERE estado = 1 AND rol_id= 2";
        $stmt  = self::executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Método público para obtener el último ID insertado =====
    public static function getLastInsertId(){
        return self::getConnection()->lastInsertId();
    }

    public static function getPresupuestoActivo($id_centro){
        $query = "SELECT * FROM aniosfiscales WHERE estado = 1 AND centroIdFK = ? LIMIT 1";
        $stmt  = self::executeQuery($query, [$id_centro]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function verificarSemanaExistente(int $numeroSemana, int $centroId): bool{
        $sql = "SELECT COUNT(*) FROM semanascarga WHERE numeroSemana = ? AND centroIdFk = ?";
        $stmt = self::executeQuery($sql, [$numeroSemana, $centroId]);
        return $stmt->fetchColumn() > 0;
    }

    public static function insertarSemanaCarga(int $numeroSemana, string $fechaInicio, string $fechaFin, int $centroId): void{

        $sql = "INSERT INTO semanascarga (numeroSemana, fechaInicio, fechaFin, centroIdFk) VALUES (?, ?, ?, ?)";
        self::executeQuery($sql, [$numeroSemana, $fechaInicio, $fechaFin, $centroId]);
    }

    public static function crearAnioFiscalConSemanas(array $datosAnioFiscal, array $semanas, int $centroId): bool{

        $pdo = self::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Insertar año fiscal
            $ok = self::insert($datosAnioFiscal);
            if (!$ok) {
                throw new Exception("No se pudo registrar el año fiscal");
            }

            // 2. Si está activo, desactivar otros
            if ($datosAnioFiscal[7] === 1) { 
                $idNuevo = self::getLastInsertId();
                self::desactivarOtros($idNuevo, $datosAnioFiscal[2]); 
            }

            // 3. Guardar semanas
            foreach ($semanas as $semana) {
                $existe = self::verificarSemanaExistente($semana['numero_semana'], $centroId);
                if (!$existe) {
                    self::insertarSemanaCarga(
                        $semana['numero_semana'],
                        $semana['inicio'],
                        $semana['fin'],
                        $centroId
                    );
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    //Obtengo todas las semanas por centro
    public static function obtenerSemanasPorCentro(int $centroId): array{
        $sql = "SELECT idSemana, numeroSemana, fechaInicio, fechaFin, 
                   archivoCdp, archivoRp, archivoPagos, fechaSubida 
            FROM semanascarga 
            WHERE centroIdFk = ? 
            ORDER BY numeroSemana ASC";
        $stmt = self::executeQuery($sql, [$centroId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
