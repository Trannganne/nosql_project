<?php
$arr=[5,12,3,9,-2,20];
function minMax($a){
    return ['min'=>min($a), 'max'=>max($a)];
}
$mm=minMax($arr);
echo "Min: {$mm['min']}, Max: {$mm['max']}";
?>