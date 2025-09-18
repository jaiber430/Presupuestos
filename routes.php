<?php

use presupuestos\controller\Auth\AuthController;
use presupuestos\controller\DashboardController;
use presupuestos\controller\MenuController;


return [
    '' => fn() => (new AuthController())->showLogin(),
    'login' => fn() => (new AuthController())->showLogin(),
    'login-post' => fn() => (new AuthController())->login($_POST),
    'register-post' => fn() => (new AuthController())->register($_POST),
    'recovery' => fn() => (new AuthController())->showRecoveryPassword($_POST),
    'recovery-post' => fn() => (new AuthController())->recoveryPassword($_POST),
    'logout-post' => fn() => (new AuthController())->logout(),
    'verify' => fn() => (new AuthController())->verify($_GET['token']),
    'dashboard' => fn()=> (new DashboardController())->index(),
    'dashboard/listar-post'=> fn()=> (new MenuController())->getByRole(),
];

