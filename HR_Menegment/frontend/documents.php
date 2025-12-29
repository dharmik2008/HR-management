<?php
require_once __DIR__ . '/../backend/bootstrap.php';
Auth::requireEmployee();

$empId = Session::getUserId();
$docModel = new DocumentModel($db);
$empModel = new EmployeeModel($db);

$message = '';
$messageType = '';

// Get employee data
$empData = $empModel->getEmployeeById($empId);
$user = Session::getUser();
$initials = getInitials($empData['Emp_firstName'], $empData['Emp_lastName']);
$profilePicUrl = getProfilePicUrl($empData['Profile_pic'] ?? null);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    
    if ($action === 'upload') {
        if (!isset($_FILES['document']) || $_FILES['document']['size'] === 0) {
            $message = 'Please select a document to upload';
            $messageType = 'danger';
        } else {
            $upload = saveUploadedFile($_FILES['document'], UPLOADS_DOCUMENTS);
            if ($upload) {
                $fileType = getFileExtension($_FILES['document']['name']);
                $fileName = $_FILES['document']['name'];
                
                $result = $docModel->uploadDocument(
                    $empId, 
                    $fileName, 
                    $fileType, 
                    $upload['relative_path'], 
                    $empId
                );
                
                $message = $result ? 'Document uploaded successfully!' : 'Failed to upload document';
                $messageType = $result ? 'success' : 'danger';
            } else {
                $message = 'Invalid file or file size exceeds limit (max 10MB)';
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

// Get employee documents
$documents = $docModel->getDocumentsByEmployee($empId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assets/HELIX.png">
    <title>Documents | HRMS</title>
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
        .file-icon { width: 50px; height: 50px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 0.75rem; }
        .file-icon.pdf { background: #dc3545; }
        .file-icon.doc { background: #0d6efd; }
        .file-icon.docx { background: #0d6efd; }
        .file-icon.xls { background: #28a745; }
        .file-icon.xlsx { background: #28a745; }
        .file-icon.jpg { background: #6f42c1; }
        .file-icon.png { background: #6f42c1; }
        .file-icon.txt { background: #6c757d; }
        .upload-zone { border: 2px dashed #0d6efd; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: 0.2s; }
        .upload-zone:hover { background: #e7f1ff; }
        .upload-zone.dragover { background: #e7f1ff; border-color: #0d6efd; }
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
                <a class="nav-pill active" href="documents.php"><i class="bi-file-earmark-text"></i>Documents</a>
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
            $pageTitle = 'Documents';
            $pageSubtitle = 'View and manage your documents';
            $pageTitle = 'Documents';
            $pageSubtitle = 'Manage your documents';
            $headerProfilePic = $profilePicUrl;
            include __DIR__ . '/partials/header-component.php'; 
            ?>
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                <small class="text-muted">Upload and manage your documents</small>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <i class="bi-plus"></i> Upload Document
                </button>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Documents List -->
            <div class="row g-3">
                <?php if ($documents): ?>
                    <?php foreach ($documents as $doc): ?>
                    <?php
                    $fileType = strtolower($doc['File_type']);
                    $iconClass = 'file-icon ' . $fileType;
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card p-4">
                            <div class="d-flex gap-3 mb-3">
                                <span class="<?php echo $iconClass; ?>"><?php echo strtoupper(substr($fileType, 0, 3)); ?></span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" title="<?php echo htmlspecialchars($doc['File_name']); ?>">
                                        <?php echo htmlspecialchars(substr($doc['File_name'], 0, 30)); ?>
                                        <?php if (strlen($doc['File_name']) > 30) echo '...'; ?>
                                    </h6>
                                    <small class="text-muted d-block"><?php echo formatDate($doc['Uploaded_at']); ?></small>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> Uploaded by: 
                                        <strong><?php echo htmlspecialchars($doc['uploader_name'] ?? 'Unknown'); ?></strong>
                                        <?php if (isset($doc['uploader_type']) && $doc['uploader_type'] === 'Admin'): ?>
                                            <span class="badge bg-primary ms-1">Admin</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php 
                                $canView = in_array(strtolower($doc['File_type']), ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                if ($canView): 
                                ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewDocument(<?php echo $doc['Doc_id']; ?>, '<?php echo strtolower($doc['File_type']); ?>')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                <?php endif; ?>
                                <a href="../backend/download-document.php?id=<?php echo $doc['Doc_id']; ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteDoc(<?php echo $doc['Doc_id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card p-5 text-center">
                            <i class="bi bi-file-earmark" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                            <p class="text-muted mb-0">No documents uploaded yet</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-cloud-arrow-up" style="font-size: 2rem; color: #0d6efd; display: block; margin-bottom: 10px;"></i>
                        <p class="mb-1"><strong>Click to upload</strong> or drag and drop</p>
                        <small class="text-muted">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TXT (Max 10MB)</small>
                    </div>
                    <input type="file" id="fileInput" name="document" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt" required>
                    <div id="fileName" class="mt-2"></div>
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
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const fileName = document.getElementById('fileName');

// Drag and drop
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    updateFileName();
});

fileInput.addEventListener('change', updateFileName);

function updateFileName() {
    if (fileInput.files.length > 0) {
        fileName.innerHTML = '<small class="text-success">✓ ' + fileInput.files[0].name + '</small>';
    }
}

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
<script src="../assets/js/dark-mode.js"></script>
</body>
</html>