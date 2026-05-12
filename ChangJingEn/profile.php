<?php
require_once '../Shared/header.php';

if (!$is_logged_in || $user_role !== 'customer') {
    redirect('homepage.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';
$active_tab = 'account'; 

// Fetch user info
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, country, points, created_at, 
                               birthday, language, subscribe FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) redirect('logout.php');

// 默认值
if (!isset($user['birthday'])) $user['birthday'] = '';
if (!isset($user['language'])) $user['language'] = 'en';
if (!isset($user['subscribe'])) $user['subscribe'] = 1;
$user['points'] = $user['points'] ?? 0;

// Total bookings and points earned
$total_bookings = 0;
$total_points_earned = 0;
try {
    // 房间预订数量
    $room_query = "SELECT COUNT(*) as count FROM book WHERE user_id = ? AND status != 'cancelled'";
    $stmt = $conn->prepare($room_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_bookings = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 餐饮预订数量
    $dining_query = "SELECT COUNT(*) as count FROM dining WHERE email = ? AND status != 'cancelled'";
    $stmt = $conn->prepare($dining_query);
    $stmt->bind_param("s", $user['email']);
    $stmt->execute();
    $total_bookings += $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    $points_earned_query = "SELECT COALESCE(SUM(points_earned), 0) as total_earned 
                            FROM payment WHERE user_id = ?";
    $stmt = $conn->prepare($points_earned_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_points_earned = $stmt->get_result()->fetch_assoc()['total_earned'];
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log("Profile stats error: " . $e->getMessage());
}

$join_date = date('F Y', strtotime($user['created_at'] ?? 'now'));

// Country list for dropdown
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
            if (!empty($countries)) {
                sort($countries);
                return $countries;
            }
        }
    }
    // 回退硬编码
    return [
        'Malaysia', 'Singapore', 'Thailand', 'Indonesia', 'Vietnam', 'Philippines',
        'United States', 'United Kingdom', 'Australia', 'China', 'Japan', 'South Korea',
        'India', 'Germany', 'France', 'Italy', 'Canada', 'Other'
    ];
}
$countries = getCountryList();

// Language options
$languages = [
    'en' => 'English',
    'zh' => '中文',
    'ms' => 'Bahasa Malaysia',
    'ja' => '日本語'
];

// Update Profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = cleanInput($_POST['first_name'] ?? '');
    $last_name  = cleanInput($_POST['last_name'] ?? '');
    $phone      = cleanInput($_POST['phone'] ?? '');
    $country    = cleanInput($_POST['country'] ?? '');
    $birthday   = cleanInput($_POST['birthday'] ?? '');
    $language   = cleanInput($_POST['language'] ?? 'en');
    $subscribe  = isset($_POST['subscribe']) ? 1 : 0;

    if (empty($first_name)) $errors['first_name'] = 'Required';
    elseif (strlen($first_name) < 2) $errors['first_name'] = 'Min 2 characters';
    if (empty($last_name)) $errors['last_name'] = 'Required';
    elseif (strlen($last_name) < 2) $errors['last_name'] = 'Min 2 characters';
    if (empty($phone)) $errors['phone'] = 'Required';
    elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) $errors['phone'] = 'Valid phone number';
    if (empty($country)) $errors['country'] = 'Select country';

    // ========== 改进后的生日验证 ==========
    if (!empty($birthday)) {
        // 基本格式检查
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            $errors['birthday'] = 'Invalid date format.';
        } else {
            $parts = explode('-', $birthday);
            // 使用 checkdate 验证真实日期
            if (!checkdate($parts[1], $parts[2], $parts[0])) {
                $errors['birthday'] = 'Please enter a valid date.';
            } elseif (strtotime($birthday) > strtotime('today')) {
                $errors['birthday'] = 'Birthday cannot be in the future.';
            }
        }
    }

    if (empty($errors)) {
        $updateStmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, country=?, birthday=?, language=?, subscribe=?, updated_at=NOW() WHERE id=?");
        $updateStmt->bind_param("ssssssii", $first_name, $last_name, $phone, $country, $birthday, $language, $subscribe, $user_id);
        if ($updateStmt->execute()) {
            $_SESSION['user_name'] = "$first_name $last_name";
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['phone'] = $phone;
            $user['country'] = $country;
            $user['birthday'] = $birthday;
            $user['language'] = $language;
            $user['subscribe'] = $subscribe;
            $success = 'Profile updated successfully.';
        } else $errors['general'] = 'Update failed.';
        $updateStmt->close();
    }
    $active_tab = 'account';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $active_tab = 'security';
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $pwdStmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $pwdStmt->bind_param("i", $user_id);
    $pwdStmt->execute();
    $pwdResult = $pwdStmt->get_result()->fetch_assoc();
    $pwdStmt->close();

    if (!$pwdResult || !password_verify($current, $pwdResult['password'])) {
        $errors['current_password'] = 'Current password is incorrect.';
    } 
    elseif (password_verify($new, $pwdResult['password'])) {
        $errors['new_password'] = 'New password cannot be the same as current password.';
    }
    elseif (strlen($new) < 8) {
        $errors['new_password'] = 'Password must be at least 8 characters.';
    }
    elseif (strlen($new) > 16) {
        $errors['new_password'] = 'Password must not exceed 16 characters.';
    }
    elseif (!preg_match('/[A-Z]/', $new)) {
        $errors['new_password'] = 'Password must contain at least one uppercase letter.';
    }
    elseif (!preg_match('/[a-z]/', $new)) {
        $errors['new_password'] = 'Password must contain at least one lowercase letter.';
    }
    elseif (!preg_match('/[0-9!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $new)) {
        $errors['new_password'] = 'Password must contain at least one number or special character.';
    }
    elseif ($new !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE id=?");
        $update->bind_param("si", $hashed, $user_id);
        if ($update->execute()) {
            $success = 'Password changed successfully. You will be redirected to the login page in 3 seconds.';
            session_destroy();
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 3000);
                  </script>';
        } else {
            $errors['general'] = 'Failed to change password.';
        }
        $update->close();
    }
}

$all_bookings = [];
try {
    $room_query = "SELECT 'Room' as type, r.name as name, 
                          b.check_in as start_date, b.check_out as end_date, b.guests, 
                          b.status, p.points_earned, b.created_at
                   FROM book b
                   JOIN rooms r ON b.room_id = r.id
                   LEFT JOIN payment p ON b.payment_id = p.id
                   WHERE b.user_id = ? AND b.status != 'cancelled'
                   ORDER BY b.created_at DESC LIMIT 20";
    $stmt = $conn->prepare($room_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $room_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $dining_query = "SELECT 'Dining' as type, name, date as start_date, 
                            NULL as end_date, guests, status, NULL as points_earned, created_at
                     FROM dining WHERE email = ? AND status != 'cancelled'
                     ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($dining_query);
    $stmt->bind_param("s", $user['email']);
    $stmt->execute();
    $dining_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $all_bookings = array_merge($room_bookings, $dining_bookings);
    usort($all_bookings, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
} catch (mysqli_sql_exception $e) {
    error_log("Booking fetch error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Grand Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Review Popup CSS -->
    <style>
        .header {
            background: rgba(26, 26, 26, 0.95);
            padding: 0.8rem 0;
        }
        
        .profile-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 120px 24px 80px;
        }
        .profile-container .hero-section {
            background: #FFFFFF;
            border-radius: 32px;
            padding: 32px 36px;
            margin-bottom: 40px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.03);
            border: 1px solid #EAE6E0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
        }
        .profile-container .hero-text h1 {
            font-size: 2rem;
            font-weight: 500;
            font-family: 'Playfair Display', serif;
            color: #1A1A1A;
            margin-bottom: 8px;
        }
        .profile-container .hero-text p {
            color: #6B6B6B;
            font-size: 0.85rem;
        }
        .profile-container .stats-group {
            display: flex;
            gap: 32px;
        }
        .profile-container .stat-card {
            text-align: center;
            min-width: 100px;
        }
        .profile-container .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #D4AF37;
            line-height: 1.2;
        }
        .profile-container .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8B7A66;
            margin-top: 4px;
        }
        .profile-container .tabs {
            display: flex;
            gap: 32px;
            border-bottom: 1px solid #EAE6E0;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .profile-container .tab-btn {
            padding: 12px 0;
            font-size: 1rem;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
            color: #8B7A66;
            transition: 0.2s;
            border-bottom: 2px solid transparent;
        }
        .profile-container .tab-btn i {
            margin-right: 8px;
        }
        .profile-container .tab-btn.active {
            color: #D4AF37;
            border-bottom-color: #D4AF37;
        }
        .profile-container .tab-content {
            display: none;
            animation: fadeIn 0.2s ease;
        }
        .profile-container .tab-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .profile-container .form-card {
            background: #FFFFFF;
            border-radius: 28px;
            padding: 28px;
            border: 1px solid #EAE6E0;
            transition: box-shadow 0.2s;
        }
        .profile-container .form-card:hover {
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.05);
        }
        .profile-container .form-group {
            margin-bottom: 24px;
        }
        .profile-container .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8B7A66;
            margin-bottom: 6px;
        }
        .profile-container .form-group input, 
        .profile-container .form-group select {
            width: 100%;
            padding: 10px 0;
            border: none;
            border-bottom: 1px solid #E0DCD6;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            background: transparent;
            transition: border-color 0.2s;
        }
        .profile-container .form-group input:focus, 
        .profile-container .form-group select:focus {
            outline: none;
            border-bottom-color: #D4AF37;
        }
        .profile-container .readonly-email {
            border-bottom-color: #E0DCD6;
            color: #9E9E9E;
            cursor: default;
        }
        .profile-container .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0;
        }
        .profile-container .btn-primary {
            background: #D4AF37;
            color: #1A1A1A;
            border: none;
            padding: 10px 28px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .profile-container .btn-primary:hover {
            background: #C5A059;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(212,175,55,0.25);
        }
        .profile-container .error-message {
            color: #D97706;
            font-size: 0.7rem;
            margin-top: 6px;
        }
        .profile-container .success-message, 
        .profile-container .alert-danger {
            padding: 12px 20px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        .profile-container .success-message {
            background: #E8F0E7;
            color: #2D6A4F;
        }
        .profile-container .alert-danger {
            background: #FCE9E6;
            color: #B23C1C;
        }
        .profile-container .info-note {
            font-size: 0.7rem;
            color: #8B7A66;
            margin-top: 4px;
        }
        .profile-container .points-rewards-content {
            background: #FFFFFF;
            border-radius: 28px;
            padding: 32px;
            border: 1px solid #EAE6E0;
            text-align: center;
        }
        .profile-container .points-rewards-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 16px;
            font-family: 'Playfair Display', serif;
        }
        .profile-container .points-badge-large {
            background: #FDF8F0;
            display: inline-block;
            padding: 20px 40px;
            border-radius: 60px;
            margin: 20px 0;
        }
        .profile-container .points-badge-large span {
            font-size: 2.5rem;
            font-weight: 800;
            color: #D4AF37;
        }
        .profile-container .points-rules {
            text-align: left;
            max-width: 400px;
            margin: 24px auto 0;
            color: #6B6B6B;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .profile-container .table-wrapper {
            overflow-x: auto;
            border-radius: 24px;
            border: 1px solid #EAE6E0;
            background: #FFFFFF;
        }
        .profile-container .bookings-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .profile-container .bookings-table th, 
        .profile-container .bookings-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #F0EBE3;
        }
        .profile-container .bookings-table th {
            background: #FDFCF9;
            font-weight: 600;
            color: #8B7A66;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }
        .profile-container .bookings-table tr:last-child td {
            border-bottom: none;
        }
        .profile-container .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .profile-container .status-confirmed { background: #E8F0E7; color: #2D6A4F; }
        .profile-container .status-cancelled { background: #FCE9E6; color: #B23C1C; }
        .profile-container .status-pending { background: #FEF4E6; color: #B47C2E; }
        .profile-container .points-earned {
            color: #D4AF37;
            font-weight: 600;
        }
        .profile-container .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #B0B0B0;
        }
        .profile-container .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        /* Review button in bookings table */
        .profile-container .review-btn {
            background: #D4AF37;
            color: #1A1A1A;
            border: none;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .profile-container .review-btn:hover {
            background: #C5A059;
            transform: translateY(-1px);
        }
        .profile-container .reviewed-badge {
            background: #E8F0E7;
            color: #2D6A4F;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-block;
        }
        
        /* Review Modal Styles */
        .review-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        .review-modal.show {
            display: flex;
            animation: modalFadeIn 0.3s ease;
        }

        .review-modal-content {
            background: white;
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: modalSlideUp 0.3s ease;
        }

        .review-modal-header {
            background: linear-gradient(135deg, #c5a059, #a07d3e);
            padding: 20px 24px;
            border-radius: 24px 24px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .review-modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
            font-family: 'Playfair Display', serif;
        }

        .review-modal-header h3 i {
            margin-right: 8px;
        }

        .review-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            opacity: 0.8;
            transition: 0.2s;
        }

        .review-close-btn:hover {
            opacity: 1;
        }

        .review-modal-body {
            padding: 24px;
        }

        .review-hotel-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #c5a059;
            margin: 0 0 8px 0;
        }

        .review-room-info {
            font-size: 0.9rem;
            color: #555;
            margin: 0 0 5px 0;
        }

        .review-booking-ref {
            font-size: 0.8rem;
            color: #888;
            margin: 0 0 20px 0;
        }

        .review-rating-section,
        .review-comment-section {
            margin-bottom: 20px;
        }

        .review-rating-section label,
        .review-comment-section label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .star-rating {
            display: flex;
            gap: 12px;
            cursor: pointer;
        }

        .star-rating .star {
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ddd;
        }

        .star-rating .star:hover {
            transform: scale(1.1);
        }

        .review-comment-section textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 0.85rem;
            font-family: inherit;
            resize: vertical;
            transition: 0.2s;
        }

        .review-comment-section textarea:focus {
            outline: none;
            border-color: #c5a059;
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }

        .review-points-info {
            background: #fef3c7;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            color: #b45309;
            text-align: center;
        }

        .review-points-info i {
            margin-right: 8px;
        }

        .review-modal-footer {
            padding: 16px 24px 24px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            border-top: 1px solid #eee;
        }

        .review-later-btn,
        .review-submit-btn {
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            border: none;
        }

        .review-later-btn {
            background: transparent;
            border: 1px solid #ddd;
            color: #666;
        }

        .review-later-btn:hover {
            background: #f5f5f5;
        }

        .review-submit-btn {
            background: #c5a059;
            color: white;
        }

        .review-submit-btn:hover {
            background: #a07d3e;
            transform: translateY(-1px);
        }

        .review-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .review-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100000;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            animation: toastSlideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-toast-success {
            background: #0b5e42;
            color: white;
        }

        .review-toast-error {
            background: #991b1b;
            color: white;
        }

        .review-toast-info {
            background: #2c3e66;
            color: white;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastSlideIn {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .review-modal-footer {
                flex-direction: column;
            }
            
            .review-later-btn,
            .review-submit-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 900px) {
            .profile-container .hero-section {
                flex-direction: column;
                align-items: flex-start;
            }
            .profile-container .stats-group {
                width: 100%;
                justify-content: space-between;
            }
            .profile-container .two-col {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .profile-container {
                padding: 100px 20px 60px;
            }
        }
        @media (max-width: 550px) {
            .profile-container .stats-group {
                flex-direction: column;
                gap: 16px;
            }
            .profile-container .stat-card {
                text-align: left;
                display: flex;
                justify-content: space-between;
                align-items: baseline;
            }
            .profile-container .stat-number {
                font-size: 1.2rem;
            }
            .profile-container .tabs {
                gap: 16px;
            }
            .profile-container .tab-btn {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="hero-section">
        <div class="hero-text">
            <h1>Hello, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p>Member since <?php echo $join_date; ?></p>
        </div>
        <div class="stats-group">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($user['points'] ?? 0); ?></div>
                <div class="stat-label">Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Bookings</div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
        <div class="alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
    <?php endif; ?>

    <!-- Four Tabs -->
    <div class="tabs">
        <button class="tab-btn <?php echo $active_tab === 'account' ? 'active' : ''; ?>" data-tab="account"><i class="fas fa-user-circle"></i> Account Details</button>
        <button class="tab-btn <?php echo $active_tab === 'security' ? 'active' : ''; ?>" data-tab="security"><i class="fas fa-lock"></i> Security</button>
        <button class="tab-btn <?php echo $active_tab === 'points' ? 'active' : ''; ?>" data-tab="points"><i class="fas fa-coins"></i> Points</button>
        <button class="tab-btn <?php echo $active_tab === 'bookings' ? 'active' : ''; ?>" data-tab="bookings"><i class="fas fa-hotel"></i> Recent Bookings</button>
    </div>

    <!-- 1. Account Details -->
    <div class="tab-content <?php echo $active_tab === 'account' ? 'active' : ''; ?>" id="account-tab">
        <div class="form-card">
            <form method="POST" action="">
                <div class="two-col">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        <?php if (isset($errors['first_name'])): ?><div class="error-message"><?php echo $errors['first_name']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        <?php if (isset($errors['last_name'])): ?><div class="error-message"><?php echo $errors['last_name']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="readonly-email">
                    <div class="info-note">Email cannot be changed</div>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        <?php if (isset($errors['phone'])): ?><div class="error-message"><?php echo $errors['phone']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Country / Region</label>
                        <select name="country" required>
                            <option value="">Select</option>
                            <?php foreach ($countries as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $user['country'] === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                            <?php if (!empty($user['country']) && !in_array($user['country'], $countries)): ?>
                                <option value="<?php echo htmlspecialchars($user['country']); ?>" selected><?php echo htmlspecialchars($user['country']); ?></option>
                            <?php endif; ?>
                        </select>
                        <?php if (isset($errors['country'])): ?><div class="error-message"><?php echo $errors['country']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Birthday (Optional)</label>
                        <!-- ✅ 关键修改：添加 max 属性禁止未来日期 -->
                        <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" max="<?php echo date('Y-m-d'); ?>">
                        <?php if (isset($errors['birthday'])): ?><div class="error-message"><?php echo $errors['birthday']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Preferred Language</label>
                        <select name="language">
                            <?php foreach ($languages as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo $user['language'] == $code ? 'selected' : ''; ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="subscribe" name="subscribe" value="1" <?php echo $user['subscribe'] ? 'checked' : ''; ?>>
                    <label for="subscribe">Receive exclusive offers and travel inspiration via email</label>
                </div>
                <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- 2. Security -->
    <div class="tab-content <?php echo $active_tab === 'security' ? 'active' : ''; ?>" id="security-tab">
        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    <?php if (isset($errors['current_password'])): ?><div class="error-message"><?php echo $errors['current_password']; ?></div><?php endif; ?>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                        <?php if (isset($errors['new_password'])): ?><div class="error-message"><?php echo $errors['new_password']; ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?><div class="error-message"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <!-- 3. Points Rewards -->
    <div class="tab-content <?php echo $active_tab === 'points' ? 'active' : ''; ?>" id="points-tab">
        <div class="points-rewards-content">
            <h3><i class="fas fa-coins"></i> Your Points</h3>
            <div class="points-badge-large">
                <span><?php echo number_format($user['points'] ?? 0); ?></span> available points
            </div>
            <div class="points-rules">
                <p><strong>How to earn:</strong> Earn 10 points for every RM1 spent on room bookings.</p>
                <p><strong>How to redeem:</strong> 100 points = RM1 discount on future stays.</p>
                <p><strong>Lifetime earned:</strong> <?php echo number_format($total_points_earned); ?> points</p>
                <p><strong>Never expire.</strong> Use them at checkout.</p>
            </div>
        </div>
    </div>

    <!-- 4. Recent Bookings -->
    <div class="tab-content <?php echo $active_tab === 'bookings' ? 'active' : ''; ?>" id="bookings-tab">
        <div class="table-wrapper">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Dates</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_bookings)): ?>
                        <?php foreach ($all_bookings as $b): ?>
                            <tr>
                                <td><?php echo $b['type']; ?></td>
                                <td><?php echo htmlspecialchars($b['name']); ?></td>
                                <td>
                                    <?php 
                                    echo date('d M Y', strtotime($b['start_date']));
                                    if ($b['end_date']) echo ' → ' . date('d M Y', strtotime($b['end_date']));
                                    ?>
                                </td>
                                <td><?php echo $b['guests']; ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($b['status']); ?>"><?php echo ucfirst($b['status']); ?></span></td>
                                <td class="points-earned">
                                    <?php 
                                    if ($b['points_earned'] > 0) {
                                        echo '+' . number_format($b['points_earned']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td id="review-cell-<?php echo $b['booking_id']; ?>" data-room-id="<?php echo $b['room_id']; ?>" data-room-name="<?php echo htmlspecialchars($b['name']); ?>" data-booking-ref="<?php echo $b['booking_id']; ?>">
                                    <span class="review-loading">Loading...</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">No bookings yet. <a href="../ChongEeLynn/accommodation.php" style="color: #D4AF37;">Explore our rooms</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<script>
// API Base URL
const API_URL = '/Hotel-Booking-Web-System/ChongEeLynn/review_api.php';

let reviewModal = null;

// Function to check review status for each booking
async function loadReviewStatuses() {
    const bookingRows = document.querySelectorAll('#bookings-tab .bookings-table tbody tr');
    
    for (const row of bookingRows) {
        if (row.querySelector('.empty-state')) continue;
        
        const reviewCell = row.querySelector('[id^="review-cell-"]');
        if (!reviewCell) continue;
        
        const bookingId = reviewCell.id.replace('review-cell-', '');
        const roomId = reviewCell.dataset.roomId;
        const roomName = reviewCell.dataset.roomName;
        const statusCell = row.querySelector('.status-badge');
        const status = statusCell ? statusCell.innerText.trim().toLowerCase() : '';
        
        if (status === 'completed' && roomId && roomId !== 'NULL' && roomId !== '') {
            try {
                const response = await fetch(`${API_URL}?action=check_booking&booking_id=${bookingId}`);
                const data = await response.json();
                
                if (data.reviewed) {
                    reviewCell.innerHTML = '<span class="reviewed-badge"><i class="fas fa-check-circle"></i> Reviewed</span>';
                } else {
                    reviewCell.innerHTML = `<button class="review-btn" onclick="showReviewPopupForBooking(${bookingId}, ${roomId}, '${encodeURIComponent(roomName)}', '${bookingId}')"><i class="fas fa-star"></i> Leave Review</button>`;
                }
            } catch (error) {
                console.error('Error checking review status:', error);
                reviewCell.innerHTML = '<span class="reviewed-badge" style="background:#FCE9E6;color:#B23C1C;"><i class="fas fa-exclamation"></i> Error</span>';
            }
        } else {
            reviewCell.innerHTML = '<span class="reviewed-badge" style="background:#F0EBE3;color:#8B7A66;"><i class="fas fa-clock"></i> Not Available</span>';
        }
    }
}

function showReviewPopupForBooking(bookingId, roomId, roomName, bookingRef) {
    const booking = {
        booking_id: bookingId,
        room_id: roomId,
        room_name: decodeURIComponent(roomName),
        booking_ref: bookingRef
    };
    showReviewPopup(booking);
}

function showReviewPopup(booking) {
    // Remove existing modal if any
    if (reviewModal) {
        reviewModal.remove();
    }
    
    // Create modal HTML
    reviewModal = document.createElement('div');
    reviewModal.id = 'reviewModal';
    reviewModal.className = 'review-modal';
    reviewModal.innerHTML = `
        <div class="review-modal-content">
            <div class="review-modal-header">
                <h3><i class="fas fa-star"></i> Leave a Review</h3>
                <button class="review-close-btn" onclick="closeReviewPopup()">&times;</button>
            </div>
            <div class="review-modal-body">
                <p class="review-hotel-name">Grand Hotel Melaka</p>
                <p class="review-room-info">Room: <strong>${escapeHtml(booking.room_name)}</strong></p>
                <p class="review-booking-ref">Booking: #${escapeHtml(booking.booking_ref)}</p>
                
                <div class="review-rating-section">
                    <label>Your Rating:</label>
                    <div class="star-rating" id="starRatingContainer">
                        <span class="star" data-rating="1">☆</span>
                        <span class="star" data-rating="2">☆</span>
                        <span class="star" data-rating="3">☆</span>
                        <span class="star" data-rating="4">☆</span>
                        <span class="star" data-rating="5">☆</span>
                    </div>
                    <input type="hidden" id="reviewRating" value="0">
                </div>
                
                <div class="review-comment-section">
                    <label>Your Review:</label>
                    <textarea id="reviewComment" rows="4" placeholder="Share your experience at Grand Hotel Melaka..."></textarea>
                </div>
                
                <div class="review-points-info">
                    <i class="fas fa-gift"></i> Earn <strong>10 points</strong> for leaving a review!
                </div>
            </div>
            <div class="review-modal-footer">
                <button class="review-later-btn" onclick="skipReview(${booking.booking_id})">Later</button>
                <button class="review-submit-btn" onclick="submitReview(${booking.booking_id}, ${booking.room_id})">Submit Review & Earn Points</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(reviewModal);
    
    // Star rating functionality
    const stars = reviewModal.querySelectorAll('.star');
    const ratingInput = reviewModal.querySelector('#reviewRating');
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.textContent = '★';
                star.style.color = '#fbbf24';
            } else {
                star.textContent = '☆';
                star.style.color = '#ddd';
            }
        });
    }
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            updateStars(rating);
            console.log('Rating selected:', rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '★';
                    s.style.color = '#fbbf24';
                } else {
                    s.textContent = '☆';
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    // Reset stars on mouse leave
    const container = reviewModal.querySelector('#starRatingContainer');
    container.addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        updateStars(currentRating);
    });
    
    // Show modal
    setTimeout(() => {
        reviewModal.classList.add('show');
    }, 10);
    
    // Close on background click
    reviewModal.addEventListener('click', function(e) {
        if (e.target === reviewModal) {
            closeReviewPopup();
        }
    });
}

function closeReviewPopup() {
    if (reviewModal) {
        reviewModal.classList.remove('show');
        setTimeout(() => {
            reviewModal.remove();
            reviewModal = null;
        }, 300);
    }
}

function skipReview(bookingId) {
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'skip_review', booking_id: bookingId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeReviewPopup();
            showToast('You can review later from your bookings page.', 'info');
            loadReviewStatuses();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    });
}

function submitReview(bookingId, roomId) {
    const rating = document.getElementById('reviewRating').value;
    const comment = document.getElementById('reviewComment').value;
    
    console.log('Rating:', rating, 'Comment:', comment);
    
    if (rating == 0) {
        showToast('Please select a rating!', 'error');
        return;
    }
    
    if (!comment.trim()) {
        showToast('Please write your review!', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.review-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'submit_review',
            booking_id: bookingId,
            room_id: roomId,
            rating: rating,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeReviewPopup();
            
            // Update points display
            const pointsElement = document.querySelector('.stat-number');
            if (pointsElement) {
                fetch(`${API_URL}?action=get_points`)
                    .then(res => res.json())
                    .then(pointsData => {
                        if (pointsData.points) {
                            pointsElement.textContent = pointsData.points.toLocaleString();
                        }
                    });
            }
            loadReviewStatuses();
        } else {
            showToast(data.error, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.querySelector('.review-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.className = `review-toast review-toast-${type}`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Check for pending review on page load
function checkForPendingReview() {
    console.log('Checking for pending review...');
    fetch(`${API_URL}?action=check_pending`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            if (data.success && data.has_review) {
                console.log('Pending review found! Showing popup...');
                showReviewPopup(data.booking);
            } else {
                console.log('No pending review found');
            }
        })
        .catch(error => {
            console.error('Error checking review status:', error);
        });
}

// Load review statuses when bookings tab becomes active
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`${tabId}-tab`).classList.add('active');
        
        if (tabId === 'bookings') {
            loadReviewStatuses();
        }
        });
    });

// Initial load if bookings tab is active on page load
if (document.getElementById('bookings-tab').classList.contains('active')) {
    loadReviewStatuses();
}

// Auto-check for pending review when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkForPendingReview, 500);
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>