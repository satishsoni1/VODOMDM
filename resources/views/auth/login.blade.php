<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — GlobalSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --teal:       #1a8a7c;
            --teal-dark:  #0d4f47;
            --teal-deep:  #083830;
            --teal-light: #e8f5f3;
            --orange:     #f07030;
            --orange-lt:  #fff3eb;
        }

        html, body { height: 100%; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

        /* ── Layout ── */
        .login-wrap {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 480px;
        }

        /* ── Left brand panel ── */
        .brand-panel {
            background: linear-gradient(145deg, var(--teal-deep) 0%, var(--teal-dark) 50%, var(--teal) 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        /* decorative circles */
        .brand-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            border: 80px solid rgba(255,255,255,.04);
            top: -160px; right: -160px;
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            width: 380px; height: 380px;
            border-radius: 50%;
            border: 60px solid rgba(255,255,255,.05);
            bottom: -120px; left: -100px;
        }

        .brand-logo { height: 44px; }

        .brand-tagline {
            font-size: 2.4rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -.5px;
            margin-bottom: 1rem;
        }
        .brand-tagline span { color: var(--orange); }

        .brand-sub {
            color: rgba(255,255,255,.65);
            font-size: 1rem;
            line-height: 1.6;
            max-width: 380px;
        }

        .feature-list { list-style: none; margin-top: 2.5rem; }
        .feature-list li {
            color: rgba(255,255,255,.8);
            font-size: .92rem;
            padding: .55rem 0;
            display: flex;
            align-items: center;
            gap: .75rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .feature-list li:last-child { border-bottom: none; }
        .feature-icon {
            width: 34px; height: 34px;
            background: rgba(255,255,255,.1);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            color: var(--orange);
        }

        .brand-footer {
            color: rgba(255,255,255,.35);
            font-size: .78rem;
        }

        /* ── Right form panel ── */
        .form-panel {
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 3.5rem;
            position: relative;
        }

        .form-panel .form-eyebrow {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--teal);
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .form-panel h2 {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--teal-dark);
            margin-bottom: .4rem;
        }

        .form-panel .form-lead {
            color: #6c757d;
            font-size: .9rem;
            margin-bottom: 2.2rem;
        }

        /* inputs */
        .gs-input-wrap {
            position: relative;
            margin-bottom: 1.2rem;
        }
        .gs-input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9db5b2;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color .2s;
        }
        .gs-input {
            width: 100%;
            padding: .78rem 1rem .78rem 2.75rem;
            border: 1.5px solid #d4eeeb;
            border-radius: 10px;
            font-size: .95rem;
            background: #f8fefd;
            color: #1a2e2c;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .gs-input:focus {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(26,138,124,.1);
        }
        .gs-input.is-invalid {
            border-color: #dc3545;
            background: #fff8f8;
        }
        .gs-input-wrap:focus-within .input-icon { color: var(--teal); }
        .gs-input::placeholder { color: #b0cbc8; }

        /* password toggle */
        .pwd-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9db5b2;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            transition: color .2s;
        }
        .pwd-toggle:hover { color: var(--teal); }

        /* error */
        .field-error {
            font-size: .78rem;
            color: #dc3545;
            margin-top: .35rem;
            padding-left: .25rem;
        }

        /* remember / forgot row */
        .form-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.6rem;
        }
        .remember-label {
            display: flex; align-items: center; gap: .5rem;
            font-size: .87rem; color: #6c757d; cursor: pointer;
        }
        .remember-check {
            width: 16px; height: 16px;
            accent-color: var(--teal);
            cursor: pointer;
        }
        .forgot-link {
            font-size: .87rem;
            color: var(--teal);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* submit btn */
        .gs-btn {
            width: 100%;
            padding: .85rem;
            background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .3px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: opacity .2s, transform .1s;
        }
        .gs-btn:hover { opacity: .93; }
        .gs-btn:active { transform: scale(.99); }
        .gs-btn .btn-arrow {
            display: inline-block;
            margin-left: .5rem;
            transition: transform .2s;
        }
        .gs-btn:hover .btn-arrow { transform: translateX(4px); }

        /* status message */
        .status-msg {
            background: var(--teal-light);
            border-left: 3px solid var(--teal);
            border-radius: 8px;
            padding: .7rem 1rem;
            font-size: .88rem;
            color: var(--teal-dark);
            margin-bottom: 1.5rem;
        }

        /* alert */
        .login-alert {
            background: #fce8e8;
            border-left: 3px solid #dc3545;
            border-radius: 8px;
            padding: .7rem 1rem;
            font-size: .88rem;
            color: #721c24;
            margin-bottom: 1.5rem;
        }

        /* client badge at bottom */
        .portal-badge {
            position: absolute;
            bottom: 1.5rem; left: 0; right: 0;
            text-align: center;
            font-size: .78rem;
            color: #adb5bd;
        }
        .portal-badge span {
            display: inline-flex; align-items: center; gap: .3rem;
        }
        .portal-lock { color: var(--teal); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .login-wrap { grid-template-columns: 1fr; }
            .brand-panel {
                padding: 2rem;
                min-height: auto;
            }
            .brand-tagline { font-size: 1.8rem; }
            .feature-list { display: none; }
            .form-panel { padding: 2.5rem 2rem; }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    {{-- ─── Left: Brand Panel ─────────────────────────────────────────────────── --}}
    <div class="brand-panel">
        <div>
            <img src="{{ asset('logo.png') }}" alt="GlobalSpace" class="brand-logo">
        </div>

        <div>
            <h1 class="brand-tagline">
                Smart Device<br>
                <span>Lifecycle</span><br>
                Management
            </h1>
            <p class="brand-sub">
                Track every device from procurement to disposal.
                Real-time MDM sync, field staff visibility, and
                complete CRM in one platform.
            </p>

            <ul class="feature-list">
                <li>
                    <div class="feature-icon"><i class="bi bi-phone-fill"></i></div>
                    <div><strong>End-to-end Lifecycle</strong> — from RFQ to device disposal</div>
                </li>
                <li>
                    <div class="feature-icon"><i class="bi bi-wifi"></i></div>
                    <div><strong>Live MDM Sync</strong> — Headwind MDM device status & compliance</div>
                </li>
                <li>
                    <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                    <div><strong>Field Staff Tracking</strong> — designation-wise device assignment</div>
                </li>
                <li>
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <div><strong>Insurance & Recovery</strong> — claims, follow-ups, case management</div>
                </li>
            </ul>
        </div>

        <div class="brand-footer">
            &copy; {{ date('Y') }} GlobalSpace &nbsp;·&nbsp; Asset Tracking Platform
        </div>
    </div>

    {{-- ─── Right: Form Panel ──────────────────────────────────────────────────── --}}
    <div class="form-panel">

        <div class="form-eyebrow">Welcome back</div>
        <h2>Sign In</h2>
        <p class="form-lead">Enter your credentials to access the platform</p>

        {{-- Session status --}}
        @if (session('status'))
        <div class="status-msg">
            <i class="bi bi-info-circle me-2"></i>{{ session('status') }}
        </div>
        @endif

        {{-- Auth errors (wrong credentials) --}}
        @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
        <div class="login-alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            {{-- Email --}}
            <div class="gs-input-wrap">
                <i class="bi bi-envelope input-icon"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    class="gs-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    autocomplete="username"
                    autofocus
                    required>
            </div>
            @error('email')
            <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
            @enderror

            {{-- Password --}}
            <div class="gs-input-wrap mt-1">
                <i class="bi bi-lock input-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="gs-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    autocomplete="current-password"
                    required>
                <button type="button" class="pwd-toggle" id="pwdToggle" tabindex="-1" title="Show/hide password">
                    <i class="bi bi-eye" id="pwdIcon"></i>
                </button>
            </div>
            @error('password')
            <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
            @enderror

            {{-- Remember / Forgot --}}
            <div class="form-extras mt-3">
                <label class="remember-label">
                    <input type="checkbox" name="remember" class="remember-check" id="remember">
                    Keep me signed in
                </label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="gs-btn" id="submitBtn">
                Sign In <span class="btn-arrow">→</span>
            </button>
        </form>

        <div class="portal-badge">
            <span>
                <i class="bi bi-shield-lock portal-lock"></i>
                Secured by GlobalSpace &nbsp;·&nbsp; v2.0
            </span>
        </div>
    </div>
</div>

<script>
// Password show/hide
const pwdInput = document.getElementById('password');
const pwdIcon  = document.getElementById('pwdIcon');
document.getElementById('pwdToggle').addEventListener('click', function () {
    const show = pwdInput.type === 'password';
    pwdInput.type = show ? 'text' : 'password';
    pwdIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});

// Spinner on submit
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Signing in…';
});
</script>
</body>
</html>
