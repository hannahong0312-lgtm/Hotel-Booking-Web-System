<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_path = rtrim(dirname(dirname($script_name)), '/\\');
    define('BASE_URL', $protocol . '://' . $host . $base_path);
}

// Include admin authentication and mail functions
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../ChangJingEn/mail_functions.php';

// Restrict access to superadmin or admin only
if ($admin_role !== 'superadmin' && $admin_role !== 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// Session configuration for newsletter settings
if (!isset($_SESSION['newsletter_config'])) {
    $_SESSION['newsletter_config'] = [
        'subject'           => '',
        'content'           => "Dear {first_name},\n\nWe have a special offer for you!",
        'birthday_subject'  => 'Happy Birthday from Grand Hotel!',
        'birthday_discount' => 10,
        'birthday_code'     => 'BDAY10',  // legacy, not used for unique codes
        'birthday_message'  => "Dear {first_name},\n\nEnjoy {discount}% off your next booking with code: {code}. Valid for 30 days."
    ];
}
$config = &$_SESSION['newsletter_config'];


// Statistics for dashboard cards

$total_subscribers = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE subscribe = 1 AND email_verified = 1")->fetch_assoc()['cnt'];
$today_birthdays   = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE DATE(birthday) = CURDATE() AND subscribe = 1 AND email_verified = 1")->fetch_assoc()['cnt'];
$active_offers     = $conn->query("SELECT COUNT(*) as cnt FROM hotel_offers WHERE is_active = 1 AND valid_to >= CURDATE()")->fetch_assoc()['cnt'];

$message      = '';
$error        = '';
$preview_html = '';
$active_tab   = $_POST['active_tab'] ?? $_GET['tab'] ?? 'newsletter';


// EMAIL TEMPLATE FUNCTIONS
function getBirthdayEmailTemplate($firstName, $discount, $code) {
    $login_url = BASE_URL . '/ChangJingEn/login.php';
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Happy Birthday from Grand Hotel</title></head>
<body style="margin:0; padding:24px 12px; background:#f0ece6; font-family: 'Segoe UI', Helvetica, Arial, sans-serif;">
<div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
    <div style="background:#121212; padding:28px 20px; text-align:center;">
        <h1 style="font-family: 'Playfair Display', 'Times New Roman', serif; font-size:28px; color:#D4AF37; margin:0; letter-spacing:1px;">GRAND HOTEL MELAKA</h1>
        <p style="color:#CCCCCC; font-size:14px; margin:8px 0 0;">Luxury Redefined</p>
    </div>
    <div style="background:#D4AF37; color:#121212; text-align:center; padding:20px; font-size:22px; font-weight:bold; letter-spacing:2px;">HAPPY BIRTHDAY</div>
    <div style="padding:32px 24px;">
        <div style="font-size:26px; font-weight:600; color:#121212; margin-bottom:12px; font-family:'Playfair Display',serif;">Dear {$firstName},</div>
        <p style="font-size:16px; line-height:1.5; color:#333333; margin-bottom:28px;">To celebrate your special day, we would like to offer you an exclusive discount on your next stay. Experience the warmth of Grand Hotel with a touch of golden luxury.</p>
        <div style="background:#F5F0E6; border-radius:48px; padding:16px 24px; text-align:center; margin:20px 0; border:1px dashed #D4AF37;">
            <div style="font-size:14px; margin-bottom:6px;">Your birthday promo code</div>
            <div style="font-size:28px; font-weight:700; color:#D4AF37; letter-spacing:2px; font-family:monospace;">{$code}</div>
            <div style="font-size:13px; margin-top:8px;">Use at checkout • Valid for 30 days • One-time use</div>
        </div>
        <div style="text-align:center;"><a href="{$login_url}" style="display:inline-block; background:#D4AF37; color:#121212; padding:14px 32px; border-radius:40px; text-decoration:none; font-weight:600; margin:16px 0;">Book Your Stay →</a></div>
        <p style="font-size:14px; margin-top:24px;">*Get {$discount}% off the best available rate. Excludes taxes & service charges. Not combinable with other offers.</p>
    </div>
    <div style="background:#f2f2f2; padding:20px; text-align:center; font-size:12px; color:#777777; border-top:1px solid #e0e0e0;">
        Grand Hotel Melaka · 88 Jalan Kota Laksamana · 75200 Melaka, Malaysia<br>
        <a href="#" style="color:#D4AF37; text-decoration:none;">Unsubscribe</a> | <a href="#" style="color:#D4AF37; text-decoration:none;">Privacy Policy</a>
    </div>
</div>
</body>
</html>
HTML;
}

function getNewsletterEmailTemplate($firstName, $subject, $content) {
    $offers_url = BASE_URL . '/ChongEeLynn/offers.php';
    $htmlContent = nl2br(htmlspecialchars($content));
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{$subject}</title></head>
<body style="margin:0; padding:24px 12px; background:#f0ece6; font-family: 'Segoe UI', Helvetica, Arial, sans-serif;">
<div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:24px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
    <div style="background:#121212; padding:28px 20px; text-align:center;">
        <h2 style="font-family:'Playfair Display',serif; font-size:26px; color:#D4AF37; margin:0;">GRAND HOTEL MELAKA</h2>
    </div>
    <div style="background:linear-gradient(135deg, #1e1e2f, #0f0f1a); padding:40px 20px; text-align:center; color:white;">
        <h3 style="font-size:28px; margin:0; text-shadow:0 2px 4px rgba(0,0,0,0.3);">Exclusive Offer</h3>
        <p style="font-size:16px; margin-top:8px;">{$subject}</p>
    </div>
    <div style="padding:28px 24px;">
        <div style="font-size:20px; font-weight:600;">Dear {$firstName},</div>
        <div style="margin:16px 0; font-size:16px; line-height:1.5; color:#333333;">{$htmlContent}</div>
        <div style="text-align:center; margin-top:24px;">
            <a href="{$offers_url}" style="display:inline-block; background:#D4AF37; color:#121212; padding:12px 28px; border-radius:40px; text-decoration:none; font-weight:600;">View Offer →</a>
        </div>
    </div>
    <div style="background:#f2f2f2; padding:20px; text-align:center; font-size:12px; color:#777777;">
        Grand Hotel Melaka · 88 Jalan Kota Laksamana · 75200 Melaka, Malaysia<br>
        <a href="#" style="color:#D4AF37; text-decoration:none;">Unsubscribe</a> | <a href="#" style="color:#D4AF37; text-decoration:none;">Manage Preferences</a>
    </div>
</div>
</body>
</html>
HTML;
}


// POST HANDLERS (Save config, send newsletters, send birthday offers, preview)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update configuration from form inputs
    if (isset($_POST['save_config']) || isset($_POST['send_newsletter']) || isset($_POST['send_birthday']) || isset($_POST['preview_newsletter']) || isset($_POST['preview_birthday'])) {
        $config['subject']            = trim($_POST['subject'] ?? '');
        $config['content']            = trim($_POST['content'] ?? '');
        $config['birthday_subject']   = trim($_POST['birthday_subject'] ?? '');
        $config['birthday_discount']  = (int)($_POST['birthday_discount'] ?? 10);
        $config['birthday_code']      = trim($_POST['birthday_code'] ?? 'BDAY10');
        $config['birthday_message']   = trim($_POST['birthday_message'] ?? '');
    }
    if (isset($_POST['save_config'])) {
        $message = '✔️ Settings saved successfully.';
    }
    elseif (isset($_POST['send_newsletter'])) {
        $subject = $config['subject'];
        $content = $config['content'];
        if (empty($subject) || empty($content)) {
            $error = '❌ Newsletter subject and content cannot be empty.';
        } else {
            $users = $conn->query("SELECT email, first_name FROM users WHERE subscribe = 1 AND email_verified = 1");
            if (!$users->num_rows) {
                $error = 'ℹ️ No subscribers found.';
            } else {
                $sent = 0; $failed = 0;
                while ($user = $users->fetch_assoc()) {
                    $personalized = str_replace('{first_name}', $user['first_name'], $content);
                    $html = getNewsletterEmailTemplate($user['first_name'], $subject, $personalized);
                    if (sendCustomEmail($user['email'], $user['first_name'], $subject, $html)) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                }
                $message = "📧 Newsletter sent! Successful: $sent, Failed: $failed.";
            }
        }
    }
    // Send Birthday Emails to users whose birthday is today
    elseif (isset($_POST['send_birthday'])) {
        $subject = $config['birthday_subject'];
        $discount = $config['birthday_discount'];
        if (empty($subject)) {
            $error = '❌ Birthday subject cannot be empty.';
        } else {
            $result = $conn->query("SELECT id, email, first_name FROM users WHERE DATE(birthday) = CURDATE() AND subscribe = 1 AND email_verified = 1");
            $sent = 0; $failed = 0;
            while ($user = $result->fetch_assoc()) {
                // Generate a unique 5‑character random code prefixed with BDAY
                $unique_code = 'BDAY' . strtoupper(substr(md5(uniqid($user['id'], true)), 0, 5));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Prevent duplicate generation for the same calendar year (one birthday offer per year)
                $check = $conn->prepare("SELECT id FROM birthday_discount_codes WHERE user_id = ? AND YEAR(created_at) = YEAR(CURDATE())");
                $check->bind_param("i", $user['id']);
                $check->execute();
                $check_result = $check->get_result();
                if ($check_result->num_rows > 0) {
                    $check->close();
                    continue; // user already received a birthday code this year → skip
                }
                $check->close();
                
                // Insert the new unique discount code
                $stmt = $conn->prepare("INSERT INTO birthday_discount_codes (user_id, code, discount_percent, expires_at) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isds", $user['id'], $unique_code, $discount, $expires_at);
                if (!$stmt->execute()) {
                    error_log("Failed to insert discount code for user {$user['id']}: " . $stmt->error);
                    $failed++;
                    continue;
                }
                $stmt->close();
                
                // Send the email with the unique code
                $html = getBirthdayEmailTemplate($user['first_name'], $discount, $unique_code);
                if (sendCustomEmail($user['email'], $user['first_name'], $subject, $html)) {
                    $sent++;
                } else {
                    $failed++;
                    // Rollback: delete the code if email sending fails
                    $conn->query("DELETE FROM birthday_discount_codes WHERE code = '$unique_code'");
                }
            }
            $message = "🎂 Birthday emails sent: $sent, Failed: $failed.";
        }
    }
    // Preview Newsletter (shows plain text placeholder)
    elseif (isset($_POST['preview_newsletter'])) {
        $content = $config['content'];
        $preview_html = "<h3>📧 Newsletter Preview</h3>" . nl2br(htmlspecialchars(str_replace('{first_name}', '[Customer Name]', $content)));
    }
    // Preview Birthday Email (shows placeholder)
    elseif (isset($_POST['preview_birthday'])) {
        $discount = $config['birthday_discount'];
        $code = $config['birthday_code']; // static preview code
        $msg = str_replace(['{first_name}', '{discount}', '{code}'], ['[Customer Name]', $discount, $code], $config['birthday_message']);
        $preview_html = "<h3>🎁 Birthday Email Preview</h3>" . nl2br(htmlspecialchars($msg));
    }
    
    $active_tab = $_POST['active_tab'] ?? 'newsletter';
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    .email-container { max-width: 1200px; margin: 0 auto; }
    .page-header { margin-bottom: 28px; padding-left: 20px; border-left: 4px solid var(--gold); }
    .page-header h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
    .page-header .sub-icon { color: var(--gold); margin-right: 8px; }
    .page-header p { color: var(--text-secondary); font-size: 0.9rem; }
    .stats-summary { display: flex; gap: 20px; margin-bottom: 32px; flex-wrap: wrap; }
    .stat-summary-card {
        flex: 1;
        border-radius: 24px;
        padding: 20px 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
        background: var(--bg-sidebar);
    }
    .stat-summary-card.subscribers { border-left: 4px solid var(--gold); background: linear-gradient(135deg, rgba(197,160,89,0.08) 0%, rgba(197,160,89,0.02) 100%); }
    .stat-summary-card.birthdays  { border-left: 4px solid #10b981; background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0.02) 100%); }
    .stat-summary-card.promotions { border-left: 4px solid #f97316; background: linear-gradient(135deg, rgba(249,115,22,0.08) 0%, rgba(249,115,22,0.02) 100%); }
    [data-theme="dark"] .stat-summary-card.subscribers { background: linear-gradient(135deg, rgba(251,191,36,0.12) 0%, rgba(251,191,36,0.02) 100%); }
    [data-theme="dark"] .stat-summary-card.birthdays  { background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(16,185,129,0.02) 100%); }
    [data-theme="dark"] .stat-summary-card.promotions { background: linear-gradient(135deg, rgba(249,115,22,0.12) 0%, rgba(249,115,22,0.02) 100%); }
    .stat-summary-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .stat-summary-card .stat-icon { position: absolute; right: 20px; bottom: 20px; font-size: 2.5rem; opacity: 0.12; color: var(--text-secondary); }
    .stat-summary-card .stat-value { font-size: 2rem; font-weight: 800; line-height: 1.2; }
    .stat-summary-card .stat-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-top: 8px; }
    .stat-summary-card .stat-sub { font-size: 0.7rem; color: var(--text-secondary); margin-top: 4px; }
    .tabs-wrapper { border-bottom: 1px solid var(--border-light); margin-bottom: 28px; }
    .tabs { display: flex; gap: 8px; }
    .tab-btn {
        background: transparent;
        border: none;
        padding: 10px 20px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        transition: 0.2s;
        position: relative;
    }
    .tab-btn i { margin-right: 8px; }
    .tab-btn.active { color: var(--gold); background: var(--bg-sidebar); }
    .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: var(--gold); }
    .tab-content { display: none; animation: fadeIn 0.2s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .tool-card {
        background: var(--bg-sidebar);
        border-radius: 24px;
        padding: 28px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: 0.2s;
        margin-bottom: 28px;
    }
    .tool-card:hover { box-shadow: var(--shadow-md); }
    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border-light);
    }
    .card-header i { font-size: 1.8rem; color: var(--gold); }
    .card-header h3 { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 600; color: var(--text-primary); margin: 0; }

    .form-group { margin-bottom: 24px; }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-light);
        border-radius: 16px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-family: 'Inter', monospace;
        transition: 0.2s;
    }
    .form-group input:focus, .form-group textarea:focus {
        outline: none;
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(197,160,89,0.1);
    }
    .helper-text { font-size: 0.7rem; color: var(--text-secondary); margin-top: 6px; line-height: 1.4; }

    .card-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--border-light);
    }
    .button-group { display: flex; flex-wrap: wrap; gap: 12px; }
    .btn-primary, .btn-secondary, .btn-outline {
        padding: 10px 24px;
        border-radius: 40px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
    }
    .btn-primary { background: var(--gold); color: white; }
    .btn-primary:hover { background: var(--gold-hover); transform: translateY(-1px); }
    .btn-secondary { background: transparent; border: 1px solid var(--border-light); color: var(--text-secondary); }
    .btn-secondary:hover { border-color: var(--gold); color: var(--gold); }
    .btn-outline { background: transparent; border: 1px solid var(--gold); color: var(--gold); }
    .btn-outline:hover { background: rgba(197,160,89,0.1); }

    .alert {
        padding: 12px 20px;
        border-radius: 20px;
        margin-bottom: 24px;
        font-size: 0.85rem;
    }
    .alert-success { background: #e6f9ed; color: #0b5e42; border: 1px solid #c8e6d9; }
    .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    [data-theme="dark"] .alert-success { background: #1a3a2a; color: #b8e6cc; border-color: #2a5a3a; }
    [data-theme="dark"] .alert-danger  { background: #3a1a1a; color: #ffa2a2; border-color: #5a2a2a; }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        justify-content: center;
        align-items: center;
        z-index: 2000;
        backdrop-filter: blur(4px);
    }
    .modal-content {
        background: var(--bg-sidebar);
        border-radius: 24px;
        max-width: 600px;
        width: 90%;
        padding: 24px;
        position: relative;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-md);
    }
    .modal-content .close {
        position: absolute;
        top: 20px;
        right: 24px;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-secondary);
    }
    .preview-body {
        max-height: 400px;
        overflow-y: auto;
        padding: 16px;
        background: var(--bg-body);
        border-radius: 16px;
        margin-top: 16px;
        white-space: pre-wrap;
    }

    @media (max-width: 768px) {
        .stats-summary { flex-direction: column; }
        .tab-btn { padding: 8px 16px; font-size: 0.85rem; }
        .tool-card { padding: 20px; }
        .card-actions { flex-direction: column; align-items: stretch; }
        .button-group { justify-content: center; }
    }
</style>

<div class="email-container">
    <div class="page-header">
        <h2><i class="fas fa-envelope-open-text sub-icon"></i> Email Marketing Tools</h2>
        <p>Design & send beautiful newsletters, automate birthday offers, and grow your guest engagement.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-summary">
        <div class="stat-summary-card subscribers">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?php echo number_format($total_subscribers); ?></div>
            <div class="stat-label">Total Subscribers</div>
            <div class="stat-sub">Email-verified & opted in</div>
        </div>
        <div class="stat-summary-card birthdays">
            <div class="stat-icon"><i class="fas fa-birthday-cake"></i></div>
            <div class="stat-value"><?php echo number_format($today_birthdays); ?></div>
            <div class="stat-label">Birthdays Today</div>
            <div class="stat-sub">Subscribers celebrating today</div>
        </div>
        <div class="stat-summary-card promotions">
            <div class="stat-icon"><i class="fas fa-tags"></i></div>
            <div class="stat-value"><?php echo number_format($active_offers); ?></div>
            <div class="stat-label">Active Promotions</div>
            <div class="stat-sub">Valid vouchers & discounts</div>
        </div>
    </div>

    <!-- Success / Error Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="tabs-wrapper">
        <div class="tabs">
            <button class="tab-btn <?php echo $active_tab === 'newsletter' ? 'active' : ''; ?>" data-tab="newsletter"><i class="fas fa-bullhorn"></i> Newsletter Campaign</button>
            <button class="tab-btn <?php echo $active_tab === 'birthday' ? 'active' : ''; ?>" data-tab="birthday"><i class="fas fa-gift"></i> Birthday Offers</button>
        </div>
    </div>

    <form method="POST" id="mainForm">
        <input type="hidden" name="active_tab" id="active_tab" value="<?php echo $active_tab; ?>">

        <!-- Tab 1: Newsletter Campaign -->
        <div class="tab-content <?php echo $active_tab === 'newsletter' ? 'active' : ''; ?>" id="tab-newsletter">
            <div class="tool-card">
                <div class="card-header">
                    <i class="fas fa-bullhorn"></i>
                    <h3>Newsletter Campaign</h3>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Email Subject</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($config['subject'] ?? ''); ?>" placeholder="e.g., Special Summer Offer - Up to 25% Off">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope-open-text"></i> Email Content</label>
                    <textarea name="content" rows="6" placeholder="Dear {first_name},&#10;&#10;We have a special offer for you!"><?php echo htmlspecialchars($config['content'] ?? ''); ?></textarea>
                    <div class="helper-text">
                        <strong>💡 Tip:</strong> Use <code>{first_name}</code> as a placeholder. HTML tags allowed.
                    </div>
                </div>
                <div class="card-actions">
                    <div class="button-group">
                        <button type="submit" name="preview_newsletter" class="btn-secondary"><i class="fas fa-eye"></i> Preview</button>
                        <button type="submit" name="send_newsletter" class="btn-primary" onclick="return confirm('Send this newsletter to ALL subscribers?')"><i class="fas fa-paper-plane"></i> Send to All Subscribers</button>
                    </div>
                    <button type="submit" name="save_config" class="btn-outline"><i class="fas fa-save"></i> Save All Settings</button>
                </div>
            </div>
        </div>

        <!-- Tab 2: Birthday Offers -->
        <div class="tab-content <?php echo $active_tab === 'birthday' ? 'active' : ''; ?>" id="tab-birthday">
            <div class="tool-card">
                <div class="card-header">
                    <i class="fas fa-gift"></i>
                    <h3>Birthday Offers</h3>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Email Subject</label>
                    <input type="text" name="birthday_subject" value="<?php echo htmlspecialchars($config['birthday_subject'] ?? ''); ?>" placeholder="Happy Birthday!">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-percent"></i> Discount Percentage</label>
                    <input type="number" name="birthday_discount" value="<?php echo htmlspecialchars($config['birthday_discount'] ?? 10); ?>" min="0" max="100">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ticket-alt"></i> Discount Code</label>
                    <input type="text" class="disabled" value="(Auto-generated per user)" disabled>
                    <div class="helper-text">Each user receives a unique code automatically.</div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope-open-text"></i> Message Template</label>
                    <textarea name="birthday_message" rows="5" placeholder="Dear {first_name},&#10;Enjoy {discount}% off your next booking with code: {code}."><?php echo htmlspecialchars($config['birthday_message'] ?? ''); ?></textarea>
                    <div class="helper-text">
                        <strong>💡 Placeholders:</strong> <code>{first_name}</code> → guest name, <code>{discount}</code> → discount percentage, <code>{code}</code> → unique discount code.
                    </div>
                </div>
                <div class="card-actions">
                    <div class="button-group">
                        <button type="submit" name="preview_birthday" class="btn-secondary"><i class="fas fa-eye"></i> Preview</button>
                        <button type="submit" name="send_birthday" class="btn-primary" onclick="return confirm('Send birthday emails to today\'s birthday users?')"><i class="fas fa-birthday-cake"></i> Send to Today's Birthdays</button>
                    </div>
                    <button type="submit" name="save_config" class="btn-outline"><i class="fas fa-save"></i> Save All Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<?php if (!empty($preview_html)): ?>
<div id="previewModal" class="modal" style="display: flex;">
    <div class="modal-content">
        <span class="close" onclick="closePreview()">&times;</span>
        <div class="preview-body"><?php echo $preview_html; ?></div>
    </div>
</div>
<script>
    function closePreview() { document.getElementById('previewModal').style.display = 'none'; }
    window.onclick = function(event) { const modal = document.getElementById('previewModal'); if (event.target === modal) closePreview(); }
</script>
<?php endif; ?>

...
<script>
    // Tab switching logic (existing)
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(`tab-${tabId}`).classList.add('active');
            document.getElementById('active_tab').value = tabId;
        });
    });
</script>

<!-- 添加主题切换脚本 -->
<script>
(function() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return;
    const html = document.documentElement;
    const saved = localStorage.getItem('theme');
    if (saved === 'dark') {
        html.setAttribute('data-theme', 'dark');
        toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
    } else {
        html.setAttribute('data-theme', 'light');
        toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
    }
    toggle.addEventListener('click', () => {
        if (html.getAttribute('data-theme') === 'light') {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
        } else {
            html.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
        }
    });
})();
</script>

<?php
?>
    </main>
</div>
</body>
</html>