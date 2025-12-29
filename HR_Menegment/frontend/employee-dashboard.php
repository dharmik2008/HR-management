<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$employee = new EmployeeModel($db);
$attendance = new AttendanceModel($db);
$leave = new LeaveModel($db);
$task = new TaskModel($db);
$document = new DocumentModel($db);
$notification = new NotificationModel($db);

// Get employee data
$empData = $employee->getEmployeeById($empId);

// Get attendance stats
$attendanceStats = $attendance->getAttendanceStats($empId);
$leaveBalance = $leave->getLeaveBalance($empId);
$weeklyAttendance = $attendance->getWeeklyAttendance($empId);
$myTasks = $task->getTasksByEmployee($empId, null, null, 5, 0);
$myLeaves = $leave->getLeavesByEmployee($empId, 5);
$myDocuments = $document->getDocumentsByEmployee($empId, 3);
$myNotifications = $notification->getNotifications('employee', $empId, false, 5);

// Calculate attendance percentage
$attendancePercentage = $attendance->getAttendancePercentage($empId);

$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Employee Dashboard | HRMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/dark-mode.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            font-family: "Inter", system-ui, -apple-system, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: #ffffff;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            border-right: 1px solid #e5e7f0;
        }
        .sidebar a {
            color: #0f172a;
            text-decoration: none;
        }
        .sidebar a.active,
        .sidebar a:hover {
            color: #0d6efd;
        }
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #5c9dff);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
        }
        .sidebar .avatar {
            width: 52px;
            height: 52px;
        }
        .sidebar-header small { color: #6b7280; }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        .nav-pill {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.7rem 0.9rem;
            border-radius: 12px;
            font-weight: 600;
            color: #0f172a;
            transition: 0.2s ease;
        }
        .nav-pill i { font-size: 1.05rem; opacity: 0.9; color: #0d6efd; }
        .nav-pill:hover { background: #e7f1ff; color: #0d6efd; }
        .nav-pill.active {
            background: #e7f1ff;
            color: #0d6efd;
            box-shadow: 0 10px 25px rgba(13,110,253,0.15);
        }
        .sidebar-actions {
            margin-top: auto;
            display: grid;
            gap: 0.6rem;
        }
        .btn-ghost {
            background: rgba(13,110,253,0.08);
            color: #0d6efd;
            border: 1px solid rgba(13,110,253,0.15);
        }
        .btn-ghost:hover { background: rgba(13,110,253,0.15); color: #0d6efd; }
        .progress-thin {
            height: 6px;
        }
        .badge-soft {
            background: rgba(13,110,253,0.1);
            color: #0d6efd;
        }
        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            border: none;
        }
        .btn-logout:hover {
            background: #fecaca;
            color: #991b1b;
        }
        /* Prevent theme toggle button from overlaying notifications */
        /* Add padding-right to notifications card to prevent overlay from fixed theme button */
        /* Only apply on screens where overlap could occur */
        @media (max-width: 1600px) {
            .notifications-card {
                padding-right: 75px !important;
            }
        }
        @media (min-width: 1601px) {
            .notifications-card {
                padding-right: 1rem !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <aside class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column">
            <a href="employee-dashboard.php" class="d-flex align-items-center mb-3 text-decoration-none">
                <img src="../assets/HELIX.png" alt="HELIX Logo" style="height:60px; width:auto; max-width:100%; border-radius:8px; object-fit:contain; margin-right:10px;">
                <div class="fw-bold" style="color:#4f46e5;">HELIX</div>
            </a>
            <nav class="sidebar-nav mt-2">
                <a class="nav-pill active" href="employee-dashboard.php"><i class="bi-speedometer2"></i>Dashboard</a>
                <a class="nav-pill" href="attendance.php"><i class="bi-calendar-check"></i>Attendance</a>
                <a class="nav-pill" href="leaves.php"><i class="bi-journal-check"></i>Leaves</a>
                <a class="nav-pill" href="tasks.php"><i class="bi-list-task"></i>Tasks</a>
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
            </nav>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <!-- Main content -->
        <main class="col-md-9 col-lg-10 p-4">
            <?php 
            // Include required models
            require_once __DIR__ . '/../backend/models/NotificationModel.php';
            
            // Initialize notification model and get unread count
            $notificationModel = new NotificationModel($db);
            $unreadCount = $notificationModel->getUnreadCount('employee', $empId);
            
            // Set page title and include header component
            $pageTitle = 'Employee Dashboard';
            $pageSubtitle = 'Welcome back! Here\'s your overview';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="mt-3">
                <!-- DEBUG: Profile Pic: <?php echo htmlspecialchars($empData['Profile_pic'] ?? 'NULL'); ?> -->
                <!-- DEBUG: URL: <?php echo htmlspecialchars($profilePicUrl ?? 'NULL'); ?> -->
                <small class="text-muted">Overview of your workday, tasks and requests</small>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted">Attendance</div>
                                <h5 class="mb-0"><?php echo round($attendancePercentage, 1); ?>%</h5>
                            </div>
                            <span class="badge bg-success">On time</span>
                        </div>
                        <div class="progress progress-thin mt-3">
                            <div class="progress-bar bg-success" style="width: <?php echo round($attendancePercentage, 1); ?>%;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card p-3">
                        <div class="text-muted">Leave Balance</div>
                        <h5 class="mb-2"><?php echo $leaveBalance['remaining']; ?> days</h5>
                        <span class="badge bg-info text-dark"><?php echo $leaveBalance['used']; ?> used</span>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card p-3">
                        <div class="text-muted">Tasks</div>
                        <h5 class="mb-2"><?php echo count($myTasks); ?> assigned</h5>
                        <span class="badge bg-warning text-dark">View all</span>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card p-3">
                        <div class="text-muted">Salary</div>
                        <h5 class="mb-2">₹<?php echo number_format($empData['Salary'] ?? 0, 0); ?></h5>
                        <span class="badge bg-primary">Per month</span>
                    </div>
                </div>
            </div>

            <!-- Two column layout -->
            <div class="row g-3">
                <div class="col-lg-8">
                    <!-- Attendance -->
                    <div class="card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">This Week Attendance</h6>
                            <a href="attendance.php" class="small text-decoration-none">View all</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($weeklyAttendance): ?>
                                    <?php foreach ($weeklyAttendance as $att): ?>
                                    <tr>
                                        <td><?php echo formatDate($att['Date'], 'D, d M'); ?></td>
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
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No attendance records</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tasks -->
                    <div class="card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">My Tasks</h6>
                            <a href="tasks.php" class="small text-decoration-none">View all</a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($myTasks): ?>
                                <?php foreach ($myTasks as $t): ?>
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($t['Title']); ?></div>
                                        <small class="text-muted">Due <?php echo formatDate($t['Due_date']); ?> • <?php echo htmlspecialchars($t['Priority']); ?> priority</small>
                                        <?php if (!empty($t['Project_name'])): ?>
                                            <small class="text-primary d-block mt-1">
                                                <i class="bi bi-diagram-3"></i> <?php echo htmlspecialchars($t['Project_name']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $statusMap = ['Pending' => 'bg-danger', 'In Progress' => 'bg-warning text-dark', 'Completed' => 'bg-success'];
                                    $badgeClass = $statusMap[$t['Status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($t['Status']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No tasks assigned</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="card p-3 mb-3 notifications-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Notifications</h6>
                            <a href="notifications.php" class="small text-decoration-none">View all</a>
                        </div>
                        <div class="list-group list-group-flush" style="max-height: 50vh; overflow-y: auto;">
                            <?php if ($myNotifications): ?>
                                <?php foreach ($myNotifications as $notif): ?>
                                <div class="list-group-item">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($notif['Title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($notif['Message']); ?> • <?php echo formatDate($notif['Created_at']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No notifications</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Leave requests -->
                    <div class="card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Leave Requests</h6>
                            <a href="leaves.php" class="small text-decoration-none">History</a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($myLeaves): ?>
                                <?php foreach ($myLeaves as $l): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($l['Leave_type']); ?></div>
                                        <?php
                                        $statusMap = ['Pending' => 'bg-warning text-dark', 'Approved' => 'bg-success', 'Rejected' => 'bg-danger', 'In review' => 'bg-info text-dark'];
                                        $badgeClass = $statusMap[$l['Status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($l['Status']); ?></span>
                                    </div>
                                    <small class="text-muted"><?php echo formatDate($l['Start_date']); ?> – <?php echo formatDate($l['End_date']); ?> (<?php echo $l['Days']; ?> days)</small>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No leave requests</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Documents</h6>
                            <a href="documents.php" class="small text-decoration-none">Upload</a>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if ($myDocuments): ?>
                                <?php foreach ($myDocuments as $doc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($doc['File_name']); ?></div>
                                        <small class="text-muted"><?php echo strtoupper($doc['File_type']); ?> • <?php echo formatDate($doc['Uploaded_at']); ?></small>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($doc['File_path']); ?>" class="badge badge-soft" download>Download</a>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No documents</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/nav-notifications-dot.js"></script>
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>