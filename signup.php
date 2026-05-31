<?php
require_once __DIR__ . '/config.php';
$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SioPao - Sign Up</title>
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
        
        .signup-container {
            display: flex;
            width: 900px;
            max-height: 90vh;
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
            overflow-y: auto;
            max-height: 90vh;
        }
        
        .signup-header {
            margin-bottom: 32px;
            position: relative;
        }
        
        .signup-header h1 {
            font-size: 36px;
            color: var(--primary);
            margin-bottom: 6px;
        }
        
        .signup-header p {
            color: var(--muted);
            font-size: 17px;
        }
        
        .signup-form {
            width: 100%;
            position: relative;
        }
        
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 15px;
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
            padding: 12px 15px 12px 50px;
            border: 2px solid rgba(140, 28, 47, 0.12);
            border-radius: 14px;
            font-size: 15px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .form-row .form-group {
            margin-bottom: 0;
        }
        
        .signup-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(120deg, var(--accent) 0%, #ffbd4a 50%, var(--accent-dark) 100%);
            color: #3c1507;
            border: none;
            border-radius: 16px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            margin-top: 12px;
        }
        
        .signup-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 22px rgba(60, 21, 7, 0.22);
        }
        
        .signup-button:active {
            transform: translateY(0);
            box-shadow: none;
        }
        
        .terms {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 18px;
            line-height: 1.5;
        }
        
        .terms a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .terms a:hover {
            color: var(--accent);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: var(--muted);
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            color: var(--accent);
        }

        .error-message {
            color: #8c1c2f;
            font-weight: 700;
            margin-bottom: 16px;
            padding: 12px;
            background-color: rgba(140, 28, 47, 0.1);
            border-radius: 8px;
        }

        .success-message {
            color: #2d6834;
            font-weight: 700;
            margin-bottom: 16px;
            padding: 12px;
            background-color: rgba(45, 104, 52, 0.1);
            border-radius: 8px;
        }
        
        @media (max-width: 950px) {
            .signup-container {
                flex-direction: column;
                width: 95%;
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

            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .left-panel {
                padding: 36px 24px;
            }
            
            .right-panel {
                padding: 36px 24px;
            }

            .signup-button {
                padding: 14px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- Left Panel with Logo and Welcome Message -->
        <div class="left-panel">
            <div class="logo-container">
                <img src="123.jpg" alt="Monlei SioPao logo" class="brand-logo">
            </div>
            <h2>Join SioPao</h2>
            <p>Create your account to start managing your reseller business and access all the powerful features we offer.</p>
        </div>
        
        <!-- Right Panel with Signup Form -->
        <div class="right-panel">
            <div class="signup-header">
                <h1>Create Account</h1>
                <p>Fill in your details to get started</p>
            </div>
            
            <?php if ($flash): ?>
                <div class="<?php echo $flash['type']==='error' ? 'error-message' : 'success-message'; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <form class="signup-form" method="POST" action="auth/register.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                        </div>
                    </div>
                </div>
                
                <div class="terms">
                    By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. As a reseller, you'll have access to our full suite of tools.
                </div>
                
                <button type="submit" class="signup-button">Create Account</button>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation on client side
        document.querySelector('.signup-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
        });
    </script>
</body>
</html>
