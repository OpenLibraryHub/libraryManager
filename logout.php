<?php
require_once __DIR__ . '/config/autoload.php';

use App\Controllers\AuthController;

$auth = new AuthController();
$auth->logout();
