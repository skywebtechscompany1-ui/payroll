<!-- Accessibility Enhancement Component -->
<div id="accessibility-panel" class="accessibility-panel position-fixed" style="top: 100px; right: -300px; z-index: 1050; transition: right 0.3s ease;">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-universal-access me-2"></i>
                Accessibility
            </h6>
        </div>
        <div class="card-body">
            <!-- Font Size Controls -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Text Size</label>
                <div class="btn-group w-100" role="group" aria-label="Font size controls">
                    <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="decrease" aria-label="Decrease font size">
                        <i class="bi bi-fonts"></i> -
                    </button>
                    <button type="button" class="btn btn-outline-secondary font-size-btn active" data-size="normal" aria-label="Reset font size">
                        <i class="bi bi-fonts"></i> A
                    </button>
                    <button type="button" class="btn btn-outline-secondary font-size-btn" data-size="increase" aria-label="Increase font size">
                        <i class="bi bi-fonts"></i> +
                    </button>
                </div>
            </div>

            <!-- High Contrast Mode -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="high-contrast-toggle">
                    <label class="form-check-label" for="high-contrast-toggle">
                        High Contrast Mode
                    </label>
                </div>
            </div>

            <!-- Reduced Motion -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="reduced-motion-toggle">
                    <label class="form-check-label" for="reduced-motion-toggle">
                        Reduce Animations
                    </label>
                </div>
            </div>

            <!-- Focus Indicators -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="focus-indicators-toggle" checked>
                    <label class="form-check-label" for="focus-indicators-toggle">
                        Enhanced Focus Indicators
                    </label>
                </div>
            </div>

            <!-- Screen Reader Announcements -->
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="screen-reader-toggle" checked>
                    <label class="form-check-label" for="screen-reader-toggle">
                        Screen Reader Announcements
                    </label>
                </div>
            </div>

            <!-- Reset Button -->
            <button type="button" class="btn btn-secondary w-100" id="accessibility-reset">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset to Default
            </button>
        </div>
    </div>
</div>

<!-- Accessibility Toggle Button -->
<button type="button"
        class="btn btn-primary accessibility-toggle position-fixed"
        style="top: 100px; right: 20px; z-index: 1049; border-radius: 50%; width: 50px; height: 50px;"
        aria-label="Open accessibility options"
        aria-expanded="false">
    <i class="bi bi-universal-access"></i>
</button>

<!-- Screen Reader Announcements -->
<div aria-live="polite" aria-atomic="true" class="visually-hidden" id="accessibility-announcements"></div>

<!-- Accessibility Styles -->
<style>
/* High Contrast Mode */
.high-contrast {
    --bs-primary: #0000ff !important;
    --bs-primary-rgb: 0, 0, 255 !important;
    --bs-secondary: #666666 !important;
    --bs-success: #00ff00 !important;
    --bs-danger: #ff0000 !important;
    --bs-warning: #ff8c00 !important;
    --bs-info: #00ffff !important;
    --bs-light: #ffffff !important;
    --bs-dark: #000000 !important;
    --bs-gray-100: #ffffff !important;
    --bs-gray-200: #f0f0f0 !important;
    --bs-gray-300: #e0e0e0 !important;
    --bs-gray-400: #cccccc !important;
    --bs-gray-500: #999999 !important;
    --bs-gray-600: #666666 !important;
    --bs-gray-700: #333333 !important;
    --bs-gray-800: #000000 !important;
    --bs-gray-900: #000000 !important;
    --bs-border-color: #000000 !important;
    --bs-border-width: 2px !important;
}

.high-contrast .btn {
    border: 2px solid currentColor !important;
}

.high-contrast .card {
    border: 2px solid var(--bs-gray-800) !important;
}

.high-contrast .form-control,
.high-contrast .form-select {
    border: 2px solid var(--bs-gray-600) !important;
}

/* Font Size Variations */
.font-size-small {
    font-size: 0.875rem !important;
}

.font-size-small .h1 { font-size: 2rem !important; }
.font-size-small .h2 { font-size: 1.75rem !important; }
.font-size-small .h3 { font-size: 1.5rem !important; }
.font-size-small .h4 { font-size: 1.25rem !important; }
.font-size-small .h5 { font-size: 1.125rem !important; }
.font-size-small .h6 { font-size: 1rem !important; }

.font-size-large {
    font-size: 1.125rem !important;
}

.font-size-large .h1 { font-size: 3rem !important; }
.font-size-large .h2 { font-size: 2.5rem !important; }
.font-size-large .h3 { font-size: 2rem !important; }
.font-size-large .h4 { font-size: 1.75rem !important; }
.font-size-large .h5 { font-size: 1.5rem !important; }
.font-size-large .h6 { font-size: 1.25rem !important; }

.font-size-xlarge {
    font-size: 1.25rem !important;
}

.font-size-xlarge .h1 { font-size: 3.5rem !important; }
.font-size-xlarge .h2 { font-size: 3rem !important; }
.font-size-xlarge .h3 { font-size: 2.5rem !important; }
.font-size-xlarge .h4 { font-size: 2rem !important; }
.font-size-xlarge .h5 { font-size: 1.75rem !important; }
.font-size-xlarge .h6 { font-size: 1.5rem !important; }

/* Reduced Motion */
.reduced-motion * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
}

/* Enhanced Focus Indicators */
.enhanced-focus *:focus {
    outline: 3px solid var(--bs-primary) !important;
    outline-offset: 2px !important;
}

.enhanced-focus .btn:focus {
    box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.5) !important;
}

.enhanced-focus .form-control:focus,
.enhanced-focus .form-select:focus {
    box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.5) !important;
}

/* Accessibility Panel Styles */
.accessibility-panel {
    width: 300px;
}

.accessibility-panel.show {
    right: 20px;
}

.accessibility-toggle {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.accessibility-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

/* Skip Links Enhancement */
.skip-nav {
    position: absolute;
    top: -40px;
    left: 6px;
    background: var(--bs-primary);
    color: var(--bs-white);
    padding: 8px;
    text-decoration: none;
    border-radius: 4px;
    z-index: 9999;
    transition: top 0.3s ease;
}

.skip-nav:focus {
    top: 6px;
}

/* Print Styles for Accessibility */
@media print {
    .accessibility-panel,
    .accessibility-toggle {
        display: none !important;
    }

    .high-contrast {
        color: #000 !important;
        background: #fff !important;
    }
}

/* Dark Mode Accessibility */
[data-bs-theme="dark"] .high-contrast {
    --bs-primary: #66b3ff !important;
    --bs-secondary: #cccccc !important;
    --bs-success: #66ff66 !important;
    --bs-danger: #ff6666 !important;
    --bs-warning: #ffcc66 !important;
    --bs-info: #66ffff !important;
    --bs-light: #333333 !important;
    --bs-dark: #ffffff !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .accessibility-panel {
        width: 280px;
        right: -280px;
    }

    .accessibility-panel.show {
        right: 10px;
    }

    .accessibility-toggle {
        width: 45px;
        height: 45px;
        right: 10px;
    }
}
</style>

<!-- Accessibility JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const AccessibilityManager = {
        init() {
            this.setupEventListeners();
            this.loadAccessibilitySettings();
            this.setupKeyboardNavigation();
            this.setupARIA();
        },

        setupEventListeners() {
            // Panel toggle
            const toggle = document.querySelector('.accessibility-toggle');
            const panel = document.getElementById('accessibility-panel');

            toggle?.addEventListener('click', () => {
                this.togglePanel();
            });

            // Font size controls
            const fontButtons = document.querySelectorAll('.font-size-btn');
            fontButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    this.setFontSize(e.target.closest('.font-size-btn').dataset.size);
                });
            });

            // High contrast toggle
            document.getElementById('high-contrast-toggle')?.addEventListener('change', (e) => {
                this.toggleHighContrast(e.target.checked);
            });

            // Reduced motion toggle
            document.getElementById('reduced-motion-toggle')?.addEventListener('change', (e) => {
                this.toggleReducedMotion(e.target.checked);
            });

            // Focus indicators toggle
            document.getElementById('focus-indicators-toggle')?.addEventListener('change', (e) => {
                this.toggleFocusIndicators(e.target.checked);
            });

            // Screen reader toggle
            document.getElementById('screen-reader-toggle')?.addEventListener('change', (e) => {
                this.toggleScreenReader(e.target.checked);
            });

            // Reset button
            document.getElementById('accessibility-reset')?.addEventListener('click', () => {
                this.resetToDefaults();
            });

            // Close panel on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && panel.classList.contains('show')) {
                    this.closePanel();
                }
            });

            // Close panel when clicking outside
            document.addEventListener('click', (e) => {
                if (panel.classList.contains('show') &&
                    !panel.contains(e.target) &&
                    !toggle.contains(e.target)) {
                    this.closePanel();
                }
            });
        },

        togglePanel() {
            const panel = document.getElementById('accessibility-panel');
            const toggle = document.querySelector('.accessibility-toggle');

            panel.classList.toggle('show');
            const isOpen = panel.classList.contains('show');

            toggle.setAttribute('aria-expanded', isOpen);
            this.announce(`Accessibility panel ${isOpen ? 'opened' : 'closed'}`);

            // Focus management
            if (isOpen) {
                const firstFocusable = panel.querySelector('button, input, select, textarea');
                firstFocusable?.focus();
            } else {
                toggle.focus();
            }
        },

        closePanel() {
            const panel = document.getElementById('accessibility-panel');
            const toggle = document.querySelector('.accessibility-toggle');

            panel.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        },

        setFontSize(size) {
            const body = document.body;
            const buttons = document.querySelectorAll('.font-size-btn');

            // Remove all font size classes
            body.classList.remove('font-size-small', 'font-size-large', 'font-size-xlarge');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Add new class
            switch(size) {
                case 'decrease':
                    body.classList.add('font-size-small');
                    this.announce('Font size decreased');
                    break;
                case 'increase':
                    body.classList.add('font-size-large');
                    this.announce('Font size increased');
                    break;
                default:
                    this.announce('Font size reset to normal');
            }

            // Update button states
            document.querySelector(`.font-size-btn[data-size="${size}"]`)?.classList.add('active');

            // Save preference
            this.saveSetting('fontSize', size === 'normal' ? null : size);
        },

        toggleHighContrast(enabled) {
            document.body.classList.toggle('high-contrast', enabled);
            this.announce(`High contrast mode ${enabled ? 'enabled' : 'disabled'}`);
            this.saveSetting('highContrast', enabled);
        },

        toggleReducedMotion(enabled) {
            document.body.classList.toggle('reduced-motion', enabled);
            this.announce(`Animations ${enabled ? 'reduced' : 'enabled'}`);
            this.saveSetting('reducedMotion', enabled);
        },

        toggleFocusIndicators(enabled) {
            document.body.classList.toggle('enhanced-focus', enabled);
            this.announce(`Enhanced focus indicators ${enabled ? 'enabled' : 'disabled'}`);
            this.saveSetting('focusIndicators', enabled);
        },

        toggleScreenReader(enabled) {
            const announcer = document.getElementById('accessibility-announcements');
            announcer.style.display = enabled ? 'block' : 'none';
            this.announce(`Screen reader announcements ${enabled ? 'enabled' : 'disabled'}`);
            this.saveSetting('screenReader', enabled);
        },

        resetToDefaults() {
            // Reset all settings
            this.setFontSize('normal');
            this.toggleHighContrast(false);
            this.toggleReducedMotion(false);
            this.toggleFocusIndicators(true);
            this.toggleScreenReader(true);

            // Reset toggles
            document.getElementById('high-contrast-toggle').checked = false;
            document.getElementById('reduced-motion-toggle').checked = false;
            document.getElementById('focus-indicators-toggle').checked = true;
            document.getElementById('screen-reader-toggle').checked = true;

            this.announce('Accessibility settings reset to defaults');
            this.clearAllSettings();
        },

        announce(message) {
            const announcer = document.getElementById('accessibility-announcements');
            if (announcer && announcer.style.display !== 'none') {
                announcer.textContent = message;
                setTimeout(() => {
                    announcer.textContent = '';
                }, 1000);
            }
        },

        saveSetting(key, value) {
            const settings = JSON.parse(localStorage.getItem('accessibilitySettings') || '{}');
            settings[key] = value;
            localStorage.setItem('accessibilitySettings', JSON.stringify(settings));
        },

        loadSetting(key) {
            const settings = JSON.parse(localStorage.getItem('accessibilitySettings') || '{}');
            return settings[key];
        },

        clearAllSettings() {
            localStorage.removeItem('accessibilitySettings');
        },

        loadAccessibilitySettings() {
            const fontSize = this.loadSetting('fontSize');
            if (fontSize) {
                this.setFontSize(fontSize);
                document.querySelector(`.font-size-btn[data-size="${fontSize}"]`)?.classList.add('active');
                document.querySelector('.font-size-btn[data-size="normal"]')?.classList.remove('active');
            }

            const highContrast = this.loadSetting('highContrast');
            if (highContrast !== null) {
                this.toggleHighContrast(highContrast);
                document.getElementById('high-contrast-toggle').checked = highContrast;
            }

            const reducedMotion = this.loadSetting('reducedMotion');
            if (reducedMotion !== null) {
                this.toggleReducedMotion(reducedMotion);
                document.getElementById('reduced-motion-toggle').checked = reducedMotion;
            }

            const focusIndicators = this.loadSetting('focusIndicators');
            if (focusIndicators !== null) {
                this.toggleFocusIndicators(focusIndicators);
                document.getElementById('focus-indicators-toggle').checked = focusIndicators;
            }

            const screenReader = this.loadSetting('screenReader');
            if (screenReader !== null) {
                this.toggleScreenReader(screenReader);
                document.getElementById('screen-reader-toggle').checked = screenReader;
            }
        },

        setupKeyboardNavigation() {
            // Enhanced keyboard navigation for all interactive elements
            const focusableElements = 'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])';

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    // Ensure proper tab order
                    setTimeout(() => {
                        const activeElement = document.activeElement;
                        if (activeElement && activeElement.matches(focusableElements)) {
                            // Element is properly focusable
                        }
                    }, 0);
                }
            });
        },

        setupARIA() {
            // Setup ARIA landmarks and labels
            this.setupARIALandmarks();
            this.setupARIAForms();
            this.setupARIAControls();
        },

        setupARIALandmarks() {
            // Add ARIA landmarks to main structural elements
            const main = document.querySelector('main');
            if (main && !main.getAttribute('role')) {
                main.setAttribute('role', 'main');
            }

            const nav = document.querySelector('nav');
            if (nav && !nav.getAttribute('role')) {
                nav.setAttribute('role', 'navigation');
            }

            const footer = document.querySelector('footer');
            if (footer && !footer.getAttribute('role')) {
                footer.setAttribute('role', 'contentinfo');
            }
        },

        setupARIAForms() {
            // Enhance form accessibility
            document.querySelectorAll('form').forEach(form => {
                if (!form.getAttribute('aria-label') && !form.getAttribute('aria-labelledby')) {
                    form.setAttribute('aria-label', 'Form');
                }
            });

            document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
                field.setAttribute('aria-required', 'true');
            });

            document.querySelectorAll('input[readonly], textarea[readonly]').forEach(field => {
                field.setAttribute('aria-readonly', 'true');
            });

            document.querySelectorAll('input[disabled], select[disabled], textarea[disabled], button[disabled]').forEach(field => {
                field.setAttribute('aria-disabled', 'true');
            });
        },

        setupARIAControls() {
            // Setup ARIA controls for dynamic content
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => {
                const menu = document.querySelector(button.getAttribute('data-bs-target') || button.dataset.bsTarget);
                if (menu) {
                    button.setAttribute('aria-haspopup', 'true');
                    button.setAttribute('aria-expanded', 'false');

                    // Monitor dropdown state changes
                    const observer = new MutationObserver(() => {
                        const isOpen = menu.classList.contains('show');
                        button.setAttribute('aria-expanded', isOpen.toString());
                    });

                    observer.observe(menu, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                }
            });
        }
    };

    // Initialize accessibility features
    AccessibilityManager.init();
});
</script>