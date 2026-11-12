<?php
/**
 * Router Utama Sistem Kasir Seventeen Petshop
 */
require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
} else {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
