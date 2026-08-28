<?php
if(isset($_GET['r'])&& is_numeric($_GET['r'])){
    $r=(float)$_GET['r'];
    $chuvi=2*pi()*$r;
    $dientich=pi()*$r*$r;
    echo "Bán kính: $r<br>";
    echo "Chu vi: " .round($chuvi, 4) ."<br>";
    echo "Diện tích: " . round($dientich,4);
}else{
    echo "Vui lòng nhập bán kính hợp lệ.";
}
?>