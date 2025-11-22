@php
    $formId = $formId ?? 'modernForm_' . uniqid();
    $method = $method ?? 'POST';
    $action = $action ?? '';
    $enctype = $enctype ?? false;
    $fields = $fields ?? [];
    $submitText = $submitText ?? __('Submit');
    $submitIcon = $submitIcon ?? 'send';
    $resetText = $resetText ?? null;
    $resetIcon = $resetIcon ?? 'arrow-clockwise';
    $validation = $validation ?? true;
    $loadingText = $loadingText ?? __('Loading...');
    $successMessage = $successMessage ?? __('Form submitted successfully!');
    $errorMessage = $errorMessage ?? __('Please correct the errors below.');
@endphp

<!-- Modern Bootstrap 5 Form Component -->
<form id="{{ $formId }}"
      class="modern-form needs-validation {{ $validation ? 'was-validated' : '' }}"
      method="{{ $method }}"
      action="{{ $action }}"
      @if($enctype) enctype="multipart/form-data" @endif
      novalidate
      data-validate="{{ $validation ? 'true' : 'false' }}"
      aria-label="{{ $title ?? __('Form') }}">

    @csrf
    @if($method == 'PUT' || $method == 'PATCH')
        @method($method)
    @endif

    <!-- Form Header -->
    @if(isset($title) || isset($description))
    <div class="form-header mb-4">
        @if(isset($title))
        <h4 class="form-title">{{ $title }}</h4>
        @endif
        @if(isset($description))
        <p class="form-description text-muted">{{ $description }}</p>
        @endif
    </div>
    @endif

    <!-- Form Fields -->
    <div class="form-fields">
        @foreach($fields as $field)
            @switch($field['type'])
                @case('text')
                @case('email')
                @case('password')
                @case('number')
                @case('tel')
                @case('url')
                @case('date')
                @case('datetime-local')
                @case('time')
                @case('month')
                @case('week')
                    <div class="form-floating mb-3">
                        <input type="{{ $field['type'] }}"
                               class="form-control"
                               id="{{ $field['id'] ?? $field['name'] }}"
                               name="{{ $field['name'] }}"
                               value="{{ $field['value'] ?? old($field['name']) }}"
                               placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                               @if($field['required'] ?? false) required @endif
                               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                               @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                               @if(isset($field['pattern'])) pattern="{{ $field['pattern'] }}" @endif
                               @if(isset($field['readonly']) && $field['readonly']) readonly @endif
                               @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                               aria-label="{{ $field['label'] }}"
                               aria-describedby="{{ $field['id'] ?? $field['name'] }}_feedback {{ $field['id'] ?? $field['name'] }}_help">
                        <label for="{{ $field['id'] ?? $field['name'] }}">{{ $field['label'] }}</label>
                        <div class="invalid-feedback" id="{{ $field['id'] ?? $field['name'] }}_feedback">
                            {{ $field['error_message'] ?? __('Please provide a valid') . ' ' . strtolower($field['label']) }}
                        </div>
                        @if(isset($field['help']))
                        <div class="form-text" id="{{ $field['id'] ?? $field['name'] }}_help">
                            {{ $field['help'] }}
                        </div>
                        @endif
                    </div>
                    @break

                @case('textarea')
                    <div class="form-floating mb-3">
                        <textarea class="form-control"
                                  id="{{ $field['id'] ?? $field['name'] }}"
                                  name="{{ $field['name'] }}"
                                  placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                                  @if(isset($field['rows'])) rows="{{ $field['rows'] }}" @endif
                                  @if($field['required'] ?? false) required @endif
                                  @if(isset($field['readonly']) && $field['readonly']) readonly @endif
                                  @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                                  aria-label="{{ $field['label'] }}"
                                  aria-describedby="{{ $field['id'] ?? $field['name'] }}_feedback {{ $field['id'] ?? $field['name'] }}_help">{{ $field['value'] ?? old($field['name']) }}</textarea>
                        <label for="{{ $field['id'] ?? $field['name'] }}">{{ $field['label'] }}</label>
                        <div class="invalid-feedback" id="{{ $field['id'] ?? $field['name'] }}_feedback">
                            {{ $field['error_message'] ?? __('Please provide a valid') . ' ' . strtolower($field['label']) }}
                        </div>
                        @if(isset($field['help']))
                        <div class="form-text" id="{{ $field['id'] ?? $field['name'] }}_help">
                            {{ $field['help'] }}
                        </div>
                        @endif
                    </div>
                    @break

                @case('select')
                    <div class="form-floating mb-3">
                        <select class="form-select"
                                id="{{ $field['id'] ?? $field['name'] }}"
                                name="{{ $field['name'] }}"
                                @if($field['required'] ?? false) required @endif
                                @if(isset($field['multiple']) && $field['multiple']) multiple @endif
                                @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                                aria-label="{{ $field['label'] }}"
                                aria-describedby="{{ $field['id'] ?? $field['name'] }}_feedback {{ $field['id'] ?? $field['name'] }}_help">
                            @if(!($field['required'] ?? false))
                            <option value="">{{ $field['placeholder'] ?? __('Choose...') }}</option>
                            @endif
                            @foreach($field['options'] ?? [] as $value => $label)
                            <option value="{{ $value }}"
                                    @if(old($field['name']) == $value || (isset($field['value']) && $field['value'] == $value)) selected @endif>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        <label for="{{ $field['id'] ?? $field['name'] }}">{{ $field['label'] }}</label>
                        <div class="invalid-feedback" id="{{ $field['id'] ?? $field['name'] }}_feedback">
                            {{ $field['error_message'] ?? __('Please select a') . ' ' . strtolower($field['label']) }}
                        </div>
                        @if(isset($field['help']))
                        <div class="form-text" id="{{ $field['id'] ?? $field['name'] }}_help">
                            {{ $field['help'] }}
                        </div>
                        @endif
                    </div>
                    @break

                @case('checkbox')
                @case('radio')
                    <div class="form-check mb-3">
                        <input class="form-check-input"
                               type="{{ $field['type'] }}"
                               id="{{ $field['id'] ?? $field['name'] }}"
                               name="{{ $field['name'] }}"
                               value="{{ $field['value'] ?? '1' }}"
                               @if($field['checked'] ?? false) checked @endif
                               @if($field['required'] ?? false) required @endif
                               @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                               aria-label="{{ $field['label'] }}">
                        <label class="form-check-label" for="{{ $field['id'] ?? $field['name'] }}">
                            {{ $field['label'] }}
                        </label>
                        @if(isset($field['help']))
                        <div class="form-text">{{ $field['help'] }}</div>
                        @endif
                    </div>
                    @break

                @case('file')
                    <div class="mb-3">
                        <label for="{{ $field['id'] ?? $field['name'] }}" class="form-label fw-medium">
                            {{ $field['label'] }}
                            @if($field['required'] ?? false)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <input type="file"
                               class="form-control"
                               id="{{ $field['id'] ?? $field['name'] }}"
                               name="{{ $field['name'] }}"
                               @if(isset($field['accept'])) accept="{{ $field['accept'] }}" @endif
                               @if(isset($field['multiple']) && $field['multiple']) multiple @endif
                               @if($field['required'] ?? false) required @endif
                               @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                               aria-describedby="{{ $field['id'] ?? $field['name'] }}_help">
                        <div class="form-text" id="{{ $field['id'] ?? $field['name'] }}_help">
                            @if(isset($field['help']))
                                {{ $field['help'] }}
                            @else
                                {{ __('Allowed formats') }}: {{ $field['accept'] ?? __('Any') }}
                            @endif
                        </div>
                        <div class="invalid-feedback">
                            {{ $field['error_message'] ?? __('Please select a file') }}
                        </div>
                    </div>
                    @break

                @case('range')
                    <div class="mb-3">
                        <label for="{{ $field['id'] ?? $field['name'] }}" class="form-label fw-medium">
                            {{ $field['label'] }}
                            <span class="range-value badge bg-primary ms-2" id="{{ $field['id'] ?? $field['name'] }}_value">
                                {{ $field['value'] ?? old($field['name']) ?? $field['default'] ?? 50 }}
                            </span>
                        </label>
                        <input type="range"
                               class="form-range"
                               id="{{ $field['id'] ?? $field['name'] }}"
                               name="{{ $field['name'] }}"
                               value="{{ $field['value'] ?? old($field['name']) ?? $field['default'] ?? 50 }}"
                               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                               @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                               @if($field['required'] ?? false) required @endif
                               @if(isset($field['disabled']) && $field['disabled']) disabled @endif
                               aria-label="{{ $field['label'] }}">
                        @if(isset($field['help']))
                        <div class="form-text">{{ $field['help'] }}</div>
                        @endif
                    </div>
                    @break

                @case('hidden')
                    <input type="hidden"
                           id="{{ $field['id'] ?? $field['name'] }}"
                           name="{{ $field['name'] }}"
                           value="{{ $field['value'] ?? '' }}">
                    @break

                @case('section')
                    <div class="form-section mb-4">
                        <h5 class="section-title mb-3">
                            @if(isset($field['icon']))
                                <i class="bi bi-{{ $field['icon'] }} me-2"></i>
                            @endif
                            {{ $field['title'] }}
                        </h5>
                        @if(isset($field['description']))
                        <p class="section-description text-muted">{{ $field['description'] }}</p>
                        @endif
                    </div>
                    @break

                @case('custom')
                    @if(isset($field['content']))
                        {!! $field['content'] !!}
                    @endif
                    @break
            @endswitch
        @endforeach
    </div>

    <!-- Form Actions -->
    <div class="form-actions d-flex gap-2 justify-content-end">
        @if($resetText)
        <button type="reset" class="btn btn-outline-secondary">
            <i class="bi bi-{{ $resetIcon }} me-2"></i>{{ $resetText }}
        </button>
        @endif
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-{{ $submitIcon }} me-2"></i>
            <span class="btn-text">{{ $submitText }}</span>
            <span class="btn-loading d-none">
                <i class="bi bi-hourglass-split me-2"></i>{{ $loadingText }}
            </span>
        </button>
    </div>
</form>

<!-- Form-specific Styles -->
<style>
.modern-form {
    max-width: 100%;
}

.form-header {
    border-bottom: 1px solid var(--bs-border-color);
    padding-bottom: 1rem;
}

.form-title {
    color: var(--bs-body-color);
    margin-bottom: 0.5rem;
}

.form-description {
    font-size: 0.875rem;
    margin-bottom: 0;
}

.form-section {
    background: var(--bs-gray-50);
    border: 1px solid var(--bs-border-color);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin: 2rem 0;
}

.section-title {
    color: var(--bs-primary);
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.section-description {
    font-size: 0.875rem;
    margin-bottom: 0;
}

/* Enhanced floating label styles */
.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label,
.form-floating > .form-select ~ label {
    color: var(--bs-primary);
}

.form-floating > .form-control:focus ~ label::after,
.form-floating > .form-control:not(:placeholder-shown) ~ label::after,
.form-floating > .form-select ~ label::after {
    background-color: var(--bs-body-bg);
}

/* Checkbox and radio improvements */
.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.form-check-input:focus {
    border-color: var(--bs-primary);
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

/* Range slider improvements */
.form-range::-webkit-slider-thumb {
    background: var(--bs-primary);
}

.form-range::-moz-range-thumb {
    background: var(--bs-primary);
}

.range-value {
    min-width: 3rem;
    text-align: center;
}

/* Loading state */
.form-actions button[type="submit"].loading .btn-text {
    display: none;
}

.form-actions button[type="submit"].loading .btn-loading {
    display: inline;
}

/* File input improvements */
.form-control[type="file"] {
    padding: 0.375rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }

    .form-actions button {
        width: 100%;
    }

    .form-section {
        margin: 1rem 0;
        padding: 1rem;
    }
}

/* Dark mode support */
[data-bs-theme="dark"] .form-section {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-600);
}

/* Accessibility improvements */
.form-control:focus,
.form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

.form-control:focus-visible,
.form-select:focus-visible {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}

/* Animation for form elements */
.form-floating {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .form-floating {
        animation: none;
    }
}
</style>

<!-- Form JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('{{ $formId }}');
    if (!form) return;

    const shouldValidate = form.getAttribute('data-validate') === 'true';
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');

    // Initialize form validator if validation is enabled
    if (shouldValidate && typeof FormValidator !== 'undefined') {
        new FormValidator(form);
    }

    // Handle range slider value display
    const rangeInputs = form.querySelectorAll('input[type="range"]');
    rangeInputs.forEach(input => {
        const valueDisplay = document.getElementById(input.id + '_value');
        if (valueDisplay) {
            input.addEventListener('input', function() {
                valueDisplay.textContent = this.value;
            });
        }
    });

    // Handle form submission
    form.addEventListener('submit', async function(e) {
        if (shouldValidate && !form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();

            // Show validation errors
            const firstInvalid = form.querySelector('.form-control:invalid, .form-select:invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.classList.add('is-invalid');
            }

            // Show error message
            if (typeof NotificationManager !== 'undefined') {
                NotificationManager.show('{{ $errorMessage }}', 'danger');
            }

            return;
        }

        // Prevent default submission for AJAX handling
        e.preventDefault();

        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                if (typeof NotificationManager !== 'undefined') {
                    NotificationManager.show(result.message || '{{ $successMessage }}', 'success');
                }

                // Handle redirect if specified
                if (result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }

                // Reset form if it's a creation form
                if (form.method.toUpperCase() === 'POST' && !result.no_reset) {
                    form.reset();
                    form.classList.remove('was-validated');
                }

                // Trigger custom event for parent page handling
                const event = new CustomEvent('formSubmitSuccess', {
                    detail: { form, result, formData }
                });
                document.dispatchEvent(event);

            } else {
                // Show error message
                if (typeof NotificationManager !== 'undefined') {
                    NotificationManager.show(result.message || '{{ $errorMessage }}', 'danger');
                }

                // Trigger custom event for error handling
                const event = new CustomEvent('formSubmitError', {
                    detail: { form, result, formData }
                });
                document.dispatchEvent(event);
            }

        } catch (error) {
            console.error('Form submission error:', error);

            // Show generic error message
            if (typeof NotificationManager !== 'undefined') {
                NotificationManager.show('{{ __("An error occurred. Please try again.") }}', 'danger');
            }

            // Trigger error event
            const event = new CustomEvent('formSubmitError', {
                detail: { form, error, formData: new FormData(form) }
            });
            document.dispatchEvent(event);

        } finally {
            // Hide loading state
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });

    // Handle file input preview (for images)
    const fileInputs = form.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Trigger preview event
                    const event = new CustomEvent('imageSelected', {
                        detail: { input, file, preview: e.target.result }
                    });
                    document.dispatchEvent(event);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Enhanced keyboard navigation
    form.addEventListener('keydown', function(e) {
        // Handle Ctrl+Enter or Cmd+Enter to submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            form.requestSubmit();
        }

        // Handle Escape to cancel
        if (e.key === 'Escape') {
            const resetBtn = form.querySelector('button[type="reset"]');
            if (resetBtn && !resetBtn.disabled) {
                resetBtn.click();
            }
        }
    });

    // Auto-save functionality (if enabled)
    const autoSaveInterval = form.getAttribute('data-autosave');
    if (autoSaveInterval) {
        let autoSaveTimer;
        const startAutoSave = () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(async () => {
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action.replace('/submit', '/autosave'), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    if (response.ok) {
                        console.log('Form auto-saved successfully');
                    }
                } catch (error) {
                    console.log('Auto-save failed:', error);
                }
            }, parseInt(autoSaveInterval) * 1000);
        };

        // Start auto-save on form changes
        form.addEventListener('input', startAutoSave);
        form.addEventListener('change', startAutoSave);
    }
});
</script>