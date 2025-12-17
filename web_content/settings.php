<?php
    // Include the necessary files
    include "../src/cdn/cdn_links.php";
    include "../render/connection.php";
    include "../render/modal.php";

    // --- NEW: 1. Fetch System Settings from Database ---
    $settings = [];
    if (isset($conn)) {
        // This query fetches all rows from a configuration table (key-value pair model)
        $sql_settings = "SELECT setting_key, setting_value FROM system_settings";
        $result_settings = $conn->query($sql_settings);
        
        if ($result_settings && $result_settings->num_rows > 0) {
            while ($row = $result_settings->fetch_assoc()) {
                // Populate the $settings array: $settings['app_name'] = 'Inventory Manager Pro'
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    // --- END NEW PHP BLOCK ---

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>System Settings</title>
        <style>
            /* Fix for fixed-top Navbar */
            body {
                padding-top: 56px; 
            }
            .settings-nav .nav-link {
                text-align: left;
                padding: 1rem 1.5rem;
            }
            .settings-nav .nav-link.active {
                border-left: 3px solid var(--bs-primary);
                background-color: var(--bs-light);
            }
        </style>
    </head>

    <body class="bg-light <?php echo $body_class; ?>">

        <?php include "../nav/header.php"; ?> 

        <div class="container-fluid mt-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-light text-body"><i class="fa-solid fa-gears me-2"></i> System Settings</h1> 
                
                <?php 
                    // Display Status Message (from update_settings.php redirect)
                    if (isset($_GET['status']) && isset($_GET['message'])) {
                        $alert_class = ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger';
                        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert" style="width: 400px;">';
                        echo htmlspecialchars($_GET['message']);
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    }
                ?>
            </div>

            <div class="card shadow mb-5">
                <div class="card-body p-0">
                    <div class="row g-0">
                        
                        <div class="col-lg-3 border-end">
                            <div class="list-group list-group-flush settings-nav" id="settingsTabs" role="tablist">
                                <a class="list-group-item list-group-item-action active" id="general-tab" data-bs-toggle="list" href="#general" role="tab" aria-controls="general" aria-selected="true">
                                    <i class="fa-solid fa-sliders me-3"></i> <b>General</b> Settings
                                </a>
                                <a class="list-group-item list-group-item-action" id="inventory-tab" data-bs-toggle="list" href="#inventory" role="tab" aria-controls="inventory">
                                    <i class="fa-solid fa-box-open me-3"></i> <b>Inventory</b> Rules
                                </a>
                                <a class="list-group-item list-group-item-action" id="notifications-tab" data-bs-toggle="list" href="#notifications" role="tab" aria-controls="notifications">
                                    <i class="fa-solid fa-bell me-3"></i> <b>Notifications</b> & Alerts
                                </a>
                                <a class="list-group-item list-group-item-action" id="backup-tab" data-bs-toggle="list" href="#backup" role="tab" aria-controls="backup">
                                    <i class="fa-solid fa-database me-3"></i> <b>Backup</b> & Data
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-9 p-4">
                            <div class="tab-content" id="nav-tabContent">
                                
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <h3>General System Configuration</h3>
                                    <p class="text-muted">Configure basic application appearance and behavior.</p>
                                    <hr>
                                    <form method="POST" action="../src/php_script/update_settings.php">
                                        <div class="mb-3">
                                            <label for="appName" class="form-label">Application Name</label>
                                            <input type="text" class="form-control" id="appName" name="app_name" value="<?php echo htmlspecialchars($settings['app_name'] ?? 'Inventory Manager Pro'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="dateFormat" class="form-label">Default Date Format</label>
                                            <select class="form-select" id="dateFormat" name="date_format">
                                                <option value="YYYY-MM-DD (2025-12-05)" <?php if (($settings['date_format'] ?? '') == 'YYYY-MM-DD (2025-12-05)') echo 'selected'; ?>>YYYY-MM-DD (2025-12-05)</option>
                                                <option value="MM/DD/YYYY (12/05/2025)" <?php if (($settings['date_format'] ?? '') == 'MM/DD/YYYY (12/05/2025)') echo 'selected'; ?>>MM/DD/YYYY (12/05/2025)</option>
                                                <option value="DD/MM/YYYY (05/12/2025)" <?php if (($settings['date_format'] ?? '') == 'DD/MM/YYYY (05/12/2025)') echo 'selected'; ?>>DD/MM/YYYY (05/12/2025)</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-save me-2"></i> Save General Settings</button>
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
                                    <h3>Inventory Management Rules</h3>
                                    <p class="text-muted">Set global rules for stock thresholds, liquidation percentage, and EOL duration.</p>
                                    <hr>
                                    <form method="POST" action="../src/php_script/update_settings.php">
                                        <div class="mb-3">
                                            <label for="lowStockThreshold" class="form-label">Global Low Stock Percentage (%)</label>
                                            <input type="number" class="form-control" id="lowStockThreshold" name="low_stock_threshold_percent" 
                                                value="<?php echo htmlspecialchars($settings['low_stock_threshold_percent'] ?? '15'); ?>" min="1" max="100" required>
                                            <small class="form-text text-muted">A warning status will trigger if stock drops below this percentage of the maximum capacity.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="liquidationPercent" class="form-label">Global Liquidation Percentage (%)</label>
                                            <input type="number" class="form-control" id="liquidationPercent" name="liquidation_percentage" 
                                                value="<?php echo htmlspecialchars($settings['liquidation_percentage'] ?? '20'); ?>" min="0" max="100" required>
                                            <small class="form-text text-muted">The percentage of stock to be liquidated for slow-moving items.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="eolDuration" class="form-label">Default EOL Duration (Years)</label>
                                            <input type="number" class="form-control" id="eolDuration" name="eol_duration_years" 
                                                value="<?php echo htmlspecialchars($settings['eol_duration_years'] ?? '3'); ?>" min="1" max="50" required>
                                            <small class="form-text text-muted">The default number of years after which an item is considered End-of-Life.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="defaultUnit" class="form-label">Default Unit of Measure</label>
                                            <select class="form-select" id="defaultUnit" name="default_unit_of_measure">
                                                <option value="Pieces" <?php if (($settings['default_unit_of_measure'] ?? '') == 'Pieces') echo 'selected'; ?>>Pieces</option>
                                                <option value="Units" <?php if (($settings['default_unit_of_measure'] ?? '') == 'Units') echo 'selected'; ?>>Units</option>
                                                <option value="Meters" <?php if (($settings['default_unit_of_measure'] ?? '') == 'Meters') echo 'selected'; ?>>Meters</option>
                                                <option value="Kilograms" <?php if (($settings['default_unit_of_measure'] ?? '') == 'Kilograms') echo 'selected'; ?>>Kilograms</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="autoAssignSKU" name="auto_assign_sku" value="1" 
                                                <?php if (($settings['auto_assign_sku'] ?? '0') == '1') echo 'checked'; ?>>
                                            <label class="form-check-label" for="autoAssignSKU">Auto-Assign SKU/ID on new product creation</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-save me-2"></i> Save Inventory Rules</button>
                                    </form>
                                </div>


                                <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                                    <h3>Notification Preferences</h3>
                                    <p class="text-muted">Manage system alerts and communication channels.</p>
                                    <hr>
                                    <form method="POST" action="../src/php_script/update_settings.php">
                                        <div class="mb-3">
                                            <label class="form-label">Trigger Events</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="triggerLowStock" name="trigger_low_stock" value="1" <?php if (($settings['trigger_low_stock'] ?? '0') == '1') echo 'checked'; ?>>
                                                <label class="form-check-label" for="triggerLowStock">
                                                    <i class="fa-solid fa-exclamation-triangle me-1 text-warning"></i> Low Stock Warning
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="triggerNewUser" name="trigger_new_user" value="1" <?php if (($settings['trigger_new_user'] ?? '0') == '1') echo 'checked'; ?>>
                                                <label class="form-check-label" for="triggerNewUser">
                                                    <i class="fa-solid fa-user-plus me-1 text-primary"></i> New User Account Created
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-save me-2"></i> Save Notification Settings</button>
                                    </form>
                                </div>
                                
                                <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                                    <h3>Data Management and Backup</h3>
                                    <p class="text-muted">Tools for exporting data and managing backups.</p>
                                    <hr>
                                    <div class="alert alert-info">
                                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Last automatic backup: **December 4, 2025, 2:00 AM**.
                                    </div>
                                    
                                    <form method="POST" action="../src/php_script/backup_actions.php" class="d-grid gap-3">
                                        <button type="submit" name="action" value="export_inventory_csv" class="btn btn-success btn-lg">
                                            <i class="fa-solid fa-file-export me-2"></i> Export All Inventory Data (.csv)
                                        </button>
                                        
                                        <button type="submit" name="action" value="run_db_backup" class="btn btn-warning btn-lg">
                                            <i class="fa-solid fa-database me-2"></i> Run Manual Database Backup Now (SQL Dump)
                                        </button>
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