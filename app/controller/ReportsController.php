<?php

namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\model\ReportsModel;

require __DIR__ . '/../../bootstrap.php';

class ReportsController{
    /**
     * POST /reports -> subir Excel semana 1 (cdp, rp, pagos)
     */
    public function index(){
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

            $files = $_FILES ?? [];
            $semanaId = (int)$_POST['semana_id'];

            // Procesa Excel usando ReportsModel adaptado
            $results = ReportsModel::processWeek1Excels($files, $semanaId);

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
                'icono'  => 'error',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }


    /**
     * GET /reports/dependencias -> lista dependencias
     */
    public function dependencias(){

        header('Content-Type: application/json; charset=utf-8');
        $deps = ReportsModel::getDependencias();
        echo json_encode($deps, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/consulta?dependencia=...&codigo_cdp=...
     */
    public function consulta(){

        header('Content-Type: application/json; charset=utf-8');
        $filters = [
            'dependencia' => $_GET['dependencia'] ?? '',
            'codigo_cdp'  => $_GET['codigo_cdp'] ?? '',
        ];
        $rows = ReportsModel::consultarCDP($filters);
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * POST /reports/delete -> limpia datos cargados (TRUNCATE) para la semana indicada
     */
    public function delete(){
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
