<?php
// 1. 에러 강제 출력 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>STEP 1: 페이지 시작</h3>";

// 2. 세션 시작 테스트
if(!session_id()) {
    session_start();
}
echo "<h3>STEP 2: 세션 시작 성공</h3>";

// 3. DB 연결 파일 불러오기
$db_file = '../inc/db_connect.php';
if (!file_exists($db_file)) {
    die("<h3 style='color:red'>[에러] DB 연결 파일이 없습니다: $db_file</h3>");
}
require_once $db_file;
echo "<h3>STEP 3: DB 파일 로드 성공</h3>";

if (!isset($pdo)) {
    die("<h3 style='color:red'>[에러] \$pdo 변수가 없습니다. db_connect.php를 확인하세요.</h3>");
}
echo "<h3>STEP 4: DB 객체 확인됨</h3>";

// 4. 강제 로그인 테스트 (폼 입력 없이 코드로 직접 테스트)
$test_id = 'admin';
$test_pw = '0083'; // 실제 비밀번호

echo "<hr><h3>[테스트 시작] ID: $test_id / PW: $test_pw 로 로그인을 시도합니다.</h3>";

try {
    // 5. 쿼리 준비 (구버전 호환 문법 사용)
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    echo "Query Prepared...<br>";
    
    // 6. 쿼리 실행
    $stmt->execute(array(':username' => $test_id));
    echo "Query Executed...<br>";
    
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "사용자 찾음: " . htmlspecialchars($admin['username']) . "<br>";
        echo "DB에 저장된 해시: " . htmlspecialchars($admin['password']) . "<br>";
        
        // 7. 비밀번호 검증
        if (password_verify($test_pw, $admin['password'])) {
            echo "<h2 style='color:green'>SUCCESS: 비밀번호 일치! 로그인 로직 정상.</h2>";
            echo "<p>이 메시지가 보인다면, login.php의 문법(?? 연산자)이 문제였을 가능성이 큽니다.</p>";
        } else {
            echo "<h2 style='color:red'>FAIL: 비밀번호 불일치!</h2>";
            echo "<p>입력한 비밀번호: $test_pw</p>";
        }
    } else {
        echo "<h2 style='color:red'>FAIL: 해당 ID($test_id)가 DB에 없습니다.</h2>";
    }

} catch (PDOException $e) {
    echo "<h2 style='color:red'>DB 에러 발생: " . $e->getMessage() . "</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red'>일반 에러 발생: " . $e->getMessage() . "</h2>";
}
?>