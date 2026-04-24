<?php
require_once '../config.php';
requireLogin();
$page_title = 'Search';

$query = sanitize($conn, $_GET['q'] ?? '');
$items = null;
$issues = null;

if ($query) {
    $items = $conn->query("
        SELECT i.*, c.name as cat_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.name LIKE '%$query%'
           OR i.supplier LIKE '%$query%'
           OR c.name LIKE '%$query%'
           OR i.description LIKE '%$query%'
        ORDER BY i.name ASC
    ");

    $issues = $conn->query("
        SELECT si.*, i.name as item_name, i.unit, d.name as dept_name
        FROM stock_issues si
        JOIN items i ON si.item_id = i.id
        LEFT JOIN departments d ON si.department_id = d.id
        WHERE i.name LIKE '%$query%'
           OR si.issued_to LIKE '%$query%'
           OR d.name LIKE '%$query%'
        ORDER BY si.issue_date DESC
        LIMIT 20
    ");
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Search</h1>
        <p>Search across items and issue records</p>
    </div>
</div>

<!-- Search Bar -->
<div class="card" style="padding:20px;">
    <form method="GET" action="">
        <div style="display:flex;gap:10px;">
            <input type="text" name="q" class="form-control"
                   placeholder="Search items, suppliers, categories, issued to..."
                   value="<?= htmlspecialchars($query) ?>"
                   autofocus style="font-size:15px;padding:11px 14px;">
            <button type="submit" class="btn btn-primary" style="padding:11px 24px;font-size:14px;">🔍 Search</button>
            <?php if ($query): ?>
            <a href="search.php" class="btn btn-outline" style="padding:11px 14px;">✕ Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($query): ?>

<!-- Items Results -->
<div class="card">
    <div class="card-title">📦 Items — <?= $items->num_rows ?> result(s) for "<?= htmlspecialchars($query) ?>"</div>
    <?php if ($items->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <?php if (isAdmin()): ?><th>Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $items->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($row['cat_name'] ?? '—') ?></span></td>
                    <td class="mono <?= $row['quantity'] <= 5 ? 'low-stock' : '' ?>"><?= $row['quantity'] ?></td>
                    <td class="mono" style="color:var(--text2);"><?= htmlspecialchars($row['unit']) ?></td>
                    <td><?= htmlspecialchars($row['supplier'] ?? '—') ?></td>
                    <td>
                        <?php if ($row['quantity'] == 0): ?>
                            <span class="badge badge-red">Out of Stock</span>
                        <?php elseif ($row['quantity'] <= 5): ?>
                            <span class="badge badge-yellow">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-green">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <?php if (isAdmin()): ?>
                    <td><a href="../items/edit.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a></td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <span class="empty-icon">🔍</span>
        <h3>No items found</h3>
        <p>No items matched "<?= htmlspecialchars($query) ?>".</p>
    </div>
    <?php endif; ?>
</div>

<!-- Issue Records Results -->
<div class="card">
    <div class="card-title">📤 Issue Records — <?= $issues->num_rows ?> result(s)</div>
    <?php if ($issues->num_rows > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Issued To</th>
                    <th>Department</th>
                    <th>Qty</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $issues->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                    <td><?= htmlspecialchars($row['issued_to']) ?></td>
                    <td><?= $row['dept_name'] ? '<span class="badge badge-blue">'.htmlspecialchars($row['dept_name']).'</span>' : '—' ?></td>
                    <td class="mono"><?= $row['quantity_issued'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                    <td class="mono" style="font-size:12px;"><?= date('d M Y', strtotime($row['issue_date'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <span class="empty-icon">📭</span>
        <h3>No issue records found</h3>
        <p>No issue records matched your search.</p>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- Default state -->
<div style="text-align:center;padding:60px 20px;color:var(--text2);">
    <div style="font-size:52px;margin-bottom:16px;">🔍</div>
    <h2 style="color:var(--text);margin-bottom:8px;">Search the Stock System</h2>
    <p>Enter a keyword above to search items, categories, suppliers, or issue records.</p>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
