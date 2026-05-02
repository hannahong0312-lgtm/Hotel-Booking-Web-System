<?php
// reset_password.php - Grand Hotel Melaka
require_once '../Shared/header.php';

if (isset($is_logged_in) && $is_logged_in) redirect('profile.php');

$token                = $_GET['token'] ?? '';
$email                = $_GET['email'] ?? '';
$valid                = false;
$error                = '';
$same_password_error  = '';

if (empty($token) || empty($email)) {
    $error = 'Invalid reset link. Please request a new one.';
    unset($_SESSION['reset_message'], $_SESSION['reset_message_type']);
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND reset_token = ? AND reset_expires > UTC_TIMESTAMP()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 1) {
        $valid = true;
        unset($_SESSION['reset_message'], $_SESSION['reset_message_type']);
    } else {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
        $clean = $conn->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE LOWER(email) = LOWER(?)");
        $clean->bind_param("s", $email);
        $clean->execute();
        $clean->close();
        unset($_SESSION['reset_message'], $_SESSION['reset_message_type']);
    }
    $stmt->close();
}

$message      = $_SESSION['reset_message'] ?? '';
$message_type = $_SESSION['reset_message_type'] ?? '';
unset($_SESSION['reset_message'], $_SESSION['reset_message_type']);

if ($message_type === 'error' && stripos($message, 'cannot be the same') !== false) {
    $same_password_error = $message;
    $message = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Grand Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header {
            background: rgba(26, 26, 26, 0.95);
            padding: 0.8rem 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #F5F2EB 0%, #FFFFFF 100%);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .reset-section {
            padding: 8rem 0 4rem;
            min-height: calc(100vh - 300px);
            background: #F8F8F8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-card {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .reset-header {
            background: #1A1A1A;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }

        .reset-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 500;
            color: #C5A059;
            margin-bottom: 0.5rem;
        }

        .reset-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .reset-body {
            padding: 2.5rem;
        }

        .email-badge {
            background: #F9F7F3;
            padding: 0.8rem 1rem;
            border-radius: 60px;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: #1A1A1A;
            border: 1px solid #EAE3D9;
        }

        .email-badge i {
            color: #C5A059;
            margin-right: 8px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1A1A1A;
            font-size: 0.9rem;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            width: 100%;
            padding: 0.8rem 2.5rem 0.8rem 1rem;
            border: 1px solid #E5E5E5;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            background: #FFFFFF;
            box-sizing: border-box;
        }

        .password-wrapper input:focus {
            outline: none;
            border-color: #C5A059;
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1rem;
        }

        .toggle-password:hover {
            color: #C5A059;
        }

        .password-rules {
            margin-top: 12px;
        }

        .rules-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
        }

        .rule-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            line-height: 1.4;
            color: #333;
        }

        .rule-icon {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            display: inline-block;
        }

        .rule-item.valid {
            color: #0d9488;
        }

        .rule-item.invalid {
            color: #e74c3c;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 0.3rem;
            display: block;
        }

        .confirm-match-status {
            margin-top: 6px;
            font-size: 0.8rem;
        }

        .match-success {
            color: #0d9488;
        }

        .match-error {
            color: #e74c3c;
        }

        .btn-reset {
            width: 100%;
            background: #C5A059;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 0.5rem;
        }

        .btn-reset:hover {
            background: #B88608;
        }

        .login-link {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.9rem;
            color: #666666;
        }

        .login-link a {
            color: #C5A059;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #1A1A1A;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .reset-section {
                padding: 8rem 1rem 4rem;
            }

            .reset-body {
                padding: 1.5rem;
            }

            .rules-list {
                grid-template-columns: 1fr;
            }

            .reset-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<div class="reset-section">
    <div class="reset-card">
        <div class="reset-header">
            <h2>Set new password</h2>
            <p>Create a strong new password for your account</p>
        </div>

        <div class="reset-body">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="login-link">
                    <a href="forgot_password.php">Request new reset link →</a>
                </div>
            <?php elseif ($valid): ?>
                <div class="email-badge">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="process/process_reset.php" id="resetForm" novalidate>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                    <div class="form-group">
                        <label>New password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" autocomplete="off">
                            <span class="toggle-password" onclick="togglePassword('password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div id="passwordError" class="error-message" style="display: none;"></div>

                        <?php if ($same_password_error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($same_password_error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="password-rules">
                            <ul class="rules-list">
                                <li id="req-length" class="rule-item"><span class="rule-icon">•</span> 8-16 characters</li>
                                <li id="req-lower" class="rule-item"><span class="rule-icon">•</span> One lowercase letter</li>
                                <li id="req-upper" class="rule-item"><span class="rule-icon">•</span> One uppercase letter</li>
                                <li id="req-number" class="rule-item"><span class="rule-icon">•</span> One number or special character</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" autocomplete="off">
                            <span class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="confirm-match-status" id="passwordMatch"></div>
                    </div>

                    <button type="submit" name="submit_reset" class="btn-reset">Reset password</button>
                </form>

                <div class="login-link">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    const form = document.getElementById('resetForm');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const passwordError = document.getElementById('passwordError');
    const matchStatus = document.getElementById('passwordMatch');

    const reqLength = document.getElementById('req-length');
    const reqLower = document.getElementById('req-lower');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');

    function updateRule(ruleElement, condition, hasValue) {
        if (!hasValue) {
            ruleElement.classList.remove('valid', 'invalid');
            const iconSpan = ruleElement.querySelector('.rule-icon');
            if (iconSpan) iconSpan.innerHTML = '•';
            ruleElement.style.color = '#333';
            return;
        }
        if (condition) {
            ruleElement.classList.add('valid');
            ruleElement.classList.remove('invalid');
            ruleElement.style.color = '#0d9488';
            const iconSpan = ruleElement.querySelector('.rule-icon');
            if (iconSpan) iconSpan.innerHTML = '✓';
        } else {
            ruleElement.classList.add('invalid');
            ruleElement.classList.remove('valid');
            ruleElement.style.color = '#e74c3c';
            const iconSpan = ruleElement.querySelector('.rule-icon');
            if (iconSpan) iconSpan.innerHTML = '✗';
        }
    }

    function validatePassword() {
        const val = passwordInput.value;
        const hasValue = val.length > 0;
        updateRule(reqLength, val.length >= 8 && val.length <= 16, hasValue);
        updateRule(reqLower, /[a-z]/.test(val), hasValue);
        updateRule(reqUpper, /[A-Z]/.test(val), hasValue);
        updateRule(reqNumber, /[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val), hasValue);
        validateConfirm();
    }

    function validateConfirm() {
        const pwd = passwordInput.value;
        const conf = confirmInput.value;
        if (conf === '') {
            matchStatus.innerHTML = '';
        } else if (pwd === conf) {
            matchStatus.innerHTML = '<span class="match-success"><i class="fas fa-check-circle"></i> Passwords match</span>';
        } else {
            matchStatus.innerHTML = '<span class="match-error"><i class="fas fa-times-circle"></i> Passwords do not match</span>';
        }
    }

    passwordInput.addEventListener('input', function() {
        if (passwordError.style.display !== 'none') {
            passwordError.style.display = 'none';
            passwordError.innerHTML = '';
        }
        validatePassword();
    });

    confirmInput.addEventListener('input', validateConfirm);

    form.addEventListener('submit', function(e) {
        let errorMsg = '';
        const pwd = passwordInput.value;
        const conf = confirmInput.value;

        if (pwd === '') {
            errorMsg = 'New password is required.';
        } else if (pwd.length < 8 || pwd.length > 16) {
            errorMsg = 'Password must be 8–16 characters.';
        } else if (!/[A-Z]/.test(pwd)) {
            errorMsg = 'Password must contain at least one uppercase letter.';
        } else if (!/[a-z]/.test(pwd)) {
            errorMsg = 'Password must contain at least one lowercase letter.';
        } else if (!/[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd)) {
            errorMsg = 'Password must contain at least one number or special character.';
        } else if (pwd !== conf) {
            errorMsg = 'Passwords do not match.';
        }

        if (errorMsg) {
            e.preventDefault();
            passwordError.innerText = errorMsg;
            passwordError.style.display = 'block';
            passwordInput.focus();
        }
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>