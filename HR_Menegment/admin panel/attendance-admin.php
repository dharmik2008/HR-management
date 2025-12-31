<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$attendanceModel = new AttendanceModel($db);
$empModel = new EmployeeModel($db);
$notificationModel = new NotificationModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'mark') {
        $empId = sanitize($_POST['emp_id'] ?? '');
        $date = sanitize($_POST['date'] ?? '');
        $status = sanitize($_POST['status'] ?? '');
        $checkinTime = sanitize($_POST['checkin_time'] ?? '');
        $checkoutTime = sanitize($_POST['checkout_time'] ?? '');
        
        if (empty($empId) || empty($date) || empty($status)) {
            $message = 'Employee, date, and status are required';
            $messageType = 'danger';
        } else {
            $userId = Session::getUserId();
            $result = $attendanceModel->markAttendance(
                $empId, 
                $date, 
                $status, 
                $checkinTime ?: null, 
                $checkoutTime ?: null, 
                $userId
            );
            
            $message = $result ? 'Attendance marked successfully!' : 'Failed to mark attendance';
            $messageType = $result ? 'success' : 'danger';

            if ($result) {
                $readableDate = $date ? date('M d, Y', strtotime($date)) : 'the selected date';
                $notificationModel->createNotification(
                    'employee',
                    $empId,
                    'Attendance Updated',
                    'Your attendance for ' . $readableDate . ' has been marked as ' . $status . '.'
                );
            }
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t');

$attendance = $attendanceModel->getAllAttendance($startDate, $endDate, null, $limit, $offset);
$employees = $empModel->getAllEmployees(1000);
$totalCount = $attendanceModel->getTotalCount($startDate, $endDate);
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Attendance</title>
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
        .avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size: 0.8rem; }
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
                <a class="nav-link active" href="attendance-admin.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
                <a class="nav-link" href="leaves.php"><i class="bi bi-calendar2-check me-2"></i>Leaves</a>
                <a class="nav-link" href="project-allocation.php"><i class="bi bi-kanban me-2"></i>Projects</a>
                <a class="nav-link" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Attendance Management'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Mark and monitor employee attendance</small>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">+ Mark Attendance</button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

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
                        <a href="attendance-admin.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Duration</th>
                            <th>Marked By</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($attendance): ?>
                            <?php foreach ($attendance as $att): ?>
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <span class="avatar"><?php echo getInitials($att['Emp_firstName'], $att['Emp_lastName']); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($att['Emp_firstName'] . ' ' . $att['Emp_lastName']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($att['Emp_email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo formatDate($att['Date'], 'd M Y'); ?></td>
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
                                <td>HR Admin</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editAttendance(<?php echo htmlspecialchars(json_encode($att)); ?>)" data-bs-toggle="modal" data-bs-target="#markAttendanceModal">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No attendance records found</td></tr>
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
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="mark">
                
                <div class="modal-header">
                    <h5 class="modal-title">Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select class="form-select" name="emp_id" id="attEmpId" required>
                            <option value="">Select employee...</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['Emp_id']; ?>"><?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" id="attDate" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="attStatus" required>
                            <option value="">Select status...</option>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="WFH">Work From Home</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check-in Time</label>
                        <input type="time" class="form-control" name="checkin_time" id="attCheckin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check-out Time</label>
                        <input type="time" class="form-control" name="checkout_time" id="attCheckout">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAttendance(att) {
    document.getElementById('attEmpId').value = att.Emp_id;
    document.getElementById('attDate').value = att.Date;
    document.getElementById('attStatus').value = att.Status;
    document.getElementById('attCheckin').value = att.Checkin_time || '';
    document.getElementById('attCheckout').value = att.Checkout_time || '';
}

document.getElementById('markAttendanceModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('attEmpId').value = '';
    document.getElementById('attDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('attStatus').value = '';
    document.getElementById('attCheckin').value = '';
    document.getElementById('attCheckout').value = '';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>