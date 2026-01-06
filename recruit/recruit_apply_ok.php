<?php
// [1] 에러 리포팅
error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = $_SERVER['DOCUMENT_ROOT'];

// [2] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} elseif (file_exists("$root/inc/db_connect.php")) {
    include "$root/inc/db_connect.php";
} else {
    die("DB 연결 파일을 찾을 수 없습니다.");
}

if (!isset($conn) || $conn->connect_error) {
    die("DB 연결 실패: " . ($conn->connect_error ?? '객체 없음'));
}

// [3] 데이터 수신
$raw_recruit_id = isset($_POST['recruit_id']) ? (int)$_POST['recruit_id'] : 0;
$name           = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone          = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$cover_letter   = isset($_POST['cover_letter']) ? trim($_POST['cover_letter']) : ''; 
$motivation     = isset($_POST['motivation']) ? trim($_POST['motivation']) : '';

if(empty($name) || empty($phone) || empty($email)) {
    echo "<script>alert('필수 정보가 누락되었습니다.'); history.back();</script>";
    exit;
}

// [4] recruit_id 검증 & 공고 제목 가져오기 (알림용)
$db_recruit_id = null;
$job_title_noti = "상시 채용 / 인재풀"; // 알림용 기본값

if ($raw_recruit_id > 0) {
    $check_sql = "SELECT id, title FROM recruits WHERE id = ?";
    if ($stmt_check = $conn->prepare($check_sql)) {
        $stmt_check->bind_param("i", $raw_recruit_id);
        $stmt_check->execute();
        $res = $stmt_check->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $db_recruit_id = $raw_recruit_id;
            $job_title_noti = $row['title']; // 공고 제목 저장
        } else {
            $db_recruit_id = null; 
        }
        $stmt_check->close();
    }
}

// [5] 파일 업로드 (기존 코드 유지)
$upload_dir = "$root/uploads/recruit/"; 
$web_path   = "/uploads/recruit/";

if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0777, true)) die("Error: 업로드 폴더 생성 실패");
}

function uploadFile($fileInputName, $targetDir, $webDir, $prefix) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $fileName = $_FILES[$fileInputName]['name'];
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = date('YmdHis') . '_' . uniqid() . '_' . $prefix . '.' . $ext;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetDir . $newFileName)) {
            return $webDir . $newFileName;
        }
    }
    return null;
}

$profile_image  = uploadFile('photo_file', $upload_dir, $web_path, 'photo'); 
$resume_path    = uploadFile('resume_file', $upload_dir, $web_path, 'resume');
$portfolio_path = uploadFile('portfolio_file', $upload_dir, $web_path, 'pf');


// [6] DB INSERT
$sql = "INSERT INTO applicants 
        (recruit_id, name, phone, email, profile_image, resume_path, portfolio_path, cover_letter, motivation, status, applied_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issssssss", $db_recruit_id, $name, $phone, $email, $profile_image, $resume_path, $portfolio_path, $cover_letter, $motivation);

    if ($stmt->execute()) {
        
        // ★ SLACK 알림 전송 ★
        sendSlackNotification($name, $job_title_noti, $email, $phone);

        echo "<script>
            alert('지원서가 성공적으로 접수되었습니다. 감사합니다.');
            location.href = '/recruit_list.php'; 
        </script>";
    } else {
        echo "<script>
            alert('지원 접수 중 오류가 발생했습니다.\\n(Error: DB_INSERT_FAIL)');
            history.back();
        </script>";
    }
    $stmt->close();
} else {
    die("SQL 준비 에러: " . $conn->error);
}

$conn->close();

// --- 슬랙 알림 함수 ---
function sendSlackNotification($name, $job_title, $email, $phone) {
    $webhook_url = "https://hooks.slack.com/services/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";
    
    $message = [
        "text" => "🔔 *새로운 지원자가 있습니다!*",
        "attachments" => [
            [
                "color" => "#36a64f", // 초록색 라인
                "fields" => [
                    ["title" => "지원 공고", "value" => $job_title, "short" => false],
                    ["title" => "이름", "value" => $name, "short" => true],
                    ["title" => "연락처", "value" => $phone, "short" => true],
                    ["title" => "이메일", "value" => $email, "short" => false]
                ],
                "footer" => "GRIFF Recruit System",
                "ts" => time()
            ]
        ]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
?>