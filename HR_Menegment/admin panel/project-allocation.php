<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$projectModel = new ProjectModel($db);
$empModel = new EmployeeModel($db);
$categoryModel = new ProjectCategoryModel($db);
$notificationModel = new NotificationModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'create') {
        $projectName = sanitize($_POST['project_name'] ?? '');
        $categoryId = sanitize($_POST['category_id'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $startDate = sanitize($_POST['start_date'] ?? '');
        $endDate = sanitize($_POST['end_date'] ?? '');
        $employees = isset($_POST['employees']) ? array_map('sanitize', $_POST['employees']) : [];
        
        if (empty($projectName) || empty($categoryId) || empty($startDate) || empty($endDate)) {
            $message = 'Project name, category, start date, and end date are required';
            $messageType = 'danger';
        } else {
            $projectId = $projectModel->createProject($projectName, $categoryId, $description, $startDate, $endDate);
            
            if ($projectId) {
                foreach ($employees as $empId) {
                    if ($projectModel->assignEmployee($projectId, $empId)) {
                        $notificationModel->createNotification(
                            'employee',
                            $empId,
                            'New Project Assigned',
                            'You have been assigned to project "' . $projectName . '".'
                        );
                    }
                }
                $message = 'Project created and employees assigned successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to create project';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'assign_employee') {
        $projectId = sanitize($_POST['project_id'] ?? '');
        $empId = sanitize($_POST['emp_id'] ?? '');
        
        if (empty($projectId) || empty($empId)) {
            $message = 'Project and employee are required';
            $messageType = 'danger';
        } else {
            $result = $projectModel->assignEmployee($projectId, $empId);
            $message = $result ? 'Employee assigned successfully!' : 'Employee already assigned to this project';
            $messageType = $result ? 'success' : 'warning';

            if ($result) {
                $project = $projectModel->getProjectById($projectId);
                $projectName = $project['Project_name'] ?? 'a project';
                $notificationModel->createNotification(
                    'employee',
                    $empId,
                    'New Project Assignment',
                    'You have been added to project "' . $projectName . '".'
                );
            }
        }
    } elseif ($action === 'remove_employee') {
        $projectId = sanitize($_POST['project_id'] ?? '');
        $empId = sanitize($_POST['emp_id'] ?? '');
        
        if (empty($projectId) || empty($empId)) {
            $message = 'Project and employee are required';
            $messageType = 'danger';
        } else {
            $result = $projectModel->removeEmployee($projectId, $empId);
            $message = $result ? 'Employee removed successfully!' : 'Failed to remove employee';
            $messageType = $result ? 'success' : 'danger';

            if ($result) {
                $project = $projectModel->getProjectById($projectId);
                $projectName = $project['Project_name'] ?? 'project';
                $notificationModel->createNotification(
                    'employee',
                    $empId,
                    'Project Update',
                    'You have been removed from project "' . $projectName . '".'
                );
            }
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$projects = $projectModel->getAllProjects($limit, $offset);
$employees = $empModel->getAllEmployees(1000);
$categories = $categoryModel->getAllCategories();
$totalCount = $projectModel->countProjects();
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Projects</title>
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
        .avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0d6efd,#5c9dff); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size: 0.7rem; }
        .btn-logout { background: #fee2e2; color: #dc2626; border: none; }
        .btn-logout:hover { background: #fecaca; color: #991b1b; }
        .project-card { border-left: 4px solid #0d6efd; }
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
                <a class="nav-link active" href="project-allocation.php"><i class="bi bi-kanban me-2"></i>Projects</a>
                <a class="nav-link" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
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
                    <h4 class="mb-1">Project Allocation</h4>
                    <small class="text-muted">Create projects and assign employees</small>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">+ Create Project</button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Projects Table -->
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Assigned Team</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($projects): ?>
                            <?php foreach ($projects as $project): ?>
                            <?php $teamMembers = $projectModel->getProjectEmployees($project['Project_id']); ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($project['Project_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($project['Description'] ?? '', 0, 40)); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($project['Category_name']); ?></td>
                                <td>
                                    <small><?php echo formatDate($project['Start_date']); ?> to <?php echo formatDate($project['End_date']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($project['Status'] ?? '') === 'Active' ? 'success' : 'secondary'; ?>">
                                        <?php echo htmlspecialchars($project['Status'] ?? ''); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if ($teamMembers): ?>
                                            <?php foreach ($teamMembers as $member): ?>
                                                <div class="d-flex align-items-center gap-2 border rounded px-2 py-1">
                                                    <span class="avatar"><?php echo strtoupper(substr($member['Emp_firstName'],0,1) . substr($member['Emp_lastName'],0,1)); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($member['Emp_firstName'] . ' ' . $member['Emp_lastName']); ?></small>
                                                    <form method="POST" class="m-0">
                                                        <input type="hidden" name="action" value="remove_employee">
                                                        <input type="hidden" name="project_id" value="<?php echo $project['Project_id']; ?>">
                                                        <input type="hidden" name="emp_id" value="<?php echo $member['Emp_id']; ?>">
                                                        <button class="btn btn-sm btn-light text-danger" type="submit"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <small class="text-muted">No team assigned</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal" data-project="<?php echo $project['Project_id']; ?>">
                                        Assign
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">No projects found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="create">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Project Name</label>
                <input type="text" name="project_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['ProjectCategory_id']; ?>"><?php echo htmlspecialchars($cat['Category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Assign Employees</label>
                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">
                    <?php foreach ($employees as $emp): ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="employees[]" value="<?php echo $emp['Emp_id']; ?>" id="emp_<?php echo $emp['Emp_id']; ?>">
                        <label class="form-check-label" for="emp_<?php echo $emp['Emp_id']; ?>">
                            <?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted">Select one or more employees to assign to this project.</small>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Employee Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" value="assign_employee">
        <input type="hidden" name="project_id" id="assignProjectId">
        <label class="form-label fw-semibold">Employee</label>
        <select name="emp_id" class="form-select" required>
            <option value="">Select Employee</option>
            <?php foreach ($employees as $emp): ?>
            <option value="<?php echo $emp['Emp_id']; ?>">
                <?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?>
            </option>
            <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Assign</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const assignModal = document.getElementById('assignModal');
assignModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const projectId = button.getAttribute('data-project');
    const input = assignModal.querySelector('#assignProjectId');
    input.value = projectId;
});
</script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>

