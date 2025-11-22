<?php // \Artisan::call('view:clear'); ?>

<!-- jQuery (needed for some legacy plugins) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<!-- Bootstrap 5.3.3 Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Modern JavaScript Libraries -->

<!-- DataTables for Bootstrap 5 -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Select2 for Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Modern Date Picker (Flatpickr) -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- InputMask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/inputmask.min.js"></script>

<!-- Moment.js for date manipulation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<!-- Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Color Picker -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js"></script>

<!-- Validation JS (if exists) -->
@if(file_exists(public_path('public/js/validation.js')))
<script src="{{ asset('public/js/validation.js') }}"></script>
@endif

<!-- Animate.css initialization (if needed) -->
@if(file_exists(public_path('public/wow/wow.min.js')))
<script src="{{ asset('public/wow/wow.min.js') }}"></script>
<script>
    // Initialize WOW.js only if element exists and reduced motion is not preferred
    if (typeof WOW !== 'undefined' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        new WOW().init();
    }
</script>
@endif

<!-- Modern JavaScript for dark mode and accessibility -->
<script>
// Dark mode functionality
class DarkModeManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupDarkModeToggle();
        this.setInitialTheme();
        this.setupSystemPreferenceListener();
    }

    setupDarkModeToggle() {
        const toggle = document.getElementById('dark-mode-toggle');
        if (toggle) {
            toggle.addEventListener('click', () => this.toggleDarkMode());
        }
    }

    setInitialTheme() {
        const savedTheme = localStorage.getItem('darkMode');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
            this.enableDarkMode();
        }
    }

    setupSystemPreferenceListener() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('darkMode')) {
                if (e.matches) {
                    this.enableDarkMode();
                } else {
                    this.disableDarkMode();
                }
            }
        });
    }

    toggleDarkMode() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        if (newTheme === 'dark') {
            this.enableDarkMode();
        } else {
            this.disableDarkMode();
        }
    }

    enableDarkMode() {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('darkMode', 'dark');
        this.updateDarkModeIcon('sun');

        // Update Chart.js for dark mode
        this.updateChartsDarkMode(true);
    }

    disableDarkMode() {
        document.documentElement.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('darkMode', 'light');
        this.updateDarkModeIcon('moon');

        // Update Chart.js for light mode
        this.updateChartsDarkMode(false);
    }

    updateDarkModeIcon(icon) {
        const toggle = document.getElementById('dark-mode-toggle');
        if (toggle) {
            toggle.innerHTML = `<i class="bi bi-${icon}-fill"></i>`;
        }
    }

    updateChartsDarkMode(isDark) {
        if (typeof Chart !== 'undefined') {
            const textColor = isDark ? '#dee2e6' : '#212529';
            const gridColor = isDark ? '#495057' : '#e9ecef';

            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = gridColor;

            // Update existing charts
            Chart.helpers.each(Chart.instances, (instance) => {
                instance.options.plugins.legend.labels.color = textColor;
                instance.options.scales && Object.keys(instance.options.scales).forEach(axis => {
                    instance.options.scales[axis].ticks.color = textColor;
                    instance.options.scales[axis].grid.color = gridColor;
                });
                instance.update();
            });
        }
    }
}

// Initialize dark mode manager
const darkModeManager = new DarkModeManager();

// Modern notification system
class NotificationManager {
    static show(message, type = 'success', duration = 4000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow`;
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        document.body.appendChild(notification);

        // Auto-dismiss
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 150);
            }
        }, duration);
    }
}

// Accessibility enhancements
class AccessibilityManager {
    constructor() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupAriaLiveRegion();
    }

    setupKeyboardNavigation() {
        // Handle Escape key for modals and dropdowns
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close open modals
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    const modal = bootstrap.Modal.getInstance(openModal);
                    if (modal) modal.hide();
                }

                // Close open dropdowns
                const openDropdown = document.querySelector('.dropdown-menu.show');
                if (openDropdown) {
                    const dropdown = bootstrap.Dropdown.getInstance(openDropdown.previousElementSibling);
                    if (dropdown) dropdown.hide();
                }
            }
        });
    }

    setupFocusManagement() {
        // Ensure modals trap focus properly
        document.addEventListener('shown.bs.modal', (e) => {
            const modal = e.target;
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstFocusable = focusableElements[0];
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
        });
    }

    setupAriaLiveRegion() {
        // Create aria-live region for dynamic content announcements
        if (!document.getElementById('aria-live-region')) {
            const liveRegion = document.createElement('div');
            liveRegion.id = 'aria-live-region';
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.className = 'visually-hidden';
            document.body.appendChild(liveRegion);
        }
    }

    announce(message) {
        const liveRegion = document.getElementById('aria-live-region');
        if (liveRegion) {
            liveRegion.textContent = message;
            setTimeout(() => {
                liveRegion.textContent = '';
            }, 1000);
        }
    }
}

// Initialize accessibility manager
const accessibilityManager = new AccessibilityManager();

// Modern form validation
class FormValidator {
    constructor(form) {
        this.form = form;
        this.setupValidation();
    }

    setupValidation() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.showErrors();
            }
        });

        // Real-time validation
        this.form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = `${field.previousElementSibling?.textContent || field.name} is required`;
        }

        // Email validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }

        // Pattern validation
        if (field.pattern && value && !new RegExp(field.pattern).test(value)) {
            isValid = false;
            message = `Please enter a valid ${field.name}`;
        }

        if (!isValid) {
            this.showFieldError(field, message);
        }

        return isValid;
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        field.classList.add('is-invalid');

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        feedback.setAttribute('role', 'alert');

        field.parentNode.appendChild(feedback);
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    showErrors() {
        // Announce errors to screen readers
        const firstError = this.form.querySelector('.is-invalid');
        if (firstError) {
            firstError.focus();
            accessibilityManager.announce('Please correct the errors in the form');
        }
    }
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new FormValidator(form);
    });

    // Show any existing notifications
    const notificationBox = document.getElementById('notification_box');
    if (notificationBox && notificationBox.textContent.trim()) {
        NotificationManager.show(notificationBox.textContent.trim(), 'success');
        notificationBox.remove();
    }

    // Setup logo form handling
    const logoForm = document.getElementById('logo-form');
    if (logoForm) {
        logoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(logoForm);
            const submitBtn = logoForm.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('logo-message');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Uploading...';

            try {
                const response = await fetch(logoForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = 'Logo updated successfully!';
                    if (result.logo_url) {
                        document.getElementById('logo-image').src = result.logo_url;
                    }
                } else {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = result.message || 'Error uploading logo';
                }

                messageDiv.classList.remove('d-none');

            } catch (error) {
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'Error uploading logo';
                messageDiv.classList.remove('d-none');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-upload me-2"></i>Save Logo';
            }
        });
    }
});

// Legacy jQuery functions (for backward compatibility)
// Fadeout notifications
$(document).ready(function () {
    if ($("#notification_box").length) {
        $("#notification_box").fadeOut(4000);
    }
});

// DataTable initialization
$(document).ready(function () {
    // Basic DataTables
    if ($('#example1').length) {
        $('#example1').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }

    if ($('#example2').length) {
        $('#example2').DataTable({
            responsive: true,
            paging: true,
            lengthChange: false,
            searching: false,
            ordering: true,
            info: true,
            autoWidth: false
        });
    }

    // Initialize Select2
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Initialize date pickers
    $('.date-picker').each(function() {
        flatpickr(this, {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });

    // Initialize date range picker
    $('#reservation').daterangepicker({
        opens: 'left'
    });

    // Initialize input masks
    $('#datemask').inputmask('yyyy-mm-dd', {'placeholder': 'yyyy-mm-dd'});
    $('[data-mask]').inputmask();
});

// Search functionality
$(document).ready(function(){
    if ($("#myInput").length && $("#myTable").length) {
        $("#myInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#myTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    }
});

// Advanced DataTable with export buttons
$(function () {
    if ($('#printable_area').length && $('#printable_area').find('table').length) {
        $('#printable_area').find('table').DataTable({
            dom: 'Bfrtip',
            responsive: true,
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="bi bi-copy"></i> Copy',
                    footer: true,
                    className: 'btn btn-secondary btn-sm'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    footer: true,
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="bi bi-filetype-csv"></i> CSV',
                    footer: true,
                    className: 'btn btn-info btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    footer: true,
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Print',
                    footer: true,
                    className: 'btn btn-primary btn-sm'
                }
            ],
            pageLength: 25,
            language: {
                search: "Search records:"
            }
        });
    }
});

// Active menu highlighting
$(document).ready(function() {
    const currentUrl = window.location.pathname;
    $('#mainMenu ul li a').each(function () {
        if (this.getAttribute('href') === currentUrl) {
            $(this).addClass('active');
            $(this).closest('li').addClass('active');
        }
    });
});

// Print function with modern implementation
function printDiv(printable_area) {
    const printContents = document.getElementById(printable_area).innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;

    // Reinitialize JavaScript after print
    location.reload();
}

// Page-specific scripts
@yield('script.bottom')