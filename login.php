<?php
require_once __DIR__ . '/config.php';
$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SioPao - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Baloo 2', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #8c1c2f;
            --primary-dark: #5b1221;
            --accent: #f4a523;
            --accent-dark: #c96f1a;
            --cream: #fce9d4;
            --sand: #f5d3a4;
            --text-dark: #2d1c13;
            --muted: #7b5b40;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(244, 165, 35, 0.25), transparent 45%),
                        radial-gradient(circle at bottom right, rgba(252, 233, 212, 0.45), transparent 50%),
                        linear-gradient(160deg, #5b1221 0%, #8c1c2f 65%, #300a14 100%);
            padding: 20px;
            color: var(--text-dark);
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 600px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            overflow: hidden;
            border: 2px solid rgba(252, 233, 212, 0.2);
            backdrop-filter: blur(8px);
            box-shadow: 0 25px 45px rgba(32, 7, 12, 0.35);
        }
        
        .left-panel {
            flex: 1;
            background: radial-gradient(circle at top, rgba(244, 165, 35, 0.35), transparent 65%),
                        linear-gradient(140deg, #8c1c2f 0%, #5b1221 60%, #300a14 100%);
            color: var(--cream);
            padding: 55px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 320px;
            height: 320px;
            background: rgba(244, 165, 35, 0.18);
            border-radius: 55% 45% 60% 40%;
        }
        
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: 10%;
            width: 220px;
            height: 220px;
            background: rgba(252, 233, 212, 0.12);
            border-radius: 45% 55% 40% 60%;
        }
        
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 48px;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 220px;
            max-width: 100%;
            filter: drop-shadow(0 10px 22px rgba(0, 0, 0, 0.28));
        }
        
        .left-panel h2 {
            font-size: 32px;
            margin-top: 10px;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
            color: var(--sand);
        }
        
        .left-panel p {
            font-size: 17px;
            opacity: 0.9;
            line-height: 1.7;
            max-width: 320px;
            color: rgba(252, 233, 212, 0.85);
        }
        
        .right-panel {
            flex: 1;
            padding: 55px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(145deg, rgba(252, 233, 212, 0.95) 0%, rgba(255, 255, 255, 0.95) 60%, rgba(255, 246, 230, 0.9) 100%);
            position: relative;
        }
        
        .login-header {
            margin-bottom: 38px;
            position: relative;
        }
        
        .login-header h1 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 6px;
        }
        
        .login-header p {
            color: var(--muted);
            font-size: 17px;
        }
        
        .login-form {
            width: 100%;
            position: relative;
        }
        
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            opacity: 0.7;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid rgba(140, 28, 47, 0.12);
            border-radius: 14px;
            font-size: 17px;
            background-color: rgba(255, 255, 255, 0.85);
            transition: all 0.25s ease;
            color: var(--text-dark);
        }
        
        .form-control:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 8px 18px rgba(92, 17, 30, 0.14);
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 26px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
            accent-color: var(--accent);
        }
        
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        
        .login-button {
            width: 100%;
            padding: 18px;
            background: linear-gradient(120deg, var(--accent) 0%, #ffbd4a 50%, var(--accent-dark) 100%);
            color: #3c1507;
            border: none;
            border-radius: 16px;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        
        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 22px rgba(60, 21, 7, 0.22);
        }
        
        .login-button:active {
            transform: translateY(0);
            box-shadow: none;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--muted);
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(92, 17, 30, 0.1);
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 15px;
            letter-spacing: 0.6px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 18px;
        }
        
        .social-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid rgba(140, 28, 47, 0.15);
            background-color: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-4px);
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 10px 16px rgba(60, 21, 7, 0.15);
        }
        
        .signup-link {
            text-align: center;
            margin-top: 30px;
            color: var(--muted);
        }
        
        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            color: var(--accent);
        }
        
        @media (max-width: 950px) {
            .login-container {
                flex-direction: column;
                width: 95%;
                height: auto;
            }
            
            .left-panel {
                padding: 45px 36px;
                align-items: center;
                text-align: center;
            }
            
            .right-panel {
                padding: 45px 36px;
            }

            .left-panel p {
                max-width: none;
            }
        }
        
        @media (max-width: 480px) {
            .left-panel {
                padding: 36px 24px;
            }
            
            .right-panel {
                padding: 36px 24px;
            }
            
            .options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .login-button {
                padding: 16px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel with Logo and Welcome Message -->
        <div class="left-panel">
            <div class="logo-container">
                <img src="123.jpg" alt="Monlei SioPao logo" class="brand-logo">
            </div>
            <h2>Welcome to SioPao</h2>
            <p>Sign in to access your personalized dashboard, manage your account, and explore all the features we offer for our valued users.</p>
        </div>
        
        <!-- Right Panel with Login Form -->
        <div class="right-panel">
            <div class="login-header">
                <h1>Sign In</h1>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <?php if ($flash): ?>
            <div style="margin-bottom:16px;color:<?php echo $flash['type']==='error' ? '#8c1c2f' : '#2d6834'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <form class="login-form" id="loginForm" method="POST" action="auth/login.php">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username or email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgotpassword.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="login-button">Sign In</button>
                
                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up now</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // No extra JS needed; form posts to backend.
    </script>
</body>
</html>
