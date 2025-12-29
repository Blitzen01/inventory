<?php
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // 2. Fetch Active Users
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $count_stmt->fetchColumn();
    $total_pages = ceil($total_users / $limit);

    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    // 3. Fetch Deleted Users
    $stmt_deleted = $pdo->prepare("SELECT * FROM deleted_users ORDER BY deleted_at DESC");
    $stmt_deleted->execute();
    $deleted_users = $stmt_deleted->fetchAll();
?>