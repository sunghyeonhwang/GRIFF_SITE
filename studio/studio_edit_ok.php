<?php
// [1] 세션 시작 및 에러 설정
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = $_SERVER['DOCUMENT_ROOT'];

// [2] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} elseif (file_exists("$root/inc/db_connect.php")) {
    include "$root/inc/db_connect.php";
} else {
    include "../inc/db_connect.php";
}

// ★ [추가] 비밀 설정 파일 로드 (슬랙/알리고 키값)
require_once "$root/inc/secrets.php";

// [3] 요청 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/';</script>";
    exit;
}

$mode = isset($_POST['mode']) ? $_POST['mode'] : 'update';
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($booking_id <= 0) {
    echo "<script>alert('예약 번호가 없습니다.'); history.back();</script>";
    exit;
}

// [4] 기존 예약 정보 조회
$sql_info = "SELECT * FROM studio_bookings WHERE id = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $booking_id);
$stmt_info->execute();
$booking = $stmt_info->get_result()->fetch_assoc();

if (!$booking) { 
    echo "<script>alert('예약 정보를 찾을 수 없습니다.'); history.back();</script>"; 
    exit; 
}

// 권한 체크
if (empty($_SESSION['client_email']) || $booking['client_email'] !== $_SESSION['client_email']) {
     echo "<script>alert('세션이 만료되었습니다. 다시 조회해주세요.'); location.href='/studio/studio_check.php';</script>";
     exit;
}

// =================================================================
// [CASE A] 예약 취소 (Delete)
// =================================================================
if ($mode === 'delete') {
    $sql = "UPDATE studio_bookings SET status = 'canceled' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        // 날짜 포맷팅
        $s_ts = strtotime($booking['start_date']);
        $e_ts = strtotime($booking['end_date']);
        if (date('Y-m-d', $s_ts) === date('Y-m-d', $e_ts)) {
            $date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('H:i', $e_ts);
        } else {
            $date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('m.d H:i', $e_ts);
        }
        
        // 알림 발송
        sendSlackNotification("cancel", $booking['client_name'], $booking['client_company'], $booking_id, $booking['selected_package'], $date_str);
        
        $msg = "[그리프 스튜디오] 예약취소알림\n{$booking['client_name']}님, 요청하신 예약이 정상 취소되었습니다.\n\n· 기존예약: {$date_str}\n\n이용해 주셔서 감사합니다.";
        sendAligoSMS($booking['client_phone'], $booking['client_name'], $msg);

        echo "<script>
            alert('예약이 정상적으로 취소되었습니다.');
            location.href = '/studio/studio_edit_completed.php?id={$booking_id}';
        </script>";
    } else {
        echo "<script>alert('취소 처리 중 오류가 발생했습니다: " . $stmt->error . "'); history.back();</script>";
    }
    exit;
}

// =================================================================
// [CASE B] 예약 수정 (Update)
// =================================================================
if ($mode === 'update') {
    if ($booking['status'] !== 'pending') {
        echo "<script>alert('이미 확정된 예약은 수정할 수 없습니다.'); history.back();</script>";
        exit;
    }

    $selected_package = $_POST['selected_package'];
    $pax            = $_POST['pax'];
    $vehicle_number = $_POST['vehicle_number'];
    $start_date     = $_POST['start_date'];
    $end_date       = $_POST['end_date'];
    
    $raw_equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    $equipment_json = json_encode($raw_equipment, JSON_UNESCAPED_UNICODE);
    $options_str = empty($raw_equipment) ? '선택 없음' : implode(', ', $raw_equipment);

    // ★ [중요] 수정 시 중복 예약 서버단 검증
    $chk_sql = "SELECT count(*) FROM studio_bookings 
                WHERE status IN ('pending', 'confirmed') 
                AND start_date < ? AND end_date > ? 
                AND id != ?"; // 내 예약은 제외
    
    if($chk_stmt = $conn->prepare($chk_sql)) {
        $chk_stmt->bind_param("ssi", $end_date, $start_date, $booking_id);
        $chk_stmt->execute();
        $chk_stmt->bind_result($cnt);
        $chk_stmt->fetch();
        $chk_stmt->close();

        if ($cnt > 0) {
            echo "<script>
                alert('변경하려는 시간에 이미 다른 예약이 존재합니다.\\n시간을 다시 확인해주세요.');
                history.back();
            </script>";
            exit;
        }
    }

    // 업데이트 실행
    $sql = "UPDATE studio_bookings SET 
            selected_package = ?, pax = ?, vehicle_number = ?, start_date = ?, end_date = ?, equipment = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $selected_package, $pax, $vehicle_number, $start_date, $end_date, $equipment_json, $booking_id);

    if ($stmt->execute()) {
        // 날짜 포맷팅
        $s_ts = strtotime($start_date);
        $e_ts = strtotime($end_date);
        if (date('Y-m-d', $s_ts) === date('Y-m-d', $e_ts)) {
            $date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('H:i', $e_ts);
        } else {
            $date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('m.d H:i', $e_ts);
        }
        
        // 알림 발송
        sendSlackNotification("update", $booking['client_name'], $booking['client_company'], $booking_id, $selected_package, $date_str, $options_str);
        
        $msg = "[그리프 스튜디오] 예약변경알림\n{$booking['client_name']}님, 예약 정보가 수정되었습니다.\n\n· 변경일시: {$date_str}\n· 변경패키지: {$selected_package}\n\n확인 부탁드립니다.";
        sendAligoSMS($booking['client_phone'], $booking['client_name'], $msg);

        echo "<script>
            alert('예약 정보가 수정되었습니다.');
            location.href = '/studio/studio_edit_completed.php?id={$booking_id}';
        </script>";
    } else {
        echo "<script>alert('수정 중 오류가 발생했습니다.'); history.back();</script>";
    }
    exit;
}

// -----------------------------------------------------------------
// [함수] 슬랙 알림 (secrets.php 상수 사용)
// -----------------------------------------------------------------
function sendSlackNotification($type, $name, $comp, $id, $pkg, $date, $opts = '-') {
    // ★ secrets.php에 정의된 상수 사용
    $webhook_url = SLACK_WEBHOOK_STUDIO; 

    if ($type === 'update') {
        $color = "#FFD700"; 
        $title = "📝 *스튜디오 예약 정보 수정됨*";
        $desc  = "고객이 예약 정보를 수정했습니다.";
    } else {
        $color = "#FF0000"; 
        $title = "🗑️ *스튜디오 예약 취소됨*";
        $desc  = "고객이 예약을 취소했습니다.";
    }

    $message = [
        "text" => $title,
        "attachments" => [[
            "color" => $color,
            "fields" => [
                ["title" => "상태", "value" => $desc, "short" => false],
                ["title" => "예약번호", "value" => "No.".$id, "short" => true],
                ["title" => "예약자", "value" => "$name ($comp)", "short" => true],
                ["title" => "패키지", "value" => $pkg, "short" => true],
                ["title" => "일시", "value" => $date, "short" => true],
                ["title" => "옵션", "value" => $opts, "short" => false]
            ],
            "footer" => "GRIFF Studio System",
            "ts" => time()
        ]]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

// -----------------------------------------------------------------
// [함수] 알리고 SMS 발송 (secrets.php 상수 사용)
// -----------------------------------------------------------------
function sendAligoSMS($receiver, $destination, $msg) {
    $sms_url = "https://apis.aligo.in/send/"; 
    $receiver = str_replace("-", "", $receiver);

    // ★ secrets.php에 정의된 상수 사용
    $_POST_DATA = [
        'key'      => ALIGO_API_KEY,    
        'userid'   => ALIGO_USER_ID,    
        'sender'   => ALIGO_SENDER,     
        'receiver' => $receiver,
        'msg'      => $msg,
        'msg_type' => 'LMS'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST_DATA);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
}
?>