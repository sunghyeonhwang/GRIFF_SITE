<?php
// [1. DB 연결 및 설정 로드]
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/db_connect.php'; // DB 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/secrets.php'; // ★ Secrets 로드

// [2. Google reCAPTCHA 검증]
$recaptcha_secret = RECAPTCHA_SECRET_KEY; // secrets.php 상수 사용
$recaptcha_response = $_POST['g-recaptcha-response'];

if (empty($recaptcha_response)) {
    echo "<script>alert('자동등록방지를 체크해주세요.'); history.back();</script>";
    exit;
}

$verify_url = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $verify_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$verify_result = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($verify_result);

if (!$response_data || !$response_data->success) {
    echo "<script>alert('자동등록방지 인증에 실패했습니다. 다시 시도해 주세요.'); history.back();</script>";
    exit;
}

// [3. 데이터 수신]
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$budget = $_POST['budget'] ?? ''; 
$raw_message = $_POST['message'] ?? '';

$final_message = "[예산 범위: " . $budget . "]\n\n" . $raw_message;
$subject = "홈페이지 프로젝트 문의 (" . $name . ")";
$ip_address = $_SERVER['REMOTE_ADDR'];

// [4. DB 입력]
if (!isset($conn)) {
    // db_connect.php가 없거나 로드 안 된 경우 대비 (보통은 위 require_once에서 로드됨)
    try {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
        $conn = new PDO($dsn, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("DB Connection Error");
    }
}

$sql = "INSERT INTO inquiries (name, email, phone, company, subject, message, status, ip_address, created_at) 
        VALUES (:name, :email, :phone, :company, :subject, :message, 'new', :ip_address, NOW())";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':company' => '',
        ':subject' => $subject,
        ':message' => $final_message,
        ':ip_address' => $ip_address
    ]);

    // 1. 고객에게 접수 확인 문자 (알리고)
    $sms_msg = "[그리프] 안녕하세요 {$name}님.\n보내주신 프로젝트 문의가 정상적으로 접수되었습니다.\n\n담당자가 내용 확인 후 빠르게 연락드리겠습니다.\n\n그리프에 관심을 가져주셔서 진심으로 감사드립니다.";
    sendAligoSMS($phone, $name, $sms_msg);

    // 2. 슬랙 알림 (관리자용)
    sendSlackNotification($name, $phone, $email, $budget, $raw_message);

    echo "<script>
        alert('문의가 성공적으로 접수되었습니다. 담당자가 확인 후 연락드리겠습니다.');
        location.href = '/'; 
    </script>";

} catch(PDOException $e) {
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); history.back();</script>";
}

// =================================================================
// [함수] 알리고 SMS (secrets.php 상수 사용)
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

// =================================================================
// [함수] 슬랙 알림 (secrets.php 상수 사용)
// =================================================================
function sendSlackNotification($name, $phone, $email, $budget, $message) {
    $webhook_url = SLACK_WEBHOOK_CONTACT; // secrets.php 상수

    $payload = [
        "text" => "📨 *새로운 프로젝트 문의가 도착했습니다!*",
        "attachments" => [[
            "color" => "#0D4097",
            "fields" => [
                ["title" => "성함", "value" => $name, "short" => true],
                ["title" => "연락처", "value" => $phone, "short" => true],
                ["title" => "이메일", "value" => $email, "short" => true],
                ["title" => "예산 범위", "value" => $budget, "short" => true],
                ["title" => "문의 내용", "value" => $message, "short" => false]
            ],
            "footer" => "Griff Studio Website Contact",
            "ts" => time()
        ]]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
?>