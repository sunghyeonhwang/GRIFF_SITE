<?php
// [1] 에러 리포팅
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
// ★ secrets.php 로드 (경로 주의)
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/secrets.php';

// =================================================================
// [A] 상태 변경 로직 (SMS + Slack)
// =================================================================
if (isset($_POST['mode']) && $_POST['mode'] === 'update_status') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $status = isset($_POST['status']) ? (string)$_POST['status'] : '';
    $allowed = ['pending', 'reviewing', 'interview', 'hired', 'rejected'];

    if ($id > 0 && in_array($status, $allowed, true)) {
        // 1. 지원자 정보 조회
        $stmt_info = $pdo->prepare("SELECT a.name, a.phone, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE a.id = ?");
        $stmt_info->execute([$id]);
        $applicant = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($applicant) {
            // 2. 상태 업데이트
            $stmt = $pdo->prepare("UPDATE applicants SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            // 3. 메시지 설정
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
                default:
                    $status_kor = "서류접수";
                    break;
            }

            // 4. 문자 발송
            if (!empty($sms_msg)) {
                sendAligoSMS($applicant['phone'], $applicant['name'], $sms_msg);
            }

            // 5. 슬랙 발송
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
    echo "<script>alert('삭제되었습니다.'); location.href='applicant_list.php';</script>";
    exit;
}

// =================================================================
// [함수] 알리고 & 슬랙 (secrets.php 상수 사용)
// =================================================================
function sendAligoSMS($receiver, $destination, $msg) {
    $sms_url = "https://apis.aligo.in/send/"; 
    $receiver = str_replace("-", "", $receiver);
    
    $_POST_DATA = [
        'key'      => ALIGO_API_KEY,    // secrets.php 상수
        'userid'   => ALIGO_USER_ID,    // secrets.php 상수
        'sender'   => ALIGO_SENDER,     // secrets.php 상수
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
    curl_exec($ch);
    curl_close($ch);
}

function sendSlackRecruitNotification($name, $job_title, $status_kor) {
    $webhook_url = SLACK_WEBHOOK_RECRUIT; // secrets.php 상수

    $color_map = ['서류검토'=>'#F59E0B', '면접대기'=>'#3B82F6', '합격'=>'#10B981', '불합격'=>'#EF4444', '서류접수'=>'#6B7280'];
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

// [C] 조회 로직
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
                <?php
                    $row_class = "bg-white border-gray-200 hover:shadow-md"; 
                    if ($row['status'] == 'rejected') {
                        $row_class = "bg-gray-100 border-gray-200 opacity-60 grayscale"; 
                    } elseif ($row['status'] == 'interview') {
                        $row_class = "bg-blue-50 border-blue-100 hover:shadow-md"; 
                    } elseif ($row['status'] == 'hired') {
                        $row_class = "bg-emerald-50 border-emerald-100 hover:shadow-md"; 
                    }
                ?>
            <div class="<?php echo $row_class; ?> rounded-xl border p-5 flex flex-col md:flex-row items-center transition cursor-pointer group relative" onclick="openModal(<?php echo $row['id']; ?>)">
                <div class="flex items-center w-full md:w-3/5 mb-4 md:mb-0">
                    <div class="w-12 h-12 rounded-full bg-white border border-gray-200 flex items-center justify-center mr-5 shrink-0 overflow-hidden relative shadow-sm">
                        <?php if(!empty($row['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-lg font-bold text-gray-400"><?php echo strtoupper(mb_substr($row['name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-gray-500 mb-0.5"><?php echo htmlspecialchars($row['job_title']); ?></span>
                        <div class="flex items-center gap-3">
                            <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <span class="w-px h-3 bg-gray-300 hidden sm:block"></span>
                            <div class="flex items-center gap-3 text-xs text-gray-500 font-medium">
                                <span class="flex items-center"><?php echo htmlspecialchars($row['phone']); ?></span>
                                <span class="w-px h-2 bg-gray-300 hidden sm:block"></span>
                                <span class="flex items-center"><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-2/5 flex items-center justify-between md:justify-end gap-4 pl-4 border-t md:border-t-0 md:border-l border-gray-100 md:border-transparent pt-4 md:pt-0">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] text-gray-400 uppercase font-bold">Applied Date</p>
                        <p class="text-xs text-gray-600 font-medium"><?php echo date("Y-m-d", strtotime($row['applied_at'])); ?></p>
                    </div>
                    <?php
                        $status_badges = [
                            'pending' => ['text'=>'서류접수', 'class'=>'bg-gray-100 text-gray-600'],
                            'reviewing' => ['text'=>'서류검토', 'class'=>'bg-yellow-100 text-yellow-700'],
                            'interview' => ['text'=>'면접대기', 'class'=>'bg-blue-100 text-blue-700'],
                            'hired' => ['text'=>'합격', 'class'=>'bg-green-100 text-green-700'],
                            'rejected' => ['text'=>'불합격', 'class'=>'bg-red-100 text-red-700'],
                        ];
                        $badge = $status_badges[$row['status']] ?? $status_badges['pending'];
                    ?>
                    <span id="status-badge-<?php echo $row['id']; ?>" class="px-3 py-1 rounded-full text-xs font-bold <?php echo $badge['class']; ?> whitespace-nowrap shadow-sm">
                        <?php echo $badge['text']; ?>
                    </span>
                    <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400 group-hover:bg-black group-hover:text-white group-hover:border-black transition shrink-0">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                    <a href="?mode=delete&id=<?php echo $row['id']; ?>" onclick="event.stopPropagation(); return confirm('정말 삭제하시겠습니까?');"
                       class="w-8 h-8 rounded-full bg-red-50 border border-red-100 flex items-center justify-center text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition shrink-0 ml-2" title="Delete">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </a>
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
    const statusConfig = {
        'pending': { text: '서류접수', class: 'bg-gray-100 text-gray-600' },
        'reviewing': { text: '서류검토', class: 'bg-yellow-100 text-yellow-700' },
        'interview': { text: '면접대기', class: 'bg-blue-100 text-blue-700' },
        'hired': { text: '합격', class: 'bg-green-100 text-green-700' },
        'rejected': { text: '불합격', class: 'bg-red-100 text-red-700' }
    };
    function openModal(id) {
        document.getElementById('applicantModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        const timestamp = new Date().getTime();
        fetch('ajax_applicant_view.php?id=' + id + '&t=' + timestamp).then(r => r.text()).then(html => {
            document.getElementById('modalContent').innerHTML = html;
            lucide.createIcons();
        });
    }
    function closeModal() {
        document.getElementById('applicantModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('modalContent').innerHTML = '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div>';
    }
    function updateStatus(id, status) {
        if(!confirm('상태를 변경하시겠습니까?')) return;
        const formData = new FormData();
        formData.append('mode', 'update_status');
        formData.append('id', id);
        formData.append('status', status);
        fetch('applicant_list.php', { method: 'POST', body: formData }).then(r => r.text()).then(result => {
            if(result.trim() === 'OK') {
                openModal(id);
                location.reload(); 
            }
        });
    }
</script>
</body>
</html>