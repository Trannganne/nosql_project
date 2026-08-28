<?php
function isPrime($n){
    if($n<2) return false;
    if($n==2) return true;
    if($n%2==0)return false;
    for($i=3;$i<=sqrt($n);$i+=2){
        if($n % $i ==0)return false;
    }
    return true;
}
for($i =1;$i<=100;$i++){
    if(isPrime($i))echo $i . " ";
}
?>