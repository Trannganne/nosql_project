<form method="post">
    <p>Vui lòng nhập chuỗi</p>
    <input type="text" name="string">
    <input type="submit" value="Chuyển đổi">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $string = $_POST['string'];
    $result = mb_strtoupper($string);
    echo "Chuỗi sau khi chuyển đổi: $result ";
} ?>