<?php
/**
 * Consolidated Header Component
 * Usage: <?php include __DIR__ . '/partials/header-component.php'; ?>
 */

// Safety initialization for unread count to prevent warnings on pages where it's not pre-defined
if (!isset($unreadCount)) {
    if (isset($db) && class_exists('Session') && Session::getUserId()) {
        if (!class_exists('NotificationModel')) {
            require_once __DIR__ . '/../../backend/models/NotificationModel.php';
        }
        $nm = new NotificationModel($db);
        $unreadCount = $nm->getUnreadCount('employee', Session::getUserId());
    } else {
        $unreadCount = 0;
    }
}
?>
<header class="d-flex flex-wrap align-items-center justify-content-between mb-4 position-relative">
    <div>
        <h4 class="mb-1"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h4>
        <?php if (!empty($pageSubtitle)): ?>
            <small class="text-muted"><?php echo htmlspecialchars($pageSubtitle); ?></small>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <!-- Notification Bell with Dropdown -->
        <div class="dropdown" style="position: relative;">
            <button id="notifBell" class="btn btn-ghost d-flex align-items-center position-relative" 
                    style="width:44px;height:44px;border-radius:10px;padding:0;border:none;"
                    title="Notifications" aria-label="Notifications">
                <i class="bi-bell" style="font-size:1.2rem;color:#0d6efd;margin:auto;"></i>
                <?php if ($unreadCount > 0): ?>
                    <span id="notifBadge" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" 
                          style="font-size: 0.6rem; padding: 0.25em 0.4em;">
                        <?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?>
                    </span>
                <?php else: ?>
                    <span id="notifBadge" class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" 
                          style="display:none; font-size: 0.6rem; padding: 0.25em 0.4em;">0</span>
                <?php endif; ?>
            </button>
            
        </div>

        <!-- Theme Toggle Button -->
        <button id="themeToggle" class="theme-toggle-btn btn btn-ghost d-flex align-items-center justify-content-center" 
                type="button" 
                aria-label="Toggle theme" 
                style="width:44px;height:44px;border-radius:10px;padding:0;border:none;"
                title="Toggle dark/light mode">
            <span id="themeIcon" style="font-size:1.2rem;">
                <?php echo (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true') ? '☀️' : '🌙'; ?>
            </span>
        </button>

        <!-- Profile Section -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" 
               id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if (!empty($headerProfilePic)): ?>
                    <img src="<?php echo htmlspecialchars($headerProfilePic); ?>" 
                         alt="Profile" 
                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e9ecef;">
                <?php else: ?>
                    <div class="avatar" style="width:40px;height:40px;font-size:0.95rem;line-height:40px;">
                        <?php echo $initials ?? 'U'; ?>
                    </div>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown" style="min-width: 200px;">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>

                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<style>
    /* Header specific styles */
    .dropdown-menu {
        z-index: 1050 !important;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .unread {
        background-color: #f8f9fa;
        border-left: 3px solid #0d6efd !important;
    }
    
    .notification-item {
        transition: background-color 0.2s;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
</style>

<!-- Theme logic handled by global assets/js/dark-mode.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Update notification badge on page load
    updateNotifBadge();

    // Notification bell click handling
    const notifBell = document.getElementById('notifBell');
    if (notifBell) {
        notifBell.addEventListener('click', function() {
            window.location.href = 'notifications.php';
        });
    }
});

// Notification polling and handling
function updateNotifBadge() {
    fetch('../backend/api/notifications_count.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            
            const count = parseInt(data.unread || 0, 10);
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(err => console.error('Failed to update notification badge', err));
}

// Update notification badge every 30 seconds
setInterval(updateNotifBadge, 30000);

</script>
