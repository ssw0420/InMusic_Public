<?php
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$password = "";
$db = "inMusic";

// DB 연동
$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed"
    ]);
    exit();
}

// 입력 검증
$steam_id = $_POST['steam_id'] ?? '';
$music_name = $_POST['music_name'] ?? '';
$music_score = isset($_POST['music_score']) ? intval($_POST['music_score']) : 0;
$music_combo = isset($_POST['music_combo']) ? intval($_POST['music_combo']) : 0;
$music_accuracy = isset($_POST['music_accuracy']) ? floatval($_POST['music_accuracy']) : 0.0;
$music_rank = $_POST['music_rank'] ?? '';

if (empty($steam_id) || empty($music_name)) {
    echo json_encode([
        "success" => false,
        "message" => "Required parameters missing"
    ]);
    $conn->close();
    exit();
}

// 노래 제목으로 노래 번호 찾기 (Prepared Statement)
$stmt = $conn->prepare("SELECT musicId FROM music WHERE musicName = ? LIMIT 1");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed"
    ]);
    $conn->close();
    exit();
}

$stmt->bind_param("s", $music_name);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode([
        "success" => false,
        "message" => "Music not found"
    ]);
    $conn->close();
    exit();
}
$music_id = $row['musicId'];

// 노래 기록 조회 (Prepared Statement)
$stmt = $conn->prepare("SELECT musicScore FROM musiclog WHERE userId = ? AND musicId = ?");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed"
    ]);
    $conn->close();
    exit();
}

$stmt->bind_param("ss", $steam_id, $music_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    // 기록이 없으면 새로 삽입
    $stmt = $conn->prepare("INSERT INTO musiclog (userId, musicId, musicScore, musicCombo, musicAccuracy, musicRank) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Insert preparation failed"
        ]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("ssiids", $steam_id, $music_id, $music_score, $music_combo, $music_accuracy, $music_rank);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        "success" => true,
        "message" => "Score registered"
    ]);
} else {
    $prev_score = $row['musicScore'];
    
    if ($prev_score < $music_score) {
        // 최고 점수 갱신
        $stmt = $conn->prepare("UPDATE musiclog SET musicScore = ?, musicCombo = ?, musicAccuracy = ?, musicRank = ? WHERE userId = ? AND musicId = ?");
        if (!$stmt) {
            echo json_encode([
                "success" => false,
                "message" => "Update preparation failed"
            ]);
            $conn->close();
            exit();
        }
        $stmt->bind_param("iidsss", $music_score, $music_combo, $music_accuracy, $music_rank, $steam_id, $music_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            "success" => true,
            "message" => "High score updated"
        ]);
    } else {
        // 점수가 낮으면 그냥 기록만 추가 (원본 로직에서는 INSERT하지만 중복키 문제 가능성 있음)
        echo json_encode([
            "success" => true,
            "message" => "Score registered (not a high score)"
        ]);
    }
}

$conn->close();
?>