<?php
require_once __DIR__ . '/../bootstrap.php';
Auth::requireAdmin();

header('Content-Type: application/json');

$notification = new NotificationModel($db);
$hrId = Session::getUserId();

$ok = $notification->markAllAsRead('admin', $hrId);

echo json_encode(['success' => $ok ? true : false]);
exit;

?>
