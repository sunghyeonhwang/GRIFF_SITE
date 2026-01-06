<?php
session_start(); // [필수] 세션 시작

$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [1] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

// [2] 장비 목록 조회
$equip_list = [];
$sql_eq = "SELECT * FROM studio_equipment ORDER BY id ASC";
$res_eq = $conn->query($sql_eq);
if ($res_eq) {
    while ($row = $res_eq->fetch_assoc()) {
        $equip_list[] = $row;
    }
}

// [3] 예약 정보 조회
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 세션 체크
if (empty($_SESSION['client_email']) || empty($_SESSION['client_phone'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='studio_check.php';</script>";
    exit;
}

// ID 유효성 체크
if ($booking_id <= 0) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='studio_booking_list.php';</script>";
    exit;
}

// DB에서 해당 ID 조회 (본인 확인)
$sql = "SELECT * FROM studio_bookings WHERE id = ? AND client_email = ? AND client_phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $booking_id, $_SESSION['client_email'], $_SESSION['client_phone']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "<script>alert('예약 정보를 찾을 수 없거나 접근 권한이 없습니다.'); location.href='studio_booking_list.php';</script>";
    exit;
}

// [4] 다른 예약된 날짜 가져오기 (수정 시 중복 방지용)
// ★ 중요: 현재 수정 중인 내 예약($booking_id)은 제외하고 가져와야 함 (내 날짜는 내가 다시 선택 가능해야 하므로)
$booked_ranges = [];
$sql_book = "SELECT start_date, end_date FROM studio_bookings WHERE status IN ('pending', 'confirmed') AND id != ?";
$stmt_book = $conn->prepare($sql_book);
$stmt_book->bind_param("i", $booking_id);
$stmt_book->execute();
$res_book = $stmt_book->get_result();

if ($res_book) {
    while($row = $res_book->fetch_assoc()) {
        $booked_ranges[] = [
            'from' => $row['start_date'],
            'to'   => $row['end_date']
        ];
    }
}
$json_booked = json_encode($booked_ranges);

// 상태 체크
$is_editable = ($booking['status'] === 'pending');
$readonly_attr = $is_editable ? '' : 'disabled';
$bg_class = $is_editable ? 'bg-white' : 'bg-neutral-100 cursor-not-allowed';

$saved_equipment = json_decode($booking['equipment'] ?? '[]', true);
if (!is_array($saved_equipment)) $saved_equipment = [];
?>

<?php if($is_editable): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .fade-up-init { opacity: 0; transform: translateY(30px); }

    .input-field {
        width: 100%; border: 1px solid #e5e7eb; border-radius: 0.75rem; 
        padding: 1rem 1.2rem; font-family: 'Freesentation', sans-serif; font-size: 1.05rem; color: #111; 
        transition: all 0.3s;
    }
    .input-field:focus { border-color: #FFD400; outline: none; }
    .input-field[readonly]:not([disabled]) { cursor: pointer; background-color: #fff; }
    
    .eng-price { font-family: 'URWDIN', sans-serif; font-weight: 700; color: #111; }
    .option-disabled { opacity: 0.4; pointer-events: none; filter: grayscale(100%); }
    .equip-checkbox:checked + div { background-color: #FFD400; border-color: #FFD400; color: #000; }

    /* Flatpickr 커스텀 */
    .flatpickr-calendar { border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important; border: none; }
    .flatpickr-day.selected { background: #000 !important; border-color: #000 !important; color: #FFD400; font-weight: bold; }

    /* SweetAlert 커스텀 */
    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
        background-color: #000 !important; color: #fff !important; font-family: 'URWDIN', sans-serif !important; 
        font-weight: 700 !important; border-radius: 12px !important; padding: 12px 30px !important;
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-24 min-h-screen">

    <div class="mb-12 text-center fade-up-init">
        <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
            MY BOOKING<span class="text-[#FFD400]">.</span>
        </h1>
        <div class="inline-block mt-4 px-5 py-2 rounded-full text-base font-bold font-kor shadow-sm 
            <?= $is_editable ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' ?>">
            상태: <?= $is_editable ? '예약 대기 (수정 가능)' : '예약 확정 (수정 불가)' ?>
        </div>
    </div>

    <form id="editForm" action="studio_edit_ok.php" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
        <input type="hidden" name="mode" id="modeInput" value="update">

        <div class="lg:col-span-4 lg:sticky lg:top-[120px] h-fit space-y-6 fade-up-init">
            <div class="bg-black text-white rounded-3xl p-8 shadow-xl">
                <h3 class="font-eng text-xl font-bold mb-6 text-[#FFD400]">ESTIMATE</h3>
                <div class="space-y-4 font-kor text-sm text-neutral-300 mb-8 border-b border-neutral-800 pb-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span>선택 패키지</span>
                            <span id="display_package" class="text-white font-bold text-right font-eng text-lg">미선택</span>
                        </div>
                        <div class="bg-neutral-900 rounded-xl p-4 text-xs text-neutral-400 leading-relaxed">
                            <ul id="package_specs_list" class="space-y-1 list-disc list-inside"><li>패키지를 선택해주세요.</li></ul>
                        </div>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span>선택 날짜</span>
                        <span id="display_date" class="text-white font-bold">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span>예약 기간</span>
                        <span id="display_days" class="text-white font-bold">1일</span>
                    </div>
                    <div class="pt-2 border-t border-neutral-800 mt-2">
                        <span class="block mb-2 text-neutral-400">추가 옵션</span>
                        <ul id="selected_options_list" class="text-white text-xs space-y-1 text-right"><li class="text-neutral-600">- 선택 없음</li></ul>
                    </div>
                </div>
                <div class="flex justify-between items-end">
                    <span class="font-kor text-sm">예상 합계 (VAT 별도)</span>
                    <span id="total_price" class="font-eng text-3xl font-bold text-[#FFD400]">₩0</span>
                </div>
                <p class="text-xs text-neutral-500 mt-4">* 예약 기간(일수)에 비례하여 계산됩니다.</p>
            </div>

            <a href="studio_booking_list.php" class="block w-full py-4 rounded-2xl border border-neutral-300 text-center font-bold text-neutral-500 hover:bg-neutral-100 hover:text-black transition-colors">
                BACK TO LIST
            </a>
        </div>

        <div class="lg:col-span-8 space-y-12 fade-up-init">
            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">기본 정보</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">예약자명</label>
                        <input type="text" value="<?= htmlspecialchars($booking['client_name']) ?>" class="input-field bg-neutral-100" readonly>
                    </div>
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">연락처</label>
                        <input type="text" value="<?= htmlspecialchars($booking['client_phone']) ?>" class="input-field bg-neutral-100" readonly>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">예약 변경</h3>
                <div class="space-y-8">
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">패키지 선택</label>
                        <select name="selected_package" id="package_select" class="input-field font-bold text-[#111] <?= $bg_class ?>" <?= $readonly_attr ?> onchange="handlePackageChange()">
                            <optgroup label="4 HOURS (Half Day)">
                                <option value="4H_START" data-price="240000" data-type="4h" <?= $booking['selected_package']=='4H_START'?'selected':'' ?>>START</option>
                                <option value="4H_BASIC" data-price="700000" data-type="4h" <?= $booking['selected_package']=='4H_BASIC'?'selected':'' ?>>BASIC</option>
                                <option value="4H_MULTI" data-price="1000000" data-type="4h" <?= $booking['selected_package']=='4H_MULTI'?'selected':'' ?>>MULTI</option>
                                <option value="4H_PRO" data-price="1400000" data-type="4h" <?= $booking['selected_package']=='4H_PRO'?'selected':'' ?>>PRO</option>
                            </optgroup>
                            <optgroup label="1 DAY (8 Hours)">
                                <option value="1D_START" data-price="420000" data-type="1d" <?= $booking['selected_package']=='1D_START'?'selected':'' ?>>D-START</option>
                                <option value="1D_BASIC" data-price="900000" data-type="1d" <?= $booking['selected_package']=='1D_BASIC'?'selected':'' ?>>D-BASIC</option>
                                <option value="1D_MULTI" data-price="1300000" data-type="1d" <?= $booking['selected_package']=='1D_MULTI'?'selected':'' ?>>D-MULTI</option>
                                <option value="1D_PRO" data-price="1800000" data-type="1d" <?= $booking['selected_package']=='1D_PRO'?'selected':'' ?>>D-PRO</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">시작 일시</label>
                            <input type="text" name="start_date" id="start_date_picker" value="<?= $booking['start_date'] ?>" class="input-field <?= $bg_class ?>" <?= $readonly_attr ?> required>
                        </div>
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">종료 일시</label>
                            <input type="text" name="end_date" id="end_date_picker" value="<?= $booking['end_date'] ?>" class="input-field <?= $bg_class ?>" <?= $readonly_attr ?> required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">방문 인원</label>
                            <select name="pax" class="input-field <?= $bg_class ?>" <?= $readonly_attr ?>>
                                <?php 
                                $paxes = ['1인','2인','3인','4인','5인','5인 이상'];
                                foreach($paxes as $px) {
                                    $sel = ($booking['pax'] == $px) ? 'selected' : '';
                                    echo "<option value='$px' $sel>$px</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">차량 번호</label>
                            <input type="text" name="vehicle_number" value="<?= htmlspecialchars($booking['vehicle_number']) ?>" class="input-field <?= $bg_class ?>" <?= $readonly_attr ?>>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($is_editable): ?>
            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">옵션 변경</h3>
                <h4 class="font-kor font-bold text-lg mb-4 text-neutral-600">엔지니어 옵션</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <?php
                    $engineers = [
                        ['name'=>'테크니컬 디렉터', 'val'=>'엔지니어: 테크니컬 디렉터(TD)', 'p4'=>250000, 'p1'=>400000, 'desc'=>'라이브 송출, 스위칭'],
                        ['name'=>'카메라 오퍼레이터', 'val'=>'엔지니어: 카메라 오퍼레이터', 'p4'=>200000, 'p1'=>320000, 'desc'=>'카메라 1대 기준'],
                        ['name'=>'오디오 엔지니어', 'val'=>'엔지니어: 오디오 엔지니어', 'p4'=>150000, 'p1'=>240000, 'desc'=>'마이크셋업, 믹싱'],
                        ['name'=>'현장 PD', 'val'=>'엔지니어: 현장 PD', 'p4'=>250000, 'p1'=>400000, 'desc'=>'진행, 큐시트 총괄']
                    ];
                    foreach($engineers as $eng):
                        $checked = in_array($eng['val'], $saved_equipment) ? 'checked' : '';
                    ?>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="<?= $eng['val'] ?>" class="equip-checkbox hidden option-chk" data-name="<?= $eng['name'] ?>" data-price-4h="<?= $eng['p4'] ?>" data-price-1d="<?= $eng['p1'] ?>" <?= $checked ?> onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-5 transition-all hover:border-[#FFD400] flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start mb-2"><strong class="font-kor text-lg text-neutral-900"><?= $eng['name'] ?></strong><svg class="w-6 h-6 text-neutral-300 group-hover:text-[#FFD400] transition-colors check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
                            <div class="flex justify-between items-end"><span class="text-sm text-neutral-500 font-kor"><?= $eng['desc'] ?></span><span class="eng-price text-[#111]">₩<?= number_format($eng['p1']) ?></span></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <h4 class="font-kor font-bold text-lg mb-4 text-neutral-600">장비 옵션</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach($equip_list as $eq): 
                        $checked = in_array($eq['name'], $saved_equipment) ? 'checked' : '';
                    ?>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="<?= htmlspecialchars($eq['name']) ?>" class="equip-checkbox hidden option-chk-eq" <?= $checked ?> onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-4 transition-all hover:border-[#FFD400] flex items-center justify-between">
                            <span class="font-eng font-medium text-neutral-700"><?= htmlspecialchars($eq['name']) ?></span>
                            <svg class="w-5 h-5 text-neutral-300 group-hover:text-[#FFD400] transition-colors check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="bg-neutral-50 rounded-[2rem] p-8 border border-neutral-200">
                    <h3 class="font-kor text-xl font-bold mb-4 text-neutral-500">선택된 옵션 (수정 불가)</h3>
                    <div class="text-sm text-neutral-600"><?= empty($saved_equipment) ? '선택 없음' : implode(', ', $saved_equipment) ?></div>
                </div>
            <?php endif; ?>

            <div class="flex gap-4 pt-6">
                <?php if($is_editable): ?>
                    <button type="button" onclick="cancelBooking()" class="flex-1 bg-red-50 text-red-500 border border-red-100 font-bold py-4 rounded-xl text-center hover:bg-red-100 transition-colors shadow-sm">
                        예약 취소
                    </button>
                    <button type="button" onclick="submitEdit()" class="flex-1 bg-black text-white font-bold py-4 rounded-xl hover:bg-[#FFD400] hover:text-black transition-colors shadow-lg">
                        수정내용 저장
                    </button>
                <?php else: ?>
                    <button type="button" class="w-full bg-neutral-300 text-white font-bold py-4 rounded-xl cursor-not-allowed">수정 불가 (예약 확정됨)</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    // 1. 이미 예약된 구간 (중간 날짜 포함 체크용)
    const bookedRanges = <?= $json_booked ?>;

    const packageSpecs = {
        '4H_START': ['공간 대관 Only (4시간)', '기본 조명 포함'],
        '4H_BASIC': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 1대', '기본 오디오'],
        '4H_MULTI': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 2대', '기본 오디오', '화면 전환'],
        '4H_PRO': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 3대', '오디오 콘솔', '화면 전환', '현장 PD'],
        '1D_START': ['공간 대관 Only (8시간)', '기본 조명 장비 포함', '✨ 장시간 촬영 할인'],
        '1D_BASIC': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 1대', '기본 오디오', '✨ 장시간 촬영 할인'],
        '1D_MULTI': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 2대', '기본 오디오', '✨ 장시간 촬영 할인'],
        '1D_PRO': ['공간 대관', '라이브 시스템 (TD 포함)', '카메라 3대', '오디오 콘솔', '현장 PD', '✨ 장시간 촬영 할인']
    };

    let fpStart, fpEnd;
    let currentMode = null;

    document.addEventListener("DOMContentLoaded", () => {
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power2.out", delay: 0.1 });

        <?php if($is_editable): ?>
            // 초기 패키지 상태에 따라 달력 모드 설정
            handlePackageChange();
        <?php else: ?>
            // 수정 불가능 상태면 그냥 견적만 계산
            updateEstimate();
        <?php endif; ?>
    });

    // 패키지 변경 핸들러
    function handlePackageChange() {
        const pkgSelect = document.getElementById('package_select');
        if (!pkgSelect) return;
        
        const selectedOption = pkgSelect.options[pkgSelect.selectedIndex];
        if (!selectedOption.value) return;

        const pkgType = selectedOption.getAttribute('data-type'); // 4h or 1d
        
        // 모드가 변경되었을 때만 달력 재설정
        if (pkgType !== currentMode) {
            currentMode = pkgType;
            initFlatpickr(currentMode === '4h');
        }
        updateEstimate();
    }

    // Flatpickr 초기화
    function initFlatpickr(enableTime) {
        if(fpStart) fpStart.destroy();
        if(fpEnd) fpEnd.destroy();

        const commonConfig = {
            locale: "ko", 
            minDate: "today",
            disable: bookedRanges, 
            dateFormat: enableTime ? "Y-m-d H:i" : "Y-m-d", // 1D면 시간 입력 X
            enableTime: enableTime,
            time_24hr: true,
            minTime: "09:00",
            maxTime: "20:00"
        };

        fpStart = flatpickr("#start_date_picker", {
            ...commonConfig,
            defaultDate: document.getElementById('start_date_picker').value, // 기존 값 유지
            onChange: function(selectedDates, dateStr) { 
                if (selectedDates.length > 0) {
                    // 1D 패키지면 시간 자동 완성
                    if (!enableTime) {
                        document.getElementById('start_date_picker').value = dateStr + " 09:00";
                    }
                    fpEnd.set('minDate', selectedDates[0]);
                    updateEstimate();
                }
            }
        });

        fpEnd = flatpickr("#end_date_picker", {
            ...commonConfig,
            defaultDate: document.getElementById('end_date_picker').value,
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length > 0) {
                    if (!enableTime) {
                        document.getElementById('end_date_picker').value = dateStr + " 18:00";
                    }
                    validateDateRange(); // 중복 체크
                    updateEstimate();
                }
            }
        });
    }

    // ★ 선택 구간 사이에 예약이 껴있는지 확인
    function validateDateRange() {
        const startVal = document.getElementById('start_date_picker').value;
        const endVal = document.getElementById('end_date_picker').value;

        if (!startVal || !endVal) return;

        // "YYYY-MM-DD" 형식이면 날짜 비교가 정확하지 않을 수 있어 시간 보정
        // input value에 이미 시간이 붙어있을 수도, 아닐 수도 있음.
        const userStart = new Date(startVal.includes(':') ? startVal : startVal + " 00:00");
        const userEnd = new Date(endVal.includes(':') ? endVal : endVal + " 23:59");

        if (userEnd < userStart) {
            Swal.fire({ icon: 'error', title: '날짜 선택 오류', text: '종료일은 시작일 이후여야 합니다.' });
            fpEnd.clear();
            return;
        }

        for (let range of bookedRanges) {
            const bookedStart = new Date(range.from);
            const bookedEnd = new Date(range.to);

            // 겹침/포함 여부 확인
            if (userStart < bookedEnd && userEnd > bookedStart) {
                Swal.fire({
                    icon: 'warning',
                    title: '예약 불가',
                    text: '선택하신 기간 중간에 이미 예약된 일정이 있습니다.',
                    confirmButtonText: '확인'
                });
                fpEnd.clear();
                return;
            }
        }
    }

    function updateEstimate() {
        const pkgSelect = document.getElementById('package_select');
        if(!pkgSelect) return;

        const selectedPkg = pkgSelect.value;
        const displayPackage = document.getElementById('display_package');
        const displayDate = document.getElementById('display_date');
        const displayDays = document.getElementById('display_days'); 
        const selectedOptionsList = document.getElementById('selected_options_list');
        const packageSpecsList = document.getElementById('package_specs_list'); 
        const totalPrice = document.getElementById('total_price');

        if(!totalPrice) return;

        const startVal = document.getElementById('start_date_picker').value;
        const endVal = document.getElementById('end_date_picker').value;

        updateOptionAvailability(selectedPkg);

        let pkgPrice = 0, pkgType = '4h', pkgName = "미선택";
        if(pkgSelect.selectedIndex >= 0) {
            const selectedOption = pkgSelect.options[pkgSelect.selectedIndex];
            pkgPrice = parseInt(selectedOption.getAttribute('data-price')) || 0;
            pkgType = selectedOption.getAttribute('data-type'); 
            pkgName = selectedOption.text;

            if(packageSpecs[selectedPkg]) {
                packageSpecsList.innerHTML = ""; 
                packageSpecs[selectedPkg].forEach(spec => {
                    const li = document.createElement('li');
                    li.innerText = spec;
                    if(spec.includes('✨')) li.style.color = '#FFD400'; 
                    packageSpecsList.appendChild(li);
                });
            }
        }
        displayPackage.innerText = pkgName;

        let days = 1;
        if (startVal && endVal) {
            const start = new Date(startVal.replace(/-/g, '/').split(' ')[0]);
            const end = new Date(endVal.replace(/-/g, '/').split(' ')[0]);
            
            if(pkgType === '1d') {
                const diffTime = Math.abs(end - start);
                days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
            } else {
                const diffTime = Math.abs(end - start);
                days = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                if (start.getTime() === end.getTime()) days = 1;
            }
            displayDate.innerText = startVal.split(' ')[0];
            displayDays.innerText = days + "일 (" + (pkgType==='4h' ? '4시간/일' : '종일') + ")";
        } else {
            displayDate.innerText = "-"; displayDays.innerText = "-";
        }

        let optionTotal = 0;
        selectedOptionsList.innerHTML = ""; 
        const engChecks = document.querySelectorAll('.option-chk:checked');
        engChecks.forEach(chk => {
            const price = (pkgType === '1d') ? parseInt(chk.getAttribute('data-price-1d')) : parseInt(chk.getAttribute('data-price-4h'));
            optionTotal += price || 0;
            const li = document.createElement('li'); li.innerText = `+ ${chk.getAttribute('data-name')}`; selectedOptionsList.appendChild(li);
        });
        document.querySelectorAll('.option-chk-eq:checked').forEach(chk => {
            const li = document.createElement('li'); li.innerText = `+ ${chk.value}`; selectedOptionsList.appendChild(li);
        });

        if(selectedOptionsList.innerHTML === "") selectedOptionsList.innerHTML = '<li class="text-neutral-600">- 선택 없음</li>';
        totalPrice.innerText = "₩" + ((pkgPrice + optionTotal) * days).toLocaleString();
    }

    function updateOptionAvailability(pkg) {
        const engCheckboxes = document.querySelectorAll('.option-chk');
        engCheckboxes.forEach(chk => {
            chk.disabled = false;
            chk.closest('label').querySelector('div').classList.remove('option-disabled');
        });
        if (pkg.includes('PRO')) {
            engCheckboxes.forEach(chk => {
                chk.disabled = true; chk.checked = false;
                chk.closest('label').querySelector('div').classList.add('option-disabled');
            });
        } else if (pkg.includes('BASIC') || pkg.includes('MULTI')) {
            disableOption('테크니컬 디렉터');
            disableOption('카메라 오퍼레이터');
        }
    }

    function disableOption(name) {
        const target = document.querySelector(`.option-chk[data-name="${name}"]`);
        if(target) {
            target.disabled = true; target.checked = false;
            target.closest('label').querySelector('div').classList.add('option-disabled');
        }
    }

    // 수정 저장
    function submitEdit() {
        document.getElementById('modeInput').value = 'update';
        
        // 날짜 유효성 최종 확인
        validateDateRange();
        if(!document.getElementById('end_date_picker').value) return; 

        Swal.fire({
            title: '예약 정보를 수정하시겠습니까?',
            text: "수정 내용으로 즉시 변경됩니다.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '수정하기',
            cancelButtonText: '취소',
            confirmButtonColor: '#000',
            cancelButtonColor: '#aaa'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('editForm').submit();
        });
    }

    // 예약 취소
    function cancelBooking() {
        document.getElementById('modeInput').value = 'delete';
        Swal.fire({
            title: '정말 예약을 취소하시겠습니까?',
            text: "취소 후에는 복구할 수 없습니다.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '네, 취소합니다',
            cancelButtonText: '돌아가기',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#000'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('editForm').submit();
            }
        });
    }
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>