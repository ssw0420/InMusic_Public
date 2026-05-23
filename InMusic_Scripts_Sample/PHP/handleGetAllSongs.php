<?php
// 학습 환경인 XAMPP 로컬 DB 기준으로 개발
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username   = "root";
$password   = "";
$dbName     = "inmusic";

$conn = new mysqli($servername, $username, $password, $dbName);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed"
    ]);
    exit();
}

// 모든 곡을 SELECT
$sql = "SELECT musicId, musicName, musicArtist FROM Music";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query error"
    ]);
    $conn->close();
    exit();
}

$songs = [];
while ($row = $result->fetch_assoc()) {
    $songs[] = [
        "musicId"     => $row["musicId"],
        "musicName"   => $row["musicName"],
        "musicArtist" => $row["musicArtist"]
    ];
}

$conn->close();
echo json_encode([
    "success" => true,
    "songs"   => $songs
]);
?>
