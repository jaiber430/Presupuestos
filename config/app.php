<?php
session_start();


require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


define('APP_URL', getenv('APP_URL'));
define('APP_NAME', getenv('APP_NAME'));
define('APP_SESSION_NAME', getenv('APP_SESSION_NAME'));
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'America/Bogota');

