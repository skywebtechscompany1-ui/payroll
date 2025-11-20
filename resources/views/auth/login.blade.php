@extends('layouts.app')

@push('styles')
<style>
    :root {
        --auth-bg: #0f172a;
        --auth-accent: #5c7cfa;
        --auth-accent-dark: #4c6ef5;
        --auth-muted: #94a3b8;
    }
    .auth-wrapper {
        min-height: calc(100vh - 100px);
        padding: 40px 15px 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.82)),
                    url('{{ asset('images/Background.jpg') }}') center/cover no-repeat;
    }
    .login-card {
        width: 100%;
        max-width: 1100px;
        background: #ffffff;
        border-radius: 26px;
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.35);
        display: flex;
        flex-wrap: wrap;
    }
    .login-card__brand {
        flex: 1 1 40%;
        min-width: 280px;
        background: linear-gradient(160deg, #101936, #182449);
        color: #f8fafc;
        padding: 50px 45px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .login-card__brand h1 {
        font-weight: 600;
        margin-bottom: 12px;
    }
    .login-card__brand p {
        color: rgba(248, 250, 252, 0.8);
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .brand-pill {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 6px 16px;
        border-radius: 40px;
        border: 1px solid rgba(248, 250, 252, 0.3);
        width: fit-content;
        margin-bottom: 22px;
    }
    .brand-highlights {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .brand-highlights li {
        position: relative;
        padding-left: 24px;
        margin-bottom: 12px;
        color: rgba(248, 250, 252, 0.85);
    }
    .brand-highlights li::before {
        content: '•';
        position: absolute;
        left: 0;
        color: var(--auth-accent);
        font-size: 20px;
        line-height: 1;
    }
    .brand-stats {
        display: flex;
        gap: 24px;
        margin-top: 28px;
    }
    .brand-stats__value {
        display: block;
        font-size: 26px;
        font-weight: 600;
        color: #fff;
    }
    .brand-stats__label {
        font-size: 13px;
        color: rgba(248, 250, 252, 0.7);
    }
    .login-card__form {
        flex: 1 1 60%;
        min-width: 320px;
        padding: 55px;
        background: #fff;
    }
    .form-headline h2 {
        font-weight: 600;
        margin-bottom: 6px;
    }
    .form-headline p {
        color: var(--auth-muted);
        margin-bottom: 35px;
    }
    .floating-label {
        margin-bottom: 22px;
    }
    .floating-label label {
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
        margin-bottom: 6px;
        display: block;
    }
    .floating-label input {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: none;
        padding: 12px 16px;
        height: auto;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .floating-label input:focus {
        border-color: var(--auth-accent);
        box-shadow: 0 0 0 3px rgba(92, 124, 250, 0.15);
    }
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 25px;
    }
    .form-actions .checkbox label {
        padding-left: 0;
    }
    .form-actions a {
        font-weight: 600;
        color: var(--auth-accent-dark);
    }
    .login-btn {
        border-radius: 16px;
        padding: 12px;
        font-weight: 600;
        font-size: 15px;
        letter-spacing: 0.3px;
        background: linear-gradient(120deg, var(--auth-accent), #7f9cfb);
        border: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .login-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(92, 124, 250, 0.3);
    }
    .form-footer {
        margin-top: 20px;
        color: var(--auth-muted);
    }
    .form-footer a {
        color: var(--auth-accent-dark);
        font-weight: 600;
    }
    @media (max-width: 992px) {
        .login-card {
            max-width: 680px;
        }
        .login-card__brand,
        .login-card__form {
            flex: 1 1 100%;
        }
        .login-card__brand {
            border-radius: 26px 26px 0 0;
        }
    }
    @media (max-width: 576px) {
        .login-card__form {
            padding: 35px 25px;
        }
        .form-actions {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="auth-wrapper">
    <div class="login-card">
        <div class="login-card__brand">
            <span class="brand-pill">{{ __('Trusted HR Suite') }}</span>
            <h1>{{ config('app.name', __('Payroll Suite')) }}</h1>
            <p>{{ __('Stay on top of attendance, payroll, and compliance from a single command center.') }}</p>
            <ul class="brand-highlights">
                <li>{{ __('Flexible monthly, daily, or hourly payroll workflows') }}</li>
                <li>{{ __('Realtime people insights & approvals') }}</li>
                <li>{{ __('Bank-ready payouts with detailed reporting') }}</li>
            </ul>
            <div class="brand-stats">
                <div>
                    <span class="brand-stats__value">{{ __('24/7') }}</span>
                    <span class="brand-stats__label">{{ __('Availability') }}</span>
                </div>
                <div>
                    <span class="brand-stats__value">99.9%</span>
                    <span class="brand-stats__label">{{ __('Uptime') }}</span>
                </div>
            </div>
        </div>
        <div class="login-card__form">
            <div class="form-headline">
                <h2>{{ __('Welcome back') }}</h2>
                <p>{{ __('Sign in to unlock your dashboard and keep work moving.') }}</p>
            </div>
            <form method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}

                <div class="floating-label{{ $errors->has('email') ? ' has-error' : '' }}">
                    <label for="email">{{ __('Work Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="floating-label{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required>
                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-actions">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ __('Remember me') }}
                        </label>
                    </div>
                    <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block login-btn">
                    {{ __('Sign in') }}
                </button>
            </form>
            <p class="form-footer">
                {{ __('Need an account?') }}
                <a href="{{ route('register') }}">{{ __('Create an account') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
