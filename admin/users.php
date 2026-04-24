<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$page_title = 'Manage Users';

$error   = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id === (int)$_SESSION['user_id']) {
        $error = 'You cannot delete your own account.';
    } else {
        $conn->query("DELETE FROM users WHERE id = $del_id");
        $success = 'User deleted successfully.';
    }
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = sanitize($conn, $_POST['username'] ?? '');
    $full_name = sanitize($conn, $_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : 'staff';

    if (empty($username) || empty($full_name) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed, $full_name, $role);
        if ($stmt->execute()) {
            $success = "User '$username' created successfully.";
        } else {
            $error = 'Username already exists or an error occurred.';
        }
        $stmt->close();
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY role, full_name");

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Manage Users</h1>
        <p>Add and manage system users</p>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success">✅ <?= $success ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">

<!-- Add User Form -->
<div class="card">
    <div class="card-title">➕ Add New User</div>
    <form method="POST" action="">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" class="form-control"
                   placeholder="e.g. Dr. Ramesh Kumar"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" class="form-control"
                   placeholder="e.g. ramesh123"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Min. 6 characters" required>
        </div>
        <div class="form-group">
            <label>Role *</label>
            <select name="role" class="form-control">
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            ➕ Create User
        </button>
    </form>
</div>

<!-- Users List -->
<div class="card" style="padding:0;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
        <span class="card-title" style="margin:0;border:none;padding:0;">👥 All Users</span>
    </div>
    <div class="table-container" style="border:none;border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                    <td class="mono" style="font-size:13px;"><?= htmlspecialchars($row['username']) ?></td>
                    <td>
                        <span class="badge <?= $row['role'] === 'admin' ? 'badge-blue' : 'badge-gray' ?>">
                            <?= ucfirst($row['role']) ?>
                        </span>
                    </td>
                    <td class="mono" style="font-size:12px;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <td>
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete=<?= $row['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete user <?= htmlspecialchars($row['username']) ?>?')">
                           🗑️ Delete
                        </a>
                        <?php else: ?>
                        <span style="font-size:12px;color:var(--text2);">(You)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<?php include '../includes/footer.php'; ?>