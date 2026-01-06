<?php
require_once '../inc/db_connect.php';

// 캐시 방지 헤더
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_GET['id'])) exit('No ID provided');
$id = (int)$_GET['id'];

// 데이터 조회
$stmt = $pdo->prepare("SELECT a.*, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE a.id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) exit('Applicant not found');

$status_map = [
    'pending' => ['text'=>'서류접수', 'class'=>'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'],
    'reviewing' => ['text'=>'서류검토', 'class'=>'bg-yellow-50 text-yellow-700 border border-yellow-100 hover:bg-yellow-100'],
    'interview' => ['text'=>'면접대기', 'class'=>'bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100'],
    'hired' => ['text'=>'합격', 'class'=>'bg-green-50 text-green-700 border border-green-100 hover:bg-green-100'],
    'rejected' => ['text'=>'불합격', 'class'=>'bg-red-50 text-red-700 border border-red-100 hover:bg-red-100'],
];

$file_count = 0;
if(!empty($app['resume_path'])) $file_count++;
if(!empty($app['portfolio_path'])) $file_count++;

// 호스트 정보
$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
?>

<input type="hidden" id="cal-name" value="<?php echo htmlspecialchars($app['name']); ?>">
<input type="hidden" id="cal-job" value="<?php echo htmlspecialchars($app['job_title']); ?>">
<input type="hidden" id="cal-email" value="<?php echo htmlspecialchars($app['email']); ?>">
<input type="hidden" id="cal-phone" value="<?php echo htmlspecialchars($app['phone']); ?>">
<input type="hidden" id="cal-link" value="<?php echo $host; ?>/adm/applicant_list.php">

<div class="flex flex-col lg:flex-row h-full">
    
    <div class="w-full lg:w-1/3 border-r border-gray-100 p-8 flex flex-col h-full bg-gray-50/50 overflow-y-auto scrollbar-hide shrink-0">
        
        <div class="text-center mb-6"> <div class="w-32 h-32 bg-white border border-gray-200 rounded-full mx-auto flex items-center justify-center mb-4 shadow-sm overflow-hidden relative">
                <?php if(!empty($app['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($app['profile_image']); ?>" class="w-full h-full object-cover" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <span class="text-4xl font-bold text-gray-300 absolute inset-0 hidden items-center justify-center bg-white">
                        <?php echo strtoupper(mb_substr($app['name'], 0, 1)); ?>
                    </span>
                <?php else: ?>
                    <span class="text-4xl font-bold text-gray-300">
                        <?php echo strtoupper(mb_substr($app['name'], 0, 1)); ?>
                    </span>
                <?php endif; ?>
            </div>

            <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($app['name']); ?></h2>
            <p class="text-sm text-blue-600 font-medium mt-0.5"><?php echo htmlspecialchars($app['job_title']); ?></p>
        </div>

        <div class="space-y-4 mb-8 text-sm bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Email</p>
                <p class="text-gray-900 font-medium break-all"><?php echo htmlspecialchars($app['email']); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Phone</p>
                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($app['phone']); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Applied Date</p>
                <p class="text-gray-900 font-medium"><?php echo date("Y-m-d", strtotime($app['applied_at'])); ?></p>
            </div>
        </div>

        <div class="mb-8">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-3 ml-1">Application Status</p>
            <div class="space-y-2">
                <?php foreach($status_map as $key => $val): ?>
                    <?php 
                        $isActive = ($app['status'] == $key);
                        $baseClass = $val['class']; 
                        $activeRing = $isActive ? 'ring-2 ring-black ring-offset-2 z-10 shadow-md transform scale-[1.02]' : 'opacity-60 hover:opacity-100 hover:scale-[1.01]';
                    ?>
                    <button onclick="updateStatus(<?php echo $app['id']; ?>, '<?php echo $key; ?>')" 
                            class="w-full text-left px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 <?php echo $baseClass . ' ' . $activeRing; ?>">
                        <?php echo $val['text']; ?>
                        <?php if($isActive): ?>
                            <i data-lucide="check" class="w-4 h-4 float-right mt-0.5"></i>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between mb-3 ml-1">
                <p class="text-xs text-gray-400 uppercase font-semibold">Attachments</p>
                <span class="text-xs font-bold bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full"><?php echo $file_count; ?></span>
            </div>
            
            <?php if($file_count > 0): ?>
                <a href="applicant_download_zip.php?id=<?php echo $app['id']; ?>" 
                   class="flex items-center justify-between w-full px-4 py-4 bg-white border border-gray-200 rounded-xl hover:border-black hover:shadow-md transition group">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-gray-100 transition">
                            <?php if($file_count > 1): ?>
                                <i data-lucide="archive" class="w-5 h-5 text-gray-600 group-hover:text-black"></i>
                            <?php else: ?>
                                <i data-lucide="file-text" class="w-5 h-5 text-gray-600 group-hover:text-black"></i>
                            <?php endif; ?>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 group-hover:text-black">
                                <?php echo $file_count > 1 ? 'Download All' : 'Download File'; ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?php echo $file_count > 1 ? '.ZIP Archive' : 'Single File'; ?>
                            </p>
                        </div>
                    </div>
                    <i data-lucide="download" class="w-4 h-4 text-gray-400 group-hover:text-black"></i>
                </a>
            <?php else: ?>
                <div class="w-full px-4 py-4 bg-gray-50 border border-transparent rounded-xl text-center">
                    <p class="text-sm text-gray-400">No attachments found.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-auto space-y-3">
            <a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" class="block w-full py-3 bg-black text-white text-center rounded-xl text-sm font-bold hover:bg-gray-800 transition shadow-lg shadow-gray-200">
                <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i> Send Email
            </a>
            
            <button onclick="toggleSchedule()" id="btn-schedule" class="block w-full py-3 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 transition">
                Schedule Interview
            </button>

            <div id="schedule-form" class="hidden bg-white border border-gray-200 p-4 rounded-xl shadow-sm mt-2 transition-all">
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">Select Interview Date</label>
                <input type="datetime-local" id="interview-date" class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-3 focus:outline-none focus:border-black">
                
                <button onclick="openGoogleCalendar()" class="w-full py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition flex items-center justify-center">
                    <i data-lucide="calendar-check" class="w-4 h-4 mr-2"></i> Open Calendar
                </button>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-2/3 flex flex-col h-full overflow-hidden bg-white">
        
        <div class="flex border-b border-gray-200 px-8 pt-8 shrink-0">
            <button onclick="switchTab('cover_letter')" id="tab_btn_cover_letter" class="tab-btn pb-4 px-2 mr-8 min-w-[120px] text-center text-sm font-bold text-black border-b-2 border-black transition-colors">Cover Letter</button>
            <button onclick="switchTab('motivation')" id="tab_btn_motivation" class="tab-btn pb-4 px-2 mr-8 min-w-[120px] text-center text-sm font-bold text-gray-400 hover:text-black border-b-2 border-transparent transition-colors">Motivation</button>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-hide p-8 relative">
            <div id="tab_content_cover_letter" class="tab-content h-full w-full">
                <div class="prose prose-sm w-full max-w-none text-gray-600 leading-relaxed whitespace-pre-line pb-10">
                    <?php echo $app['cover_letter'] ? htmlspecialchars($app['cover_letter']) : 'No cover letter provided.'; ?>
                </div>
            </div>
            <div id="tab_content_motivation" class="tab-content hidden h-full w-full">
                <div class="prose prose-sm w-full max-w-none text-gray-600 leading-relaxed whitespace-pre-line pb-10">
                    <?php echo $app['motivation'] ? htmlspecialchars($app['motivation']) : 'No motivation text provided.'; ?>
                </div>
            </div>
        </div>
    </div>
</div>