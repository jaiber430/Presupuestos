<?php

use presupuestos\controller\Auth\AuthController;
use presupuestos\controller\DashboardController;
use presupuestos\controller\MenuController;
use presupuestos\controller\ReportsController;
use presupuestos\controller\AnioFiscalController;
use presupuestos\controller\role\RoleController;

return [
    '' => fn() => (new AuthController())->showLogin(),
    'login' => fn() => (new AuthController())->showLogin(),
    'login-post' => fn() => (new AuthController())->login($_POST),
    'getCentro' => fn() => (new AuthController())->getCentros(),
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
    // Eliminación de datos de reportes (sólo POST)
    'reports/delete-post'=> fn()=> (new ReportsController())->delete(),
    'dashboard/listar-post'=> fn()=> (new MenuController())->getByRole(),
    'dashboard/actualizar-permiso-post'=> fn()=> (new MenuController())->updatePermisses(),
    'crear_anio_fiscal-post'=> fn() => AnioFiscalController::crear(),
    //Gestión de Roles
    'gestionar/usuarios'=> fn()=> (new RoleController())-> showManage(),
    /*
    
    'gestionar-usuarios'=> fn()=> (RoleController()->listar()),
    'roles/list' (new RoleController())->list(),
    'roles/create' (new RoleController())->create(),
    'permisos/list' (new PermisoController())->list(),
    'permisoRol/assign' (new PermisoRolController())->assign(),
    'permisoRol/revoke' (new PermisoRolController())->revoke(),
    'permisoRol/listByRole' (new PermisoRolController())->listByRole(),
    */
    
];

