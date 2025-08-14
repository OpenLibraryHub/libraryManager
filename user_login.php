<?php
require_once __DIR__ . '/config/autoload.php';

use App\Controllers\AuthController;
use App\Helpers\Session;

Session::start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!Session::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    Session::flash('error', 'Token inválido');
    header('Location: login.php');
    exit;
  }
  $auth = new AuthController();
  $res = $auth->patronLogin($_POST);
  if ($res['success']) {
    header('Location: ' . $res['redirect']);
    exit;
  }
  Session::flash('error', $res['message'] ?? 'Error');
  header('Location: login.php');
  exit;
}
http_response_code(405);
echo 'Method Not Allowed';


