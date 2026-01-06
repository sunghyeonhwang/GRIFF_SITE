<?php
// 에러 확인용
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
require_once '../inc/admin_header.php';

// --- [초기화 및 모드 설정] ---
$mode = 'insert';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 기본값
$data = [
    'title' => '', 
    'job_type' => '정규직',
    'location' => 'Seoul, Korea', 
    'deadline' => '', 
    'status' => 'open',
    'content' => '',
    'tech_stack' => '',
    'salary' => '' // ★ 추가됨
];

// 수정 모드: 데이터 가져오기
if ($id > 0) {
    $mode = 'update';
    $stmt = $pdo->prepare("SELECT * FROM recruits WHERE id = ?");
    $stmt->execute([$id]);
    $fetch_data = $stmt->fetch();
    
    if($fetch_data) {
        $data = $fetch_data;
    } else {
        echo "<script>alert('Job posting not found.'); history.back();</script>";
        exit;
    }
}

// --- [POST 데이터 처리] ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $title = $_POST['title'] ?? '';
    $job_type = $_POST['job_type'] ?? '';
    $location = $_POST['location'] ?? '';
    $deadline = empty($_POST['deadline']) ? NULL : $_POST['deadline'];
    $status = $_POST['status'] ?? 'open';
    $content = $_POST['content'] ?? '';
    $tech_stack = $_POST['tech_stack'] ?? '';
    $salary = $_POST['salary'] ?? ''; // ★ 추가됨

    try {
        if ($mode == 'insert') {
            // ★ SQL 수정: salary 추가
            $sql = "INSERT INTO recruits (title, job_type, location, deadline, status, content, tech_stack, salary) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $job_type, $location, $deadline, $status, $content, $tech_stack, $salary]);
        } else {
            // ★ SQL 수정: salary 추가
            $sql = "UPDATE recruits SET 
                    title=?, job_type=?, location=?, deadline=?, status=?, content=?, tech_stack=?, salary=?
                    WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $job_type, $location, $deadline, $status, $content, $tech_stack, $salary, $id]);
        }

        echo "<script>alert('Saved successfully.'); location.href='recruit_list.php';</script>";
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('DB Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<script src="https://cdn.tiny.cloud/1/kqri7o2cv17ktehs2cxvenepb6sz91iooxgzglmhv11wkhi3/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="max-w-7xl mx-auto mb-20">
    <form method="POST">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo $mode == 'insert' ? 'Post New Job' : 'Edit Job Posting'; ?></h1>
                <p class="text-sm text-gray-500 mt-1">Create a new job opening for talent acquisition</p>
            </div>
            <div class="flex gap-3">
                <a href="recruit_list.php" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-black text-white rounded-lg text-sm font-bold hover:bg-[#0098FF] shadow-sm transition">
                    <?php echo $mode == 'insert' ? 'Publish Job' : 'Update Job'; ?>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Job Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" 
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-lg"
                           placeholder="e.g. Senior Product Designer" required>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Job Description</label>
                    <textarea id="recruitEditor" name="content"><?php echo $data['content']; ?></textarea>
                </div>

            </div>

            <div class="space-y-6">
                
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-lg focus:bg-white focus:border-gray-300 focus:ring-0 text-sm">
                        <option value="open" <?php if($data['status']=='open') echo 'selected'; ?>>Open</option>
                        <option value="closed" <?php if($data['status']=='closed') echo 'selected'; ?>>Closed</option>
                    </select>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Job Type</label>
                    <select name="job_type" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        <option value="정규직" <?php if($data['job_type']=='정규직') echo 'selected'; ?>>정규직</option>
                        <option value="비정규직" <?php if($data['job_type']=='비정규직') echo 'selected'; ?>>비정규직</option>
                        <option value="기간제" <?php if($data['job_type']=='기간제') echo 'selected'; ?>>기간제</option>
                        <option value="아르바이트" <?php if($data['job_type']=='아르바이트') echo 'selected'; ?>>아르바이트</option>
                    </select>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Tech Stack</label>
                    <div class="relative">
                        <i data-lucide="code-2" class="w-4 h-4 absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="tech_stack" value="<?php echo htmlspecialchars($data['tech_stack'] ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm"
                               placeholder="e.g. React, Node.js, Figma">
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Comma separated list</p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Location</label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="w-4 h-4 absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($data['location']); ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm"
                               placeholder="e.g. Seoul, Korea">
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Salary</label>
                    <div class="relative">
                        <i data-lucide="banknote" class="w-4 h-4 absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="salary" value="<?php echo htmlspecialchars($data['salary'] ?? ''); ?>"
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm"
                               placeholder="e.g. 협의, 5,000만원 ~">
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Deadline</label>
                    <input type="date" name="deadline" value="<?php echo $data['deadline']; ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm">
                    <p class="text-xs text-gray-400 mt-2">Leave blank for "Always Open"</p>
                </div>

            </div>
        </div>
    </form>
</div>

</main>

<script>
    tinymce.init({
        selector: '#recruitEditor',
        height: 500,
        menubar: true,
        plugins: 'image link lists table code help wordcount powerpaste',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code',
        content_style: "body { font-family: 'Inter', sans-serif; font-size: 16px; line-height: 1.6; }"
    });

    lucide.createIcons();
</script>
</body>
</html>