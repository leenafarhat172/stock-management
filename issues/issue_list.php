<?php
require_once '../config.php';
requireLogin();
$page_title = 'Issue Records';

// Filters
$search = sanitize($conn, $_GET['search'] ?? '');
$from_date = sanitize($conn, $_GET['from_date'] ?? '');
$to_date = sanitize($conn, $_GET['to_date'] ?? '');

$where = "WHERE 1=1";
if ($search) $where .= " AND (i.name LIKE '%$search%' OR si.issued_to LIKE '%$search%')";
if ($from_date) $where .= " AND si.issue_date >= '$from_date'";
if ($to_date) $where .= " AND si.issue_date <= '$to_date'";

$records = $conn->query("
    SELECT si.*, i.name as item_name, i.unit, d.name as dept_name, u.full_name as issued_by_name
    FROM stock_issues si
    JOIN items i ON si.item_id = i.id
    LEFT JOIN departments d ON si.department_id = d.id
    LEFT JOIN users u ON si.issued_by = u.id
    $where
    ORDER BY si.created_at DESC
");

$total_records = $records->num_rows;

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Issue Records</h1>
        <p><?= $total_records ?> record(s) found</p>
    </div>
    <a href="issue_item.php" class="btn btn-primary">📤 Issue New Item</a>
</div>

<!-- Filters -->
<div class="card" style="padding:16px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div class="form-group" style="margin:0;flex:1;min-width:180px;">
            <label>Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Item name or person..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
        </div>
        <button type="submit" class="btn btn-primary" style="height:38px;">🔍 Filter</button>
        <a href="issue_list.php" class="btn btn-outline" style="height:38px;">Clear</a>
    </form>
</div>

<!-- Records Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Issued To</th>
                <th>Department</th>
                <th>Qty Issued</th>
                <th>Issue Date</th>
                <th>Purpose</th>
                <th>Issued By</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($records->num_rows > 0):
                $i = 1;
                while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td class="mono" style="color:var(--text2);"><?= $i++ ?></td>
                <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>
                <td><?= htmlspecialchars($row['issued_to']) ?></td>
                <td>
                    <?php if ($row['dept_name']): ?>
                    <span class="badge badge-blue"><?= htmlspecialchars($row['dept_name']) ?></span>
                    <?php else: ?>
                    <span style="color:var(--text2);">—</span>
                    <?php endif; ?>
                </td>
                <td class="mono"><?= $row['quantity_issued'] ?> <?= htmlspecialchars($row['unit']) ?></td>
                <td class="mono" style="font-size:12px;"><?= date('d M Y', strtotime($row['issue_date'])) ?></td>
                <td style="font-size:13px;color:var(--text2);">
                    <?= $row['purpose'] ? htmlspecialchars(substr($row['purpose'], 0, 40)) . '...' : '—' ?>
                </td>
                <td style="font-size:12px;color:var(--text2);">
                    <?= htmlspecialchars($row['issued_by_name'] ?? 'N/A') ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <span class="empty-icon">📭</span>
                        <h3>No records found</h3>
                        <p>No issue records match your filters.</p>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
