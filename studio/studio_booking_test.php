<?php
$root = $_SERVER['DOCUMENT_ROOT'];
// 헤더는 선택사항 (필요 없으면 주석 처리)
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<div class="max-w-md mx-auto mt-20 p-8 border border-neutral-300 rounded-2xl shadow-lg text-center bg-white">
    <h1 class="font-bold text-2xl mb-2">🧪 예약 시스템 테스트</h1>
    <p class="text-neutral-500 mb-8 text-sm">아래 버튼을 누르면 테스트 데이터가 즉시 전송됩니다.</p>

    <form action="studio_booking_ok.php" method="POST">
        
        <input type="hidden" name="client_name" value="테스트맨">
        <input type="hidden" name="client_phone" value="010-1234-5678">
        <input type="hidden" name="client_email" value="test@griff.studio">
        <input type="hidden" name="client_company" value="그리프 테스트팀">
        
        <input type="hidden" name="selected_package" value="1D_PRO">
        
        <input type="hidden" name="service_type" value="라이브행사">
        <input type="hidden" name="pax" value="5인 이상">
        <input type="hidden" name="vehicle_number" value="12가 3456">
        
        <input type="hidden" name="start_date" value="<?= date('Y-m-d 09:00', strtotime('+1 day')) ?>">
        <input type="hidden" name="end_date" value="<?= date('Y-m-d 18:00', strtotime('+1 day')) ?>">

        <input type="hidden" name="equipment[]" value="엔지니어: 테크니컬 디렉터(TD)">
        <input type="hidden" name="equipment[]" value="엔지니어: 카메라 오퍼레이터">
        <input type="hidden" name="equipment[]" value="Sony FX6 Body">

        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-4 rounded-xl text-lg transition-colors shadow-md">
            🚀 테스트 예약 발송
        </button>
    </form>

    <div class="mt-6 text-left bg-neutral-100 p-4 rounded-lg text-xs text-neutral-500">
        <strong>[전송될 데이터 미리보기]</strong><br>
        - 이름: 테스트맨<br>
        - 패키지: 1D_PRO<br>
        - 일시: 내일 09:00 ~ 18:00<br>
        - 옵션: TD, 카메라 감독, Sony FX6
    </div>
</div>

<style>
    /* 간단한 스타일링 */
    body { background-color: #f3f4f6; font-family: sans-serif; }
</style>