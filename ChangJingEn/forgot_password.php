<?php
// forgot_password.php - Grand Hotel Melaka 
require_once '../Shared/header.php';

if (isset($is_logged_in) && $is_logged_in) redirect('profile.php');

$message = $_SESSION['reset_message'] ?? '';
$type    = $_SESSION['reset_message_type'] ?? '';
$field_error = $_SESSION['reset_field_error'] ?? '';   
$old_email   = $_SESSION['reset_email'] ?? '';
unset($_SESSION['reset_message'], $_SESSION['reset_message_type'], $_SESSION['reset_field_error'], $_SESSION['reset_email']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Grand Hotel</title>
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

        .forgot-section {
            padding: 8rem 0 4rem;
            min-height: calc(100vh - 300px);
            background: #F8F8F8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 20px 35px -10px rgba(0,0,0,0.1), 0 0 0 1px rgba(197,160,89,0.1);
            overflow: hidden;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .forgot-card:hover {
            box-shadow: 0 25px 45px -12px rgba(0,0,0,0.2), 0 0 0 1px rgba(197,160,89,0.3);
            transform: translateY(-2px);
        }

        .forgot-header {
            background: linear-gradient(135deg, #1A1A1A 0%, #2A2520 100%);
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            position: relative;
        }

        .forgot-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 500;
            color: #C5A059;
            margin-bottom: 0.5rem;
        }

        .forgot-header p {
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
        }

        .forgot-body {
            padding: 2.5rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1A1A1A;
            font-size: 0.9rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #C5A059;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-icon input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #E5E5E5;
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            background: #FFFFFF;
            box-sizing: border-box;
        }

        .input-icon input:focus {
            outline: none;
            border-color: #C5A059;
            box-shadow: 0 0 0 3px rgba(197,160,89,0.1);
        }

        .input-icon input:focus + i {
            color: #1A1A1A;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.75rem;
            margin-top: 0.3rem;
            display: block;
        }

        .btn-submit {
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

        .btn-submit:hover {
            background: #B88608;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(197,160,89,0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
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
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 768px) {
            .forgot-section {
                padding: 8rem 1rem 4rem;
            }
            .forgot-body {
                padding: 1.5rem;
            }
            .forgot-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>

<div class="forgot-section">
    <div class="forgot-card">
        <div class="forgot-header">
            <h2>Forgot password?</h2>
            <p>Enter your email to receive a reset link</p>
        </div>

        <div class="forgot-body">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $type; ?>">
                    <i class="fas <?php echo $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="process/process_forgot.php" id="forgotForm" novalidate>
                <div class="input-group">
                    <label>Email address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($old_email); ?>" placeholder="your@email.com" autofocus>
                    </div>
                    <div id="emailFieldError" class="error-message">
                        <?php echo htmlspecialchars($field_error); ?>
                    </div>
                </div>

                <button type="submit" name="submit_forgot" class="btn-submit">Send reset link</button>
            </form>

            <div class="login-link">
                <a href="login.php">Back to Sign In</a>
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('forgotForm');
    const emailInput = form.querySelector('input[name="email"]');
    const fieldErrorDiv = document.getElementById('emailFieldError');
    const originalError = fieldErrorDiv.innerText.trim();

    emailInput.addEventListener('input', function() {
        if (fieldErrorDiv.innerText !== '') {
            fieldErrorDiv.innerText = '';
        }
        emailInput.style.borderColor = '#E5E5E5';
    });

    form.addEventListener('submit', function(e) {
        let error = '';
        const email = emailInput.value.trim();
        if (email === '') {
            error = 'Email address is required.';
        } else if (!/^[^\s@]+@([^\s@]+\.)+[^\s@]+$/.test(email)) {
            error = 'Please enter a valid email address.';
        }
        if (error) {
            e.preventDefault();
            fieldErrorDiv.innerText = error;
            emailInput.style.borderColor = '#e74c3c';
        }
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>