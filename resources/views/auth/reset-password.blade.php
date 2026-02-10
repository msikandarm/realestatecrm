<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Real Estate CRM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .auth-container { width: 100%; max-width: 480px; }
        .auth-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); overflow: hidden; }
        .auth-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; color: white; }
        .auth-icon { width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 2rem; }
        .auth-header h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 8px; }
        .auth-header p { font-size: 0.95rem; opacity: 0.9; }
        .auth-body { padding: 40px 30px; }
        .alert { padding: 15px 18px; border-radius: 10px; margin-bottom: 25px; font-size: 0.9rem; display: flex; align-items: center; gap: 12px; }
        .alert i { font-size: 1.2rem; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.95rem; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1.1rem; }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 1rem; }
        .toggle-password:hover { color: #667eea; }
        .form-group input { width: 100%; padding: 14px 45px 14px 45px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 0.95rem; transition: all 0.3s; font-family: inherit; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
        .btn-primary { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3); }
        .btn-primary:active { transform: translateY(0); }
        .auth-footer { padding: 20px 30px; background: #f9fafb; text-align: center; }
        .auth-footer a { color: #667eea; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .auth-footer a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .auth-header { padding: 30px 20px; }
            .auth-body { padding: 30px 20px; }
            .auth-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your new password below</p>
            </div>

            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $email ?? '') }}"
                                   placeholder="Enter your email"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password"
                                   placeholder="Enter new password"
                                   required>
                            <i class="toggle-password fas fa-eye" onclick="togglePassword('password')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   placeholder="Confirm new password"
                                   required>
                            <i class="toggle-password fas fa-eye" onclick="togglePassword('password_confirmation')"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i>
                        Reset Password
                    </button>
                </form>
            </div>

            <div class="auth-footer">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
