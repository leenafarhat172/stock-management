<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$page_title = 'Edit Item';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: list.php");
    exit();
}

$item = $conn->query("SELECT * FROM items WHERE id = $id")->fetch_assoc();
if (!$item) {
    header("Location: list.php?msg=notfound");
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$error = '';

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
        $stmt = $conn->prepare("UPDATE items SET name=?, category_id=?, quantity=?, unit=?, supplier=?, description=?, date_added=? WHERE id=?");
        $stmt->bind_param("siissssi", $name, $category_id, $quantity, $unit, $supplier, $description, $date_added, $id);
        if ($stmt->execute()) {
            header("Location: list.php?msg=updated");
            exit();
        } else {
            $error = 'Failed to update item.';
        }
        $stmt->close();
    }
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Edit Item</h1>
        <p>Update stock item details</p>
    </div>
    <a href="list.php" class="btn btn-outline">← Back to Items</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">✏️ Edit: <?= htmlspecialchars($item['name']) ?></div>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($_POST['name'] ?? $item['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— Select Category —</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= (($_POST['category_id'] ?? $item['category_id']) == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" class="form-control" min="0"
                       value="<?= htmlspecialchars($_POST['quantity'] ?? $item['quantity']) ?>" required>
            </div>
            <div class="form-group">
                <label>Unit</label>
                <select name="unit" class="form-control">
                    <?php
                    $units = ['pcs','reams','boxes','sets','kg','litre','dozen','rolls','packets'];
                    foreach ($units as $u): ?>
                    <option value="<?= $u ?>" <?= (($_POST['unit'] ?? $item['unit']) === $u) ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="supplier" class="form-control"
                       value="<?= htmlspecialchars($_POST['supplier'] ?? $item['supplier']) ?>">
            </div>
            <div class="form-group">
                <label>Date Added</label>
                <input type="date" name="date_added" class="form-control"
                       value="<?= htmlspecialchars($_POST['date_added'] ?? $item['date_added']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? $item['description']) ?></textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="list.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
