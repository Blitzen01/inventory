<?php
    // Include the necessary files
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";

    // --- Fetch System Settings ---
    $settings = [];
    if (isset($conn)) {
        $sql_settings = "SELECT setting_key, setting_value FROM system_settings";
        $result_settings = $conn->query($sql_settings);
        
        if ($result_settings && $result_settings->num_rows > 0) {
            while ($row = $result_settings->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Settings | Stock Focus</title>
    <style>
        body { background-color: #f4f7f6; padding-top: 80px; }
        .settings-card { border: none; border-radius: 15px; overflow: hidden; }
        
        /* Sidebar Styling */
        .settings-nav { background-color: #fff; height: 100%; }
        .settings-nav .nav-link {
            text-align: left;
            padding: 1.2rem 1.5rem;
            color: #495057;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.2s;
            font-weight: 500;
        }
        .settings-nav .nav-link i { width: 25px; font-size: 1.1rem; }
        .settings-nav .nav-link:hover { background-color: #f8f9fa; color: #0d6efd; }
        .settings-nav .nav-link.active {
            color: #0d6efd;
            background-color: #e7f1ff;
            border-left: 4px solid #0d6efd;
        }

        /* Form Styling */
        .form-label { font-weight: 600; font-size: 0.9rem; color: #333; margin-top: 10px; }
        .form-control, .form-select { border-radius: 8px; padding: 10px; }
        .section-title { font-weight: 700; color: #212529; margin-bottom: 0.5rem; }
    </style>
</head>

<body>
    <?php include "../nav/header.php"; ?> 

    <div class="container pb-5">
        <div class="row mb-4">
            <div class="col-12 d-md-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark"><i class="fa-solid fa-gears me-2 text-primary"></i> System Settings</h2>
                    <p class="text-muted">Global configuration for application behavior and inventory logic.</p>
                </div>
                
                <?php if (isset($_GET['status'])): ?>
                    <div class="alert <?= ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show shadow-sm mb-0" role="alert">
                        <i class="fa-solid <?= ($_GET['status'] == 'success') ? 'fa-check-circle' : 'fa-circle-xmark'; ?> me-2"></i>
                        <?= htmlspecialchars($_GET['message'] ?? ''); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm settings-card">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-lg-3 border-end">
                        <div class="nav flex-column nav-pills settings-nav" id="settingsTabs" role="tablist">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab"><i class="fa-solid fa-desktop me-2"></i> General</button>
                            <button class="nav-link" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory" type="button" role="tab"><i class="fa-solid fa-boxes-stacked me-2"></i> Inventory Rules</button>
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab"><i class="fa-solid fa-bell me-2"></i> Notifications</button>
                            <button class="nav-link" id="backup-tab" data-bs-toggle="pill" data-bs-target="#backup" type="button" role="tab"><i class="fa-solid fa-database me-2"></i> Backup & Data</button>
                        </div>
                    </div>

                    <div class="col-lg-9 bg-white">
                        <div class="tab-content p-4 p-md-5" id="settingsTabContent">
                            
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <h4 class="section-title">General Configuration</h4>
                                <p class="small text-muted mb-4">Update the application identity and regional formats.</p>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="mb-3">
                                        <label class="form-label">Application Name</label>
                                        <input type="text" class="form-control" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'Inventory Manager Pro'); ?>" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Date Display Format</label>
                                        <select class="form-select" name="date_format">
                                            <option value="YYYY-MM-DD" <?= (($settings['date_format'] ?? '') == 'YYYY-MM-DD') ? 'selected' : ''; ?>>ISO (2025-12-05)</option>
                                            <option value="MM/DD/YYYY" <?= (($settings['date_format'] ?? '') == 'MM/DD/YYYY') ? 'selected' : ''; ?>>US (12/05/2025)</option>
                                            <option value="DD/MM/YYYY" <?= (($settings['date_format'] ?? '') == 'DD/MM/YYYY') ? 'selected' : ''; ?>>UK/EU (05/12/2025)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Save General</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="inventory" role="tabpanel">
                                <h4 class="section-title">Inventory Rules & Logic</h4>
                                <p class="small text-muted mb-4">Define how the system calculates stock health and lifecycle.</p>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Low Stock Threshold (%)</label>
                                            <input type="number" class="form-control" name="low_stock_threshold_percent" value="<?= htmlspecialchars($settings['low_stock_threshold_percent'] ?? '15'); ?>" min="1" max="100">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Liquidation Alert (%)</label>
                                            <input type="number" class="form-control" name="liquidation_percentage" value="<?= htmlspecialchars($settings['liquidation_percentage'] ?? '20'); ?>" min="0" max="100">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Standard EOL Duration (Years)</label>
                                        <input type="number" class="form-control" name="eol_duration_years" value="<?= htmlspecialchars($settings['eol_duration_years'] ?? '3'); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Save Rules</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="notifications" role="tabpanel">
                                <h4 class="section-title">Notification Channels</h4>
                                <p class="small text-muted mb-4">Choose which events trigger system-wide alerts.</p>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="list-group list-group-flush border rounded mb-4">
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Low Stock Warnings</h6>
                                                <small class="text-muted">Notify admins when items reach threshold.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="trigger_low_stock" value="1" <?= (($settings['trigger_low_stock'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">New User Registration</h6>
                                                <small class="text-muted">Alert system when a new account is created.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="trigger_new_user" value="1" <?= (($settings['trigger_new_user'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Update Preferences</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="backup" role="tabpanel">
                                <h4 class="section-title">Data Management</h4>
                                <p class="small text-muted mb-4">Export your database or download inventory snapshots.</p>
                                
                                <div class="p-3 bg-light rounded border mb-4 d-flex align-items-center">
                                    <div class="bg-white rounded p-3 me-3 border shadow-sm">
                                        <i class="fa-solid fa-clock-rotate-left text-info fa-2x"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted text-uppercase fw-bold">Last Snapshot</span>
                                        <span class="fw-bold">Dec 4, 2025 - 02:00 AM</span>
                                    </div>
                                </div>

                                <form method="POST" action="../src/php_script/backup_actions.php">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <button type="submit" name="action" value="export_inventory_csv" class="btn btn-outline-success w-100 h-100 py-3">
                                                <i class="fa-solid fa-file-csv fa-2x d-block mb-2"></i>
                                                Export Inventory CSV
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" name="action" value="run_db_backup" class="btn btn-outline-warning w-100 h-100 py-3">
                                                <i class="fa-solid fa-download fa-2x d-block mb-2"></i>
                                                Download SQL Dump
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>