<?php
// register.php - Customer Registration (仅显示表单)
require_once '../Shared/header.php';

if ($is_logged_in) {
    redirect('profile.php');
}

// 从 session 获取错误信息和上次输入的数据
$errors = $_SESSION['reg_errors'] ?? [];
$old_input = $_SESSION['reg_old'] ?? [];
// 清除 session 中的临时数据
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);

// 默认值
$first_name = $old_input['first_name'] ?? '';
$last_name  = $old_input['last_name'] ?? '';
$email      = $old_input['email'] ?? '';
$phone      = $old_input['phone'] ?? '';
$country    = $old_input['country'] ?? '';
$subscribe  = $old_input['subscribe'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Grand Hotel</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>

<section class="register-section">
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2>Create Your Account</h2>
                <p>Join Grand Hotel for exclusive offers and seamless booking</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form method="POST" action="process/register_process.php" novalidate>
                <div class="form-grid">
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Country/Region -->
                    <div class="full-width">
                        <div class="form-group">
                            <label for="country">Country/Region *</label>
                            <select id="country" name="country" required>
                                <option value="">Select your country</option>
                                <?php
                                $countries = [
                                    'Malaysia', 'Singapore', 'Thailand', 'Indonesia', 'Vietnam', 'Philippines',
                                    'United States', 'United Kingdom', 'Australia', 'China', 'Japan', 'South Korea',
                                    'India', 'Germany', 'France', 'Italy', 'Canada', 'Other'
                                ];
                                foreach ($countries as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo $country === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['country'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['country']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Password Row -->
                <div class="password-row" style="margin-top: 1.5rem;">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password" onclick="togglePassword('password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="strength-meter">
                            <div class="strength-bar" id="strength-bar"></div>
                        </div>
                        <span class="strength-text" id="strength-text"></span>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <span class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Terms & Subscribe -->
                <div class="full-width" style="margin-top: 1.5rem;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" <?php echo isset($old_input['terms']) ? 'checked' : ''; ?>>
                        <label for="terms">I agree to the <a href="#" style="color: var(--gold);">Terms & Conditions</a> and <a href="#" style="color: var(--accent);">Privacy Policy</a> *</label>
                    </div>
                    <?php if (isset($errors['terms'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['terms']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="full-width">
                    <div class="checkbox-group">
                        <input type="checkbox" id="subscribe" name="subscribe" value="1" <?php echo $subscribe ? 'checked' : ''; ?>>
                        <label for="subscribe">I would like to receive exclusive offers and travel inspiration via email.</label>
                    </div>
                </div>

                <button type="submit" class="btn-register-submit">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
</section>

<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password strength meter
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;

        let strengthLevel = '';
        let strengthClass = '';
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthText.innerHTML = '';
            return;
        }
        if (strength <= 2) {
            strengthLevel = 'Weak';
            strengthClass = 'strength-weak';
        } else if (strength === 3) {
            strengthLevel = 'Medium';
            strengthClass = 'strength-medium';
        } else if (strength === 4) {
            strengthLevel = 'Strong';
            strengthClass = 'strength-strong';
        } else {
            strengthLevel = 'Very Strong';
            strengthClass = 'strength-very-strong';
        }
        strengthBar.className = 'strength-bar ' + strengthClass;
        strengthText.innerHTML = `Password strength: ${strengthLevel}`;
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>