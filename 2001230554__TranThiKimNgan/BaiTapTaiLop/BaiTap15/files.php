<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
echo "<h3>Danh sách file đã upload</h3>";
if (file_exists('data/uploads.txt')) {
    $lines = file('data/uploads.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<ul>";
    foreach ($lines as $ln) {
        list($user, $fname, $time) = explode("|", $ln);
        echo "<li>$time — $user — <a href='uploads/$fname'
target='_blank'>$fname</a></li>";
    }
    echo "</ul>";
} else echo "Chưa có file nào.";
