<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Restaurant Accounting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }

        .reset-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .reset-logo i {
            font-size: 60px;
            color: #667eea;
        }

        .reset-logo h2 {
            margin-top: 15px;
            color: #2c3e50;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .info-text {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-logo">
            <i class="fas fa-key"></i>
            <h2>Forgot Password?</h2>
            <p class="info-text">
                No problem! Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        @if(session('status'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-reset w-100">
                <i class="fas fa-paper-plane"></i> Send Password Reset Link
            </button>
        </form>

        <div class="back-to-login">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
