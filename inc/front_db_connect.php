<?php
$host = 'localhost'; // 카페24는 보통 localhost
$user = 'griffhq';   // 카페24 아이디
$pass = 'Good121930!@'; // 설정한 DB 비밀번호
$db   = 'griffhq';   // 카페24 아이디와 동일

$conn = new mysqli($host, $user, $pass, $db);

// 한글 깨짐 방지
$conn->set_charset("utf8");

// 연결 실패 시 에러 표시
if ($conn->connect_error) {
    die("DB 연결 실패: " . $conn->connect_error);
}
?>