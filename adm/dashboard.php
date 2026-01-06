<?php
// 에러 확인용
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
require_once '../inc/admin_header.php';

// --- [데이터 카운트 집계] ---

// 1. 신규 지원자 (applicants 테이블 / status = 'pending')
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM applicants WHERE status = 'pending'");
    $new_applicants = $stmt->fetchColumn();
} catch (Exception $e) {
    $new_applicants = 0; // 테이블이 없거나 에러 시 0 처리
}

// 2. 진행중 프로젝트 (projects 테이블이 있다면)
try {
    // projects 테이블 구조를 모르므로 일단 전체 개수나 예외처리
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects"); 
    $active_projects = $stmt->fetchColumn();
} catch (Exception $e) {
    $active_projects = '-'; // 테이블 아직 안만들었으면 - 표시
}

// 3. 안 읽은 문의 (inquiries 테이블 / status = 'new')
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
    $unread_inquiries = $stmt->fetchColumn();
} catch (Exception $e) {
    $unread_inquiries = 0;
}

// 4. 오늘 스튜디오 예약 (studio_bookings / start_date가 오늘인 것)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM studio_bookings WHERE DATE(start_date) = CURDATE() AND status = 'confirmed'");
    $today_studio = $stmt->fetchColumn();
} catch (Exception $e) {
    $today_studio = 0;
}

// --- [하단 리스트 데이터] ---

// 1. 최근 문의 5개
try {
    $recent_inquiries = $pdo->query("SELECT * FROM inquiries ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_inquiries = [];
}

// 2. 다가오는 스튜디오 일정 5개
try {
    $upcoming_bookings = $pdo->query("SELECT * FROM studio_bookings WHERE start_date >= CURDATE() AND status = 'confirmed' ORDER BY start_date ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $upcoming_bookings = [];
}
?>

<div class="max-w-7xl mx-auto pb-20">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, Admin!</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="applicant_list.php" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 transition group">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-gray-500 uppercase">New Applicants</h3>
                <div class="p-2 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-900"><?php echo $new_applicants; ?></p>
        </a>

        <a href="project_list.php" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-purple-300 transition group">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-gray-500 uppercase">Total Projects</h3>
                <div class="p-2 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition"><i data-lucide="folder-open" class="w-5 h-5 text-purple-600"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-900"><?php echo $active_projects; ?></p>
        </a>

        <a href="inquiry_list.php" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-red-300 transition group">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-gray-500 uppercase">New Inquiries</h3>
                <div class="p-2 bg-red-50 rounded-lg group-hover:bg-red-100 transition"><i data-lucide="mail" class="w-5 h-5 text-red-600"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-900"><?php echo $unread_inquiries; ?></p>
        </a>

        <a href="studio_scheduler.php" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 hover:border-green-300 transition group">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xs font-bold text-gray-500 uppercase">Today's Bookings</h3>
                <div class="p-2 bg-green-50 rounded-lg group-hover:bg-green-100 transition"><i data-lucide="video" class="w-5 h-5 text-green-600"></i></div>
            </div>
            <p class="text-3xl font-bold text-gray-900"><?php echo $today_studio; ?></p>
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">Recent Inquiries</h3>
                <a href="inquiry_list.php" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
            </div>
            <div class="p-2 flex-1">
                <?php if(count($recent_inquiries) > 0): ?>
                    <ul class="divide-y divide-gray-50">
                        <?php foreach($recent_inquiries as $row): ?>
                        <li class="px-4 py-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($row['name']); ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1"><?php echo htmlspecialchars($row['message']); ?></p>
                                </div>
                                <span class="text-[10px] font-medium text-gray-400 whitespace-nowrap ml-2">
                                    <?php echo date('m.d', strtotime($row['created_at'])); ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="h-40 flex items-center justify-center text-sm text-gray-400">No new messages.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">Upcoming Schedule</h3>
                <a href="studio_scheduler.php" class="text-xs font-bold text-blue-600 hover:underline">View Calendar</a>
            </div>
            <div class="p-2 flex-1">
                <?php if(count($upcoming_bookings) > 0): ?>
                    <ul class="divide-y divide-gray-50">
                        <?php foreach($upcoming_bookings as $row): ?>
                        <li class="px-4 py-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-100 flex flex-col items-center justify-center flex-shrink-0">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase"><?php echo date('M', strtotime($row['start_date'])); ?></span>
                                    <span class="text-sm font-bold text-gray-900"><?php echo date('d', strtotime($row['start_date'])); ?></span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate"><?php echo htmlspecialchars($row['client_name']); ?></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500"><?php echo htmlspecialchars($row['service_type']); ?></span>
                                        <span class="text-[10px] px-1.5 py-0.5 bg-gray-100 rounded text-gray-500"><?php echo date('H:i', strtotime($row['start_date'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="h-40 flex items-center justify-center text-sm text-gray-400">No upcoming bookings.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</main>
<script>
    lucide.createIcons();
</script>
</body>
</html>