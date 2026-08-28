<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f = $_FILES['file'];
    if ($f['error'] === 0) {
        $target = 'uploads/' . time() . "_" . basename($f['name']);
        if (move_uploaded_file($f['tmp_name'], $target)) {
            $line = $_SESSION['user'] . "|" . basename($target) . "|" . date("Y-m-d H:i:s") . PHP_EOL;
            file_put_contents('data/uploads.txt', $line, FILE_APPEND | LOCK_EX);
            $msg = "Upload thành công.";
        } else $msg = "Lỗi lưu file.";
    } else $msg = "Lỗi upload.";
}
?>

<p>Xin chào <?= htmlspecialchars(
                $$_SESSION['user']
            ) ?> | <a href="files.php">Xem files</a> | <a href="logout.php">Đăng xuất</a></p>
<?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
<form method="post" enctype="multipart/form-data">Chọn file: <input type="file" name="file"><br>
    <input type="submit" value="Upload">
</form>