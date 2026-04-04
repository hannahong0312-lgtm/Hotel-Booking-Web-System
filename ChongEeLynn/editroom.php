<?php
include '../Shared/config.php';

$id = (int)$_GET['id'];
$result = $conn->query("SELECT * FROM rooms WHERE id = $id");
if ($result->num_rows == 0) die("Room not found");
$room = $result->fetch_assoc();

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
    
    $sql = "UPDATE rooms SET 
            name='$name', category='$category', description='$description', 
            price=$price, max_guests=$max_guests, bed_type='$bed_type', 
            size=$size, rooms_available=$rooms_available, image='$image', is_active=$is_active 
            WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Room updated successfully!";
        echo "<script>setTimeout(()=>{window.location='roommanagement.php';},1500);</script>";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Room</title>
    <link rel="stylesheet" href="css/addroom.css">
</head>
<body>
<div class="form-container">
    <h2>Edit Room: <?= htmlspecialchars($room['name']) ?></h2>
    <?php if($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Room Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($room['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($room['category']) ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" required><?= htmlspecialchars($room['description']) ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" value="<?= $room['price'] ?>" required>
            </div>
            <div class="form-group">
                <label>Max Guests</label>
                <input type="number" name="max_guests" value="<?= $room['max_guests'] ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Bed Type</label>
                <input type="text" name="bed_type" value="<?= htmlspecialchars($room['bed_type']) ?>" required>
            </div>
            <div class="form-group">
                <label>Size (sq ft)</label>
                <input type="number" name="size" value="<?= $room['size'] ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rooms Available</label>
                <input type="number" name="rooms_available" value="<?= $room['rooms_available'] ?>" required>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image" value="<?= htmlspecialchars($room['image']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?= $room['is_active'] ? 'checked' : '' ?>> Active
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit">Update Room</button>
            <a href="roommanagement.php" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>