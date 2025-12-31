<?php
/**
 * Reusable Header Component for Admin Panel
 * Usage: <?php include __DIR__ . '/partials/header.php'; ?>
 * 
 * Variables needed:
 * - $pageTitle: Title of the current page
 * - $user: User data array (should be set in parent page)
 * - $initials: User initials (should be set in parent page)
 */

// Get user initials if not set
if (!isset($initials) && isset($user['Hr_firstName'], $user['Hr_lastName'])) {
    $initials = getInitials($user['Hr_firstName'], $user['Hr_lastName']);
}
?>
<div class="col-lg-10 col-md-9 p-0">
<div class=" ms-auto d-flex align-items-center gap-2 p-3 shadow-sm justify-content-end bg-white ">
            <!-- Theme Toggle Button -->

            <button class="theme-toggle-btn btn btn-ghost d-flex align-items-center justify-content-center" type="button" 
                    aria-label="Toggle theme" 
                    style="width:40px;height:40px;border-radius:10px;padding:0;border:none;">
                <span class="theme-icon" style="font-size:1.2rem;">☀️</span>
            </button>
            
            <!-- Logout Button -->
            <a href="../frontend/logout.php" class="btn btn-outline-danger btn-sm d-flex align-items-center">
                <i class="bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
<main class="p-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <h4 class="mb-0"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h4>
        
    </div>
