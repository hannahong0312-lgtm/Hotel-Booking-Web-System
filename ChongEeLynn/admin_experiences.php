<?php
// admin_experiences.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $id = (int)$_POST['update_id'];
    $type = $conn->real_escape_string($_POST['type']);
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $feature1 = isset($_POST['feature1']) && $_POST['feature1'] !== '' ? $conn->real_escape_string($_POST['feature1']) : null;
    $feature2 = isset($_POST['feature2']) && $_POST['feature2'] !== '' ? $conn->real_escape_string($_POST['feature2']) : null;
    $image_path = isset($_POST['image_path']) && $_POST['image_path'] !== '' ? $conn->real_escape_string($_POST['image_path']) : null;
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "UPDATE experiences SET 
            type='$type',
            category='$category',
            title='$title',
            description='$description',
            feature1=" . ($feature1 ? "'$feature1'" : "NULL") . ",
            feature2=" . ($feature2 ? "'$feature2'" : "NULL") . ",
            image_path=" . ($image_path ? "'$image_path'" : "NULL") . ",
            display_order=$display_order,
            is_active=$is_active
            WHERE id=$id";
    
    if ($conn->query($sql)) {
        $message = "Experience updated successfully!";
        $messageType = "success";
        echo "<script>setTimeout(()=>{window.location='admin_experiences.php';},1000);</script>";
    } else {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}

// Handle toggle active status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE experiences SET is_active = NOT is_active WHERE id = $id");
    header("Location: admin_experiences.php");
    exit();
}

// Fetch all experiences
$result = $conn->query("SELECT * FROM experiences ORDER BY display_order ASC, id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experience Management - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/admin_experiences.css">
</head>
<body>
<div class="experience-container">
    <div class="page-header">
        <div class="header-left">
            <h1>Experience Management</h1>
            <p class="subtitle">Manage hotel experiences, activities, and featured highlights</p>
        </div>
        <div class="header-right">
            <div class="stats">
                <span class="stat-badge">
                    Total Items: <?= $result->num_rows ?>
                </span>
            </div>
        </div>
    </div>

    <?php if(isset($message) && !isset($_GET['toggle'])): ?>
        <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="experience-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Features</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $result->data_seek(0);
                while($row = $result->fetch_assoc()): 
                    // Handle NULL values - only show image if exists
                    $hasImage = !empty($row['image_path']);
                    $imagePath = $hasImage ? htmlspecialchars($row['image_path']) : '';
                    $category = !empty($row['category']) ? htmlspecialchars($row['category']) : '-';
                    $description = !empty($row['description']) ? htmlspecialchars($row['description']) : '';
                    $feature1 = !empty($row['feature1']) ? htmlspecialchars($row['feature1']) : '';
                    $feature2 = !empty($row['feature2']) ? htmlspecialchars($row['feature2']) : '';
                    $title = !empty($row['title']) ? htmlspecialchars($row['title']) : 'Untitled';
                ?>
                <tr class="experience-row">
                    <td class="id-cell"><?= $row['id'] ?></td>
                    <td class="type-cell">
                        <span class="type-badge type-<?= $row['type'] ?>">
                            <?= ucfirst($row['type']) ?>
                        </span>
                    </td>
                    <td class="image-cell">
                        <?php if($hasImage): ?>
                        <div class="image-group">
                            <img src="images/<?= $imagePath ?>" class="experience-thumb" alt="experience">
                            <div class="image-hover">
                                <span>View</span>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="no-image">
                            <span>No image</span>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="title-cell"><?= $title ?></td>
                    <td class="category-cell"><?= $category ?></td>
                    <td class="description-cell" title="<?= $description ?>">
                        <?= strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description ?>
                    </td>
                    <td class="features-cell">
                        <div class="features-list">
                            <?php if($feature1): ?>
                                <span class="feature-tag"><?= $feature1 ?></span>
                            <?php endif; ?>
                            <?php if($feature2): ?>
                                <span class="feature-tag"><?= $feature2 ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="order-cell"><?= $row['display_order'] ?></td>
                    <td>
                        <button class="status-toggle" onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)">
                            <span class="status-badge <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </button>
                    </td>
                    <td class="actions">
                        <button onclick="openEditModal(<?= $row['id'] ?>)" class="btn-edit">Edit</button>
                        <button onclick="toggleStatus(<?= $row['id'] ?>, <?= $row['is_active'] ?>)" class="btn-toggle">Toggle</button>
                    </td>
                </tr>
                
                <!-- Edit Modal -->
                <div id="editModal<?= $row['id'] ?>" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Edit Experience</h3>
                            <span class="close" onclick="closeEditModal(<?= $row['id'] ?>)">&times;</span>
                        </div>
                        <form method="POST" class="edit-form">
                            <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type">
                                        <option value="main" <?= $row['type'] == 'main' ? 'selected' : '' ?>>Main</option>
                                        <option value="favorite" <?= $row['type'] == 'favorite' ? 'selected' : '' ?>>Favorite</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="text" name="category" value="<?= htmlspecialchars($row['category'] ?? '') ?>" placeholder="e.g., Heritage walk">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($row['title'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3" required><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Feature 1 (Optional)</label>
                                    <input type="text" name="feature1" value="<?= htmlspecialchars($row['feature1'] ?? '') ?>" placeholder="e.g., Guided Tour">
                                    <small class="hint">Leave empty if not applicable</small>
                                </div>
                                <div class="form-group">
                                    <label>Feature 2 (Optional)</label>
                                    <input type="text" name="feature2" value="<?= htmlspecialchars($row['feature2'] ?? '') ?>" placeholder="e.g., Free Breakfast">
                                    <small class="hint">Leave empty if not applicable</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Image Filename</label>
                                    <input type="text" name="image_path" value="<?= htmlspecialchars($row['image_path'] ?? '') ?>" placeholder="e.g., experience.jpg">
                                    <small class="hint">Enter the filename of the image in the "images" folder. Leave empty for no image.</small>
                                </div>
                                <div class="form-group">
                                    <label>Display Order</label>
                                    <input type="number" name="display_order" value="<?= $row['display_order'] ?? 0 ?>">
                                    <small class="hint">Lower numbers appear first</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>>
                                    Active (visible on website)
                                </label>
                            </div>
                            
                            <div class="modal-actions">
                                <button type="submit" class="btn-submit">Save Changes</button>
                                <button type="button" class="btn-cancel" onclick="closeEditModal(<?= $row['id'] ?>)">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if($result->num_rows == 0): ?>
                <tr>
                    <td colspan="10" class="empty-state">
                        <p>No experiences found.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleStatus(id, currentStatus) {
    if(confirm('Toggle experience status?')) {
        window.location.href = '?toggle=' + id;
    }
}

function openEditModal(id) {
    document.getElementById('editModal' + id).style.display = 'flex';
}

function closeEditModal(id) {
    document.getElementById('editModal' + id).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>
</body>
</html>