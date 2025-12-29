<?php
require_once __DIR__ . '/../bootstrap.php';
Auth::requireAdmin();

header('Content-Type: application/json');

$notification = new NotificationModel($db);
$hrId = Session::getUserId();

$count = (int) $notification->getUnreadCount('admin', $hrId);

echo json_encode(['unread' => $count]);
exit;

?>
