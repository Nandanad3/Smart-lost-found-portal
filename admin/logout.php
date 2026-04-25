<?php
require_once __DIR__ . '/../config/db.php';
session_destroy();
header('Location: /campusfind/admin/login.php');
exit;