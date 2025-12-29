<?php
/**
 * Reusable Header Component for Employee Panel
 * Usage: <?php include __DIR__ . '/partials/header.php'; ?>
 * 
 * Variables needed:
 * - $pageTitle: Title of the current page
 * - $pageSubtitle: Subtitle/description of the current page (optional)
 * - $initials: User initials (should be set in parent page)
 * - $notification: NotificationModel instance (optional, for badge)
 * - $empId: Employee ID (optional, for notification badge)
 */

// Get unread notifications count if available
$unreadCount = 0;
if (isset($notification) && isset($empId)) {
    $unreadCount = (int) $notification->getUnreadCount('employee', $empId);
}
?>
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1"><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?></h4>
        <?php if (!empty($pageSubtitle)): ?>
            <small class="text-muted"><?php echo htmlspecialchars($pageSubtitle); ?></small>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <a href="leaves.php" class="btn btn-outline-primary">Request Leave</a>

        <div class="d-flex align-items-center gap-2">
            <button id="notifBell" class="btn btn-ghost d-flex align-items-center position-relative" title="Notifications" aria-label="Notifications" style="width:44px;height:44px;border-radius:10px;padding:0;border:none;">
                <i class="bi-bell" style="font-size:1.2rem;color:#0d6efd;margin:auto;"></i>
                <?php if ($unreadCount > 0): ?>
                    <span id="notifBadge" style="position:absolute;top:6px;right:6px;background:#dc3545;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;line-height:1;"><?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?></span>
                    <span id="notifDot" style="position:absolute;top:8px;right:8px;width:10px;height:10px;background:#dc3545;border-radius:50%;box-shadow:0 0 0 2px #fff;"></span>
                <?php else: ?>
                    <span id="notifBadge" style="display:none;position:absolute;top:6px;right:6px;background:#dc3545;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;line-height:1;">0</span>
                    <span id="notifDot" style="display:none;position:absolute;top:8px;right:8px;width:10px;height:10px;background:#dc3545;border-radius:50%;box-shadow:0 0 0 2px #fff;"></span>
                <?php endif; ?>
            </button>
            
            <!-- Theme Toggle Button -->
            <button class="theme-toggle-btn btn btn-ghost d-flex align-items-center justify-content-center" type="button" aria-label="Toggle theme" style="width:44px;height:44px;border-radius:10px;padding:0;border:none;">
                <span class="theme-icon" style="font-size:1.2rem;">☀️</span>
            </button>
        </div>

        <?php
            $rawPic = isset($user['Profile_pic']) ? $user['Profile_pic'] : null;
            $headerProfilePic = $profilePicUrl ?? ($rawPic ? getProfilePicUrl($rawPic) : null);
        ?>
        <a href="profile.php" class="d-flex align-items-center ms-1" title="Profile" aria-label="Profile">
            <?php if (!empty($headerProfilePic)): ?>
                <img src="<?php echo htmlspecialchars($headerProfilePic); ?>" alt="Profile" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <?php else: ?>
                <div class="avatar" style="width:40px;height:40px;font-size:0.95rem;line-height:1;"><?php echo $initials ?? 'U'; ?></div>
            <?php endif; ?>
        </a>
    </div>
</div>

<script>
    // Notification polling script (can be placed in a separate file and included globally)
    function toggleNavDot(show) {
        const navLink = document.querySelector('a[href="notifications.php"]');
        if (!navLink) return;
        navLink.style.position = 'relative';
        let dot = navLink.querySelector('.navNotifDot');
        if (!dot) {
            dot = document.createElement('span');
            dot.className = 'navNotifDot';
            dot.style.cssText = 'position:absolute;top:6px;right:10px;width:10px;height:10px;background:#dc3545;border-radius:50%;box-shadow:0 0 0 2px #fff;display:none;';
            navLink.appendChild(dot);
        }
        dot.style.display = show ? 'block' : 'none';
    }

    async function updateNotifBadge() {
        try {
            const res = await fetch('../backend/api/notifications_count.php', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response not ok');
            const data = await res.json();
            const badge = document.getElementById('notifBadge');
            const dot = document.getElementById('notifDot');
            if (!badge) return;
            const n = parseInt(data.unread || 0, 10);
            if (n > 0) {
                badge.textContent = n > 99 ? '99+' : n;
                badge.style.display = 'inline-block';
                if (dot) dot.style.display = 'block';
                toggleNavDot(true);
            } else {
                badge.style.display = 'none';
                if (dot) dot.style.display = 'none';
                toggleNavDot(false);
            }
        } catch (err) {
            console.error('Failed to update notification badge', err);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const bell = document.getElementById('notifBell');
        if (bell) {
            bell.addEventListener('click', async function () {
                try {
                    const res = await fetch('../backend/api/notifications_mark_read.php', { method: 'POST', credentials: 'same-origin' });
                    if (res.ok) {
                        const badge = document.getElementById('notifBadge');
                        const dot = document.getElementById('notifDot');
                        if (badge) badge.style.display = 'none';
                        if (dot) dot.style.display = 'none';
                        toggleNavDot(false);
                    }
                } catch (err) {
                    console.error('Failed to mark notifications read', err);
                }
                window.location.href = 'notifications.php';
            });
        }

        updateNotifBadge();
        setInterval(updateNotifBadge, 15000);
    });
</script>
