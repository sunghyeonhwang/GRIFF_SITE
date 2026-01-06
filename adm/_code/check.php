<?php
// 에러를 강제로 화면에 출력
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>서버 진단 시작</h1>";

// 1. 현재 위치 확인
echo "<p>현재 파일 위치: " . __DIR__ . "</p>";

// 2. 연결 파일 경로 확인
$db_path = dirname(__DIR__) . '/inc/db_connect.php';
echo "<p>찾으려는 DB 파일 경로: " . $db_path . "</p>";

// 3. 파일 존재 여부 확인
if (file_exists($db_path)) {
    echo "<p style='color:green;'>[성공] DB 연결 파일이 존재합니다.</p>";
} else {
    echo "<p style='color:red;'>[실패] DB 연결 파일을 찾을 수 없습니다. 경로를 확인해주세요.</p>";
    echo "<p>실제 폴더 구조가 /inc 가 맞는지, /includes 는 아닌지 FTP에서 확인해주세요.</p>";
    exit;
}

// 4. DB 연결 시도
try {
    require_once $db_path;
    if (isset($pdo)) {
        echo "<p style='color:green;'>[성공] 데이터베이스 연결 성공!</p>";
    } else {
        echo "<p style='color:red;'>[실패] 파일은 불렀으나 \$pdo 변수가 없습니다.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>[에러] DB 연결 중 오류 발생: " . $e->getMessage() . "</p>";
}
?>