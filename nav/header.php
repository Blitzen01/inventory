<?php

// 2. Database Connection
if (!isset($conn) || !$conn instanceof mysqli) {
    // Adjust path if necessary to point to your connection file
    include_once dirname(__DIR__) . "/render/connection.php"; 
}

// 3. Fallback for Session variables
$userType = $_SESSION['user_type'] ?? 'Guest';
$username = $_SESSION['username'] ?? 'User';

// 4. App Name Fetching
$appName = "MVentory"; // Hardcoded default
if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'app_name' LIMIT 1";
    if ($result = $conn->query($sql)) {
        if ($row = $result->fetch_assoc()) { 
            $appName = htmlspecialchars($row['setting_value']); 
        }
        $result->free();
    }
}

// 5. Layout Helpers
$parts = explode(' ', $appName, 2);
$firstWord = $parts[0];
$restOfName = $parts[1] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);

/**
 * Helper function to highlight the current page in the nav
 */
function isActive($pageName, $currentPage) {
    return ($pageName === $currentPage) ? 'active fw-bold border-bottom border-info border-2' : '';
}
?>

<style>
    .navbar-glass {
        background: rgba(0, 0, 0, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 1);
    }
    .nav-link {
        transition: all 0.2s ease-in-out;
        padding: 0.5rem 1rem !important;
        font-size: 0.95rem;
    }
    .nav-link:hover {
        color: #0dcaf0 !important;
        transform: translateY(-1px);
    }
    .dropdown-menu {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-glass shadow-sm fixed-top py-2">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center me-4" href="dashboard.php">
            <img src="../src/image/logo/varay_logo.png" alt="Logo" class="me-2" style="width: 2.2rem; height: auto;">
            <span class="fs-4 tracking-tight">
                <span class="text-info fw-bold"><?php echo $firstWord; ?></span><?php echo $restOfName; ?>
            </span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($userType !== 'Viewer'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('dashboard.php', $currentPage); ?>" href="dashboard.php">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('inventory.php', $currentPage); ?>" href="inventory.php">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> Inventory
                    </a>
                </li>
                <?php if ($userType !== 'Viewer'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('allocation.php', $currentPage); ?>" href="allocation.php">
                        <i class="fa-solid fa-map"></i> Allocation
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if ($userType !== 'Viewer'): ?>
                <li class="nav-item">
                    <a class="nav-link text-warning <?php echo isActive('damage_monitoring.php', $currentPage); ?>" href="damage_monitoring.php">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Damage
                    </a>
                </li>
                <?php endif; ?>

                <?php if (!in_array($userType, ['Viewer', 'Stock Handler'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('accounts.php', $currentPage); ?>" href="accounts.php">
                        <i class="fa-solid fa-users me-1"></i> Accounts
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($userType !== 'Viewer'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="logDrop" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-file-lines me-1"></i> Logs
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark border-0 shadow" style="border-radius: 12px;">
                        <li><a class="dropdown-item py-2" href="inventory_logs.php"><i class="fa-solid fa-box-archive me-2 text-info"></i> Inventory Logs</a></li>
                        <li><a class="dropdown-item py-2" href="head_office_logs.php"><i class="fa-solid fa-building me-2 text-info"></i> Head Office Logs</a></li>
                        <li><a class="dropdown-item py-2" href="branch_logs.php"><i class="fa-solid fa-code-branch me-2 text-info"></i> Branch Logs</a></li>
                        <li><a class="dropdown-item py-2" href="system_logs.php"><i class="fa-solid fa-gears text-info"></i> System Logs</a></li>
                        <li><hr class="dropdown-divider opacity-25"></li>
                        <li><a class="dropdown-item py-2" href="activity_log.php"><i class="fa-solid fa-clock-rotate-left me-2 text-info"></i> Full Activity</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle rounded-pill px-3 d-flex align-items-center" href="#" id="profileDrop" data-bs-toggle="dropdown" style="background: rgba(255,255,255,0.05);">
                        <i class="fa-solid fa-circle-user me-1 text-info fs-5"></i> 
                        <span class="small fw-semibold"><?php echo strtoupper(htmlspecialchars($username)); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark border-0 shadow-lg mt-2" style="border-radius: 15px;">
                        <li><a class="dropdown-item py-2" href="profile.php"><i class="fa-solid fa-user me-2"></i> My Profile</a></li>
                        
                        <?php if ($userType === 'Administrator'): ?>
                        <li><a class="dropdown-item py-2" href="settings.php"><i class="fa-solid fa-sliders me-2"></i> System Settings</a></li>
                        <?php endif; ?>

                        <li><hr class="dropdown-divider opacity-25"></li>
                        <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php"><i class="fa-solid fa-power-off me-2"></i> Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>