<?php
    session_start();
    require_once '../render/connection.php';
    include "../src/cdn/cdn_links.php";
    include "../src/fetch/system_logs.php";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Settings & User Audit | M-Ventory</title>
    <style>
        body { background-color: #f4f7f6; padding-top: 70px; }
        .card { border: none; border-radius: 10px; }
        .table thead th { 
            background-color: #f8f9fa; 
            text-transform: uppercase; 
            font-size: 11px; 
            letter-spacing: 1px; 
            color: #555;
            border-top: none;
        }
        .status-pill { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 50px; }
        .user-tag { background: #eee; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .change-box { font-family: monospace; font-size: 11px; padding: 5px; border-radius: 5px; display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        .pagination .page-link { color: #333; border: none; margin: 0 3px; border-radius: 5px; }
        .pagination .active .page-link { background-color: #212529; color: white; }
    </style>
</head>
<body>

<?php include "../nav/header.php"; ?> 

<div class="container-fluid px-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark m-0"><i class="fa-solid fa-gears me-2"></i>System Audit Trail</h3>
            <p class="text-muted small">Tracking settings changes and user management actions</p>
        </div>
        <div class="col-md-6 text-md-end d-flex justify-content-end align-items-center gap-3">
             <form method="GET" class="d-flex align-items-center gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
                <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                    <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Timestamp</th>
                            <th>Entity</th>
                            <th>Target Record</th>
                            <th>Action</th>
                            <th>Previous Value</th>
                            <th>New Value</th>
                            <th>Modified By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_audit_log->fetch_assoc()): 
                            $dt = strtotime($row['full_datetime']);
                            
                            // Style action types
                            $action = $row['action_type'];
                            $action_bg = match($action) {
                                'INSERT' => 'bg-success text-white',
                                'UPDATE' => 'bg-warning text-dark',
                                'DELETE' => 'bg-danger text-white',
                                'RESTORED' => 'bg-info text-white',
                                default  => 'bg-secondary text-white'
                            };

                            // Icon for Entity
                            $entity_icon = ($row['table_name'] == 'settings') ? 'fa-sliders' : 'fa-user-gear';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= date('M d, Y', $dt) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('h:i:s A', $dt) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border shadow-sm" style="font-size: 10px;">
                                    <i class="fa-solid <?= $entity_icon ?> me-1"></i> <?= strtoupper($row['table_name']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= $row['record_id'] ?></div>
                            </td>
                            <td>
                                <span class="status-pill <?= $action_bg ?>"><?= $action ?></span>
                            </td>
                            <td>
                                <code class="change-box bg-light text-muted border"><?= htmlspecialchars($row['old_value'] ?? 'N/A') ?></code>
                            </td>
                            <td>
                                <code class="change-box bg-dark text-white shadow-sm"><?= htmlspecialchars($row['new_value'] ?? 'N/A') ?></code>
                            </td>
                            <td>
                                <span class="user-tag">
                                    <i class="fa-solid fa-user-shield me-1 text-primary"></i>
                                    <?= htmlspecialchars($row['first_name'] ?: "System") ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>