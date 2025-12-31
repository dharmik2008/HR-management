<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$leaveModel = new LeaveModel($db);
$empModel = new EmployeeModel($db);
$notificationModel = new NotificationModel($db);

$message = '';
$messageType = '';

// Handle approve / reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $leaveId = sanitize($_POST['leave_id'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if ($action === 'update_status' && $leaveId && in_array($status, ['Approved', 'Rejected'])) {
        $leaveData = $leaveModel->getLeaveById($leaveId);
        $result = $leaveModel->updateLeaveStatus($leaveId, $status, Session::getUserId());
        $message = $result ? "Leave $status successfully!" : "Failed to update leave status";
        $messageType = $result ? 'success' : 'danger';

        if ($result && $leaveData) {
            $title = "Leave $status";
            $start = $leaveData['Start_date'] ?? '';
            $end = $leaveData['End_date'] ?? '';
            $dateRange = trim($start) && trim($end)
                ? 'from ' . date('M d, Y', strtotime($start)) . ' to ' . date('M d, Y', strtotime($end))
                : '';
            $notificationModel->createNotification(
                'employee',
                $leaveData['Emp_id'],
                $title,
                "Your leave request $dateRange has been $status."
            );
        }
    }
}

// Filters and pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : null;

$leaves = $leaveModel->getAllLeaves($statusFilter ?: null, $limit, $offset);
$totalCount = count($leaveModel->getAllLeaves($statusFilter ?: null));
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Leaves</title>
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
        .table thead { background:#f8fafc; }
        .avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size: 0.75rem; }
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
                <a class="nav-link active" href="leaves.php"><i class="bi bi-calendar2-check me-2"></i>Leaves</a>
                <a class="nav-link" href="project-allocation.php"><i class="bi bi-kanban me-2"></i>Projects</a>
                <a class="nav-link" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Leave Requests'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Review and approve employee leaves</small>
                </div>
                <form class="d-flex align-items-center gap-2" method="GET">
                    <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </form>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($leaves): ?>
                            <?php foreach ($leaves as $lv): ?>
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <span class="avatar"><?php echo getInitials($lv['Emp_firstName'], $lv['Emp_lastName']); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($lv['Emp_firstName'] . ' ' . $lv['Emp_lastName']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($lv['Emp_email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($lv['Leave_type']); ?></td>
                                <td><?php echo formatDate($lv['Start_date']); ?> - <?php echo formatDate($lv['End_date']); ?></td>
                                <td><?php echo htmlspecialchars($lv['Days']); ?></td>
                                <td>
                                    <?php
                                    $statusBadge = [
                                        'Pending' => 'bg-warning text-dark',
                                        'Approved' => 'bg-success',
                                        'Rejected' => 'bg-danger'
                                    ];
                                    $badge = $statusBadge[$lv['Status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($lv['Status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($lv['Status'] === 'Pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="leave_id" value="<?php echo $lv['Leave_id']; ?>">
                                            <input type="hidden" name="status" value="Approved">
                                            <button class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="leave_id" value="<?php echo $lv['Leave_id']; ?>">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <small class="text-muted">No actions</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No leave requests found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <small class="text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></small>
                    <div class="btn-group">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($statusFilter ?? ''); ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($statusFilter ?? ''); ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>

