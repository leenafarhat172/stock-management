<?php
require_once '../config.php';
requireLogin();
$page_title = 'Issue Item';

$error = '';
$success = '';

$items = $conn->query("SELECT * FROM items WHERE quantity > 0 ORDER BY name");
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $issued_to = sanitize($conn, $_POST['issued_to'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $quantity_issued = (int)($_POST['quantity_issued'] ?? 0);
    $issue_date = sanitize($conn, $_POST['issue_date'] ?? date('Y-m-d'));
    $purpose = sanitize($conn, $_POST['purpose'] ?? '');

    // Validate
    if (!$item_id || empty($issued_to) || $quantity_issued <= 0) {
        $error = 'Please fill in all required fields with valid values.';
    } else {
        // Check available quantity
        $item_check = $conn->query("SELECT quantity, name FROM items WHERE id = $item_id")->fetch_assoc();
        if (!$item_check) {
            $error = 'Item not found.';
        } elseif ($quantity_issued > $item_check['quantity']) {
            $error = "Not enough stock! Only <strong>{$item_check['quantity']}</strong> unit(s) available for '{$item_check['name']}'.";
        } else {
            // Insert issue record
            $stmt = $conn->prepare("INSERT INTO stock_issues (item_id, issued_to, department_id, quantity_issued, issue_date, purpose, issued_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiissi", $item_id, $issued_to, $department_id, $quantity_issued, $issue_date, $purpose, $_SESSION['user_id']);

            if ($stmt->execute()) {
                // Deduct from stock
                $conn->query("UPDATE items SET quantity = quantity - $quantity_issued WHERE id = $item_id");
                $success = "✅ Successfully issued <strong>$quantity_issued</strong> unit(s) of <strong>{$item_check['name']}</strong> to <strong>$issued_to</strong>.";
                // Reset items list for updated quantities
                $items = $conn->query("SELECT * FROM items WHERE quantity > 0 ORDER BY name");
            } else {
                $error = 'Failed to record issue. Please try again.';
            }
            $stmt->close();
        }
    }
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Issue Item</h1>
        <p>Record stock issued to a person or department</p>
    </div>
    <a href="issue_list.php" class="btn btn-outline">📃 View Issue Records</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;">

<div class="card">
    <div class="card-title">📤 Issue Form</div>
    <form method="POST" action="">
        <div class="form-group">
            <label>Select Item *</label>
            <select name="item_id" class="form-control" id="itemSelect" onchange="updateQty()" required>
                <option value="">— Choose Item —</option>
                <?php while ($row = $items->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"
                        data-qty="<?= $row['quantity'] ?>"
                        data-unit="<?= htmlspecialchars($row['unit']) ?>"
                        <?= ($_POST['item_id'] ?? '') == $row['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?> (Available: <?= $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?>)
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="stockInfo" style="display:none;margin-bottom:14px;">
            <div class="alert alert-warning" style="margin:0;">
                📦 Available Stock: <strong id="availQty">—</strong> <span id="availUnit"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Issued To (Person Name) *</label>
                <input type="text" name="issued_to" class="form-control"
                       placeholder="e.g. Dr. Ramesh Kumar"
                       value="<?= htmlspecialchars($_POST['issued_to'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department_id" class="form-control">
                    <option value="">— Select Department —</option>
                    <?php
                    $departments->data_seek(0);
                    while ($dept = $departments->fetch_assoc()): ?>
                    <option value="<?= $dept['id'] ?>" <?= ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Quantity to Issue *</label>
                <input type="number" name="quantity_issued" id="qtyInput" class="form-control"
                       placeholder="0" min="1"
                       value="<?= htmlspecialchars($_POST['quantity_issued'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Issue Date *</label>
                <input type="date" name="issue_date" class="form-control"
                       value="<?= htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Purpose / Remarks</label>
            <textarea name="purpose" class="form-control"
                      placeholder="e.g. For departmental exam use..."><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-success">📤 Issue Item</button>
            <a href="issue_list.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<!-- Quick Guide -->
<div>
    <div class="card">
        <div class="card-title">ℹ️ How It Works</div>
        <div style="font-size:13px;color:var(--text2);line-height:1.8;">
            <p>1. Select the item you want to issue from the dropdown.</p><br>
            <p>2. Enter the name of the person receiving the item.</p><br>
            <p>3. Select their department (optional).</p><br>
            <p>4. Enter the quantity — cannot exceed available stock.</p><br>
            <p>5. Click <strong style="color:var(--accent2);">Issue Item</strong> to record and deduct from stock.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-title">⚠️ Low Stock Items</div>
        <?php
        $low = $conn->query("SELECT name, quantity, unit FROM items WHERE quantity <= 5 ORDER BY quantity ASC LIMIT 5");
        if ($low->num_rows > 0): ?>
        <div style="font-size:13px;">
            <?php while ($l = $low->fetch_assoc()): ?>
            <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);">
                <span><?= htmlspecialchars($l['name']) ?></span>
                <span class="mono low-stock"><?= $l['quantity'] ?> <?= htmlspecialchars($l['unit']) ?></span>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p style="font-size:13px;color:var(--text2);">✅ All items have sufficient stock.</p>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
function updateQty() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('stockInfo');
    const qtyInput = document.getElementById('qtyInput');

    if (sel.value) {
        const qty = opt.getAttribute('data-qty');
        const unit = opt.getAttribute('data-unit');
        document.getElementById('availQty').textContent = qty;
        document.getElementById('availUnit').textContent = unit;
        info.style.display = 'block';
        qtyInput.max = qty;
    } else {
        info.style.display = 'none';
        qtyInput.removeAttribute('max');
    }
}
// Run on page load if item pre-selected
window.addEventListener('load', updateQty);
</script>

<?php include '../includes/footer.php'; ?>
