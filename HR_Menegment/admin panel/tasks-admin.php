<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$taskModel = new TaskModel($db);
$empModel = new EmployeeModel($db);
$projectModel = new ProjectModel($db);
$notificationModel = new NotificationModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'create') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $assignedToId = sanitize($_POST['assigned_to'] ?? '');
        $priority = sanitize($_POST['priority'] ?? '');
        $dueDate = sanitize($_POST['due_date'] ?? '');
        $projectId = !empty($_POST['project_id']) ? sanitize($_POST['project_id']) : null;
        
        if (empty($title) || empty($assignedToId) || empty($dueDate)) {
            $message = 'Title, assignee, and due date are required';
            $messageType = 'danger';
        } else {
            $userId = Session::getUserId();
            $result = $taskModel->createTask($title, $description, $userId, $assignedToId, $priority, $dueDate, $projectId);
            
            if ($result) {
                // Create notification
                $employee = $empModel->getEmployeeById($assignedToId);
                $notificationModel->createNotification(
                    'employee',
                    $assignedToId,
                    'New Task Assigned',
                    'A new task "' . $title . '" has been assigned to you'
                );
                
                $message = 'Task created successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to create task';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'update') {
        $taskId = sanitize($_POST['task_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $priority = sanitize($_POST['priority'] ?? '');
        $dueDate = sanitize($_POST['due_date'] ?? '');
        $status = sanitize($_POST['status'] ?? '');
        $projectId = !empty($_POST['project_id']) ? sanitize($_POST['project_id']) : null;
        
        if (empty($taskId) || empty($title)) {
            $message = 'Task ID and title are required';
            $messageType = 'danger';
        } else {
            $taskData = $taskModel->getTaskById($taskId);
            $result = $taskModel->updateTask($taskId, $title, $description, $priority, $dueDate, $status, $projectId);
            $message = $result ? 'Task updated successfully!' : 'Failed to update task';
            $messageType = $result ? 'success' : 'danger';

            if ($result && $taskData && !empty($taskData['Assigned_to'])) {
                $notificationModel->createNotification(
                    'employee',
                    $taskData['Assigned_to'],
                    'Task Updated',
                    'Task "' . $title . '" has been updated. Current status: ' . ($status ?: 'N/A')
                );
            }
        }
    } elseif ($action === 'delete') {
        $taskId = sanitize($_POST['task_id'] ?? '');
        
        if (empty($taskId)) {
            $message = 'Task ID is required';
            $messageType = 'danger';
        } else {
            $taskData = $taskModel->getTaskById($taskId);
            $result = $taskModel->deleteTask($taskId);
            $message = $result ? 'Task deleted successfully!' : 'Failed to delete task';
            $messageType = $result ? 'success' : 'danger';

            if ($result && $taskData && !empty($taskData['Assigned_to'])) {
                $notificationModel->createNotification(
                    'employee',
                    $taskData['Assigned_to'],
                    'Task Removed',
                    'Task "' . ($taskData['Title'] ?? 'Task') . '" has been removed by HR.'
                );
            }
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$tasks = $taskModel->getAllTasks(null, null, $limit, $offset);
$employees = $empModel->getAllEmployees(1000);
$projects = $projectModel->getAllProjects();
$allTasks = $taskModel->getAllTasks();
$totalCount = count($allTasks);
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Tasks</title>
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
                <a class="nav-link" href="attendance-admin.php"><i class="bi bi-clipboard-data me-2"></i>Attendance</a>
                <a class="nav-link" href="leaves.php"><i class="bi bi-calendar2-check me-2"></i>Leaves</a>
                <a class="nav-link" href="project-allocation.php"><i class="bi bi-kanban me-2"></i>Projects</a>
                <a class="nav-link active" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Task Management'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Create and manage employee tasks</small>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">+ Create Task</button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Tasks Table -->
            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Assigned To</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($tasks): ?>
<?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($task['Title']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($task['Description'] ?? '', 0, 50)); ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($task['Project_name'])): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($task['Project_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-flex align-items-center gap-2">
                                    <?php $empName = trim(($task['employee_name'] ?? '') ?: (($task['Emp_firstName'] ?? '') . ' ' . ($task['Emp_lastName'] ?? ''))); ?>
                                    <span class="avatar"><?php echo getInitials($task['Emp_firstName'] ?? 'N', $task['Emp_lastName'] ?? 'A'); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($empName ?: 'N/A'); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($task['Emp_email'] ?? ''); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $priorityColor = [
                                        'High' => 'danger',
                                        'Medium' => 'warning',
                                        'Low' => 'success'
                                    ];
                                    $color = $priorityColor[$task['Priority']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($task['Priority']); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusColor = [
                                        'Pending' => 'warning text-dark',
                                        'In Progress' => 'info text-dark',
                                        'Completed' => 'success'
                                    ];
                                    $color = $statusColor[$task['Status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($task['Status']); ?></span>
                                </td>
                                <td><?php echo formatDate($task['Due_date']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)" data-bs-toggle="modal" data-bs-target="#createTaskModal">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(<?php echo $task['Task_id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No tasks found</td></tr>
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
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-outline-secondary btn-sm">← Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-outline-secondary btn-sm">Next →</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
    </div>
</div>

<!-- Create/Edit Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="taskForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="task_id" id="taskId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalTitle">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Task Title</label>
                            <input type="text" class="form-control" name="title" id="taskTitle" placeholder="Enter task title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="taskDesc" rows="3" placeholder="Task description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Project</label>
                            <select class="form-select" name="project_id" id="taskProjectId">
                                <option value="">No project</option>
                                <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['Project_id']; ?>"><?php echo htmlspecialchars($project['Project_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign To</label>
                            <select class="form-select" name="assigned_to" id="taskAssignedTo" required>
                                <option value="">Select employee...</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['Emp_id']; ?>"><?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority" id="taskPriority" required>
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" id="taskDueDate" required>
                        </div>
                        <div class="col-md-6" id="statusDiv" style="display:none;">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="taskStatus">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="task_id" id="deleteTaskId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this task? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTask(task) {
    document.getElementById('taskModalTitle').textContent = 'Edit Task';
    document.querySelector('#taskForm input[name="action"]').value = 'update';
    document.getElementById('taskId').value = task.Task_id;
    document.getElementById('taskTitle').value = task.Title;
    document.getElementById('taskDesc').value = task.Description || '';
    document.getElementById('taskProjectId').value = task.Project_id || '';
    document.getElementById('taskAssignedTo').value = task.Assigned_to;
    document.getElementById('taskPriority').value = task.Priority;
    document.getElementById('taskDueDate').value = task.Due_date;
    document.getElementById('taskStatus').value = task.Status;
    document.getElementById('statusDiv').style.display = 'block';
}

function deleteTask(taskId) {
    document.getElementById('deleteTaskId').value = taskId;
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteConfirmModal.show();
}

document.getElementById('createTaskModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('taskForm').reset();
    document.getElementById('taskModalTitle').textContent = 'Create Task';
    document.querySelector('#taskForm input[name="action"]').value = 'create';
    document.getElementById('taskId').value = '';
    document.getElementById('taskPriority').value = 'Medium';
    document.getElementById('statusDiv').style.display = 'none';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>