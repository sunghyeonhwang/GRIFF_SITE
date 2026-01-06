<?php
// [1. DB 연결 설정]
$host = 'localhost'; 
$user = 'griffhq';   
$pass = 'Good121930!@';   
$dbName = 'griffhq'; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Connection Error: " . $e->getMessage());
}

// [2. Google reCAPTCHA 검증]
$recaptcha_secret = "6Ldo0j0sAAAAABUeuYZIzYVIrST7EtjzZkoo4bev"; 
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

    // -------------------------------------------------------------
    // [NEW] 1. 고객에게 접수 확인 문자 발송 (알리고)
    // -------------------------------------------------------------
    $sms_msg = "[그리프] 안녕하세요 {$name}님.\n보내주신 프로젝트 문의가 정상적으로 접수되었습니다.\n\n담당자가 내용 확인 후 빠르게 연락드리겠습니다.\n\n그리프에 관심을 가져주셔서 진심으로 감사드립니다.";
    sendAligoSMS($phone, $name, $sms_msg);

    // -------------------------------------------------------------
    // [NEW] 2. 슬랙 알림 발송 (관리자용)
    // -------------------------------------------------------------
    sendSlackNotification($name, $phone, $email, $budget, $raw_message);

    echo "<script>
        alert('문의가 성공적으로 접수되었습니다. 담당자가 확인 후 연락드리겠습니다.');
        location.href = '/'; 
    </script>";

} catch(PDOException $e) {
    echo "<script>
        alert('시스템 오류가 발생했습니다.\\n" . addslashes($e->getMessage()) . "');
        history.back();
    </script>";
}

// =================================================================
// [함수 1] 알리고 SMS 발송 함수
// =================================================================
function sendAligoSMS($receiver, $destination, $msg) {
    // 알리고 계정 정보 (이전 설정값 적용)
    $sms_config = [
        'userid' => 'griff261',
        'key'    => '5o4amu1n07weck1mof53q9lc026fwkvu',
        'sender' => '02-326-3701',
    ];

    $sms_url = "https://apis.aligo.in/send/"; 
    $receiver = str_replace("-", "", $receiver); // 하이픈 제거

    $_POST_DATA = [
        'key'      => $sms_config['key'],
        'userid'   => $sms_config['userid'],
        'sender'   => $sms_config['sender'],
        'receiver' => $receiver,
        'msg'      => $msg,
        'msg_type' => 'LMS' // 내용이 길 수 있으므로 LMS로 설정
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

// =================================================================
// [함수 2] 슬랙 알림 발송 함수
// =================================================================
function sendSlackNotification($name, $phone, $email, $budget, $message) {
    // ⚠️ 슬랙 웹훅 URL
    $webhook_url = "https://hooks.slack.com/services/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";

    $payload = [
        "text" => "📨 *새로운 프로젝트 문의가 도착했습니다!*",
        "attachments" => [
            [
                "color" => "#0D4097", // 브랜드 컬러 (네이비)
                "fields" => [
                    ["title" => "성함", "value" => $name, "short" => true],
                    ["title" => "연락처", "value" => $phone, "short" => true],
                    ["title" => "이메일", "value" => $email, "short" => true],
                    ["title" => "예산 범위", "value" => $budget, "short" => true],
                    ["title" => "문의 내용", "value" => $message, "short" => false]
                ],
                "footer" => "Griff Studio Website Contact",
                "ts" => time()
            ]
        ]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // 전송 실행
    curl_exec($ch);
    curl_close($ch);
}
?>