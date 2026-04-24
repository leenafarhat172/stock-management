<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$page_title = 'Add Item';

$error = '';
$success = '';

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unit = sanitize($conn, $_POST['unit'] ?? 'pcs');
    $supplier = sanitize($conn, $_POST['supplier'] ?? '');
    $description = sanitize($conn, $_POST['description'] ?? '');
    $date_added = sanitize($conn, $_POST['date_added'] ?? date('Y-m-d'));

    if (empty($name)) {
        $error = 'Item name is required.';
    } elseif ($quantity < 0) {
        $error = 'Quantity cannot be negative.';
    } else {
        $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity, unit, supplier, description, date_added) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissss", $name, $category_id, $quantity, $unit, $supplier, $description, $date_added);
        if ($stmt->execute()) {
            header("Location: list.php?msg=added");
            exit();
        } else {
            $error = 'Failed to add item. Please try again.';
        }
        $stmt->close();
    }
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Add New Item</h1>
        <p>Add a new stock item to the system</p>
    </div>
    <a href="list.php" class="btn btn-outline">← Back to Items</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">📦 Item Details</div>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="name" class="form-control"
                       placeholder="e.g. A4 Paper Ream"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— Select Category —</option>
                    <?php
                    $categories->data_seek(0);
                    while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" class="form-control"
                       placeholder="0" min="0"
                       value="<?= htmlspecialchars($_POST['quantity'] ?? '0') ?>" required>
            </div>
            <div class="form-group">
                <label>Unit</label>
                <select name="unit" class="form-control">
                    <?php
                    $units = ['pcs','reams','boxes','sets','kg','litre','dozen','rolls','packets'];
                    foreach ($units as $u): ?>
                    <option value="<?= $u ?>" <?= ($_POST['unit'] ?? 'pcs') === $u ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="supplier" class="form-control"
                       placeholder="e.g. OfficeSupplies Co."
                       value="<?= htmlspecialchars($_POST['supplier'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Date Added</label>
                <input type="date" name="date_added" class="form-control"
                       value="<?= htmlspecialchars($_POST['date_added'] ?? date('Y-m-d')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"
                      placeholder="Optional description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">✅ Add Item</button>
            <a href="list.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>