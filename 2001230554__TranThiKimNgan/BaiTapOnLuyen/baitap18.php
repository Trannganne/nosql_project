<?php

// Danh sách sinh viên
$students = [
    [
        "name" => "Nguyễn Văn An",
        "score" => 8.5
    ],
    [
        "name" => "Trần Thị Bình",
        "score" => 9.2
    ],
    [
        "name" => "Lê Văn Cường",
        "score" => 7.8
    ],
    [
        "name" => "Phạm Thị Dung",
        "score" => 8.9
    ]
];

// Hàm tìm sinh viên có điểm cao nhất
function findHighestScoreStudent($students)
{
    $highestStudent = $students[0];

    for ($i = 1; $i < count($students); $i++) {
        if ($students[$i]["score"] > $highestStudent["score"]) {
            $highestStudent = $students[$i];
        }
    }

    return $highestStudent;
}

// Hiển thị danh sách sinh viên
echo "<h2>Danh sách sinh viên</h2>";

echo "<table border='1' cellpadding='8'>";
echo "<tr>";
echo "<th>STT</th>";
echo "<th>Họ tên</th>";
echo "<th>Điểm</th>";
echo "</tr>";

for ($i = 0; $i < count($students); $i++) {
    echo "<tr>";
    echo "<td>" . ($i + 1) . "</td>";
    echo "<td>" . htmlspecialchars($students[$i]["name"]) . "</td>";
    echo "<td>" . $students[$i]["score"] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Tìm sinh viên điểm cao nhất
$highestStudent = findHighestScoreStudent($students);

echo "<h2>Sinh viên có điểm cao nhất</h2>";
echo "Họ tên: " . htmlspecialchars($highestStudent["name"]) . "<br>";
echo "Điểm: " . $highestStudent["score"];
