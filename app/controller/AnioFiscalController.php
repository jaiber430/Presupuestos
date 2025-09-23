<?php
namespace presupuestos\controller;

require_once __DIR__ . '../../../config/app.php';
use presupuestos\model\AnioFiscalModel;
use Exception;

class AnioFiscalController {

   public static function crear()
    {
        if (
            empty($_POST['year_fiscal']) ||
            empty($_POST['valor_presupuesto']) ||
            empty($_POST['fecha_inicio']) ||
            empty($_POST['fecha_cierre']) ||
            empty($_POST['subdirector_id'])
        ) {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "Faltan datos obligatorios",
                "icono"  => "error"
            ]);
            return;
        }

        $anioFiscal    = (int) $_POST['year_fiscal'];
        $presupuesto   = (float) $_POST['valor_presupuesto'];
        $estado        = ($_POST['estado'] ?? '') === 'activo' ? 1 : 0;
        $fechaInicio   = $_POST['fecha_inicio'];
        $fechaCierre   = $_POST['fecha_cierre'];
        $subdirectorId = (int) $_POST['subdirector_id'];

        $usuarioId = $_SESSION[APP_SESSION_NAME]['id'] ?? null;
        $centroId  = $_SESSION[APP_SESSION_NAME]['centro_id'] ?? null;

        if (!$usuarioId || !$centroId) {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "No se encontró información de sesión (usuario o centro)",
                "icono"  => "error"
            ]);
            return;
        }

        $ok = AnioFiscalModel::insert([
            $subdirectorId,   // subdirector_id
            $usuarioId,       // user_id
            $anioFiscal,      // anio_fiscal
            $presupuesto,     // valor_anio_fiscal
            $presupuesto,     // presupuesto_actual
            $fechaInicio,     // fecha_inicio
            $fechaCierre,     // fecha_cierre
            $estado,          // estado
            $centroId         // id_centro
        ]);

        if ($ok) {
            if ($estado === 1) {
                // Usamos el método público para obtener el último ID insertado
                $idNuevo = AnioFiscalModel::getLastInsertId();
                AnioFiscalModel::desactivarOtros($idNuevo, $anioFiscal);
            }

            echo json_encode([
                "tipo"   => "redireccionar",
                "titulo" => "Éxito",
                "texto"  => "Año fiscal creado correctamente",
                "icono"  => "success",
                "url"    => "dashboard"
            ]);
        } else {
            echo json_encode([
                "tipo"   => "simple",
                "titulo" => "Error",
                "texto"  => "No se pudo registrar el año fiscal",
                "icono"  => "error"
            ]);
        }
    }

    public static function modificar() {
        try {
            if (empty($_POST['anio_fiscal_id']) || empty($_POST['tipo_modificacion']) || empty($_POST['monto'])) {
                throw new Exception("Datos incompletos");
            }

            $id     = $_POST['anio_fiscal_id'];
            $tipo   = $_POST['tipo_modificacion'];
            $monto  = (float) $_POST['monto'];
            $motivo = $_POST['motivo'] ?? "";
            $usuario = $_POST['usuario_id'];

            // Presupuesto actual
            $actual = AnioFiscalModel::getPresupuestoActual($id);
            if (!$actual) {
                throw new Exception("Año fiscal no encontrado");
            }

            $anterior = (float) $actual['presupuesto_actual'];
            $nuevo    = self::calcularNuevoPresupuesto($anterior, $monto, $tipo);

            // Registrar modificación
            AnioFiscalModel::insertModificacion([
                $id, $tipo, $anterior, $monto, $nuevo, $motivo, $usuario
            ]);

            // Actualizar
            AnioFiscalModel::updatePresupuesto($id, $nuevo);

            echo json_encode([
                "tipo" => "recargar",
                "titulo" => "Éxito",
                "texto" => "Presupuesto actualizado correctamente",
                "icono" => "success"
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "tipo" => "simple",
                "titulo" => "Error",
                "texto" => $e->getMessage(),
                "icono" => "error"
            ]);
        }
    }

    private static function calcularNuevoPresupuesto($anterior, $monto, $tipo) {
        switch ($tipo) {
            case 'incremento':
                return $anterior + $monto;
            case 'decremento':
                $nuevo = $anterior - $monto;
                if ($nuevo < 0) throw new Exception('El presupuesto no puede ser negativo');
                return $nuevo;
            case 'ajuste':
                if ($monto < 0) throw new Exception('El presupuesto no puede ser negativo');
                return $monto;
            default:
                throw new Exception('Tipo de modificación no válido');
        }
    }
}
