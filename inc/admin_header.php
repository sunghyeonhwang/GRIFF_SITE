<?php
// 세션이 없으면 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 로그인 체크: 세션이 없으면 로그인 페이지로 강제 이동
if (!isset($_SESSION['admin_id'])) {
    header("Location: /adm/login.php");
    exit;
}

// 현재 페이지 이름 확인 (메뉴 하이라이트용)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRIFF CMS Admin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="/asset/css/admin.css?v=<?php echo time(); ?>">

    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="flex h-screen bg-gray-50 overflow-hidden">

    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col hidden md:flex z-10 shrink-0">
        
        <div class="h-20 flex items-center justify-center border-b border-gray-100">
            <a href="/adm/dashboard.php" class="flex items-center hover:opacity-80 transition">
                <img src="/img/inc/logo_cms.svg" alt="GRIFF CMS" class="w-32 h-auto">
            </a>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto scrollbar-hide">
            
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo $current_page == 'dashboard.php' ? 'bg-black text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'; ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                Dashboard
            </a>

            <div class="pt-6 pb-2">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Content</p>
            </div>

            <a href="main_visual.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'visual') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="monitor-play" class="w-5 h-5 mr-3"></i>
                Main Visual
            </a>

            <a href="project_list.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'project') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3"></i>
                Project Manager
            </a>

            <a href="recruit_list.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'recruit') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="briefcase" class="w-5 h-5 mr-3"></i>
                Recruit / Jobs
            </a>
            
            <a href="applicant_list.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'applicant') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                Applicants
            </a>

            <div class="pt-6 pb-2">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Business</p>
            </div>

            <a href="studio_scheduler.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'studio') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="calendar" class="w-5 h-5 mr-3"></i>
                Studio Scheduler
            </a>

            <a href="inquiry_list.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo strpos($current_page, 'inquiry') !== false ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <i data-lucide="mail" class="w-5 h-5 mr-3"></i>
                Inquiries
            </a>
            
            <div class="pt-6 pb-2">
                <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">System</p>
            </div>

            <a href="settings.php" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors <?php echo ($current_page == 'settings.php') ? 'bg-black text-white shadow-md' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900'; ?>">
                <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                Settings
            </a>

        </nav>

        <div class="p-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center">
                <div class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-sm font-bold text-gray-700 shadow-sm">
                    <?php echo isset($_SESSION['admin_name']) ? strtoupper(substr($_SESSION['admin_name'], 0, 1)) : 'A'; ?>
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate"><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></p>
                    <a href="logout.php" class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center mt-0.5">
                        <i data-lucide="log-out" class="w-3 h-3 mr-1"></i> Log out
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-8 bg-gray-50/50">
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                lucide.createIcons();
            });
        </script>