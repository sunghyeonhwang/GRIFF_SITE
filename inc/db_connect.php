<?php
// [inc/db_connect.php]

// 1. secrets.php 파일 로드 (같은 폴더에 있다고 가정)
// __DIR__ 은 현재 파일(db_connect.php)이 있는 경로를 의미합니다.
require_once __DIR__ . '/secrets.php';

try {
    // 2. secrets.php에 정의된 상수(DB_HOST, DB_NAME 등)를 사용
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // PDO 객체 생성 (이 $pdo 변수를 다른 페이지들이 가져다 씀)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // (구형 코드 호환용 $conn 변수도 필요하다면 아래처럼 추가)
    $conn = $pdo; 

} catch (PDOException $e) {
    // 실운영시에는 에러 메시지를 숨기는 것이 좋습니다.
    // die("DB Connection Error"); 
    die("DB Connection Error: " . $e->getMessage());
}
?>