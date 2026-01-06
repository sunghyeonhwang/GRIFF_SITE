<?php
// [1] 에러 리포팅
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';

// =================================================================
// [A] 상태 변경 로직 (SMS + Slack 추가됨)
// =================================================================
if (isset($_POST['mode']) && $_POST['mode'] === 'update_status') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? (string)$_POST['status'] : '';
    $allowed = ['pending', 'reviewing', 'interview', 'hired', 'rejected'];

    if ($id > 0 && in_array($status, $allowed, true)) {
        // 1. 지원자 정보 먼저 조회 (문자/슬랙 발송용)
        $stmt_info = $pdo->prepare("SELECT a.name, a.phone, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE a.id = ?");
        $stmt_info->execute([$id]);
        $applicant = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($applicant) {
            // 2. 상태 업데이트
            $stmt = $pdo->prepare("UPDATE applicants SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            // 3. 상태별 한글 명칭 및 메시지 설정
            $status_kor = "";
            $sms_msg = "";
            
            switch ($status) {
                case 'reviewing':
                    $status_kor = "서류검토";
                    $sms_msg = "[GRIFF 채용]\n{$applicant['name']}님, 제출해주신 서류 검토가 시작되었습니다.\n꼼꼼히 검토 후 다시 안내드리겠습니다.";
                    break;
                case 'interview':
                    $status_kor = "면접대기";
                    $sms_msg = "[GRIFF 채용]\n{$applicant['name']}님, 서류 전형에 합격하셨습니다.\n면접 일정 조율을 위해 담당자가 곧 연락드릴 예정입니다.";
                    break;
                case 'hired':
                    $status_kor = "합격";
                    $sms_msg = "[GRIFF 채용]\n축하합니다! {$applicant['name']}님, 최종 합격하셨습니다.\n입사 관련 안내 메일을 확인해주세요.";
                    break;
                case 'rejected':
                    $status_kor = "불합격";
                    $sms_msg = "[GRIFF 채용]\n{$applicant['name']}님, 아쉽게도 이번 채용에서는 모시지 못하게 되었습니다.\n지원해 주셔서 진심으로 감사드립니다.";
                    break;
                default: // pending 등
                    $status_kor = "서류접수";
                    break;
            }

            // 4. 알리고 문자 발송 (상태가 변경되고 메시지가 있을 경우만)
            if (!empty($sms_msg)) {
                sendAligoSMS($applicant['phone'], $applicant['name'], $sms_msg);
            }

            // 5. 슬랙 알림 발송
            sendSlackRecruitNotification($applicant['name'], $applicant['job_title'], $status_kor);
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK";
    exit;
}

// [B] 삭제 로직
if (isset($_GET['mode']) && $_GET['mode'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $pdo->prepare("DELETE FROM applicants WHERE id = ?")->execute([$id]);
    }
    header('Location: applicant_list.php');
    exit;
}

// =================================================================
// [함수 1] 알리고 SMS 발송
// =================================================================
function sendAligoSMS($receiver, $destination, $msg) {
    // 알리고 계정 정보
    $sms_config = [
        'userid' => 'griff261',
        'key'    => '5o4amu1n07weck1mof53q9lc026fwkvu',
        'sender' => '02-326-3701',
    ];

    $sms_url = "https://apis.aligo.in/send/"; 
    $receiver = str_replace("-", "", $receiver); // 하이픈 제거

    $_POST_DATA = [
        'key'      => $sms_config['key'],
        'userid'   => $sms_config['userid'],
        'sender'   => $sms_config['sender'],
        'receiver' => $receiver,
        'msg'      => $msg,
        'msg_type' => 'LMS'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST_DATA);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}

// =================================================================
// [함수 2] 슬랙 알림 발송
// =================================================================
function sendSlackRecruitNotification($name, $job_title, $status_kor) {
    // ★ [설정] 채용 알림용 슬랙 웹훅 URL을 입력하세요.
    $webhook_url = "https://hooks.slack.com/services/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"; 

    $color_map = [
        '서류검토' => '#F59E0B', // Yellow
        '면접대기' => '#3B82F6', // Blue
        '합격' => '#10B981', // Green
        '불합격' => '#EF4444', // Red
        '서류접수' => '#6B7280'  // Gray
    ];
    $color = $color_map[$status_kor] ?? '#000000';

    $message = [
        "text" => "👤 *지원자 상태 변경 알림*",
        "attachments" => [[
            "color" => $color,
            "fields" => [
                ["title" => "지원자", "value" => $name, "short" => true],
                ["title" => "변경 상태", "value" => $status_kor, "short" => true],
                ["title" => "지원 공고", "value" => $job_title, "short" => false]
            ],
            "footer" => "GRIFF Recruit System",
            "ts" => time()
        ]]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

require_once '../inc/admin_header.php';

// [C] 리스트 조회 로직
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$job_filter = isset($_GET['recruit_id']) ? $_GET['recruit_id'] : 'All';
$recruit_list = $pdo->query("SELECT id, title FROM recruits ORDER BY id DESC")->fetchAll();

$sql = "SELECT a.*, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (a.name LIKE :search OR a.email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($job_filter && $job_filter !== 'All') {
    $sql .= " AND a.recruit_id = :rid";
    $params[':rid'] = $job_filter;
}
$sql .= " ORDER BY a.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applicants = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto pb-20">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Applicants</h1>
            <p class="text-sm text-gray-500 mt-1">Review and manage job applications</p>
        </div>
        <a href="applicant_export.php<?php echo '?' . $_SERVER['QUERY_STRING']; ?>" target="_blank" 
           class="group bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#0098FF] transition flex items-center shadow-sm">
            <img src="/img/admin/download_white.svg" class="w-4 h-4 mr-2 group-hover:hidden" alt="Download"> 
            <img src="/img/admin/download_white.svg" class="w-4 h-4 mr-2 hidden group-hover:block" alt="Download Hover">
            Export List
        </a>
    </div>

    <div class="flex flex-wrap gap-2 mb-8 overflow-x-auto pb-2">
        <a href="applicant_list.php?recruit_id=All" class="px-4 py-2 rounded-full text-sm font-bold border transition whitespace-nowrap <?php echo $job_filter == 'All' ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'; ?>">All (<?php echo count($applicants); ?>)</a>
        <?php foreach ($recruit_list as $job): ?>
            <a href="applicant_list.php?recruit_id=<?php echo $job['id']; ?>" class="px-4 py-2 rounded-full text-sm font-bold border transition whitespace-nowrap <?php echo $job_filter == $job['id'] ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'; ?>"><?php echo htmlspecialchars($job['title']); ?></a>
        <?php endforeach; ?>
    </div>

    <div class="space-y-4">
        <?php if (count($applicants) > 0): ?>
            <?php foreach ($applicants as $row): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col md:flex-row items-center hover:shadow-md transition cursor-pointer group" onclick="openModal(<?php echo $row['id']; ?>)">
                <div class="flex items-center w-full md:w-1/2 mb-4 md:mb-0">
                    <div class="w-12 h-12 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center mr-4 shrink-0 overflow-hidden relative">
                        <?php if(!empty($row['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <span class="text-lg font-bold text-gray-400 absolute inset-0 hidden items-center justify-center bg-gray-100"><?php echo strtoupper(mb_substr($row['name'], 0, 1)); ?></span>
                        <?php else: ?>
                            <span class="text-lg font-bold text-gray-400"><?php echo strtoupper(mb_substr($row['name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="text-xs text-blue-600 font-medium mt-0.5"><?php echo htmlspecialchars($row['job_title']); ?></p>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                            <span class="flex items-center"><i data-lucide="mail" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($row['email']); ?></span>
                            <span class="hidden sm:flex items-center"><i data-lucide="phone" class="w-3 h-3 mr-1"></i> <?php echo htmlspecialchars($row['phone']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2 flex items-center justify-between md:justify-end gap-6">
                    <div class="text-right hidden sm:block"><p class="text-xs text-gray-400"><?php echo date("Y-m-d", strtotime($row['applied_at'])); ?></p></div>
                    <?php
                        $status_badges = [
                            'pending' => ['text'=>'서류접수', 'class'=>'bg-gray-100 text-gray-600'],
                            'reviewing' => ['text'=>'서류검토', 'class'=>'bg-yellow-50 text-yellow-600 border border-yellow-100'],
                            'interview' => ['text'=>'면접대기', 'class'=>'bg-blue-50 text-blue-600 border border-blue-100'],
                            'hired' => ['text'=>'합격', 'class'=>'bg-green-50 text-green-600 border border-green-100'],
                            'rejected' => ['text'=>'불합격', 'class'=>'bg-red-50 text-red-600 border border-red-100'],
                        ];
                        $badge = $status_badges[$row['status']] ?? $status_badges['pending'];
                    ?>
                    <span id="status-badge-<?php echo $row['id']; ?>" class="px-3 py-1 rounded-full text-xs font-bold <?php echo $badge['class']; ?>">
                        <?php echo $badge['text']; ?>
                    </span>
                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-black group-hover:text-white transition"><i data-lucide="chevron-right" class="w-4 h-4"></i></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-xl border border-gray-200"><i data-lucide="users" class="w-10 h-10 text-gray-300 mx-auto mb-3"></i><p class="text-gray-500 font-medium">No applicants found.</p></div>
        <?php endif; ?>
    </div>
</div>

<div id="applicantModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="absolute inset-4 md:inset-10 bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-w-6xl mx-auto my-auto h-[90vh]">
        <button onclick="closeModal()" class="absolute top-4 right-4 z-10 w-8 h-8 flex items-center justify-center bg-white/50 hover:bg-gray-100 rounded-full transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        <div id="modalContent" class="w-full h-full flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div></div>
    </div>
</div>

</main>

<script>
    lucide.createIcons();

    // 상태 설정값
    const statusConfig = {
        'pending': { text: '서류접수', class: 'bg-gray-100 text-gray-600' },
        'reviewing': { text: '서류검토', class: 'bg-yellow-50 text-yellow-600 border border-yellow-100' },
        'interview': { text: '면접대기', class: 'bg-blue-50 text-blue-600 border border-blue-100' },
        'hired': { text: '합격', class: 'bg-green-50 text-green-600 border border-green-100' },
        'rejected': { text: '불합격', class: 'bg-red-50 text-red-600 border border-red-100' }
    };

    // 모달 열기
    function openModal(id) {
        document.getElementById('applicantModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const timestamp = new Date().getTime();
        fetch('ajax_applicant_view.php?id=' + id + '&t=' + timestamp)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
                lucide.createIcons();
            });
    }

    // 모달 닫기
    function closeModal() {
        document.getElementById('applicantModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('modalContent').innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div>';
    }

    // 탭 전환
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-black', 'border-b-2', 'border-black');
            btn.classList.add('text-gray-400');
        });
        const activeBtn = document.getElementById('tab_btn_' + tabName);
        if(activeBtn) {
            activeBtn.classList.remove('text-gray-400');
            activeBtn.classList.add('text-black', 'border-b-2', 'border-black');
        }

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const activeContent = document.getElementById('tab_content_' + tabName);
        if(activeContent) activeContent.classList.remove('hidden');
    }

    // 상태 업데이트
    function updateStatus(id, status) {
        if(!confirm('상태를 변경하시겠습니까?')) return;

        const formData = new FormData();
        formData.append('mode', 'update_status');
        formData.append('id', id);
        formData.append('status', status);

        fetch('applicant_list.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if(result.trim() === 'OK') {
                openModal(id);
                const badge = document.getElementById('status-badge-' + id);
                if(badge && statusConfig[status]) {
                    badge.className = `px-3 py-1 rounded-full text-xs font-bold ${statusConfig[status].class}`;
                    badge.innerText = statusConfig[status].text;
                }
            }
        });
    }

    // ----------------------------------------------------
    // ★ 여기로 이동된 스케줄 관련 함수들
    // ----------------------------------------------------
    
    // 1. 스케줄 입력 폼 토글
    function toggleSchedule() {
        const form = document.getElementById('schedule-form');
        const btn = document.getElementById('btn-schedule');
        
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            btn.classList.add('hidden');
            
            // 기본값: 현재시간 + 24시간
            const now = new Date();
            now.setDate(now.getDate() + 1);
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('interview-date').value = now.toISOString().slice(0, 16);
        } else {
            form.classList.add('hidden');
            btn.classList.remove('hidden');
        }
    }

    // 2. 구글 캘린더 열기 (Hidden Input에서 값 읽기)
    function openGoogleCalendar() {
        // 모달 안에 숨겨둔 input 값들 가져오기
        const appName = document.getElementById('cal-name').value;
        const appJob = document.getElementById('cal-job').value;
        const appEmail = document.getElementById('cal-email').value;
        const appPhone = document.getElementById('cal-phone').value;
        const appLink = document.getElementById('cal-link').value;
        
        const dateInput = document.getElementById('interview-date').value;
        if (!dateInput) {
            alert('Please select a date and time.');
            return;
        }

        const startDate = new Date(dateInput);
        const endDate = new Date(startDate.getTime() + 60 * 60 * 1000); // 1시간

        const formatTime = (date) => {
            return date.toISOString().replace(/-|:|\.\d\d\d/g, "");
        };

        const startStr = formatTime(startDate);
        const endStr = formatTime(endDate);

        const title = `Interview: ${appName} (${appJob})`;
        const details = `Candidate: ${appName}\nPhone: ${appPhone}\nEmail: ${appEmail}\n\nLink to CMS: ${appLink}`;
        const location = "Google Meet / Office";
        const guestEmail = appEmail;

        const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${startStr}/${endStr}&details=${encodeURIComponent(details)}&location=${encodeURIComponent(location)}&add=${encodeURIComponent(guestEmail)}`;

        window.open(url, '_blank');
    }
</script>
</body>
</html>