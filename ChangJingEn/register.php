<?php
// register.php - Customer Registration (Full-width Country/Region)
require_once '../Shared/header.php';

// Redirect if already logged in (any role)
if ($is_logged_in) {
    redirect('profile.php');
}

$first_name = $last_name = $email = $phone = $country = $password = $confirm_password = '';
$subscribe = 0;
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $first_name = cleanInput($_POST['first_name'] ?? '');
    $last_name  = cleanInput($_POST['last_name'] ?? '');
    $email      = cleanInput($_POST['email'] ?? '');
    $phone      = cleanInput($_POST['phone'] ?? '');
    $country    = cleanInput($_POST['country'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $subscribe  = isset($_POST['subscribe']) ? 1 : 0;
    $terms      = isset($_POST['terms']);

    // Validation
    if (empty($first_name)) {
        $errors['first_name'] = 'First name is required.';
    } elseif (strlen($first_name) < 2) {
        $errors['first_name'] = 'First name must be at least 2 characters.';
    }

    if (empty($last_name)) {
        $errors['last_name'] = 'Last name is required.';
    } elseif (strlen($last_name) < 2) {
        $errors['last_name'] = 'Last name must be at least 2 characters.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'This email is already registered.';
        }
        $stmt->close();
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if (empty($country)) {
        $errors['country'] = 'Please select your country/region.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$terms) {
        $errors['terms'] = 'You must agree to the Terms & Conditions.';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        $status = 'inactive'; // Requires admin activation
        $created_at = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, country, password, role, status, subscribe, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssis", $first_name, $last_name, $email, $phone, $country, $hashed_password, $role, $status, $subscribe, $created_at);

        if ($stmt->execute()) {
            $success = true;
            // Clear form data
            $first_name = $last_name = $email = $phone = $country = $password = $confirm_password = '';
            $subscribe = 0;
        } else {
            $errors['general'] = 'Registration failed. Please try again later.';
        }
        $stmt->close();
    }
}

// Country list
$countries = [
    'Malaysia', 'Singapore', 'Thailand', 'Indonesia', 'Vietnam', 'Philippines',
    'United States', 'United Kingdom', 'Australia', 'China', 'Japan', 'South Korea',
    'India', 'Germany', 'France', 'Italy', 'Canada', 'Other'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Grand Hotel</title>
    <style>
        /* Additional styles for register page */
        .register-section {
            padding: 8rem 0 4rem;
            min-height: calc(100vh - 300px);
            background: var(--gray-bg);
        }
        .register-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            padding: 2.5rem;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 500;
            color: var(--black);
            margin-bottom: 0.5rem;
        }
        .register-header p {
            color: var(--gray-text);
            font-size: 0.9rem;
        }
        /* Two-column layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .full-width {
            grid-column: span 2;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--black);
            font-size: 0.9rem;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--gray-border);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--white);
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }
        /* Password wrapper with toggle */
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray-text);
            font-size: 1rem;
        }
        .toggle-password:hover {
            color: var(--accent);
        }
        /* Password strength meter */
        .strength-meter {
            margin-top: 0.5rem;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-bar {
            width: 0%;
            height: 100%;
            transition: width 0.3s ease;
        }
        .strength-text {
            font-size: 0.7rem;
            margin-top: 0.25rem;
            display: block;
        }
        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-medium { background: #f39c12; width: 50%; }
        .strength-strong { background: #2ecc71; width: 75%; }
        .strength-very-strong { background: #27ae60; width: 100%; }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .checkbox-group input {
            width: auto;
            margin: 0;
        }
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            font-size: 0.85rem;
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 0.3rem;
        }
        .btn-register-submit {
            width: 100%;
            background: var(--accent);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }
        .btn-register-submit:hover {
            background: var(--accent-dark);
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--gray-text);
        }
        .login-link a {
            color: var(--accent);
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        /* Password row: two columns inside full-width */
        .password-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .form-grid, .password-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .full-width {
                grid-column: span 1;
            }
            .register-container {
                padding: 1.5rem;
            }
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

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Registration successful! Your account is pending admin approval. You will be notified once activated.
                </div>
                <div class="login-link">
                    <a href="login.php">Go to Sign In →</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <div class="form-grid">
                        <!-- First Name -->
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="error-message"><?php echo $errors['first_name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Last Name -->
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="error-message"><?php echo $errors['last_name']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="error-message"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="error-message"><?php echo $errors['phone']; ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Country/Region (full width) -->
                        <div class="full-width">
                            <div class="form-group">
                                <label for="country">Country/Region *</label>
                                <select id="country" name="country" required>
                                    <option value="">Select your country</option>
                                    <?php foreach ($countries as $c): ?>
                                        <option value="<?php echo $c; ?>" <?php echo $country === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['country'])): ?>
                                    <div class="error-message"><?php echo $errors['country']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Password Row (side by side) -->
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
                                <div class="error-message"><?php echo $errors['password']; ?></div>
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
                                <div class="error-message"><?php echo $errors['confirm_password']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Terms & Subscribe -->
                    <div class="full-width" style="margin-top: 1.5rem;">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                            <label for="terms">I agree to the <a href="#" style="color: var(--accent);">Terms & Conditions</a> and <a href="#" style="color: var(--accent);">Privacy Policy</a> *</label>
                        </div>
                        <?php if (isset($errors['terms'])): ?>
                            <div class="error-message"><?php echo $errors['terms']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="full-width">
                        <div class="checkbox-group">
                            <input type="checkbox" id="subscribe" name="subscribe" <?php echo $subscribe ? 'checked' : ''; ?>>
                            <label for="subscribe">I would like to receive exclusive offers and travel inspiration via email.</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-register-submit">Create Account</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
            <?php endif; ?>
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