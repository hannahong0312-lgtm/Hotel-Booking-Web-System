<?php
// cart.php - Select quantity of rooms (not guests) with stock limit from rooms_available
session_start();
include '../Shared/config.php';
include '../Shared/header.php';

// --- Handle single item removal via GET ---
if (isset($_GET['remove_index'])) {
    $remove_index = (int)$_GET['remove_index'];
    if (isset($_SESSION['cart'][$remove_index])) {
        array_splice($_SESSION['cart'], $remove_index, 1);
        if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit();
}

// --- Handle batch removal via GET ---
if (isset($_GET['remove_indices'])) {
    $indices = explode(',', $_GET['remove_indices']);
    rsort($indices);
    foreach ($indices as $idx) {
        $idx = (int)$idx;
        if (isset($_SESSION['cart'][$idx])) {
            array_splice($_SESSION['cart'], $idx, 1);
        }
    }
    if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
    header('Location: cart.php');
    exit();
}

// --- AJAX: update quantity of rooms ---
if (isset($_GET['action']) && $_GET['action'] == 'update_quantity' && isset($_GET['index']) && isset($_GET['quantity'])) {
    header('Content-Type: application/json');
    $index = (int)$_GET['index'];
    $new_qty = (int)$_GET['quantity'];
    if (isset($_SESSION['cart'][$index])) {
        $room_id = $_SESSION['cart'][$index]['room_id'];
        
        $room_sql = "SELECT rooms_available FROM rooms WHERE id = $room_id";
        $room_res = mysqli_query($conn, $room_sql);
        $max_available = ($room_res && mysqli_num_rows($room_res) > 0) ? (int)mysqli_fetch_assoc($room_res)['rooms_available'] : 0;
        
        $max_qty = $max_available;
        $new_qty = max(1, min($new_qty, $max_qty));
        $_SESSION['cart'][$index]['quantity'] = $new_qty;
        echo json_encode(['success' => true, 'new_quantity' => $new_qty, 'max' => $max_qty]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// --- AJAX: sync cart from localStorage ---
if (isset($_GET['action']) && $_GET['action'] == 'sync_cart' && isset($_GET['cart_data'])) {
    header('Content-Type: application/json');
    $cart_data = json_decode($_GET['cart_data'], true);
    if (is_array($cart_data) && !empty($cart_data)) {
        $_SESSION['cart'] = $cart_data;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// --- Login check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: ../ChangJingEn/login.php');
    exit();
}

// --- Initialise cart ---
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --- MIGRATION: Fix old cart items missing 'quantity' or 'max_rooms' ---
$need_reload = false;
foreach ($_SESSION['cart'] as $idx => &$item) {
    if (!isset($item['quantity'])) {
        $item['quantity'] = 1;
        $need_reload = true;
    }
    if (!isset($item['max_rooms'])) {
        // Fetch from database
        $room_id = $item['room_id'];
        $room_sql = "SELECT rooms_available FROM rooms WHERE id = $room_id";
        $room_res = mysqli_query($conn, $room_sql);
        $max_rooms = ($room_res && mysqli_num_rows($room_res) > 0) ? (int)mysqli_fetch_assoc($room_res)['rooms_available'] : 5;
        $item['max_rooms'] = $max_rooms;
        $need_reload = true;
    }
    // Also ensure 'nights' exists (for very old carts)
    if (!isset($item['nights'])) {
        $date1 = new DateTime($item['check_in']);
        $date2 = new DateTime($item['check_out']);
        $nights = $date2->diff($date1)->days;
        $item['nights'] = ($nights <= 0) ? 1 : $nights;
        $need_reload = true;
    }
}
unset($item);
// If we fixed missing keys, reload the page to remove warnings and show correct data
if ($need_reload) {
    header('Location: cart.php');
    exit();
}
// --- End migration ---

// --- Add item to cart (from room page) ---
if (isset($_GET['room_id'], $_GET['arrive'], $_GET['depart'])) {
    $room_id = (int)$_GET['room_id'];
    $check_in = $_GET['arrive'];
    $check_out = $_GET['depart'];
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;

    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date2->diff($date1)->days;
    if ($nights <= 0) $nights = 1;

    $room_sql = "SELECT id, name, price, rooms_available, image FROM rooms WHERE id = $room_id AND is_active = 1";
    $room_result = mysqli_query($conn, $room_sql);
    if ($room_result && mysqli_num_rows($room_result) > 0) {
        $room = mysqli_fetch_assoc($room_result);
        $max_qty = (int)$room['rooms_available'];
        $quantity = max(1, min($quantity, $max_qty));
        
        $exists = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['room_id'] == $room_id && $item['check_in'] == $check_in && $item['check_out'] == $check_out) {
                $new_qty = $item['quantity'] + $quantity;
                $new_qty = min($new_qty, $max_qty);
                $item['quantity'] = $new_qty;
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $_SESSION['cart'][] = [
                'room_id'    => $room['id'],
                'room_name'  => $room['name'],
                'room_price' => (float)$room['price'],
                'max_rooms'  => $max_qty,
                'check_in'   => $check_in,
                'check_out'  => $check_out,
                'nights'     => $nights,
                'quantity'   => $quantity,
                'image'      => $room['image']
            ];
        }
    }
    header('Location: cart.php');
    exit();
}

$cart_items = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart | Grand Hotel</title>
    <link rel="stylesheet" href="css/payment.css">
    <style>
        /* (your existing CSS – unchanged) */
        .empty-cart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 60vh;
            text-align: center;
        }
        .empty-cart { padding: 40px; }
        .empty-cart i { font-size: 4rem; color: #ccc; margin-bottom: 20px; }
        .cart-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
            flex-wrap: wrap;
        }
        .cart-item-check { width: 40px; text-align: center; }
        .cart-item-check input { width: 18px; height: 18px; cursor: pointer; }
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-details { flex: 3; min-width: 180px; }
        .cart-item-details h4 { margin: 0 0 5px; font-size: 1rem; }
        .cart-item-details p { margin: 3px 0; font-size: 0.85rem; color: #555; }
        .cart-item-price { flex: 1; text-align: right; min-width: 100px; }
        .cart-item-price .price { font-weight: bold; color: var(--gold); }
        .room-quantity-select {
            width: 70px;
            padding: 4px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .remove-item {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0 8px;
        }
        .remove-item:hover { color: #a71d2a; }
        .select-all-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            background: #fafafa;
            margin-bottom: 10px;
        }
        .cart-summary-panel {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .subtotal-top {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--gold);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold);
        }
        .btn-outline:hover { background: var(--gold); color: white; }
        @media (max-width: 768px) {
            .cart-item { flex-direction: column; align-items: stretch; }
            .cart-item-price { text-align: left; }
            .cart-summary-panel { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
<div id="syncMessage" class="sync-message"></div>
<main>
<div class="booking-container" style="max-width: 1200px;">
    <div class="booking-header">
        <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
        <p><?= count($cart_items) ?> item(s) · Review and select rooms to proceed</p>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart-container">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <a href="../ChongEeLynn/accommodation.php" class="btn btn-primary">Browse Rooms</a>
            </div>
        </div>
    <?php else: ?>
        <form method="POST" action="payment.php" id="checkoutForm">
            <div class="booking-details-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-list"></i> Selected items (<span id="selectedCount">0</span>)</h3>
                    <div class="subtotal-top">
                        Subtotal: RM <span id="cartSubtotal">0.00</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="select-all-row">
                        <label style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="selectAllCheckbox"> <strong>Select All</strong>
                        </label>
                        <button type="button" class="btn-voucher" style="background:#6c757d;" id="removeSelectedBtn">Remove Selected</button>
                    </div>
                    <div id="cartItemsContainer">
                        <?php foreach ($cart_items as $index => $item): 
                            // Use null coalescing to be extra safe (though migration already fixed)
                            $qty = $item['quantity'] ?? 1;
                            $max_rooms = $item['max_rooms'] ?? 5;
                            $nights = $item['nights'] ?? 1;
                            $item_total = $item['room_price'] * $nights * $qty;
                        ?>
                            <div class="cart-item" data-index="<?= $index ?>" data-total="<?= $item_total ?>">
                                <div class="cart-item-check">
                                    <input type="checkbox" name="selected_items[]" value="<?= $index ?>" class="item-checkbox">
                                </div>
                                <img src="../ChongEeLynn/images/<?= htmlspecialchars($item['image'] ?? 'room-default.jpg') ?>"
                                     alt="<?= htmlspecialchars($item['room_name']) ?>"
                                     class="cart-item-image"
                                     onerror="this.src='../ChongEeLynn/images/room-default.jpg'">
                                <div class="cart-item-details">
                                    <h4><?= htmlspecialchars($item['room_name']) ?></h4>
                                    <p><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($item['check_in'])) ?> – <?= date('d M Y', strtotime($item['check_out'])) ?> (<?= $nights ?> nights)</p>
                                    <p><i class="fas fa-door-open"></i> Rooms:
                                        <select class="room-quantity-select" data-index="<?= $index ?>" onchange="updateQuantity(<?= $index ?>, this.value)">
                                            <?php for ($q = 1; $q <= $max_rooms; $q++): ?>
                                                <option value="<?= $q ?>" <?= $qty == $q ? 'selected' : '' ?>><?= $q ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <small style="color:#888"> (Max <?= $max_rooms ?> available)</small>
                                    </p>
                                </div>
                                <div class="cart-item-price">
                                    <div>RM <?= number_format($item['room_price'], 0) ?> / night / room</div>
                                    <div class="price">RM <?= number_format($item_total, 2) ?></div>
                                </div>
                                <button type="button" class="remove-item" data-index="<?= $index ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="cart-summary-panel">
                <a href="../ChongEeLynn/accommodation.php" class="btn btn-outline"><i class="fas fa-plus-circle"></i> Add a room</a>
                <button type="submit" class="btn btn-primary" style="background: var(--gold); padding: 12px 32px;">Checkout →</button>
            </div>
        </form>
    <?php endif; ?>
</div>
</main>

<script>
// (JavaScript unchanged from previous – keep as is)
function updateSelectedSubtotal() {
    let checkboxes = document.querySelectorAll('.item-checkbox');
    let total = 0;
    let selectedCount = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            selectedCount++;
            let cartItem = cb.closest('.cart-item');
            if (cartItem) {
                let itemTotal = parseFloat(cartItem.getAttribute('data-total'));
                if (!isNaN(itemTotal)) total += itemTotal;
            }
        }
    });
    document.getElementById('selectedCount').innerText = selectedCount;
    document.getElementById('cartSubtotal').innerText = total.toFixed(2);
    document.getElementById('summarySubtotal').innerText = total.toFixed(2);
}

function removeCartItem(index) {
    if (confirm('Remove this room?')) {
        window.location.href = '?remove_index=' + index;
    }
}

function removeSelectedItems() {
    let selected = [...document.querySelectorAll('.item-checkbox:checked')].map(cb => cb.value);
    if (selected.length === 0) {
        alert('No items selected');
        return;
    }
    if (confirm(`Remove ${selected.length} item(s)?`)) {
        window.location.href = '?remove_indices=' + selected.join(',');
    }
}

function updateQuantity(index, newQuantity) {
    fetch(`?action=update_quantity&index=${index}&quantity=${newQuantity}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update room quantity');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network error while updating quantity');
        });
}

function syncLocalStorageWithSession() {
    let cartItems = <?= json_encode($cart_items) ?>;
    if (cartItems.length) localStorage.setItem('hotelCart', JSON.stringify(cartItems));
    else localStorage.removeItem('hotelCart');
}

function showSyncMessage(msg, isError = false) {
    let div = document.getElementById('syncMessage');
    if (!div) return;
    div.style.backgroundColor = isError ? '#dc3545' : '#28a745';
    div.innerHTML = msg;
    div.style.display = 'block';
    setTimeout(() => div.style.display = 'none', 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    syncLocalStorageWithSession();

    let sessionCartEmpty = <?= empty($cart_items) ? 'true' : 'false' ?>;
    if (sessionCartEmpty) {
        let saved = localStorage.getItem('hotelCart');
        if (saved && saved !== '[]') {
            showSyncMessage('Restoring saved cart...');
            fetch(`?action=sync_cart&cart_data=${encodeURIComponent(saved)}`)
                .then(res => res.json())
                .then(data => { if (data.success) location.reload(); });
        }
    }

    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            let idx = this.getAttribute('data-index');
            if (idx !== null) removeCartItem(parseInt(idx));
        });
    });

    let removeSelectedBtn = document.getElementById('removeSelectedBtn');
    if (removeSelectedBtn) removeSelectedBtn.addEventListener('click', removeSelectedItems);

    let checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.addEventListener('change', updateSelectedSubtotal));
    let selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll) {
        selectAll.addEventListener('change', function(e) {
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateSelectedSubtotal();
        });
    }

    updateSelectedSubtotal();

    let form = document.getElementById('checkoutForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (document.querySelectorAll('.item-checkbox:checked').length === 0) {
                e.preventDefault();
                alert('Please select at least one room to checkout.');
            }
        });
    }
});
</script>
<?php include '../Shared/footer.php'; ?>