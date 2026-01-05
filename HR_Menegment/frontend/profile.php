<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$empModel = new EmployeeModel($db);
$categoryModel = new ProjectCategoryModel($db);

$message = '';
$messageType = '';

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $dob = sanitize($_POST['dob'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        if (empty($firstName) || empty($lastName)) {
            $message = 'First name and last name are required';
            $messageType = 'danger';
        } else {
            $profilePic = null;
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['size'] > 0) {
                $upload = saveUploadedFile($_FILES['profile_pic'], UPLOADS_PROFILES);
                if ($upload) {
                    $profilePic = $upload['relative_path'];
                }
            }
            
            $result = $empModel->updateEmployee(
                $empId, 
                $firstName, 
                $lastName, 
                $phone ?: null,
                $dob ?: null,
                $gender ?: null,
                null,
                $address ?: null,
                $profilePic
            );
            
            if ($result) {
                // Update session
                $updatedEmp = $empModel->getEmployeeById($empId);
                Session::set('user', $updatedEmp);
                $empData = $updatedEmp;
                $user = $updatedEmp;
                
                $message = 'Profile updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to update profile';
                $messageType = 'danger';
            }
        }
    }
}

$categories = $categoryModel->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Profile | HRMS</title>
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
        .profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #0d6efd, #5c9dff); display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 2rem; border: 4px solid white; }
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
                <a class="nav-pill" href="projects.php"><i class="bi-diagram-3"></i>Projects</a>
                <a class="nav-pill" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
                <a class="nav-pill active" href="profile.php"><i class="bi-person-circle"></i>Profile</a>
            </nav>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <main class="col-md-9 col-lg-10 p-4">
            <?php 
            $pageTitle = 'Profile';
            $pageSubtitle = 'Manage your account settings';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Profile Summary Card -->
            <div class="card p-4 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);">
                <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                    <!-- Profile Photo -->
                    <div class="position-relative">
                        <?php if (!empty($profilePicUrl)): ?>
                            <img src="<?php echo htmlspecialchars($profilePicUrl); ?>" alt="Profile" 
                                 class="rounded-circle shadow-sm" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #fff;">
                        <?php else: ?>
                            <div class="avatar rounded-circle shadow-sm" 
                                 style="width: 120px; height: 120px; font-size: 2.5rem; border: 4px solid #fff;">
                                <?php echo $initials; ?>
                            </div>
                        <?php endif; ?>
                        <div class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" 
                             style="width: 24px; height: 24px;"></div>
                    </div>

                    <!-- Basic Info -->
                    <div class="text-center text-md-start flex-grow-1">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                            <h2 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($empData['Emp_firstName'] . ' ' . $empData['Emp_lastName']); ?></h2>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                <?php echo htmlspecialchars($empData['Category_name'] ?? 'Employee'); ?>
                            </span>
                        </div>
                        <p class="text-muted mb-3">
                            <i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($empData['Emp_email']); ?> &nbsp;|&nbsp; 
                            <i class="bi bi-person-badge me-1"></i> <?php echo htmlspecialchars($empData['Emp_code']); ?>
                        </p>
                        
                        <!-- Quick Stats Grid -->
                        <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                            <div class="bg-white p-3 rounded-3 shadow-sm border border-light" style="min-width: 140px;">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Monthly Salary</small>
                                <div class="fw-bold text-dark h5 mb-0 mt-1">₹<?php echo number_format($empData['Salary'] ?? 0); ?></div>
                            </div>
                            <div class="bg-white p-3 rounded-3 shadow-sm border border-light" style="min-width: 140px;">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Joining Date</small>
                                <div class="fw-bold text-dark h5 mb-0 mt-1"><?php echo date('M Y', strtotime($empData['Joining_date'])); ?></div>
                            </div>
                            <div class="bg-white p-3 rounded-3 shadow-sm border border-light" style="min-width: 140px;">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Status</small>
                                <div class="fw-bold text-success h5 mb-0 mt-1"><?php echo htmlspecialchars($empData['Status']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card p-4">
                        <h6 class="mb-4">Edit Profile</h6>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($empData['Emp_firstName']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($empData['Emp_lastName']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email (Read-only)</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($empData['Emp_email']); ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employee Code (Read-only)</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($empData['Emp_code']); ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($empData['Emp_phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($empData['Emp_dob'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo $empData['Emp_gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $empData['Emp_gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $empData['Emp_gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department (Read-only)</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($empData['Category_name'] ?? 'N/A'); ?>" disabled>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($empData['Address'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" name="profile_pic" accept="image/*">
                                    <small class="text-muted">JPG, PNG (Max 5MB)</small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="employee-dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Employment Info -->
                    <div class="card p-4 mb-3">
                        <h6 class="mb-3">Employment Info</h6>
                        <div class="mb-3">
                            <small class="text-muted">Joining Date</small>
                            <p class="mb-0 fw-semibold"><?php echo formatDate($empData['Joining_date']); ?></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Status</small>
                            <p class="mb-0"><span class="badge bg-success"><?php echo htmlspecialchars($empData['Status']); ?></span></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Monthly Salary</small>
                            <p class="mb-0 fw-semibold">₹<?php echo number_format($empData['Salary'] ?? 0, 2); ?></p>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="card p-4">
                        <h6 class="mb-3">Account Info</h6>
                        <div class="mb-3">
                            <small class="text-muted">Member Since</small>
                            <p class="mb-0 fw-semibold"><?php echo formatDate($empData['Created_at']); ?></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Account Status</small>
                            <p class="mb-0">
                                <span class="badge bg-success">Active</span>
                            </p>
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