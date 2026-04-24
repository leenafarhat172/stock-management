<?php
// includes/sidebar.php - Shared layout component
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — ' : '' ?>Stock Management System</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <h2>📦 StockMS</h2>
        <span>Dept. Stock Management</span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main</div>
        <a href="<?= BASE_URL ?>dashboard.php"
           class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">🏠</span> Dashboard
        </a>

        <div class="nav-section-label">Stock</div>
        <a href="<?= BASE_URL ?>items/list.php"
           class="nav-link <?= in_array($current_page, ['list.php','add.php','edit.php']) && strpos($_SERVER['PHP_SELF'],'items') !== false ? 'active' : '' ?>">
            <span class="icon">📋</span> All Items
        </a>
        <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>items/add.php"
           class="nav-link <?= $current_page === 'add.php' && strpos($_SERVER['PHP_SELF'],'items') !== false ? 'active' : '' ?>">
            <span class="icon">➕</span> Add Item
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Issues</div>
        <a href="<?= BASE_URL ?>issues/issue_item.php"
           class="nav-link <?= $current_page === 'issue_item.php' ? 'active' : '' ?>">
            <span class="icon">📤</span> Issue Item
        </a>
        <a href="<?= BASE_URL ?>issues/issue_list.php"
           class="nav-link <?= $current_page === 'issue_list.php' ? 'active' : '' ?>">
            <span class="icon">📃</span> Issue Records
        </a>

        <div class="nav-section-label">Reports</div>
        <a href="<?= BASE_URL ?>reports/stock_report.php"
           class="nav-link <?= $current_page === 'stock_report.php' ? 'active' : '' ?>">
            <span class="icon">📊</span> Stock Report
        </a>

        <div class="nav-section-label">Tools</div>
        <a href="<?= BASE_URL ?>search/search.php"
           class="nav-link <?= $current_page === 'search.php' ? 'active' : '' ?>">
            <span class="icon">🔍</span> Search
        </a>
        <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>admin/users.php"
           class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>">
            <span class="icon">👥</span> Manage Users
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></strong>
        <span style="text-transform:capitalize;"><?= $_SESSION['role'] ?? '' ?></span>
        &nbsp;·&nbsp; <a href="<?= BASE_URL ?>logout.php" style="color:var(--danger);">Logout</a>
    </div>
</aside>

<!-- Main Area -->
<div class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <span class="topbar-title">
                <?= SITE_NAME ?> &nbsp;/&nbsp; <strong><?= $page_title ?? 'Dashboard' ?></strong>
            </span>
        </div>
        <div class="topbar-actions">
            <a href="<?= BASE_URL ?>search/search.php" class="btn btn-outline btn-sm">🔍 Search</a>
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
    <div class="content">
