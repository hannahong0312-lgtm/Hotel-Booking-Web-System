<?php
// admin_login.php - Grand Hotel Melaka
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
    <link rel="stylesheet" href="css/admin_login.css">
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