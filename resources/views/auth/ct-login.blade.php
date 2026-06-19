<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('images/favico.ico') }}">
    
    <title>Login | Cargo Taxi</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }
        
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(132, 204, 22, 0.06) 0%, rgba(132, 204, 22, 0.01) 100%);
            animation: float 25s infinite ease-in-out;
        }
        
        .bg-circle:nth-child(1) { width: 800px; height: 800px; top: -400px; right: -200px; }
        .bg-circle:nth-child(2) { width: 600px; height: 600px; bottom: -300px; left: -200px; animation-delay: -8s; }
        .bg-circle:nth-child(3) { width: 400px; height: 400px; top: 30%; left: 40%; animation-delay: -15s; }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }
        
        /* Left Panel - Brand */
        .brand-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            position: relative;
            z-index: 1;
        }
        
        .brand-content {
            max-width: 500px;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        .brand-logo img {
            height: 60px;
            width: auto;
        }
        
        .brand-logo-fallback {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .brand-logo-icon {
            width: 56px;
            height: 56px;
            background: #84cc16;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #1e293b;
            box-shadow: 0 8px 32px rgba(132, 204, 22, 0.4);
        }
        
        .brand-logo-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.02em;
        }
        
        .brand-logo-text span {
            color: #84cc16;
        }
        
        .brand-tagline {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }
        
        .brand-tagline span {
            color: #84cc16;
        }
        
        .brand-description {
            font-size: 1.125rem;
            color: rgba(30, 41, 59, 0.6);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        
        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .brand-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(30, 41, 59, 0.8);
        }
        
        .brand-feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(132, 204, 22, 0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #84cc16;
        }
        
        /* Right Panel - Login Form */
        .login-panel {
            width: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0a1628;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #64748b;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-input {
            width: 100%;
            height: 52px;
            padding: 0 1rem 0 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #ff9500;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 149, 0, 0.15);
        }
        
        .form-input::placeholder {
            color: #94a3b8;
        }
        
        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .form-input:focus + .form-icon {
            color: #ff9500;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.25rem;
        }
        
        .password-toggle:hover {
            color: #64748b;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
            cursor: pointer;
        }
        
        .remember-label input {
            width: 18px;
            height: 18px;
            accent-color: #f59e0b;
        }
        
        .forgot-link {
            font-size: 0.875rem;
            color: #84cc16;
            font-weight: 500;
            text-decoration: none;
        }
        
        .forgot-link:hover {
            color: #65d419;
        }
        
        .btn-login {
            width: 100%;
            height: 52px;
            background: #84cc16;
            color: #1e293b;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-login:hover {
            background: #65d419;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(132, 204, 22, 0.4);
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .social-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-social {
            height: 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-social:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        /* Language Selector */
        .lang-select {
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 10;
        }
        
        .lang-select select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 2rem 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1rem;
        }
        
        .lang-select select option {
            background: #0a1628;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .brand-panel {
                display: none;
            }
            
            .login-panel {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .login-panel {
                padding: 1rem;
            }
            
            .login-card {
                padding: 2rem;
            }
            
            .social-btns {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animation */
        .login-card {
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>
    
    <div class="lang-select">
        <select onchange="window.location.href=this.value">
            <option value="{{ url('locale/en') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
            <option value="{{ url('locale/de') }}" {{ app()->getLocale() == 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
        </select>
    </div>
    
    <!-- Brand Panel -->
    <div class="brand-panel">
        <div class="brand-content">
            <div class="brand-logo">
                @include('components.cargotaxi-logo', ['size' => 80])
            </div>
            
            <h1 class="brand-tagline" style="color: #1e293b;">
                Schnell, sicher,<br><span style="color: #84cc16;">sinnvoll.</span>
            </h1>
            
            <p class="brand-description">
                Die neue Art des Transportierens. Sie tragen die Entscheidung, wir tragen Ihre Last.
            </p>
            
            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span>24/7 verfügbar - rund um die Uhr</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <span>Live-Tracking Ihrer Sendungen</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span>Sichere und versicherte Lieferung</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Login Panel -->
    <div class="login-panel">
        <div class="login-card">
            <div class="login-header">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to your account</p>
            </div>
            
            @if(session('status'))
                <div class="error-box" style="background: #ecfdf5; border-color: #a7f3d0; color: #047857;">
                    <i class="fas fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="form-input-wrapper">
                        <input type="email" name="email" class="form-input" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="form-input-wrapper">
                        <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                        <i class="fas fa-lock form-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <a href="{{ url('forget') }}" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
            
            <div class="divider">or continue with</div>
            
            <div class="social-btns">
                <a href="{{ url('login/google') }}" class="btn-social">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

