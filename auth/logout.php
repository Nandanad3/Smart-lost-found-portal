<?php
require_once __DIR__ . '/../config/db.php';
session_destroy();
header('Location: /campusfind/auth/login.php');
exit;