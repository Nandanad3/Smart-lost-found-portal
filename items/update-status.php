<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$action = $_POST['action'] ?? '';
$itemId = intval($_POST['item_id'] ?? 0);

if (!$itemId || !$action) {
    header('Location: /campusfind/pages/dashboard.php'); exit;
}

// Verify ownership
$stmt = $pdo->prepare('SELECT * FROM items WHERE id=? AND reported_by=?');
$stmt->execute([$itemId, $_SESSION['user_id']]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('dashboard','Item not found or access denied.','error');
    header('Location: /campusfind/pages/dashboard.php'); exit;
}

switch ($action) {

    // ── Reporter confirms claim ──────────────────────────
    case 'confirm_claim':
        if (!$item['claim_pending']) {
            setFlash('dashboard','No pending claim to confirm.','warn');
            break;
        }
        $stmt = $pdo->prepare('UPDATE items SET status="claimed", claimed_by=?, claim_pending=NULL WHERE id=?');
        $stmt->execute([$item['claim_pending'], $itemId]);
        setFlash('dashboard','Claim confirmed! Contact details have been revealed to the claimant. ✅','success');
        break;

    // ── Reporter dismisses a wrong claim attempt ─────────
    case 'dismiss_claim':
        $stmt = $pdo->prepare('UPDATE items SET claim_pending=NULL WHERE id=?');
        $stmt->execute([$itemId]);
        setFlash('dashboard','Claim dismissed. Item is still active.','warn');
        break;

    // ── Reporter found their own item ────────────────────
    case 'mark_found':
        $stmt = $pdo->prepare('UPDATE items SET status="found", claim_pending=NULL WHERE id=?');
        $stmt->execute([$itemId]);
        setFlash('dashboard','Your item has been marked as found! 🎉','success');
        break;

    // ── Delete item ──────────────────────────────────────
    case 'delete':
        $stmt = $pdo->prepare('DELETE FROM items WHERE id=? AND reported_by=?');
        $stmt->execute([$itemId, $_SESSION['user_id']]);
        setFlash('dashboard','Item deleted successfully.','warn');
        break;

    default:
        setFlash('dashboard','Unknown action.','error');
}

header('Location: /campusfind/pages/dashboard.php'); exit;