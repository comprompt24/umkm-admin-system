<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

header('Location: login.php');
exit();
?>