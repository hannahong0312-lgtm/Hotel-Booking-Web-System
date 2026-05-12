<?php
// admin_facilities.php - Grand Hotel Melaka
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once 'admin_header.php';

if ($admin_role !== 'superadmin' && $admin_role !== 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE facilities SET is_active = NOT is_active WHERE id = $id");
    header("Location: admin_facilities.php");
    exit();
}

$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_facility'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $hours = trim($_POST['hours']);
    $feature1 = trim($_POST['feature1']);
    $feature2 = trim($_POST['feature2']);
    $display_order = (int)$_POST['display_order'];
    $reverse_layout = isset($_POST['reverse_layout']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Image upload
    $image_path = $_POST['old_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $new_name = 'fac_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $new_name)) {
                if (!empty($image_path) && file_exists(__DIR__ . '/' . $image_path)) {
                    unlink(__DIR__ . '/' . $image_path);
                }
                $image_path = $new_name;
            } else {
                $message = "Image upload failed. Please check directory permissions.";
                $messageType = "error";
            }
        } else {
            $message = "Unsupported image format. Please use JPG, PNG, GIF or WebP.";
            $messageType = "error";
        }
    }

    if (empty($category) || empty($description)) {
        $message = "Category and Description are required.";
        $messageType = "error";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE facilities SET category=?, description=?, image_path=?, hours=?, feature1=?, feature2=?, display_order=?, reverse_layout=?, is_active=? WHERE id=?");
            $stmt->bind_param("sssssssiii", $category, $description, $image_path, $hours, $feature1, $feature2, $display_order, $reverse_layout, $is_active, $id);
            if ($stmt->execute()) {
                $message = "Facility updated successfully!";
                $messageType = "success";
            } else {
                $message = "Update failed: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO facilities (category, description, image_path, hours, feature1, feature2, display_order, reverse_layout, is_active) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssssii", $category, $description, $image_path, $hours, $feature1, $feature2, $display_order, $reverse_layout, $is_active);
            if ($stmt->execute()) {
                $message = "Facility added successfully!";
                $messageType = "success";
            } else {
                $message = "Add failed: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
    if ($messageType === 'success') {
        echo "<script>setTimeout(()=>{window.location='admin_facilities.php';}, 1000);</script>";
        exit;
    }
}

$result = $conn->query("SELECT * FROM facilities ORDER BY display_order ASC, id ASC");
$total = $result->num_rows;
$activeCount = $conn->query("SELECT COUNT(*) FROM facilities WHERE is_active = 1")->fetch_row()[0];
$inactiveCount = $total - $activeCount;

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities Management - Grand Hotel Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --bg-body: #f5f5f5;
            --bg-sidebar: #ffffff;
            --bg-header: #ffffff;
            --text-primary: #1e1e1e;
            --text-secondary: #6c6c6c;
            --border-light: #eaeaea;
            --gold: #c5a059;
            --gold-light: #d4af6a;
            --gold-hover: #b88d3a;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.03);
            --shadow-md: 0 6px 20px rgba(0,0,0,0.05);
            --success: #27ae60;
            --success-light: #d5f4e6;
            --danger: #e74c3c;
            --danger-light: #fadbd8;
            --warning: #f39c12;
            --info: #3498db;
        }
        [data-theme="dark"] {
            --bg-body: #0a0a0a;
            --bg-sidebar: #121212;
            --bg-header: #121212;
            --text-primary: #E2E8F0;
            --text-secondary: #94A3B8;
            --border-light: #2a2a2a;
            --gold: #fbbf24;
            --gold-hover: #f59e0b;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.3);
            --shadow-md: 0 6px 20px rgba(0,0,0,0.4);
            --success-light: #1e4a2e;
            --danger-light: #4a1a1a;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            transition: background 0.3s, color 0.2s;
            font-size: 1rem;
            line-height: 1.5;
        }
        /* Stats cards */
        .stats-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .stat-summary-card {
            flex: 1;
            border-radius: 28px;
            padding: 20px 24px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
            background: var(--bg-sidebar);
        }
        .stat-summary-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .stat-summary-card .stat-icon {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 3rem;
            opacity: 0.15;
            color: var(--gold);
        }
        [data-theme="dark"] .stat-summary-card .stat-icon {
            opacity: 0.25;
            color: var(--gold-light);
        }
        .stat-summary-card .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--gold);
            line-height: 1.2;
        }
        .stat-summary-card .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        .stat-summary-card .stat-sub {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        /* Filter bar */
        .filter-bar {
            background: var(--bg-sidebar);
            border-radius: 24px;
            padding: 16px 24px;
            margin-bottom: 28px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 16px;
            border: 1px solid var(--border-light);
        }
        .filter-bar input[type="text"] {
            flex: 3;
            min-width: 260px;
            padding: 10px 16px;
            border: 1px solid var(--border-light);
            border-radius: 40px;
            background: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.85rem;
        }
        .filter-bar select {
            flex: 1;
            min-width: 140px;
            padding: 10px 16px;
            border: 1px solid var(--border-light);
            border-radius: 40px;
            background: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.85rem;
        }
        .btn-outline-gold {
            background: transparent;
            border: 1px solid var(--gold);
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            transition: 0.2s;
            font-weight: 500;
            color: var(--gold);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-outline-gold:hover {
            background: rgba(197,160,89,0.1);
            border-color: var(--gold-hover);
            color: var(--gold-hover);
        }
        /* Table - fixed border collapse */
        .facility-table {
            width: 100%;
            background: var(--bg-sidebar);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-light);
            border-collapse: collapse;
        }
        .facility-table th {
            text-align: left;
            padding: 18px 16px;
            background: var(--gold);
            color: #1e1e1e;
            font-weight: 700;
            font-size: 0.8rem;
            border-bottom: 1px solid var(--border-light);
        }
        [data-theme="dark"] .facility-table th {
            background: var(--gold);
            color: #0a0a0a;
        }
        .facility-table td {
            padding: 16px 16px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
            font-size: 0.85rem;
            vertical-align: middle;
        }
        .facility-table tr:hover td {
            background: rgba(197, 160, 89, 0.05);
        }
        /* Features tags */
        .features-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .feature-tag {
            display: inline-block;
            background: var(--bg-body);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
            white-space: normal;
            word-break: break-word;
        }
        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-active {
            background: var(--success-light);
            color: #0b5e42;
        }
        .status-inactive {
            background: var(--danger-light);
            color: #991b1b;
        }
        /* Action buttons - border only (transparent background) */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-edit-border {
            background: transparent;
            border: 1px solid var(--info);
            color: var(--info);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-edit-border:hover {
            background: rgba(52,152,219,0.1);
        }
        .btn-toggle-border {
            background: transparent;
            border: 1px solid var(--warning);
            color: #e67e22;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-toggle-border:hover {
            background: rgba(243,156,18,0.1);
        }
        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 1100;
            backdrop-filter: blur(2px);
        }
        .modal-content {
            background: var(--bg-sidebar);
            border-radius: 28px;
            width: 550px;
            max-width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            padding: 24px;
            position: relative;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 12px;
        }
        .modal-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--text-primary);
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.8rem;
            color: var(--text-primary);
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            background: var(--bg-body);
            color: var(--text-primary);
            font-size: 0.85rem;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: normal;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }
        .btn-primary {
            background: var(--gold);
            border: none;
            padding: 8px 20px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-light);
            padding: 8px 20px;
            border-radius: 40px;
            cursor: pointer;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 20px;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }
        .alert-success {
            background: var(--success-light);
            color: #0b5e42;
            border: 1px solid #c8e6d9;
        }
        .alert-danger {
            background: var(--danger-light);
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        @media (max-width: 768px) {
            .stats-summary { flex-direction: column; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .filter-bar input[type="text"] { width: 100%; }
            .facility-table th, .facility-table td { padding: 12px 8px; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
<div class="stats-summary">
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-building"></i></div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-label">Total Facilities</div>
        <div class="stat-sub">All hotel facilities</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= $activeCount ?></div>
        <div class="stat-label">Active</div>
        <div class="stat-sub">Visible to guests</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-eye-slash"></i></div>
        <div class="stat-value"><?= $inactiveCount ?></div>
        <div class="stat-label">Inactive</div>
        <div class="stat-sub">Hidden from website</div>
    </div>
</div>

<div class="filter-bar">
    <input type="text" id="searchInput" placeholder="Search by category...">
    <select id="statusFilter">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>
    <button class="btn-outline-gold" onclick="openAddModal()">+ Add New</button>
    <button class="btn-outline-gold" onclick="window.location.reload()">Reset</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="facility-table">
    <thead>
        <tr><th>Order</th><th>Image</th><th>Category</th><th>Hours</th><th>Features</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody id="tableBody">
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr data-id="<?= $row['id'] ?>" data-status="<?= $row['is_active'] ?>" data-category="<?= strtolower(htmlspecialchars($row['category'])) ?>">
            <td><?= $row['display_order'] ?></td>
            <td><img src="<?= htmlspecialchars($row['image_path'] ?: 'placeholder.jpg') ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px;"></td>
            <td><strong><?= htmlspecialchars($row['category']) ?></strong></td>
            <td><?= htmlspecialchars($row['hours']) ?: '—' ?></td>
            <td>
                <div class="features-list">
                    <?php if(!empty($row['feature1'])): ?>
                        <span class="feature-tag"><?= htmlspecialchars($row['feature1']) ?></span>
                    <?php endif; ?>
                    <?php if(!empty($row['feature2'])): ?>
                        <span class="feature-tag"><?= htmlspecialchars($row['feature2']) ?></span>
                    <?php endif; ?>
                </div>
            </td>
            <td><span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="action-buttons">
                <button class="btn-edit-border" onclick="openEditModal(<?= $row['id'] ?>)">Edit</button>
                <button class="btn-toggle-border" onclick="toggleStatus(<?= $row['id'] ?>)">Toggle</button>
            </td>
        </tr>

        <!-- Edit Modal per facility -->
        <div id="editModal<?= $row['id'] ?>" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Facility</h3>
                    <button class="close-btn" onclick="closeModal(<?= $row['id'] ?>)">&times;</button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <div class="form-group"><label>Category *</label><input type="text" name="category" class="form-control" value="<?= htmlspecialchars($row['category']) ?>" required></div>
                    <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" required><?= htmlspecialchars($row['description']) ?></textarea></div>
                    <div class="form-group"><label>Hours</label><input type="text" name="hours" class="form-control" value="<?= htmlspecialchars($row['hours']) ?>"></div>
                    <div class="form-group"><label>Display Order</label><input type="number" name="display_order" class="form-control" value="<?= $row['display_order'] ?>"></div>
                    <div class="form-group"><label>Feature 1</label><input type="text" name="feature1" class="form-control" value="<?= htmlspecialchars($row['feature1']) ?>"></div>
                    <div class="form-group"><label>Feature 2</label><input type="text" name="feature2" class="form-control" value="<?= htmlspecialchars($row['feature2']) ?>"></div>
                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($row['image_path']) ?>">
                        <?php if($row['image_path']): ?>
                            <div style="margin-top:8px;">Current: <img src="<?= htmlspecialchars($row['image_path']) ?>" width="50" style="border-radius:6px;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="reverse_layout" value="1" <?= $row['reverse_layout'] ? 'checked' : '' ?>> Reverse layout (image right)</label>
                        <label><input type="checkbox" name="is_active" value="1" <?= $row['is_active'] ? 'checked' : '' ?>> Active</label>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" name="save_facility" class="btn-primary">Save Changes</button>
                        <button type="button" class="btn-secondary" onclick="closeModal(<?= $row['id'] ?>)">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if ($total == 0): ?>
        <tr><td colspan="7" style="text-align:center; padding:60px;">No facilities found. Click "Add New".<?php echo ' '; ?></td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Facility</h3>
            <button class="close-btn" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group"><label>Category *</label><input type="text" name="category" class="form-control" required></div>
            <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" required></textarea></div>
            <div class="form-group"><label>Hours</label><input type="text" name="hours" class="form-control"></div>
            <div class="form-group"><label>Display Order</label><input type="number" name="display_order" class="form-control" value="0"></div>
            <div class="form-group"><label>Feature 1</label><input type="text" name="feature1" class="form-control"></div>
            <div class="form-group"><label>Feature 2</label><input type="text" name="feature2" class="form-control"></div>
            <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*" class="form-control"></div>
            <div class="checkbox-group">
                <label><input type="checkbox" name="reverse_layout" value="1"> Reverse layout (image right)</label>
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
            </div>
            <div class="modal-actions">
                <button type="submit" name="save_facility" class="btn-primary">Add Facility</button>
                <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function filterTable() {
        let search = document.getElementById('searchInput').value.toLowerCase();
        let status = document.getElementById('statusFilter').value;
        let rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            if (row.cells.length < 2) return;
            let cat = row.getAttribute('data-category') || '';
            let stat = row.getAttribute('data-status');
            let show = cat.includes(search) && (!status || stat === status);
            row.style.display = show ? '' : 'none';
        });
    }
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);

    function openEditModal(id) { document.getElementById('editModal'+id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById('editModal'+id).style.display = 'none'; }
    function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
    function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }
    function toggleStatus(id) {
        if (confirm('Toggle this facility status?')) window.location.href = '?toggle=' + id;
    }
    window.onclick = function(e) {
        if (e.target.classList.contains('modal')) e.target.style.display = 'none';
    }
</script>

</body>
</html>