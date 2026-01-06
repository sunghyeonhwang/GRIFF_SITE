<?php
session_start();

// 이미 로그인된 상태라면 대시보드로 이동
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// 로그인이 안 되어 있다면 로그인 페이지로 이동
header("Location: login.php");
exit;
?>