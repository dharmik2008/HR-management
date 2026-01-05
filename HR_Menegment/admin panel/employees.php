<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$empModel = new EmployeeModel($db);
$categoryModel = new ProjectCategoryModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'create') {
        // Create new employee
        $empCode = sanitize($_POST['emp_code'] ?? '');
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = sanitize($_POST['phone'] ?? '');
        $dob = sanitize($_POST['dob'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $joiningDate = sanitize($_POST['joining_date'] ?? '');
        $categoryId = sanitize($_POST['category_id'] ?? '');
        $salary = sanitize($_POST['salary'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        // Validate
        if (empty($empCode) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $message = 'All required fields are mandatory';
            $messageType = 'danger';
        } elseif (!isValidEmail($email)) {
            $message = 'Invalid email format';
            $messageType = 'danger';
        } elseif ($empModel->getEmployeeByEmail($email)) {
            $message = 'Email already exists';
            $messageType = 'danger';
        } else {
            $profilePic = null;
            // Handle profile picture upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
                $upload = saveUploadedFile($_FILES['profile_pic'], UPLOADS_PROFILES);
                if ($upload) {
                    $profilePic = $upload['relative_path'];
                }
            }
            
            $result = $empModel->createEmployee(
                $empCode, $firstName, $lastName, $email, $password, 
                $phone ?: null, $dob ?: null, $gender ?: null, 
                $joiningDate ?: null, $categoryId ?: null, 
                $salary ?: null, $address ?: null, $profilePic
            );
            
            if ($result) {
                $message = 'Employee created successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to create employee';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'update') {
        // Update employee - allow partial updates
        $empId = sanitize($_POST['emp_id'] ?? '');
        
        if (empty($empId)) {
            $message = 'Employee ID is required';
            $messageType = 'danger';
        } else {
            // Get current employee data
            $currentEmp = $empModel->getEmployeeById($empId);
            if (!$currentEmp) {
                $message = 'Employee not found';
                $messageType = 'danger';
            } else {
                // Use submitted values if provided, otherwise use existing values
                $firstName = !empty($_POST['first_name']) ? sanitize($_POST['first_name']) : $currentEmp['Emp_firstName'];
                $lastName = !empty($_POST['last_name']) ? sanitize($_POST['last_name']) : $currentEmp['Emp_lastName'];
                $phone = isset($_POST['phone']) && $_POST['phone'] !== '' ? sanitize($_POST['phone']) : ($currentEmp['Emp_phone'] ?? null);
                $dob = isset($_POST['dob']) && $_POST['dob'] !== '' ? sanitize($_POST['dob']) : ($currentEmp['Emp_dob'] ?? null);
                $gender = isset($_POST['gender']) && $_POST['gender'] !== '' ? sanitize($_POST['gender']) : ($currentEmp['Emp_gender'] ?? null);
                $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? sanitize($_POST['category_id']) : ($currentEmp['ProjectCategory_id'] ?? null);
                $address = isset($_POST['address']) && $_POST['address'] !== '' ? sanitize($_POST['address']) : ($currentEmp['Address'] ?? null);
                $salary = isset($_POST['salary']) && $_POST['salary'] !== '' ? sanitize($_POST['salary']) : ($currentEmp['Salary'] ?? null);
                
                // Handle email update (admin only)
                $emailUpdated = false;
                if (isset($_POST['email']) && !empty($_POST['email'])) {
                    $newEmail = sanitize($_POST['email']);
                    if (!isValidEmail($newEmail)) {
                        $message = 'Invalid email format';
                        $messageType = 'danger';
                    } elseif ($newEmail !== $currentEmp['Emp_email']) {
                        // Check if email already exists for another employee
                        $existingEmp = $empModel->getEmployeeByEmail($newEmail);
                        if ($existingEmp && $existingEmp['Emp_id'] != $empId) {
                            $message = 'Email already exists for another employee';
                            $messageType = 'danger';
                        } else {
                            $emailResult = $empModel->updateEmail($empId, $newEmail);
                            if ($emailResult) {
                                $emailUpdated = true;
                            } else {
                                $message = 'Failed to update email';
                                $messageType = 'danger';
                            }
                        }
                    }
                }
                
                // Only proceed with other updates if email update didn't fail
                if ($messageType !== 'danger' || $emailUpdated) {
                    $profilePic = null;
                    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
                        $upload = saveUploadedFile($_FILES['profile_pic'], UPLOADS_PROFILES);
                        if ($upload) {
                            $profilePic = $upload['relative_path'];
                        }
                    }
                    
                    $result = $empModel->updateEmployee(
                        $empId, $firstName, $lastName, $phone, 
                        $dob, $gender, $categoryId, 
                        $address, $profilePic, $salary
                    );
                    
                    if ($result || $emailUpdated) {
                        $message = 'Employee updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update employee';
                        $messageType = 'danger';
                    }
                }
            }
        }
    } elseif ($action === 'update_salary') {
        // Update salary
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
    } elseif ($action === 'update_status') {
        // Update employee status
        $empId = sanitize($_POST['emp_id'] ?? '');
        $status = sanitize($_POST['status'] ?? '');
        
        if (empty($empId) || empty($status)) {
            $message = 'Employee and status are required';
            $messageType = 'danger';
        } else {
            $result = $empModel->updateStatus($empId, $status);
            $message = $result ? 'Status updated successfully!' : 'Failed to update status';
            $messageType = $result ? 'success' : 'danger';
        }
    } elseif ($action === 'delete') {
        // Hard delete employee (permanently removes employee and related data)
        $empId = sanitize($_POST['emp_id'] ?? '');
        if (empty($empId)) {
            $message = 'Employee ID is required';
            $messageType = 'danger';
            $result = false;
        } else {
            $result = $empModel->deleteEmployee($empId, true);
            $message = $result ? 'Employee removed successfully!' : 'Failed to remove employee';
            $messageType = $result ? 'success' : 'danger';
        }

        // If called via AJAX/fetch expecting JSON, return JSON immediately
        $isAjax = false;
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $isAjax = true;
        } elseif (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $isAjax = true;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => (bool)$result,
                'message' => $message
            ]);
            exit;
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
if ($searchTerm) {
    $employees = $empModel->searchEmployees($searchTerm, 100);
} else {
    $employees = $empModel->getAllEmployees($limit, $offset);
}

$categories = $categoryModel->getAllCategories();
$totalCount = $empModel->getTotalCount();
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Employees</title>
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
                <a class="nav-link active" href="employees.php"><i class="bi bi-people me-2"></i>Employees</a>
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
        $pageTitle = 'Employees'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>   

                    <small class="text-muted">Manage profiles, roles, salary and documents</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">+ Add Employee</button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card p-3 mb-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by name or email...">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="searchEmployees()">Search</button>
                    </div>
                </div>
            </div>

            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Salary</th>
                            <th>Joining Date</th>
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
                                        <small class="text-muted"><?php echo htmlspecialchars($emp['Emp_code']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($emp['Emp_email']); ?></td>
                                <td><?php echo htmlspecialchars($emp['Category_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <select class="form-select form-select-sm" onchange="updateStatus(<?php echo $emp['Emp_id']; ?>, this.value)">
                                        <option value="Active" <?php echo $emp['Status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="Probation" <?php echo $emp['Status'] === 'Probation' ? 'selected' : ''; ?>>Probation</option>
                                        <option value="Inactive" <?php echo $emp['Status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </td>
                                <td>₹<?php echo number_format($emp['Salary'] ?? 0, 0); ?></td>
                                <td><?php echo formatDate($emp['Joining_date']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($emp)); ?>)" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Edit</button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this employee?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="emp_id" value="<?php echo $emp['Emp_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No employees found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!$searchTerm && $totalPages > 1): ?>
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

<!-- Add/Edit Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="employeeForm">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="emp_id" id="empId">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Code</label>
                            <input type="text" class="form-control" name="emp_code" id="empCode" placeholder="EMP001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <small class="text-muted">(Admin can change)</small></label>
                            <input type="email" class="form-control" name="email" id="empEmail" placeholder="john@company.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="empFirstName" placeholder="John" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="empLastName" placeholder="Doe" required>
                        </div>
                        <div class="col-md-6" id="passwordDiv">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="empPassword" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="empPhone" placeholder="+91 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" id="empDob">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" id="empGender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-control" name="joining_date" id="empJoiningDate">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="category_id" id="empCategory">
                                <option value="">Select Department</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['ProjectCategory_id']; ?>"><?php echo htmlspecialchars($cat['Category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Salary</label>
                            <input type="number" class="form-control" name="salary" id="empSalary" placeholder="50000">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="empAddress" rows="2" placeholder="Full address"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" name="profile_pic" id="empProfilePic" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editEmployee(emp) {
    // Keep posting to the same page; just switch the action type
    const form = document.getElementById('employeeForm');
    form.action = '';
    form.setAttribute('novalidate', 'novalidate');
    document.getElementById('modalTitle').textContent = 'Edit Employee';
    document.getElementById('formAction').value = 'update';
    document.getElementById('empId').value = emp.Emp_id;
    document.getElementById('empCode').value = emp.Emp_code;
    document.getElementById('empCode').disabled = true;
    document.getElementById('empCode').removeAttribute('required');
    document.getElementById('empEmail').value = emp.Emp_email;
    document.getElementById('empEmail').disabled = false;
    document.getElementById('empEmail').setAttribute('required', 'required');
    document.getElementById('empFirstName').value = emp.Emp_firstName;
    document.getElementById('empFirstName').removeAttribute('required');
    document.getElementById('empLastName').value = emp.Emp_lastName;
    document.getElementById('empLastName').removeAttribute('required');
    document.getElementById('empPhone').value = emp.Emp_phone || '';
    document.getElementById('empDob').value = emp.Emp_dob || '';
    document.getElementById('empGender').value = emp.Emp_gender || '';
    document.getElementById('empJoiningDate').value = emp.Joining_date || '';
    document.getElementById('empCategory').value = emp.ProjectCategory_id || '';
    document.getElementById('empSalary').value = emp.Salary || '';
    document.getElementById('empAddress').value = emp.Address || '';
    document.getElementById('passwordDiv').style.display = 'none';
    document.getElementById('empPassword').removeAttribute('required');
}

function updateStatus(empId, status) {
    const form = new FormData();
    form.append('action', 'update_status');
    form.append('emp_id', empId);
    form.append('status', status);
    
    fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: form
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}

function deleteEmployeeConfirm(empId) {
    if (!confirm('Are you sure you want to permanently remove this employee? This action cannot be undone.')) {
        return;
    }

    const form = new FormData();
    form.append('action', 'delete');
    form.append('emp_id', empId);

    fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: form
    }).then(response => {
        // If network-level error (e.g., 500), show response text
        if (!response.ok) {
            return response.text().then(text => {
                // show server response (useful to capture PHP warnings/errors)
                alert(text || 'Failed to delete employee (server error).');
            });
        }

        // Try to parse JSON; if parsing fails, show raw text so we can inspect errors
        return response.text().then(text => {
            try {
                const data = JSON.parse(text);
                if (data && data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete employee.');
                }
            } catch (e) {
                // Not JSON — show raw server response to help debugging
                const trimmed = (text || '').trim();
                if (trimmed) {
                    alert('Server response:\n' + trimmed);
                } else {
                    alert('Failed to delete employee (invalid server response).');
                }
            }
        });
    }).catch(err => {
        console.error('Fetch error:', err);
        alert('Network error while deleting employee.');
    });
}
function searchEmployees() {
    const search = document.getElementById('searchInput').value;
    if (search) {
        window.location.href = '?search=' + encodeURIComponent(search);
    }
}

// Reset form on modal close
document.getElementById('addEmployeeModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('employeeForm');
    form.reset();
    form.action = '';
    form.removeAttribute('novalidate');
    document.getElementById('modalTitle').textContent = 'Add Employee';
    document.getElementById('formAction').value = 'create';
    document.getElementById('empId').value = '';
    document.getElementById('empCode').disabled = false;
    document.getElementById('empCode').setAttribute('required', 'required');
    document.getElementById('empEmail').disabled = false;
    document.getElementById('empEmail').setAttribute('required', 'required');
    document.getElementById('empFirstName').setAttribute('required', 'required');
    document.getElementById('empLastName').setAttribute('required', 'required');
    document.getElementById('empPassword').setAttribute('required', 'required');
    document.getElementById('passwordDiv').style.display = 'block';
});

// Ensure form action is set correctly before submission and disable HTML5 validation when editing
document.getElementById('employeeForm').addEventListener('submit', function(e) {
    const actionInput = document.getElementById('formAction');
    const empId = document.getElementById('empId').value;
    
    // If empId exists, we're editing, so ensure action is 'update' and disable HTML5 validation
    if (empId && empId !== '') {
        actionInput.value = 'update';
        this.setAttribute('novalidate', 'novalidate');
    } else {
        // Creating new employee - remove novalidate to allow HTML5 validation
        this.removeAttribute('novalidate');
    }
}, false);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>