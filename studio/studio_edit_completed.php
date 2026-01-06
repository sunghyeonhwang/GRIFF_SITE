<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

$booking_id = $_GET['id'] ?? 0;

// 예약 정보 조회
$sql = "SELECT * FROM studio_bookings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/';</script>";
    exit;
}

// [중요 수정] 취소된 예약은 세션 검증을 패스하거나, 세션이 있을 때만 체크
// 만약 '취소됨(canceled)' 상태라면 로그인 여부 상관없이 결과 페이지를 보여줌 (사용자 편의)
// 또는 세션이 살아있다면 이메일 대조
if (isset($_SESSION['client_email']) && $booking['client_email'] !== $_SESSION['client_email']) {
    echo "<script>alert('본인의 예약만 확인할 수 있습니다.'); location.href='/';</script>";
    exit;
}

$equipment_arr = json_decode($booking['equipment'] ?? '[]', true);
$is_canceled = ($booking['status'] === 'canceled');
?>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .result-container { position: relative; z-index: 20; opacity: 1 !important; }
    
    .check-icon-circle {
        width: 80px; height: 80px; background: #000; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 2rem; color: #FFD400;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .check-icon-circle.canceled {
        background: #EF4444; color: #fff;
        box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2);
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[800px] mx-auto px-6 pt-40 pb-32 min-h-screen flex flex-col justify-center text-center">

    <div class="result-container">
        <div class="check-icon-circle <?= $is_canceled ? 'canceled' : '' ?>">
            <?php if($is_canceled): ?>
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            <?php else: ?>
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            <?php endif; ?>
        </div>
        
        <h1 class="font-eng text-4xl md:text-5xl font-bold mb-4">
            <?= $is_canceled ? "CANCELLATION COMPLETE!" : "UPDATE COMPLETE!" ?>
        </h1>
        <p class="font-kor text-lg text-neutral-600 mb-12">
            <?= $is_canceled 
                ? "예약이 정상적으로 취소되었습니다.<br>이용해 주셔서 감사합니다." 
                : "예약 정보가 성공적으로 수정되었습니다.<br>변경된 내용은 아래와 같습니다." ?>
        </p>

        <div class="bg-white rounded-[2rem] p-8 border border-neutral-200 shadow-xl text-left max-w-lg mx-auto mb-12">
            <h3 class="font-kor text-xl font-bold mb-6 pb-4 border-b border-neutral-100 flex justify-between items-center">
                <?= $is_canceled ? "취소 내역" : "변경 내역" ?>
                <span class="text-sm font-normal text-neutral-400 font-eng">No. <?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </h3>
            
            <div class="space-y-4 font-kor text-neutral-600">
                <div class="flex justify-between">
                    <span class="text-neutral-400">예약자명</span>
                    <strong class="text-black"><?= htmlspecialchars($booking['client_name']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">패키지</span>
                    <strong class="text-[#FFD400] font-eng bg-black px-2 py-0.5 rounded text-sm"><?= htmlspecialchars($booking['selected_package']) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">시작 일시</span>
                    <strong class="text-black font-eng"><?= date('Y.m.d H:i', strtotime($booking['start_date'])) ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-neutral-400">종료 일시</span>
                    <strong class="text-black font-eng"><?= date('Y.m.d H:i', strtotime($booking['end_date'])) ?></strong>
                </div>
                
                <div class="border-t border-neutral-100 my-2 pt-2">
                    <span class="block text-neutral-400 mb-2 text-sm">포함된 옵션</span>
                    <?php if (!empty($equipment_arr)): ?>
                        <?php foreach ($equipment_arr as $opt): ?>
                            <div class="text-black text-sm mb-1">• <?= htmlspecialchars($opt) ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-neutral-300 text-sm">- 선택 없음</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="studio_logout.php" class="px-8 py-4 bg-neutral-200 rounded-xl font-eng font-bold text-neutral-600 hover:bg-neutral-300 transition-colors">
                LOGOUT
            </a>
            
            <?php if (!$is_canceled): ?>
            <a href="studio_booking_list.php" class="px-8 py-4 bg-white border border-neutral-300 rounded-xl font-eng font-bold text-black hover:bg-neutral-50 transition-colors">
                MODIFY
            </a>
            <?php endif; ?>

            <a href="/" class="px-8 py-4 bg-black rounded-xl font-eng font-bold text-white hover:bg-[#FFD400] hover:text-black transition-colors shadow-lg">
                GO HOME
            </a>
        </div>
    </div>
</div>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>