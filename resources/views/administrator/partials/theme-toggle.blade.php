@php
    $toggleId = $toggleId ?? 'themeToggle_' . uniqid();
    $size = $size ?? 'md'; // sm, md, lg
    $showLabel = $showLabel ?? false;
    $labelText = $labelText ?? __('Toggle Dark Mode');
@endphp

<!-- Modern Theme Toggle Component -->
<div class="theme-toggle-wrapper d-inline-flex align-items-center gap-2">
    @if($showLabel)
    <label for="{{ $toggleId }}" class="form-label mb-0 me-2">
        <i class="bi bi-moon-stars-fill me-1"></i>
        {{ $labelText }}
    </label>
    @endif

    <div class="form-check form-switch theme-switch">
        <input class="form-check-input"
               type="checkbox"
               id="{{ $toggleId }}"
               role="switch"
               aria-label="{{ $labelText }}"
               aria-checked="false"
               data-bs-toggle="tooltip"
               data-bs-placement="bottom"
               data-bs-title="{{ $labelText }}">
    </div>
</div>

<!-- Theme Toggle Styles -->
<style>
.theme-switch {
    position: relative;
}

.theme-switch .form-check-input {
    width: {{ $size === 'sm' ? '2.5rem' : ($size === 'lg' ? '4rem' : '3rem') }};
    height: {{ $size === 'sm' ? '1.25rem' : ($size === 'lg' ? '2rem' : '1.5rem') }};
    background-color: var(--bs-gray-300);
    border-color: var(--bs-gray-300);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.theme-switch .form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='white' d='M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z'/%3e%3c/svg%3e");
}

.theme-switch .form-check-input:not(:checked) {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='white' d='M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z'/%3e%3c/svg%3e");
}

.theme-switch .form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
    border-color: var(--bs-primary);
}

/* Dark mode specific styles */
[data-bs-theme="dark"] .theme-switch .form-check-input {
    background-color: var(--bs-gray-600);
    border-color: var(--bs-gray-600);
}

[data-bs-theme="dark"] .theme-switch .form-check-input:not(:checked) {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='%23fff' d='M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z'/%3e%3c/svg%3e");
}

/* Animation styles */
@keyframes themeSwitch {
    0% { transform: rotate(0deg); }
    50% { transform: rotate(180deg); }
    100% { transform: rotate(360deg); }
}

.theme-switch .form-check-input {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Size variations */
.theme-switch.size-sm .form-check-input {
    width: 2.5rem;
    height: 1.25rem;
}

.theme-switch.size-lg .form-check-input {
    width: 4rem;
    height: 2rem;
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .theme-switch .form-check-input {
        transition: none;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .theme-switch .form-check-input {
        border-width: 2px;
    }
}
</style>

<!-- Theme Toggle JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('{{ $toggleId }}');
    if (!toggle) return;

    // Initialize theme from localStorage or system preference
    const initTheme = () => {
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        let theme = 'light';
        if (savedTheme) {
            theme = savedTheme;
        } else if (systemPrefersDark) {
            theme = 'dark';
        }

        applyTheme(theme);
        toggle.checked = theme === 'dark';
    };

    // Apply theme to document
    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);

        // Update meta theme-color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.content = getComputedStyle(document.documentElement)
                .getPropertyValue('--bs-primary').trim() || '#0d6efd';
        }

        // Trigger custom event for other components
        const event = new CustomEvent('themeChanged', {
            detail: { theme, isDark: theme === 'dark' }
        });
        document.dispatchEvent(event);
    };

    // Handle toggle changes
    toggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'dark' : 'light';
        applyTheme(newTheme);

        // Add animation class
        document.body.classList.add('theme-transitioning');
        setTimeout(() => {
            document.body.classList.remove('theme-transitioning');
        }, 300);
    });

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            const systemTheme = e.matches ? 'dark' : 'light';
            applyTheme(systemTheme);
            toggle.checked = e.matches;
        }
    });

    // Handle keyboard navigation
    toggle.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.checked = !this.checked;
            this.dispatchEvent(new Event('change'));
        }
    });

    // Initialize theme on load
    initTheme();

    // Add transition class for smooth theme switching
    document.documentElement.style.setProperty('--theme-transition-duration', '0.3s');
    document.documentElement.style.setProperty('--theme-transition-timing-function', 'ease-in-out');
});

// Theme transition styles
const themeTransitionStyles = `
    .theme-transitioning *,
    .theme-transitioning *::before,
    .theme-transitioning *::after {
        transition: background-color 0.3s ease-in-out,
                    border-color 0.3s ease-in-out,
                    color 0.3s ease-in-out,
                    box-shadow 0.3s ease-in-out !important;
    }
`;

// Inject theme transition styles
const styleSheet = document.createElement('style');
styleSheet.textContent = themeTransitionStyles;
document.head.appendChild(styleSheet);
</script>