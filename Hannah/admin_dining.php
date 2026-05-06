<?php
ob_start(); // Start output buffering
include '../Shared/config.php';
include '../ChangJingEn/admin_header.php';

/*Get distinct restaurant names from dining table for filter dropdown.*/
function getDistinctRestaurants($conn) {
    $result = $conn->query("SELECT DISTINCT name FROM dining ORDER BY name");
    $restaurants = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row['name'];
        }
    }
    return $restaurants;
}

/*Get all possible statuses for filter (based on existing data and logical statuses). */
function getStatusOptions() {
    return ['confirmed', 'cancelled', 'completed', 'pending'];
}

// --- Process POST Actions (Update / Delete) ---
$redirect_params = $_GET; // preserve current GET filters for redirect
unset($redirect_params['update_success'], $redirect_params['delete_success'], $redirect_params['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Update Reservation
        if ($action === 'update' && isset($_POST['reservation_id'])) {
            $id = intval($_POST['reservation_id']);
            $restaurant_name = cleanInput($_POST['restaurant_name'] ?? '');
            $first_name = cleanInput($_POST['first_name'] ?? '');
            $last_name = cleanInput($_POST['last_name'] ?? '');
            $phone = cleanInput($_POST['phone'] ?? '');
            $email = cleanInput($_POST['email'] ?? '');
            $date = cleanInput($_POST['date'] ?? '');
            $time = cleanInput($_POST['time'] ?? '');
            $guests = intval($_POST['guests'] ?? 0);
            $special_requests = cleanInput($_POST['special_requests'] ?? '');
            $status = cleanInput($_POST['status'] ?? 'confirmed');
            
            $errors = [];
            if (empty($restaurant_name)) $errors[] = "Restaurant name is required.";
            if (empty($first_name)) $errors[] = "First name is required.";
            if (empty($last_name)) $errors[] = "Last name is required.";
            if (empty($phone)) $errors[] = "Phone number is required.";
            if (empty($email)) $errors[] = "Email is required.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
            if (empty($date)) $errors[] = "Date is required.";
            if (empty($time)) $errors[] = "Time is required.";
            if ($guests < 1 || $guests > 50) $errors[] = "Guests must be between 1 and 50.";
            
            if (empty($errors)) {
                $sql = "UPDATE dining SET 
                            name = ?,
                            first_name = ?,
                            last_name = ?,
                            phone = ?,
                            email = ?,
                            date = ?,
                            time = ?,
                            guests = ?,
                            special_requests = ?,
                            status = ?
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssissi", 
                    $restaurant_name, $first_name, $last_name, $phone, $email,
                    $date, $time, $guests, $special_requests, $status, $id
                );
                if ($stmt->execute()) {
                    $redirect_params['update_success'] = 1;
                } else {
                    $redirect_params['error'] = "Database error: " . urlencode($stmt->error);
                }
                $stmt->close();
            } else {
                $redirect_params['error'] = urlencode(implode(", ", $errors));
            }
            // Redirect to avoid form resubmission
            header("Location: admin_dining.php?" . http_build_query($redirect_params));
            exit;
        }
        
        // Delete Reservation
        elseif ($action === 'delete' && isset($_POST['delete_id'])) {
            $id = intval($_POST['delete_id']);
            $sql = "DELETE FROM dining WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $redirect_params['delete_success'] = 1;
            } else {
                $redirect_params['error'] = "Could not delete reservation: " . urlencode($stmt->error);
            }
            $stmt->close();
            header("Location: admin_dining.php?" . http_build_query($redirect_params));
            exit;
        }
    }
}

// --- Filters & Pagination Setup ---

$filter_restaurant = isset($_GET['restaurant']) ? cleanInput($_GET['restaurant']) : '';
$filter_status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$filter_date_from = isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : '';
$search_term = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build WHERE clause for filtering
$where_conditions = [];
$params = [];
$types = "";

if (!empty($filter_restaurant)) {
    $where_conditions[] = "name = ?";
    $params[] = $filter_restaurant;
    $types .= "s";
}
if (!empty($filter_status)) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
if (!empty($filter_date_from)) {
    $where_conditions[] = "date >= ?";
    $params[] = $filter_date_from;
    $types .= "s";
}
if (!empty($filter_date_to)) {
    $where_conditions[] = "date <= ?";
    $params[] = $filter_date_to;
    $types .= "s";
}
if (!empty($search_term)) {
    $where_conditions[] = "(code LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search_term%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sssss";
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM dining $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);
$count_stmt->close();

// Fetch reservations for current page
$sql = "SELECT id, code, name, first_name, last_name, phone, email, date, time, guests, special_requests, status, created_at 
        FROM dining $where_sql 
        ORDER BY date DESC, time DESC 
        LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result();
$stmt->close();

// Get stats for summary cards
$stats_sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(guests) as total_guests
              FROM dining $where_sql";
$stats_stmt = $conn->prepare($stats_sql);
if (!empty($params_without_limit)) {
    // Need to re-bind without limit/offset for stats
    $stats_params = array_slice($params, 0, count($params)-2);
    $stats_types = substr($types, 0, -2);
    if (!empty($stats_params)) {
        $stats_stmt->bind_param($stats_types, ...$stats_params);
    }
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

$distinct_restaurants = getDistinctRestaurants($conn);
$status_options = getStatusOptions();
$restaurant_dropdown = $distinct_restaurants; // for filter

// Predefined restaurant full names for edit dropdown (same as frontend)
$restaurant_edit_options = [
    'Royale Restaurant' => 'Royale Restaurant',
    'The Grand Buffet' => 'The Grand Buffet',
    'Rooftop Bar' => 'Rooftop Bar'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dining | Grand Hotel</title>
    <link rel="stylesheet" href="css/admin_dining.css">
</head>
<body>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Reservations</div><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-number"><?php echo number_format($stats['total'] ?? 0); ?></div>
        <div class="stat-trend"><i class="fas fa-utensils"></i> All dining</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Confirmed</div><i class="fas fa-check-circle"></i></div>
        <div class="stat-number"><?php echo number_format($stats['confirmed'] ?? 0); ?></div>
        <div class="stat-trend"><i class="fas fa-clock"></i> Active reservations</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Cancelled</div><i class="fas fa-times-circle"></i></div>
        <div class="stat-number"><?php echo number_format($stats['cancelled'] ?? 0); ?></div>
        <div class="stat-trend"><i class="fas fa-chart-line"></i> Non-active</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Guests</div><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo number_format($stats['total_guests'] ?? 0); ?></div>
        <div class="stat-trend"><i class="fas fa-chart-simple"></i> Cumulative covers</div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_GET['update_success'])): ?>
    <div class="alert-success"><i class="fas fa-check-circle"></i> Reservation updated successfully!</div>
<?php elseif (isset($_GET['delete_success'])): ?>
    <div class="alert-success"><i class="fas fa-trash-alt"></i> Reservation deleted successfully.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
<?php endif; ?>

<!-- Filter Bar -->
<div class="filters-bar">
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Restaurant</label>
            <select name="restaurant">
                <option value="">All Restaurants</option>
                <?php foreach ($distinct_restaurants as $rest): ?>
                    <option value="<?php echo htmlspecialchars($rest); ?>" <?php echo ($filter_restaurant == $rest) ? 'selected' : ''; ?>><?php echo htmlspecialchars($rest); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <?php foreach ($status_options as $stat): ?>
                    <option value="<?php echo $stat; ?>" <?php echo ($filter_status == $stat) ? 'selected' : ''; ?>><?php echo ucfirst($stat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
        </div>
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
        </div>
        <div class="filter-group search-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="Reservation code, name, email..." value="<?php echo htmlspecialchars($search_term); ?>">
        </div>
        <div class="filter-group">
            <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
            <a href="admin_dining.php" class="btn-reset" style="display: inline-block; text-align: center; padding: 8px 16px; margin-left: 8px;">Reset</a>
        </div>
    </form>
</div>

<!-- Reservations Table -->
<div class="recent-section">
    <div class="section-title"><i class="fas fa-utensils"></i> Dining Reservations</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Code</th><th>Restaurant</th><th>Customer</th><th>Date</th><th>Time</th><th>Guests</th><th>Phone</th><th>Email</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if ($reservations && $reservations->num_rows > 0): ?>
                    <?php while ($row = $reservations->fetch_assoc()): 
                        $fullname = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                        $reservation_data = [
                            'id' => $row['id'],
                            'restaurant_name' => $row['name'],
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'],
                            'phone' => $row['phone'],
                            'email' => $row['email'],
                            'date' => $row['date'],
                            'time' => $row['time'],
                            'guests' => $row['guests'],
                            'special_requests' => $row['special_requests'],
                            'status' => $row['status']
                        ];
                    ?>
                    <tr data-reservation='<?php echo htmlspecialchars(json_encode($reservation_data)); ?>'>
                        <td><?php echo $row['id']; ?></td>
                        <td><span class="ref-link"><?php echo htmlspecialchars($row['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo $fullname; ?></td>
                        <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($row['time'])); ?></td>
                        <td><?php echo $row['guests']; ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td class="action-buttons">
                            <button class="btn-icon btn-edit" onclick="openEditModal(this)"><i class="fas fa-edit"></i> Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this reservation permanently?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-icon btn-delete"><i class="fas fa-trash-alt"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align: center; padding: 48px;">No dining reservations found matching filters.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 28px;">
        <?php
        $query_params = $_GET;
        unset($query_params['page']);
        $base_url = "admin_dining.php?" . http_build_query($query_params);
        for ($i = 1; $i <= $total_pages; $i++):
            $active = ($i == $page) ? 'active' : '';
        ?>
            <a href="<?php echo $base_url . '&page=' . $i; ?>" style="padding: 6px 12px; border-radius: 30px; background: <?php echo $i==$page ? 'var(--gold)' : 'var(--bg-sidebar)'; ?>; border:1px solid var(--border-light);"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Reservation</h3>
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="reservation_id" id="edit_id">
            <div class="form-edit-grid">
                <div class="full-width">
                    <label>Restaurant *</label>
                    <select name="restaurant_name" id="edit_restaurant" required>
                        <?php foreach ($restaurant_edit_options as $val => $display): ?>
                            <option value="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($display); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label>First Name *</label><input type="text" name="first_name" id="edit_first_name" required></div>
                <div><label>Last Name *</label><input type="text" name="last_name" id="edit_last_name" required></div>
                <div><label>Phone *</label><input type="tel" name="phone" id="edit_phone" required></div>
                <div><label>Email *</label><input type="email" name="email" id="edit_email" required></div>
                <div><label>Date *</label><input type="date" name="date" id="edit_date" required></div>
                <div><label>Time *</label><input type="time" name="time" id="edit_time" required></div>
                <div><label>Guests (1-50) *</label><input type="number" name="guests" id="edit_guests" min="1" max="50" required></div>
                <div><label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="full-width"><label>Special Requests</label><textarea name="special_requests" id="edit_requests" rows="2"></textarea></div>
            </div>
            <div class="edit-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>

<script>
    // Edit Modal Functions
    const modal = document.getElementById('editModal');
    let currentRow = null;
    
    function openEditModal(btn) {
        const row = btn.closest('tr');
        if (!row) return;
        currentRow = row;
        const data = JSON.parse(row.getAttribute('data-reservation'));
        
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_restaurant').value = data.restaurant_name;
        document.getElementById('edit_first_name').value = data.first_name;
        document.getElementById('edit_last_name').value = data.last_name;
        document.getElementById('edit_phone').value = data.phone;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_date').value = data.date;
        document.getElementById('edit_time').value = data.time.substring(0,5);
        document.getElementById('edit_guests').value = data.guests;
        document.getElementById('edit_status').value = data.status;
        document.getElementById('edit_requests').value = data.special_requests || '';
        
        modal.classList.add('active');
    }
    
    function closeEditModal() {
        modal.classList.remove('active');
    }
    
    window.onclick = function(event) {
        if (event.target === modal) closeEditModal();
    }
    
    // Auto-remove success/error messages after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success, .alert-error');
        alerts.forEach(alert => alert.style.display = 'none');
    }, 5000);
</script>

<?php
ob_end_flush();
?>