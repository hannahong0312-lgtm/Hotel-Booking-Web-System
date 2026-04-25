<?php
// admin_reports.php - Grand Hotel Melaka
require_once __DIR__ . '/../ChangJingEn/admin_header.php';

// Set timezone to Malaysia
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get date range from filter
$period = $_GET['period'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

if ($period === 'custom' && isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
} elseif ($period === 'daily') {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
} elseif ($period === 'weekly') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
} elseif ($period === 'monthly') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
} elseif ($period === 'yearly') {
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
}

// Function to get revenue data from book table
function getRevenueData($conn, $start_date, $end_date) {
    $sql = "SELECT 
                SUM(grand_total) as total_revenue,
                COUNT(*) as total_bookings,
                AVG(grand_total) as avg_booking_value,
                SUM(CASE WHEN status = 'confirmed' THEN grand_total ELSE 0 END) as confirmed_revenue,
                SUM(CASE WHEN status = 'completed' THEN grand_total ELSE 0 END) as completed_revenue
            FROM book 
            WHERE status != 'cancelled'
            AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Function to get booking status breakdown
function getBookingStatus($conn, $start_date, $end_date) {
    $sql = "SELECT 
                status,
                COUNT(*) as count,
                SUM(grand_total) as revenue
            FROM book 
            WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
            GROUP BY status";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['status']] = ['count' => $row['count'], 'revenue' => $row['revenue']];
    }
    return $data;
}

// Function to get room category performance
function getRoomCategoryPerformance($conn, $start_date, $end_date) {
    $sql = "SELECT 
                r.category,
                COUNT(b.id) as bookings_count,
                SUM(b.grand_total) as revenue,
                AVG(b.grand_total) as avg_value
            FROM book b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.status != 'cancelled'
            AND b.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
            GROUP BY r.category";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Function to get new users count
function getNewUsersCount($conn, $start_date, $end_date) {
    $sql = "SELECT 
                COUNT(*) as new_users
            FROM users 
            WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['new_users'];
}

// Function to get total users
function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

// Function to get average rating
function getAverageRating($conn, $start_date, $end_date) {
    $sql = "SELECT 
                AVG(r_rating) as avg_rating,
                COUNT(*) as total_reviews
            FROM review 
            WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Function to get rating distribution
function getRatingDistribution($conn, $start_date, $end_date) {
    $sql = "SELECT 
                r_rating,
                COUNT(*) as count
            FROM review 
            WHERE created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
            GROUP BY r_rating
            ORDER BY r_rating DESC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['r_rating'] . ' Star'] = $row['count'];
    }
    return $data;
}

// Function to get daily revenue for chart
function getDailyRevenue($conn, $start_date, $end_date) {
    $sql = "SELECT 
                DATE(created_at) as date,
                SUM(grand_total) as revenue,
                COUNT(*) as bookings
            FROM book 
            WHERE status != 'cancelled'
            AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Function to get top performing rooms
function getTopRooms($conn, $start_date, $end_date) {
    $sql = "SELECT 
                r.name,
                r.category,
                COUNT(b.id) as bookings_count,
                SUM(b.grand_total) as revenue
            FROM book b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.status != 'cancelled'
            AND b.created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
            GROUP BY b.room_id
            ORDER BY revenue DESC
            LIMIT 5";
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Function to get offer usage
function getOfferUsage($conn, $start_date, $end_date) {
    // Note: Assuming offers are linked via booking_ref or similar
    // Adjust this query based on how offers are tracked in your system
    $sql = "SELECT 
                COUNT(*) as total_offers_used,
                AVG(discount_percentage) as avg_discount
            FROM hotel_offers 
            WHERE is_active = 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Fetch all data
$revenue_data = getRevenueData($conn, $start_date, $end_date);
$booking_status = getBookingStatus($conn, $start_date, $end_date);
$room_performance = getRoomCategoryPerformance($conn, $start_date, $end_date);
$new_users = getNewUsersCount($conn, $start_date, $end_date);
$total_users = getTotalUsers($conn);
$rating_data = getAverageRating($conn, $start_date, $end_date);
$rating_distribution = getRatingDistribution($conn, $start_date, $end_date);
$daily_revenue = getDailyRevenue($conn, $start_date, $end_date);
$top_rooms = getTopRooms($conn, $start_date, $end_date);
$offer_stats = getOfferUsage($conn, $start_date, $end_date);

// Calculate occupancy rate (assuming total rooms = 20, adjust as needed)
$total_rooms = 9;
$booked_nights = 0;
$occupancy_sql = "SELECT SUM(DATEDIFF(check_out, check_in)) as total_nights 
                  FROM book 
                  WHERE status != 'cancelled'
                  AND check_in <= '$end_date' 
                  AND check_out >= '$start_date'";
$occupancy_result = $conn->query($occupancy_sql);
if ($occupancy_result && $row = $occupancy_result->fetch_assoc()) {
    $booked_nights = $row['total_nights'] ?? 0;
}
$total_possible_nights = $total_rooms * (strtotime($end_date) - strtotime($start_date)) / 86400;
$occupancy_rate = $total_possible_nights > 0 ? ($booked_nights / $total_possible_nights) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Grand Hotel Admin</title>
    <link rel="stylesheet" href="css/admin_reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="reports-container">
    <div class="page-header">
        <div class="header-left">
            <h1>Reports & Analytics</h1>
            <p class="subtitle">Track hotel performance, revenue, and key metrics</p>
        </div>
        <div class="header-right">
            <div class="date-filter">
                <form method="GET" action="" class="filter-form">
                    <select name="period" id="period" onchange="this.form.submit()">
                        <option value="daily" <?= $period == 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="weekly" <?= $period == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        <option value="monthly" <?= $period == 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="yearly" <?= $period == 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        <option value="custom" <?= $period == 'custom' ? 'selected' : '' ?>>Custom Range</option>
                    </select>
                    
                    <div id="customDateRange" style="display: <?= $period == 'custom' ? 'inline-flex' : 'none' ?>;">
                        <input type="date" name="start_date" value="<?= $start_date ?>">
                        <span>to</span>
                        <input type="date" name="end_date" value="<?= $end_date ?>">
                        <button type="submit" class="btn-apply">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>Total Revenue</h3>
                <div class="stat-value">RM <?= number_format($revenue_data['total_revenue'] ?? 0, 2) ?></div>
                <p>from <?= $revenue_data['total_bookings'] ?? 0 ?> bookings</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3>Occupancy Rate</h3>
                <div class="stat-value"><?= number_format($occupancy_rate, 1) ?>%</div>
                <p>rooms occupied</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3>New Users</h3>
                <div class="stat-value"><?= $new_users ?></div>
                <p>Total: <?= $total_users ?> active users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-info">
                <h3>Avg Rating</h3>
                <div class="stat-value"><?= number_format($rating_data['avg_rating'] ?? 0, 1) ?> / 5</div>
                <p>from <?= $rating_data['total_reviews'] ?? 0 ?> reviews</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-container">
            <h3>Revenue Trend</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Booking Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-container">
            <h3>Room Category Performance</h3>
            <canvas id="roomChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Rating Distribution</h3>
            <canvas id="ratingChart"></canvas>
        </div>
    </div>

    <!-- Top Rooms Table -->
    <div class="data-table-section">
        <h3>Top Performing Rooms</h3>
        <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Room Name</th>
                        <th>Category</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($top_rooms as $room): ?>
                    <tr>
                        <td><?= htmlspecialchars($room['name']) ?></td>
                        <td><span class="category-badge category-<?= $room['category'] ?>"><?= ucfirst($room['category']) ?></span></td>
                        <td><?= $room['bookings_count'] ?></td>
                        <td>RM <?= number_format($room['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($top_rooms)): ?>
                    <tr>
                        <td colspan="4" class="empty-state">No data available for selected period</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="data-table-section">
        <h3>Daily Breakdown</h3>
        <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                        <th>Avg. Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($daily_revenue as $day): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($day['date'])) ?></td>
                        <td><?= $day['bookings'] ?></td>
                        <td>RM <?= number_format($day['revenue'], 2) ?></td>
                        <td>RM <?= number_format($day['revenue'] / max($day['bookings'], 1), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($daily_revenue)): ?>
                    <tr>
                        <td colspan="4" class="empty-state">No data available for selected period</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Room Category Performance Table -->
    <div class="data-table-section">
        <h3>Room Category Performance</h3>
        <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                        <th>Avg. Per Booking</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($room_performance as $room): ?>
                    <tr>
                        <td><span class="category-badge category-<?= $room['category'] ?>"><?= ucfirst($room['category']) ?></span></td>
                        <td><?= $room['bookings_count'] ?></td>
                        <td>RM <?= number_format($room['revenue'], 2) ?></td>
                        <td>RM <?= number_format($room['avg_value'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($room_performance)): ?>
                    <tr>
                        <td colspan="4" class="empty-state">No data available</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($daily_revenue, 'date')) ?>,
        datasets: [{
            label: 'Revenue (RM)',
            data: <?= json_encode(array_column($daily_revenue, 'revenue')) ?>,
            borderColor: '#c5a059',
            backgroundColor: 'rgba(197, 160, 89, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary') }
            }
        }
    }
});

// Booking Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusLabels = <?= json_encode(array_keys($booking_status)) ?>;
const statusCounts = <?= json_encode(array_column($booking_status, 'count')) ?>;
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusCounts,
            backgroundColor: ['#27ae60', '#f39c12', '#e74c3c']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Room Category Chart
const roomCtx = document.getElementById('roomChart').getContext('2d');
const roomLabels = <?= json_encode(array_column($room_performance, 'category')) ?>;
const roomBookings = <?= json_encode(array_column($room_performance, 'bookings_count')) ?>;
new Chart(roomCtx, {
    type: 'bar',
    data: {
        labels: roomLabels,
        datasets: [{
            label: 'Number of Bookings',
            data: roomBookings,
            backgroundColor: '#c5a059'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary') }
            }
        }
    }
});

// Rating Distribution Chart
const ratingCtx = document.getElementById('ratingChart').getContext('2d');
const ratingLabels = <?= json_encode(array_keys($rating_distribution)) ?>;
const ratingCounts = <?= json_encode(array_values($rating_distribution)) ?>;
new Chart(ratingCtx, {
    type: 'bar',
    data: {
        labels: ratingLabels,
        datasets: [{
            label: 'Number of Reviews',
            data: ratingCounts,
            backgroundColor: '#f39c12'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: getComputedStyle(document.documentElement).getPropertyValue('--text-primary') }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});

// Toggle custom date range
document.getElementById('period').addEventListener('change', function() {
    const customDiv = document.getElementById('customDateRange');
    if (this.value === 'custom') {
        customDiv.style.display = 'inline-flex';
    } else {
        customDiv.style.display = 'none';
        this.form.submit();
    }
});
</script>
</body>
</html>