<?php

use presupuestos\controller\Auth\AuthController;
use presupuestos\controller\DashboardController;
// use Klassroom\controller\CourseController;
// use Klassroom\controller\ClassroomController;
// use Klassroom\controller\AssigmentController;

return [
    '' => fn() => (new AuthController())->showLogin(),
    'login' => fn() => (new AuthController())->showLogin(),
    'login-post' => fn() => (new AuthController())->login($_POST),
    'register-post' => fn() => (new AuthController())->register($_POST),
    'recovery' => fn() => (new AuthController())->showRecoveryPassword($_POST),
    'recovery-post' => fn() => (new AuthController())->recoveryPassword($_POST),
    'dashboard' => fn()=> (new DashboardController())->index(),
];

