<?php
// login.php - Customer Login Page (with centered form to match register field width)
require_once '../Shared/header.php';

// If already logged in as customer, redirect to profile
if ($is_logged_in && $user_role === 'customer') {
    redirect('profile.php');
}
// If logged in as admin/superadmin, redirect to homepage (or dashboard later)
if ($is_logged_in && in_array($user_role, ['admin', 'superadmin'])) {
    redirect('../ChangJingEn/homepage.php');
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                if ($row['role'] !== 'customer') {
                    $errors['general'] = 'This account is not a customer account.';
                } elseif ($row['status'] !== 'active') {
                    $errors['general'] = 'Your account is not activated yet. Please contact admin.';
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_email'] = $row['email'];
                    $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
                    $_SESSION['user_role'] = $row['role'];

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (86400 * 30);
                        setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        $stmt2 = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $stmt2->bind_param("si", $token, $row['id']);
                        $stmt2->execute();
                        $stmt2->close();
                    }

                    redirect('profile.php');
                }
            } else {
                $errors['general'] = 'Invalid email or password.';
            }
        } else {
            $errors['general'] = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Grand Hotel</title>
    <style>
        .header {
            background: rgba(26, 26, 26, 0.95);
            padding: 0.8rem 0;
        }
        .login-section {
            padding: 8rem 0 4rem;
            min-height: calc(100vh - 300px);
            background: var(--gray-bg);
        }
        .login-container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: var(--white);
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            padding: 2.5rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 500;
            color: var(--black);
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--gray-text);
            font-size: 0.9rem;
        }
        /* Centered form to match register field width */
        .login-form-wrapper {
            max-width: 100%;  /* Matches width of a single register field */
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--black);
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--gray-border);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
            background: var(--white);
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .checkbox-group .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-group .remember label {
            font-weight: normal;
            font-size: 0.85rem;
            color: var(--gray-text);
            margin: 0;
        }
        .checkbox-group .forgot-password {
            font-size: 0.85rem;
            color: var(--accent);
            text-decoration: none;
        }
        .checkbox-group .forgot-password:hover {
            text-decoration: underline;
        }
        .btn-login-submit {
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
            margin-bottom: 1rem;
        }
        .btn-login-submit:hover {
            background: var(--accent-dark);
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--gray-text);
        }
        .register-link a {
            color: var(--accent);
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 0.3rem;
        }
    </style>
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
                <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
            <?php endif; ?>

            <div class="login-form-wrapper">
                <form method="POST" action="" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error-message"><?php echo $errors['password']; ?></div>
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