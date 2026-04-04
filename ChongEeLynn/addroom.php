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
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "INSERT INTO rooms (name, category, description, price, max_guests, bed_type, size, rooms_available, image, is_active) 
            VALUES ('$name', '$category', '$description', $price, $max_guests, '$bed_type', $size, $rooms_available, '$image', $is_active)";
    
    if ($conn->query($sql)) {
        $message = "Room added successfully!";
        echo "<script>setTimeout(()=>{window.location='roommanagement.php';},1500);</script>";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Room</title>
    <link rel="stylesheet" href="css/addroom.css">
</head>
<body>
<div class="form-container">
    <h2>Add New Room</h2>
    <?php if($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Room Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>Max Guests</label>
                <input type="number" name="max_guests" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Bed Type</label>
                <input type="text" name="bed_type" placeholder="e.g., Queen, Twin, King" required>
            </div>
            <div class="form-group">
                <label>Size (sq ft)</label>
                <input type="number" name="size" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rooms Available</label>
                <input type="number" name="rooms_available" required>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image" placeholder="https://example.com/room.jpg">
            </div>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" checked> Active (visible to customers)
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit">Save Room</button>
            <a href="roommanagement.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>