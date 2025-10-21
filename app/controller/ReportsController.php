<?php

namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\model\ReportsModel;
use Exception;
use PDO;

class ReportsController
{
    /**
     * POST /reports -> subir Excel semana 1 (cdp, rp, pagos)
     */
    public function index()
    {
        Auth::check();

        try {
            if (empty($_POST['week']) || empty($_POST['semana_id'])) {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'tipo'   => 'simple',
                    'titulo' => 'Datos incompletos',
                    'texto'  => 'Debes indicar la semana y el ID de semana.',
                    'icono'  => 'warning',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $files = $_FILES;
            $semanaId = (int)$_POST['semana_id'];
            $centroId = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];
            // Procesa Excel usando ReportsModel adaptado
            $results = ReportsModel::processWeek1Excels($files, $semanaId, $centroId);

            ReportsModel::fillInformePresupuestal($semanaId, $centroId);
            ReportsModel::updateInformeWithPagos();

            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'tipo'   => 'simple',
                'titulo' => '¡Éxito!',
                'texto'  => implode("\n", $results),
                'icono'  => 'success',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'tipo'   => 'simple',
                'titulo' => 'Error al subir',
                'texto'  => $e->getMessage(),
                "archivo" => $e->getFile(),
                "linea"  => $e->getLine(),
                "traza"  => $e->getTraceAsString(),
                'icono'  => 'error',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function getInformePresupuestalPorSemana(int $semanaId)
    {
        Auth::check();

        $idCentroIdSession = $_SESSION[APP_SESSION_NAME]['idCentroIdSession'];

        // Obtener datos del modelo
        $datosInforme = ReportsModel::getInformePresupuestalPorSemana(
            $idCentroIdSession,
            $semanaId
        );

        return $datosInforme;
       
        // Calcular resúmenes
        $resumenes = $this->calcularResumenes($datosInforme);

        // Preparar datos para la vista
        $vistaDatos = [
            'datos' => $datosInforme,
            'resumenes' => $resumenes,
            'semanaId' => $semanaId,
            'totalRegistros' => count($datosInforme)
        ];
    }

    private function calcularResumenes(array $datos): array
    {
        $totalPresupuesto = 0;
        $totalPagado = 0;
        $totalSaldo = 0;
        $totalComprometido = 0;

        foreach ($datos as $fila) {
            $totalPresupuesto += floatval($fila['valorActual'] ?? 0);
            $totalPagado += floatval($fila['valorPagado'] ?? 0);
            $totalSaldo += floatval($fila['saldoPorComprometer'] ?? 0);
            $totalComprometido += floatval($fila['valorComprometido'] ?? 0);
        }

        return [
            'totalPresupuesto' => $totalPresupuesto,
            'totalPagado' => $totalPagado,
            'totalSaldo' => $totalSaldo,
            'totalComprometido' => $totalComprometido
        ];
    }

    /**
     * GET /reports/dependencias -> lista dependencias
     */
    public function dependencias()
    {
        header('Content-Type: application/json; charset=utf-8');
        $deps = ReportsModel::getDependencias();
        echo json_encode($deps, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/cdps -> lista números CDP únicos
     */
    public function cdps()
    {
        header('Content-Type: application/json; charset=utf-8');
        $cdps = ReportsModel::getCDPs();
        echo json_encode($cdps, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/conceptos -> lista conceptos internos únicos
     */
    public function conceptos()
    {
        header('Content-Type: application/json; charset=utf-8');
        $conceptos = ReportsModel::getConceptos();
        echo json_encode($conceptos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/consulta?dependencia=...&numero_cdp=...&concepto_interno=...
     */
    public function consulta()
    {
        header('Content-Type: application/json; charset=utf-8');
        $filters = [
            'dependencia' => $_GET['dependencia'] ?? '',
            'numero_cdp'  => $_GET['numero_cdp'] ?? '',
            'concepto_interno' => $_GET['concepto_interno'] ?? ''
        ];
        $rows = ReportsModel::consultarCDP($filters);
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * POST /reports/delete -> limpia datos cargados (TRUNCATE) para la semana indicada
     */
    public function delete()
    {
        Auth::check();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $week = $_POST['week'] ?? '';
            if ($week === '') {
                echo json_encode([
                    'tipo' => 'simple',
                    'titulo' => 'Semana requerida',
                    'texto' => 'No se recibió la semana a eliminar.',
                    'icono' => 'warning'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            ReportsModel::clearWeekData($week);

            echo json_encode([
                'tipo' => 'simple',
                'titulo' => 'Datos eliminados',
                'texto' => 'Se eliminaron los datos asociados a la semana seleccionada.',
                'icono' => 'success'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            echo json_encode([
                'tipo' => 'simple',
                'titulo' => 'Error al eliminar',
                'texto' => $e->getMessage(),
                'icono' => 'error'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
