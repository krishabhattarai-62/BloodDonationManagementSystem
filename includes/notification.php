<?php
function getUnreadCount($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getNotifications($pdo, $user_id, $limit = 10)
{
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function markAllRead($pdo, $user_id)
{
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$user_id]);
}
?>