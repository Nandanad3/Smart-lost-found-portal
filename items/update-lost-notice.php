<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$noticeId = intval($_POST['notice_id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($noticeId <= 0 || $action === '') {
    setFlash('notice', 'Invalid notice action.', 'error');
    header('Location: /campusfind/pages/notice-board.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, user_id, status FROM lost_notices WHERE id = ?');
$stmt->execute([$noticeId]);
$notice = $stmt->fetch();

if (!$notice || intval($notice['user_id']) !== intval($_SESSION['user_id'])) {
    setFlash('notice', 'Notice not found or access denied.', 'error');
    header('Location: /campusfind/pages/notice-board.php');
    exit;
}

if ($action === 'mark_resolved') {
    $stmt = $pdo->prepare('UPDATE lost_notices SET status = "resolved" WHERE id = ?');
    $stmt->execute([$noticeId]);
    setFlash('notice', 'Notice marked as found/resolved.', 'success');
} elseif ($action === 'reopen') {
    $stmt = $pdo->prepare('UPDATE lost_notices SET status = "active" WHERE id = ?');
    $stmt->execute([$noticeId]);
    setFlash('notice', 'Notice reopened as active.', 'warn');
} else {
    setFlash('notice', 'Unknown action.', 'error');
}

header('Location: /campusfind/pages/notice-board.php');
exit;
?>
