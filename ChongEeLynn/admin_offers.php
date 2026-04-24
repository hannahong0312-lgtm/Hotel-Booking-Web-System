<?php
// admin_offers.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM hotel_offers WHERE id = $id");
    header("Location: admin_offers.php");
    exit();
}

// Handle toggle active status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE hotel_offers SET is_active = NOT is_active WHERE id = $id");
    header("Location: admin_offers.php");
    exit();
}

$result = $conn->query("SELECT * FROM hotel_offers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offer Management - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/admin_offers.css">
</head>
<body>
<div class="offers-container">
    <div class="page-header">
        <div class="header-left">
            <h1>Offer Management</h1>
            <p class="subtitle">Manage all hotel promotions, discounts, and special offers</p>
        </div>
        <div class="header-right">
            <div class="stats">
                <span class="stat-badge">
                    Total Offers: <?= $result->num_rows ?>
                </span>
            </div>
            <a href="add_offer.php" class="btn-add">
                Add New Offer
            </a>
        </div>
    </div>

    <div class="filters">
        <input type="text" id="searchInput" placeholder="Search offers..." class="search-input">
        <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <option value="seasonal">Seasonal</option>
            <option value="holiday">Holiday</option>
            <option value="corporate">Corporate</option>
            <option value="spa">Spa</option>
            <option value="early_bird">Early Bird</option>
            <option value="last_minute">Last Minute</option>
        </select>
        <select id="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>
    
    <div class="table-responsive">
        <table class="offers-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $result->data_seek(0);
                while($row = $result->fetch_assoc()): 
                ?>
                <tr class="offer-row" data-category="<?= $row['category'] ?>" data-status="<?= $row['is_active'] ?>">
                    <td class="id-cell"><?= $row['id'] ?></td>
                    <td class="code-cell"><?= htmlspecialchars($row['code']) ?></td>
                    <td class="image-cell">
                        <div class="image-group">
                            <img src="images/<?= htmlspecialchars($row['image']) ?>" class="offer-thumb" alt="offer" onerror="this.src='images/default-offer.jpg'">
                            <div class="image-hover">
                                <span>View</span>
                            </div>
                        </div>
                    </td>
                    <td class="title-cell"><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <span class="category-badge category-<?= $row['category'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $row['category'])) ?>
                        </span>
                    </td>
                    <td class="description-cell" title="<?= htmlspecialchars($row['description']) ?>">
                        <?= htmlspecialchars(substr($row['description'], 0, 60)) . (strlen($row['description']) > 60 ? '...' : '') ?>
                    </td>
                    <td class="discount-cell"><?= $row['discount_percentage'] ?>%</td>
                    <td class="date-cell"><?= date('d M Y', strtotime($row['valid_from'])) ?></td>
                    <td class="date-cell"><?= date('d M Y', strtotime($row['valid_to'])) ?></td>
                    <td>
                        <button class="status-toggle" onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)">
                            <span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </button>
                    </td>
                    <td class="actions">
                        <a href="edit_offer.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                        <button onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)" class="btn-toggle">Toggle</button>
                        <button onclick="deleteOffer(<?= $row['id'] ?>)" class="btn-delete">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows == 0): ?>
                <tr>
                    <td colspan="11" class="empty-state">
                        <p>No offers found. Click "Add New Offer" to get started.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('.offer-row');
    
    rows.forEach(row => {
        let title = row.querySelector('.title-cell').textContent.toLowerCase();
        let code = row.querySelector('.code-cell').textContent.toLowerCase();
        let id = row.querySelector('.id-cell').textContent.toLowerCase();
        if(title.includes(searchValue) || code.includes(searchValue) || id.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('categoryFilter').addEventListener('change', function() {
    let category = this.value;
    let rows = document.querySelectorAll('.offer-row');
    
    rows.forEach(row => {
        let showCategory = !category || row.dataset.category === category;
        if(showCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('statusFilter').addEventListener('change', function() {
    let status = this.value;
    let rows = document.querySelectorAll('.offer-row');
    
    rows.forEach(row => {
        let showStatus = !status || row.dataset.status === status;
        if(showStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function toggleStatus(id, currentStatus) {
    if(confirm('Toggle offer status?')) {
        window.location.href = '?toggle=' + id;
    }
}

function deleteOffer(id) {
    if(confirm('Delete this offer permanently? This action cannot be undone!')) {
        window.location.href = '?delete=' + id;
    }
}
</script>
</body>
</html>