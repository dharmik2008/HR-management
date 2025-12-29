<?php
require_once __DIR__ . '/../backend/bootstrap.php';

Session::destroy();

// Redirect to login page
header('Location: ' . APP_URL . '/frontend/index.php');
exit;