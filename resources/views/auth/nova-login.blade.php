<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ url('images/favico.ico') }}">
    
    <title>Login | {{ config('app.name', 'DispatchPro') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #10b981;
            --color-primary-dark: #059669;
            --color-primary-50: #ecfdf5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            inset: 0;
            overflow: hidden;
            z-index: 0;
        }
        
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            animation: float 20s infinite ease-in-out;
        }
        
        .bg-shape:nth-child(1) {
            width: 600px;
            height: 600px;
            top: -200px;
            right: -100px;
            animation-delay: 0s;
        }
        
        .bg-shape:nth-child(2) {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
        }
        
        .bg-shape:nth-child(3) {
            width: 300px;
            height: 300px;
            top: 50%;
            left: 50%;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, -20px) rotate(5deg); }
            50% { transform: translate(-10px, 20px) rotate(-5deg); }
            75% { transform: translate(-20px, -10px) rotate(3deg); }
        }
        
        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        
        .login-header {
            text-align: center;
            padding: 2.5rem 2rem 2rem;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            position: relative;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            backdrop-filter: blur(10px);
        }
        
        .logo-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.03em;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            font-size: 0.9375rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.2s;
        }
        
        .form-input {
            width: 100%;
            height: 52px;
            padding: 0 1rem 0 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            color: #111827;
            background: #f9fafb;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: white;
            box-shadow: 0 0 0 4px var(--color-primary-50);
        }
        
        .form-input:focus + .form-input-icon,
        .form-input:not(:placeholder-shown) + .form-input-icon {
            color: var(--color-primary);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: #6b7280;
        }
        
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
            accent-color: var(--color-primary);
            cursor: pointer;
        }
        
        .remember-me span {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .forgot-link {
            font-size: 0.875rem;
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .forgot-link:hover {
            color: var(--color-primary-dark);
        }
        
        .btn-login {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        
        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .btn-social {
            height: 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-social:hover {
            border-color: #d1d5db;
            background: #f9fafb;
        }
        
        .btn-social img {
            width: 20px;
            height: 20px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.9375rem;
            color: #6b7280;
        }
        
        .register-link a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .register-link a:hover {
            color: var(--color-primary-dark);
        }
        
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .success-message {
            background: var(--color-primary-50);
            border: 1px solid #a7f3d0;
            color: #047857;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Language Switcher */
        .lang-switch {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 10;
        }
        
        .lang-switch select {
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
        
        .lang-switch select option {
            background: #1e293b;
            color: white;
        }
        
        /* Animation */
        .login-card {
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .login-body {
                padding: 1.5rem;
            }
            
            .social-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>
    
    <!-- Language Switcher -->
    <div class="lang-switch">
        <select onchange="window.location.href=this.value">
            <option value="{{ url('locale/en') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
            <option value="{{ url('locale/de') }}" {{ app()->getLocale() == 'de' ? 'selected' : '' }}>🇩🇪 Deutsch</option>
        </select>
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <span class="logo-text">DispatchPro</span>
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your account to continue</p>
            </div>
            
            <div class="login-body">
                @if(session('status'))
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="error-message">
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
                            <input type="email" 
                                   name="email" 
                                   class="form-input" 
                                   placeholder="Enter your email"
                                   value="{{ old('email') }}"
                                   required 
                                   autofocus>
                            <i class="fas fa-envelope form-input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="form-input-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-input" 
                                   placeholder="Enter your password"
                                   required>
                            <i class="fas fa-lock form-input-icon"></i>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Remember me</span>
                        </label>
                        <a href="{{ url('forget') }}" class="forgot-link">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                @if(Route::has('login.google') || Route::has('login.facebook'))
                <div class="divider">or continue with</div>
                
                <div class="social-buttons">
                    <a href="{{ url('login/google') }}" class="btn-social">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Google
                    </a>
                    <a href="{{ url('login/facebook') }}" class="btn-social">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877F2">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </a>
                </div>
                @endif
                
                @if(Route::has('register'))
                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Create one</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

