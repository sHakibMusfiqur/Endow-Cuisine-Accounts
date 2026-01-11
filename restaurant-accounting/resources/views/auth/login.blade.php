<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Endow Cuisine Accounting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #EA222A 0%, #292929 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo i {
            font-size: 60px;
            color: #EA222A;
        }

        .login-logo h2 {
            margin-top: 15px;
            color: #292929;
        }

        .form-control:focus {
            border-color: #EA222A;
            box-shadow: 0 0 0 0.2rem rgba(234, 34, 42, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #FF3D47 0%, #EA222A 100%);
        }

        .demo-credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }

        .forgot-password-link {
            color: #EA222A;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password-link:hover {
            color: #292929;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-utensils"></i>
            <h2>Endow Cuisine Accounting</h2>
            <p class="text-muted">Please login to continue</p>
        </div>

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <a href="{{ route('password.request') }}" class="forgot-password-link">
                    Forgot your password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="demo-credentials">
            <strong>Demo Credentials:</strong><br>
            <small>
                <strong>Admin:</strong> admin@restaurant.com / password<br>
                <strong>Accountant:</strong> accountant@restaurant.com / password<br>
                <strong>Manager:</strong> manager@restaurant.com / password
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
