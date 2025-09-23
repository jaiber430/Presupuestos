<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;
use presupuestos\helpers\ResponseHelper;
use presupuestos\model\ReportsModel;

require __DIR__ . '/../../bootstrap.php';

class ReportsController {
    /**
     * POST /reports -> subir CSVs semana 1 (cdp, rp, pagos)
     */
    public function index() {
        Auth::check();

        try {
            if (empty($_POST['week'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'tipo'   => 'simple',
                    'titulo' => 'Datos incompletos',
                    'texto'  => 'Debes indicar la semana.',
                    'icono'  => 'warning',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Nota: FormularioAjax envía archivos en $_FILES
            $files = $_FILES ?? [];

            $results = ReportsModel::processWeek1CSVs($files);

            // Estructura compatible con alerts.js -> tipo simple/recargar/limpiar
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'tipo'   => 'simple',
                'titulo' => '¡Éxito!',
                'texto'  => implode("\n", $results),
                'icono'  => 'success',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
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
    public function dependencias() {
        Auth::check();
        header('Content-Type: application/json; charset=utf-8');
        $deps = ReportsModel::getDependencias();
        echo json_encode($deps, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reports/consulta?dependencia=...&codigo_cdp=...
     */
    public function consulta() {
        Auth::check();
        header('Content-Type: application/json; charset=utf-8');
        $filters = [
            'dependencia' => $_GET['dependencia'] ?? '',
            'codigo_cdp'  => $_GET['codigo_cdp'] ?? '',
        ];
        $rows = ReportsModel::consultarCDP($filters);
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
