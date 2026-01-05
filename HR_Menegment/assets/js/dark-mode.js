/**
 * Global Dark Mode Toggle
 * Handles theme switching and persistence via localStorage
 * Applies theme immediately to prevent flash of unstyled content
 */

(function () {
    'use strict';

    // Prevent double initialization
    if (window.__HRMS_DARK_MODE_INIT__) {
        console.log('Dark mode already initialized, skipping...');
        return;
    }
    window.__HRMS_DARK_MODE_INIT__ = true;

    // Theme storage key
    const THEME_STORAGE_KEY = 'hrms_theme';

    // Check if dark mode is enabled in localStorage
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
    const isDarkMode = savedTheme === 'dark';

    // Apply dark mode class to body if needed
    if (isDarkMode) {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
    }

    // Theme values
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    /**
     * Apply theme immediately (before DOM ready) to prevent flash
     */
    function applyThemeImmediate() {
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        if (savedTheme === THEME_DARK) {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        } else {
            document.documentElement.classList.remove('dark-mode');
            document.body.classList.remove('dark-mode');
        }
    }

    /**
     * Apply dark mode by adding class to body
     */
    function applyDarkMode() {
        document.documentElement.classList.add('dark-mode');
        document.body.classList.add('dark-mode');
        localStorage.setItem(THEME_STORAGE_KEY, THEME_DARK);
        // Also set a cookie to remember the theme preference
        document.cookie = `darkMode=true; path=/; max-age=31536000; samesite=lax`;
    }

    /**
     * Apply light mode by removing dark mode class from body
     */
    function applyLightMode() {
        document.documentElement.classList.remove('dark-mode');
        document.body.classList.remove('dark-mode');
        localStorage.setItem(THEME_STORAGE_KEY, THEME_LIGHT);
        // Also set a cookie to remember the theme preference
        document.cookie = `darkMode=false; path=/; max-age=31536000; samesite=lax`;
    }

    /**
     * Update the toggle button icon based on current theme
     * @param {boolean} isDark - Whether dark mode is active
     */
    function updateToggleIcon(isDark) {
        const themeIcons = document.querySelectorAll('#themeIcon, .theme-icon');
        themeIcons.forEach(icon => {
            icon.textContent = isDark ? '☀️' : '🌙';
            icon.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        });
    }

    /**
     * Toggle between dark and light mode
     */
    function toggleTheme() {
        const isDark = document.body.classList.contains('dark-mode');

        if (isDark) {
            applyLightMode();
            updateToggleIcon(false);
        } else {
            applyDarkMode();
            updateToggleIcon(true);
        }

        // Update all theme toggle icons
        updateThemeIcons(!isDark);

        // Dispatch event for other components
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { isDark: !isDark } }));
    }

    /**
     * Update all theme toggle icons
     */
    function updateThemeIcons(isDark) {
        const themeIcons = document.querySelectorAll('#themeIcon, .theme-icon, .theme-toggle-icon');
        themeIcons.forEach(icon => {
            icon.textContent = isDark ? '☀️' : '🌙';
            icon.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        });
    }

    /**
     * Initialize theme on page load
     */
    function initTheme() {
        console.log('Dark mode script initializing...');
        // Check localStorage for saved theme preference
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);

        if (savedTheme === THEME_DARK) {
            applyDarkMode();
        } else {
            applyLightMode();
        }
    }

    // Apply theme immediately to prevent flash
    applyThemeImmediate();

    // Event Delegation for Theme Toggle
    // This handles any .theme-toggle-btn present now or added later
    document.addEventListener('click', function (event) {
        const toggleBtn = event.target.closest('.theme-toggle-btn');
        if (toggleBtn) {
            console.log('Theme toggle button clicked:', toggleBtn.id || 'anonymous');
            event.preventDefault(); // Prevent jump if it's a link
            toggleTheme();
        }
    });

    // Initialize theme when DOM is ready or immediately if already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initTheme();
            const isDark = document.body.classList.contains('dark-mode');
            updateThemeIcons(isDark);
        });
    } else {
        initTheme();
        const isDark = document.body.classList.contains('dark-mode');
        updateThemeIcons(isDark);
    }

    // Update icons on theme change (for browser sync or other theme changes)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                const isDark = document.body.classList.contains('dark-mode');
                updateToggleIcon(isDark);
            }
        });
    });

    // Start observing the body for class changes
    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    // Expose toggle function globally (optional, for manual triggering)
    window.toggleTheme = toggleTheme;
})();

