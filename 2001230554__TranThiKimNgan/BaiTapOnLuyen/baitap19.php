<?php

// Kiểm tra xem người dùng có gửi form hay chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Lấy nội dung người dùng nhập
    $content = trim($_POST['content']);

    // Kiểm tra nội dung có rỗng không
    if (!empty($content)) {

        // Ghi nội dung vào file note.txt
        // FILE_APPEND: ghi thêm, không xóa nội dung cũ
        // PHP_EOL: xuống dòng
        file_put_contents(
            'note.txt',
            $content . PHP_EOL,
            FILE_APPEND
        );

        $message = "Đã lưu nội dung thành công!";
    } else {
        $message = "Vui lòng nhập nội dung!";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý ghi chú</title>
</head>

<body>

    <h2>Nhập nội dung</h2>

    <form method="post">

        <textarea
            name="content"
            rows="5"
            cols="50"
            placeholder="Nhập nội dung..."></textarea>

        <br><br>

        <input type="submit" value="Lưu">

    </form>

    <?php

    // Hiển thị thông báo
    if (isset($message)) {
        echo "<p>$message</p>";
    }

    ?>

    <h2>Toàn bộ nội dung đã lưu</h2>

    <?php

    // Kiểm tra file note.txt có tồn tại không
    if (file_exists('note.txt')) {

        // Đọc toàn bộ nội dung file
        $content = file_get_contents('note.txt');

        // Hiển thị nội dung
        echo "<pre>";
        echo htmlspecialchars($content);
        echo "</pre>";
    } else {

        echo "Chưa có nội dung nào.";
    }

    ?>

</body>

</html>