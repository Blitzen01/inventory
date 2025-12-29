<?php
    session_start();
    date_default_timezone_set('Asia/Manila');
    
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
        :root {
            --primary-color: #0d6efd;
            --sidebar-bg: #ffffff;
            --body-bg: #f0f2f5;
        }

        body { 
            background-color: var(--body-bg); 
            padding-top: 90px; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .settings-card { 
            border: none; 
            border-radius: 12px; 
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Sidebar Styling */
        .settings-nav { background-color: var(--sidebar-bg); height: 100%; border-radius: 12px 0 0 12px; }
        .settings-nav .nav-link {
            text-align: left;
            padding: 1rem 1.5rem;
            color: #555;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s ease;
            font-weight: 500;
            border-radius: 0;
        }
        .settings-nav .nav-link i { width: 28px; font-size: 1.1rem; opacity: 0.7; }
        .settings-nav .nav-link:hover { background-color: #f8f9fa; color: var(--primary-color); }
        .settings-nav .nav-link.active {
            color: var(--primary-color);
            background-color: #f0f7ff;
            border-left: 5px solid var(--primary-color);
        }

        /* Forms & Tabs */
        .tab-content { min-height: 500px; }
        .section-title { font-weight: 800; letter-spacing: -0.5px; color: #1a1d20; }
        .form-label { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #6c757d; margin-top: 15px; }
        .form-control:focus, .form-select:focus { border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        /* Dashboard Mini-Cards */
        .info-status-box {
            background: #fff;
            border: 1px solid #e9ecef;
            border-left: 4px solid #0dcaf0;
            border-radius: 8px;
        }

        .btn-save {
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <?php include "../nav/header.php"; ?> 

    <div class="container pb-5">
        <div class="row mb-4 align-items-center">
            <div class="col-md-7">
                <h2 class="section-title mb-1"><i class="fa-solid fa-sliders me-2 text-primary"></i> Control Panel</h2>
                <p class="text-muted mb-0">Manage system-wide logic, security thresholds, and automated rules.</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <?php if (isset($_GET['status'])): ?>
                    <div class="alert <?= ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show d-inline-block shadow-sm mb-0" role="alert">
                        <i class="fa-solid <?= ($_GET['status'] == 'success') ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?> me-2"></i>
                        <?= htmlspecialchars($_GET['message'] ?? ''); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card settings-card">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-lg-3 border-end">
                        <div class="nav flex-column nav-pills settings-nav" id="settingsTabs" role="tablist">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab"><i class="fa-solid fa-window-maximize"></i> General</button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#inventory" type="button" role="tab"><i class="fa-solid fa-boxes-stacked"></i> Inventory Rules</button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#notifications" type="button" role="tab"><i class="fa-solid fa-envelope-open-text"></i> Notifications</button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab"><i class="fa-solid fa-shield-halved"></i> Security</button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#backup" type="button" role="tab"><i class="fa-solid fa-cloud-arrow-down"></i> Backup & Data</button>
                        </div>
                    </div>

                    <div class="col-lg-9 bg-white" style="border-radius: 0 12px 12px 0;">
                        <div class="tab-content p-4 p-md-5" id="settingsTabContent">
                            
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <h4 class="section-title mb-4">Application Identity</h4>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="mb-4">
                                        <label class="form-label">Software Name</label>
                                        <input type="text" class="form-control form-control-lg" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'Stock Focus'); ?>">
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">System Date Format</label>
                                            <select class="form-select" name="date_format">
                                                <option value="MM/DD/YYYY" <?= (($settings['date_format'] ?? '') == 'MM/DD/YYYY') ? 'selected' : ''; ?>>US (12/22/2025)</option>
                                                <option value="DD/MM/YYYY" <?= (($settings['date_format'] ?? '') == 'DD/MM/YYYY') ? 'selected' : ''; ?>>UK (22/12/2025)</option>
                                                <option value="YYYY-MM-DD" <?= (($settings['date_format'] ?? '') == 'YYYY-MM-DD') ? 'selected' : ''; ?>>Standard (2025-12-22)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 pt-md-4 mt-md-3">
                                            <div class="form-check form-switch custom-switch">
                                                <input class="form-check-input" type="checkbox" name="dark_mode_default" value="1" <?= (($settings['dark_mode_default'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label fw-bold">Default Dark Theme</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-save shadow-sm"><i class="fa-solid fa-check me-2"></i> Save Changes</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="inventory" role="tabpanel">
                                <h4 class="section-title mb-4">Stock Logic & Math</h4>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Critical Stock Trigger (%)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="low_stock_threshold_percent" value="<?= htmlspecialchars($settings['low_stock_threshold_percent'] ?? '10'); ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Liquidation Alert (%)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="liquidation_percentage" value="<?= htmlspecialchars($settings['liquidation_percentage'] ?? '50'); ?>">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Primary Unit Type</label>
                                            <input type="text" class="form-control" name="default_unit_of_measure" placeholder="e.g. Pieces, Kilos, Boxes" value="<?= htmlspecialchars($settings['default_unit_of_measure'] ?? 'Pieces'); ?>">
                                        </div>
                                    </div>
                                    <div class="p-3 bg-light rounded mb-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="auto_assign_sku" value="1" <?= (($settings['auto_assign_sku'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-bold text-dark">Enable Smart SKU Auto-Generation</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-save shadow-sm">Save Inventory Rules</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="notifications" role="tabpanel">
                                <h4 class="section-title mb-4">Alert Configurations</h4>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="list-group list-group-flush border rounded overflow-hidden mb-4">
                                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">Stock Threshold Alerts</p>
                                                <small class="text-muted">Broadcast warnings when items hit low stock levels.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="trigger_low_stock" value="1" <?= (($settings['trigger_low_stock'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-0 fw-bold">Account Registration Alerts</p>
                                                <small class="text-muted">Notify administrators when new staff accounts are created.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="trigger_new_user" value="1" <?= (($settings['trigger_new_user'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-save shadow-sm">Update Preferences</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="security" role="tabpanel">
                                <h4 class="section-title mb-4">Access & Audit</h4>
                                <form method="POST" action="../src/php_script/update_settings.php">
                                    <div class="mb-4">
                                        <label class="form-label">Min Password Complexity (Length)</label>
                                        <input type="number" class="form-control" name="password_min_length" value="<?= htmlspecialchars($settings['password_min_length'] ?? '8'); ?>">
                                    </div>
                                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                                        <i class="fa-solid fa-circle-exclamation me-3 fa-lg"></i>
                                        <div>
                                            <strong>Audit Logging is active:</strong> Every change to stock is currently being recorded in the system logs.
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="log_all_actions" value="1" <?= (($settings['log_all_actions'] ?? '0') == '1') ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold">Record Every Admin Interaction</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-save shadow-sm">Update Security</button>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="backup" role="tabpanel">
                                <h4 class="section-title mb-4">Maintenance & Recovery</h4>
                                <div class="info-status-box p-3 mb-4 d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 p-3 rounded me-3 text-info">
                                        <i class="fa-solid fa-clock-rotate-left fa-xl"></i>
                                    </div>
                                    <div>
                                        <span class="d-block small text-muted text-uppercase fw-bold">System Last Recorded Backup</span>
                                        <span class="h5 mb-0 fw-bold text-dark"><?= htmlspecialchars($settings['last_backup_datetime'] ?? 'No Records Found'); ?></span>
                                    </div>
                                </div>
                                <form method="POST" action="../src/php_script/backup_actions.php">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <button type="submit" name="action" value="export_inventory_csv" class="btn btn-outline-dark w-100 py-4 border-2">
                                                <i class="fa-solid fa-file-export fa-2x mb-2 text-success"></i><br>
                                                <strong>Export Inventory CSV</strong>
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" name="action" value="run_db_backup" class="btn btn-outline-dark w-100 py-4 border-2">
                                                <i class="fa-solid fa-database fa-2x mb-2 text-primary"></i><br>
                                                <strong>Download SQL Snapshot</strong>
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