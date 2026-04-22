<?php
// admin_dashboard.php - Grand Hotel Melaka
require_once 'admin_header.php';

$sql_users = "SELECT COUNT(*) AS total FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total'];

$sql_rooms = "SELECT COUNT(*) AS total FROM rooms WHERE is_active = 1";
$result_rooms = $conn->query($sql_rooms);
$total_rooms = $result_rooms->fetch_assoc()['total'];

$sql_bookings = "SELECT COUNT(*) AS total FROM book";
$result_bookings = $conn->query($sql_bookings);
$total_bookings = $result_bookings->fetch_assoc()['total'];

$sql_revenue = "SELECT SUM(grand_total) AS total FROM book WHERE status IN ('confirmed', 'completed')";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total'] ?? 0;

$sql_recent = "SELECT b.id, b.booking_ref, b.check_in, b.check_out, b.grand_total, b.status, b.created_at,
                      u.first_name, u.last_name, r.name AS room_name
               FROM book b
               JOIN users u ON b.user_id = u.id
               JOIN rooms r ON b.room_id = r.id
               ORDER BY b.created_at DESC
               LIMIT 5";
$recent_bookings = $conn->query($sql_recent);
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 36px;
    }
    .stat-card {
        background: var(--bg-sidebar);
        border-radius: 24px;
        padding: 24px 22px;
        border: 1px solid var(--border-light);
        transition: transform 0.2s, box-shadow 0.2s;
        box-shadow: var(--shadow-sm);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: var(--text-secondary);
    }
    .stat-header i {
        font-size: 2rem;
        color: var(--gold);
        opacity: 0.85;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 8px;
    }
    .stat-trend {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #10b981;
    }
    .recent-section {
        background: var(--bg-sidebar);
        border-radius: 24px;
        padding: 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-primary);
    }
    .section-title i {
        color: var(--gold);
    }
    .table-wrapper {
        overflow-x: auto;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .data-table th {
        text-align: left;
        padding: 14px 12px;
        background: rgba(0,0,0,0.02);
        font-weight: 700;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-light);
    }
    .data-table td {
        padding: 14px 12px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
    }
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .status-confirmed { background: #e6f9ed; color: #0b5e42; }
    .status-completed { background: #e0f2fe; color: #075985; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-pending { background: #fff3e3; color: #b45309; }
    .ref-link {
        font-family: monospace;
        font-weight: 700;
        color: var(--gold);
    }
    @media (max-width: 768px) {
        .stats-grid { gap: 16px; }
        .stat-number { font-size: 1.6rem; }
        .data-table th, .data-table td { padding: 10px 8px; font-size: 0.75rem; }
        .section-title { font-size: 1.1rem; }
    }
</style>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Users</div><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo number_format($total_users); ?></div>
        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +12% this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Rooms</div><i class="fas fa-bed"></i></div>
        <div class="stat-number"><?php echo number_format($total_rooms); ?></div>
        <div class="stat-trend"><i class="fas fa-info-circle"></i> 2 under maintenance</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Bookings</div><i class="fas fa-calendar-check"></i></div>
        <div class="stat-number"><?php echo number_format($total_bookings); ?></div>
        <div class="stat-trend"><i class="fas fa-arrow-up"></i> +5% vs last week</div>
    </div>
    <div class="stat-card">
        <div class="stat-header"><div class="stat-label">Total Revenue</div><i class="fas fa-coins"></i></div>
        <div class="stat-number">RM <?php echo number_format($total_revenue, 2); ?></div>
        <div class="stat-trend"><i class="fas fa-chart-line"></i> +18% target</div>
    </div>
</div>

<div class="recent-section">
    <div class="section-title"><i class="fas fa-clock"></i> Recent Bookings</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Ref #</th><th>Customer</th><th>Room</th><th>Check In</th><th>Check Out</th><th>Total (RM)</th><th>Status</th><th>Booked On</th></tr>
            </thead>
            <tbody>
                <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                    <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><span class="ref-link"><?php echo htmlspecialchars($row['booking_ref']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['check_in'])); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['check_out'])); ?></td>
                        <td><?php echo number_format($row['grand_total'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <td><td colspan="9" style="text-align: center; padding: 48px;">No bookings found.<?php echo ' '; ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    (function() {
        const toggle = document.getElementById('themeToggle');
        if (!toggle) return;
        const html = document.documentElement;
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            html.setAttribute('data-theme', 'dark');
            toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
        } else {
            html.setAttribute('data-theme', 'light');
            toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
        }
        toggle.addEventListener('click', () => {
            if (html.getAttribute('data-theme') === 'light') {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
            } else {
                html.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
            }
        });
    })();

    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const container = this.nextElementSibling;
            if (container && container.classList.contains('dropdown-container')) {
                container.classList.toggle('show');
                const icon = this.querySelector('.fa-chevron-down');
                if (icon) icon.classList.toggle('rotate');
            }
        });
    });

    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('mainSidebar');
    if (toggleBtn && sidebar) {
        const updateDisplay = () => {
            toggleBtn.style.display = window.innerWidth <= 768 ? 'block' : 'none';
            if (window.innerWidth > 768) sidebar.classList.remove('open');
        };
        updateDisplay();
        window.addEventListener('resize', updateDisplay);
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            });
        }
    }
</script>

<?php
// 关闭 main-content 和 content-wrapper 以及 body 标签
?>
    </main>
</div>
</body>
</html>