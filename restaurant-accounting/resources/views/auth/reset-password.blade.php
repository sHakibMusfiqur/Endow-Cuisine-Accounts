<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Endow Cuisine Accounting</title>
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
            color: #EA222A;
        }

        .reset-logo h2 {
            margin-top: 15px;
            color: #292929;
        }

        .form-control:focus {
            border-color: #EA222A;
            box-shadow: 0 0 0 0.2rem rgba(234, 34, 42, 0.25);
        }

        .btn-reset {
            background: linear-gradient(135deg, #EA222A 0%, #C01D24 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
        }

        .btn-reset:hover {
            background: linear-gradient(135deg, #FF3D47 0%, #EA222A 100%);
        }

        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
        }

        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 20px;
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
            <i class="fas fa-lock"></i>
            <h2>Reset Password</h2>
            <p class="info-text">
                Enter your new password below.
            </p>
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

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ old('email', $email) }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password_confirmation"
                           name="password_confirmation" required>
                </div>
            </div>

            <div class="password-requirements">
                <strong><i class="fas fa-info-circle"></i> Password Requirements:</strong>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Both passwords must match</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-primary btn-reset w-100 mt-3">
                <i class="fas fa-check"></i> Reset Password
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
