<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$empModel = new EmployeeModel($db);
$attendanceModel = new AttendanceModel($db);
$leaveModel = new LeaveModel($db);
$taskModel = new TaskModel($db);

// Get dashboard stats
$totalEmployees = $empModel->getTotalCount();
$activeEmployees = $empModel->getActiveCount();
$pendingLeaves = $leaveModel->getPendingLeavesCount();
$totalTasks = $taskModel->getTotalCount();

// Get recent data
$recentEmployees = $empModel->getAllEmployees(5);
$recentLeaves = $leaveModel->getPendingLeaves(5);
$recentAttendance = $attendanceModel->getAllAttendance(null, null, 5);

$user = Session::getUser();
$initials = getInitials($user['Hr_firstName'] ?? 'HR', $user['Hr_lastName'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Dashboard</title>
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
        .badge-soft { background:rgba(13,110,253,0.12); color:#0d6efd; }
        .table thead { background:#f8fafc; }
        .avatar { width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.alt2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card.alt3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card.alt4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
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
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link" href="employees.php"><i class="bi bi-people me-2"></i>Employees</a>
                <a class="nav-link" href="attendance-admin.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
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
        $pageTitle = 'Dashboard'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="mb-4">
                
                <small class="text-muted">Welcome back! Here's your HR overview</small>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Total Employees</div>
                                <h3 class="mb-0"><span id="totalEmployeesCount"><?php echo $totalEmployees; ?></span></h3>
                            </div>
                            <i class="bi bi-people" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card alt2 p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Active</div>
                                <h3 class="mb-0"><span id="activeEmployeesCount"><?php echo $activeEmployees; ?></span></h3>
                            </div>
                            <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card alt3 p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Pending Leaves</div>
                                <h3 class="mb-0"><span id="pendingLeavesCount"><?php echo $pendingLeaves; ?></span></h3>
                            </div>
                            <i class="bi bi-calendar-check" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card alt4 p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Total Tasks</div>
                                <h3 class="mb-0"><span id="totalTasksCount"><?php echo $totalTasks; ?></span></h3>
                            </div>
                            <i class="bi bi-list-check" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Data -->
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Recent Employees</h6>
                            <a href="employees.php" class="small text-decoration-none">View all</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Joining Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentEmployees as $emp): ?>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <span class="avatar"><?php echo getInitials($emp['Emp_firstName'], $emp['Emp_lastName']); ?></span>
                                        <?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($emp['Emp_email']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['Category_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($emp['Status']); ?></span></td>
                                    <td><?php echo formatDate($emp['Joining_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card p-4">
                        <h6 class="mb-3">Pending Leave Requests</h6>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentLeaves as $l): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($l['Emp_firstName'] . ' ' . $l['Emp_lastName']); ?></div>
                                    <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($l['Leave_type']); ?></span>
                                </div>
                                <small class="text-muted"><?php echo formatDate($l['Start_date']); ?> - <?php echo formatDate($l['End_date']); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
<script>
    async function fetchDashboardStats() {
        try {
            const res = await fetch('../backend/api/dashboard_stats.php', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            };

            setText('totalEmployeesCount', data.totalEmployees ?? 0);
            setText('activeEmployeesCount', data.activeEmployees ?? 0);
            setText('pendingLeavesCount', data.pendingLeaves ?? 0);
            setText('totalTasksCount', data.totalTasks ?? 0);
        } catch (err) {
            console.error('Failed to fetch dashboard stats:', err);
        }
    }

    // Initial fetch and polling every 15 seconds
    fetchDashboardStats();
    setInterval(fetchDashboardStats, 15000);
</script>
</body>
</html>