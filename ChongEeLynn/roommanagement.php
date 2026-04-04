<?php

include '../Shared/config.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM rooms WHERE id = $id");
    header("Location: roommanagement.php");
    exit();
}

// Handle toggle active status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE rooms SET is_active = NOT is_active WHERE id = $id");
    header("Location: roommanagement.php");
    exit();
}

$result = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="css/roommanagement.css">
</head>
<body>
<div class="container">
    <h1>Room Management</h1>
    <a href="addroom.php" class="btn-add">+ Add New Room</a>
    
    <table class="room-table">
        <thead>
            <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Max Guests</th><th>Bed Type</th><th>Size</th><th>Available</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><img src="<?= htmlspecialchars($row['image']) ?>" class="room-thumb" alt="room"></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <td><?= $row['max_guests'] ?></td>
                <td><?= htmlspecialchars($row['bed_type']) ?></td>
                <td><?= $row['size'] ?> sq ft</td>
                <td><?= $row['rooms_available'] ?></td>
                <td class="status-<?= $row['is_active'] ? 'active' : 'inactive' ?>">
                    <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                </td>
                <td class="actions">
                    <a href="editroom.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                    <a href="?toggle=<?= $row['id'] ?>" class="btn-toggle">Toggle</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this room?')" class="btn-delete">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>