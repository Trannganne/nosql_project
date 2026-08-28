<?php
if(isset($_FILES['avatar'])){
    $f=$_FILES['avatar'];

    if($f['error']===0){
        $ext=pathinfo($f['name'], PATHINFO_EXTENSION);
        $allowed=['jpg','jpeg','png','gif'];
        if(!in_array(strtolower($ext),$allowed)){
            echo "Chỉ cho phép ảnh.";
            exit;
        }
        $target='uploads/' . time() . "_" . basename($f['name']);
        if(move_uploaded_file($f['tmp_name'],$target)){
            echo "Upload thành công!<br>";
            echo "<img src='$target' style='max-width:300px;'>";
        }else echo "Không thể di chuyển file.";
    }else echo "Lỗi upload: " . $f['error'];
}else echo "Không có file được gửi.";
?>