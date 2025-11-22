<!-- Modern Bootstrap 5 Sidebar Navigation -->
<nav class="sidebar-nav" role="navigation" aria-label="{{ __('Main navigation') }}">
    <ul class="nav nav-pills flex-column" id="mainMenu">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
               aria-current="{{ request()->is('dashboard') ? 'page' : 'false' }}">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span>{{ __('Dashboard') }}</span>
            </a>
        </li>

        <!-- Employee Management -->
        @permission('people')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#employeeMenu"
                 aria-expanded="false" aria-controls="employeeMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-people-fill nav-icon"></i>
                    <span>{{ __('Employee Management') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="employeeMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-employee')
                    <li class="nav-item">
                        <a href="{{ url('/people/employees/create') }}"
                           class="nav-link nav-sub-link {{ request()->is('people/employees/create') ? 'active' : '' }}">
                            <i class="bi bi-person-plus nav-sub-icon"></i>
                            {{ __('New Employee') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/people/employees') }}"
                           class="nav-link nav-sub-link {{ request()->is('people/employees') ? 'active' : '' }}">
                            <i class="bi bi-people nav-sub-icon"></i>
                            {{ __('Manage Employee') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- Payroll Management -->
        @permission('payroll-management')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#payrollMenu"
                 aria-expanded="false" aria-controls="payrollMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-cash-stack nav-icon"></i>
                    <span>{{ __('Payroll Management') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="payrollMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-salary')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/payroll') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash nav-sub-icon"></i>
                            {{ __('Manage Salary') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('salary-list')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/payroll/salary-list') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/payroll/salary-list') ? 'active' : '' }}">
                            <i class="bi bi-list-ul nav-sub-icon"></i>
                            {{ __('Salary List') }}
                        </a>
                    </li>
                    @endpermission

                    <li class="nav-item">
                        <a href="{{ url('/hrm/payroll/increment/search') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/payroll/increment/search') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow nav-sub-icon"></i>
                            {{ __('New Increment') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/payroll/increment/list') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/payroll/increment/list') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart nav-sub-icon"></i>
                            {{ __('Increment List') }}
                        </a>
                    </li>

                    @permission('make-payment')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/salary-payments') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/salary-payments') ? 'active' : '' }}">
                            <i class="bi bi-credit-card nav-sub-icon"></i>
                            {{ __('Make Payment') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('generate-payslip')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/generate-payslips/') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/generate-payslips/*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text nav-sub-icon"></i>
                            {{ __('Generate Payslip') }}
                        </a>
                    </li>
                    @endpermission

                    @permission('manage-bonus')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/bonuses') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/bonuses') ? 'active' : '' }}">
                            <i class="bi bi-gift nav-sub-icon"></i>
                            {{ __('Manage Bonus') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('manage-deduction')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/allowance') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/allowance') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle nav-sub-icon"></i>
                            {{ __('Manage Allowance') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/deduction') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/deduction') ? 'active' : '' }}">
                            <i class="bi bi-dash-circle nav-sub-icon"></i>
                            {{ __('Manage Deduction') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('loan-management')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/loans') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/loans') ? 'active' : '' }}">
                            <i class="bi bi-bank nav-sub-icon"></i>
                            {{ __('Loan Management') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('provident-fund')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/provident-funds') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/provident-funds') ? 'active' : '' }}">
                            <i class="bi bi-piggy-bank nav-sub-icon"></i>
                            {{ __('Provident Fund') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- Leave Management -->
        @permission('leave-application')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#leaveMenu"
                 aria-expanded="false" aria-controls="leaveMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check nav-icon"></i>
                    <span>{{ __('Leave Management') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="leaveMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-leave-application')
                    <li class="nav-item">
                        <a href="{{ url('/setting/leave_categories/create') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/leave_categories/create') ? 'active' : '' }}">
                            <i class="bi bi-tag nav-sub-icon"></i>
                            {{ __('New Leave Category') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/setting/leave_categories') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/leave_categories') ? 'active' : '' }}">
                            <i class="bi bi-tags nav-sub-icon"></i>
                            {{ __('Leave Category List') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/application_lists') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/application_lists') ? 'active' : '' }}">
                            <i class="bi bi-list-check nav-sub-icon"></i>
                            {{ __('Leave Application List') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('my-leave-application')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/leave_application/create') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/leave_application/create') ? 'active' : '' }}">
                            <i class="bi bi-calendar-plus nav-sub-icon"></i>
                            {{ __('New Leave Application') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/leave_application') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/leave_application') ? 'active' : '' }}">
                            <i class="bi bi-calendar3 nav-sub-icon"></i>
                            {{ __('Leave Application Manage') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- NOC/Experience Certificate -->
        @permission('manage-award')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#certificateMenu"
                 aria-expanded="false" aria-controls="certificateMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-file-earmark-medical nav-icon"></i>
                    <span>{{ __('NOC/Ex. Certificate') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="certificateMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-award')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/noc/add') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/noc/add') ? 'active' : '' }}">
                            <i class="bi bi-file-plus nav-sub-icon"></i>
                            {{ __('NOC/Certificate Add') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/noc/list') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/noc/list') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text nav-sub-icon"></i>
                            {{ __('NOC List') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/certificate/list') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/certificate/list') ? 'active' : '' }}">
                            <i class="bi bi-award nav-sub-icon"></i>
                            {{ __('Experience Certificate') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- Notice Board -->
        @permission('notice')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#noticeMenu"
                 aria-expanded="false" aria-controls="noticeMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-megaphone nav-icon"></i>
                    <span>{{ __('Notice Board') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="noticeMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-notice')
                    <li class="nav-item">
                        <a href="{{ url('hrm/notice/create') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/notice/create') ? 'active' : '' }}">
                            <i class="bi bi-plus-square nav-sub-icon"></i>
                            {{ __('New Notice') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/notice') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/notice') ? 'active' : '' }}">
                            <i class="bi bi-bell nav-sub-icon"></i>
                            {{ __('Manage Notice') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('notice-board')
                    <li class="nav-item">
                        <a href="{{url('/hrm/notice/show')}}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/notice/show') ? 'active' : '' }}">
                            <i class="bi bi-bullseye nav-sub-icon"></i>
                            {{ __('Notice List') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- Salary Statement -->
        @permission('file-upload')
        <li class="nav-item">
            <a href="{{ url('/hrm/salary/statement/search') }}"
               class="nav-link {{ request()->is('hrm/salary/statement/search') ? 'active' : '' }}">
                <i class="bi bi-file-text nav-icon"></i>
                <span>{{ __('Salary Statement') }}</span>
            </a>
        </li>
        @endpermission

        <!-- Configuration -->
        @permission('hrm-setting')
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#configMenu"
                 aria-expanded="false" aria-controls="configMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-gear nav-icon"></i>
                    <span>{{ __('Configuration') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="configMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ url('/setting/departments') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/departments') ? 'active' : '' }}">
                            <i class="bi bi-building nav-sub-icon"></i>
                            {{ __('Manage Job Groups') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/setting/designations') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/designations') ? 'active' : '' }}">
                            <i class="bi bi-person-badge nav-sub-icon"></i>
                            {{ __('Manage Designations') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/setting/leave_categories') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/leave_categories') ? 'active' : '' }}">
                            <i class="bi bi-tags nav-sub-icon"></i>
                            {{ __('Manage Leave Categories') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/setting/working-days') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/working-days') ? 'active' : '' }}">
                            <i class="bi bi-calendar-week nav-sub-icon"></i>
                            {{ __('Set Working Day') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/setting/holidays') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/holidays') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event nav-sub-icon"></i>
                            {{ __('Holiday List') }}
                        </a>
                    </li>
                    @permission('role')
                    <li class="nav-item">
                        <a href="{{ route('setting.role.index') }}"
                           class="nav-link nav-sub-link {{ request()->is('setting/role') ? 'active' : '' }}">
                            <i class="bi bi-shield-check nav-sub-icon"></i>
                            {{ __('Role') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endpermission

        <!-- Employee-specific menus -->
        @if(\Auth::user()->access_label != 1)
        <li class="nav-item">
            <a href="{{ url('/hrm/p9-report?employee='.\Auth::id()) }}"
               class="nav-link {{ request()->is('hrm/p9-report*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-pdf nav-icon"></i>
                <span>{{ __('P9 Report') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/hrm/generate-payslips/') }}"
               class="nav-link {{ request()->is('hrm/generate-payslips/*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text nav-icon"></i>
                <span>{{ __('Generate Payslip') }}</span>
            </a>
        </li>
        @endif

        <!-- Reports (Admin only) -->
        @if(\Auth::user()->access_label == 1)
        <li class="nav-item">
            <div class="nav-link accordion-button collapsed d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" data-bs-target="#reportsMenu"
                 aria-expanded="false" aria-controls="reportsMenu"
                 role="button" tabindex="0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-graph-up nav-icon"></i>
                    <span>{{ __('Reports') }}</span>
                </div>
                <i class="bi bi-chevron-down accordion-chevron"></i>
            </div>
            <div class="collapse" id="reportsMenu">
                <ul class="nav nav-pills flex-column ms-3">
                    @permission('manage-employee')
                    <li class="nav-item">
                        <a href="{{ url('/people/employees-report') }}"
                           class="nav-link nav-sub-link {{ request()->is('people/employees-report') ? 'active' : '' }}">
                            <i class="bi bi-people nav-sub-icon"></i>
                            {{ __('Employee Report') }}
                        </a>
                    </li>
                    @endpermission
                    <li class="nav-item">
                        <a href="{{ url('/hrm/payroll-report-detail') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/payroll-report-detail') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph nav-sub-icon"></i>
                            {{ __('Detailed Payroll Report') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/nssf-report') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/nssf-report') ? 'active' : '' }}">
                            <i class="bi bi-shield-check nav-sub-icon"></i>
                            {{ __('NSSF Report') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/nhif-report') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/nhif-report') ? 'active' : '' }}">
                            <i class="bi bi-hospital nav-sub-icon"></i>
                            {{ __('SHIF Report') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/paye-report') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/paye-report') ? 'active' : '' }}">
                            <i class="bi bi-receipt nav-sub-icon"></i>
                            {{ __('PAYE Report') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/salary/sheet/search') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/salary/sheet/search') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack nav-sub-icon"></i>
                            {{ __('Salary Report') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/hrm/p9-report') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/p9-report') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-pdf nav-sub-icon"></i>
                            {{ __('P9 Report') }}
                        </a>
                    </li>
                    @permission('leave-reports')
                    <li class="nav-item">
                        <a href="{{ url('/hrm/leave-reports') }}"
                           class="nav-link nav-sub-link {{ request()->is('hrm/leave-reports') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check nav-sub-icon"></i>
                            {{ __('Leave Reports') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endif

        <!-- User Profile Section -->
        <li class="nav-item mt-3 border-top pt-3">
            <a href="{{ url('/profile/user-profile') }}"
               class="nav-link {{ request()->is('profile/user-profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle nav-icon"></i>
                <span>{{ __('Profile') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('/profile/change-password') }}"
               class="nav-link {{ request()->is('profile/change-password') ? 'active' : '' }}">
                <i class="bi bi-shield-lock nav-icon"></i>
                <span>{{ __('Change Password') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
               class="nav-link text-danger">
                <i class="bi bi-box-arrow-right nav-icon"></i>
                <span>{{ __('Logout') }}</span>
            </a>
        </li>
    </ul>

    <!-- Logout form for sidebar -->
    <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

<!-- Custom Styles for Sidebar Navigation -->
<style>
/* Sidebar Navigation Styles */
.sidebar-nav {
    padding: 1rem 0;
}

.nav-item {
    margin-bottom: 0.25rem;
}

.nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    background: transparent;
    text-decoration: none;
    display: flex;
    align-items: center;
    min-height: 44px; /* WCAG touch target size */
    position: relative;
}

.nav-link:hover,
.nav-link:focus {
    color: white;
    background-color: rgba(255, 255, 255, 0.1);
    text-decoration: none;
}

.nav-link.active {
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 70%;
    background-color: white;
    border-radius: 0 2px 2px 0;
}

.nav-icon {
    font-size: 1.25rem;
    width: 1.5rem;
    text-align: center;
    margin-right: 0.75rem;
}

.nav-sub-link {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
    margin-left: 0.5rem;
    min-height: 36px;
}

.nav-sub-link:hover,
.nav-sub-link:focus {
    color: white;
    background-color: rgba(255, 255, 255, 0.05);
}

.nav-sub-link.active {
    color: white;
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-sub-icon {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
    margin-right: 0.5rem;
}

/* Accordion Styles */
.accordion-button {
    background: transparent;
    color: inherit;
    padding: 0;
    box-shadow: none;
    border: none;
    min-height: 44px;
}

.accordion-button:not(.collapsed) {
    background: transparent;
    color: white;
    box-shadow: none;
}

.accordion-button::after {
    display: none;
}

.accordion-chevron {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
    margin-left: auto;
}

.accordion-button:not(.collapsed) .accordion-chevron {
    transform: rotate(180deg);
}

/* Collapse Menu Styles */
.collapse .nav {
    padding-top: 0.5rem;
}

/* Dark mode support */
[data-bs-theme="dark"] .sidebar-nav {
    background: linear-gradient(180deg, #1a1d23 0%, #2d3748 100%);
}

/* Mobile responsiveness */
@media (max-width: 767.98px) {
    .sidebar-nav {
        padding: 0.5rem 0;
    }

    .nav-link {
        padding: 1rem;
        min-height: 48px;
    }

    .nav-sub-link {
        padding: 0.75rem 1rem;
        min-height: 44px;
    }

    .nav-icon {
        font-size: 1.5rem;
        width: 2rem;
        margin-right: 1rem;
    }
}

/* Accessibility improvements */
.nav-link:focus-visible {
    outline: 2px solid white;
    outline-offset: 2px;
}

.nav-link[aria-expanded="true"] {
    color: white;
    font-weight: 600;
}

/* Animation for menu items */
.nav-item {
    animation: slideIn 0.3s ease forwards;
    opacity: 0;
}

.nav-item:nth-child(1) { animation-delay: 0.1s; }
.nav-item:nth-child(2) { animation-delay: 0.2s; }
.nav-item:nth-child(3) { animation-delay: 0.3s; }
.nav-item:nth-child(4) { animation-delay: 0.4s; }
.nav-item:nth-child(5) { animation-delay: 0.5s; }

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .nav-link,
    .accordion-chevron,
    .nav-item {
        animation: none;
        transition: none;
    }
}
</style>

<!-- Sidebar Navigation JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle keyboard navigation for accordion menus
    document.querySelectorAll('.accordion-button').forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Set active menu item based on current URL
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('#mainMenu .nav-link:not(.accordion-button)');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href.replace(/^https?:\/\/[^\/]+/, ''))) {
            link.classList.add('active');
        }
    });

    // Handle submenu expansion on mobile
    if (window.innerWidth < 768) {
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                const collapse = this.nextElementSibling;
                const parent = this.closest('.nav-item');

                // Close other open submenus
                document.querySelectorAll('.collapse.show').forEach(otherCollapse => {
                    if (otherCollapse !== collapse) {
                        otherCollapse.classList.remove('show');
                        otherCollapse.previousElementSibling.classList.add('collapsed');
                        otherCollapse.previousElementSibling.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    }

    // Announce menu state changes to screen readers
    const liveRegion = document.getElementById('aria-live-region');
    if (liveRegion) {
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const menuName = this.querySelector('span').textContent;
                const message = isExpanded ?
                    `${menuName} menu expanded` :
                    `${menuName} menu collapsed`;

                liveRegion.textContent = message;

                // Clear announcement after a delay
                setTimeout(() => {
                    liveRegion.textContent = '';
                }, 1000);
            });
        });
    }
});
</script>