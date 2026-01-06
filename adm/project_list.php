<?php
// 에러 확인용 (AJAX 응답에 불필요한 공백이 들어가지 않도록 주의)
ini_set('display_errors', 0); // AJAX 처리 중 에러가 화면에 출력되지 않게 잠시 끔
error_reporting(E_ALL);

require_once '../inc/db_connect.php';

// --- [A. AJAX 처리 로직 (순서 변경)] ---
// 가장 상단에서 처리하여 HTML 출력을 방지함
if (isset($_POST['mode']) && $_POST['mode'] == 'reorder') {
    header('Content-Type: application/json'); // JSON 응답 명시
    
    try {
        $order = $_POST['order'];
        if (is_array($order)) {
            foreach ($order as $index => $id) {
                // 순서값 업데이트 (1부터 시작)
                $stmt = $pdo->prepare("UPDATE projects SET sort_order = :order WHERE id = :id");
                $result = $stmt->execute([':order' => $index + 1, ':id' => $id]);
                
                if (!$result) {
                    throw new Exception("ID $id 업데이트 실패");
                }
            }
        }
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        // 에러 발생 시 메시지 전달
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 여기서부터 HTML 출력 시작
require_once '../inc/admin_header.php';

// --- [B. 기능 처리 로직] ---

// 1. 상태 변경
if (isset($_GET['mode']) && $_GET['mode'] == 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT status FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $curr = $stmt->fetchColumn();
    $new_status = ($curr == 'published') ? 'draft' : 'published';
    $update = $pdo->prepare("UPDATE projects SET status = ? WHERE id = ?");
    $update->execute([$new_status, $id]);
    echo "<script>location.href='project_list.php';</script>";
    exit;
}

// 2. 삭제 처리
if (isset($_GET['mode']) && $_GET['mode'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $del = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $del->execute([$id]);
    echo "<script>alert('삭제되었습니다.'); location.href='project_list.php';</script>";
    exit;
}

// --- [C. 데이터 조회 로직] ---
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'All';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';

$sql = "SELECT * FROM projects WHERE 1=1";
$params = [];

if ($search_keyword) {
    $sql .= " AND (title LIKE :search OR client_name LIKE :search)";
    $params[':search'] = "%$search_keyword%";
}
if ($category_filter && $category_filter !== 'All') {
    $sql .= " AND category = :category";
    $params[':category'] = $category_filter;
}
if ($status_filter && $status_filter !== 'All') {
    $sql .= " AND status = :status";
    $params[':status'] = strtolower($status_filter);
}

// 정렬: 사용자 지정 순서(sort_order) 우선
$sql .= " ORDER BY sort_order ASC, id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
    exit;
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div class="max-w-7xl mx-auto pb-20">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Project Manager</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and organize all your projects</p>
        </div>
        <a href="project_post.php" class="group bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-[#0098FF] transition flex items-center shadow-sm whitespace-nowrap">
    <img src="/img/admin/add_gr.svg" class="w-4 h-4 mr-2 group-hover:hidden" alt="Add">
    <img src="/img/admin/add_white.svg" class="w-4 h-4 mr-2 hidden group-hover:block" alt="Add Hover">
    + Add New Project
</a>
    </div>

    <div class="bg-white p-2 rounded-xl border border-gray-200 mb-6 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search_keyword); ?>" 
                       placeholder="Search by title or client..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 transition text-sm">
            </div>
            
            <select name="category" onchange="this.form.submit()" class="w-full md:w-48 px-4 py-2.5 bg-gray-50 border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 text-sm cursor-pointer">
                <option value="All">All Categories</option>
                <option value="Event" <?php if($category_filter=='Event') echo 'selected'; ?>>Event</option>
                <option value="Design" <?php if($category_filter=='Design') echo 'selected'; ?>>Design</option>
                <option value="Film" <?php if($category_filter=='Film') echo 'selected'; ?>>Film</option>
                <option value="Studio" <?php if($category_filter=='Studio') echo 'selected'; ?>>Studio</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="w-full md:w-40 px-4 py-2.5 bg-gray-50 border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 text-sm cursor-pointer">
                <option value="All">All Status</option>
                <option value="Published" <?php if($status_filter=='Published') echo 'selected'; ?>>Published</option>
                <option value="Draft" <?php if($status_filter=='Draft') echo 'selected'; ?>>Draft</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                        <th class="pl-4 py-3 w-10"></th>
                        <th class="px-4 py-3 w-32">Thumbnail</th>
                        <th class="px-4 py-3">Project Title</th>
                        <th class="px-4 py-3 w-32">Category</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3 w-32">Date</th>
                        <th class="px-4 py-3 w-24">Status</th>
                        <th class="px-4 py-3 w-40 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortable-list" class="divide-y divide-gray-100">
                    <?php if (count($projects) > 0): ?>
                        <?php foreach ($projects as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>" class="hover:bg-gray-50 transition group bg-white">
                            
                            <td class="pl-4 py-3 cursor-move drag-handle text-gray-300 hover:text-gray-600">
                                <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                            </td>

                            <td class="px-4 py-3">
                                <div class="w-20 h-12 bg-gray-200 rounded overflow-hidden border border-gray-100 relative">
                                    <?php if(!empty($row['thumbnail_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['thumbnail_path']); ?>" 
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden absolute inset-0 bg-gray-200 flex items-center justify-center text-gray-400">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="px-4 py-3">
                                <p class="font-bold text-gray-900 text-sm truncate max-w-xs"><?php echo htmlspecialchars($row['title']); ?></p>
                            </td>

                            <td class="px-4 py-3">
                                <?php
                                    $badge = 'bg-gray-100 text-gray-600';
                                    if($row['category'] == 'Event') $badge = 'bg-purple-100 text-purple-700';
                                    elseif($row['category'] == 'Design') $badge = 'bg-blue-100 text-blue-700';
                                    elseif($row['category'] == 'Film') $badge = 'bg-red-100 text-red-700';
                                    elseif($row['category'] == 'Studio') $badge = 'bg-green-100 text-green-700';
                                ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $badge; ?>">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-600 text-sm">
                                <?php echo htmlspecialchars($row['client_name']); ?>
                            </td>

                            <td class="px-4 py-3 text-gray-500 text-sm">
                                <?php echo date("M d, Y", strtotime($row['completion_date'])); ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php if($row['status'] == 'published'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">Published</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">Draft</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="project_post.php?id=<?php echo $row['id']; ?>" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <a href="project_list.php?mode=toggle&id=<?php echo $row['id']; ?>" 
                                       class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition" 
                                       title="<?php echo $row['status'] == 'published' ? 'Hide Project' : 'Show Project'; ?>">
                                        <?php if($row['status'] == 'published'): ?>
                                            <img src="/img/admin/view_on.svg" class="w-5 h-5" alt="Published">
                                        <?php else: ?>
                                            <img src="/img/admin/view_off.svg" class="w-5 h-5 opacity-50" alt="Draft">
                                        <?php endif; ?>
                                    </a>
                                    <a href="project_list.php?mode=delete&id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('이 프로젝트를 영구 삭제하시겠습니까?');"
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
                            <td colspan="8" class="px-6 py-24 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i data-lucide="folder-open" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">No Projects Found</h3>
                                    <p class="text-gray-500 mt-1 mb-6 text-sm">Create your first project to get started.</p>
                                    <a href="project_post.php" class="group bg-black text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition flex items-center shadow-sm">
                                        Create Project
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

    var el = document.getElementById('sortable-list');
    var sortable = Sortable.create(el, {
        handle: '.drag-handle',
        animation: 150,
        ghostClass: 'bg-blue-50',
        onEnd: function (evt) {
            var order = [];
            document.querySelectorAll('#sortable-list tr').forEach(function(row) {
                order.push(row.getAttribute('data-id'));
            });

            // AJAX 요청 (JSON 응답 처리)
            fetch('project_list.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'mode=reorder&order[]=' + order.join('&order[]=')
            })
            .then(response => response.json()) // JSON으로 파싱
            .then(data => {
                if(data.status !== 'success') {
                    // DB 컬럼 없음 등 구체적인 에러 메시지 출력
                    alert('순서 저장 실패: ' + (data.message || '알 수 없는 오류'));
                } else {
                    console.log('Order updated');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('통신 에러가 발생했습니다.');
            });
        }
    });
</script>
</body>
</html>