<?php
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
$sql = "SELECT * FROM studio_equipment ORDER BY id ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $equip_list[] = $row;
    }
}

// [3] 예약된 날짜 가져오기 (시간까지 정확하게)
$booked_ranges = [];
$sql_book = "SELECT start_date, end_date FROM studio_bookings WHERE status IN ('pending', 'confirmed')";
$res_book = $conn->query($sql_book);
if ($res_book) {
    while($row = $res_book->fetch_assoc()) {
        $booked_ranges[] = [
            'from' => $row['start_date'],
            'to'   => $row['end_date']
        ];
    }
}
// 자바스크립트로 넘기기 위한 JSON 변환
$json_booked = json_encode($booked_ranges);

// [4] GET 파라미터 확인
$pre_package = isset($_GET['package']) ? $_GET['package'] : ''; 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ko.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .fade-up-init { opacity: 0; transform: translateY(30px); }

    /* 입력 필드 스타일 */
    .input-field {
        width: 100%; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; 
        padding: 1rem 1.2rem; font-family: 'Freesentation', sans-serif; font-size: 1.05rem; color: #111; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .input-field:focus {
        background-color: #ffffff; border-color: #FFD400; 
        box-shadow: 0 4px 20px rgba(255, 212, 0, 0.15); outline: none; transform: translateY(-2px);
    }
    .input-field[readonly] { cursor: pointer; background-color: #fff; }

    .input-error {
        border-color: #ef4444 !important; background-color: #fef2f2 !important;
        animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
    }
    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    .radio-label, .cursor-pointer { cursor: pointer; transition: all 0.3s; }
    .radio-input:checked + .radio-content { background-color: #000; color: #fff; border-color: #000; }
    .radio-content {
        border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 0.8rem;
        text-align: center; background: #fff; color: #6b7280;
    }
    .equip-checkbox:checked + div { background-color: #FFD400; border-color: #FFD400; color: #000; }
    .option-disabled { opacity: 0.4; pointer-events: none; filter: grayscale(100%); }

    /* Flatpickr 스타일 */
    .flatpickr-calendar { border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important; border: none; }
    .flatpickr-day.selected { background: #000 !important; border-color: #000 !important; color: #FFD400; font-weight: bold; }
    .flatpickr-time { border-top: 1px solid #eee !important; }

    .eng-price { font-family: 'URWDIN', sans-serif; font-weight: 700; color: #111; }
    .term-checkbox { width: 1.2rem; height: 1.2rem; accent-color: #000; cursor: pointer; }
    .term-link { color: #666; text-decoration: underline; cursor: pointer; font-size: 0.9rem; margin-left: 0.5rem; }
    
    /* SweetAlert 커스텀 */
    div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
        background-color: #000 !important; color: #fff !important; font-family: 'URWDIN', sans-serif !important; 
        font-weight: 700 !important; border-radius: 12px !important; padding: 12px 30px !important;
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-24 min-h-screen">

    <div class="mb-16 fade-up-init">
        <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
            STUDIO BOOKING<span class="text-[#FFD400]">.</span>
        </h1>
        <p class="font-kor text-neutral-600 mt-4 text-lg">
            원하시는 일정과 옵션을 선택하여 예약을 신청해주세요.
        </p>
    </div>

    <form id="bookingForm" action="studio_booking_ok.php" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <div class="lg:col-span-4 lg:sticky lg:top-[120px] h-fit space-y-6 fade-up-init">
            <div class="bg-white rounded-3xl p-6 border border-neutral-200">
                <h3 class="font-eng text-xl font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    SCHEDULE
                </h3>
                <div id="inline_calendar" class="w-full"></div>
                <div class="mt-4 flex items-center gap-2 text-xs text-neutral-400 justify-center">
                    <span class="w-3 h-3 bg-neutral-200 rounded-full"></span> 예약 불가
                    <span class="w-3 h-3 bg-black border border-black text-[#FFD400] rounded-full ml-2"></span> 선택 가능
                </div>
            </div>

            <div class="bg-black text-white rounded-3xl p-8 shadow-xl">
                <h3 class="font-eng text-xl font-bold mb-6 text-[#FFD400]">ESTIMATE</h3>
                
                <div class="space-y-4 font-kor text-sm text-neutral-300 mb-8 border-b border-neutral-800 pb-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span>선택 패키지</span>
                            <span id="display_package" class="text-white font-bold text-right font-eng text-lg">미선택</span>
                        </div>
                        <div class="bg-neutral-900 rounded-xl p-4 text-xs text-neutral-400 leading-relaxed">
                            <ul id="package_specs_list" class="space-y-1 list-disc list-inside">
                                <li>패키지를 선택해주세요.</li>
                            </ul>
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
                        <ul id="selected_options_list" class="text-white text-xs space-y-1 text-right">
                            <li class="text-neutral-600">- 선택 없음</li>
                        </ul>
                    </div>
                </div>

                <div class="flex justify-between items-end">
                    <span class="font-kor text-sm">예상 합계 (VAT 별도)</span>
                    <span id="total_price" class="font-eng text-3xl font-bold text-[#FFD400]">₩0</span>
                </div>
                <p class="text-xs text-neutral-500 mt-4">* 예약 기간(일수)에 비례하여 계산됩니다.</p>
            </div>
        </div>

        <div class="lg:col-span-8 space-y-12 fade-up-init">
            
            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">예약자 정보</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">예약자명 <span class="text-[#FFD400]">*</span></label>
                        <input type="text" name="client_name" class="input-field" placeholder="성함을 입력해주세요" required>
                    </div>
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">연락처 <span class="text-[#FFD400]">*</span></label>
                        <input type="text" name="client_phone" class="input-field" placeholder="010-0000-0000" required>
                    </div>
                    
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">이메일 <span class="text-[#FFD400]">*</span></label>
                        <input type="email" name="client_email" class="input-field" placeholder="sample@email.com" required>
                    </div>
                    
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">업체명 / 소속</label>
                        <input type="text" name="client_company" class="input-field" placeholder="개인이라면 '개인'으로 입력">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">예약 상세</h3>
                
                <div class="space-y-8">
                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">패키지 선택 <span class="text-[#FFD400]">*</span></label>
                        <select name="selected_package" id="package_select" class="input-field font-bold text-[#111]" required onchange="handlePackageChange()">
                            <option value="" disabled selected>패키지를 선택해주세요</option>
                            <optgroup label="4 HOURS (Half Day)">
                                <option value="4H_START" data-price="240000" data-type="4h" <?= $pre_package=='4H_START'?'selected':'' ?>>START</option>
                                <option value="4H_BASIC" data-price="700000" data-type="4h" <?= $pre_package=='4H_BASIC'?'selected':'' ?>>BASIC</option>
                                <option value="4H_MULTI" data-price="1000000" data-type="4h" <?= $pre_package=='4H_MULTI'?'selected':'' ?>>MULTI</option>
                                <option value="4H_PRO" data-price="1400000" data-type="4h" <?= $pre_package=='4H_PRO'?'selected':'' ?>>PRO</option>
                            </optgroup>
                            <optgroup label="1 DAY (8 Hours)">
                                <option value="1D_START" data-price="420000" data-type="1d" <?= $pre_package=='1D_START'?'selected':'' ?>>D-START</option>
                                <option value="1D_BASIC" data-price="900000" data-type="1d" <?= $pre_package=='1D_BASIC'?'selected':'' ?>>D-BASIC</option>
                                <option value="1D_MULTI" data-price="1300000" data-type="1d" <?= $pre_package=='1D_MULTI'?'selected':'' ?>>D-MULTI</option>
                                <option value="1D_PRO" data-price="1800000" data-type="1d" <?= $pre_package=='1D_PRO'?'selected':'' ?>>D-PRO</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">시작 일시 <span class="text-[#FFD400]">*</span></label>
                            <input type="text" id="start_date_picker" name="start_date" class="input-field cursor-pointer bg-white" placeholder="날짜 및 시간 선택" required readonly disabled>
                        </div>
                        <div>
                            <label class="block font-kor text-sm font-bold mb-2 ml-1">종료 일시 <span class="text-[#FFD400]">*</span></label>
                            <input type="text" id="end_date_picker" name="end_date" class="input-field cursor-pointer bg-white" placeholder="종료 시간 선택" required readonly disabled>
                        </div>
                    </div>
                    <p class="text-sm text-neutral-500 font-kor -mt-4 ml-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        운영 시간: 09:00 ~ 20:00 (1Day 패키지는 시간 자동 지정)
                    </p>

                    <div>
                        <label class="block font-kor text-sm font-bold mb-3 ml-1">예약 목적 <span class="text-[#FFD400]">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <?php 
                            $purposes = ['단순대관', '사진촬영', '영상촬영', '라이브행사'];
                            foreach($purposes as $idx => $p): 
                            ?>
                            <label class="radio-label">
                                <input type="radio" name="service_type" value="<?= $p ?>" class="radio-input hidden" <?= $idx==0?'checked':'' ?>>
                                <div class="radio-content font-kor font-medium"><?= $p ?></div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block font-kor text-sm font-bold mb-3 ml-1">방문 인원 <span class="text-[#FFD400]">*</span></label>
                        <select name="pax" class="input-field appearance-none" required>
                            <option value="" disabled selected>인원 수를 선택해주세요</option>
                            <option value="1인">1인</option>
                            <option value="2인">2인</option>
                            <option value="3인">3인</option>
                            <option value="4인">4인</option>
                            <option value="5인">5인</option>
                            <option value="5인 이상">5인 이상 (별도 문의)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-kor text-sm font-bold mb-2 ml-1">차량 번호</label>
                        <input type="text" name="vehicle_number" class="input-field" placeholder="예: 12가 3456 (주차 등록용)">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">엔지니어 옵션</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="엔지니어: 테크니컬 디렉터(TD)" 
                               class="equip-checkbox hidden option-chk" 
                               data-name="테크니컬 디렉터"
                               data-price-4h="250000" data-price-1d="400000" onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-5 transition-all hover:border-[#FFD400] flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start mb-2">
                                <strong class="font-kor text-lg text-neutral-900">테크니컬 디렉터 (TD)</strong>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-sm text-neutral-500 font-kor">라이브 송출, 스위칭 운용</span>
                                <span class="eng-price text-[#111]">₩400,000 (1Day)</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="엔지니어: 카메라 오퍼레이터" 
                               class="equip-checkbox hidden option-chk" 
                               data-name="카메라 오퍼레이터"
                               data-price-4h="200000" data-price-1d="320000" onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-5 transition-all hover:border-[#FFD400] flex flex-col justify-between h-full">
                             <div class="flex justify-between items-start mb-2">
                                <strong class="font-kor text-lg text-neutral-900">카메라 오퍼레이터</strong>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-sm text-neutral-500 font-kor">카메라 1대 기준</span>
                                <span class="eng-price text-[#111]">₩320,000 (1Day)</span>
                            </div>
                        </div>
                    </label>
                     <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="엔지니어: 오디오 엔지니어" 
                               class="equip-checkbox hidden option-chk" 
                               data-name="오디오 엔지니어"
                               data-price-4h="150000" data-price-1d="240000" onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-5 transition-all hover:border-[#FFD400] flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start mb-2">
                                <strong class="font-kor text-lg text-neutral-900">오디오 엔지니어</strong>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-sm text-neutral-500 font-kor">마이크셋업, 믹싱 운용</span>
                                <span class="eng-price text-[#111]">₩240,000 (1Day)</span>
                            </div>
                        </div>
                    </label>
                     <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="엔지니어: 현장 PD" 
                               class="equip-checkbox hidden option-chk" 
                               data-name="현장 PD"
                               data-price-4h="250000" data-price-1d="400000" onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-5 transition-all hover:border-[#FFD400] flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start mb-2">
                                <strong class="font-kor text-lg text-neutral-900">현장 PD</strong>
                            </div>
                            <div class="flex justify-between items-end">
                                <span class="text-sm text-neutral-500 font-kor">진행, 큐시트, 리허설 총괄</span>
                                <span class="eng-price text-[#111]">₩400,000 (1Day)</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-8 text-black">장비 선택</h3>
                <?php if (!empty($equip_list)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach($equip_list as $eq): ?>
                    <label class="cursor-pointer group select-none">
                        <input type="checkbox" name="equipment[]" value="<?= htmlspecialchars($eq['name']) ?>" class="equip-checkbox hidden option-chk-eq" onchange="updateEstimate()">
                        <div class="border border-neutral-200 rounded-xl p-4 transition-all hover:border-[#FFD400] flex items-center justify-between">
                            <span class="font-eng font-medium text-neutral-700"><?= htmlspecialchars($eq['name']) ?></span>
                            <svg class="w-5 h-5 text-neutral-300 group-hover:text-[#FFD400] transition-colors check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-neutral-400 text-sm">등록된 장비가 없습니다.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-neutral-200">
                <h3 class="font-kor text-2xl font-bold mb-6 text-black">이용약관 및 동의</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer select-none pb-4 border-b border-neutral-100">
                        <input type="checkbox" id="check_all" class="term-checkbox">
                        <span class="font-kor font-bold text-lg">전체 약관에 동의합니다.</span>
                    </label>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="term_service" class="term-checkbox required-term" required>
                            <span class="font-kor text-neutral-700">(필수) 스튜디오 이용약관 동의</span>
                        </label>
                        <span class="term-link font-kor text-xs" onclick="showTerm('service')">내용보기</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="term_privacy" class="term-checkbox required-term" required>
                            <span class="font-kor text-neutral-700">(필수) 개인정보 수집 및 이용 동의</span>
                        </label>
                        <span class="term-link font-kor text-xs" onclick="showTerm('privacy')">내용보기</span>
                    </div>
                </div>
            </div>

            <button type="button" onclick="submitBooking()" class="w-full bg-black text-white font-eng font-bold text-xl py-6 rounded-2xl hover:bg-[#FFD400] hover:text-black transition-colors shadow-xl">
                BOOK NOW
            </button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    // 1. 이미 예약된 구간 (중간 날짜 포함 체크용)
    const bookedRanges = <?= $json_booked ?>; // [{from:'2025-01-12 10:00', to:'2025-01-12 14:00'}, ...]

    // 2. 패키지 정보
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
    
    // 약관 내용
    const termsContent = {
        'service': `<div style="text-align:left; font-size:0.9rem; line-height:1.6;">...</div>`,
        'privacy': `<div style="text-align:left; font-size:0.9rem; line-height:1.6;">...</div>`
    };

    let fpStart, fpEnd, inlineCal;
    let currentMode = '4h'; // '4h' or '1d'

    document.addEventListener("DOMContentLoaded", () => {
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power2.out", delay: 0.1 });

        // 약관 전체 동의
        document.getElementById('check_all').addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.required-term').forEach(chk => chk.checked = isChecked);
        });

        // 초기 Flatpickr 설정 (기본은 시간 선택 가능)
        initFlatpickr(true);

        // 초기 패키지 상태 반영
        handlePackageChange();
    });

    // 패키지 변경 시 실행되는 함수
    function handlePackageChange() {
        const pkgSelect = document.getElementById('package_select');
        const selectedOption = pkgSelect.options[pkgSelect.selectedIndex];
        
        if (!selectedOption.value) return;

        const pkgType = selectedOption.getAttribute('data-type');
        
        // 4H -> 1D 또는 1D -> 4H로 변경될 때만 플랫피커 재설정
        if (pkgType !== currentMode) {
            currentMode = pkgType;
            // 1Day 패키지면 시간 선택 끔 (날짜만), 4H 패키지면 시간 선택 켬
            initFlatpickr(currentMode === '4h');
        }

        updateEstimate();
    }

    // Flatpickr 초기화/재설정 함수
    function initFlatpickr(enableTime) {
        // 기존 인스턴스가 있다면 제거
        if(fpStart) fpStart.destroy();
        if(fpEnd) fpEnd.destroy();
        if(inlineCal) inlineCal.destroy();

        // 공통 설정
        const commonConfig = {
            locale: "ko", 
            minDate: "today",
            disable: bookedRanges, // 예약된 날짜 비활성화
            dateFormat: enableTime ? "Y-m-d H:i" : "Y-m-d", // 시간 포함 여부
            enableTime: enableTime,
            time_24hr: true,
            minTime: "09:00",
            maxTime: "20:00"
        };

        // 1. 시작일 피커
        fpStart = flatpickr("#start_date_picker", {
            ...commonConfig,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const startDate = selectedDates[0];
                    
                    // 1Day 패키지일 경우 자동 시간 설정 (09:00 ~ 18:00)
                    if (!enableTime) {
                        // dateStr에는 시간 정보가 없으므로 09:00 추가해서 화면엔 표시 가능하나,
                        // 실제 input 값에는 "YYYY-MM-DD"만 들어감.
                        // 하지만 DB에는 시간이 필요하므로 hidden input이나 submit 시 처리가 필요함.
                        // 여기서는 편의상 input value에 강제로 시간 추가
                        document.getElementById('start_date_picker').value = dateStr + " 09:00";
                    }

                    // 종료일 최소 날짜 설정
                    fpEnd.set('minDate', startDate);
                    
                    // 인라인 달력 동기화
                    inlineCal.setDate(startDate);
                }
                updateEstimate();
            }
        });

        // 2. 종료일 피커
        fpEnd = flatpickr("#end_date_picker", {
            ...commonConfig,
            onChange: function(selectedDates, dateStr, instance) {
                if(selectedDates.length > 0) {
                     // 1Day 패키지일 경우 자동 시간 설정 (18:00)
                    if (!enableTime) {
                        document.getElementById('end_date_picker').value = dateStr + " 18:00";
                    }
                    validateDateRange(); // 중간에 예약 낀거 체크
                    updateEstimate();
                }
            }
        });

        // 3. 인라인 달력 (단순 조회용)
        inlineCal = flatpickr("#inline_calendar", {
            inline: true, 
            locale: "ko", 
            minDate: "today", 
            disable: bookedRanges,
            onChange: function(selectedDates, dateStr) {
                fpStart.setDate(selectedDates[0]);
                // 날짜 클릭 시 자동으로 시작일 선택 효과
                if(!enableTime) {
                    document.getElementById('start_date_picker').value = dateStr + " 09:00";
                }
                fpStart.open(); // 시작일 피커 열기 (시간 선택 유도)
            }
        });
        
        // 입력 필드 활성화
        document.getElementById('start_date_picker').disabled = false;
        document.getElementById('end_date_picker').disabled = false;
    }

    // ★ 핵심 로직: 선택한 기간 사이에 예약된 날짜가 끼어있는지 검사
    function validateDateRange() {
        const startVal = document.getElementById('start_date_picker').value;
        const endVal = document.getElementById('end_date_picker').value;

        if (!startVal || !endVal) return;

        const userStart = new Date(startVal);
        const userEnd = new Date(endVal);

        // 유효성 체크 1: 종료일이 시작일보다 앞서면 안됨
        if (userEnd < userStart) {
            Swal.fire({ icon: 'error', title: '날짜 선택 오류', text: '종료일은 시작일 이후여야 합니다.' });
            fpEnd.clear();
            return;
        }

        // 유효성 체크 2: 선택 구간 사이에 이미 예약된 날짜가 있는지 확인
        for (let range of bookedRanges) {
            const bookedStart = new Date(range.from);
            const bookedEnd = new Date(range.to);

            // 사용자가 선택한 기간이 예약된 기간을 "포함"하거나 "겹치는지" 확인
            // (UserStart <= BookedEnd) AND (UserEnd >= BookedStart) -> 겹침
            if (userStart < bookedEnd && userEnd > bookedStart) {
                Swal.fire({
                    icon: 'warning',
                    title: '예약 불가',
                    text: '선택하신 기간 중간에 이미 예약된 일정이 포함되어 있습니다. 다른 일정을 선택해주세요.',
                    confirmButtonText: '확인'
                });
                fpEnd.clear(); // 종료일 초기화
                return;
            }
        }
    }

    // 약관 보기 모달
    function showTerm(type) {
        let title = type === 'service' ? '스튜디오 이용약관' : '개인정보 수집 및 이용 동의';
        Swal.fire({
            title: title,
            html: termsContent[type],
            confirmButtonText: '확인',
            width: '600px'
        });
    }

    // 견적 계산 및 표시
    function updateEstimate() {
        const pkgSelect = document.getElementById('package_select');
        const selectedPkg = pkgSelect.value;
        
        // ... (이하 기존 updateEstimate 로직과 동일) ...
        // (코드 길이상 생략된 부분은 기존 코드 그대로 유지하시면 됩니다. 
        //  단, 날짜 계산 시 '1D' 패키지는 09:00, 18:00 강제 설정된 값을 기준으로 계산됩니다.)
        
        const displayPackage = document.getElementById('display_package');
        const displayDate = document.getElementById('display_date');
        const displayDays = document.getElementById('display_days'); 
        const selectedOptionsList = document.getElementById('selected_options_list');
        const packageSpecsList = document.getElementById('package_specs_list'); 
        const totalPrice = document.getElementById('total_price');

        const startVal = document.getElementById('start_date_picker').value;
        const endVal = document.getElementById('end_date_picker').value;

        updateOptionAvailability(selectedPkg);

        let pkgPrice = 0, pkgType = '4h', pkgName = "미선택";

        if(pkgSelect.selectedIndex > 0) {
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
        } else {
            displayPackage.innerText = "미선택";
            packageSpecsList.innerHTML = "<li>패키지를 선택해주세요.</li>";
        }
        displayPackage.innerText = pkgName;

        let days = 1;
        if (startVal && endVal) {
            const start = new Date(startVal.replace(/-/g, '/')); // 크로스브라우징 안전
            const end = new Date(endVal.replace(/-/g, '/'));
            
            // 1Day 패키지는 날짜 차이 + 1일
            if(pkgType === '1d') {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                // 1일차 09:00 ~ 1일차 18:00 이면 diff는 0.xxx일 -> 1일로 침
                // 1일차 09:00 ~ 2일차 18:00 이면 diff는 1.xxx일 -> 2일로 침
                days = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                // 같은 날짜여도 18시-09시 = 9시간 차이라 1일 미만이 나올 수 있음 보정
                if (start.getDate() === end.getDate()) days = 1;
            } else {
                // 4H 패키지는 일단위 계산 (여기서는 단순하게 일수만 계산)
                const diffTime = Math.abs(end - start);
                days = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                if (start.getDate() === end.getDate()) days = 1;
            }

            displayDate.innerText = startVal.split(' ')[0];
            displayDays.innerText = days + "일 (" + (pkgType==='4h' ? '4시간/일' : '종일') + ")";
        } else if(startVal) {
            displayDate.innerText = startVal.split(' ')[0];
            displayDays.innerText = "1일 (예상)";
        } else {
            displayDate.innerText = "-"; displayDays.innerText = "-";
        }

        let optionTotal = 0;
        selectedOptionsList.innerHTML = ""; 
        const engChecks = document.querySelectorAll('.option-chk:checked');
        engChecks.forEach(chk => {
            const name = chk.getAttribute('data-name');
            const price4h = parseInt(chk.getAttribute('data-price-4h')) || 0;
            const price1d = parseInt(chk.getAttribute('data-price-1d')) || 0;
            let currentPrice = (pkgType === '1d') ? price1d : price4h;
            optionTotal += currentPrice;
            const li = document.createElement('li'); li.innerText = `+ ${name}`; selectedOptionsList.appendChild(li);
        });
        const equipChecks = document.querySelectorAll('.option-chk-eq:checked');
        equipChecks.forEach(chk => {
            const li = document.createElement('li'); li.innerText = `+ ${chk.value}`; selectedOptionsList.appendChild(li);
        });

        if(engChecks.length === 0 && equipChecks.length === 0) selectedOptionsList.innerHTML = '<li class="text-neutral-600">- 선택 없음</li>';
        
        const grandTotal = (pkgPrice + optionTotal) * days;
        totalPrice.innerText = "₩" + grandTotal.toLocaleString();
    }

    // 옵션 활성/비활성 (기존 로직 유지)
    function updateOptionAvailability(pkg) {
        const engCheckboxes = document.querySelectorAll('.option-chk');
        engCheckboxes.forEach(chk => {
            chk.disabled = false;
            chk.closest('label').querySelector('div').classList.remove('option-disabled');
        });
        if (pkg.includes('BASIC') || pkg.includes('MULTI')) {
            disableOption('테크니컬 디렉터');
            disableOption('카메라 오퍼레이터');
        } else if (pkg.includes('PRO')) {
            engCheckboxes.forEach(chk => {
                chk.disabled = true; chk.checked = false; 
                chk.closest('label').querySelector('div').classList.add('option-disabled');
            });
        }
    }
    function disableOption(nameKey) {
        const target = document.querySelector(`.option-chk[data-name="${nameKey}"]`);
        if(target) {
            target.disabled = true; target.checked = false; 
            target.closest('label').querySelector('div').classList.add('option-disabled');
        }
    }

    function submitBooking() {
        const form = document.getElementById('bookingForm');
        let isValid = true;
        let firstInvalid = null;

        // 필수값 체크
        form.querySelectorAll('input[required], select[required]').forEach(el => {
            if (!el.value.trim()) {
                isValid = false;
                el.classList.add('input-error');
                if(!firstInvalid) firstInvalid = el;
            }
        });

        // 약관 동의 체크
        let termsChecked = true;
        form.querySelectorAll('.required-term').forEach(term => {
            if(!term.checked) termsChecked = false;
        });

        if(isValid && termsChecked) {
            // 마지막으로 날짜 범위 재검증
            validateDateRange();
            if(!document.getElementById('end_date_picker').value) return; // 검증 실패시 중단

            Swal.fire({
                title: '예약을 신청하시겠습니까?',
                html: '<span style="color:#666; font-size:1.1rem;">담당자가 확인 후<br><strong>확정 연락</strong>을 드립니다.</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '신청하기',
                cancelButtonText: '취소'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        } else {
            if(!termsChecked) {
                Swal.fire({ icon: 'warning', title: '약관 동의 필요', text: '필수 이용약관에 동의해주세요.' });
            } else if(firstInvalid) {
                firstInvalid.focus();
            }
        }
    }
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>