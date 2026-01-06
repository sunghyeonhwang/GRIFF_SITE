<?php
// --- [1. 네임스페이스 및 DB 연결] ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once '../inc/db_connect.php';

// ------------------------------------------------------------------------
// [AJAX 처리 1] 이메일 발송 & DB 저장
// ------------------------------------------------------------------------
if (isset($_POST['mode']) && $_POST['mode'] == 'send_email') {
    require_once '../inc/PHPMailer/Exception.php';
    require_once '../inc/PHPMailer/PHPMailer.php';
    require_once '../inc/PHPMailer/SMTP.php';

    $to_email = $_POST['to_email'];
    $subject  = $_POST['subject'];
    $message  = $_POST['message'];
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $mail = new PHPMailer(true);

    try {
        // [SMTP 서버 설정]
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ★ 본인 계정 정보
        $mail->Username   = 'sunghyeon.hwang@griff.co.kr';                
        $mail->Password   = 'ognodjeponwomclb'; // 앱 비밀번호

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        
        $mail->setFrom('sunghyeon.hwang@griff.co.kr', 'GRIFF Admin');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();

        // ★ [DB 업데이트] 답장 내용 저장 & 상태 'solved'로 변경
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE inquiries SET reply_content = ?, replied_at = NOW(), status = 'solved' WHERE id = ?");
            $stmt->execute([$message, $id]);
        }

        echo "OK"; 
    } catch (Exception $e) {
        echo "FAIL: " . $mail->ErrorInfo;
    }
    exit; 
}

// ------------------------------------------------------------------------
// [AJAX 처리 2] 상태 변경 (Solved 토글)
// ------------------------------------------------------------------------
if (isset($_POST['mode']) && $_POST['mode'] == 'toggle_solve') {
    $id = (int)$_POST['id'];
    $is_solved = $_POST['solved'] === 'true';
    $status = $is_solved ? 'solved' : 'read';
    
    $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    exit;
}

// ------------------------------------------------------------------------
// [AJAX 처리 3] 삭제 처리
// ------------------------------------------------------------------------
if (isset($_POST['mode']) && $_POST['mode'] == 'delete') {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$id]);
    echo "OK";
    exit;
}

// --- [2. HTML 화면 출력 시작] ---
require_once '../inc/admin_header.php';

// [데이터 조회]
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM inquiries WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR message LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<script src="https://cdn.tiny.cloud/1/kqri7o2cv17ktehs2cxvenepb6sz91iooxgzglmhv11wkhi3/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="flex h-[calc(100vh-65px)] overflow-hidden bg-white border-t border-gray-200">
    
    <div class="w-[400px] flex flex-col border-r border-gray-200 bg-white shrink-0">
        <div class="p-6 border-b border-gray-100 shrink-0">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Inquiries</h1>
            <p class="text-xs text-gray-500 mb-4">Manage messages and replies</p>
            <form class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search..." 
                       class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border-transparent rounded-lg text-sm focus:bg-white focus:border-gray-300 focus:ring-0 transition">
            </form>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide">
            <?php if (count($rows) > 0): ?>
                <ul class="divide-y divide-gray-50">
                    <?php foreach ($rows as $row): ?>
                    <?php 
                        $is_new = ($row['status'] == 'new');
                        $is_solved = ($row['status'] == 'solved');
                        $is_replied = !empty($row['reply_content']);
                    ?>
                    <li onclick="loadInquiry(<?php echo $row['id']; ?>, this)" 
                        class="cursor-pointer hover:bg-gray-50 transition p-6 border-l-4 border-transparent hover:border-gray-200 group inquiry-item"
                        data-id="<?php echo $row['id']; ?>">
                        
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-sm font-bold text-gray-900 <?php echo $is_new ? 'text-blue-600' : ''; ?>">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </span>
                            <span class="text-[11px] text-gray-400 whitespace-nowrap ml-2">
                                <?php echo date("M d", strtotime($row['created_at'])); ?>
                            </span>
                        </div>

                        <p class="text-xs font-semibold text-gray-800 mb-1 truncate">
                            <?php echo htmlspecialchars($row['subject'] ?? 'No Subject'); ?>
                        </p>
                        <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                            <?php echo htmlspecialchars(mb_substr($row['message'], 0, 100)); ?>...
                        </p>
                        
                        <div class="mt-3 flex gap-2">
                            <?php if($is_solved): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-600">
                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i> Solved
                                </span>
                            <?php endif; ?>
                            <?php if($is_replied): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600">
                                    <i data-lucide="corner-down-right" class="w-3 h-3 mr-1"></i> Replied
                                </span>
                            <?php endif; ?>
                        </div>

                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center h-full text-center p-6">
                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="inbox" class="w-6 h-6 text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-400">No messages found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="inquiry-detail" class="flex-1 bg-white flex flex-col h-full overflow-hidden relative">
        <div class="flex flex-col items-center justify-center h-full text-gray-300">
            <i data-lucide="mail-open" class="w-16 h-16 mb-4 opacity-20"></i>
            <p class="text-sm">Select an item to read</p>
        </div>
    </div>
</div>

</main>

<script>
    lucide.createIcons();

    // 1. 상세 내용 로드 (AJAX)
    function loadInquiry(id, el) {
        // 리스트 활성화 스타일 처리
        document.querySelectorAll('.inquiry-item').forEach(item => {
            item.classList.remove('bg-blue-50/50', 'border-blue-500');
            item.classList.add('border-transparent');
        });
        el.classList.add('bg-blue-50/50', 'border-blue-500');
        el.classList.remove('border-transparent');

        const container = document.getElementById('inquiry-detail');
        container.innerHTML = '<div class="flex items-center justify-center h-full"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-black"></div></div>';

        // 기존 에디터 인스턴스 제거
        if (tinymce.get('reply-message')) {
            tinymce.get('reply-message').remove();
        }

        // 상세 뷰 로드
        fetch('ajax_inquiry_view.php?id=' + id)
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                lucide.createIcons();

                // 답장용 에디터 초기화
                tinymce.init({
                    selector: '#reply-message',
                    height: 300,
                    menubar: false,
                    statusbar: false,
                    plugins: 'link image code lists',
                    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
                    content_style: "body { font-family: 'Inter', sans-serif; font-size: 14px; line-height: 1.6; }"
                });
            });
    }

    // 2. 이메일 전송 (실시간 목록 갱신 포함)
    function sendEmail() {
        const btn = document.getElementById('btn-send-mail');
        const toEmail = document.getElementById('reply-to').value;
        const subject = document.getElementById('reply-subject').value;
        
        // 현재 문의 ID 가져오기
        const idInput = document.getElementById('current_inquiry_id');
        const id = idInput ? idInput.value : 0;

        const message = tinymce.get('reply-message').getContent();

        if(!tinymce.get('reply-message').getContent({format: 'text'}).trim()) {
            alert('Please write a message.');
            return;
        }

        // 로딩 표시
        const originalText = btn.innerHTML;
        btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> Sending...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('mode', 'send_email');
        formData.append('id', id);
        formData.append('to_email', toEmail);
        formData.append('subject', subject);
        formData.append('message', message);

        fetch('inquiry_list.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => {
            if(res.trim() === 'OK') {
                alert('Email sent successfully!');
                
                // (1) 오른쪽 상세 화면 리로드 (회신 내용 표시)
                const listItem = document.querySelector(`.inquiry-item[data-id="${id}"]`);
                loadInquiry(id, listItem);

                // (2) 왼쪽 목록 강제 업데이트 (새로고침 없이 뱃지 추가)
                if (listItem) {
                    // 제목 파란색(New 상태) 제거
                    const title = listItem.querySelector('span.text-blue-600');
                    if(title) title.classList.remove('text-blue-600');

                    // 뱃지 컨테이너 찾기 또는 생성
                    let badgeContainer = listItem.querySelector('.mt-3');
                    if (!badgeContainer) {
                        badgeContainer = document.createElement('div');
                        badgeContainer.className = 'mt-3 flex gap-2';
                        listItem.appendChild(badgeContainer);
                    }

                    // Solved + Replied 뱃지 주입
                    badgeContainer.innerHTML = `
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-600">
                            <i data-lucide="check" class="w-3 h-3 mr-1"></i> Solved
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600">
                            <i data-lucide="corner-down-right" class="w-3 h-3 mr-1"></i> Replied
                        </span>
                    `;
                    lucide.createIcons();
                }

                // 폼 초기화
                tinymce.get('reply-message').setContent('');
                toggleReplyForm();

            } else {
                alert('Failed to send email.\n' + res);
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // 3. 삭제
    function deleteInquiry(id) {
        if(!confirm('Are you sure you want to delete this message?')) return;
        const formData = new FormData();
        formData.append('mode', 'delete');
        formData.append('id', id);
        fetch('inquiry_list.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => { if(res.trim() === 'OK') location.reload(); });
    }

    // 4. Solved 토글 (체크박스)
    function toggleSolved(id, checkbox) {
        const isChecked = checkbox.checked;
        const formData = new FormData();
        formData.append('mode', 'toggle_solve');
        formData.append('id', id);
        formData.append('solved', isChecked);
        fetch('inquiry_list.php', { method: 'POST', body: formData })
        .then(() => location.reload());
    }

    // 5. 답장 폼 열기/닫기 UI
    function toggleReplyForm() {
        const container = document.getElementById('reply-container');
        const overlay = document.getElementById('reply-overlay');
        
        if (container.classList.contains('translate-y-full')) {
            container.classList.remove('translate-y-full');
            overlay.classList.remove('hidden');
        } else {
            container.classList.add('translate-y-full');
            overlay.classList.add('hidden');
        }
    }
</script>
</body>
</html>