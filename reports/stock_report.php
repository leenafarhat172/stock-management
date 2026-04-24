<?php
require_once '../config.php';
requireLogin();
$page_title = 'Stock Report';

// Summary stats
$total_items = $conn->query("SELECT COUNT(*) as c FROM items")->fetch_assoc()['c'];
$total_qty = $conn->query("SELECT SUM(quantity) as c FROM items")->fetch_assoc()['c'] ?? 0;
$out_of_stock = $conn->query("SELECT COUNT(*) as c FROM items WHERE quantity = 0")->fetch_assoc()['c'];
$low_stock = $conn->query("SELECT COUNT(*) as c FROM items WHERE quantity > 0 AND quantity <= 5")->fetch_assoc()['c'];
$total_issued = $conn->query("SELECT SUM(quantity_issued) as c FROM stock_issues")->fetch_assoc()['c'] ?? 0;

// Filter
$filter = $_GET['filter'] ?? 'all';
$where = "WHERE 1=1";
if ($filter === 'low') $where = "WHERE i.quantity > 0 AND i.quantity <= 5";
if ($filter === 'out') $where = "WHERE i.quantity = 0";
if ($filter === 'good') $where = "WHERE i.quantity > 5";

$items = $conn->query("
    SELECT i.*, c.name as cat_name,
           (SELECT SUM(si.quantity_issued) FROM stock_issues si WHERE si.item_id = i.id) as total_issued
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    $where
    ORDER BY i.name ASC
");

include '../includes/sidebar.php';
?>

<style>
@media print {
    .sidebar, .topbar, .no-print { display: none !important; }
    .main { margin-left: 0 !important; }
    body { background: white !important; color: black !important; }
    .card { border: 1px solid #ccc !important; background: white !important; }
    table { color: black !important; }
    thead th { background: #eee !important; color: black !important; }
    tbody tr:hover { background: none !important; }
    .badge { border: 1px solid #999 !important; color: black !important; background: #eee !important; }
    .print-header { display: block !important; }
}
.print-header { display: none; text-align:center; margin-bottom:20px; }
</style>

<div class="page-header no-print">
    <div>
        <h1>Stock Report</h1>
        <p>Complete overview of departmental stock</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" class="btn btn-outline">🖨️ Print Report</button>
    </div>
</div>

<!-- Print Header -->
<div class="print-header">
    <h2>Department of Computer Application and Science</h2>
    <h3>Departmental Stock Management Report</h3>
    <p>Generated on: <?= date('d F Y, h:i A') ?></p>
    <hr>
</div>

<!-- Summary Cards -->
<div class="stats-grid no-print">
    <div class="stat-card blue">
        <span class="stat-icon">📋</span>
        <div class="stat-value"><?= $total_items ?></div>
        <div class="stat-label">Total Item Types</div>
    </div>
    <div class="stat-card green">
        <span class="stat-icon">📦</span>
        <div class="stat-value"><?= number_format($total_qty) ?></div>
        <div class="stat-label">Total Stock Units</div>
    </div>
    <div class="stat-card yellow">
        <span class="stat-icon">📤</span>
        <div class="stat-value"><?= number_format($total_issued) ?></div>
        <div class="stat-label">Total Units Issued</div>
    </div>
    <div class="stat-card red">
        <span class="stat-icon">⛔</span>
        <div class="stat-value"><?= $out_of_stock ?></div>
        <div class="stat-label">Out of Stock</div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="no-print" style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
    <a href="?filter=all" class="btn <?= $filter==='all' ? 'btn-primary' : 'btn-outline' ?>">All Items (<?= $total_items ?>)</a>
    <a href="?filter=good" class="btn <?= $filter==='good' ? 'btn-success' : 'btn-outline' ?>">✅ In Stock</a>
    <a href="?filter=low" class="btn <?= $filter==='low' ? 'btn-outline' : 'btn-outline' ?>" style="<?= $filter==='low' ? 'border-color:var(--warning);color:var(--warning);' : '' ?>">⚠️ Low Stock (<?= $low_stock ?>)</a>
    <a href="?filter=out" class="btn <?= $filter==='out' ? 'btn-danger' : 'btn-outline' ?>">⛔ Out of Stock (<?= $out_of_stock ?>)</a>
</div>

<!-- Report Table -->
<div class="card" style="padding:0;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
        <span class="card-title" style="margin:0;border:none;padding:0;">📊 Stock Inventory</span>
        <span style="font-family:var(--mono);font-size:12px;color:var(--text2);">Report Date: <?= date('d M Y') ?></span>
    </div>
    <div class="table-container" style="border:none;border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Available Qty</th>
                    <th>Unit</th>
                    <th>Total Issued</th>
                    <th>Supplier</th>
                    <th>Date Added</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items->num_rows > 0):
                    $i = 1;
                    while ($row = $items->fetch_assoc()): ?>
                <tr>
                    <td class="mono" style="color:var(--text2);"><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                    <td><span class="badge badge-gray"><?= htmlspecialchars($row['cat_name'] ?? '—') ?></span></td>
                    <td class="mono <?= $row['quantity'] <= 5 ? 'low-stock' : '' ?>"><?= $row['quantity'] ?></td>
                    <td class="mono" style="color:var(--text2);"><?= htmlspecialchars($row['unit']) ?></td>
                    <td class="mono"><?= $row['total_issued'] ?? 0 ?></td>
                    <td style="font-size:13px;"><?= htmlspecialchars($row['supplier'] ?? '—') ?></td>
                    <td class="mono" style="font-size:12px;"><?= $row['date_added'] ? date('d M Y', strtotime($row['date_added'])) : '—' ?></td>
                    <td>
                        <?php if ($row['quantity'] == 0): ?>
                            <span class="badge badge-red">Out of Stock</span>
                        <?php elseif ($row['quantity'] <= 5): ?>
                            <span class="badge badge-yellow">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-green">In Stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <span class="empty-icon">📭</span>
                            <h3>No items found</h3>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px;border-top:1px solid var(--border);font-family:var(--mono);font-size:12px;color:var(--text2);display:flex;justify-content:space-between;">
        <span>Total Items Shown: <strong style="color:var(--text);"><?= $items->num_rows ?></strong></span>
        <span>Department of Computer Application and Science</span>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
