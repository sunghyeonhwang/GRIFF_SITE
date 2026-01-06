<?php
// 에러 확인용
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
require_once '../inc/admin_header.php';

// --- [A. 기능 처리 로직] ---

// 1. 마감 상태 변경 (Open <-> Closed)
if (isset($_GET['mode']) && $_GET['mode'] == 'toggle_status' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT status FROM recruits WHERE id = ?");
    $stmt->execute([$id]);
    $curr = $stmt->fetchColumn();
    
    $new_status = ($curr == 'open') ? 'closed' : 'open';
    
    $update = $pdo->prepare("UPDATE recruits SET status = ? WHERE id = ?");
    $update->execute([$new_status, $id]);
    
    echo "<script>location.href='recruit_list.php';</script>";
    exit;
}

// 2. 숨김 상태 변경 (Visible <-> Hidden) ★ 추가됨
if (isset($_GET['mode']) && $_GET['mode'] == 'toggle_hide' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT is_hidden FROM recruits WHERE id = ?");
    $stmt->execute([$id]);
    $curr = $stmt->fetchColumn();
    
    $new_hidden = ($curr == 1) ? 0 : 1;
    
    $update = $pdo->prepare("UPDATE recruits SET is_hidden = ? WHERE id = ?");
    $update->execute([$new_hidden, $id]);
    
    echo "<script>location.href='recruit_list.php';</script>";
    exit;
}

// 3. 삭제 처리
if (isset($_GET['mode']) && $_GET['mode'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $del = $pdo->prepare("DELETE FROM recruits WHERE id = ?");
    $del->execute([$id]);
    
    echo "<script>alert('Deleted successfully.'); location.href='recruit_list.php';</script>";
    exit;
}

// --- [B. 데이터 조회 로직] ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';

// 지원자 통계 서브쿼리 포함
$sql = "SELECT r.*,
        (SELECT COUNT(*) FROM applicants WHERE recruit_id = r.id) as cnt_total,
        (SELECT COUNT(*) FROM applicants WHERE recruit_id = r.id AND status = 'interview') as cnt_interview,
        (SELECT COUNT(*) FROM applicants WHERE recruit_id = r.id AND status = 'rejected') as cnt_rejected
        FROM recruits r WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND (title LIKE :search OR job_type LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status_filter && $status_filter !== 'All') {
    $sql .= " AND status = :status";
    $params[':status'] = strtolower($status_filter);
}

// 숨김 처리된 것도 관리자는 봐야 하므로 조건 없이 가져오되, 정렬만 조정
$sql .= " ORDER BY is_hidden ASC, status ASC, deadline ASC"; 

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recruits = $stmt->fetchAll();

// D-Day 계산 함수
function getDday($deadline) {
    if (!$deadline) return '-';
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $target = new DateTime($deadline);
    $target->setTime(0, 0, 0);
    $interval = $today->diff($target);
    $days = (int)$interval->format('%r%a');
    
    if ($days < 0) return "Ended";
    if ($days == 0) return "D-Day";
    return "D-" . $days;
}
?>

<div class="max-w-7xl mx-auto pb-20">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recruit / Jobs</h1>
            <p class="text-sm text-gray-500 mt-1">Manage job openings and talent acquisition</p>
        </div>
        <a href="recruit_post.php" class="group bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#0098FF] transition flex items-center shadow-sm whitespace-nowrap">
            <img src="/img/admin/add_gr.svg" class="w-4 h-4 mr-2 group-hover:hidden" alt="Add">
            <img src="/img/admin/add_white.svg" class="w-4 h-4 mr-2 hidden group-hover:block" alt="Add Hover">
            Post New Job
        </a>
    </div>

    <div class="bg-white p-2 rounded-xl border border-gray-200 mb-6 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by job title..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 transition text-sm">
            </div>
            
            <select name="status" onchange="this.form.submit()" class="w-full md:w-40 px-4 py-2.5 bg-gray-50 border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 text-sm cursor-pointer">
                <option value="All">All Status</option>
                <option value="open" <?php if($status_filter=='open') echo 'selected'; ?>>Open (채용중)</option>
                <option value="closed" <?php if($status_filter=='closed') echo 'selected'; ?>>Closed (마감)</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                        <th class="px-6 py-4">Job Title</th>
                        <th class="px-6 py-4 w-32">Job Type</th>
                        <th class="px-6 py-4 w-48">Applicants Stats</th> 
                        <th class="px-6 py-4 w-32">Deadline</th>
                        <th class="px-6 py-4 w-24">D-Day</th>
                        <th class="px-6 py-4 w-24">Status</th>
                        <th class="px-6 py-4 w-48 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($recruits) > 0): ?>
                        <?php foreach ($recruits as $row): ?>
                        
                        <tr class="hover:bg-gray-50 transition group <?php echo $row['is_hidden'] ? 'bg-gray-50/50' : ''; ?>">
                            
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900 text-sm <?php echo $row['is_hidden'] ? 'text-gray-400' : ''; ?>">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                    <?php if($row['is_hidden']): ?>
                                        <span class="ml-2 text-[10px] bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded">Hidden</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">Posted on <?php echo date("Y.m.d", strtotime($row['created_at'])); ?></p>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-100">
                                    <?php echo htmlspecialchars($row['job_type']); ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3 text-xs">
                                    <div class="text-center">
                                        <p class="font-bold text-gray-900"><?php echo $row['cnt_total']; ?></p>
                                        <p class="text-gray-400 text-[10px]">Total</p>
                                    </div>
                                    <div class="w-px h-6 bg-gray-200"></div>
                                    <div class="text-center">
                                        <p class="font-bold text-blue-600"><?php echo $row['cnt_interview']; ?></p>
                                        <p class="text-gray-400 text-[10px]">Interv.</p>
                                    </div>
                                    <div class="w-px h-6 bg-gray-200"></div>
                                    <div class="text-center">
                                        <p class="font-bold text-gray-400"><?php echo $row['cnt_rejected']; ?></p>
                                        <p class="text-gray-400 text-[10px]">Reject</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-gray-500 text-sm font-medium">
                                <?php echo $row['deadline'] ? date("Y.m.d", strtotime($row['deadline'])) : 'Always'; ?>
                            </td>

                            <td class="px-6 py-4">
                                <?php 
                                    $dday = getDday($row['deadline']);
                                    $d_class = "text-gray-500";
                                    if($dday == 'D-Day' || (strpos($dday, 'D-') !== false && (int)str_replace('D-', '', $dday) <= 7)) {
                                        $d_class = "text-red-600 font-bold";
                                    }
                                ?>
                                <span class="text-sm <?php echo $d_class; ?>"><?php echo $dday; ?></span>
                            </td>

                            <td class="px-6 py-4">
                                <?php if($row['status'] == 'open'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">Open</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">Closed</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    
                                    <a href="recruit_list.php?mode=toggle_status&id=<?php echo $row['id']; ?>" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition" 
                                       title="<?php echo $row['status']=='open' ? 'Close Job' : 'Open Job'; ?>">
                                        <?php if($row['status'] == 'open'): ?>
                                            <img src="/img/admin/dline_icon_on.svg" class="w-5 h-5" alt="Open">
                                        <?php else: ?>
                                            <img src="/img/admin/dline_icon_off.svg" class="w-5 h-5 opacity-50" alt="Closed">
                                        <?php endif; ?>
                                    </a>

                                    <a href="recruit_list.php?mode=toggle_hide&id=<?php echo $row['id']; ?>" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition"
                                       title="<?php echo $row['is_hidden'] ? 'Show Job' : 'Hide Job'; ?>">
                                        <?php if($row['is_hidden']): ?>
                                            <img src="/img/admin/view_off.svg" class="w-5 h-5 opacity-50" alt="Hidden">
                                        <?php else: ?>
                                            <img src="/img/admin/view_on.svg" class="w-5 h-5" alt="Visible">
                                        <?php endif; ?>
                                    </a>

                                    <a href="recruit_post.php?id=<?php echo $row['id']; ?>" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>

                                    <a href="recruit_list.php?mode=delete&id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('이 채용공고를 삭제하시겠습니까?');"
                                       class="group w-9 h-9 flex items-center justify-center bg-red-50 rounded-lg hover:bg-[#FFE5E5] transition" title="Delete">
                                        <img src="/img/admin/trash_on.svg" class="w-4 h-4 group-hover:hidden" alt="Delete">
                                        <img src="/img/admin/trash_over.svg" class="w-4 h-4 hidden group-hover:block" alt="Delete Hover">
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="briefcase" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">No Job Postings Found</h3>
                                    <p class="text-gray-500 mt-1 mb-6 text-sm">Create a new job opening to start recruiting.</p>
                                    <a href="recruit_post.php" class="group bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#0098FF] transition flex items-center shadow-sm">
                                        Post New Job
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>