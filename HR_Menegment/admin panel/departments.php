<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$categoryModel = new ProjectCategoryModel($db);
$empModel = new EmployeeModel($db);
$projectModel = new ProjectModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'create') {
        $name = sanitize($_POST['category_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($name)) {
            $message = 'Department name is required';
            $messageType = 'danger';
        } else {
            $result = $categoryModel->createCategory($name, $description ?: null);
            $message = $result ? 'Department created successfully!' : 'Failed to create department';
            $messageType = $result ? 'success' : 'danger';
        }
    } elseif ($action === 'update') {
        $categoryId = sanitize($_POST['category_id'] ?? '');
        $name = sanitize($_POST['category_name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($categoryId) || empty($name)) {
            $message = 'Department ID and name are required';
            $messageType = 'danger';
        } else {
            $result = $categoryModel->updateCategory($categoryId, $name, $description ?: null);
            $message = $result ? 'Department updated successfully!' : 'Failed to update department';
            $messageType = $result ? 'success' : 'danger';
        }
    } elseif ($action === 'delete') {
        $categoryId = sanitize($_POST['category_id'] ?? '');
        
        if (empty($categoryId)) {
            $message = 'Department ID is required';
            $messageType = 'danger';
        } else {
            $empCount = $categoryModel->getEmployeeCount($categoryId);
            if ($empCount > 0) {
                $message = 'Cannot delete department with assigned employees. Please reassign or remove employees first.';
                $messageType = 'danger';
            } else {
                try {
                    $result = $categoryModel->deleteCategory($categoryId);
                    $message = $result ? 'Department deleted successfully!' : 'Failed to delete department';
                    $messageType = $result ? 'success' : 'danger';
                } catch (Exception $e) {
                    $message = 'Delete failed due to related records. Please reassign or remove dependencies.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Get all categories with employee and project counts
$categories = $categoryModel->getAllCategories();
$categoryStats = [];
$categoryProjects = [];
foreach ($categories as $cat) {
    $stats = $categoryModel->getCategoryWithCount($cat['ProjectCategory_id']);
    $categoryStats[$cat['ProjectCategory_id']] = $stats;
    $categoryProjects[$cat['ProjectCategory_id']] = $categoryModel->getProjectsByCategory($cat['ProjectCategory_id']);
}

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Departments</title>
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
        .btn-logout { background: #fee2e2; color: #dc2626; border: none; }
        .btn-logout:hover { background: #fecaca; color: #991b1b; }
        .dept-card { border-left: 4px solid #0d6efd; }
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
                <a class="nav-link" href="tasks-admin.php"><i class="bi bi-card-checklist me-2"></i>Tasks</a>
                <a class="nav-link active" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Departments'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Manage project categories and departments</small>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal">+ Add Department</button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Departments Grid -->
            <div class="row g-3">
                <?php if ($categories): ?>
                    <?php foreach ($categories as $cat): ?>
                    <?php $stats = $categoryStats[$cat['ProjectCategory_id']] ?? $cat; ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card dept-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($cat['Category_name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($cat['Description'] ?? 'No description'); ?></small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button type="button" class="dropdown-item" onclick="editDept(<?php echo htmlspecialchars(json_encode($cat)); ?>)" data-bs-toggle="modal" data-bs-target="#addDeptModal">Edit</button></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><button type="button" class="dropdown-item text-danger" onclick="deleteDept(<?php echo $cat['ProjectCategory_id']; ?>)">Delete</button></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center">
                                        <h5 class="mb-0"><?php echo $stats['employee_count'] ?? 0; ?></h5>
                                        <small class="text-muted">Employees</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded text-center">
                                        <h5 class="mb-0"><?php echo $stats['project_count'] ?? 0; ?></h5>
                                        <small class="text-muted">Projects</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card p-5 text-center">
                            <p class="text-muted mb-0">No departments found. Create one to get started.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Departments Table -->
            <div class="card p-3 mt-4">
                <h6 class="mb-3">All Departments</h6>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Employees</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <?php 
                        $stats = $categoryStats[$cat['ProjectCategory_id']] ?? $cat;
                        $projects = $categoryProjects[$cat['ProjectCategory_id']] ?? [];
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cat['Category_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['Description'] ?? '—'); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo $stats['employee_count'] ?? 0; ?> employees</span></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editDept(<?php echo htmlspecialchars(json_encode($cat)); ?>)" data-bs-toggle="modal" data-bs-target="#addDeptModal">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDept(<?php echo $cat['ProjectCategory_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="deptForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="category_id" id="categoryId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="deptModalTitle">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" class="form-control" name="category_name" id="categoryName" placeholder="Engineering" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="categoryDesc" rows="3" placeholder="Department description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
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
                <input type="hidden" name="category_id" id="deleteId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this department? This action cannot be undone.</p>
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
function editDept(dept) {
    document.getElementById('deptModalTitle').textContent = 'Edit Department';
    document.querySelector('#deptForm input[name="action"]').value = 'update';
    document.getElementById('categoryId').value = dept.ProjectCategory_id;
    document.getElementById('categoryName').value = dept.Category_name;
    document.getElementById('categoryDesc').value = dept.Description || '';
}

function deleteDept(categoryId) {
    document.getElementById('deleteId').value = categoryId;
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteConfirmModal.show();
}

document.getElementById('addDeptModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('deptForm').reset();
    document.getElementById('deptModalTitle').textContent = 'Add Department';
    document.querySelector('#deptForm input[name="action"]').value = 'create';
    document.getElementById('categoryId').value = '';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>