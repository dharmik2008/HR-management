<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$attendanceModel = new AttendanceModel($db);
$empModel = new EmployeeModel($db);

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Get filter parameters
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t');

// Get attendance data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$attendance = $attendanceModel->getAttendanceByEmployee($empId, $startDate, $endDate, $limit, $offset);
$attendanceSummary = $attendanceModel->getAttendanceSummaryByRange($empId, $startDate, $endDate);
$attendancePercentage = $attendanceModel->getAttendancePercentageByRange($empId, $startDate, $endDate);

// Get all records for pagination
$allAttendance = $attendanceModel->getAttendanceByEmployee($empId, $startDate, $endDate);
$totalCount = count($allAttendance);
$totalPages = ceil($totalCount / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Attendance | HRMS</title>
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
        .table thead { background: #f8fafc; }
        .stat-box { text-align: center; padding: 20px; }
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
                <a class="nav-pill active" href="attendance.php"><i class="bi-calendar-check"></i>Attendance</a>
                <a class="nav-pill" href="leaves.php"><i class="bi-journal-check"></i>Leaves</a>
                <a class="nav-pill" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
            </nav>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">
            <?php 
            // Include required models
            require_once __DIR__ . '/../backend/models/NotificationModel.php';
            
            // Initialize notification model and get unread count
            $notificationModel = new NotificationModel($db);
            $unreadCount = $notificationModel->getUnreadCount('employee', $empId);
            
            // Set page title and include header component
            $pageTitle = 'Attendance';
            $pageSubtitle = 'View and track your attendance records';
            $pageTitle = 'My Attendance';
            $pageSubtitle = 'View your attendance history';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="mt-3">
                <small class="text-muted">Track your daily check-ins and check-outs</small>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card p-3">
                        <div class="stat-box">
                            <h5 class="text-success"><?php echo $attendanceSummary['present'] ?? 0; ?></h5>
                            <small class="text-muted">Present Days</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <div class="stat-box">
                            <h5 class="text-danger"><?php echo $attendanceSummary['absent'] ?? 0; ?></h5>
                            <small class="text-muted">Absent Days</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <div class="stat-box">
                            <h5 class="text-warning"><?php echo $attendanceSummary['wfh'] ?? 0; ?></h5>
                            <small class="text-muted">WFH Days</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <div class="stat-box">
                            <h5 class="text-info"><?php echo round($attendancePercentage, 1); ?>%</h5>
                            <small class="text-muted">Attendance %</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card p-3 mb-3">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="attendance.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Attendance Records</h6>
                    <small class="text-muted">Showing <?php echo count($attendance); ?> of <?php echo $totalCount; ?> records</small>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Duration</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($attendance): ?>
                            <?php foreach ($attendance as $att): ?>
                            <tr>
                                <td><?php echo formatDate($att['Date'], 'D, d M Y'); ?></td>
                                <td>
                                    <?php
                                    $statusBadge = [
                                        'Present' => 'bg-success',
                                        'Absent' => 'bg-danger',
                                        'WFH' => 'bg-warning text-dark',
                                        'Leave' => 'bg-info text-dark'
                                    ];
                                    $badgeClass = $statusBadge[$att['Status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($att['Status']); ?></span>
                                </td>
                                <td><?php echo $att['Checkin_time'] ? formatTime($att['Checkin_time']) : '—'; ?></td>
                                <td><?php echo $att['Checkout_time'] ? formatTime($att['Checkout_time']) : '—'; ?></td>
                                <td>
                                    <?php
                                    if ($att['Checkin_time'] && $att['Checkout_time']) {
                                        $checkin = strtotime($att['Checkin_time']);
                                        $checkout = strtotime($att['Checkout_time']);
                                        $duration = abs($checkout - $checkin) / 3600;
                                        echo number_format($duration, 1) . ' hrs';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No attendance records found</td></tr>
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
                            <a href="?page=<?php echo $page - 1; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/nav-notifications-dot.js"></script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>