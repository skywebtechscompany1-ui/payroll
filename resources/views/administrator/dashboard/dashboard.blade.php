@extends('administrator.master')
@section('title', __('Dashboard'))
@section('main_content')

<!-- Modern Dashboard Content -->
<div class="container-fluid p-4 fade-in">
    <!-- Success/Error Notifications -->
    @if(session()->has('success') || session()->has('status'))
    <div class="alert alert-success alert-dismissible fade show" id="notification_box" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ !empty(session()->get('success')) ? session()->get('success') : session()->get('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @php
        $user = Auth::user();
        $notics = \App\Notice::take(4)->get();
        $holidays = \App\Holiday::all();
        $personalevents = \App\PersonalEvent::all();
        $currentDate = now()->format('l, F j, Y');
    @endphp

    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="h2 mb-1">{{ __('Dashboard') }}</h1>
                    <p class="text-muted mb-0">{{ __('Welcome back') }}, {{ $user->name }}! {{ __('Today is') }} {{ $currentDate }}</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <button class="btn btn-outline-primary" onclick="window.print()" aria-label="Print dashboard">
                        <i class="bi bi-printer me-2"></i>{{ __('Print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($user->access_label == 1)
    <!-- Admin Dashboard -->
    <!-- Statistics Cards -->
    <section class="mb-5">
        <div class="row g-3 g-lg-4">
            <!-- Employees Card -->
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm stats-card" data-stat="employees">
                    <div class="card-body text-center p-3 p-lg-4">
                        <div class="stats-icon mb-3 text-success">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($employees) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('Employees') }}</p>
                        <a href="{{ url('/people/employees') }}" class="btn btn-link btn-sm text-success text-decoration-none">
                            {{ __('View Details') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Leave Applications Card -->
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm stats-card" data-stat="leaves">
                    <div class="card-body text-center p-3 p-lg-4">
                        <div class="stats-icon mb-3 text-warning">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($leaves) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('Leave Applications') }}</p>
                        <a href="{{ url('/hrm/application_lists') }}" class="btn btn-link btn-sm text-warning text-decoration-none">
                            {{ __('Review') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notices Card -->
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm stats-card" data-stat="notices">
                    <div class="card-body text-center p-3 p-lg-4">
                        <div class="stats-icon mb-3 text-info">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($notices) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('Notices') }}</p>
                        <a href="{{ url('/hrm/notice') }}" class="btn btn-link btn-sm text-info text-decoration-none">
                            {{ __('Manage') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Job Groups Card -->
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm stats-card" data-stat="departments">
                    <div class="card-body text-center p-3 p-lg-4">
                        <div class="stats-icon mb-3 text-danger">
                            <i class="bi bi-building"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($job_groups) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('Job Groups') }}</p>
                        <a href="{{ url('/setting/departments') }}" class="btn btn-link btn-sm text-danger text-decoration-none">
                            {{ __('Configure') }} <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Charts Section -->
    <section class="mb-5">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart-fill me-2 text-primary"></i>
                            {{ __('Overview Distribution') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="doughnutChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart-fill me-2 text-success"></i>
                            {{ __('Statistics Comparison') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="barChart" width="400" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-fill me-2 text-warning"></i>
                    {{ __('Quick Actions') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="{{ url('/people/employees/create') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-person-plus-fill fs-3 mb-2"></i>
                            <span>{{ __('Add Employee') }}</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ url('/hrm/notice/create') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-megaphone fs-3 mb-2"></i>
                            <span>{{ __('Post Notice') }}</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ url('/setting/leave_category') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-calendar-check fs-3 mb-2"></i>
                            <span>{{ __('Leave Settings') }}</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ url('/hrm/payroll') }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="bi bi-cash-stack fs-3 mb-2"></i>
                            <span>{{ __('Process Payroll') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tables Section -->
    <section class="mb-5">
        <div class="row g-4">
            <!-- Upcoming Holidays -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-event-fill me-2 text-danger"></i>
                            {{ __('Upcoming Holidays') }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('SL') }}</th>
                                        <th>{{ __('Holiday') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th class="d-none d-lg-table-cell">{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($sl = 1)
                                    @foreach($holidays->take(5) as $holiday)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $holiday->holiday_name }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger-emphasis">
                                                {{ \Carbon\Carbon::parse($holiday->date)->format('M j, Y') }}
                                            </span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <small class="text-muted">{{ Str::limit($holiday->description, 30) }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($holidays->count() > 5)
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ url('/setting/holidays') }}" class="btn btn-sm btn-outline-danger">
                                {{ __('View All Holidays') }} <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Notices -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-megaphone-fill me-2 text-info"></i>
                            {{ __('Recent Notices') }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('SL') }}</th>
                                        <th>{{ __('Title') }}</th>
                                        <th class="d-none d-lg-table-cell">{{ __('Preview') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($sl = 1)
                                    @foreach($notics as $notic)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>
                                            <div class="fw-medium">{{ $notic->notice_title }}</div>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <small class="text-muted">{{ Str::limit($notic->description, 40) }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($notics->count() >= 4)
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ url('/hrm/notice') }}" class="btn btn-sm btn-outline-info">
                                {{ __('View All Notices') }} <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @else
    <!-- Employee Dashboard -->
    <!-- Employee Stats Cards -->
    <section class="mb-5">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mb-3 text-warning">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($leaves) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('My Leave Applications') }}</p>
                        <a href="{{ url('/hrm/application_lists') }}" class="btn btn-warning">
                            {{ __('View Applications') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="stats-icon mb-3 text-info">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                        <h3 class="h2 mb-2">{{ count($notices) }}</h3>
                        <p class="card-text text-muted fw-medium">{{ __('Company Notices') }}</p>
                        <a href="{{ url('/hrm/notice') }}" class="btn btn-info">
                            {{ __('View Notices') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Employee Quick Actions -->
    <section class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning-fill me-2 text-primary"></i>
                    {{ __('Quick Actions') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ url('/hrm/leave_application') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-calendar-plus fs-3 mb-2"></i>
                            <span>{{ __('Apply for Leave') }}</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ url('/hrm/notice') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-megaphone fs-3 mb-2"></i>
                            <span>{{ __('View Notices') }}</span>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ url('/hrm/attendance') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="bi bi-clock-history fs-3 mb-2"></i>
                            <span>{{ __('Attendance History') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Personal Events (shown for both roles) -->
    @if(count($personal_events) > 0)
    <section class="mb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event-fill me-2 text-primary"></i>
                    {{ __('Personal Events') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="eventsTable">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('SL#') }}</th>
                                <th>{{ __('Event Name') }}</th>
                                <th>{{ __('Start Date') }}</th>
                                <th>{{ __('End Date') }}</th>
                                <th>{{ __('Created By') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($sl = 1)
                            @foreach($personal_events as $personal_event)
                            <tr>
                                <td>{{ $sl++ }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2">
                                        {{ $personal_event->personal_event }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success-emphasis">
                                        {{ \Carbon\Carbon::parse($personal_event->start_date)->format('M j, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">
                                        {{ \Carbon\Carbon::parse($personal_event->end_date)->format('M j, Y') }}
                                    </span>
                                </td>
                                <td>{{ $personal_event->name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    @endif
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($user->access_label == 1)
    // Chart data from PHP
    const chartData = {
        labels: ['{{ __("Employees") }}', '{{ __("Notices") }}', '{{ __("Holidays") }}'],
        data: [{{ count($employees) }}, {{ count($notics) }}, {{ count($holidays) }}]
    };

    // Get theme colors
    const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDarkMode ? '#dee2e6' : '#212529';
    const gridColor = isDarkMode ? '#495057' : '#e9ecef';

    // Doughnut Chart
    const doughnutCtx = document.getElementById('doughnutChart');
    if (doughnutCtx) {
        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(25, 135, 84, 1)',
                        'rgba(13, 202, 240, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }

    // Bar Chart
    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: '{{ __("Total Count") }}',
                    data: chartData.data,
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(13, 202, 240, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(25, 135, 84, 1)',
                        'rgba(13, 202, 240, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 60
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Initialize DataTable for events table
    if (document.getElementById('eventsTable')) {
        $('#eventsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[1, 'asc']], // Order by start date
            language: {
                search: "Search events:",
                lengthMenu: "Show _MENU_ events",
                info: "Showing _START_ to _END_ of _TOTAL_ events",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
    @endif

    // Add hover effect to stats cards
    document.querySelectorAll('.stats-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'all 0.3s ease';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<!-- Custom Styles for Dashboard -->
<style>
.stats-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.stats-icon {
    font-size: 2.5rem;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.stats-card:hover .stats-icon {
    transform: scale(1.1);
    opacity: 1;
}

@media (max-width: 768px) {
    .stats-icon {
        font-size: 2rem;
    }

    .card-body {
        padding: 1.5rem !important;
    }
}

/* Chart container height fix */
canvas {
    max-height: 300px;
}

/* Badge improvements */
.badge {
    font-weight: 500;
    font-size: 0.875rem;
}

/* Print-friendly styles */
@media print {
    .no-print {
        display: none !important;
    }

    .stats-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>
@endsection