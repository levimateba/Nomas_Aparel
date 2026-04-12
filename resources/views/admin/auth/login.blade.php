<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ $settings->site_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #16456e 0%, #165752 100%);
            font-family: 'Poppins', Inter, sans-serif;
            padding: 24px;
        }
        .login-shell {
            width: 100%;
            max-width: 430px;
        }
        .login-box {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0,0,0,0.18);
        }
        .login-header {
            background: linear-gradient(135deg, #16456e 0%, #165752 100%);
            padding: 32px 30px;
            color: white;
            text-align: center;
        }
        .login-logo {
            width: 78px;
            height: 78px;
            object-fit: cover;
            border-radius: 18px;
            background: rgba(255,255,255,0.16);
            padding: 6px;
            margin-bottom: 16px;
        }
        .login-body {
            padding: 28px;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: 600;
            color: #233746;
        }
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cfd8df;
            border-radius: 10px;
            box-sizing: border-box;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 14px 0 18px;
            color: #52616d;
        }
        .remember-row input {
            margin: 0;
        }
        button {
            width: 100%;
            background: linear-gradient(135deg, #16456e 0%, #165752 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(22, 69, 110, 0.22);
        }
        .error {
            color: #b42318;
            background: rgba(180, 35, 24, 0.08);
            border: 1px solid rgba(180, 35, 24, 0.15);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-box">
            <div class="login-header">
                @if(!empty($settings->logo))
                    <img src="{{ $settings->logo }}" alt="{{ $settings->site_name }}" class="login-logo">
                @endif
                <h2 style="margin:0;">Admin Login</h2>
                <p style="margin:10px 0 0; opacity:0.9;">Manage {{ $settings->site_name }}</p>
            </div>

            <div class="login-body">
                @if($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <label class="remember-row">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>

                    <button type="submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
