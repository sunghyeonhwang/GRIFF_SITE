<?php
// 1. 에러 확인 및 DB 연결
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php'; 

// =================================================================
// [로직 처리]
// =================================================================

// 0. 초기 데이터 보장
$check = $pdo->query("SELECT count(*) FROM main_visuals WHERE id = 1")->fetchColumn();
if ($check == 0) {
    $pdo->exec("INSERT INTO main_visuals (id, bg_color, scroll_text_color) VALUES (1, '#000000', '#ffffff')");
}

// A. 메인 비주얼 설정 저장
if (isset($_POST['mode']) && $_POST['mode'] == 'save_visual') {
    try {
        $sql = "UPDATE main_visuals SET 
                video_url = ?, text_1 = ?, text_2 = ?, 
                text_3 = ?, text_4 = ?, bg_color = ?, 
                scroll_text_color = ? WHERE id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['video_url'], $_POST['text_1'], $_POST['text_2'], 
            $_POST['text_3'], $_POST['text_4'], $_POST['bg_color'], 
            $_POST['scroll_text_color']
        ]);
        echo "<script>alert('메인 비주얼 설정이 저장되었습니다.'); location.replace('main_visual.php');</script>";
    } catch (PDOException $e) { echo "<script>alert('DB Error');</script>"; }
    exit;
}

// B. 메타 설정 저장
if (isset($_POST['mode']) && $_POST['mode'] == 'save_meta') {
    try {
        if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] == 0) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/img/meta/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $ext = pathinfo($_FILES['og_image']['name'], PATHINFO_EXTENSION);
            $new_name = "og_" . time() . "." . $ext;
            
            if(move_uploaded_file($_FILES['og_image']['tmp_name'], $upload_dir . $new_name)) {
                $img_path = '/img/meta/' . $new_name;
                $pdo->prepare("UPDATE site_settings SET og_image = ? WHERE id = 1")->execute([$img_path]);
            }
        }
        $stmt = $pdo->prepare("UPDATE site_settings SET og_title = ?, og_desc = ? WHERE id = 1");
        $stmt->execute([$_POST['og_title'], $_POST['og_desc']]);
        
        echo "<script>alert('SEO 설정이 저장되었습니다.'); location.replace('main_visual.php');</script>";
    } catch (PDOException $e) { echo "<script>alert('DB Error');</script>"; }
    exit;
}

// C. 클라이언트 로고 멀티 업로드 (경로 수정됨)
if (isset($_POST['mode']) && $_POST['mode'] == 'upload_client') {
    // [수정] 파일 배열 처리 및 경로 변경
    if (isset($_FILES['client_logos'])) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/img/inc/client_logo/'; // 요청하신 경로
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true); // 폴더 없으면 생성
        
        $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
        $file_count = count($_FILES['client_logos']['name']);
        $success_count = 0;

        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['client_logos']['error'][$i] == 0) {
                $ext = strtolower(pathinfo($_FILES['client_logos']['name'][$i], PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed)) {
                    $new_name = "client_" . time() . "_" . $i . "_" . rand(100,999) . "." . $ext;
                    if(move_uploaded_file($_FILES['client_logos']['tmp_name'][$i], $upload_dir . $new_name)) {
                        // DB 저장
                        $pdo->prepare("INSERT INTO clients (title, logo_path) VALUES (?, ?)")
                            ->execute(['Partner', '/img/inc/client_logo/'.$new_name]);
                        $success_count++;
                    }
                }
            }
        }
        
        if($success_count > 0) {
            echo "<script>alert('{$success_count}개의 이미지가 업로드되었습니다.'); location.replace('main_visual.php');</script>";
        } else {
            echo "<script>alert('업로드할 파일이 없거나 오류가 발생했습니다.'); location.replace('main_visual.php');</script>";
        }
    }
    exit;
}

// D. 클라이언트 삭제
if (isset($_GET['del_client'])) {
    $id = (int)$_GET['del_client'];
    $path = $pdo->query("SELECT logo_path FROM clients WHERE id = $id")->fetchColumn();
    if($path && file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
        @unlink($_SERVER['DOCUMENT_ROOT'].$path);
    }
    $pdo->prepare("DELETE FROM clients WHERE id = ?")->execute([$id]);
    echo "<script>location.replace('main_visual.php');</script>"; exit;
}

// [데이터 조회]
$visual = $pdo->query("SELECT * FROM main_visuals WHERE id = 1")->fetch();
$meta = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();
$clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();

require_once '../inc/admin_header.php';
?>

<style>
    /* 색상 선택 커스텀 UI */
    .color-input-group { 
        display: flex; 
        align-items: center; 
        border: 1px solid #e5e7eb; 
        padding: 4px; 
        border-radius: 8px; 
        background: #fff; 
        width: 100%;
    }
    .color-swatch { 
        width: 36px; 
        height: 36px; 
        border-radius: 6px; 
        border: 1px solid #ddd; 
        overflow: hidden; 
        position: relative; 
        flex-shrink: 0;
        cursor: pointer;
    }
    .color-picker-hidden { 
        position: absolute; 
        top: -10px; left: -10px; 
        width: 200%; height: 200%; 
        opacity: 0; 
        cursor: pointer; 
    }
    .color-text {
        flex: 1;
        border: none;
        outline: none;
        padding: 0 10px;
        font-family: monospace;
        font-size: 14px;
        color: #333;
        text-transform: uppercase;
    }
</style>

<div class="max-w-7xl mx-auto pb-20">
    
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Main Visual & Settings</h1>
            <p class="text-sm text-gray-500 mt-1">메인 페이지의 히어로 섹션 비주얼과 브랜드 로고, 검색 최적화(SEO)를 관리합니다.</p>
        </div>
        <a href="/" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
            <i data-lucide="external-link" class="w-4 h-4 mr-2"></i> 사이트 미리보기
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="font-bold text-gray-800 flex items-center">
                        <i data-lucide="monitor-play" class="w-5 h-5 mr-2 text-blue-600"></i> Hero Visual
                    </h2>
                    <span class="text-xs font-medium text-gray-400">Section 1</span>
                </div>
                
                <form method="post" class="p-6">
                    <input type="hidden" name="mode" value="save_visual">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Background Video URL</label>
                        <div class="flex gap-2">
                            <input type="text" name="video_url" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent outline-none transition" 
                                   value="<?= htmlspecialchars($visual['video_url']) ?>" placeholder="https://example.com/video.mp4">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">MP4 또는 WebM 형식의 고화질 영상 URL을 입력하세요. (권장: 1920x1080)</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Line 1 (Script)</label>
                            <input type="text" name="text_1" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-black outline-none transition" value="<?= htmlspecialchars($visual['text_1']) ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Line 2 (Outline)</label>
                            <input type="text" name="text_2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-black outline-none transition" value="<?= htmlspecialchars($visual['text_2']) ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Line 3 (Stroke)</label>
                            <input type="text" name="text_3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-black outline-none transition" value="<?= htmlspecialchars($visual['text_3']) ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Line 4 (Solid)</label>
                            <input type="text" name="text_4" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-2 focus:ring-black outline-none transition" value="<?= htmlspecialchars($visual['text_4']) ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pt-6 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Background Color</label>
                            <div class="color-input-group">
                                <div class="color-swatch" style="background-color: <?= $visual['bg_color'] ?>;">
                                    <input type="color" name="bg_color" class="color-picker-hidden" value="<?= $visual['bg_color'] ?>" oninput="this.parentElement.style.backgroundColor = this.value; this.parentElement.nextElementSibling.value = this.value;">
                                </div>
                                <input type="text" class="color-text" value="<?= $visual['bg_color'] ?>" oninput="this.previousElementSibling.firstElementChild.value = this.value; this.previousElementSibling.style.backgroundColor = this.value;">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Scroll Text Color</label>
                            <div class="color-input-group">
                                <div class="color-swatch" style="background-color: <?= $visual['scroll_text_color'] ?>;">
                                    <input type="color" name="scroll_text_color" class="color-picker-hidden" value="<?= $visual['scroll_text_color'] ?>" oninput="this.parentElement.style.backgroundColor = this.value; this.parentElement.nextElementSibling.value = this.value;">
                                </div>
                                <input type="text" class="color-text" value="<?= $visual['scroll_text_color'] ?>" oninput="this.previousElementSibling.firstElementChild.value = this.value; this.previousElementSibling.style.backgroundColor = this.value;">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition shadow-lg">
                            변경사항 저장
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h2 class="font-bold text-gray-800 flex items-center">
                        <i data-lucide="users" class="w-5 h-5 mr-2 text-blue-600"></i> Client Logos
                    </h2>
                    <span class="text-xs font-medium text-gray-400"><?= count($clients) ?> Partners</span>
                </div>
                
                <div class="p-6">
                    <form method="post" enctype="multipart/form-data" class="mb-8">
                        <input type="hidden" name="mode" value="upload_client">
                        <div class="flex items-center gap-4 p-8 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition relative justify-center flex-col text-center">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center border border-gray-200 shadow-sm mb-2">
                                <i data-lucide="upload-cloud" class="w-6 h-6 text-blue-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-700">여기를 클릭하여 이미지를 선택하세요</p>
                                <p class="text-xs text-gray-400 mt-1">SVG, PNG, JPG / 여러 장 선택 가능 (Multi-upload)</p>
                            </div>
                            <input type="file" name="client_logos[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="this.form.submit()">
                        </div>
                    </form>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach($clients as $client): ?>
                        <div class="group relative bg-white border border-gray-100 rounded-xl h-24 flex items-center justify-center hover:shadow-md hover:border-gray-300 transition-all p-4">
                            <img src="<?= $client['logo_path'] ?>" class="max-w-full max-h-full object-contain opacity-60 group-hover:opacity-100 transition duration-300 grayscale group-hover:grayscale-0">
                            
                            <a href="?del_client=<?= $client['id'] ?>" class="absolute top-1 right-1 p-1.5 bg-gray-100 text-gray-400 rounded-full opacity-0 group-hover:opacity-100 transition hover:bg-red-500 hover:text-white" onclick="return confirm('이 클라이언트를 삭제하시겠습니까?');">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-bold text-gray-800 flex items-center">
                        <i data-lucide="search" class="w-5 h-5 mr-2 text-blue-600"></i> SEO & Meta
                    </h2>
                </div>

                <form method="post" enctype="multipart/form-data" class="p-6">
                    <input type="hidden" name="mode" value="save_meta">
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">OG Title</label>
                            <input type="text" name="og_title" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-1 focus:ring-black outline-none" value="<?= htmlspecialchars($meta['og_title'] ?? '') ?>">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">OG Description</label>
                            <textarea name="og_desc" rows="3" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:ring-1 focus:ring-black outline-none resize-none"><?= htmlspecialchars($meta['og_desc'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Share Image</label>
                            <div class="relative group">
                                <div class="w-full aspect-video bg-gray-100 rounded-lg border border-gray-200 overflow-hidden flex items-center justify-center relative">
                                    <?php if(!empty($meta['og_image'])): ?>
                                        <img src="<?= $meta['og_image'] ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i data-lucide="image" class="w-8 h-8 text-gray-300"></i>
                                    <?php endif; ?>
                                    
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition cursor-pointer">
                                        <p class="text-white text-xs font-bold flex items-center">
                                            <i data-lucide="upload" class="w-3 h-3 mr-1"></i> 변경하기
                                        </p>
                                    </div>
                                    <input type="file" name="og_image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-400 mb-2">PREVIEW</p>
                            <div class="bg-gray-100 rounded-lg p-3 max-w-[280px] mx-auto">
                                <div class="bg-white rounded overflow-hidden shadow-sm">
                                    <div class="h-24 bg-gray-200 overflow-hidden">
                                        <?php if(!empty($meta['og_image'])): ?>
                                            <img src="<?= $meta['og_image'] ?>" class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-2">
                                        <div class="h-3 w-3/4 bg-gray-800 rounded mb-1 text-[10px] leading-tight font-bold text-gray-800 truncate">
                                            <?= htmlspecialchars($meta['og_title'] ?: '사이트 제목') ?>
                                        </div>
                                        <div class="h-2 w-full bg-gray-300 rounded mb-1 text-[9px] text-gray-500 line-clamp-2">
                                            <?= htmlspecialchars($meta['og_desc'] ?: '사이트 설명이 여기에 표시됩니다.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-black transition">
                            설정 저장
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    lucide.createIcons();
</script>

</main>
</body>
</html>