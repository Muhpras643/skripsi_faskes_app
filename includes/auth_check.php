<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Mengarah ke login.php di root
    exit();
}

function auth_check($required_role = null) {
    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        if ($_SESSION['user_role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../user/dashboard.php');
        }
        exit();
    }
}
?>