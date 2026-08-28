<?php
if($_SERVER['REQUEST_METHOD']==='POST'&&!empty($_POST['note'])){
    $note=trim($_POST['note']);
    $line=date("Y-m-d H:i:s") . " | " . str_replace(["\r","\n","|"],"",$note) . PHP_EOL;
    file_put_contents('data/data.txt', $line, FILE_APPEND | LOCK_EX);
    echo "Đã lưu.<hr>Toàn bộ nội dung: <br><pre>" . htmlspecialchars(file_get_contents('data/data.txt')) . "</pre>";
}else echo "Vui lòng nhập nội dung.";
?>