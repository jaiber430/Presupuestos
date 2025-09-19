<?php
namespace presupuestos\controller;

use presupuestos\helpers\Auth;

require __DIR__ . '/../../bootstrap.php';

class ReportsController {
    public function index() {
        Auth::check();

        // Variables para el layout
        $pageTitle = 'Reportes';

        // Estilos específicos (opcional)
        $pageStyles = '<link rel="stylesheet" type="text/css" href="' . APP_URL . 'css/reports/reports.css">';

        // Scripts específicos (opcional)
        $pageScripts = '';

        // Capturar contenido de la vista reports
        ob_start();
        $view= '/../view/reports/reports.php';
        $content = ob_get_clean();

        require __DIR__ . '/../view/layout/layout.php';
    }
}
