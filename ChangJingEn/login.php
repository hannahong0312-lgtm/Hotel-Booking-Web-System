<?php
// login.php - Customer Login Page (仅显示表单，CSS 已分离)
require_once '../Shared/header.php';

if ($is_logged_in) {
    redirect('profile.php');
}

// 从 session 中取出可能存在的错误信息和上次输入的邮箱
$errors = $_SESSION['login_errors'] ?? [];
$old_email = $_SESSION['login_email'] ?? '';
// 清除 session 中的临时数据，避免刷新页面重复显示
unset($_SESSION['login_errors'], $_SESSION['login_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Grand Hotel</title>
    <!-- 引入外部 login.css (与 login.php 同级目录下的 css 文件夹) -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<section class="login-section">
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your Grand Hotel account</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <div class="login-form-wrapper">
                <form method="POST" action="process/login_process.php" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old_email); ?>" required autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="checkbox-group">
                        <div class="remember">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-login-submit">Sign In</button>
                </form>

                <div class="register-link">
                    Don't have an account? <a href="register.php">Create one</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../Shared/footer.php'; ?>
</body>
</html>