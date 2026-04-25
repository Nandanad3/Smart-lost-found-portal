<?php
// ── Database Configuration ──────────────────────────────
// Running on XAMPP: http://localhost/campusfind/
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default: no password
define('DB_NAME', 'campusfind');

$pdo = null;
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Ensure public lost notice board table exists
$pdo->exec('
    CREATE TABLE IF NOT EXISTS lost_notices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(140) NOT NULL,
        category VARCHAR(80) NOT NULL,
        description TEXT NULL,
        lost_location VARCHAR(190) NOT NULL,
        lost_date DATE NULL,
        contact_phone VARCHAR(30) NOT NULL,
        contact_email VARCHAR(190) NULL,
        reward_note VARCHAR(190) NULL,
        status ENUM("active","resolved") NOT NULL DEFAULT "active",
        views_count INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_lost_notices_status_created (status, created_at),
        INDEX idx_lost_notices_category (category),
        CONSTRAINT fk_lost_notices_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
');

// Ensure admins table exists for admin portal login
$pdo->exec('
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(80) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
');

// Seed one default admin account only when empty
$adminCount = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount === 0) {
    $seedStmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
    $seedStmt->execute(['admin', password_hash('admin123', PASSWORD_BCRYPT)]);
}

// ── Session ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helper: is logged in? ────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ── Helper: require login ────────────────────────────────
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /campusfind/auth/login.php');
        exit;
    }
}

// ── Helper: is admin? ────────────────────────────────────
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /campusfind/admin/login.php');
        exit;
    }
}

// Flash message helpers (available in all pages that include db.php)
if (!function_exists('setFlash')) {
    function setFlash($key, $msg, $type = 'success') {
        $_SESSION['flash'][$key] = ['msg' => $msg, 'type' => $type];
    }
}

if (!function_exists('flash')) {
    function flash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $msg  = $_SESSION['flash'][$key]['msg'];
            $type = $_SESSION['flash'][$key]['type'];
            unset($_SESSION['flash'][$key]);
            $icon = $type === 'success' ? '✅' : ($type === 'error' ? '❌' : ($type === 'warn' ? '⚠️' : 'ℹ️'));
            echo '<div class="flash alert alert-' . htmlspecialchars($type) . '">' . $icon . ' ' . htmlspecialchars($msg) . '</div>';
        }
    }
}

// ── Category list ────────────────────────────────────────
function getCategories() {
    return [
        'Electronics',
        'Stationery',
        'Clothing',
        'Accessories',
        'ID / Documents',
        'Books',
        'Keys',
        'Bags',
        'Sports Equipment',
        'Wallet / Purse',
        'Water Bottle',
        'Other'
    ];
}

function getCategoryEmoji($cat) {
    $map = [
        'Electronics'     => '📱',
        'Stationery'      => '✏️',
        'Clothing'        => '👕',
        'Accessories'     => '💍',
        'ID / Documents'  => '🪪',
        'Books'           => '📚',
        'Keys'            => '🔑',
        'Bags'            => '👜',
        'Sports Equipment'=> '⚽',
        'Wallet / Purse'  => '👛',
        'Water Bottle'    => '🍶',
        'Other'           => '📦',
    ];
    return $map[$cat] ?? '📦';
}
?>