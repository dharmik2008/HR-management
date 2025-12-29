<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$projectModel = new ProjectModel($db);
$empModel = new EmployeeModel($db);

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Get projects assigned to this employee
$projects = $projectModel->getProjectsByEmployee($empId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Projects | HRMS</title>
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
        .project-card { border-left: 4px solid #0d6efd; transition: transform 0.2s; }
        .project-card:hover { transform: translateY(-2px); }
        .progress-bar { background: linear-gradient(90deg, #0d6efd, #5c9dff); }
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
                <a class="nav-pill" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill active" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
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
            $pageTitle = 'Projects';
            $pageSubtitle = 'View your assigned projects';
            $pageTitle = 'My Projects';
            $pageSubtitle = 'Track your project contributions';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="mt-3">
                <small class="text-muted">View your assigned projects</small>
            </div>

            <!-- Projects Grid -->
            <div class="row g-3 mt-2">
                <?php if ($projects): ?>
                    <?php foreach ($projects as $project): ?>
                    <div class="col-md-6">
                        <div class="card project-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($project['Project_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($project['Category_name'] ?? 'Uncategorized'); ?></small>
                                </div>
                                <?php
                                $statusColor = [
                                    'Active' => 'success',
                                    'Paused' => 'warning',
                                    'Completed' => 'info'
                                ];
                                $color = $statusColor[$project['Status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($project['Status']); ?></span>
                            </div>
                            
                            <p class="text-muted small"><?php echo htmlspecialchars($project['Description'] ?? 'No description'); ?></p>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Duration</small>
                                    <?php
                                    if ($project['Start_date'] && $project['End_date']) {
                                        $start = new DateTime($project['Start_date']);
                                        $end = new DateTime($project['End_date']);
                                        $today = new DateTime();
                                        $total = $start->diff($end)->days;
                                        $elapsed = $start->diff($today)->days;
                                        $progress = min(100, max(0, ($elapsed / $total) * 100));
                                    } else {
                                        $progress = 0;
                                    }
                                    ?>
                                    <small><?php echo round($progress, 0); ?>%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo round($progress, 0); ?>%"></div>
                                </div>
                            </div>

                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <small class="text-muted">Start</small>
                                    <div class="fw-semibold"><?php echo formatDate($project['Start_date']); ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">End</small>
                                    <div class="fw-semibold"><?php echo formatDate($project['End_date']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card p-5 text-center">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                            <p class="text-muted mb-0">No projects assigned yet</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/nav-notifications-dot.js"></script>
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>