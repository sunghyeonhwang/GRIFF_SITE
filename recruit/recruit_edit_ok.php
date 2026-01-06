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
    die("DB 연결 실패");
}

$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

if ($id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
    exit;
}

// [공통] 알림을 위해 기존 정보(이름, 공고제목) 조회
$info_sql = "SELECT a.name, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE a.id = ?";
$info_stmt = $conn->prepare($info_sql);
$info_stmt->bind_param("i", $id);
$info_stmt->execute();
$info_res = $info_stmt->get_result();
$info_row = $info_res->fetch_assoc();
$applicant_name = $info_row['name'] ?? 'Unknown';
$job_title_noti = $info_row['job_title'] ?? '상시 채용 / 인재풀';


// ==========================================
// A. 지원 취소 (삭제)
// ==========================================
if ($mode === 'delete') {
    // 파일 삭제 (기존 코드 유지)
    $stmt = $conn->prepare("SELECT profile_image, resume_path, portfolio_path FROM applicants WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if (!empty($row['profile_image']) && file_exists($root . $row['profile_image'])) unlink($root . $row['profile_image']);
        if (!empty($row['resume_path']) && file_exists($root . $row['resume_path'])) unlink($root . $row['resume_path']);
        if (!empty($row['portfolio_path']) && file_exists($root . $row['portfolio_path'])) unlink($root . $row['portfolio_path']);
    }

    // DB 삭제
    $del_stmt = $conn->prepare("DELETE FROM applicants WHERE id = ?");
    $del_stmt->bind_param("i", $id);
    
    if ($del_stmt->execute()) {
        // ★ SLACK 알림 (취소) ★
        sendSlackEditNotification("delete", $applicant_name, $job_title_noti);

        echo "<script>
            alert('지원이 정상적으로 취소되었습니다.');
            location.href = '/recruit/recruit_list.php';
        </script>";
    } else {
        echo "<script>alert('삭제 처리 중 오류가 발생했습니다.'); history.back();</script>";
    }
    exit;
}

// ==========================================
// B. 지원 수정 (업데이트)
// ==========================================
if ($mode === 'update') {
    $name = trim($_POST['name']);
    $cover_letter = trim($_POST['cover_letter']);
    $motivation = trim($_POST['motivation']);

    // 파일 업로드 (기존 코드 유지)
    $upload_dir = "$root/uploads/recruit/";
    $web_path   = "/uploads/recruit/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    function handleUpload($inputName, $prefix, $targetDir, $webDir) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
            $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
            $newFileName = date('YmdHis') . '_' . uniqid() . '_' . $prefix . '.' . $ext;
            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetDir . $newFileName)) {
                return $webDir . $newFileName;
            }
        }
        return null; 
    }

    $new_photo = handleUpload('photo_file', 'photo', $upload_dir, $web_path);
    $new_resume = handleUpload('resume_file', 'resume', $upload_dir, $web_path);
    $new_port = handleUpload('portfolio_file', 'pf', $upload_dir, $web_path);

    // 쿼리 생성
    $query = "UPDATE applicants SET name = ?, cover_letter = ?, motivation = ?";
    $types = "sss";
    $params = [$name, $cover_letter, $motivation];

    if ($new_photo) { $query .= ", profile_image = ?"; $types .= "s"; $params[] = $new_photo; }
    if ($new_resume) { $query .= ", resume_path = ?"; $types .= "s"; $params[] = $new_resume; }
    if ($new_port) { $query .= ", portfolio_path = ?"; $types .= "s"; $params[] = $new_port; }

    $query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // ★ SLACK 알림 (수정) ★
        sendSlackEditNotification("update", $name, $job_title_noti);

        echo "<script>
            alert('지원서가 성공적으로 수정되었습니다.');
            location.href = '/recruit/recruit_list.php'; 
        </script>";
    } else {
        echo "<script>alert('수정 중 오류가 발생했습니다.'); history.back();</script>";
    }
    exit;
}

// --- 슬랙 알림 함수 (수정/삭제용) ---
function sendSlackEditNotification($type, $name, $job_title) {
    $webhook_url = "https://hooks.slack.com/services/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";
    
    if ($type === 'update') {
        $color = "#FFD700"; // 노란색 (수정)
        $title = "📝 *지원서가 수정되었습니다.*";
    } else {
        $color = "#FF0000"; // 빨간색 (삭제)
        $title = "🗑️ *지원이 취소되었습니다.*";
    }

    $message = [
        "text" => $title,
        "attachments" => [
            [
                "color" => $color,
                "fields" => [
                    ["title" => "지원자", "value" => $name, "short" => true],
                    ["title" => "관련 공고", "value" => $job_title, "short" => true]
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