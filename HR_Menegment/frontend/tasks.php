<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$taskModel = new TaskModel($db);
$empModel = new EmployeeModel($db);
$notificationModel = new NotificationModel($db);

$message = '';
$messageType = '';

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'update_status') {
        $taskId = sanitize($_POST['task_id'] ?? '');
        $status = sanitize($_POST['status'] ?? '');
        
        if (empty($taskId) || empty($status)) {
            $message = 'Task and status are required';
            $messageType = 'danger';
        } else {
            $result = $taskModel->updateTaskStatus($taskId, $status);
            $message = $result ? 'Task status updated successfully!' : 'Failed to update task';
            $messageType = $result ? 'success' : 'danger';
        }
    }
}

// Get data
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$tasks = $taskModel->getTasksByEmployee($empId, $statusFilter, null, $limit, $offset);
$allTasks = $taskModel->getTasksByEmployee($empId);
$totalCount = count($allTasks);
$totalPages = ceil($totalCount / $limit);

// Get task counts by status
$taskCounts = array_fill_keys(['Pending', 'In Progress', 'Completed'], 0);
foreach ($allTasks as $task) {
    if (isset($taskCounts[$task['Status']])) {
        $taskCounts[$task['Status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Tasks | HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body { background: #ffffff; font-family: "Inter", system-ui, -apple-system, sans-serif; }
        .sidebar { min-height: 100vh; background: #ffffff; color: #0f172a; display: flex; flex-direction: column; gap: 1.5rem; border-right: 1px solid #e5e7f0; }
        .sidebar a { color: #0f172a; text-decoration: none; }
        .sidebar a.active, .sidebar a:hover { color: #0d6efd; }
        .card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .avatar { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #0d6efd, #5c9dff); display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; }
        .sidebar-header small { color: #6b7280; }
        .sidebar-nav { display: flex; flex-direction: column; gap: 0.35rem; }
        .nav-pill { display: flex; align-items: center; gap: 0.65rem; padding: 0.7rem 0.9rem; border-radius: 12px; font-weight: 600; color: #0f172a; transition: 0.2s ease; }
        .nav-pill i { font-size: 1.05rem; opacity: 0.9; color: #0d6efd; }
        .nav-pill:hover { background: #e7f1ff; color: #0d6efd; }
        .nav-pill.active { background: #e7f1ff; color: #0d6efd; box-shadow: 0 10px 25px rgba(13,110,253,0.15); }
        .sidebar-actions { margin-top: auto; display: grid; gap: 0.6rem; }
        .btn-logout { background: #fee2e2; color: #dc2626; border: none; }
        .btn-logout:hover { background: #fecaca; color: #991b1b; }
        .task-card { border-left: 4px solid #0d6efd; }
        .status-tab.active { background: #0d6efd; color: white; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column">
            <a href="employee-dashboard.php" class="d-flex align-items-center mb-3 text-decoration-none">
                <img src="../assets/HELIX.png" alt="HELIX Logo" style="height:60px; width:auto; max-width:100%; border-radius:8px; object-fit:contain; margin-right:10px;">
                <div class="fw-bold" style="color:#4f46e5;">HELIX</div>
            </a>
            <nav class="sidebar-nav mt-2">
                <a class="nav-pill" href="employee-dashboard.php"><i class="bi-speedometer2"></i>Dashboard</a>
                <a class="nav-pill" href="attendance.php"><i class="bi-calendar-check"></i>Attendance</a>
                <a class="nav-pill" href="leaves.php"><i class="bi-journal-check"></i>Leaves</a>
                <a class="nav-pill active" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
            </nav>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <main class="col-lg-10 p-4">
            <?php 
            // Include required models
            require_once __DIR__ . '/../backend/models/NotificationModel.php';
            
            // Initialize notification model and get unread count
            $notificationModel = new NotificationModel($db);
            $unreadCount = $notificationModel->getUnreadCount('employee', $empId);
            
            // Set page title and include header component
            $pageTitle = 'My Tasks';
            $pageSubtitle = 'Manage your assigned tasks';

            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Task Status Tabs -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <a href="tasks.php" class="btn btn-outline-primary status-tab <?php echo !$statusFilter ? 'active' : ''; ?>">
                        All (<?php echo $totalCount; ?>)
                    </a>
                    <a href="?status=Pending" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'Pending' ? 'active' : ''; ?>">
                        Pending (<?php echo $taskCounts['Pending']; ?>)
                    </a>
                    <a href="?status=In Progress" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'In Progress' ? 'active' : ''; ?>">
                        In Progress (<?php echo $taskCounts['In Progress']; ?>)
                    </a>
                    <a href="?status=Completed" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'Completed' ? 'active' : ''; ?>">
                        Completed (<?php echo $taskCounts['Completed']; ?>)
                    </a>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="row g-3">
                <?php if ($tasks): ?>
                    <?php foreach ($tasks as $task): ?>
                    <div class="col-md-6">
                        <div class="card task-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($task['Title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($task['Description']); ?></small>
                                    <?php if (!empty($task['Project_name'])): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-primary">
                                                <i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($task['Project_name']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $priorityColor = [
                                    'High' => 'danger',
                                    'Medium' => 'warning',
                                    'Low' => 'success'
                                ];
                                $color = $priorityColor[$task['Priority']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($task['Priority']); ?></span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Due: <?php echo formatDate($task['Due_date']); ?></small>
                            </div>
                            <form method="POST" class="d-flex gap-2 align-items-end">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="task_id" value="<?php echo $task['Task_id']; ?>">
                                <select class="form-select form-select-sm" name="status" onchange="this.parentElement.submit()">
                                    <option value="Pending" <?php echo $task['Status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $task['Status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $task['Status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card p-5 text-center">
                            <p class="text-muted mb-0">No tasks assigned</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></small>
                <div class="btn-group">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/nav-notifications-dot.js"></script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>