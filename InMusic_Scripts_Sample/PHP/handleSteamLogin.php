<?php
// 학습 환경인 XAMPP 로컬 DB 기준으로 개발
header("Content-Type: application/json; charset=UTF-8");

// DB 접속 정보
$servername = "localhost";
$server_username = "root";
$server_password = "";
$dbName = "inmusic";

// 입력 검증
if (!isset($_POST["userId"]) || !isset($_POST["userName"]) || empty($_POST["userId"])) {
    echo json_encode([
        "success" => false,
        "message" => "Required parameters missing"
    ]);
    exit();
}

$userId   = $_POST["userId"];
$userName = $_POST["userName"];

$conn = new mysqli($servername, $server_username, $server_password, $dbName);

// 연결 에러 처리
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB Connection failed"
    ]);
    exit();
}

// 유저 존재 여부 확인 (Prepared Statement)
$stmt = $conn->prepare("SELECT userId FROM user WHERE userId = ?");
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
$stmt->close();

// 이미 있으면 userName만 UPDATE
if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE user SET userName = ? WHERE userId = ?");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Update preparation failed"
        ]);
        $conn->close();
        exit();
    }
    
    $stmt->bind_param("ss", $userName, $userId);
    $updateResult = $stmt->execute();
    $stmt->close();

    if (!$updateResult) {
        $response = [
            "success" => false,
            "message" => "Error updating userName"
        ];
    } else {
        $response = [
            "success" => true,
            "newUser" => false,
            "message" => "UserName updated successfully"
        ];
    }
} else {
    // 없으면 새 유저로 INSERT
    $stmt = $conn->prepare("INSERT INTO user (userId, userName) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Insert preparation failed"
        ]);
        $conn->close();
        exit();
    }
    
    $stmt->bind_param("ss", $userId, $userName);
    $insertResult = $stmt->execute();
    $stmt->close();

    if (!$insertResult) {
        $response = [
            "success" => false,
            "message" => "Error inserting new user"
        ];
    } else {
        $response = [
            "success" => true,
            "newUser" => true,
            "message" => "User inserted successfully"
        ];
    }
}

// DB 연결 종료
$conn->close();

// 결과를 JSON으로 출력
echo json_encode($response);
?>
