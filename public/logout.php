<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Clear session
$_SESSION = [];
session_destroy();

// Redirect to home
header('Location: /LibraireWeb/public/index.php');
exit;
