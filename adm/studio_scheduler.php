<?php
// 에러 확인용
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';

// ------------------------------------------------------------------------
// [AJAX] 데이터 처리 영역
// ------------------------------------------------------------------------

// 1. 통계 데이터 가져오기
if (isset($_GET['mode']) && $_GET['mode'] == 'get_stats') {
    header('Content-Type: application/json');
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM studio_bookings")->fetchColumn(),
        'pending' => $pdo->query("SELECT COUNT(*) FROM studio_bookings WHERE status = 'pending'")->fetchColumn(),
        'confirmed' => $pdo->query("SELECT COUNT(*) FROM studio_bookings WHERE status = 'confirmed'")->fetchColumn(),
        'completed' => $pdo->query("SELECT COUNT(*) FROM studio_bookings WHERE status = 'completed'")->fetchColumn(),
        'canceled' => $pdo->query("SELECT COUNT(*) FROM studio_bookings WHERE status = 'canceled'")->fetchColumn(),
    ];
    echo json_encode($stats);
    exit;
}

// 2. 캘린더 데이터 가져오기
if (isset($_GET['mode']) && $_GET['mode'] == 'fetch') {
    header('Content-Type: application/json');
    
    $sql = "SELECT * FROM studio_bookings ORDER BY start_date ASC";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rows as $row) {
        $color = '#fbbf24'; // 대기 (Yellow)
        $textColor = '#713f12';
        $borderColor = '#fbbf24';

        if ($row['status'] == 'confirmed') { 
            $color = '#3b82f6'; $textColor = '#ffffff'; $borderColor = '#2563eb'; 
        } 
        if ($row['status'] == 'completed') { 
            $color = '#9ca3af'; $textColor = '#ffffff'; $borderColor = '#6b7280';
        } 
        if ($row['status'] == 'canceled') { 
            $color = '#ef4444'; $textColor = '#ffffff'; $borderColor = '#dc2626';
        } 

        $displayTitle = $row['client_name'];

        $events[] = [
            'id' => $row['id'],
            'title' => $displayTitle,
            'start' => $row['start_date'],
            'end' => $row['end_date'],
            'backgroundColor' => $color,
            'borderColor' => $borderColor,
            'textColor' => $textColor,
            'extendedProps' => $row 
        ];
    }
    echo json_encode($events);
    exit;
}

// 3. 장비 목록 가져오기
if (isset($_GET['mode']) && $_GET['mode'] == 'get_equip') {
    header('Content-Type: application/json');
    $rows = $pdo->query("SELECT * FROM studio_equipment ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
    exit;
}

// 4. 장비 추가/삭제
if (isset($_POST['mode']) && $_POST['mode'] == 'add_equip') {
    $pdo->prepare("INSERT INTO studio_equipment (name) VALUES (?)")->execute([$_POST['name']]);
    echo "OK"; exit;
}
if (isset($_POST['mode']) && $_POST['mode'] == 'del_equip') {
    $pdo->prepare("DELETE FROM studio_equipment WHERE id = ?")->execute([$_POST['id']]);
    echo "OK"; exit;
}

// 5. 예약 저장 (신규/수정)
if (isset($_POST['mode']) && $_POST['mode'] == 'save') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    $client_name    = $_POST['client_name'] ?? '';
    $client_phone   = $_POST['client_phone'] ?? '';
    $client_email   = $_POST['client_email'] ?? ''; // 이메일 처리 확인
    $client_company = $_POST['client_company'] ?? '';
    $service_type   = $_POST['service_type'] ?? '';
    $pax            = isset($_POST['pax']) ? (int)$_POST['pax'] : 1;
    $vehicle_number = $_POST['vehicle_number'] ?? '';
    $start_date     = $_POST['start_date'] ?? '';
    $end_date       = $_POST['end_date'] ?? '';
    $admin_memo     = $_POST['admin_memo'] ?? '';
    $status         = $_POST['status'] ?? 'pending';
    
    $equipment = isset($_POST['equipment']) ? implode(',', $_POST['equipment']) : '';

    if ($id > 0) {
        $sql = "UPDATE studio_bookings SET client_name=?, client_phone=?, client_email=?, client_company=?, service_type=?, pax=?, vehicle_number=?, start_date=?, end_date=?, equipment=?, admin_memo=?, status=? WHERE id=?";
        $pdo->prepare($sql)->execute([$client_name, $client_phone, $client_email, $client_company, $service_type, $pax, $vehicle_number, $start_date, $end_date, $equipment, $admin_memo, $status, $id]);
    } else {
        $sql = "INSERT INTO studio_bookings (client_name, client_phone, client_email, client_company, service_type, pax, vehicle_number, start_date, end_date, equipment, admin_memo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$client_name, $client_phone, $client_email, $client_company, $service_type, $pax, $vehicle_number, $start_date, $end_date, $equipment, $admin_memo, $status]);
    }
    echo "OK"; exit;
}

// ★ 6. 예약 삭제 (DB 삭제) 추가
if (isset($_POST['mode']) && $_POST['mode'] == 'delete') {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM studio_bookings WHERE id = ?")->execute([$id]);
        echo "OK";
    }
    exit;
}

// 7. 드래그 앤 드롭 날짜 업데이트
if (isset($_POST['mode']) && $_POST['mode'] == 'update_date') {
    $id = (int)$_POST['id'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $pdo->prepare("UPDATE studio_bookings SET start_date = ?, end_date = ? WHERE id = ?")->execute([$start, $end, $id]);
    echo "OK"; exit;
}

require_once '../inc/admin_header.php';
?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<style>
    .fc-event { border-radius: 6px !important; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border: none !important; padding: 0; }
    .fc-event-main { padding: 0; }
    .input-field:focus { box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05); }
</style>

<div class="max-w-7xl mx-auto pb-20 h-full flex flex-col">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Studio Schedule</h1>
            <p class="text-sm text-gray-500 mt-1">스튜디오 예약 관리 및 일정 확인</p>
        </div>
        <div class="flex gap-2">
             <button type="button" onclick="openEquipModal()" class="px-4 py-2 text-sm font-bold border border-gray-300 rounded-lg hover:bg-gray-50 bg-white text-gray-700 flex items-center transition shadow-sm">
                <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i> 장비 관리
            </button>
            <div class="bg-gray-100 p-1 rounded-lg flex items-center">
                <button type="button" onclick="switchView('calendar')" id="btn-calendar" class="px-4 py-2 text-sm font-medium rounded-md shadow-sm bg-white text-gray-900 transition-all">달력</button>
                <button type="button" onclick="switchView('list')" id="btn-list" class="px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all">리스트</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">총 예약</p>
            <p class="text-3xl font-extrabold text-gray-900" id="stat-total">-</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-100 shadow-sm">
            <p class="text-xs font-bold text-yellow-700 mb-2 uppercase tracking-wide">대기 (Pending)</p>
            <p class="text-3xl font-extrabold text-yellow-800" id="stat-pending">-</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 shadow-sm">
            <p class="text-xs font-bold text-blue-700 mb-2 uppercase tracking-wide">확정 (Confirmed)</p>
            <p class="text-3xl font-extrabold text-blue-800" id="stat-confirmed">-</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 shadow-sm">
            <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">완료 (Completed)</p>
            <p class="text-3xl font-extrabold text-gray-700" id="stat-completed">-</p>
        </div>
        <div class="bg-red-50 rounded-xl p-6 border border-red-100 shadow-sm">
            <p class="text-xs font-bold text-red-700 mb-2 uppercase tracking-wide">취소 (Canceled)</p>
            <p class="text-3xl font-extrabold text-red-800" id="stat-canceled">-</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[700px] flex flex-col">
        <div id="view-calendar" class="p-6 h-full">
            <div id='calendar'></div>
        </div>
        <div id="view-list" class="hidden w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">예약자 정보</th>
                        <th class="px-6 py-4">날짜</th>
                        <th class="px-6 py-4">시간</th>
                        <th class="px-6 py-4">목적</th>
                        <th class="px-6 py-4">인원</th>
                        <th class="px-6 py-4">상태</th>
                        <th class="px-6 py-4 text-right">관리</th>
                    </tr>
                </thead>
                <tbody id="list-table-body" class="divide-y divide-gray-50 text-sm text-gray-600 bg-white"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="bookingModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="absolute top-0 right-0 h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-0">
        
        <div class="px-8 py-6 border-b border-gray-100 flex items-start justify-between bg-white shrink-0">
            <div>
                <h2 class="text-2xl font-bold text-gray-900" id="modal-title">예약 상세 정보</h2>
                <div class="mt-3" id="modal-status-badge"></div>
            </div>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-black transition p-2 hover:bg-gray-100 rounded-full"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>

        <form id="bookingForm" class="flex-1 overflow-y-auto px-8 py-8 space-y-10">
            <input type="hidden" name="id" id="input-id">
            
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5 flex items-center">
                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> 예약자 정보
                </h3>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">예약자명</label>
                        <input type="text" name="client_name" id="input-client_name" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none placeholder-gray-400" 
                               placeholder="홍길동">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">연락처</label>
                        <input type="text" name="client_phone" id="input-client_phone" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none placeholder-gray-400" 
                               placeholder="010-0000-0000">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">이메일</label>
                        <input type="email" name="client_email" id="input-client_email" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none placeholder-gray-400" 
                               placeholder="example@email.com">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">업체명 / 소속</label>
                        <input type="text" name="client_company" id="input-client_company" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none placeholder-gray-400" 
                               placeholder="회사명 또는 소속 (선택)">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5 flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> 예약 내용
                </h3>
                <div class="grid grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">시작 일시</label>
                        <input type="datetime-local" name="start_date" id="input-start_date" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">종료 일시</label>
                        <input type="datetime-local" name="end_date" id="input-end_date" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">인원 수</label>
                        <div class="relative">
                            <input type="number" name="pax" id="input-pax" 
                                   class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none" value="1">
                            <span class="absolute right-4 top-3.5 text-sm text-gray-400 font-medium">명</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">예약 목적</label>
                        <div class="relative">
                            <select name="service_type" id="input-service_type" 
                                    class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none appearance-none">
                                <option value="단순 대관">단순 대관</option>
                                <option value="사진 촬영">사진 촬영</option>
                                <option value="영상 촬영">영상 촬영</option>
                                <option value="라이브/행사">라이브/행사</option>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-4 top-3.5 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">차량 번호</label>
                        <input type="text" name="vehicle_number" id="input-vehicle_number" 
                               class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none placeholder-gray-400" 
                               placeholder="예: 12가 3456 (주차 등록용)">
                    </div>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-end mb-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                        <i data-lucide="camera" class="w-4 h-4 mr-2"></i> 사용 장비
                    </h3>
                    <button type="button" onclick="openEquipModal()" class="text-xs text-blue-600 hover:text-blue-800 font-bold hover:underline">목록 편집</button>
                </div>
                <div id="equipment-list-container" class="grid grid-cols-2 gap-3 bg-gray-50 rounded-xl p-5 border border-gray-100">
                    </div>
            </div>

            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center">
                    <i data-lucide="sticky-note" class="w-4 h-4 mr-2"></i> 관리자 메모
                </h3>
                <textarea name="admin_memo" id="input-admin_memo" rows="3" 
                          class="input-field w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:border-black focus:ring-1 focus:ring-black transition-all outline-none resize-none placeholder-gray-400 leading-relaxed" 
                          placeholder="내부 공유용 메모를 입력하세요..."></textarea>
            </div>

            <input type="hidden" name="status" id="input-status" value="pending">
        </form>

        <div class="px-8 py-6 border-t border-gray-100 bg-white shrink-0">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">상태 변경</h3>
            <div class="grid grid-cols-4 gap-3 mb-6">
                <button type="button" onclick="updateStatus('pending')" id="btn-status-pending" class="py-3 rounded-xl border text-xs font-bold transition">대기</button>
                <button type="button" onclick="updateStatus('confirmed')" id="btn-status-confirmed" class="py-3 rounded-xl border text-xs font-bold transition">확정</button>
                <button type="button" onclick="updateStatus('completed')" id="btn-status-completed" class="py-3 rounded-xl border text-xs font-bold transition">완료</button>
                <button type="button" onclick="updateStatus('canceled')" id="btn-status-canceled" class="py-3 rounded-xl border text-xs font-bold transition">취소</button>
            </div>
            
            <div class="flex justify-between items-center mt-6">
                 <button type="button" onclick="deleteBooking()" id="btn-delete" class="px-4 py-3.5 text-red-500 hover:bg-red-50 rounded-xl text-sm font-bold transition flex items-center">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> 삭제
                 </button>

                 <div class="flex gap-3">
                     <button type="button" onclick="closeModal()" class="px-6 py-3.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition">닫기</button>
                     <button type="button" onclick="saveBooking()" class="px-8 py-3.5 bg-black text-white rounded-xl text-sm font-bold shadow-lg hover:bg-gray-800 flex items-center transition transform active:scale-95">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i> 저장하기
                     </button>
                 </div>
            </div>
        </div>
    </div>
</div>

<div id="equipModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('equipModal').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-xl p-8 w-[400px]">
        <h3 class="text-lg font-bold text-gray-900 mb-6">장비 목록 편집</h3>
        <div class="flex gap-2 mb-6">
            <input type="text" id="new-equip-name" class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-black focus:ring-0 outline-none" placeholder="새 장비 이름">
            <button type="button" onclick="addEquipment()" class="px-4 py-2.5 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800">추가</button>
        </div>
        <ul id="manage-equip-list" class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar"></ul>
        <div class="mt-6 text-right">
             <button type="button" onclick="document.getElementById('equipModal').classList.add('hidden'); loadEquipmentCheckboxes();" class="text-sm font-bold text-gray-500 hover:text-black">닫기</button>
        </div>
    </div>
</div>

</main>

<script>
    lucide.createIcons();
    let calendar;
    let allEvents = [];
    let equipmentList = [];

    document.addEventListener('DOMContentLoaded', function() {
        loadStats();
        initCalendar();
        loadEquipmentCheckboxes(); 
        loadData();
    });

    // ... (기존 loadStats, loadData, loadEquipmentCheckboxes 함수들 유지) ...
    function loadStats() {
        fetch('studio_scheduler.php?mode=get_stats')
            .then(res => res.json())
            .then(data => {
                document.getElementById('stat-total').innerText = data.total;
                document.getElementById('stat-pending').innerText = data.pending;
                document.getElementById('stat-confirmed').innerText = data.confirmed;
                document.getElementById('stat-completed').innerText = data.completed;
                document.getElementById('stat-canceled').innerText = data.canceled;
            });
    }

    function loadData() {
        fetch('studio_scheduler.php?mode=fetch')
            .then(res => res.json())
            .then(events => {
                allEvents = events;
                calendar.removeAllEvents();
                calendar.addEventSource(events);
                renderList(events);
            });
    }

    function loadEquipmentCheckboxes() {
        fetch('studio_scheduler.php?mode=get_equip')
            .then(res => res.json())
            .then(list => {
                equipmentList = list;
                const container = document.getElementById('equipment-list-container');
                container.innerHTML = '';
                
                list.forEach(item => {
                    const div = document.createElement('label');
                    div.className = 'flex items-center space-x-3 cursor-pointer group';
                    div.innerHTML = `
                        <input type="checkbox" name="equipment[]" value="${item.name}" class="rounded text-black focus:ring-black border-gray-300 w-4 h-4 cursor-pointer">
                        <span class="text-sm text-gray-700 font-medium group-hover:text-black transition-colors">${item.name}</span>
                    `;
                    container.appendChild(div);
                });

                const manageList = document.getElementById('manage-equip-list');
                manageList.innerHTML = '';
                list.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100 group hover:border-gray-200 transition-colors';
                    li.innerHTML = `
                        <span class="text-sm font-medium text-gray-700">${item.name}</span>
                        <button type="button" onclick="deleteEquipment(${item.id})" class="text-gray-400 hover:text-red-500 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    `;
                    manageList.appendChild(li);
                });
                lucide.createIcons();
            });
    }

    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            height: '100%',
            editable: true,
            droppable: true,
            dayMaxEvents: true,
            eventDisplay: 'block',
            eventContent: function(arg) {
                let data = arg.event.extendedProps;
                let title = arg.event.title;
                let pax = data.pax || 0;
                let type = data.service_type || '';
                let iconName = 'calendar'; 
                if(type.includes('사진')) iconName = 'camera';
                else if(type.includes('영상')) iconName = 'video';
                else if(type.includes('라이브')) iconName = 'radio';
                else if(type.includes('대관')) iconName = 'key';

                let html = `
                    <div class="flex items-center justify-between w-full overflow-hidden px-2 py-1.5 gap-2">
                        <div class="flex items-center gap-1.5 overflow-hidden">
                            <i data-lucide="${iconName}" class="w-4 h-4 flex-shrink-0"></i>
                            <span class="truncate font-bold text-xs leading-tight">${title}</span>
                        </div>
                `;
                if (pax > 0) {
                    html += `<span class="flex-shrink-0 flex items-center justify-center w-5 h-5 bg-white/30 rounded-full text-[10px] font-bold" title="${pax}명">${pax}</span>`;
                }
                html += `</div>`;
                return { html: html };
            },
            eventDidMount: function(info) { lucide.createIcons({ root: info.el }); },
            eventClick: function(info) { openModal(info.event.extendedProps); },
            dateClick: function(info) { openModal(null, info.dateStr); },
            eventDrop: function(info) {
                if(!confirm(`'${info.event.title}' 일정을 변경하시겠습니까?`)) { info.revert(); return; }
                updateEventDate(info.event);
            }
        });
        calendar.render();
    }

    function updateEventDate(event) {
        const formData = new FormData();
        formData.append('mode', 'update_date');
        formData.append('id', event.id);
        const toLocal = (d) => {
            const offset = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - offset).toISOString().slice(0, 16);
        };
        formData.append('start', toLocal(event.start));
        formData.append('end', toLocal(event.end || event.start));
        fetch('studio_scheduler.php', { method: 'POST', body: formData }).then(res => res.text()).then(res => {
            if(res.trim() !== 'OK') { alert('일정 변경 실패'); info.revert(); } else { renderList(allEvents); }
        });
    }

    function openEquipModal() { document.getElementById('equipModal').classList.remove('hidden'); }
    function addEquipment() {
        const name = document.getElementById('new-equip-name').value;
        if(!name) return alert('장비 이름을 입력하세요.');
        const fd = new FormData(); fd.append('mode', 'add_equip'); fd.append('name', name);
        fetch('studio_scheduler.php', { method: 'POST', body: fd }).then(() => { document.getElementById('new-equip-name').value = ''; loadEquipmentCheckboxes(); });
    }
    function deleteEquipment(id) {
        if(!confirm('정말 삭제하시겠습니까?')) return;
        const fd = new FormData(); fd.append('mode', 'del_equip'); fd.append('id', id);
        fetch('studio_scheduler.php', { method: 'POST', body: fd }).then(() => { loadEquipmentCheckboxes(); });
    }
    function openModalById(id) { const event = allEvents.find(e => e.id == id); if(event) openModal(event.extendedProps); }
    function closeModal() { document.getElementById('bookingModal').classList.add('hidden'); }

    function openModal(data = null, dateStr = null) {
        document.getElementById('bookingModal').classList.remove('hidden');
        document.getElementById('bookingForm').reset();
        
        if (data) {
            document.getElementById('modal-title').innerText = `예약 상세 정보 #${data.id}`;
            document.getElementById('input-id').value = data.id;
            document.getElementById('input-client_name').value = data.client_name;
            document.getElementById('input-client_phone').value = data.client_phone;
            document.getElementById('input-client_email').value = data.client_email; // ★ 추가됨
            document.getElementById('input-client_company').value = data.client_company;
            document.getElementById('input-pax').value = data.pax;
            document.getElementById('input-service_type').value = data.service_type;
            document.getElementById('input-vehicle_number').value = data.vehicle_number;
            document.getElementById('input-admin_memo').value = data.admin_memo;
            
            document.getElementById('input-start_date').value = data.start_date.replace(' ', 'T');
            document.getElementById('input-end_date').value = data.end_date.replace(' ', 'T');

            const equipArr = data.equipment ? data.equipment.split(',') : [];
            document.querySelectorAll('input[name="equipment[]"]').forEach(cb => {
                cb.checked = equipArr.includes(cb.value);
            });

            updateStatusUI(data.status);
            document.getElementById('btn-delete').classList.remove('hidden'); // 삭제 버튼 보이기
        } else {
            document.getElementById('modal-title').innerText = "새 예약 등록";
            document.getElementById('input-id').value = 0;
            if(dateStr) {
                document.getElementById('input-start_date').value = dateStr + 'T10:00';
                document.getElementById('input-end_date').value = dateStr + 'T14:00';
            }
            updateStatusUI('pending');
            document.getElementById('btn-delete').classList.add('hidden'); // 새 예약일 땐 삭제 버튼 숨기기
        }
    }

    // ★ 예약 삭제 함수 추가
    function deleteBooking() {
        const id = document.getElementById('input-id').value;
        if (!id || id == 0) return;
        
        if (!confirm('정말 이 예약 기록을 영구 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

        const formData = new FormData();
        formData.append('mode', 'delete');
        formData.append('id', id);

        fetch('studio_scheduler.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === 'OK') {
                closeModal();
                loadStats();
                loadData();
            } else {
                alert('Error: ' + res);
            }
        });
    }

    function updateStatusUI(status) {
        document.getElementById('input-status').value = status;
        const btns = ['pending', 'confirmed', 'canceled', 'completed'];
        btns.forEach(s => {
            const btn = document.getElementById(`btn-status-${s}`);
            btn.className = 'py-3 rounded-xl border border-gray-200 text-gray-400 hover:bg-gray-50 text-xs font-bold transition';
        });
        const activeBtn = document.getElementById(`btn-status-${status}`);
        if(status === 'pending') activeBtn.className = 'py-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-xs font-bold transition shadow-sm ring-1 ring-yellow-200';
        if(status === 'confirmed') activeBtn.className = 'py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold transition shadow-sm ring-1 ring-blue-200';
        if(status === 'canceled') activeBtn.className = 'py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold transition shadow-sm ring-1 ring-red-200';
        if(status === 'completed') activeBtn.className = 'py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-600 text-xs font-bold transition shadow-sm ring-1 ring-gray-300';

        const container = document.getElementById('modal-status-badge');
        let badgeHtml = '';
        if(status === 'pending') badgeHtml = '<span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-bold border border-yellow-200">대기중</span>';
        if(status === 'confirmed') badgeHtml = '<span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold border border-blue-200">확정됨</span>';
        if(status === 'canceled') badgeHtml = '<span class="px-2.5 py-1 rounded-full bg-red-100 text-red-800 text-xs font-bold border border-red-200">취소됨</span>';
        if(status === 'completed') badgeHtml = '<span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold border border-gray-200">완료됨</span>';
        container.innerHTML = badgeHtml;
    }

    function updateStatus(status) { updateStatusUI(status); }

    function saveBooking() {
        const formData = new FormData(document.getElementById('bookingForm'));
        formData.append('mode', 'save');
        fetch('studio_scheduler.php', { method: 'POST', body: formData }).then(res => res.text()).then(res => {
            if (res.trim() === 'OK') { closeModal(); loadStats(); loadData(); } else { alert('Error: ' + res); }
        });
    }

    function renderList(events) {
        const tbody = document.getElementById('list-table-body');
        tbody.innerHTML = '';
        events.forEach(evt => {
            const data = evt.extendedProps;
            const d = new Date(data.start_date);
            const dateStr = d.toLocaleDateString('ko-KR', { month: 'long', day: 'numeric', weekday: 'short' });
            const timeStr = d.toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit', hour12: false }) + ' ~ ' + new Date(data.end_date).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit', hour12: false });
            let statusBadge = '';
            if(data.status === 'pending') statusBadge = '<span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">대기</span>';
            if(data.status === 'confirmed') statusBadge = '<span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">확정</span>';
            if(data.status === 'canceled') statusBadge = '<span class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">취소</span>';
            if(data.status === 'completed') statusBadge = '<span class="px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">완료</span>';

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition border-b border-gray-50 group';
            tr.innerHTML = `
                <td class="px-6 py-4 font-mono text-xs text-gray-400 group-hover:text-gray-600">#${data.id}</td>
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-900">${data.client_name}</div>
                    <div class="text-xs text-gray-400 group-hover:text-gray-500">${data.client_company || '-'}</div>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-700">${dateStr}</td>
                <td class="px-6 py-4 font-mono text-xs text-gray-500">${timeStr}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${data.service_type}</td>
                <td class="px-6 py-4 text-sm text-gray-700"><span class="bg-gray-100 px-2 py-0.5 rounded text-xs font-bold">${data.pax}명</span></td>
                <td class="px-6 py-4">${statusBadge}</td>
                <td class="px-6 py-4 text-right">
                    <button type="button" onclick='openModalById(${data.id})' class="text-gray-400 hover:text-black transition p-1.5 hover:bg-gray-100 rounded-lg"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        lucide.createIcons();
    }

    function switchView(view) {
        const calView = document.getElementById('view-calendar');
        const listView = document.getElementById('view-list');
        const btnCal = document.getElementById('btn-calendar');
        const btnList = document.getElementById('btn-list');
        if(view === 'calendar') {
            calView.classList.remove('hidden'); listView.classList.add('hidden');
            btnCal.className = 'px-4 py-2 text-sm font-medium rounded-md shadow-sm bg-white text-gray-900 transition-all';
            btnList.className = 'px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all';
            calendar.updateSize();
        } else {
            calView.classList.add('hidden'); listView.classList.remove('hidden');
            btnList.className = 'px-4 py-2 text-sm font-medium rounded-md shadow-sm bg-white text-gray-900 transition-all';
            btnCal.className = 'px-4 py-2 text-sm font-medium rounded-md text-gray-500 hover:text-gray-900 transition-all';
        }
    }
</script>