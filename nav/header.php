<?php
// Ensure connection is available but avoid multiple includes
if (!isset($conn) || !$conn instanceof mysqli) {
    include dirname(__DIR__) . "/render/connection.php"; 
}

// Default app name
$appName = "My App";

// Fetch app name from DB if connection is valid
if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT setting_value FROM system_settings WHERE setting_key = 'app_name' LIMIT 1";
    if ($result = $conn->query($sql)) {
        if ($row = $result->fetch_assoc()) {
            $appName = htmlspecialchars($row['setting_value']);
        }
        $result->free();
    }
}

// Split app name for styling
$parts = explode(' ', $appName, 2);
$firstWord = $parts[0];
$restOfName = $parts[1] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold me-4" href="dashboard.php">
            <img src="../src/image/logo/mventory_logo_no_bg.png" alt="" srcset="" style="width: 2rem;">
            <span class="text-info"><?php echo $firstWord; ?></span><?php echo $restOfName; ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Menu -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="inventory.php">
                        <i class="fa-solid fa-boxes-stacked me-1"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger fw-semibold" href="damage_monitoring.php">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Damage Monitoring
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="accounts.php">
                        <i class="fa-solid fa-wallet me-1"></i> Accounts
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLogs" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-circle-check me-1"></i> Logs
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownLogs">
                        <li>
                            <a class="dropdown-item" href="inventory_logs.php">
                                <i class="fa-solid fa-box-archive me-1"></i> Inventory Logs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="branch_logs.php">
                                <i class="fa-solid fa-code-branch me-1"></i> Branch Logs
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="damage_logs.php">
                                <i class="fa-solid fa-triangle-exclamation me-1 text-danger"></i> Damage Logs
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="activity_log.php">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> All Activity
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>

            <!-- Right Menu -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownProfile" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-circle me-1"></i> Profile
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownProfile">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fa-solid fa-user me-1"></i> View Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="settings.php">
                                <i class="fa-solid fa-gear me-1"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>