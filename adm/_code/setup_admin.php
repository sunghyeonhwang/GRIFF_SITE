<?php
// 방금 만든 DB 연결 파일을 불러옵니다 (경로 주의: 상위 폴더로 이동)
include '../inc/db_connect.php';

// 1. 요청하신 계정 정보 (admin / 0083)
$username = 'admin';
$password = '0083'; 
$email = 'sunghyeon.hwang@griff.co.kr';

// 2. 비밀번호 암호화
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. 기존 관리자 삭제 후 재생성
try {
    // 기존 계정 삭제
    $sql = "DELETE FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);

    // 새 계정 생성
    $sql = "INSERT INTO admins (username, password, email) VALUES (:username, :pass, :email)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username, ':pass' => $hashed_password, ':email' => $email]);

    echo "<h1>🎉 관리자 계정 생성 완료! (in /adm)</h1>";
    echo "<p>ID: <strong>$username</strong></p>";
    echo "<p>PW: <strong>$password</strong> (암호화됨)</p>";
    echo "<p>이제 <a href='login.php'>로그인 페이지</a>로 이동하세요.</p>";

} catch(PDOException $e) {
    echo "계정 생성 실패: " . $e->getMessage();
}
?>