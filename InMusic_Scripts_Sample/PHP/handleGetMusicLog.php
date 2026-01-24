<?php
header("Content-Type: application/json; charset=UTF-8");

$servername   = "localhost";
$dbUsername   = "root";
$dbPassword   = "";
$dbName       = "inmusic";

// 입력 검증
if (!isset($_POST["userId"]) || empty($_POST["userId"])) {
    echo json_encode([
        "success" => false,
        "message" => "userId is required"
    ]);
    exit();
}

$userId = $_POST["userId"];

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed"
    ]);
    exit();
}

// Prepared Statement로 SQL Injection 방지
$stmt = $conn->prepare("SELECT musicId, musicScore, musicCombo, musicAccuracy, musicRank FROM musiclog WHERE userId = ?");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed"
    ]);
    $conn->close();
    exit();
}

$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query error"
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

$stmt->close();
$conn->close();
echo json_encode([
    "success" => true,
    "logs"    => $logs
]);
?>
