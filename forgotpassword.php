<?php
require_once __DIR__ . '/config.php';
$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SioPao - Forgot Password</title>
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
            margin-bottom: 28px;
        }
        
        .login-header h1 {
            font-size: 34px;
            color: var(--primary);
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: var(--muted);
            font-size: 17px;
            line-height: 1.5;
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
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            opacity: 0.7;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 52px;
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
        
        .submit-button {
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
            margin-top: 8px;
        }
        
        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 22px rgba(60, 21, 7, 0.22);
        }
        
        .submit-button:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .status-message {
            margin-top: 22px;
            font-size: 16px;
            color: var(--primary-dark);
            line-height: 1.6;
        }

        .helper-links {
            margin-top: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        .helper-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .helper-links a:hover {
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

            .helper-links {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .left-panel {
                padding: 36px 24px;
            }
            
            .right-panel {
                padding: 36px 24px;
            }
            
            .submit-button {
                padding: 16px;
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <div class="logo-container">
                <img src="123.jpg" alt="Monlei SioPao logo" class="brand-logo">
            </div>
            <h2>Forgot your password?</h2>
            <p>No worries! Enter the email you use with SioPao and we will send a secure link so you can create a new password in minutes.</p>
        </div>
        <div class="right-panel">
            <div class="login-header">
                <h1>Reset link request</h1>
                <p>We will email you instructions to set a brand-new password. Please check your inbox after submitting the request.</p>
            </div>
            <?php if ($flash): ?>
            <div style="margin-bottom:16px;color:<?php echo $flash['type']==='error' ? '#8c1c2f' : '#2d6834'; ?>;font-weight:700;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>
            <form id="forgotForm" method="POST" action="auth/send_otp.php" novalidate>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                </div>
                <button type="submit" class="submit-button">Send Verification Code</button>
            </form>
            <div id="statusMessage" class="status-message" aria-live="polite"></div>
            <div class="helper-links">
                <span>Remember your password?</span>
                <a href="login.php">Back to login</a>
            </div>
        </div>
    </div>

    <script>
        const forgotForm = document.getElementById('forgotForm');
        const statusMessage = document.getElementById('statusMessage');
        const submitBtn = document.querySelector('.submit-button');

        forgotForm.addEventListener('submit', function(event) {
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();

            if (!email) {
                event.preventDefault();
                statusMessage.textContent = 'Please enter the email associated with your SioPao account.';
                emailInput.focus();
                return;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                event.preventDefault();
                statusMessage.textContent = 'That does not look like a valid email address. Please try again.';
                emailInput.focus();
                return;
            }

            // Allow submit to backend; prevent double clicks
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending code...';
            statusMessage.textContent = '';
        });
    </script>
</body>
</html>
