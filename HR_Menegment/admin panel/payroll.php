<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$empModel = new EmployeeModel($db);
$categoryModel = new ProjectCategoryModel($db);

$message = '';
$messageType = '';

// Handle salary update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'update_salary') {
        $empId = sanitize($_POST['emp_id'] ?? '');
        $salary = sanitize($_POST['salary'] ?? '');
        
        if (empty($empId) || empty($salary)) {
            $message = 'Employee and salary are required';
            $messageType = 'danger';
        } else {
            $result = $empModel->updateSalary($empId, $salary);
            $message = $result ? 'Salary updated successfully!' : 'Failed to update salary';
            $messageType = $result ? 'success' : 'danger';
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$employees = $empModel->getAllEmployees($limit, $offset);
$totalCount = $empModel->getTotalCount();
$totalPages = ceil($totalCount / $limit);
$categories = $categoryModel->getAllCategories();

// Calculate total payroll
$totalPayroll = 0;
foreach ($employees as $emp) {
    $totalPayroll += $emp['Salary'] ?? 0;
}

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Payroll</title>
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
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.alt { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .table {
                border-collapse: collapse;
                width: 100%;
            }
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            .table thead {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .avatar {
                background: #0d6efd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
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
                <a class="nav-link" href="departments.php"><i class="bi bi-building me-2"></i>Departments</a>
                <a class="nav-link" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link active" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Payroll Management'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Manage employee salaries and payroll</small>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row g-3 mb-4 no-print">
                <div class="col-md-6">
                    <div class="card stat-card p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Total Monthly Payroll</div>
                                <h3 class="mb-0">₹<?php echo number_format($totalPayroll, 0); ?></h3>
                            </div>
                            <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card alt p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="opacity-75">Average Salary</div>
                                <h3 class="mb-0">₹<?php echo number_format(count($employees) > 0 ? $totalPayroll / count($employees) : 0, 0); ?></h3>
                            </div>
                            <i class="bi bi-graph-up" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-3 print-section">
                <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                    <h6 class="mb-0">Employee Salaries</h6>
                    <a href="../backend/download-payroll.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download"></i> Download Excel
                    </a>
                </div>
                <div class="mb-3 print-only" style="display: none;">
                    <h5 class="mb-1">Employee Salaries</h5>
                    <small class="text-muted">Generated on <?php echo date('d M Y'); ?></small>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Monthly Salary</th>
                            <th>Annual Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($employees): ?>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <span class="avatar"><?php echo getInitials($emp['Emp_firstName'], $emp['Emp_lastName']); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($emp['Emp_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($emp['Salary'] ?? 0, 2); ?></strong>
                                </td>
                                <td>
                                    ₹<?php echo number_format(($emp['Salary'] ?? 0) * 12, 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($emp['Status']); ?></span>
                                </td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editSalary(<?php echo htmlspecialchars(json_encode($emp)); ?>)" data-bs-toggle="modal" data-bs-target="#editSalaryModal">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No employees found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top no-print">
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

<!-- Edit Salary Modal -->
<div class="modal fade" id="editSalaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_salary">
                <input type="hidden" name="emp_id" id="salaryEmpId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Update Salary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" id="salaryEmpName"></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Salary (₹)</label>
                        <input type="number" class="form-control" name="salary" id="salaryAmount" placeholder="50000" required step="0.01">
                    </div>
                    <div class="alert alert-info">
                        <strong>Annual Salary:</strong> ₹<span id="annualSalary">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Salary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSalary(emp) {
    document.getElementById('salaryEmpId').value = emp.Emp_id;
    document.getElementById('salaryEmpName').textContent = emp.Emp_firstName + ' ' + emp.Emp_lastName;
    document.getElementById('salaryAmount').value = emp.Salary || '';
    updateAnnual();
}

document.getElementById('salaryAmount').addEventListener('input', updateAnnual);

function updateAnnual() {
    const monthly = parseFloat(document.getElementById('salaryAmount').value) || 0;
    const annual = monthly * 12;
    document.getElementById('annualSalary').textContent = annual.toLocaleString('en-IN');
}

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>