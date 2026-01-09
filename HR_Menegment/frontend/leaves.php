<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$leaveModel = new LeaveModel($db);
$empModel = new EmployeeModel($db);

$empId = Session::getUserId();
$empData = $empModel->getEmployeeById($empId);

$message = '';
$messageType = '';

$messageType = '';

$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Handle leave request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    if ($action === 'create') {
        $leaveType = sanitize($_POST['leave_type'] ?? '');
        $startDate = sanitize($_POST['start_date'] ?? '');
        $endDate = sanitize($_POST['end_date'] ?? '');
        $comment = sanitize($_POST['comment'] ?? '');
        
        if (empty($leaveType) || empty($startDate) || empty($endDate)) {
            $message = 'Leave type, start date, and end date are required';
            $messageType = 'danger';
        } else {
            try {
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
                if ($end < $start) {
                    $message = 'End date must be after start date';
                    $messageType = 'danger';
                } else {
                    $interval = $start->diff($end);
                    $days = $interval->days + 1;
                    $created = $leaveModel->applyLeave($empId, $leaveType, $startDate, $endDate, $days, $comment ?: null, null);
                    if ($created) {
                        $message = 'Leave request submitted successfully';
                $messageType = 'success';
            } else {
                        $message = 'Failed to submit leave request';
                        $messageType = 'danger';
                    }
                }
            } catch (Exception $e) {
                $message = 'Invalid date format';
                $messageType = 'danger';
            }
        }
    }
}

// Pagination and optional status filter for this employee
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : null;

$allLeaves = $leaveModel->getLeavesByEmployee($empId);
if ($statusFilter) {
    $allLeaves = array_values(array_filter($allLeaves, fn($l) => $l['Status'] === $statusFilter));
}
$totalCount = count($allLeaves);
$totalPages = max(1, ceil($totalCount / $limit));
$leaves = array_slice($allLeaves, $offset, $limit);

$leaveCounts = [
    'Pending' => count(array_filter($leaveModel->getLeavesByEmployee($empId), fn($l) => $l['Status'] === 'Pending')),
    'Approved' => count(array_filter($leaveModel->getLeavesByEmployee($empId), fn($l) => $l['Status'] === 'Approved')),
    'Rejected' => count(array_filter($leaveModel->getLeavesByEmployee($empId), fn($l) => $l['Status'] === 'Rejected'))
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Leaves | HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body { background:#fff; font-family:"Inter", system-ui, -apple-system, sans-serif; }
        .sidebar { min-height:100vh; background:#fff; color:#0f172a; display:flex; flex-direction:column; gap:1.5rem; border-right:1px solid #e5e7f0; }
        .sidebar a { color:#0f172a; text-decoration:none; }
        .sidebar a.active, .sidebar a:hover { color:#0d6efd; }
        .card { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .avatar { width:48px; height:48px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:600; }
        .nav-pill { display:flex; align-items:center; gap:0.65rem; padding:0.7rem 0.9rem; border-radius:12px; font-weight:600; color:#0f172a; transition:0.2s ease; }
        .nav-pill i { font-size:1.05rem; opacity:0.9; color:#0d6efd; }
        .nav-pill:hover { background:#e7f1ff; color:#0d6efd; }
        .nav-pill.active { background:#e7f1ff; color:#0d6efd; box-shadow:0 10px 25px rgba(13,110,253,0.15); }
        .sidebar-actions { margin-top:auto; display:grid; gap:0.6rem; }
        .btn-logout { background:#fee2e2; color:#dc2626; border:none; }
        .btn-logout:hover { background:#fecaca; color:#991b1b; }
        .status-tab.active { background:#0d6efd; color:white; }
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
                <a class="nav-pill active" href="leaves.php"><i class="bi-journal-check"></i>Leaves</a>
                <a class="nav-pill" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
            </nav>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <main class="col-lg-10 col-md-9 p-4">
            <?php 
            // Include required models
            require_once __DIR__ . '/../backend/models/NotificationModel.php';
            
            // Initialize notification model and get unread count
            $notificationModel = new NotificationModel($db);
            $unreadCount = $notificationModel->getUnreadCount('employee', $empId);
            
            // Set page title and include header component
            $pageTitle = 'My Leaves';
            $pageSubtitle = 'Manage your leave requests';
            $pageTitle = 'Leave Management';
            $pageSubtitle = 'Apply for leaves and track status';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                <small class="text-muted">View your leave requests and statuses</small>
                <a href="leaves.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi-arrow-clockwise"></i> Refresh
                </a>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Request Leave Form -->
            <div class="card p-3 mb-4">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="create">
                    <div class="col-md-3">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type" class="form-select" required>
                            <option value="">Select</option>
                            <option value="Sick">Sick</option>
                            <option value="Casual">Casual</option>
                            <option value="Vacation">Vacation</option>
                            <option value="WFH">WFH</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comment (optional)</label>
                        <textarea name="comment" class="form-control" rows="2" placeholder="Add a note"></textarea>
                    </div>
                </form>
            </div>

            <!-- Status Tabs -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <a href="leaves.php" class="btn btn-outline-primary status-tab <?php echo !$statusFilter ? 'active' : ''; ?>">
                        All (<?php echo $totalCount; ?>)
                    </a>
                    <a href="?status=Pending" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'Pending' ? 'active' : ''; ?>">
                        Pending (<?php echo $leaveCounts['Pending']; ?>)
                    </a>
                    <a href="?status=Approved" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'Approved' ? 'active' : ''; ?>">
                        Approved (<?php echo $leaveCounts['Approved']; ?>)
                    </a>
                    <a href="?status=Rejected" class="btn btn-outline-primary status-tab <?php echo $statusFilter === 'Rejected' ? 'active' : ''; ?>">
                        Rejected (<?php echo $leaveCounts['Rejected']; ?>)
                    </a>
                </div>
            </div>

            <!-- Leaves Table -->
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Comment</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($leaves): ?>
                            <?php foreach ($leaves as $leave): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($leave['Leave_type']); ?></td>
                                <td>
                                    <small><?php echo formatDate($leave['Start_date']); ?> to <?php echo formatDate($leave['End_date']); ?></small>
                                </td>
                                <td><span class="badge bg-info text-dark"><?php echo $leave['Days']; ?> days</span></td>
                                <td>
                                    <?php
                                    $statusBadge = [
                                        'Pending' => 'bg-warning text-dark',
                                        'Approved' => 'bg-success',
                                        'Rejected' => 'bg-danger'
                                    ];
                                    $badgeClass = $statusBadge[$leave['Status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($leave['Status']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($leave['Comment'] ?? '—'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No leave requests found</td></tr>
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
                            <a href="?page=<?php echo $page - 1; ?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js"></script>
<script>
    // Prevent selecting previous dates and ensure end date is after start date
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.addEventListener('change', function() {
        if (this.value) {
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        }
    });
</script>
</body>
</html>