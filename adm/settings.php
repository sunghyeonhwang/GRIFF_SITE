<?php
// 에러 확인용 (운영 시 0)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
require_once '../inc/admin_header.php';

// [1] 관리자 정보 조회
$session_id = $_SESSION['admin_id'] ?? 0;
$admin_info = ['username' => 'Unknown'];

if ($session_id) {
    $stmt_admin = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt_admin->execute([$session_id]);
    $fetched_admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);
    if ($fetched_admin) $admin_info = $fetched_admin;
}

// [2] 사이트 설정 데이터 조회
$stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    // 없으면 기본값으로 생성
    $pdo->exec("INSERT INTO site_settings (id) VALUES (1)");
    header("Refresh:0");
    exit;
}

// [3] POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    try {
        // A. 일반 설정 (General)
        if ($mode === 'update_general') {
            // [수정] DB 컬럼명에 맞춰 footer_copyright 사용
            $sql = "UPDATE site_settings SET 
                    site_name=?, contact_email=?, footer_copyright=?, instagram_url=?, youtube_url=? 
                    WHERE id=1";
            $pdo->prepare($sql)->execute([
                $_POST['site_name'], $_POST['contact_email'], $_POST['footer_copyright'], 
                $_POST['instagram_url'], $_POST['youtube_url']
            ]);
            echo "<script>alert('일반 설정이 저장되었습니다.'); location.replace('settings.php');</script>";
        }
        // B. 관리자 비밀번호 (Admin)
        else if ($mode === 'update_admin') {
            $current_pw = $_POST['current_pw'];
            $new_pw     = $_POST['new_pw'];
            $confirm_pw = $_POST['confirm_pw'];

            if ($admin_info && password_verify($current_pw, $admin_info['password'])) {
                if ($new_pw === $confirm_pw) {
                    if (strlen($new_pw) < 4) {
                        echo "<script>alert('비밀번호는 4자리 이상이어야 합니다.'); history.back();</script>"; exit;
                    }
                    $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$new_hash, $session_id]);
                    echo "<script>alert('비밀번호가 변경되었습니다. 다시 로그인해주세요.'); location.href='/adm/logout.php';</script>";
                } else {
                    echo "<script>alert('새 비밀번호가 일치하지 않습니다.'); history.back();</script>";
                }
            } else {
                echo "<script>alert('현재 비밀번호가 틀렸습니다.'); history.back();</script>";
            }
        }
        // C. SEO & OG 설정 (통합)
        else if ($mode === 'update_seo') {
            // 변수 정리
            $seo_title = $_POST['seo_title'];
            $seo_description = $_POST['seo_description'];
            $seo_keywords = $_POST['seo_keywords'];
            
            $og_title = $_POST['og_title'];
            $og_desc = $_POST['og_desc'];
            
            // 이미지 업로드 처리
            $og_image_path = null;
            if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] == 0) {
                $upload_dir = '../img/meta/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $ext = pathinfo($_FILES['og_image']['name'], PATHINFO_EXTENSION);
                $new_name = "og_" . time() . "." . $ext;
                
                if (move_uploaded_file($_FILES['og_image']['tmp_name'], $upload_dir . $new_name)) {
                    $og_image_path = '/img/meta/' . $new_name;
                }
            }

            // SQL 실행 (이미지 변경 여부에 따라 분기)
            if ($og_image_path) {
                $sql = "UPDATE site_settings SET 
                        seo_title=?, seo_description=?, seo_keywords=?, 
                        og_title=?, og_desc=?, og_image=? 
                        WHERE id=1";
                $pdo->prepare($sql)->execute([$seo_title, $seo_description, $seo_keywords, $og_title, $og_desc, $og_image_path]);
            } else {
                $sql = "UPDATE site_settings SET 
                        seo_title=?, seo_description=?, seo_keywords=?, 
                        og_title=?, og_desc=? 
                        WHERE id=1";
                $pdo->prepare($sql)->execute([$seo_title, $seo_description, $seo_keywords, $og_title, $og_desc]);
            }
            echo "<script>alert('SEO/OG 설정이 저장되었습니다.'); location.replace('settings.php');</script>";
        }

    } catch (Exception $e) {
        echo "<script>alert('오류 발생: " . addslashes($e->getMessage()) . "'); history.back();</script>";
    }
    exit;
}
?>

<div class="max-w-4xl mx-auto pb-20 pt-10 px-6">
    
    <div class="mb-10">
        <h1 class="font-eng text-3xl font-bold text-gray-900">Settings</h1>
        <p class="font-kor text-sm text-gray-500 mt-2">사이트 기본 정보, 관리자 계정, SEO 설정을 통합 관리합니다.</p>
    </div>

    <div class="flex border-b border-gray-200 mb-8 font-eng">
        <button onclick="openTab('general')" id="tab-btn-general" class="tab-btn px-6 py-3 text-sm font-bold text-black border-b-2 border-black flex items-center gap-2 transition-colors">
            <i data-lucide="settings-2" class="w-4 h-4"></i> General
        </button>
        <button onclick="openTab('admin')" id="tab-btn-admin" class="tab-btn px-6 py-3 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-black flex items-center gap-2 transition-colors">
            <i data-lucide="user" class="w-4 h-4"></i> Admin
        </button>
        <button onclick="openTab('seo')" id="tab-btn-seo" class="tab-btn px-6 py-3 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-black flex items-center gap-2 transition-colors">
            <i data-lucide="globe" class="w-4 h-4"></i> SEO & Meta
        </button>
    </div>

    <div id="tab-content-general" class="tab-content space-y-6">
        <form method="POST">
            <input type="hidden" name="mode" value="update_general">
            
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Site Info</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Website Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Official Email</label>
                        <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Footer Copyright</label>
                        <input type="text" name="footer_copyright" value="<?php echo htmlspecialchars($settings['footer_copyright'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Social Links</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Instagram URL</label>
                        <div class="relative">
                            <i data-lucide="instagram" class="w-4 h-4 absolute left-3 top-3.5 text-gray-400"></i>
                            <input type="text" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">YouTube URL</label>
                        <div class="relative">
                            <i data-lucide="youtube" class="w-4 h-4 absolute left-3 top-3.5 text-gray-400"></i>
                            <input type="text" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition shadow-lg">Save Changes</button>
            </div>
        </form>
    </div>

    <div id="tab-content-admin" class="tab-content hidden space-y-6">
        <form method="POST">
            <input type="hidden" name="mode" value="update_admin">
            
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Change Password</h2>
                
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-100 flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center border border-gray-200">
                        <i data-lucide="user" class="w-5 h-5 text-gray-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase">Current Admin</p>
                        <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($admin_info['username']); ?></p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Current Password</label>
                        <input type="password" name="current_pw" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">New Password</label>
                        <input type="password" name="new_pw" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_pw" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition shadow-lg">Update Password</button>
            </div>
        </form>
    </div>

    <div id="tab-content-seo" class="tab-content hidden space-y-6">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="mode" value="update_seo">
            
            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100 flex items-center gap-2">
                    <i data-lucide="search" class="w-5 h-5 text-blue-600"></i> Search Engine Optimization
                </h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Browser Title (SEO)</label>
                        <input type="text" name="seo_title" value="<?php echo htmlspecialchars($settings['seo_title'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">브라우저 탭에 표시되는 제목입니다.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Meta Description</label>
                        <textarea name="seo_description" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none resize-none"><?php echo htmlspecialchars($settings['seo_description'] ?? ''); ?></textarea>
                        <p class="text-xs text-gray-400 mt-1">구글 등 검색 결과에 표시되는 설명입니다.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Keywords</label>
                        <input type="text" name="seo_keywords" value="<?php echo htmlspecialchars($settings['seo_keywords'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">콤마(,)로 구분하여 입력하세요.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100 flex items-center gap-2">
                    <i data-lucide="share-2" class="w-5 h-5 text-green-600"></i> Social Sharing (Open Graph)
                </h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">OG Title</label>
                        <input type="text" id="og_title" name="og_title" value="<?php echo htmlspecialchars($settings['og_title'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none transition" oninput="updatePreview()">
                        <p class="text-xs text-gray-400 mt-1">카톡/페이스북 공유 시 표시되는 제목입니다.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">OG Description</label>
                        <textarea id="og_desc" name="og_desc" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:border-black focus:ring-1 focus:ring-black outline-none resize-none" oninput="updatePreview()"><?php echo htmlspecialchars($settings['og_desc'] ?? ''); ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Share Image (OG Image)</label>
                        <div class="flex items-start gap-6">
                            <div class="w-40 h-24 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden flex-shrink-0 relative group">
                                <?php if(!empty($settings['og_image'])): ?>
                                    <img src="<?php echo $settings['og_image']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <i data-lucide="image" class="w-8 h-8"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="og_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                                <p class="text-xs text-gray-400 mt-2">* 권장 사이즈: 1200x630px (JPG/PNG)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-xl border border-gray-200 shadow-sm mb-6">
                <h2 class="font-eng text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Preview</h2>
                <div class="max-w-sm mx-auto bg-[#F7E600] p-3 rounded-lg shadow-sm">
                    <div class="bg-white rounded overflow-hidden">
                        <div class="h-32 bg-gray-200 overflow-hidden relative">
                            <?php if(!empty($settings['og_image'])): ?>
                                <img src="<?php echo $settings['og_image']; ?>" class="w-full h-full object-cover opacity-80">
                            <?php endif; ?>
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">OG Image Area</div>
                        </div>
                        <div class="p-3">
                            <h3 id="preview_title" class="font-bold text-sm text-black truncate mb-1">
                                <?php echo htmlspecialchars($settings['og_title'] ?? 'GRIFF'); ?>
                            </h3>
                            <p id="preview_desc" class="text-xs text-gray-500 line-clamp-2">
                                <?php echo htmlspecialchars($settings['og_desc'] ?? 'Creative Studio'); ?>
                            </p>
                            <p class="text-[10px] text-gray-300 mt-2">griff.kr</p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-xs text-gray-400 mt-4">카카오톡 공유 예시 화면입니다.</p>
            </div>

            <div class="text-right">
                <button type="submit" class="px-6 py-3 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition shadow-lg">Save Config</button>
            </div>
        </form>
    </div>

</div>

</main>

<script>
    lucide.createIcons();

    // 탭 전환
    function openTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-black', 'border-black');
            btn.classList.add('text-gray-400', 'border-transparent');
        });
        
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        activeBtn.classList.remove('text-gray-400', 'border-transparent');
        activeBtn.classList.add('text-black', 'border-black');

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById('tab-content-' + tabName).classList.remove('hidden');
    }

    // 미리보기 실시간 업데이트
    function updatePreview() {
        const t = document.getElementById('og_title').value;
        const d = document.getElementById('og_desc').value;
        document.getElementById('preview_title').innerText = t || 'Title';
        document.getElementById('preview_desc').innerText = d || 'Description...';
    }
</script>
</body>
</html>