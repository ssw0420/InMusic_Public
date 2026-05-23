<?php
// 학습 환경인 XAMPP 로컬 DB 기준으로 개발
header("Content-Type: application/json; charset=UTF-8");

// DB 접속 정보
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName     = "inmusic";

// 입력 검증
if (!isset($_POST["musicId"]) || !isset($_POST["musicName"]) || !isset($_POST["musicArtist"]) ||
    empty($_POST["musicId"]) || empty($_POST["musicName"])) {
    echo json_encode([
        "success" => false,
        "message" => "Required parameters missing"
    ]);
    exit();
}

$musicId      = $_POST["musicId"];
$musicName    = $_POST["musicName"];
$musicArtist  = $_POST["musicArtist"];

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB Connection failed"
    ]);
    exit();
}

// Prepared Statement로 ON DUPLICATE KEY UPDATE 구문 사용
$stmt = $conn->prepare(
    "INSERT INTO Music (musicId, musicName, musicArtist) VALUES (?, ?, ?) 
     ON DUPLICATE KEY UPDATE musicName = ?, musicArtist = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed"
    ]);
    $conn->close();
    exit();
}

$stmt->bind_param("sssss", $musicId, $musicName, $musicArtist, $musicName, $musicArtist);
$result = $stmt->execute();
$stmt->close();

if ($result === TRUE) {
    $response = [
        "success" => true,
        "message" => "Music upsert success"
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Query execution failed"
    ];
}

$conn->close();
echo json_encode($response);
?>
