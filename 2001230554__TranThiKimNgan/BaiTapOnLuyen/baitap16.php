<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = $_POST['songuyen'];
    $sum = 0;
    if ($n < 0) {
        for ($i = $n; $i <= 1; $i++) {
            $sum += $i;
        }
    } else {
        for ($i = 1; $i <= $n; $i++) {
            $sum += $i;
        }
    }

    echo "tổng các số từ 1 đến $n là: $sum ";
} ?>


<form method="post">
    <p>Vui lòng nhập số nguyên</p>
    <input type="number" name="songuyen">
    <input type="submit" value="Tính">
</form>