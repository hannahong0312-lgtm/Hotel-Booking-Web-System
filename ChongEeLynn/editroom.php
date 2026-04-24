<?php
// editroom.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM rooms WHERE id = $id");
if ($result->num_rows == 0) die("Room not found");
$room = $result->fetch_assoc();

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
    
    $sql = "UPDATE rooms SET 
            name='$name', 
            category='$category', 
            description='$description', 
            price=$price, 
            max_guests=$max_guests, 
            bed_type='$bed_type', 
            size=$size, 
            rooms_available=$rooms_available, 
            image='$image', 
            bathroom_image='$bathroom_image', 
            amenities_image='$amenities_image', 
            is_active=$is_active 
            WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Room updated successfully!";
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
    <title>Edit Room - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/editroom.css">
</head>
<body>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Room</h2>
        <p>Editing: <strong><?= htmlspecialchars($room['name']) ?></strong></p>
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
                    <input type="text" name="name" value="<?= htmlspecialchars($room['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="standard" <?= $room['category'] == 'standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="deluxe" <?= $room['category'] == 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
                        <option value="family" <?= $room['category'] == 'family' ? 'selected' : '' ?>>Family</option>
                        <option value="suite" <?= $room['category'] == 'suite' ? 'selected' : '' ?>>Suite</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <textarea name="description" rows="4" required><?= htmlspecialchars($room['description']) ?></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Pricing & Capacity</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Price per Night <span class="required">*</span></label>
                    <input type="number" step="0.01" name="price" value="<?= $room['price'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Max Guests <span class="required">*</span></label>
                    <input type="number" name="max_guests" value="<?= $room['max_guests'] ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Bed Type <span class="required">*</span></label>
                    <input type="text" name="bed_type" value="<?= htmlspecialchars($room['bed_type']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Size (sq ft) <span class="required">*</span></label>
                    <input type="number" name="size" value="<?= $room['size'] ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Rooms Available <span class="required">*</span></label>
                <input type="number" name="rooms_available" value="<?= $room['rooms_available'] ?>" required>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Room Images</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Main Room Image <span class="required">*</span></label>
                    <input type="text" name="image" value="<?= htmlspecialchars($room['image']) ?>" required>
                    <div class="image-preview">
                        <img src="images/<?= htmlspecialchars($room['image']) ?>" alt="Current room image" onerror="this.src='images/default-room.jpg'">
                        <span class="image-name"><?= htmlspecialchars($room['image']) ?></span>
                    </div>
                    <small class="hint">Place image in the "images" folder</small>
                </div>
                <div class="form-group">
                    <label>Bathroom Image <span class="required">*</span></label>
                    <input type="text" name="bathroom_image" value="<?= htmlspecialchars($room['bathroom_image']) ?>" required>
                    <div class="image-preview">
                        <img src="images/<?= htmlspecialchars($room['bathroom_image']) ?>" alt="Current bathroom image" onerror="this.src='images/bathroom-default.jpg'">
                        <span class="image-name"><?= htmlspecialchars($room['bathroom_image']) ?></span>
                    </div>
                    <small class="hint">Place image in the "images" folder</small>
                </div>
            </div>
            <div class="form-group">
                <label>Amenities Image <span class="required">*</span></label>
                <input type="text" name="amenities_image" value="<?= htmlspecialchars($room['amenities_image']) ?>" required>
                <div class="image-preview">
                    <img src="images/<?= htmlspecialchars($room['amenities_image']) ?>" alt="Current amenities image" onerror="this.src='images/tea-coffee-default.jpg'">
                    <span class="image-name"><?= htmlspecialchars($room['amenities_image']) ?></span>
                </div>
                <small class="hint">Place image in the "images" folder</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Status</h3>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?= $room['is_active'] ? 'checked' : '' ?>>
                    Active (visible to customers)
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">Update Room</button>
            <a href="roommanagement.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>