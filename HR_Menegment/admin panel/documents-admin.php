<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireAdmin();

$docModel = new DocumentModel($db);
$empModel = new EmployeeModel($db);

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'upload') {
        $empId = sanitize($_POST['emp_id'] ?? '');
        
        if (empty($empId)) {
            $message = 'Employee selection is required';
            $messageType = 'danger';
        } elseif (!isset($_FILES['document']) || $_FILES['document']['size'] === 0) {
            $message = 'Please select a document to upload';
            $messageType = 'danger';
        } else {
            $upload = saveUploadedFile($_FILES['document'], UPLOADS_DOCUMENTS);
            if ($upload) {
                $fileType = getFileExtension($_FILES['document']['name']);
                $fileName = $_FILES['document']['name'];
                $userId = Session::getUserId();
                
                $result = $docModel->uploadDocument(
                    $empId, 
                    $fileName, 
                    $fileType, 
                    $upload['relative_path'], 
                    $userId
                );
                
                $message = $result ? 'Document uploaded successfully!' : 'Failed to upload document';
                $messageType = $result ? 'success' : 'danger';
            } else {
                $message = 'Invalid file or file size exceeds limit';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $docId = sanitize($_POST['doc_id'] ?? '');
        
        if (empty($docId)) {
            $message = 'Document ID is required';
            $messageType = 'danger';
        } else {
            $result = $docModel->deleteDocument($docId);
            $message = $result ? 'Document deleted successfully!' : 'Failed to delete document';
            $messageType = $result ? 'success' : 'danger';
        }
    }
}

// Get data
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$documents = $docModel->getAllDocuments($limit, $offset);
$employees = $empModel->getAllEmployees();
$totalCount = count($docModel->getAllDocuments());
$totalPages = ceil($totalCount / $limit);

$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>HRMS Admin | Documents</title>
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
        .file-icon { width: 40px; height: 40px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: white; }
        .file-icon.pdf { background: #dc3545; }
        .file-icon.doc { background: #0d6efd; }
        .file-icon.xls { background: #28a745; }
        .file-icon.img { background: #6f42c1; }
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
                <a class="nav-link active" href="documents-admin.php"><i class="bi bi-file-earmark-arrow-down me-2"></i>Documents</a>
                <a class="nav-link" href="payroll.php"><i class="bi bi-cash-coin me-2"></i>Payroll</a>
            </div>
            <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
        </aside>

        <?php 
        $pageTitle = 'Document Management'; 
        include __DIR__ . '/partials/header.php'; 
        ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>

                    <small class="text-muted">Manage employee documents and files</small>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">+ Upload Document</button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card p-3">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>File</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Uploaded By</th>
                            <th>Uploaded Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($documents): ?>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td class="d-flex align-items-center gap-2">
                                    <?php
                                    $fileType = strtolower($doc['File_type']);
                                    $iconClass = 'file-icon';
                                    if (in_array($fileType, ['pdf'])) $iconClass .= ' pdf';
                                    elseif (in_array($fileType, ['doc', 'docx'])) $iconClass .= ' doc';
                                    elseif (in_array($fileType, ['xls', 'xlsx'])) $iconClass .= ' xls';
                                    elseif (in_array($fileType, ['jpg', 'jpeg', 'png'])) $iconClass .= ' img';
                                    ?>
                                    <span class="<?php echo $iconClass; ?>"><?php echo strtoupper(substr($fileType, 0, 3)); ?></span>
                                    <div>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($doc['File_name']); ?></div>
                                        <small class="text-muted"><?php echo $fileType; ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($doc['employee_name']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo strtoupper($doc['File_type']); ?></span></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($doc['uploader_name'] ?? 'Unknown'); ?></strong>
                                        <?php if (isset($doc['uploader_type'])): ?>
                                            <span class="badge <?php echo $doc['uploader_type'] === 'Admin' ? 'bg-primary' : 'bg-success'; ?> ms-1">
                                                <?php echo htmlspecialchars($doc['uploader_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo formatDate($doc['Uploaded_at']); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <?php 
                                        $canView = in_array(strtolower($doc['File_type']), ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                        if ($canView): 
                                        ?>
                                            <button class="btn btn-sm btn-outline-info" onclick="viewDocument(<?php echo $doc['Doc_id']; ?>, '<?php echo strtolower($doc['File_type']); ?>')" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="../backend/download-document.php?id=<?php echo $doc['Doc_id']; ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDoc(<?php echo $doc['Doc_id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No documents uploaded</td></tr>
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

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select class="form-select" name="emp_id" required>
                            <option value="">Choose an employee...</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['Emp_id']; ?>"><?php echo htmlspecialchars($emp['Emp_firstName'] . ' ' . $emp['Emp_lastName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Document</label>
                        <input type="file" class="form-control" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt">
                        <small class="text-muted">Max file size: 10MB. Allowed: PDF, DOC, XLS, JPG, PNG</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Document Modal -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 400px; max-height: 80vh; overflow: auto;">
                <div id="documentViewer" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading document...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a id="downloadDocumentLink" href="#" class="btn btn-primary" download>Download</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="doc_id" id="deleteDocId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this document? This action cannot be undone.</p>
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
function viewDocument(docId, fileType) {
    const viewer = document.getElementById('documentViewer');
    const downloadLink = document.getElementById('downloadDocumentLink');
    const docUrl = '../backend/download-document.php?id=' + docId + '&view=1';
    const downloadUrl = '../backend/download-document.php?id=' + docId;
    
    // Set download link
    downloadLink.href = downloadUrl;
    
    // Clear previous content
    viewer.innerHTML = '';
    
    // Show loading
    viewer.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading document...</p></div>';
    
    // Open modal
    const viewModal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
    viewModal.show();
    
    // Load document based on file type
    if (fileType === 'pdf') {
        // For PDF, use iframe
        const iframe = document.createElement('iframe');
        iframe.src = docUrl;
        iframe.style.width = '100%';
        iframe.style.height = '80vh';
        iframe.style.border = 'none';
        iframe.onload = function() {
            viewer.innerHTML = '';
            viewer.appendChild(iframe);
        };
        iframe.onerror = function() {
            viewer.innerHTML = '<div class="text-center p-4"><p class="text-danger">Failed to load PDF. <a href="' + downloadUrl + '">Download instead</a></p></div>';
        };
    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
        // For images, use img tag
        const img = document.createElement('img');
        img.src = docUrl;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '80vh';
        img.style.objectFit = 'contain';
        img.style.margin = 'auto';
        img.onload = function() {
            viewer.innerHTML = '';
            viewer.appendChild(img);
        };
        img.onerror = function() {
            viewer.innerHTML = '<div class="text-center p-4"><p class="text-danger">Failed to load image. <a href="' + downloadUrl + '">Download instead</a></p></div>';
        };
    } else {
        viewer.innerHTML = '<div class="text-center p-4"><p class="text-muted">Preview not available for this file type. <a href="' + downloadUrl + '">Download instead</a></p></div>';
    }
}

function deleteDoc(docId) {
    document.getElementById('deleteDocId').value = docId;
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteConfirmModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dark-mode.js?v=<?php echo time(); ?>"></script>
</body>
</html>