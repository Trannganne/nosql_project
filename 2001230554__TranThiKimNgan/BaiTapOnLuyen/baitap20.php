<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    setcookie('name', $_POST['name'], time() + 3600, '/');
    header('Location: baitap20.php');
    exit;
}

if (isset($_COOKIE['name'])) {
    echo "Chào mừng lại, " . htmlspecialchars($_COOKIE['name']) . "!";
    echo "<p><a href='?del=1'>Xóa cookie</a></p>";
} else {
?>
    <form method="post">
        Nhập tên để ghi nhớ: <input name="name">
        <input type="submit" value="Lưu">
    </form>
<?php
}
if (isset($_GET['del'])) {
    setcookie('name', '', time() - 3600, '/');
    header('Location: baitap20.php');
    exit;
}
?>