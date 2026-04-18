<?php
// profile.php - Four Tabs (Account, Security, Points Rewards, Recent Bookings)
// 统一使用 subscribe 字段
require_once '../Shared/header.php';

if (!$is_logged_in || $user_role !== 'customer') {
    redirect('homepage.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// 获取用户基本信息（使用 subscribe 字段）
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

// 统计预订数量（房间+餐饮）
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
    
    // 从 payment 表获取生命周期总赚取积分
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

// 处理个人信息更新（使用 subscribe）
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
    if ($birthday && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) $errors['birthday'] = 'Invalid date';

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
}

// 修改密码（不变）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $pwdStmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $pwdStmt->bind_param("i", $user_id);
    $pwdStmt->execute();
    $pwdResult = $pwdStmt->get_result()->fetch_assoc();
    $pwdStmt->close();

    if (!$pwdResult || !password_verify($current, $pwdResult['password']))
        $errors['current_password'] = 'Current password is incorrect.';
    elseif (strlen($new) < 6)
        $errors['new_password'] = 'Password must be at least 6 characters.';
    elseif ($new !== $confirm)
        $errors['confirm_password'] = 'Passwords do not match.';
    else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=?, updated_at=NOW() WHERE id=?");
        $update->bind_param("si", $hashed, $user_id);
        if ($update->execute()) $success = 'Password changed. Please log in again.';
        else $errors['general'] = 'Failed to change password.';
        $update->close();
    }
}

// 获取所有预订（房间+餐饮）用于表格 - 修复房间名称查询，并关联 payment 获取 points_earned
$all_bookings = [];
try {
    // 房间预订：通过 JOIN rooms 获取 room name，LEFT JOIN payment 获取 points_earned
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
    
    // 餐饮预订（没有积分赚取，直接显示 NULL）
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

$countries = [
    'Malaysia', 'Singapore', 'Thailand', 'Indonesia', 'Vietnam', 'Philippines',
    'United States', 'United Kingdom', 'Australia', 'China', 'Japan', 'South Korea',
    'India', 'Germany', 'France', 'Italy', 'Canada', 'Other'
];
$languages = [
    'en' => 'English',
    'zh' => '中文',
    'ms' => 'Bahasa Malaysia',
    'ja' => '日本語'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Grand Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* 所有样式限定在 .profile-container 内，避免影响 footer */
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
    <!-- 顶部欢迎 + 统计（移除了 Spent 和 Member ID） -->
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

    <!-- 四个标签页 -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="account"><i class="fas fa-user-circle"></i> Account Details</button>
        <button class="tab-btn" data-tab="security"><i class="fas fa-lock"></i> Security</button>
        <button class="tab-btn" data-tab="points"><i class="fas fa-coins"></i> Points</button>
        <button class="tab-btn" data-tab="bookings"><i class="fas fa-hotel"></i> Recent Bookings</button>
    </div>

    <!-- 1. Account Details -->
    <div class="tab-content active" id="account-tab">
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
                        </select>
                        <?php if (isset($errors['country'])): ?><div class="error-message"><?php echo $errors['country']; ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Birthday (Optional)</label>
                        <input type="date" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>">
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
    <div class="tab-content" id="security-tab">
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
    <div class="tab-content" id="points-tab">
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

    <!-- 4. Recent Bookings（Points 列现在会显示真实赚取的积分） -->
    <div class="tab-content" id="bookings-tab">
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

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>