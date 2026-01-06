<?php
// Cafe24 호스팅 정보 입력
$host = 'localhost';
$db_name = 'griffhq'; // 호스팅 아이디와 동일
$db_user = 'griffhq'; // 호스팅 아이디와 동일
$db_pass = 'Good121930!@'; // ★여기에 실제 DB 비밀번호 입력★

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // 실무에서는 에러 메시지를 숨기는 것이 좋지만, 개발 중에는 확인을 위해 출력
    die("DB 연결 실패: " . $e->getMessage());
}
?>