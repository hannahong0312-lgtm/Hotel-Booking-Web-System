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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Admin Panel</title>
    <link rel="stylesheet" href="css/roommanagement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h1><i class="fas fa-hotel"></i> Room Management</h1>
            <p class="subtitle">Manage all hotel rooms, view details, and update status</p>
        </div>
        <div class="header-right">
            <div class="stats">
                <span class="stat-badge">
                    <i class="fas fa-bed"></i> Total: <?= $result->num_rows ?>
                </span>
            </div>
            <a href="addroom.php" class="btn-add">
                <i class="fas fa-plus"></i> Add New Room
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <input type="text" id="searchInput" placeholder="🔍 Search rooms..." class="search-input">
        <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <option value="standard">Standard</option>
            <option value="deluxe">Deluxe</option>
            <option value="family">Family</option>
            <option value="suite">Suite</option>
        </select>
        <select id="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>
    
    <div class="table-responsive">
        <table class="room-table" id="roomTable">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-image"></i> Images</th>
                    <th><i class="fas fa-tag"></i> Name</th>
                    <th><i class="fas fa-layer-group"></i> Category</th>
                    <th><i class="fas fa-align-left"></i> Description</th>
                    <th><i class="fas fa-dollar-sign"></i> Price</th>
                    <th><i class="fas fa-users"></i> Max Guests</th>
                    <th><i class="fas fa-bed"></i> Bed Type</th>
                    <th><i class="fas fa-ruler-combined"></i> Size</th>
                    <th><i class="fas fa-door-open"></i> Available</th>
                    <th><i class="fas fa-bath"></i> Bathroom</th>
                    <th><i class="fas fa-mug-hot"></i> Amenities</th>
                    <th><i class="fas fa-toggle-on"></i> Status</th>
                    <th><i class="fas fa-cog"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Reset result pointer to beginning
                $result->data_seek(0);
                while($row = $result->fetch_assoc()): 
                ?>
                <tr class="room-row" data-category="<?= $row['category'] ?>" data-status="<?= $row['is_active'] ?>">
                    <td class="id-cell">#<?= $row['id'] ?></td>
                    <td class="image-cell">
                        <div class="image-group">
                            <img src="images/<?= htmlspecialchars($row['image']) ?>" class="room-thumb" alt="room" onerror="this.src='images/default-room.jpg'">
                            <div class="image-hover">
                                <span><i class="fas fa-expand"></i> View</span>
                            </div>
                        </div>
                    </td>
                    <td class="name-cell"><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                        <span class="category-badge category-<?= $row['category'] ?>">
                            <?= ucfirst($row['category']) ?>
                        </span>
                    </td>
                    <td class="description-cell" title="<?= htmlspecialchars($row['description']) ?>">
                        <?= htmlspecialchars(substr($row['description'], 0, 60)) . (strlen($row['description']) > 60 ? '...' : '') ?>
                    </td>
                    <td class="price-cell">$<?= number_format($row['price'], 2) ?></td>
                    <td class="center"><i class="fas fa-user"></i> <?= $row['max_guests'] ?></td>
                    <td><?= htmlspecialchars($row['bed_type']) ?></td>
                    <td class="center"><?= $row['size'] ?> <small>sq ft</small></td>
                    <td class="center">
                        <span class="availability-badge <?= $row['rooms_available'] > 0 ? 'available' : 'soldout' ?>">
                            <?= $row['rooms_available'] ?> left
                        </span>
                    </td>
                    <td class="image-cell">
                        <img src="images/<?= htmlspecialchars($row['bathroom_image']) ?>" class="thumb-small" alt="bathroom" onerror="this.src='images/bathroom-default.jpg'">
                    </td>
                    <td class="image-cell">
                        <img src="images/<?= htmlspecialchars($row['amenities_image']) ?>" class="thumb-small" alt="amenities" onerror="this.src='images/tea-coffee-default.jpg'">
                    </td>
                    <td>
                        <button class="status-toggle" onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)">
                            <span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <i class="fas <?= $row['is_active'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </button>
                    </td>
                    <td class="actions">
                        <a href="editroom.php?id=<?= $row['id'] ?>" class="btn-edit" title="Edit Room">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)" class="btn-toggle" title="Toggle Status">
                            <i class="fas fa-sync-alt"></i> Toggle
                        </button>
                        <button onclick="deleteRoom(<?= $row['id'] ?>)" class="btn-delete" title="Delete Room">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows == 0): ?>
                <tr>
                    <td colspan="14" class="empty-state">
                        <i class="fas fa-bed fa-3x"></i>
                        <p>No rooms found. Click "Add New Room" to get started.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('.room-row');
    
    rows.forEach(row => {
        let name = row.querySelector('.name-cell').textContent.toLowerCase();
        let id = row.querySelector('.id-cell').textContent.toLowerCase();
        if(name.includes(searchValue) || id.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    filterTable();
});

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    filterTable();
});

function filterTable() {
    let category = document.getElementById('categoryFilter').value;
    let status = document.getElementById('statusFilter').value;
    let rows = document.querySelectorAll('.room-row');
    
    rows.forEach(row => {
        let showCategory = !category || row.dataset.category === category;
        let showStatus = !status || row.dataset.status === status;
        
        if(showCategory && showStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Toggle status with AJAX
function toggleStatus(id, currentStatus) {
    if(confirm('Toggle room status?')) {
        window.location.href = `?toggle=${id}`;
    }
}

// Delete room with confirmation
function deleteRoom(id) {
    if(confirm('⚠️ Delete this room permanently? This action cannot be undone!')) {
        window.location.href = `?delete=${id}`;
    }
}
</script>
</body>
</html>
<?php $conn->close(); ?>