<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <!-- head -->
    @include('administrator.layouts.head')
    <!-- /head -->
    <body class="bg-light">
        <!-- Skip to main content for accessibility -->
        <a href="#main-content" class="visually-hidden-focusable">Skip to main content</a>

        <!-- Mobile-first navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top" role="navigation" aria-label="Main navigation">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/dashboard') }}" aria-label="Dashboard">
                    <i class="bi bi-building me-2"></i>
                    <span class="fw-bold">Payroll System</span>
                </a>

                <!-- Mobile menu button -->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Desktop navigation -->
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <!-- Dynamic menu items will go here -->
                    </ul>

                    <!-- User menu -->
                    <div class="d-flex align-items-center">
                        <!-- Dark mode toggle -->
                        @include('administrator.partials.theme-toggle', ['size' => 'md'])

                        <!-- User dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-link text-white dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ Auth::user()->name ?? 'User' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ url('/profile') }}">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="{{ url('/settings') }}">
                                    <i class="bi bi-gear me-2"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Offcanvas sidebar for mobile -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="sidebarLabel">Navigation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @include('administrator.layouts.left_side_bar')
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div class="container-fluid">
            <div class="row">
                <!-- Desktop sidebar (hidden on mobile) -->
                <aside class="col-md-3 col-lg-2 d-none d-md-block">
                    <div class="position-sticky top-0">
                        @include('administrator.layouts.left_side_bar')
                    </div>
                </aside>

                <!-- Main content area -->
                <main class="col-md-9 col-lg-10" id="main-content">
                    @yield('main_content')
                </main>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-white py-3 mt-auto">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; {{ date('Y') }} Payroll Management System. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">Version 2.0 | Built with Laravel 11 & Bootstrap 5</small>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        @include('administrator.layouts.scripts')

        <!-- Accessibility Features -->
        @include('administrator.partials.accessibility')

        <!-- Performance Optimizations -->
        @include('administrator.partials.performance')

        <!-- Responsive Testing (Development Only) -->
        @include('administrator.partials.responsive-test')

        <!-- Logo Modal (modernized) -->
        <div class="modal fade" id="logo-modal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="logoModalLabel">
                            <i class="bi bi-image me-2"></i>Change Logo
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <img src="{{ asset('public/backend/img/corporatelogo.png') }}"
                                 width="120" height="120"
                                 id="logo-image"
                                 class="rounded border p-2"
                                 alt="Current logo">
                        </div>
                        <hr>
                        <div id="logo-message" class="alert d-none"></div>
                        <form id="logo-form" action="{{ route('logo.update') }}" enctype="multipart/form-data" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="logo_image" class="form-label fw-semibold">Upload Logo Image</label>
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*" required>
                                <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>Save Logo
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout form for security -->
        <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

    </body>
</html>
