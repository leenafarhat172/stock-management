<?php
require_once '../config.php';
requireLogin();
$page_title = 'Change Password';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validations
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif ($new_password === $current_password) {
        $error = 'New password must be different from current password.';
    } else {
        // Get current password from DB
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT password FROM users WHERE id = $user_id");
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update password
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            if ($stmt->execute()) {
                $success = 'Password changed successfully! Please use your new password next time you login.';
            } else {
                $error = 'Failed to update password. Please try again.';
            }
            $stmt->close();
        }
    }
}

include '../includes/sidebar.php';
?>

<div class="page-header">
    <div>
        <h1>Change Password</h1>
        <p>Update your account password</p>
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
        <div class="card-title">🔐 Change Your Password</div>

        <!-- User Info -->
        <div style="background:var(--bg3);border-radius:8px;padding:14px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
            <div style="font-size:32px;">👤</div>
            <div>
                <div style="font-weight:600;color:var(--text);"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:12px;color:var(--text2);font-family:var(--mono);">
                    @<?= htmlspecialchars($_SESSION['username']) ?> &nbsp;·&nbsp;
                    <span style="text-transform:capitalize;"><?= $_SESSION['role'] ?></span>
                </div>
            </div>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label>Current Password *</label>
                <div style="position:relative;">
                    <input type="password" name="current_password" id="cur_pass"
                           class="form-control" placeholder="Enter current password"
                           style="padding-right:44px;" required>
                    <button type="button" onclick="togglePass('cur_pass','eye1')"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text2);">
                        <span id="eye1">👁️</span>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>New Password *</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="new_pass"
                           class="form-control" placeholder="Min. 6 characters"
                           style="padding-right:44px;" required oninput="checkStrength(this.value)">
                    <button type="button" onclick="togglePass('new_pass','eye2')"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text2);">
                        <span id="eye2">👁️</span>
                    </button>
                </div>
                <!-- Password strength bar -->
                <div style="margin-top:8px;">
                    <div style="height:4px;background:var(--border);border-radius:2px;overflow:hidden;">
                        <div id="strength-bar" style="height:100%;width:0%;transition:all 0.3s;border-radius:2px;"></div>
                    </div>
                    <div id="strength-text" style="font-size:11px;font-family:var(--mono);margin-top:4px;color:var(--text2);"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password *</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="con_pass"
                           class="form-control" placeholder="Re-enter new password"
                           style="padding-right:44px;" required oninput="checkMatch()">
                    <button type="button" onclick="togglePass('con_pass','eye3')"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--text2);">
                        <span id="eye3">👁️</span>
                    </button>
                </div>
                <div id="match-text" style="font-size:11px;font-family:var(--mono);margin-top:4px;"></div>
            </div>

            <!-- Rules -->
            <div style="background:var(--bg3);border-radius:6px;padding:12px;margin-bottom:18px;font-size:12px;color:var(--text2);line-height:1.8;">
                <strong style="color:var(--text);">Password Rules:</strong><br>
                ✔ At least 6 characters long<br>
                ✔ Must be different from current password<br>
                ✔ Both new passwords must match
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
                    🔐 Change Password
                </button>
                <a href="<?= BASE_URL ?>dashboard.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle password visibility
function togglePass(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type = 'text';
        eye.textContent = '🙈';
    } else {
        input.type = 'password';
        eye.textContent = '👁️';
    }
}

// Password strength checker
function checkStrength(val) {
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    let strength = 0;

    if (val.length >= 6) strength++;
    if (val.length >= 10) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const levels = [
        { label: '', color: '', width: '0%' },
        { label: 'Very Weak', color: '#ff6b6b', width: '20%' },
        { label: 'Weak', color: '#ff9f43', width: '40%' },
        { label: 'Fair', color: '#ffd43b', width: '60%' },
        { label: 'Strong', color: '#38d9a9', width: '80%' },
        { label: 'Very Strong', color: '#4f8ef7', width: '100%' },
    ];

    const level = levels[strength] || levels[0];
    bar.style.width = level.width;
    bar.style.backgroundColor = level.color;
    text.textContent = level.label ? '🔒 Strength: ' + level.label : '';
    text.style.color = level.color;

    checkMatch();
}

// Match checker
function checkMatch() {
    const newP = document.getElementById('new_pass').value;
    const conP = document.getElementById('con_pass').value;
    const text = document.getElementById('match-text');

    if (conP.length === 0) {
        text.textContent = '';
        return;
    }
    if (newP === conP) {
        text.textContent = '✅ Passwords match';
        text.style.color = 'var(--accent2)';
    } else {
        text.textContent = '❌ Passwords do not match';
        text.style.color = 'var(--danger)';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
