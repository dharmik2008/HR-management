<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$notification = new NotificationModel($db);
$leaveModel = new LeaveModel($db);
$taskModel = new TaskModel($db);
$projectModel = new ProjectModel($db);

$hrId = Session::getUserId();

// Handle deletion
if (isset($_GET['delete'])) {
    $notifId = intval($_GET['delete']);
    if ($notification->deleteNotification($notifId)) {
        header('Location: notifications-admin.php');
        exit;
    }
}

// Get all admin notifications
$allNotifications = $notification->getNotifications('admin', $hrId);

// Categorize notifications
$leaveRequests = [];
$taskUpdates = [];
$projectUpdates = [];
$otherNotifications = [];

foreach ($allNotifications as $notif) {
    if (strpos($notif['Title'], 'Leave') !== false || strpos($notif['Message'], 'leave') !== false) {
        $leaveRequests[] = $notif;
    } elseif (strpos($notif['Title'], 'Task') !== false || strpos($notif['Message'], 'task') !== false) {
        $taskUpdates[] = $notif;
    } elseif (strpos($notif['Title'], 'Project') !== false || strpos($notif['Message'], 'project') !== false) {
        $projectUpdates[] = $notif;
    } else {
        $otherNotifications[] = $notif;
    }
}

$user = Session::getUser();
$initials = getInitials($user['Hr_firstName'] ?? 'HR', $user['Hr_lastName'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Notifications | HRMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dark-mode.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        body { background:#f6f8fb; font-family:"Inter", system-ui, -apple-system, sans-serif; }
        .sidebar { min-height:100vh; background:#fff; border-right:1px solid #e5e7eb; }
        .brand { font-weight:700; color:#0d6efd; text-decoration:none; display:flex; align-items:center; gap:10px; }
        .brand span { width:36px; height:36px; border-radius:10px; background:#0d6efd; color:#fff; display:inline-flex; align-items:center; justify-content:center; }
        .nav-link { color:#4b5563; border-radius:10px; }
        .nav-link.active { background:#0d6efd; color:#fff; }
        .nav-link:hover { background:#e9f2ff; color:#0d6efd; }
        .card { border:0; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.05); }
        .notification-item { padding:16px; border-bottom:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px; background:#f9fafb; }
        .notification-item.unread { background:#e7f1ff; border-left:4px solid #0d6efd; }
        .notification-item:hover { background:#f0f5ff; }
        .badge-leave { background:#fef3c7; color:#92400e; }
        .badge-task { background:#dbeafe; color:#1e40af; }
        .badge-project { background:#dcfce7; color:#166534; }
        .avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; }
        .btn-logout { background: #fee2e2; color: #dc2626; border: none; }
        .btn-logout:hover { background: #fecaca; color: #991b1b; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-2 col-md-3 sidebar p-3 d-flex flex-column">
            <a href="dashboard.php" class="d-flex align-items-center mb-4 text-decoration-none">
                <img src="../assets/HELIX.png" alt="HELIX Logo" style="height:60px; width:auto; max-width:100%; border-radius:8px; object-fit:contain; margin-right:10px;">
                <div class="fw-bold" style="color:#4f46e5;">HELIX</div>
            </a>
            <div class="nav flex-column gap-1">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link" href="employees.php"><i class="bi bi-people me-2"></i>Employees</a>
                <a class="nav-link" href="attendance-admin.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
                <a class="nav-link" href="leaves.php"><i class="bi bi-calendar2-check me-2"></i>Leaves</a>
                <a class="nav-link" href="project-allocation.php"><i class="bi bi-kanban me-2"></i>Projects</a>
                <a class="nav-link" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link active" href="notifications-admin.php"><i class="bi bi-bell me-2"></i>Notifications</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <main class="col-lg-10 col-md-9 p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="ms-auto d-flex align-items-center gap-2">
<button class="theme-toggle-btn btn btn-ghost d-flex align-items-center justify-content-center me-2" type="button" 
                    aria-label="Toggle theme" 
                    style="width:40px;height:40px;border-radius:10px;padding:0;border:none;">
                <span class="theme-icon" style="font-size:1.2rem;">☀️</span>
            </button>
                    <a href="../frontend/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">Notifications</h4>
                    <small class="text-muted">View all employee requests and updates</small>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()"><i class="bi-arrow-clockwise me-1"></i>Refresh</button>
                </div>
            </div>

            <!-- Leave Requests -->
            <div class="mb-4">
                <h6 class="mb-3"><i class="bi-calendar-check text-warning me-2"></i>Leave Requests (<?php echo count($leaveRequests); ?>)</h6>
                <div class="card p-4">
                    <?php if (empty($leaveRequests)): ?>
                        <p class="text-muted text-center py-3">No leave requests</p>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $notif): ?>
                            <div class="notification-item <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($notif['Message']); ?></small>
                                        <div class="mt-2">
                                            <span class="badge badge-leave"><?php echo htmlspecialchars($notif['Status']); ?></span>
                                            <span class="text-muted ms-2" style="font-size:0.85rem;"><?php echo formatDate($notif['Created_at'], 'd M Y, h:i A'); ?></span>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete this notification?')) { window.location.href='?delete=<?php echo $notif['Notification_id']; ?>'; }"><i class="bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Task Updates -->
            <div class="mb-4">
                <h6 class="mb-3"><i class="bi-card-checklist text-info me-2"></i>Task Updates (<?php echo count($taskUpdates); ?>)</h6>
                <div class="card p-4">
                    <?php if (empty($taskUpdates)): ?>
                        <p class="text-muted text-center py-3">No task updates</p>
                    <?php else: ?>
                        <?php foreach ($taskUpdates as $notif): ?>
                            <div class="notification-item <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($notif['Message']); ?></small>
                                        <div class="mt-2">
                                            <span class="badge badge-task"><?php echo htmlspecialchars($notif['Status']); ?></span>
                                            <span class="text-muted ms-2" style="font-size:0.85rem;"><?php echo formatDate($notif['Created_at'], 'd M Y, h:i A'); ?></span>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete this notification?')) { window.location.href='?delete=<?php echo $notif['Notification_id']; ?>'; }"><i class="bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Project Updates -->
            <div class="mb-4">
                <h6 class="mb-3"><i class="bi-kanban text-success me-2"></i>Project Updates (<?php echo count($projectUpdates); ?>)</h6>
                <div class="card p-4">
                    <?php if (empty($projectUpdates)): ?>
                        <p class="text-muted text-center py-3">No project updates</p>
                    <?php else: ?>
                        <?php foreach ($projectUpdates as $notif): ?>
                            <div class="notification-item <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($notif['Message']); ?></small>
                                        <div class="mt-2">
                                            <span class="badge badge-project"><?php echo htmlspecialchars($notif['Status']); ?></span>
                                            <span class="text-muted ms-2" style="font-size:0.85rem;"><?php echo formatDate($notif['Created_at'], 'd M Y, h:i A'); ?></span>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete this notification?')) { window.location.href='?delete=<?php echo $notif['Notification_id']; ?>'; }"><i class="bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Other Notifications -->
            <?php if (!empty($otherNotifications)): ?>
            <div class="mb-4">
                <h6 class="mb-3"><i class="bi-inbox text-secondary me-2"></i>Other Notifications (<?php echo count($otherNotifications); ?>)</h6>
                <div class="card p-4">
                    <?php foreach ($otherNotifications as $notif): ?>
                        <div class="notification-item <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($notif['Message']); ?></small>
                                    <div class="mt-2">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($notif['Status']); ?></span>
                                        <span class="text-muted ms-2" style="font-size:0.85rem;"><?php echo formatDate($notif['Created_at'], 'd M Y, h:i A'); ?></span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Delete this notification?')) { window.location.href='?delete=<?php echo $notif['Notification_id']; ?>'; }"><i class="bi-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>
