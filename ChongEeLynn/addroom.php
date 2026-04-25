<?php
// addroom.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

$message = "";
$messageType = "";

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
        $messageType = "success";
        echo "<script>setTimeout(()=>{window.location='roommanagement.php';},1500);</script>";
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
    <title>Add New Room - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/editroom.css">
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Add New Room</h2>
        <p>Fill in the details to add a new room to your hotel</p>
    </div>
    
    <?php if($message): ?>
        <div class="message <?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>
    
    <form method="POST" class="room-form">
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Room Name <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="e.g., Ocean View Suite" required>
                </div>
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
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
                <label>Description <span class="required">*</span></label>
                <textarea name="description" rows="4" placeholder="Describe the room features, amenities, and highlights..." required></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Pricing & Capacity</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Price per Night <span class="required">*</span></label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Max Guests <span class="required">*</span></label>
                    <input type="number" name="max_guests" placeholder="e.g., 4" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Bed Type <span class="required">*</span></label>
                    <input type="text" name="bed_type" placeholder="e.g., King, Queen, Twin" required>
                </div>
                <div class="form-group">
                    <label>Size (sq ft) <span class="required">*</span></label>
                    <input type="number" name="size" placeholder="e.g., 350" required>
                </div>
            </div>
            <div class="form-group">
                <label>Rooms Available <span class="required">*</span></label>
                <input type="number" name="rooms_available" placeholder="Number of rooms of this type" required>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Room Images</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Main Room Image <span class="required">*</span></label>
                    <input type="text" name="image" placeholder="filename.jpg (e.g., deluxe-room.jpg)" required>
                    <small class="hint">Place image in the "images" folder</small>
                </div>
                <div class="form-group">
                    <label>Bathroom Image <span class="required">*</span></label>
                    <input type="text" name="bathroom_image" placeholder="bathroom-default.jpg" required>
                    <small class="hint">Place image in the "images" folder</small>
                </div>
            </div>
            <div class="form-group">
                <label>Amenities Image <span class="required">*</span></label>
                <input type="text" name="amenities_image" placeholder="tea-coffee-default.jpg" required>
                <small class="hint">Place image in the "images" folder</small>
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
            <button type="submit" class="btn-submit">Save Room</button>
            <a href="roommanagement.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>