<?php
require_once __DIR__ . '/../bootstrap.php';
Auth::requireEmployee();

header('Content-Type: application/json');

$notification = new NotificationModel($db);
$userId = Session::getUserId();

$ok = $notification->markAllAsRead('employee', $userId);

echo json_encode(['success' => $ok ? true : false]);
exit;

?>
