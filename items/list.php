<?php
require_once '../config.php';
requireLogin();
$page_title = 'All Items';

// Handle delete
if (isset($_GET['delete']) && isAdmin()) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM items WHERE id = $id");
    header("Location: list.php?msg=deleted");
    exit();
}

$msg = $_GET['msg'] ?? '';

// Filters
$search = sanitize($conn, $_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);

$where = "WHERE 1=1";
if ($search) $where .= " AND i.name LIKE '%$search%'";
if ($category) $where .= " AND i.category_id = $category";

$items = $conn->query("
    SELECT i.*, c.name as cat_name
    FROM items i
    LEFT JOIN categories c ON i.category_id = c.id
    $where
    ORDER BY i.name ASC
");

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>All Items</h1>
        <p><?= $items->num_rows ?> item(s) found</p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="add.php" class="btn btn-primary">➕ Add Item</a>
    <?php endif; ?>
</div>

<?php if ($msg === 'deleted'): ?>
<div class="alert alert-danger">🗑️ Item deleted successfully.</div>
<?php elseif ($msg === 'added'): ?>
<div class="alert alert-success">✅ Item added successfully.</div>
<?php elseif ($msg === 'updated'): ?>
<div class="alert alert-success">✅ Item updated successfully.</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="padding:16px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div class="form-group" style="margin:0;flex:1;min-width:180px;">
            <label>Search Item</label>
            <input type="text" name="search" class="form-control" placeholder="Item name..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;min-width:160px;">
            <label>Category</label>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="height:38px;">🔍 Filter</button>
        <a href="list.php" class="btn btn-outline" style="height:38px;">Clear</a>
    </form>
</div>

<!-- Items Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Supplier</th>
                <th>Date Added</th>
                <th>Status</th>
                <?php if (isAdmin()): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($items->num_rows > 0):
                $i = 1;
                while ($row = $items->fetch_assoc()): ?>
            <tr>
                <td class="mono" style="color:var(--text2);"><?= $i++ ?></td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong>
                    <?php if ($row['description']): ?>
                    <br><small style="color:var(--text2);font-size:11px;"><?= htmlspecialchars(substr($row['description'],0,50)) ?>...</small>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($row['cat_name'] ?? 'Uncategorized') ?></span></td>
                <td class="mono <?= $row['quantity'] <= 5 ? 'low-stock' : '' ?>"><?= $row['quantity'] ?></td>
                <td class="mono" style="color:var(--text2);"><?= htmlspecialchars($row['unit']) ?></td>
                <td><?= htmlspecialchars($row['supplier'] ?? '—') ?></td>
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
                <?php if (isAdmin()): ?>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                        <a href="list.php?delete=<?= $row['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this item?')">🗑️</a>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <span class="empty-icon">📭</span>
                        <h3>No items found</h3>
                        <p>Try adjusting your search filters.</p>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
