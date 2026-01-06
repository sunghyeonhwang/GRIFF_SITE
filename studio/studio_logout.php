<?php
session_start();
session_destroy(); // 세션 파기
?>
<script>
    alert('로그아웃 되었습니다.');
    location.href = '/'; // 메인 페이지로 이동
</script>