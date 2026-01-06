<?php
session_start();

// 1. 모든 세션 변수 해제 (로그인 정보 삭제)
session_unset();

// 2. 세션 자체를 파괴
session_destroy();

// 3. 알림창을 띄우고 로그인 페이지로 이동
echo "<script>
        alert('성공적으로 로그아웃 되었습니다.');
        location.href = 'login.php';
      </script>";
exit;
?>