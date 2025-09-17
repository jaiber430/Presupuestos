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
    'dashboard' => fn()=> (new DashboardController())->index(),
    // 'aulas'=> fn()=> (new ClassroomController())->index(),
    
    // 'create-course-post'=> fn()=> (new CourseController())->createCourse($_POST),
    // 'create-classroom-post'=> fn()=> (new ClassroomController())->createClassroom($_POST)
];

