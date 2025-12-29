<?php
require_once __DIR__ . '/../bootstrap.php';
Auth::requireEmployee();

header('Content-Type: application/json');

$notification = new NotificationModel($db);
$userId = Session::getUserId();

$count = (int) $notification->getUnreadCount('employee', $userId);

echo json_encode(['unread' => $count]);
exit;

?>
