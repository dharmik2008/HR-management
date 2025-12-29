<?php
/**
 * Consolidated Header Component
 * Usage: <?php include __DIR__ . '/partials/header-component.php'; ?>
 */
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
                    data-bs-toggle="dropdown" aria-expanded="false"
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
            
            <!-- Notification Dropdown Menu -->
            <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                <li class="dropdown-header bg-light py-2 px-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Notifications</strong>
                        <?php if ($unreadCount > 0): ?>
                            <form action="notifications.php" method="post" class="d-inline">
                                <input type="hidden" name="action" value="mark_all_read">
                                <button type="submit" class="btn btn-sm btn-link p-0">Mark all as read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
                <div id="notificationList">
                    <?php 
                    // Show a few recent notifications
                    $recentNotifications = array_slice($allNotifications ?? [], 0, 5);
                    if (!empty($recentNotifications)): 
                        foreach ($recentNotifications as $notif): 
                    ?>
                        <li class="dropdown-item p-3 border-bottom <?php echo $notif['Status'] === 'Unread' ? 'unread' : ''; ?>" 
                            style="white-space: normal;"
                            onclick="window.location.href='notifications.php'">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-2">
                                    <i class="bi <?php echo getNotificationIcon($notif['Type']); ?> text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($notif['Title']); ?></h6>
                                        <small class="text-muted"><?php echo timeAgo($notif['CreatedAt']); ?></small>
                                    </div>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($notif['Message']); ?></p>
                                </div>
                            </div>
                        </li>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <li class="dropdown-item text-muted text-center py-3">No notifications</li>
                    <?php endif; ?>
                </div>
                <li class="dropdown-divider m-0"></li>
                <li class="text-center py-2">
                    <a href="notifications.php" class="text-primary">View all notifications</a>
                </li>
            </ul>
        </div>

        <!-- Theme Toggle Button -->
        <button id="themeToggle" class="btn btn-ghost d-flex align-items-center justify-content-center" 
                type="button" 
                aria-label="Toggle theme" 
                style="width:44px;height:44px;border-radius:10px;padding:0;border:none;"
                onclick="toggleTheme()"
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

<script src="../assets/js/dark-mode.js"></script>
<script>
// Theme Toggle Functionality
function updateThemeIcon(isDark) {
    const themeIcons = document.querySelectorAll('#themeIcon, .theme-icon');
    themeIcons.forEach(icon => {
        icon.textContent = isDark ? '☀️' : '🌙';
        icon.closest('button')?.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });
    
    // Set a cookie to remember the theme preference
    document.cookie = `darkMode=${isDark}; path=/; max-age=31536000; samesite=lax`;
}

// Override the updateToggleIcon from dark-mode.js
window.updateToggleIcon = updateThemeIcon;

// Initialize theme icon on page load
function initThemeIcon() {
    const isDark = document.body.classList.contains('dark-mode');
    updateThemeIcon(isDark);
}

// Call init when DOM is loaded
document.addEventListener('DOMContentLoaded', initThemeIcon);

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check current theme and update icon
    const isDark = document.body.classList.contains('dark-mode');
    updateThemeIcon(isDark);
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Update notification badge on page load
    updateNotifBadge();
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

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Update notification badge on page load
    updateNotifBadge();
});
</script>
