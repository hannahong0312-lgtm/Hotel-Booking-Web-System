<?php
// add_offer.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $conn->real_escape_string($_POST['code']);
    $category = $conn->real_escape_string($_POST['category']);
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $image = $conn->real_escape_string($_POST['image']);
    $discount_percentage = (float)$_POST['discount_percentage'];
    $valid_from = $conn->real_escape_string($_POST['valid_from']);
    $valid_to = $conn->real_escape_string($_POST['valid_to']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $terms = isset($_POST['terms']) ? $conn->real_escape_string($_POST['terms']) : '';
    
    $sql = "INSERT INTO hotel_offers (code, category, title, description, image, discount_percentage, valid_from, valid_to, is_active, terms) 
            VALUES ('$code', '$category', '$title', '$description', '$image', $discount_percentage, '$valid_from', '$valid_to', $is_active, '$terms')";
    
    if ($conn->query($sql)) {
        $message = "Offer added successfully!";
        $messageType = "success";
        echo "<script>setTimeout(()=>{window.location='admin_offers.php';},1500);</script>";
    } else {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Offer - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/edit_offer.css">
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Add New Offer</h2>
        <p>Create a new promotion or discount offer for your customers</p>
    </div>
    
    <?php if($message): ?>
        <div class="message <?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>
    
    <form method="POST" class="offer-form">
        <div class="form-section">
            <h3>Offer Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Offer Code <span class="required">*</span></label>
                    <input type="text" name="code" placeholder="e.g., SUMMER25, WELCOME10" required>
                    <small class="hint">Unique promotional code for this offer</small>
                </div>
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="seasonal">Seasonal</option>
                        <option value="holiday">Holiday</option>
                        <option value="corporate">Corporate</option>
                        <option value="spa">Spa</option>
                        <option value="early_bird">Early Bird</option>
                        <option value="last_minute">Last Minute</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Offer Title <span class="required">*</span></label>
                <input type="text" name="title" placeholder="e.g., Summer Special Getaway" required>
            </div>
            
            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <textarea name="description" rows="4" placeholder="Describe the offer details and what's included..." required></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Pricing & Discount</h3>
            <div class="form-group">
                <label>Discount Percentage <span class="required">*</span></label>
                <input type="number" step="0.01" name="discount_percentage" placeholder="e.g., 25" min="0" max="100" required>
                <small class="hint">Enter discount percentage (e.g., 25 for 25% off)</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Validity Period</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Valid From <span class="required">*</span></label>
                    <input type="date" name="valid_from" required>
                </div>
                <div class="form-group">
                    <label>Valid To <span class="required">*</span></label>
                    <input type="date" name="valid_to" required>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Offer Image</h3>
            <div class="form-group">
                <label>Image Filename <span class="required">*</span></label>
                <input type="text" name="image" placeholder="offer-image.jpg" required>
                <small class="hint">Place image in the "images" folder</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Terms & Conditions</h3>
            <div class="form-group">
                <label>Terms</label>
                <textarea name="terms" rows="4" placeholder="Enter terms and conditions for this offer..."></textarea>
                <small class="hint">Optional: Add specific terms and conditions</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Status</h3>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" checked>
                    Active (visible to customers)
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">Save Offer</button>
            <a href="admin_offers.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>