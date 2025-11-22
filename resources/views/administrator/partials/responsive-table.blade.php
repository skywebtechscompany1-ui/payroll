@php
    $tableId = $tableId ?? 'responsiveTable_' . uniqid();
    $data = $data ?? [];
    $columns = $columns ?? [];
    $title = $title ?? __('Data Table');
    $showSearch = $showSearch ?? true;
    $showExport = $showExport ?? true;
    $pageLength = $pageLength ?? 25;
    $responsive = $responsive ?? true;
    $class = $class ?? '';
@endphp

<!-- Responsive Table Component -->
<div class="card border-0 shadow-sm {{ $class }}" id="{{ $tableId }}_container">
    <!-- Card Header -->
    <div class="card-header bg-white border-0 pt-4 pb-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    @if(isset($icon))
                        <i class="{{ $icon }} me-2"></i>
                    @endif
                    {{ $title }}
                </h5>
                @if(isset($description))
                    <small class="text-muted">{{ $description }}</small>
                @endif
            </div>
            @if($showExport)
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="{{ $tableId }}_exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i>{{ __('Export') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="{{ $tableId }}_exportDropdown">
                        <li><a class="dropdown-item export-btn" data-format="copy" href="#">
                            <i class="bi bi-clipboard me-2"></i>{{ __('Copy') }}
                        </a></li>
                        <li><a class="dropdown-item export-btn" data-format="csv" href="#">
                            <i class="bi bi-filetype-csv me-2"></i>{{ __('CSV') }}
                        </a></li>
                        <li><a class="dropdown-item export-btn" data-format="excel" href="#">
                            <i class="bi bi-file-earmark-excel me-2"></i>{{ __('Excel') }}
                        </a></li>
                        <li><a class="dropdown-item export-btn" data-format="pdf" href="#">
                            <i class="bi bi-file-earmark-pdf me-2"></i>{{ __('PDF') }}
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item export-btn" data-format="print" href="#">
                            <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                        </a></li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-0">
        @if($showSearch)
        <!-- Search and Filter Controls -->
        <div class="p-3 border-bottom bg-light">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="{{ $tableId }}_search"
                               placeholder="{{ __('Search...') }}" aria-label="{{ __('Search table') }}">
                    </div>
                </div>
                @if(isset($filters) && count($filters) > 0)
                <div class="col-md-8">
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($filters as $filter)
                            @switch($filter['type'])
                                @case('select')
                                    <select class="form-select form-select-sm" name="{{ $filter['name'] }}"
                                            data-filter="{{ $filter['name'] }}" aria-label="{{ $filter['label'] ?? $filter['name'] }}">
                                        <option value="">{{ $filter['label'] ?? __('All') }}</option>
                                        @foreach($filter['options'] ?? [] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('date')
                                    <input type="date" class="form-control form-control-sm" name="{{ $filter['name'] }}"
                                           data-filter="{{ $filter['name'] }}" aria-label="{{ $filter['label'] ?? $filter['name'] }}">
                                    @break
                                @default
                                    <input type="text" class="form-control form-control-sm" name="{{ $filter['name'] }}"
                                           data-filter="{{ $filter['name'] }}" placeholder="{{ $filter['label'] ?? $filter['name'] }}"
                                           aria-label="{{ $filter['label'] ?? $filter['name'] }}">
                            @endswitch
                        @endforeach
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $tableId }}_clearFilters">
                            <i class="bi bi-x-circle me-1"></i>{{ __('Clear') }}
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Table Container -->
        <div class="table-responsive @if($responsive) table-responsive-mobile @endif">
            <table class="table table-hover mb-0" id="{{ $tableId }}" aria-label="{{ $title }}">
                <thead class="table-light">
                    <tr>
                        @if(isset($showRowNumbers) && $showRowNumbers)
                        <th scope="col" class="text-center" style="width: 50px;">
                            {{ __('SL') }}
                        </th>
                        @endif
                        @foreach($columns as $column)
                        <th scope="col" @if(isset($column['width'])) style="width: {{ $column['width'] }};" @endif>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $column['title'] }}</span>
                                @if($column['sortable'] ?? true)
                                    <button class="btn btn-link btn-sm p-0 ms-1 sort-btn" data-column="{{ $column['key'] }}"
                                            aria-label="{{ __('Sort by') }} {{ $column['title'] }}">
                                        <i class="bi bi-arrow-down-up"></i>
                                    </button>
                                @endif
                            </div>
                        </th>
                        @endforeach
                        @if(isset($actions) && count($actions) > 0)
                        <th scope="col" class="text-center" style="width: 120px;">
                            {{ __('Actions') }}
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if(count($data) > 0)
                        @php($rowNumber = 1)
                        @foreach($data as $row)
                        <tr data-id="{{ $row['id'] ?? '' }}">
                            @if(isset($showRowNumbers) && $showRowNumbers)
                            <td class="text-center">{{ $rowNumber++ }}</td>
                            @endif
                            @foreach($columns as $column)
                            <td @if(isset($column['class'])) class="{{ $column['class'] }}" @endif>
                                @if(isset($column['render']))
                                    {!! $column['render']($row) !!}
                                @else
                                    {{ $row[$column['key']] ?? '' }}
                                @endif
                            </td>
                            @endforeach
                            @if(isset($actions) && count($actions) > 0)
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @foreach($actions as $action)
                                        @switch($action['type'])
                                            @case('view')
                                                <a href="{{ $action['url'] ?? '#' }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                                                   aria-label="{{ __('View') }} {{ $action['label'] ?? '' }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @break
                                            @case('edit')
                                                <a href="{{ $action['url'] ?? '#' }}"
                                                   class="btn btn-sm btn-outline-warning"
                                                   @if(isset($action['target'])) target="{{ $action['target'] }}" @endif
                                                   aria-label="{{ __('Edit') }} {{ $action['label'] ?? '' }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @break
                                            @case('delete')
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger delete-btn"
                                                        data-url="{{ $action['url'] ?? '#' }}"
                                                        data-message="{{ $action['message'] ?? __('Are you sure?') }}"
                                                        aria-label="{{ __('Delete') }} {{ $action['label'] ?? '' }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @break
                                            @case('custom')
                                                <button type="button"
                                                        class="btn btn-sm {{ $action['class'] ?? 'btn-outline-secondary' }} custom-action"
                                                        data-action="{{ $action['action'] ?? '' }}"
                                                        data-url="{{ $action['url'] ?? '' }}"
                                                        aria-label="{{ $action['label'] ?? '' }}">
                                                    @if(isset($action['icon']))
                                                        <i class="bi bi-{{ $action['icon'] }}"></i>
                                                    @endif
                                                    {{ $action['text'] ?? '' }}
                                                </button>
                                                @break
                                        @endswitch
                                    @endforeach
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{ count($columns) + (isset($showRowNumbers) && $showRowNumbers ? 1 : 0) + (isset($actions) && count($actions) > 0 ? 1 : 0) }}"
                            class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                <h5>{{ __('No data available') }}</h5>
                                <p class="mb-0">{{ __('There are no records to display') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Card Footer -->
    @if(isset($footer) || count($data) > $pageLength)
    <div class="card-footer bg-transparent border-0">
        <div class="row align-items-center">
            @if(isset($footer))
            <div class="col">
                {{ $footer }}
            </div>
            @endif
            @if(count($data) > $pageLength)
            <div class="col-auto">
                <nav aria-label="{{ __('Table pagination') }}">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">{{ __('Previous') }}</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">{{ __('Next') }}</a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Mobile Card View (shown on small screens) -->
<div class="d-block d-md-none" id="{{ $tableId }}_mobileView">
    @if(count($data) > 0)
        @foreach($data as $index => $row)
        <div class="card mb-3 border shadow-sm">
            <div class="card-body">
                @if(isset($showRowNumbers) && $showRowNumbers)
                <div class="badge bg-primary mb-2">{{ __('Record') }} #{{ $index + 1 }}</div>
                @endif

                @foreach($columns as $column)
                <div class="row mb-2">
                    <div class="col-6">
                        <small class="text-muted fw-medium">{{ $column['title'] }}</small>
                    </div>
                    <div class="col-6">
                        @if(isset($column['render']))
                            {!! $column['render']($row) !!}
                        @else
                            <span class="fw-medium">{{ $row[$column['key']] ?? '—' }}</span>
                        @endif
                    </div>
                </div>
                @endforeach

                @if(isset($actions) && count($actions) > 0)
                <div class="border-top pt-2 mt-3">
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($actions as $action)
                            @switch($action['type'])
                                @case('view')
                                    <a href="{{ $action['url'] ?? '#' }}"
                                       class="btn btn-sm btn-outline-primary flex-fill"
                                       @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                                    </a>
                                    @break
                                @case('edit')
                                    <a href="{{ $action['url'] ?? '#' }}"
                                       class="btn btn-sm btn-outline-warning flex-fill"
                                       @if(isset($action['target'])) target="{{ $action['target'] }}" @endif>
                                        <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                                    </a>
                                    @break
                                @case('delete')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-btn flex-fill"
                                            data-url="{{ $action['url'] ?? '#' }}"
                                            data-message="{{ $action['message'] ?? __('Are you sure?') }}">
                                        <i class="bi bi-trash me-1"></i>{{ __('Delete') }}
                                    </button>
                                    @break
                                @case('custom')
                                    <button type="button"
                                            class="btn btn-sm {{ $action['class'] ?? 'btn-outline-secondary' }} custom-action flex-fill"
                                            data-action="{{ $action['action'] ?? '' }}"
                                            data-url="{{ $action['url'] ?? '' }}">
                                        @if(isset($action['icon']))
                                            <i class="bi bi-{{ $action['icon'] }} me-1"></i>
                                        @endif
                                        {{ $action['text'] ?? $action['label'] ?? '' }}
                                    </button>
                                    @break
                            @endswitch
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    @else
    <div class="text-center py-5">
        <div class="text-muted">
            <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
            <h5>{{ __('No data available') }}</h5>
            <p class="mb-0">{{ __('There are no records to display') }}</p>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for this table component -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableId = '{{ $tableId }}';
    const table = document.getElementById(tableId);
    const mobileView = document.getElementById(tableId + '_mobileView');
    const searchInput = document.getElementById(tableId + '_search');

    if (!table) return;

    // Initialize DataTable if jQuery is available
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        const dataTable = $('#' + tableId).DataTable({
            responsive: true,
            pageLength: {{ $pageLength }},
            language: {
                search: "{{ __('Search:') }}",
                lengthMenu: "{{ __('Show _MENU_ entries') }}",
                info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
                zeroRecords: "{{ __('No matching records found') }}",
                emptyTable: "{{ __('No data available in table') }}",
                paginate: {
                    first: "{{ __('First') }}",
                    last: "{{ __('Last') }}",
                    next: "{{ __('Next') }}",
                    previous: "{{ __('Previous') }}"
                }
            },
            @if($showExport)
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    text: '<i class="bi bi-clipboard"></i> {{ __("Copy") }}',
                    className: 'btn btn-secondary btn-sm'
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="bi bi-filetype-csv"></i> {{ __("CSV") }}',
                    className: 'btn btn-info btn-sm'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> {{ __("Excel") }}',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> {{ __("PDF") }}',
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> {{ __("Print") }}',
                    className: 'btn btn-primary btn-sm'
                }
                ],
            @endif
            initComplete: function() {
                // Hide mobile view when DataTable is initialized
                if (mobileView) {
                    mobileView.style.display = 'none';
                }
            }
        });

        // Handle custom search
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                dataTable.search(this.value).draw();
            });
        }

        // Handle custom filters
        document.querySelectorAll(`[data-filter]`).forEach(filter => {
            filter.addEventListener('change', function() {
                const filterName = this.getAttribute('data-filter');
                const filterValue = this.value;

                // Apply filter to DataTable
                dataTable.column(filterName + ':name').search(filterValue).draw();
            });
        }

        // Clear filters
        const clearBtn = document.getElementById(tableId + '_clearFilters');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                document.querySelectorAll(`[data-filter]`).forEach(filter => {
                    filter.value = '';
                });
                if (searchInput) searchInput.value = '';
                dataTable.search('').columns().search().draw();
            });
        }

        // Handle sort buttons
        document.querySelectorAll(`#${tableId} .sort-btn`).forEach(btn => {
            btn.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                const columnIndex = Array.from(table.querySelectorAll('thead th')).findIndex(th =>
                    th.textContent.includes(column)
                );

                if (columnIndex !== -1) {
                    dataTable.order([columnIndex, 'asc']).draw();
                }
            });
        }

        // Handle delete buttons
        document.querySelectorAll(`#${tableId} .delete-btn`).forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const message = this.getAttribute('data-message');

                if (confirm(message)) {
                    // Show loading state
                    this.disabled = true;
                    this.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                    // Perform delete action
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove row from table
                            dataTable.row(this.closest('tr')).remove().draw();
                            // Show success message
                            showNotification(data.message || '{{ __("Deleted successfully") }}', 'success');
                        } else {
                            // Show error message
                            showNotification(data.message || '{{ __("Delete failed") }}', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        showNotification('{{ __("An error occurred") }}', 'error');
                    })
                    .finally(() => {
                        // Reset button
                        this.disabled = false;
                        this.innerHTML = '<i class="bi bi-trash"></i>';
                    });
                }
            });
        });
    }

    // Show mobile view on small screens if DataTable is not available
    if (typeof $ === 'undefined' && window.innerWidth < 768 && mobileView) {
        table.closest('.table-responsive').style.display = 'none';
        mobileView.style.display = 'block';
    }

    // Handle custom action buttons
    document.querySelectorAll(`#${tableId} .custom-action`).forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const url = this.getAttribute('data-url');

            // Emit custom event that can be handled by parent page
            const event = new CustomEvent('tableCustomAction', {
                detail: { action, url, button: this }
            });
            document.dispatchEvent(event);
        });
    });
});

// Helper function for notifications
function showNotification(message, type = 'success') {
    if (typeof NotificationManager !== 'undefined') {
        NotificationManager.show(message, type);
    } else {
        // Fallback simple alert
        alert(message);
    }
}
</script>

<!-- Mobile-specific styles -->
<style>
@media (max-width: 767.98px) {
    .table-responsive-mobile {
        display: none;
    }

    #{{ $tableId }}_mobileView {
        display: block !important;
    }

    .card .btn-group .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.5rem;
    }
}

/* Enhance touch targets for mobile */
@media (pointer: coarse) {
    .btn, .form-control, .form-select {
        min-height: 44px;
    }

    .table td, .table th {
        padding: 0.75rem;
    }
}

/* Improve accessibility */
.sort-btn:focus {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }

    .table-responsive-mobile {
        display: table !important;
    }

    #{{ $tableId }}_mobileView {
        display: none !important;
    }
}
</style>