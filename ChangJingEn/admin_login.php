<?php
// admin_login.php - Admin Login (Grand Hotel Management)
session_start();

if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = $_SESSION['admin_login_error'] ?? '';
$old_email = $_SESSION['admin_login_email'] ?? '';
unset($_SESSION['admin_login_error'], $_SESSION['admin_login_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Grand Hotel Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #FFFFFF;
            background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.02) 0px, rgba(0,0,0,0.02) 2px, transparent 2px, transparent 8px);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            max-width: 460px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            border: 1px solid #EAE6E0;
            overflow: hidden;
        }
        .login-header {
            background: #0A0D14;
            padding: 32px 32px 16px;
            text-align: center;
            border-bottom: 1px solid #F0EBE3;
        }
        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 500;
            color: #FFFFFF;
            margin-bottom: 8px;
        }
        .login-header p {
            font-size: 0.85rem;
            color: #FFFFFF;
        }
        .login-body {
            padding: 32px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #1A1A1A;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 1px solid #E0DCD6;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            background: #FFFFFF;
            transition: all 0.2s;
        }
        .input-wrapper input:focus {
            outline: none;
            border-color: #D4AF37;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
        }
        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #8B7A66;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .login-btn {
            width: 100%;
            background: #D4AF37;
            color: #1A1A1A;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .login-btn:hover {
            background: #C5A059;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(212,175,55,0.2);
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 6px;
            display: none;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none;
        }
        @media (max-width: 500px) {
            .login-card { max-width: 95%; }
            .login-body { padding: 24px; }
            .login-header { padding: 24px 24px 12px; }
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <h1>Welcome Admin</h1>
        <p>Grand Hotel Management</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="process/admin_login_process.php" method="POST" id="adminLoginForm" novalidate>
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($old_email); ?>" placeholder="admin@example.com" autofocus>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                <div id="emailError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter your password">
                    <button type="button" class="input-icon" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div id="passwordError" class="error-message"></div>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
    </div>
</div>

<script>
    const form = document.getElementById('adminLoginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    // 邮箱验证正则（更严格）
    function isValidEmail(email) {
        return /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/.test(email);
    }

    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // 邮箱验证
        const email = emailInput.value.trim();
        if (email === '') {
            emailError.textContent = 'Email address is required.';
            emailError.style.display = 'block';
            isValid = false;
        } else if (!isValidEmail(email)) {
            emailError.textContent = 'Please enter a valid email address.';
            emailError.style.display = 'block';
            isValid = false;
        } else {
            emailError.style.display = 'none';
        }
        
        // 密码验证
        if (passwordInput.value.trim() === '') {
            passwordError.textContent = 'Password is required.';
            passwordError.style.display = 'block';
            isValid = false;
        } else {
            passwordError.style.display = 'none';
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // 输入时隐藏错误
    emailInput.addEventListener('input', function() { emailError.style.display = 'none'; });
    passwordInput.addEventListener('input', function() { passwordError.style.display = 'none'; });
    
    // 密码显示切换
    const toggleBtn = document.getElementById('togglePassword');
    toggleBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>