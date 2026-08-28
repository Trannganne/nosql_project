<?php
session_status();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
echo "Xin chào, " . htmlspecialchars($_SESSION['user']);
echo "<p><a href='logout.php'>Đăng xuất</a></p>";
