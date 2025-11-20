<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', __('Laravel')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
        }
        body {
            font-family: 'DM Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: {{ auth()->check() ? '#f5f7fb' : '#050b18' }};
            color: #0f172a;
        }
        .frame-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .nav-shell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 5vw;
            border-bottom: 1px solid rgba(15, 23, 42, 0.05);
            background: {{ auth()->check() ? '#ffffff' : 'transparent' }};
            position: relative;
            z-index: 10;
        }
        .nav-shell--ghost {
            border-color: transparent;
            color: #e2e8f0;
        }
        .nav-shell__brand {
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-decoration: none;
            color: inherit;
        }
        .nav-shell__actions {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .btn-outline {
            border: 1px solid rgba(226, 232, 240, 0.4);
            color: inherit;
            padding: 8px 18px;
            border-radius: 999px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-solid {
            background: linear-gradient(120deg, #4c6ef5, #7f9cfb);
            color: #fff;
            padding: 9px 22px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .content-shell {
            flex: 1;
        }
    </style>

    @stack('styles')
</head>
<body class="{{ auth()->check() ? 'body-app' : 'body-auth' }}">
    <div class="frame-shell" id="app">
        <header class="nav-shell {{ auth()->check() ? '' : 'nav-shell--ghost' }}">
            <a class="nav-shell__brand" href="{{ url('/') }}">
                {{ config('app.name', 'Payroll Suite') }}
            </a>
            <div class="nav-shell__actions">
                @guest
                    @if (!request()->routeIs('login'))
                        <a class="btn-outline" href="{{ route('login') }}">{{ __('Login') }}</a>
                    @endif
                    @if (!request()->routeIs('register'))
                        <a class="btn-solid" href="{{ route('register') }}">{{ __('Register') }}</a>
                    @endif
                @else
                    <span>{{ Auth::user()->name }}</span>
                    <a class="btn-solid" href="{{ route('logout') }}"
                       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                        {{ csrf_field() }}
                    </form>
                @endguest
            </div>
        </header>

        <main class="content-shell">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
