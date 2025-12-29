<?php
/**
 * Sidebar Footer Component for Admin Panel
 * Includes Theme Toggle and Logout Button
 * Usage: <?php include __DIR__ . '/partials/sidebar-footer.php'; ?>
 */
?>
<div class="sidebar-footer mt-auto pt-3" style="margin-top: auto !important;">
    <!-- Logout Button -->
    <a href="../frontend/logout.php" class="btn btn-logout w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
        <i class="bi-box-arrow-right"></i>Logout
    </a>
</div>

<style>
    /* Sidebar Footer Styles */
    .sidebar-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid transparent;
    }
    
    body.dark-mode .sidebar-footer {
        border-top-color: #334155;
    }
</style>

