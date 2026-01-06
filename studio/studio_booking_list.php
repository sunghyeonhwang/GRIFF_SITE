<?php
// [1] 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// [2] PRG 패턴 적용 (새로고침/뒤로가기 오류 해결의 핵심)
// POST로 로그인 정보가 넘어왔다면 -> 세션에 저장 후 -> 자기 자신에게 GET 방식으로 이동(Redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['client_email']) && isset($_POST['client_phone'])) {
        $_SESSION['client_email'] = $_POST['client_email'];
        $_SESSION['client_phone'] = $_POST['client_phone'];
        
        // 데이터 재전송 방지를 위해 페이지 리로딩
        header("Location: studio_booking_list.php");
        exit;
    }
}

// [3] 에러 설정 및 DB 연결
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} elseif (file_exists("$root/inc/db_connect.php")) {
    include "$root/inc/db_connect.php";
} else {
    include "../inc/db_connect.php";
}

// [4] 권한 체크 (세션 확인)
if (empty($_SESSION['client_email']) || empty($_SESSION['client_phone'])) {
    echo "<script>alert('로그인 정보가 없습니다. 다시 로그인해주세요.'); location.href='studio_check.php';</script>";
    exit;
}

$email = $_SESSION['client_email'];
$phone = $_SESSION['client_phone'];

// [5] 예약 목록 조회 (최신순)
$sql = "SELECT * FROM studio_bookings WHERE client_email = ? AND client_phone = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $phone);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    
    /* 상태 뱃지 스타일 */
    .badge {
        display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px;
        font-size: 0.8rem; font-weight: 700; font-family: 'Freesentation', sans-serif;
    }
    .badge-pending { background-color: #FEF3C7; color: #D97706; } /* 대기중 (노랑) */
    .badge-confirmed { background-color: #DBEAFE; color: #1E40AF; } /* 확정 (파랑) */
    .badge-canceled { background-color: #F3F4F6; color: #9CA3AF; text-decoration: line-through; } /* 취소 (회색) */
    .badge-completed { background-color: #D1FAE5; color: #065F46; } /* 완료 (초록) */

    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1000px] mx-auto px-6 pt-32 md:pt-48 pb-24 min-h-screen">

    <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
        <div>
            <h1 class="font-eng text-[40px] md:text-[50px] font-bold leading-none text-black">
                MY BOOKING LIST<span class="text-[#FFD400]">.</span>
            </h1>
            <p class="font-kor text-neutral-500 mt-3 text-lg">
                <strong class="text-black"><?= htmlspecialchars($_SESSION['client_name'] ?? '고객') ?></strong>님의 예약 내역입니다.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="studio_booking.php" class="px-6 py-3 bg-black text-white rounded-xl font-bold font-eng hover:bg-[#FFD400] hover:text-black transition-colors shadow-lg">
                + NEW BOOKING
            </a>
            <a href="studio_logout.php" class="px-6 py-3 bg-neutral-100 text-neutral-600 rounded-xl font-bold font-eng hover:bg-neutral-200 transition-colors">
                LOGOUT
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    // 상태별 텍스트 및 클래스 설정
                    $status_text = '';
                    $badge_class = '';
                    $is_link_active = true;

                    switch ($row['status']) {
                        case 'pending': 
                            $status_text = '예약 대기'; 
                            $badge_class = 'badge-pending'; 
                            break;
                        case 'confirmed': 
                            $status_text = '예약 확정'; 
                            $badge_class = 'badge-confirmed'; 
                            break;
                        case 'completed': 
                            $status_text = '이용 완료'; 
                            $badge_class = 'badge-completed'; 
                            $is_link_active = false; // 완료된 건은 수정 불가
                            break;
                        case 'canceled': 
                            $status_text = '취소됨'; 
                            $badge_class = 'badge-canceled'; 
                            $is_link_active = false; // 취소된 건은 수정 불가
                            break;
                        default:
                            $status_text = $row['status'];
                            $badge_class = 'bg-gray-100 text-gray-800';
                    }

                    // 날짜 포맷
                    $s_date = date('Y.m.d H:i', strtotime($row['start_date']));
                    $e_date = date('Y.m.d H:i', strtotime($row['end_date']));
                    // 같은 날이면 뒤에 시간만 표시
                    if (date('Y-m-d', strtotime($row['start_date'])) == date('Y-m-d', strtotime($row['end_date']))) {
                        $e_date = date('H:i', strtotime($row['end_date']));
                    }
                ?>
                
                <div class="bg-white rounded-[20px] p-6 md:p-8 border border-neutral-200 transition-all duration-300 <?= $is_link_active ? 'card-hover' : 'opacity-70' ?>">
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-6">
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="badge <?= $badge_class ?>"><?= $status_text ?></span>
                                <span class="text-neutral-400 text-sm font-eng">No. <?= str_pad($row['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <h3 class="font-eng text-2xl font-bold text-black mb-1">
                                <?= htmlspecialchars($row['selected_package']) ?>
                            </h3>
                            <div class="text-neutral-600 font-kor text-lg">
                                <?= $s_date ?> ~ <?= $e_date ?>
                            </div>
                            <div class="text-neutral-400 text-sm mt-2">
                                예약신청일: <?= date('Y.m.d', strtotime($row['created_at'])) ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php if ($row['status'] === 'pending'): ?>
                                <a href="studio_edit.php?id=<?= $row['id'] ?>" class="px-6 py-3 border border-neutral-300 rounded-xl font-bold text-neutral-700 hover:bg-black hover:text-white hover:border-black transition-colors">
                                    수정 / 취소
                                </a>
                            <?php elseif ($row['status'] === 'confirmed'): ?>
                                <a href="studio_edit.php?id=<?= $row['id'] ?>" class="px-6 py-3 bg-neutral-100 rounded-xl font-bold text-neutral-600 hover:bg-neutral-200 transition-colors">
                                    상세 보기
                                </a>
                            <?php else: ?>
                                <span class="px-6 py-3 text-neutral-400 font-medium cursor-not-allowed">
                                    상세 보기 불가
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        
        <?php else: ?>
            <div class="text-center py-20 bg-neutral-50 rounded-[2rem] border border-neutral-200 border-dashed">
                <div class="text-neutral-300 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h3 class="font-kor text-xl text-neutral-500 mb-6">아직 예약된 내역이 없습니다.</h3>
                <a href="studio_booking.php" class="inline-block px-8 py-4 bg-black text-white rounded-xl font-bold hover:bg-[#FFD400] hover:text-black transition-colors shadow-lg">
                    첫 예약하기
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>