<?php
require_once __DIR__ . '/../bootstrap.php';
Auth::requireAdmin();

header('Content-Type: application/json');

$empModel = new EmployeeModel($db);
$leaveModel = new LeaveModel($db);
$taskModel = new TaskModel($db);

$data = [
    'totalEmployees' => (int) $empModel->getTotalCount(),
    'activeEmployees' => (int) $empModel->getActiveCount(),
    'pendingLeaves' => (int) $leaveModel->getPendingLeavesCount(),
    'totalTasks' => (int) $taskModel->getTotalCount()
];

echo json_encode($data);
exit;

?>
