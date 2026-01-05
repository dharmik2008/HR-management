<?php
/**
 * Global Fixed Bottom-Right Theme Toggle Button Component
 * Usage: <?php include __DIR__ . '/partials/theme-toggle.php'; ?>
 * 
 * This component creates a fixed position theme toggle button in the bottom-right corner
 * that works across all pages and persists theme preference via localStorage.
 * All styles are in assets/css/dark-mode.css (no inline CSS).
 */
?>
<button class="theme-toggle-btn fixed-bottom-right" type="button" aria-label="Toggle theme">
    <span class="theme-icon">☀️</span>
</button>
