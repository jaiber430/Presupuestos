<?php

use presupuestos\controller\Auth\AuthController;
use presupuestos\controller\DashboardController;
use presupuestos\controller\MenuController;
use presupuestos\controller\ReportsController;
use presupuestos\controller\AnioFiscalController;

return [
    '' => fn() => (new AuthController())->showLogin(),
    'login' => fn() => (new AuthController())->showLogin(),
    'login-post' => fn() => (new AuthController())->login($_POST),
    'register-post' => fn() => (new AuthController())->register($_POST),
    'recovery-post' => fn() => (new AuthController())->recoveryPassword($_POST),
    'send-successful'=> fn() =>(new AuthController())->showSendSuccessful(),
    'recovery' => fn() => (new AuthController())->showRecoveryPassword($_POST),
    'logout-post' => fn() => (new AuthController())->logout(),
    'verify' => fn() => (new AuthController())->verify($_GET['token']),
    'dashboard' => fn()=> (new DashboardController())->index(),    
    'reports'=> fn()=> (new DashboardController())->index("reportes"),
    'reports-post'=> fn()=> (new ReportsController())->index(),
    'reports/dependencias'=> fn()=> (new ReportsController())->dependencias(),
    'reports/consulta'=> fn()=> (new ReportsController())->consulta(),
    'dashboard/listar-post'=> fn()=> (new MenuController())->getByRole(),
    'dashboard/actualizar-permiso-post'=> fn()=> (new MenuController())->updatePermisses(),
    'crear_anio_fiscal-post'=> fn() => AnioFiscalController::crear(),
];

