<?php
include '../Shared/config.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $max_guests = (int)$_POST['max_guests'];
    $bed_type = $conn->real_escape_string($_POST['bed_type']);
    $size = (int)$_POST['size'];
    $rooms_available = (int)$_POST['rooms_available'];
    $image = $conn->real_escape_string($_POST['image']);
    $bathroom_image = $conn->real_escape_string($_POST['bathroom_image']);
    $amenities_image = $conn->real_escape_string($_POST['amenities_image']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "INSERT INTO rooms (name, category, description, price, max_guests, bed_type, size, rooms_available, image, bathroom_image, amenities_image, is_active) 
            VALUES ('$name', '$category', '$description', $price, $max_guests, '$bed_type', $size, $rooms_available, '$image', '$bathroom_image', '$amenities_image', $is_active)";
    
    if ($conn->query($sql)) {
        $message = "Room added successfully!";
        echo "<script>setTimeout(()=>{window.location='roommanagement.php';},1500);</script>";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room</title>
    <link rel="stylesheet" href="css/editroom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<!-- Back Button at the very top -->
<div class="top-bar">
    <a href="roommanagement.php" class="top-back-btn"><i class="fas fa-arrow-left"></i> Back to Room Management</a>
</div>

<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus-circle"></i> Add New Room</h2>
        <p>Fill in the details to add a new room to your hotel</p>
    </div>
    
    <?php if($message): ?>
        <div class="message success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
    <?php endif; ?>
    
    <form method="POST" class="room-form">
        <!-- Basic Information -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Room Name <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="e.g., Ocean View Suite" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="family">Family</option>
                        <option value="suite">Suite</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description <span class="required">*</span></label>
                <textarea name="description" rows="4" placeholder="Describe the room features, amenities, and highlights..." required></textarea>
            </div>
        </div>
        
        <!-- Pricing & Capacity -->
        <div class="form-section">
            <h3><i class="fas fa-chart-line"></i> Pricing & Capacity</h3>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Price per Night <span class="required">*</span></label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Max Guests <span class="required">*</span></label>
                    <input type="number" name="max_guests" placeholder="e.g., 4" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-bed"></i> Bed Type <span class="required">*</span></label>
                    <input type="text" name="bed_type" placeholder="e.g., King, Queen, Twin" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-ruler-combined"></i> Size (sq ft) <span class="required">*</span></label>
                    <input type="number" name="size" placeholder="e.g., 350" required>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-door-open"></i> Rooms Available <span class="required">*</span></label>
                <input type="number" name="rooms_available" placeholder="Number of rooms of this type" required>
            </div>
        </div>
        
        <!-- Images (ALL REQUIRED) -->
        <div class="form-section">
            <h3><i class="fas fa-images"></i> Room Images <span class="required">*</span></h3>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Main Room Image <span class="required">*</span></label>
                    <input type="text" name="image" placeholder="filename.jpg (e.g., deluxe-room.jpg)" required>
                    <small class="hint">Place image in the "images" folder - REQUIRED</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-bath"></i> Bathroom Image <span class="required">*</span></label>
                    <input type="text" name="bathroom_image" placeholder="bathroom-default.jpg" required>
                    <small class="hint">Place image in the "images" folder - REQUIRED</small>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-mug-hot"></i> Amenities Image <span class="required">*</span></label>
                <input type="text" name="amenities_image" placeholder="tea-coffee-default.jpg" required>
                <small class="hint">Place image in the "images" folder - REQUIRED</small>
            </div>
        </div>
        
        <!-- Status -->
        <div class="form-section">
            <h3><i class="fas fa-toggle-on"></i> Status</h3>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" checked>
                    <span class="checkmark"></span>
                    Active (visible to customers)
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Room</button>
            <a href="roommanagement.php" class="btn-cancel"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>