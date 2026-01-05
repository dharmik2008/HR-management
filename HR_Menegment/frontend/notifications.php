<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$notificationModel = new NotificationModel($db);
$empModel = new EmployeeModel($db);

$message = '';
$messageType = '';

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'mark_read') {
        $notifId = sanitize($_POST['notif_id'] ?? '');
        if (!empty($notifId)) {
            $notificationModel->markAsRead($notifId);
        }
    } elseif ($action === 'mark_unread') {
        $notifId = sanitize($_POST['notif_id'] ?? '');
        if (!empty($notifId)) {
            $notificationModel->markAsUnread($notifId);
        }
    } elseif ($action === 'mark_all_read') {
        $notificationModel->markAllAsRead('employee', $empId);
        $message = 'All notifications marked as read';
        $messageType = 'success';
    } elseif ($action === 'delete') {
        $notifId = sanitize($_POST['notif_id'] ?? '');
        if (!empty($notifId)) {
            $notificationModel->deleteNotification($notifId);
            $message = 'Notification deleted';
            $messageType = 'success';
        }
    }
}

// Get notifications
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$allNotifications = $notificationModel->getNotifications('employee', $empId, false, 1000);
$totalCount = count($allNotifications);
$totalPages = ceil($totalCount / $limit);

$notifications = array_slice($allNotifications, $offset, $limit);
$unreadCount = $notificationModel->getUnreadCount('employee', $empId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Notifications | HRMS</title>
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
        .notification-item { border-left: none; }
        .notification-item.unread { background: #f0f6ff; }
        .unread-badge-btn { width: 10px; height: 10px; border-radius: 50%; background: #0d6efd; display: inline-block; border: none; padding: 0; cursor: pointer; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-lg-2 sidebar p-4">
            <a href="employee-dashboard.php" class="d-flex align-items-center mb-3 text-decoration-none">
                <img src="../assets/HELIX.png" alt="HELIX Logo" style="height:60px; width:auto; max-width:100%; border-radius:8px; object-fit:contain; margin-right:10px;">
                <div class="fw-bold" style="color:#4f46e5;">HELIX</div>
            </a>
            <nav class="sidebar-nav mt-2">
                <a class="nav-pill" href="employee-dashboard.php"><i class="bi-speedometer2"></i>Dashboard</a>
                <a class="nav-pill" href="attendance.php"><i class="bi-calendar-check"></i>Attendance</a>
                <a class="nav-pill" href="leaves.php"><i class="bi-journal-check"></i>Leaves</a>
                <a class="nav-pill" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
                <a class="nav-pill active" href="notifications.php"><i class="bi-bell"></i>Notifications</a>
            </nav>
            <div class="sidebar-actions">
                <a href="logout.php" class="btn btn-logout w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <i class="bi-box-arrow-right"></i>Logout
                </a>
            </div>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">
            <?php 
            $pageTitle = 'Notifications';
            $pageSubtitle = 'Stay updated with your HR activities';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="d-flex justify-content-end mb-3">
                <?php if ($unreadCount > 0): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Mark all as read</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Unread Count -->
            <?php if ($unreadCount > 0): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> You have <strong><?php echo $unreadCount; ?> unread notification<?php echo $unreadCount !== 1 ? 's' : ''; ?></strong>
            </div>
            <?php endif; ?>

            <!-- Notifications List -->
            <div class="card">
                <?php if ($notifications): ?>
                    <div class="list-group list-group-flush" style="max-height: 65vh; overflow-y: auto;">
                        <?php foreach ($notifications as $notif): ?>
                        <div class="list-group-item notification-item <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?> p-3 d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    <?php if ($notif['Status'] === 'Unread'): ?>
                                    <form method="POST" class="d-inline me-2" style="line-height: 0;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notif_id" value="<?php echo $notif['Notification_id']; ?>">
                                        <button type="submit" class="unread-badge-btn" title="Mark as read"></button>
                                    </form>
                                    <?php endif; ?>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($notif['Title']); ?></h6>
                                </div>
                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($notif['Message']); ?></p>
                                <small class="text-muted"><?php echo formatDate($notif['Created_at'], 'd M Y H:i'); ?></small>
                            </div>
                            <div class="dropdown ms-2">
                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($notif['Status'] === 'Unread'): ?>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="notif_id" value="<?php echo $notif['Notification_id']; ?>">
                                            <button type="submit" class="dropdown-item">Mark as read</button>
                                        </form>
                                    </li>
                                    <?php else: ?>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="mark_unread">
                                            <input type="hidden" name="notif_id" value="<?php echo $notif['Notification_id']; ?>">
                                            <button type="submit" class="dropdown-item">Mark as unread</button>
                                        </form>
                                    </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="notif_id" value="<?php echo $notif['Notification_id']; ?>">
                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center">
                        <i class="bi bi-bell-slash" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block;"></i>
                        <p class="text-muted mb-0">No notifications yet</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></small>
                <div class="btn-group">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
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