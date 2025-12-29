<?php
/**
 * Compact user bar for employee pages.
 * Expects: $user, $profilePicUrl, $initials (optional), $empData (optional).
 */

if (empty($initials) && isset($user['Emp_firstName'], $user['Emp_lastName'])) {
    $initials = getInitials($user['Emp_firstName'], $user['Emp_lastName']);
}

$displayName = trim(($user['Emp_firstName'] ?? '') . ' ' . ($user['Emp_lastName'] ?? ''));
$displayMeta = $empData['Category_name'] ?? ($user['Emp_email'] ?? 'Employee');
$picture = $profilePicUrl ?? null;
?>
<style>
    /* Hide old sidebar header avatar/name block */
    .sidebar .sidebar-header { display: none !important; }
</style>

<div class="d-flex align-items-center justify-content-end flex-wrap gap-3 mb-4 w-100">
    <div class="dropdown">
        <button class="d-flex align-items-center gap-3 text-decoration-none border-0 bg-transparent p-0" style="color:#0f172a;" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($picture)): ?>
                <img src="<?php echo htmlspecialchars($picture); ?>" alt="Profile" style="width:44px;height:44px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
                <div class="avatar" style="width:44px;height:44px;font-size:0.95rem;line-height:1;"><?php echo htmlspecialchars($initials ?? 'U'); ?></div>
            <?php endif; ?>
            <div class="text-end">
                <div class="fw-semibold"><?php echo htmlspecialchars($displayName ?: 'Employee'); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars($displayMeta); ?></small>
            </div>
            <i class="bi bi-chevron-down ms-1" style="color:#6b7280;"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Theme Toggle Button -->
        <button id="themeToggle" class="btn btn-ghost d-flex align-items-center justify-content-center" 
                type="button" 
                aria-label="Toggle theme" 
                style="width:40px;height:40px;border-radius:10px;padding:0;border:none;"
                onclick="toggleTheme()">
            <span id="themeIcon" style="font-size:1.2rem;">🌙</span>
        </button>

        <!-- Notification Button -->
        <a id="notifBell" href="notifications.php" class="position-relative border-0 bg-transparent p-0 text-decoration-none d-flex align-items-center justify-content-center" title="Notifications" aria-label="Notifications" style="width:40px;height:40px;">
            <i class="bi bi-bell" style="font-size:1.2rem;color:#0d6efd;"></i>
            <span id="notifBadge" style="display:none;position:absolute;top:-2px;right:-4px;background:#dc3545;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;line-height:1;">0</span>
            <span id="notifDot" style="display:none;position:absolute;top:2px;right:2px;width:10px;height:10px;background:#dc3545;border-radius:50%;box-shadow:0 0 0 2px #fff;"></span>
        </a>
    </div>
</div>


