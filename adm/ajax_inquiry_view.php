<?php
require_once '../inc/db_connect.php';

if (!isset($_GET['id'])) exit;
$id = (int)$_GET['id'];

// 상태가 'new'라면 'read'로 업데이트
$pdo->query("UPDATE inquiries SET status = 'read' WHERE id = $id AND status = 'new'");

// 데이터 조회
$stmt = $pdo->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) exit('Not found');

$is_solved = ($row['status'] == 'solved');

// 날짜 포맷팅
$timestamp = strtotime($row['created_at']);
$week_kr = array("일", "월", "화", "수", "목", "금", "토");
$weekday = $week_kr[date("w", $timestamp)];
$date_str = date("Y년 m월 d일", $timestamp) . " (" . $weekday . ")";
$time_str = date("A h:i", $timestamp);
?>

<input type="hidden" id="current_inquiry_id" value="<?php echo $row['id']; ?>">

<div class="flex flex-col h-full relative">
    
    <div class="px-10 py-8 shrink-0">
        <h1 class="text-3xl font-bold text-gray-900 mb-6 leading-tight">
            <?php echo htmlspecialchars($row['subject'] ?? 'No Subject'); ?>
        </h1>
        
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-lg font-bold text-gray-500 mr-4">
                    <?php echo strtoupper(mb_substr($row['name'], 0, 1)); ?>
                </div>
                <div>
                    <p class="text-base font-bold text-gray-900">
                        <?php echo htmlspecialchars($row['name']); ?>
                        <?php if($row['company']): ?>
                            <span class="text-gray-400 font-normal ml-1">from <?php echo htmlspecialchars($row['company']); ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="text-sm text-gray-500 hover:text-blue-600 transition cursor-pointer" onclick="navigator.clipboard.writeText('<?php echo $row['email']; ?>'); alert('Copied!');">
                        <?php echo htmlspecialchars($row['email']); ?>
                    </p>
                </div>
            </div>
            
            <div class="text-right text-sm text-gray-400">
                <p class="font-bold text-gray-600"><?php echo $date_str; ?></p>
                <p class="text-xs mt-1"><?php echo $time_str; ?></p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-10 pb-10 scrollbar-hide">
        <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100 min-h-[200px] mb-6">
            <div class="prose prose-lg max-w-none text-gray-800 leading-loose whitespace-pre-line font-medium">
                <?php echo htmlspecialchars($row['message']); ?>
            </div>
            <?php if(!empty($row['phone'])): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Contact Phone</p>
                    <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($row['phone']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if(!empty($row['reply_content'])): ?>
            <div class="relative">
                <div class="absolute left-8 -top-6 w-0.5 h-6 bg-gray-200"></div> <div class="bg-blue-50 rounded-2xl p-8 border border-blue-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-blue-800 flex items-center">
                            <i data-lucide="corner-down-right" class="w-4 h-4 mr-2"></i> Sent Reply
                        </h3>
                        <span class="text-xs text-blue-400">
                            <?php echo date("Y년 m월 d일 A h:i", strtotime($row['replied_at'])); ?>
                        </span>
                    </div>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        <?php echo $row['reply_content']; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="px-10 py-6 border-t border-gray-100 bg-white flex items-center justify-between shrink-0 relative z-10">
        <div class="flex gap-3">
            <button onclick="toggleReplyForm()" 
                    class="flex items-center px-5 py-2.5 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition">
                <i data-lucide="reply" class="w-4 h-4 mr-2"></i> Write a Reply
            </button>
            
            <button onclick="deleteInquiry(<?php echo $row['id']; ?>)" 
                    class="flex items-center px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-bold hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete
            </button>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-gray-700">Mark as Solved</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" 
                       onchange="toggleSolved(<?php echo $row['id']; ?>, this)"
                       <?php echo $is_solved ? 'checked' : ''; ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
            </label>
        </div>
    </div>

    <div id="reply-overlay" onclick="toggleReplyForm()" class="hidden absolute inset-0 bg-black/20 backdrop-blur-sm z-20 transition-opacity"></div>
    
    <div id="reply-container" class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] transform translate-y-full transition-transform duration-300 z-30 flex flex-col h-[70%] border-t border-gray-200">
        
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-2xl">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i data-lucide="corner-up-left" class="w-5 h-5 mr-2 text-gray-500"></i> Reply to <?php echo htmlspecialchars($row['name']); ?>
            </h3>
            <button onclick="toggleReplyForm()" class="p-2 hover:bg-gray-200 rounded-full transition text-gray-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="flex-1 p-6 flex flex-col gap-4 overflow-y-auto">
            <div class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">To</label>
                    <input type="text" id="reply-to" value="<?php echo htmlspecialchars($row['email']); ?>" readonly 
                           class="w-full bg-gray-100 border-transparent rounded-lg text-sm text-gray-600 cursor-not-allowed">
                </div>
                <div class="flex-[2]">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Subject</label>
                    <input type="text" id="reply-subject" value="Re: <?php echo htmlspecialchars($row['subject'] ?? 'Inquiry'); ?>" 
                           class="w-full bg-white border-gray-200 rounded-lg text-sm focus:border-black focus:ring-0">
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Message</label>
                <textarea id="reply-message" class="flex-1 w-full p-4 bg-white border border-gray-200 rounded-xl text-base leading-relaxed focus:border-black focus:ring-0 resize-none placeholder-gray-300" 
                          placeholder="Write your reply here..."></textarea>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-white flex justify-end gap-3">
            <button onclick="toggleReplyForm()" class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-800 transition">Cancel</button>
            <button onclick="sendEmail()" id="btn-send-mail" class="px-8 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100 flex items-center">
                <i data-lucide="send" class="w-4 h-4 mr-2"></i> Send Reply
            </button>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>