<?php

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$user_id) {
    header('Location: index.php');
    exit();
}

?>