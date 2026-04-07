<?php
// register.php - Customer Registration
require_once '../Shared/header.php';

if (isset($is_logged_in) && $is_logged_in) {
    redirect('profile.php');
}

$errors    = $_SESSION['reg_errors'] ?? [];
$old_input = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);

$first_name = $old_input['first_name'] ?? '';
$last_name  = $old_input['last_name']  ?? '';
$email      = $old_input['email']      ?? '';
$phone      = $old_input['phone']      ?? '';
$country    = $old_input['country']    ?? '';
$subscribe  = $old_input['subscribe']  ?? 0;

function getCountryList() {
    $jsonFile = __DIR__ . '/countries.json';
    if (file_exists($jsonFile)) {
        $json = file_get_contents($jsonFile);
        $data = json_decode($json, true);
        if (is_array($data) && !empty($data)) {
            $countries = [];
            foreach ($data as $item) {
                if (is_string($item)) $countries[] = $item;
                elseif (isset($item['name']['common'])) $countries[] = $item['name']['common'];
                elseif (isset($item['common'])) $countries[] = $item['common'];
            }
            if (!empty($countries)) { sort($countries); return $countries; }
        }
    }
    return [
        'Malaysia', 'Singapore', 'Thailand', 'Indonesia', 'Vietnam', 'Philippines',
        'United States', 'United Kingdom', 'Australia', 'China', 'Japan', 'South Korea',
        'India', 'Germany', 'France', 'Italy', 'Canada', 'Other'
    ];
}
$countries = getCountryList();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Grand Hotel</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 两列密码规则布局 */
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
            color: #333; /* 默认黑色 */
        }
        .rule-icon {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            display: inline-block;
        }
        .rule-item.valid {
            color: #0d9488; /* 满足规则：青色/绿色 */
        }
        .rule-item.invalid {
            color: #e74c3c; /* 不满足规则：红色 */
        }
        .confirm-match-status {
            margin-top: 6px;
            font-size: 0.8rem;
        }
        .match-success { color: #0d9488; }
        .match-error { color: #e74c3c; }
        .confirm-password-group { margin-top: 1.5rem; }
        .country-group { margin-bottom: 2rem; }
        @media (max-width: 768px) {
            .rules-list {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            .confirm-password-group { margin-top: 1.2rem; }
            .country-group { margin-bottom: 1.5rem; }
        }
    </style>
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
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="full-width country-group">
                        <div class="form-group">
                            <label for="country">Country/Region *</label>
                            <select id="country" name="country" required>
                                <option value="">Select your country</option>
                                <?php foreach ($countries as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $country === $c ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['country'])): ?>
                                <div class="error-message"><?php echo htmlspecialchars($errors['country']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 密码区域 -->
                <div class="password-section">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password" onclick="togglePassword('password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="password-rules">
                            <ul class="rules-list">
                                <li id="req-length" class="rule-item">
                                    <span class="rule-icon">•</span>
                                    <span class="rule-text">Must be between 8 and 32 characters</span>
                                </li>
                                <li id="req-lower" class="rule-item">
                                    <span class="rule-icon">•</span>
                                    <span class="rule-text">Contain one lowercase letter</span>
                                </li>
                                <li id="req-upper" class="rule-item">
                                    <span class="rule-icon">•</span>
                                    <span class="rule-text">Contain one uppercase letter</span>
                                </li>
                                <li id="req-number" class="rule-item">
                                    <span class="rule-icon">•</span>
                                    <span class="rule-text">One number (0-9) or one special character</span>
                                </li>
                            </ul>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group confirm-password-group">
                        <label for="confirm_password">Confirm password *</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <span class="toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                        <div class="confirm-match-status" id="passwordMatch"></div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="full-width" style="margin-top: 1.5rem;">
                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" <?php echo isset($old_input['terms']) ? 'checked' : ''; ?>>
                        <label for="terms">I agree to the <a href="#" style="color: var(--gold);">Terms & Conditions</a> and <a href="#" style="color: var(--accent);">Privacy Policy</a> *</label>
                    </div>
                    <?php if (isset($errors['terms'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['terms']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="full-width" style="margin-top: 0.5rem;">
                    <div class="checkbox-group">
                    <input type="checkbox" id="subscribe" name="subscribe" value="1" checked>
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

    const passwordInput = document.getElementById('password');
    const reqLength = document.getElementById('req-length');
    const reqLower = document.getElementById('req-lower');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');

    function setIcon(ruleElement, state) {
        const iconSpan = ruleElement.querySelector('.rule-icon');
        if (state === 'valid') {
            iconSpan.innerHTML = '✓';
        } else if (state === 'invalid') {
            iconSpan.innerHTML = '✗';
        } else {
            iconSpan.innerHTML = '•';
        }
    }

    function updateRule(rule, condition, hasValue) {
        if (!hasValue) {
            rule.classList.remove('valid', 'invalid');
            setIcon(rule, 'default');
            rule.style.color = '#333';
        } else if (condition) {
            rule.classList.add('valid');
            rule.classList.remove('invalid');
            setIcon(rule, 'valid');
            rule.style.color = '#0d9488';
        } else {
            rule.classList.remove('valid');
            rule.classList.add('invalid');
            setIcon(rule, 'invalid');
            rule.style.color = '#e74c3c';
        }
    }

    function validatePassword() {
        const val = passwordInput.value;
        const hasValue = val.length > 0;

        updateRule(reqLength, val.length >= 8 && val.length <= 32, hasValue);
        updateRule(reqLower, /[a-z]/.test(val), hasValue);
        updateRule(reqUpper, /[A-Z]/.test(val), hasValue);
        updateRule(reqNumber, /[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val), hasValue);
    }

    passwordInput.addEventListener('input', validatePassword);

    // 确认密码匹配
    const confirmInput = document.getElementById('confirm_password');
    const matchStatus = document.getElementById('passwordMatch');

    function validateConfirm() {
        const pwd = passwordInput.value;
        const confirm = confirmInput.value;
        if (confirm === '') {
            matchStatus.innerHTML = '';
        } else if (pwd === confirm) {
            matchStatus.innerHTML = '<span class="match-success">✓ Passwords match</span>';
        } else {
            matchStatus.innerHTML = '<span class="match-error">✗ Passwords do not match</span>';
        }
    }

    passwordInput.addEventListener('input', validateConfirm);
    confirmInput.addEventListener('input', validateConfirm);

    // 初始化
    validatePassword();
    validateConfirm();
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>