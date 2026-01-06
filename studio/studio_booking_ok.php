<?php
// [1] 에러 리포팅
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [2] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} elseif (file_exists("$root/inc/db_connect.php")) {
    include "$root/inc/db_connect.php";
} else {
    include "../inc/db_connect.php"; 
}
require_once "$root/inc/secrets.php"; // ★ secrets.php 로드

// [3] 데이터 수신 및 처리
$is_success = false;
$booking_id = 0;
$error_msg = "";

$display_options = []; 
$client_name = ""; $client_phone = ""; $client_email = ""; $selected_package = ""; $start_date = ""; $end_date = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name    = $_POST['client_name'];
    $client_phone   = $_POST['client_phone'];
    $client_email   = $_POST['client_email'];
    $client_company = $_POST['client_company'] ?? '-';
    $selected_package = $_POST['selected_package'] ?? '';
    $service_type   = $_POST['service_type'];
    $pax            = $_POST['pax'];
    $vehicle_number = $_POST['vehicle_number'] ?? '없음';
    $start_date     = $_POST['start_date'];
    $end_date       = $_POST['end_date'];
    
    // 옵션 배열 처리
    $raw_equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    $display_options = $raw_equipment; 
    
    // DB 저장용 JSON 변환
    $equipment_json = json_encode($raw_equipment, JSON_UNESCAPED_UNICODE);
    
    // 알림 발송용 옵션 문자열
    $options_str = empty($raw_equipment) ? '선택 없음' : implode(', ', $raw_equipment);

    // [중요] 중복 예약 서버단 검증 (더블 체크)
    $chk_sql = "SELECT count(*) FROM studio_bookings 
                WHERE status IN ('pending', 'confirmed') 
                AND start_date < ? AND end_date > ?";
    
    if($chk_stmt = $conn->prepare($chk_sql)) {
        $chk_stmt->bind_param("ss", $end_date, $start_date);
        $chk_stmt->execute();
        $chk_stmt->bind_result($cnt);
        $chk_stmt->fetch();
        $chk_stmt->close();

        if ($cnt > 0) {
            echo "<script>
                alert('죄송합니다. 선택하신 기간에 이미 다른 예약이 존재합니다.\\n날짜를 다시 확인해주세요.');
                history.back();
            </script>";
            exit;
        }
    }

    // 날짜 포맷 정리
    $s_ts = strtotime($start_date);
    $e_ts = strtotime($end_date);
    if (date('Y-m-d', $s_ts) === date('Y-m-d', $e_ts)) {
        $booking_date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('H:i', $e_ts);
    } else {
        $booking_date_str = date('Y.m.d H:i', $s_ts) . " ~ " . date('m.d H:i', $e_ts);
    }

    // DB Insert
    $sql = "INSERT INTO studio_bookings 
            (client_name, client_phone, client_email, client_company, selected_package, service_type, pax, vehicle_number, start_date, end_date, equipment, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssssssss", $client_name, $client_phone, $client_email, $client_company, $selected_package, $service_type, $pax, $vehicle_number, $start_date, $end_date, $equipment_json);
        
        if ($stmt->execute()) {
            $is_success = true;
            $booking_id = $conn->insert_id;

            // 1. 슬랙 알림 (관리자용)
            $booking_info = [
                'no' => $booking_id,
                'name' => $client_name,
                'phone' => $client_phone,
                'email' => $client_email,
                'package' => $selected_package,
                'options' => $options_str, 
                'date' => $booking_date_str,
                'company' => $client_company
            ];
            sendNotificationToSlack($booking_info);

            // 2. 알리고 문자 발송 (고객용 - 예약 접수 알림)
            $msg = "[그리프 스튜디오] 예약신청안내\n{$client_name}님, 예약 신청이 정상 접수되었습니다.\n\n📅 일시: {$booking_date_str}\n📦 패키지: {$selected_package}\n\n담당자가 스케줄 확인 후 확정 연락을 드리겠습니다.";
            sendAligoSMS($client_phone, $client_name, $msg);

        } else {
            $error_msg = "DB 저장 중 오류: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "쿼리 준비 실패: " . $conn->error;
    }
} else {
    echo "<script>location.href='/studio/studio_booking.php';</script>";
    exit;
}

// =================================================================
// [함수 1] 슬랙 알림 (secrets.php 상수 사용)
// =================================================================
function sendNotificationToSlack($info) {
    $webhook_url = SLACK_WEBHOOK_STUDIO; // secrets.php 상수
    $message = [
        "text" => "📣 *새로운 스튜디오 예약 신청*",
        "attachments" => [[
            "color" => "#FFD400",
            "fields" => [
                ["title" => "예약번호", "value" => "No." . $info['no'], "short" => true],
                ["title" => "신청자", "value" => $info['name'] . " (" . $info['company'] . ")", "short" => true],
                ["title" => "연락처", "value" => $info['phone'], "short" => true],
                ["title" => "이메일", "value" => $info['email'], "short" => true],
                ["title" => "패키지", "value" => $info['package'], "short" => true],
                ["title" => "추가 옵션", "value" => $info['options'], "short" => false],
                ["title" => "예약일시", "value" => $info['date'], "short" => false]
            ]
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

// =================================================================
// [함수 2] 알리고 SMS 발송 (secrets.php 상수 사용)
// =================================================================
function sendAligoSMS($receiver, $destination, $msg) {
    $sms_url = "https://apis.aligo.in/send/"; 
    $receiver = str_replace("-", "", $receiver);

    $_POST_DATA = [
        'key'      => ALIGO_API_KEY,    // secrets.php 상수
        'userid'   => ALIGO_USER_ID,    // secrets.php 상수
        'sender'   => ALIGO_SENDER,     // secrets.php 상수
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
    curl_exec($ch);
    curl_close($ch);
}
?>
<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .result-container { position: relative; z-index: 20; opacity: 1 !important; }
    .check-icon-circle {
        width: 80px; height: 80px; background: #000; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 2rem; color: #FFD400;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[800px] mx-auto px-6 pt-40 pb-32 min-h-screen flex flex-col justify-center text-center">

    <?php if ($is_success): ?>
        <div class="result-container">
            <div class="check-icon-circle">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="font-eng text-4xl md:text-5xl font-bold mb-4">BOOKING COMPLETED!</h1>
            <p class="font-kor text-lg text-neutral-600 mb-12">
                예약 신청이 성공적으로 접수되었습니다.<br>
                담당자가 내용을 확인 후 <strong class="text-black">확정 연락</strong>을 드리겠습니다.
            </p>
            <div class="bg-white rounded-[2rem] p-8 border border-neutral-200 shadow-xl text-left max-w-lg mx-auto mb-12">
                <h3 class="font-kor text-xl font-bold mb-6 pb-4 border-b border-neutral-100 flex justify-between items-center">
                    예약 요약
                    <span class="text-sm font-normal text-neutral-400 font-eng">No. <?= str_pad($booking_id, 6, '0', STR_PAD_LEFT) ?></span>
                </h3>
                <div class="space-y-4 font-kor text-neutral-600">
                    <div class="flex justify-between"><span class="text-neutral-400">예약자명</span><strong class="text-black"><?= htmlspecialchars($client_name) ?></strong></div>
                    <div class="flex justify-between"><span class="text-neutral-400">연락처</span><strong class="text-black"><?= htmlspecialchars($client_phone) ?></strong></div>
                    <div class="flex justify-between"><span class="text-neutral-400">이메일</span><strong class="text-black"><?= htmlspecialchars($client_email) ?></strong></div>
                    <div class="flex justify-between"><span class="text-neutral-400">선택 패키지</span><strong class="text-[#FFD400] font-eng bg-black px-2 py-0.5 rounded text-sm"><?= htmlspecialchars($selected_package) ?></strong></div>
                    <div class="flex justify-between items-start"><span class="text-neutral-400 shrink-0 mr-4">선택 옵션</span><div class="text-right"><?php if (!empty($display_options)): ?><?php foreach ($display_options as $opt): ?><div class="text-black text-sm mb-1 font-medium">• <?= htmlspecialchars($opt) ?></div><?php endforeach; ?><?php else: ?><span class="text-neutral-300">-</span><?php endif; ?></div></div>
                    <div class="border-t border-neutral-100 my-4"></div>
                    <div class="flex justify-between"><span class="text-neutral-400">시작 일시</span><strong class="text-black font-eng"><?= date('Y.m.d H:i', strtotime($start_date)) ?></strong></div>
                    <div class="flex justify-between"><span class="text-neutral-400">종료 일시</span><strong class="text-black font-eng"><?= date('Y.m.d H:i', strtotime($end_date)) ?></strong></div>
                </div>
            </div>
            <div class="flex gap-4 justify-center">
                <a href="/" class="px-8 py-4 bg-neutral-100 rounded-xl font-eng font-bold text-neutral-600 hover:bg-neutral-200 transition-colors">GO HOME</a>
                <a href="/studio/studio_intro.php" class="px-8 py-4 bg-black rounded-xl font-eng font-bold text-white hover:bg-[#FFD400] hover:text-black transition-colors shadow-lg">STUDIO INFO</a>
            </div>
        </div>
    <?php else: ?>
        <div class="result-container">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-8 text-red-500"><svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>
            <h1 class="font-eng text-3xl font-bold mb-4">BOOKING FAILED</h1>
            <p class="font-kor text-neutral-600 mb-8">죄송합니다. 예약 처리 중 오류가 발생했습니다.<br>잠시 후 다시 시도해주시거나 고객센터로 문의 바랍니다.</p>
            <p class="text-sm text-red-400 mb-8 bg-red-50 p-4 rounded-lg inline-block">Error: <?= htmlspecialchars($error_msg) ?></p>
            <div><button onclick="history.back()" class="px-8 py-4 bg-black rounded-xl font-eng font-bold text-white hover:bg-[#FFD400] hover:text-black transition-colors">BACK</button></div>
        </div>
    <?php endif; ?>
</div>
<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>