<?php
if(isset($_POST['score'])){
    $s=(float)$_POST['score'];
    if($s>=8)$rank="Giỏi";
    elseif ($s>=6.5)$rank="Khá";
    elseif ($s>=5) $rank="Trung bình";
    else $rank="Yếu";
    echo "Điểm: $s - Xếp loại: $rank";

}else echo "Chưa gửi điểm.";
?>