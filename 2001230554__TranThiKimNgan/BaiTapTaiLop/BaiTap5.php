<?php
for($i=2;$i<=9;$i++){
    echo"<h3>Bảng $i</h3>";
    for($j=1;$j<=10;$j++){
        echo "$i x $j = " . ($i*$j) ."<br>";
    }
}
?>