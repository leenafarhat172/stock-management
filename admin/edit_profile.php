<?php
require_once '../config.php';
requireLogin();
$page_title = 'Edit Profile';

$error   = '';
$success = '';

$user_id = $_SESSION['user_id'];

// Get current user details
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user   = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username  = sanitize($conn, $_POST['username'] ?? '');
    $new_full_name = sanitize($conn, $_POST['full_name'] ?? '');

    // Validations
    if (empty($new_username) || empty($new_full_name)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $error = 'Username can only contain letters, numbers and underscore (_). No spaces allowed.';
    } else {
        // Check if username already taken by another user
        $check = $conn->query("SELECT id FROM users WHERE username = '$new_username' AND id != $user_id");
        if ($check->num_rows > 0) {
            $error = 'This username is already taken. Please choose another one.';
        } else {
            // Update profile
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_username, $new_full_name, $user_id);
            if ($stmt->execute()) {
                // Update session
                $_SESSION['username']  = $new_username;
                $_SESSION['full_name'] = $new_full_name;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $user['username']  = $new_username;
                $user['full_name'] = $new_full_name;
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
            $stmt->close();
        }
    }
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Edit Profile</h1>
        <p>Update your username and display name</p>
    </div>
    <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success">✅ <?= $success ?></div>
<?php endif; ?>

<div style="max-width:480px;">
    <div class="card">
        <div class="card-title">👤 Your Profile</div>

        <!-- Current Info -->
        <div style="background:var(--bg3);border-radius:8px;padding:16px;margin-bottom:24px;">
            <div style="font-size:12px;color:var(--text2);font-family:var(--mono);margin-bottom:10px;letter-spacing:0.06em;text-transform:uppercase;">Current Details</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text2);">Full Name</span>
                    <span style="color:var(--text);font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text2);">Username</span>
                    <span style="color:var(--accent);font-family:var(--mono);">@<?= htmlspecialchars($user['username']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text2);">Role</span>
                    <span class="badge <?= $user['role'] === 'admin' ? 'badge-blue' : 'badge-gray' ?>"><?= ucfirst($user['role']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text2);">Member Since</span>
                    <span style="color:var(--text);font-family:var(--mono);font-size:12px;"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control"
                       placeholder="e.g. Dr. Ramesh Kumar"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? $user['full_name']) ?>"
                       required>
                <div style="font-size:11px;color:var(--text2);margin-top:5px;font-family:var(--mono);">
                    This name is shown in the sidebar and dashboard.
                </div>
            </div>

            <div class="form-group">
                <label>Username *</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text2);font-family:var(--mono);">@</span>
                    <input type="text" name="username" class="form-control"
                           placeholder="e.g. admin123"
                           value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>"
                           style="padding-left:28px;font-family:var(--mono);"
                           oninput="checkUsername(this.value)"
                           required>
                </div>
                <div id="username-hint" style="font-size:11px;margin-top:5px;font-family:var(--mono);color:var(--text2);">
                    Only letters, numbers and underscore. No spaces.
                </div>
            </div>

            <!-- Warning -->
            <div style="background:rgba(255,212,59,0.08);border:1px solid rgba(255,212,59,0.25);border-radius:6px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--warning);line-height:1.7;">
                ⚠️ <strong>Note:</strong> After changing your username, you must use the <strong>new username</strong> to login next time.
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
                    💾 Save Changes
                </button>
                <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Quick Links -->
    <div class="card">
        <div class="card-title">🔗 Quick Links</div>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="change_password.php" class="btn btn-outline" style="justify-content:flex-start;gap:10px;">
                🔐 Change Password
            </a>
            <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-outline" style="justify-content:flex-start;gap:10px;">
                🏠 Back to Dashboard
            </a>
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger" style="justify-content:flex-start;gap:10px;">
                🚪 Logout
            </a>
        </div>
    </div>
</div>

<script>
function checkUsername(val) {
    const hint = document.getElementById('username-hint');
    const valid = /^[a-zA-Z0-9_]*$/.test(val);

    if (val.length === 0) {
        hint.textContent = 'Only letters, numbers and underscore. No spaces.';
        hint.style.color = 'var(--text2)';
    } else if (!valid) {
        hint.textContent = '❌ Invalid characters! Only letters, numbers and _ allowed.';
        hint.style.color = 'var(--danger)';
    } else if (val.length < 3) {
        hint.textContent = '⚠️ Username too short. Minimum 3 characters.';
        hint.style.color = 'var(--warning)';
    } else {
        hint.textContent = '✅ Username looks good!';
        hint.style.color = 'var(--accent2)';
    }
}
</script>

<?php include '../includes/footer.php'; ?>