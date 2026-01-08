(function () {
    function toggleNavDot(show) {
        const navLink = document.querySelector('a.nav-pill[href="notifications.php"]');
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
            if (!res.ok) return;
            const data = await res.json();
            const unread = parseInt(data.unread || 0, 10);
            const badge = document.getElementById('notifBadge');
            const dot = document.getElementById('notifDot');

            if (badge) {
                if (unread > 0) {
                    badge.textContent = unread > 99 ? '99+' : unread;
                    badge.style.display = 'inline-block';
                    if (dot) dot.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                    if (dot) dot.style.display = 'none';
                }
            }

            toggleNavDot(unread > 0);
        } catch (err) {
            console.error('Failed to update notification badge', err);
        }
    }

    async function markNotificationsRead() {
        try {
            await fetch('../backend/api/notifications_mark_read.php', { method: 'POST', credentials: 'same-origin' });
            const badge = document.getElementById('notifBadge');
            const dot = document.getElementById('notifDot');
            if (badge) badge.style.display = 'none';
            if (dot) dot.style.display = 'none';
            toggleNavDot(false);
        } catch (err) {
            console.error('Failed to mark notifications read', err);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateNotifBadge();
        setInterval(updateNotifBadge, 15000);
    });
})();

