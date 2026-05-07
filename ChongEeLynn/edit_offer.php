<?php
// edit_offer.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM hotel_offers WHERE id = $id");
if ($result->num_rows == 0) die("Offer not found");
$offer = $result->fetch_assoc();

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
    
    // Check if offer code already exists (excluding current offer)
    $check_sql = "SELECT id FROM hotel_offers WHERE code = '$code' AND id != $id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $message = "Error: An offer with the code '$code' already exists. Please use a different offer code.";
        $messageType = "error";
    } else {
        $sql = "UPDATE hotel_offers SET 
                code='$code', 
                category='$category', 
                title='$title', 
                description='$description', 
                image='$image', 
                discount_percentage=$discount_percentage, 
                valid_from='$valid_from', 
                valid_to='$valid_to', 
                is_active=$is_active, 
                terms='$terms' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            $message = "Offer updated successfully!";
            $messageType = "success";
            echo "<script>setTimeout(()=>{window.location='admin_offers.php';},1500);</script>";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/edit_offer.css">
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Offer</h2>
        <p>Editing: <strong><?= htmlspecialchars($offer['title']) ?></strong></p>
    </div>
    
    <?php if($message): ?>
        <div class="message <?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>
    
    <form method="POST" class="offer-form" id="offerForm">
        <div class="form-section">
            <h3>Offer Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Offer Code <span class="required">*</span></label>
                    <input type="text" name="code" id="offerCode" value="<?= htmlspecialchars($offer['code']) ?>" required>
                    <small class="hint" id="codeError" style="color: var(--danger); display: none;"></small>
                    <small class="hint">Unique promotional code for this offer (uppercase letters and numbers only)</small>
                </div>
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="seasonal" <?= $offer['category'] == 'seasonal' ? 'selected' : '' ?>>Seasonal</option>
                        <option value="holiday" <?= $offer['category'] == 'holiday' ? 'selected' : '' ?>>Holiday</option>
                        <option value="corporate" <?= $offer['category'] == 'corporate' ? 'selected' : '' ?>>Corporate</option>
                        <option value="spa" <?= $offer['category'] == 'spa' ? 'selected' : '' ?>>Spa</option>
                        <option value="early_bird" <?= $offer['category'] == 'early_bird' ? 'selected' : '' ?>>Early Bird</option>
                        <option value="last_minute" <?= $offer['category'] == 'last_minute' ? 'selected' : '' ?>>Last Minute</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Offer Title <span class="required">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($offer['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <textarea name="description" rows="4" required><?= htmlspecialchars($offer['description']) ?></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Pricing & Discount</h3>
            <div class="form-group">
                <label>Discount Percentage <span class="required">*</span></label>
                <input type="number" step="0.01" name="discount_percentage" value="<?= $offer['discount_percentage'] ?>" min="0" max="100" required>
                <small class="hint">Enter discount percentage (e.g., 25 for 25% off)</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Validity Period</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Valid From <span class="required">*</span></label>
                    <input type="date" name="valid_from" value="<?= $offer['valid_from'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Valid To <span class="required">*</span></label>
                    <input type="date" name="valid_to" value="<?= $offer['valid_to'] ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Offer Image</h3>
            <div class="form-group">
                <label>Image Filename <span class="required">*</span></label>
                <input type="text" name="image" value="<?= htmlspecialchars($offer['image']) ?>" required>
                <div class="image-preview">
                    <img src="images/<?= htmlspecialchars($offer['image']) ?>" alt="Current offer image" onerror="this.src='images/default-offer.jpg'">
                    <span class="image-name"><?= htmlspecialchars($offer['image']) ?></span>
                </div>
                <small class="hint">Place image in the "images" folder</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Terms & Conditions</h3>
            <div class="form-group">
                <label>Terms</label>
                <textarea name="terms" rows="4" placeholder="Enter terms and conditions for this offer..."><?= htmlspecialchars($offer['terms'] ?? '') ?></textarea>
                <small class="hint">Optional: Add specific terms and conditions</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Status</h3>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?= $offer['is_active'] ? 'checked' : '' ?>>
                    Active (visible to customers)
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">Update Offer</button>
            <a href="admin_offers.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
const currentOfferId = <?= $id ?>;
const originalCode = "<?= htmlspecialchars($offer['code']) ?>";

// Real-time duplicate code checking for edit offer
document.getElementById('offerCode').addEventListener('blur', function() {
    const offerCode = this.value.trim().toUpperCase();
    const codeError = document.getElementById('codeError');
    
    // Skip check if code hasn't changed or is empty
    if (offerCode === originalCode || !offerCode) {
        codeError.style.display = 'none';
        document.querySelector('.btn-submit').disabled = false;
        document.querySelector('.btn-submit').style.opacity = '1';
        return;
    }
    
    if (offerCode) {
        fetch('check_offer_code.php?code=' + encodeURIComponent(offerCode) + '&exclude_id=' + currentOfferId)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    codeError.textContent = 'Offer code already exists! Please choose a different code.';
                    codeError.style.display = 'block';
                    document.querySelector('.btn-submit').disabled = true;
                    document.querySelector('.btn-submit').style.opacity = '0.5';
                } else {
                    codeError.style.display = 'none';
                    document.querySelector('.btn-submit').disabled = false;
                    document.querySelector('.btn-submit').style.opacity = '1';
                }
            });
    } else {
        codeError.style.display = 'none';
        document.querySelector('.btn-submit').disabled = false;
        document.querySelector('.btn-submit').style.opacity = '1';
    }
});

// Auto-uppercase for offer code
document.getElementById('offerCode').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Form validation before submit
document.getElementById('offerForm').addEventListener('submit', function(e) {
    const validFrom = new Date(document.querySelector('[name="valid_from"]').value);
    const validTo = new Date(document.querySelector('[name="valid_to"]').value);
    const codeError = document.getElementById('codeError');
    const discount = parseFloat(document.querySelector('[name="discount_percentage"]').value);
    const code = document.getElementById('offerCode').value.trim();
    
    if (!code) {
        e.preventDefault();
        alert('Please enter an offer code.');
        return;
    }
    
    if (validFrom > validTo) {
        e.preventDefault();
        alert('Valid From date cannot be later than Valid To date.');
        return;
    }
    
    if (discount <= 0 || discount > 100) {
        e.preventDefault();
        alert('Discount percentage must be between 1 and 100.');
        return;
    }
    
    if (codeError.style.display === 'block') {
        e.preventDefault();
        alert('Please fix the errors before submitting.');
    }
});
</script>

</body>
</html>