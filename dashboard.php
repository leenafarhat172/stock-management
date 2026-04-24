<?php
require_once 'config.php';
requireLogin();
$page_title = 'Dashboard';

// Stats
$total_items = $conn->query("SELECT COUNT(*) as c FROM items")->fetch_assoc()['c'];
$total_qty = $conn->query("SELECT SUM(quantity) as c FROM items")->fetch_assoc()['c'] ?? 0;
$total_issues = $conn->query("SELECT COUNT(*) as c FROM stock_issues")->fetch_assoc()['c'];
$low_stock_count = $conn->query("SELECT COUNT(*) as c FROM items WHERE quantity <= 5")->fetch_assoc()['c'];

// Recent issues
$recent_issues = $conn->query("
    SELECT si.*, i.name as item_name, d.name as dept_name
    FROM stock_issues si
    JOIN items i ON si.item_id = i.id
    LEFT JOIN departments d ON si.department_id = d.id
    ORDER BY si.created_at DESC LIMIT 6
");

// Low stock items
$low_stock_items = $conn->query("SELECT * FROM items WHERE quantity <= 5 ORDER BY quantity ASC LIMIT 6");

include 'includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?> · <?= date('D, d M Y') ?></p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="items/add.php" class="btn btn-primary">➕ Add New Item</a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <span class="stat-icon">📋</span>
        <div class="stat-value"><?= $total_items ?></div>
        <div class="stat-label">Total Item Types</div>
    </div>
    <div class="stat-card green">
        <span class="stat-icon">📦</span>
        <div class="stat-value"><?= number_format($total_qty) ?></div>
        <div class="stat-label">Total Quantity</div>
    </div>
    <div class="stat-card yellow">
        <span class="stat-icon">📤</span>
        <div class="stat-value"><?= $total_issues ?></div>
        <div class="stat-label">Total Issues</div>
    </div>
    <div class="stat-card red">
        <span class="stat-icon">⚠️</span>
        <div class="stat-value"><?= $low_stock_count ?></div>
        <div class="stat-label">Low Stock Items</div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if ($low_stock_count > 0): ?>
<div class="alert alert-warning">
    ⚠️ <strong><?= $low_stock_count ?> item(s)</strong> are running low on stock (quantity ≤ 5).
    <a href="reports/stock_report.php" style="color:var(--warning);font-weight:600;"> View Report →</a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">

<!-- Recent Issues -->
<div class="card">
    <div class="card-title">📤 Recent Issues</div>
    <?php if ($recent_issues->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Issued To</th>
                    <th>Qty</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recent_issues->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars($row['issued_to']) ?></td>
                    <td class="mono"><?= $row['quantity_issued'] ?></td>
                    <td class="mono" style="font-size:12px;"><?= date('d M Y', strtotime($row['issue_date'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">
        <a href="issues/issue_list.php" class="btn btn-outline btn-sm">View All →</a>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <span class="empty-icon">📭</span>
        <h3>No issues yet</h3>
        <p>No stock has been issued.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Low Stock Items -->
<div class="card">
    <div class="card-title">⚠️ Low Stock Items</div>
    <?php if ($low_stock_items->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $low_stock_items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td class="mono low-stock"><?= $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td>
                        <?php if (isAdmin()): ?>
                        <a href="items/edit.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">Update</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <span class="empty-icon">✅</span>
        <h3>All stocked up!</h3>
        <p>No items are running low.</p>
    </div>
    <?php endif; ?>
</div>

</div>

<?php include 'includes/footer.php'; ?>
