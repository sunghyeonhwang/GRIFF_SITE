<?php
// [inc/front_db_connect.php]

// 1. 보안 설정 파일 로드 (여기서 아이디/비번을 가져옵니다)
// __DIR__ 은 현재 파일이 있는 경로(/inc)를 의미합니다.
require_once __DIR__ . '/secrets.php';

// 2. MySQLi 연결 (secrets.php의 상수 사용)
// define된 DB_HOST, DB_USER, DB_PASS, DB_NAME 상수를 그대로 씁니다.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. 연결 에러 체크
if ($conn->connect_error) {
    // 실 서버에서는 구체적인 에러 내용을 보여주지 않는 것이 보안상 좋습니다.
    // die("Connection failed: " . $conn->connect_error); // 개발용
    die("DB Connection Failed"); // 운영용
}

// 4. 한글 깨짐 방지 설정 (utf8mb4 권장)
$conn->set_charset("utf8mb4");
?>