<?php
// roommanagement.php - Grand Hotel Melaka

require_once __DIR__ . '/../Shared/config.php';

// Get current filter values from URL (preserve them after toggle)
$current_category = isset($_GET['category']) ? $_GET['category'] : '';
$current_status = isset($_GET['status']) ? $_GET['status'] : '';
$current_search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle toggle active status - PRESERVE FILTERS
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    $stmt = $conn->prepare("UPDATE rooms SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    // Build redirect URL with preserved filters
    $redirect = "roommanagement.php";
    $params = [];
    if ($current_category) $params[] = "category=" . urlencode($current_category);
    if ($current_status !== '') $params[] = "status=" . urlencode($current_status);
    if ($current_search) $params[] = "search=" . urlencode($current_search);
    
    if (count($params) > 0) {
        $redirect .= "?" . implode("&", $params);
    }
    
    header("Location: $redirect");
    exit();
}

// Build query with filters
$sql = "SELECT * FROM rooms";
$where = [];
$params = [];
$types = "";

if ($current_category !== '') {
    $where[] = "category = ?";
    $params[] = $current_category;
    $types .= "s";
}
if ($current_status !== '') {
    $where[] = "is_active = ?";
    $params[] = $current_status;
    $types .= "i";
}

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY id DESC";

// Execute filtered query
if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get total count for stats
$total_result = $conn->query("SELECT COUNT(*) as total FROM rooms");
$total_rooms = $total_result->fetch_assoc()['total'];

require_once __DIR__ . '/../ChangJingEn/admin_header.php';
?>

<link rel="stylesheet" href="css/roommanagement.css">

<div class="rooms-container">
    <div class="page-header">
        <div class="header-left">
            <h1>Room Management</h1>
            <p class="subtitle">Manage all hotel rooms, view details, and update status</p>
        </div>
        <div class="header-right">
            <div class="stats">
                <span class="stat-badge">
                    Showing: <?= $result->num_rows ?> / <?= $total_rooms ?> rooms
                </span>
            </div>
            <a href="addroom.php" class="btn-add">
                Add New Room
            </a>
        </div>
    </div>

    <!-- Filters - values preserved from URL -->
    <div class="filters">
        <input type="text" id="searchInput" placeholder="Search rooms..." class="search-input" value="<?= htmlspecialchars($current_search) ?>">
        <select id="categoryFilter" class="filter-select">
            <option value="">All Categories</option>
            <option value="standard" <?= $current_category == 'standard' ? 'selected' : '' ?>>Standard</option>
            <option value="deluxe" <?= $current_category == 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
            <option value="family" <?= $current_category == 'family' ? 'selected' : '' ?>>Family</option>
            <option value="suite" <?= $current_category == 'suite' ? 'selected' : '' ?>>Suite</option>
        </select>
        <select id="statusFilter" class="filter-select">
            <option value="" <?= $current_status == '' ? 'selected' : '' ?>>All Status</option>
            <option value="1" <?= $current_status == '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $current_status == '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <button id="searchBtn" class="btn-search">Search</button>
        <button id="resetBtn" class="btn-reset">Reset</button>
    </div>
    
    <div class="table-responsive">
        <table class="room-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Images</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Max Guests</th>
                    <th>Bed Type</th>
                    <th>Size</th>
                    <th>Available</th>
                    <th>Bathroom</th>
                    <th>Amenities</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        // Apply search filter in PHP (client-side search)
                        $matches_search = true;
                        if ($current_search !== '') {
                            $matches_search = stripos($row['name'], $current_search) !== false || 
                                            stripos($row['id'], $current_search) !== false;
                        }
                        if (!$matches_search) continue;
                    ?>
                    <tr class="room-row" data-category="<?= htmlspecialchars($row['category']) ?>" data-status="<?= $row['is_active'] ?>">
                        <td class="id-cell"><?= $row['id'] ?></td>
                        <td class="image-cell">
                            <div class="image-group">
                                <img src="images/<?= htmlspecialchars($row['image']) ?>" class="room-thumb" alt="room" onerror="this.src='images/default-room.jpg'">
                                <div class="image-hover">
                                    <span>View</span>
                                </div>
                            </div>
                        </td>
                        <td class="name-cell"><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            <span class="category-badge category-<?= htmlspecialchars($row['category']) ?>">
                                <?= ucfirst(htmlspecialchars($row['category'])) ?>
                            </span>
                        </td>
                        <td class="description-cell" title="<?= htmlspecialchars($row['description']) ?>">
                            <?= htmlspecialchars(substr($row['description'], 0, 60)) . (strlen($row['description']) > 60 ? '...' : '') ?>
                        </td>
                        <td class="price-cell">$<?= number_format($row['price'], 2) ?></td>
                        <td class="center"><?= $row['max_guests'] ?></td>
                        <td><?= htmlspecialchars($row['bed_type']) ?></td>
                        <td class="center"><?= $row['size'] ?> sq ft</td>
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
                                    <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </button>
                        </td>
                        <td class="actions">
                            <a href="editroom.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                            <button onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)" class="btn-toggle">Toggle</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="empty-state">
                            <p>No rooms found. Click "Add New Room" to get started.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Get current filter values
let currentCategory = "<?= $current_category ?>";
let currentStatus = "<?= $current_status ?>";
let currentSearch = "<?= htmlspecialchars($current_search) ?>";

// Function to update URL with filters and reload
function updateFilters() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    let url = 'roommanagement.php?';
    let params = [];
    
    if (search) params.push('search=' + encodeURIComponent(search));
    if (category) params.push('category=' + encodeURIComponent(category));
    if (status !== '') params.push('status=' + encodeURIComponent(status));
    
    if (params.length > 0) {
        url += params.join('&');
    } else {
        url = 'roommanagement.php';
    }
    
    window.location.href = url;
}

// Reset all filters
function resetFilters() {
    window.location.href = 'roommanagement.php';
}

// Toggle status - preserves current filters
function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus ? 'inactive' : 'active';
    if(confirm(`Set this room to ${newStatus}? Inactive rooms keep their booking history but won't appear in the booking system.`)) {
        let url = '?toggle=' + id;
        
        // Preserve current filters
        if (currentCategory) url += '&category=' + encodeURIComponent(currentCategory);
        if (currentStatus !== '') url += '&status=' + encodeURIComponent(currentStatus);
        if (currentSearch) url += '&search=' + encodeURIComponent(currentSearch);
        
        window.location.href = url;
    }
}

// Event listeners
document.getElementById('searchBtn').addEventListener('click', function() {
    updateFilters();
});

document.getElementById('resetBtn').addEventListener('click', function() {
    resetFilters();
});

// Press Enter in search input to search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        updateFilters();
    }
});

// Category and status change automatically
document.getElementById('categoryFilter').addEventListener('change', function() {
    updateFilters();
});

document.getElementById('statusFilter').addEventListener('change', function() {
    updateFilters();
});
</script>