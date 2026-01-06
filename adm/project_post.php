<?php
// 에러 확인용
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../inc/db_connect.php';
require_once '../inc/admin_header.php';

// ★ [템플릿 파일 로드]
$template_file = dirname(__FILE__) . '/inc/editor_templates.php';
if (file_exists($template_file)) {
    require_once $template_file;
} else {
    // 파일이 없을 경우 기본 빈 배열 (에러 방지)
    $editor_templates = [];
}

// --- [이미지 업로드 함수] ---
function uploadImage($file) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        if (isset($file['error']) && $file['error'] !== 4) {
             echo "<script>alert('업로드 에러 발생 코드: {$file['error']}');</script>";
        }
        return false;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        echo "<script>alert('지원하지 않는 형식: {$file['name']}');</script>";
        return false;
    }
    $filename = 'img_' . time() . '_' . rand(1000,9999) . '.' . $ext;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/img/uploads/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            echo "<script>alert('폴더 생성 실패! FTP 권한을 확인해주세요.');</script>";
            return false;
        }
    }
    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return '/img/uploads/' . $filename;
    } else {
        echo "<script>alert('파일 이동 실패. 권한 문제일 수 있습니다.');</script>";
        return false;
    }
}

// --- [초기화] ---
$mode = 'insert';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$data = [
    'title' => '', 'video_url' => '', 'content' => '', 'status' => 'draft',
    'category' => '', 'client_name' => '', 'completion_date' => '', 
    'external_link' => '', 'thumbnail_path' => ''
];
$gallery_images = []; 

// 수정 모드
if ($id > 0) {
    $mode = 'update';
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $fetch_data = $stmt->fetch();
    if($fetch_data) {
        $data = $fetch_data;
        $stmt_img = $pdo->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY id ASC");
        $stmt_img->execute([$id]);
        $gallery_images = $stmt_img->fetchAll();
    } else {
        echo "<script>alert('존재하지 않는 프로젝트입니다.'); history.back();</script>";
        exit;
    }
}

// 갤러리 개별 삭제
if (isset($_GET['del_img']) && $id > 0) {
    $img_id = (int)$_GET['del_img'];
    $stmt_path = $pdo->prepare("SELECT image_path FROM project_images WHERE id = ?");
    $stmt_path->execute([$img_id]);
    $del_path = $stmt_path->fetchColumn();
    if($del_path) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $del_path;
        if(file_exists($full_path)) @unlink($full_path);
    }
    $stmt = $pdo->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?");
    $stmt->execute([$img_id, $id]);
    echo "<script>location.href='project_post.php?id=$id';</script>";
    exit;
}

// --- [POST 처리] ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $category = $_POST['category'] ?? '';
    $client_name = $_POST['client_name'] ?? '';
    $completion_date = empty($_POST['completion_date']) ? NULL : $_POST['completion_date'];
    $external_link = $_POST['external_link'] ?? '';
    $thumbnail_path = $data['thumbnail_path'];
    
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $uploaded = uploadImage($_FILES['thumbnail']);
        if ($uploaded) $thumbnail_path = $uploaded;
    }

    try {
        if ($mode == 'insert') {
            $sql = "INSERT INTO projects (title, video_url, content, status, category, client_name, completion_date, external_link, thumbnail_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $video_url, $content, $status, $category, $client_name, $completion_date, $external_link, $thumbnail_path]);
            $project_id = $pdo->lastInsertId();
        } else {
            $sql = "UPDATE projects SET 
                    title=?, video_url=?, content=?, status=?, category=?, client_name=?, completion_date=?, external_link=?, thumbnail_path=? 
                    WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $video_url, $content, $status, $category, $client_name, $completion_date, $external_link, $thumbnail_path, $id]);
            $project_id = $id;
        }

        if (isset($_FILES['gallery_images'])) {
            $total = count($_FILES['gallery_images']['name']);
            if ($total > 0 && !empty($_FILES['gallery_images']['name'][0])) {
                for ($i = 0; $i < $total; $i++) {
                    if ($_FILES['gallery_images']['error'][$i] == 0) {
                        $file_item = [
                            'name' => $_FILES['gallery_images']['name'][$i],
                            'type' => $_FILES['gallery_images']['type'][$i],
                            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                            'error' => $_FILES['gallery_images']['error'][$i],
                            'size' => $_FILES['gallery_images']['size'][$i],
                        ];
                        $uploaded_gallery = uploadImage($file_item);
                        if ($uploaded_gallery) {
                            $stmt_g = $pdo->prepare("INSERT INTO project_images (project_id, image_path) VALUES (?, ?)");
                            $stmt_g->execute([$project_id, $uploaded_gallery]);
                        }
                    }
                }
            }
        }
        echo "<script>alert('저장되었습니다.'); location.href='project_list.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('DB Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<script src="https://cdn.tiny.cloud/1/kqri7o2cv17ktehs2cxvenepb6sz91iooxgzglmhv11wkhi3/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
    .tox-tinymce { height: 900px !important; }
    
    /* 작성 가이드 스타일 */
    .guide-box { 
        background-color: #f8fafc; 
        border: 1px solid #e2e8f0; 
        border-radius: 0.5rem; 
        padding: 0.75rem; 
        margin-bottom: 1rem; 
    }
    .guide-title { 
        font-weight: 700; 
        color: #1e293b; 
        margin-bottom: 0.4rem; 
        display: flex; 
        align-items: center; 
        gap: 0.4rem; 
        font-size: 0.85rem; 
    }
    .guide-list { 
        list-style: none; 
        padding: 0; 
        margin: 0; 
        font-size: 0.75rem; 
        color: #475569; 
    }
    .guide-list li { 
        margin-bottom: 0.2rem; 
        display: flex; 
        align-items: start; 
        gap: 0.4rem; 
        line-height: 1.4;
    }
    .guide-list li::before { content: "•"; color: #3b82f6; font-weight: bold; }
    .guide-highlight { color: #3b82f6; font-weight: 600; }
</style>

<div class="max-w-7xl mx-auto mb-20">
    <form method="POST" enctype="multipart/form-data">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo $mode == 'insert' ? 'Add New Project' : 'Edit Project'; ?></h1>
                <p class="text-sm text-gray-500 mt-1">Create and publish a new project case study</p>
            </div>
            <div class="flex gap-3">
                <a href="project_list.php" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 shadow-sm transition">
                    <?php echo $mode == 'insert' ? 'Publish Project' : 'Update Project'; ?>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Project Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" 
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-lg" required>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Video Embed</label>
                    <div class="flex gap-2">
                        <input type="text" name="video_url" id="video_url" value="<?php echo htmlspecialchars($data['video_url']); ?>"
                               class="flex-1 px-4 py-3 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm"
                               placeholder="YouTube or Vimeo URL" oninput="previewVideo()">
                        <button type="button" onclick="previewVideo()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200">Preview</button>
                    </div>
                    <div id="video_preview" class="mt-4 aspect-video bg-black rounded-lg overflow-hidden <?php echo empty($data['video_url']) ? 'hidden' : ''; ?>"></div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Case Study Content</label>
                    
                    <div class="guide-box">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="guide-title"><i data-lucide="info" class="w-3.5 h-3.5 text-blue-500"></i> 작성 가이드</h4>
                            <button type="button" onclick="copyTemplateCode()" class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-[11px] font-bold hover:bg-blue-100 transition flex items-center gap-1 border border-blue-200">
                                <i data-lucide="copy" class="w-3 h-3"></i> 템플릿 코드 복사
                            </button>
                        </div>
                        <ul class="guide-list">
                            <li><strong>템플릿 사용 (옵션 1):</strong> 우측 <b>[템플릿 코드 복사]</b> 버튼 클릭 → 에디터 <code>&lt;&gt;</code>(소스코드) 버튼 → 붙여넣기.</li>
                            <li><strong>템플릿 사용 (옵션 2):</strong> 에디터 툴바의 <b>[템플릿 아이콘]</b>을 눌러서 바로 삽입할 수도 있습니다.</li>
                            <li><strong>이미지 교체:</strong> 에디터 내 이미지 클릭 → 툴바 [이미지] 아이콘 → 업로드 탭 사용.</li>
                            <li><strong>유튜브 삽입:</strong> 본문에 <code>https://youtu.be/...</code> 주소를 입력하면 자동 변환됩니다.</li>
                        </ul>
                    </div>

                    <textarea id="myEditor" name="content"><?php echo $data['content']; ?></textarea>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Gallery Images</label>
                    <?php if (count($gallery_images) > 0): ?>
                        <p class="text-xs text-gray-500 mb-2">Saved Images</p>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <?php foreach ($gallery_images as $img): ?>
                                <div class="relative group aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                                    <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="w-full h-full object-cover">
                                    <a href="?id=<?php echo $id; ?>&del_img=<?php echo $img['id']; ?>" 
                                       onclick="return confirm('이 이미지를 삭제하시겠습니까?');"
                                       class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition shadow-md cursor-pointer">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-10 text-center hover:bg-gray-50 transition cursor-pointer relative mb-4">
                        <input type="file" id="gallery_input" name="gallery_images[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <i data-lucide="images" class="w-10 h-10 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-sm text-gray-900 font-medium">Click to upload multiple images</p>
                    </div>
                    <div id="new_gallery_preview" class="grid grid-cols-4 gap-4"></div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-transparent rounded-lg focus:bg-white text-sm">
                        <option value="draft" <?php if($data['status']=='draft') echo 'selected'; ?>>Draft</option>
                        <option value="published" <?php if($data['status']=='published') echo 'selected'; ?>>Published</option>
                    </select>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Category</label>
                    <select name="category" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm" required>
                        <option value="">Select...</option>
                        <option value="Event" <?php if($data['category']=='Event') echo 'selected'; ?>>Event</option>
                        <option value="Design" <?php if($data['category']=='Design') echo 'selected'; ?>>Design</option>
                        <option value="Film" <?php if($data['category']=='Film') echo 'selected'; ?>>Film</option>
                        <option value="Studio" <?php if($data['category']=='Studio') echo 'selected'; ?>>Studio</option>
                    </select>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Client Name</label>
                    <input type="text" name="client_name" value="<?php echo htmlspecialchars($data['client_name']); ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black text-sm">
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Completion Date</label>
                    <input type="date" name="completion_date" value="<?php echo $data['completion_date']; ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm">
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Project URL</label>
                    <input type="url" name="external_link" value="<?php echo htmlspecialchars($data['external_link']); ?>"
                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm">
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <label class="block text-sm font-bold text-gray-900 mb-2">Thumbnail</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition min-h-[160px] flex items-center justify-center overflow-hidden">
                        <input type="file" name="thumbnail" onchange="previewThumbnail(this)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div id="thumb_placeholder" class="<?php echo !empty($data['thumbnail_path']) ? 'hidden' : ''; ?>">
                            <i data-lucide="image" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                            <span class="text-sm text-gray-500">Upload Image</span>
                        </div>
                        <img id="thumb_preview" src="<?php echo !empty($data['thumbnail_path']) ? htmlspecialchars($data['thumbnail_path']) : ''; ?>" 
                             class="w-full h-auto rounded <?php echo empty($data['thumbnail_path']) ? 'hidden' : ''; ?>">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<textarea id="template_code" style="display:none;">
<?php 
// 템플릿이 존재하면 첫 번째 템플릿의 내용을 출력
if (!empty($editor_templates) && isset($editor_templates[0]['content'])) {
    echo htmlspecialchars($editor_templates[0]['content']);
}
?>
</textarea>

<script>
    tinymce.init({
        selector: '#myEditor',
        height: 900,
        menubar: true,
        language: 'ko_KR',
        plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
        toolbar: 'undo redo | template | fontfamily blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor removeformat | code fullscreen preview',
        
        font_family_formats: 'Freesentation=Freesentation; Pretendard=Pretendard; Noto Sans KR=Noto Sans KR; 맑은 고딕=Malgun Gothic; 돋움=Dotum; sans-serif=sans-serif;',
        
        content_css: '/adm/css/admin.css',
        
        content_style: `
            @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap');
            
            body { 
                font-family: 'Noto Sans KR', sans-serif; 
                font-size: 16px; 
                line-height: 1.6; 
                color: #333; 
                padding: 20px; 
                background-color: #f9f9f9;
                max-width: 100%;
                overflow-x: hidden;
            } 
            
            .custom-project-layout header, 
            .custom-project-layout section { 
                max-width: 900px; 
                margin: 0 auto; 
            }

            .custom-project-layout .interlude-banner { 
                width: 100vw;
                position: relative;
                left: 50%;
                right: 50%;
                margin-left: -50vw;
                margin-right: -50vw;
                height: 400px;
                margin-top: 40px;
                margin-bottom: 40px;
                overflow: hidden;
            }
            .custom-project-layout img { width: 100%; height: 100%; object-fit: cover; display: block; margin: 0; }
            .custom-project-layout .hero-image-frame { height: 400px; overflow: hidden; margin-bottom: 30px; }
            
            .custom-project-layout .grid-feature { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; align-items: center; }
            .custom-project-layout .grid-gallery { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .custom-project-layout .grid-collage { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

            .custom-project-layout .cafe-description {
                font-size: 1rem !important;
                line-height: 1.7;
                color: #444;
                text-align: left !important;
            }
        `,

        // ★ [핵심] PHP에서 불러온 템플릿 배열을 JSON으로 변환하여 JS에 전달
        templates: <?php echo json_encode($editor_templates); ?>,

        images_upload_url: 'upload_image.php',
        automatic_uploads: true,
        file_picker_types: 'image media',
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', 'upload_image.php'); 
            xhr.upload.onprogress = (e) => { progress(e.loaded / e.total * 100); };
            xhr.onload = () => {
                if (xhr.status === 403) { reject({ message: 'HTTP Error: ' + xhr.status, remove: true }); return; }
                if (xhr.status < 200 || xhr.status >= 300) { reject('HTTP Error: ' + xhr.status); return; }
                const json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') { reject('Invalid JSON: ' + xhr.responseText); return; }
                resolve(json.location); 
            };
            xhr.onerror = () => { reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status); };
            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }),
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
    });

    // [코드 복사 함수]
    function copyTemplateCode() {
        var copyText = document.getElementById("template_code");
        var tempInput = document.createElement("textarea");
        tempInput.value = copyText.value.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"').replace(/&amp;/g, "&");
        document.body.appendChild(tempInput);
        
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(tempInput.value).then(() => {
            alert("템플릿 코드가 복사되었습니다!\n에디터의 [소스 코드] 버튼을 눌러 붙여넣기 하세요.");
        });
        document.body.removeChild(tempInput);
    }

    // ★ [수정] 유튜브 프리뷰 자동 실행 및 정규식 개선
    function previewVideo() {
        var url = document.getElementById('video_url').value.trim();
        var previewBox = document.getElementById('video_preview');
        var embedUrl = '';

        if (!url) {
            previewBox.innerHTML = '';
            previewBox.classList.add('hidden');
            return;
        }

        // 유튜브 URL 패턴 매칭 (youtu.be, watch?v=, embed 등)
        // 예: https://youtu.be/ID, https://www.youtube.com/watch?v=ID
        var youtubeRegExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var youtubeMatch = url.match(youtubeRegExp);

        if (youtubeMatch && youtubeMatch[2].length == 11) {
            var videoId = youtubeMatch[2];
            embedUrl = 'https://www.youtube.com/embed/' + videoId;
        } 
        // 비메오 URL 처리
        else if (url.includes('vimeo.com')) {
            var vimeoRegExp = /vimeo.*\/(\d+)/i;
            var vimeoMatch = url.match(vimeoRegExp);
            if (vimeoMatch && vimeoMatch[1]) {
                embedUrl = 'https://player.vimeo.com/video/' + vimeoMatch[1];
            }
        }

        if (embedUrl) {
            previewBox.innerHTML = '<iframe src="' + embedUrl + '" class="w-full h-full" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            previewBox.classList.remove('hidden');
        } else {
            // 올바르지 않은 주소일 경우 숨김
            previewBox.classList.add('hidden');
        }
    }

    // ★ 수정 모드 진입 시 미리보기 자동 실행
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('video_url').value) {
            previewVideo();
        }
    });

    function previewThumbnail(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('thumb_placeholder').classList.add('hidden');
                var img = document.getElementById('thumb_preview');
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    const galleryInput = document.getElementById('gallery_input');
    const previewContainer = document.getElementById('new_gallery_preview');
    let dataTransfer = new DataTransfer();

    galleryInput.addEventListener('change', function(e) {
        for (let i = 0; i < this.files.length; i++) {
            dataTransfer.items.add(this.files[i]);
        }
        this.files = dataTransfer.files;
        renderGalleryPreviews();
    });

    function renderGalleryPreviews() {
        previewContainer.innerHTML = '';
        Array.from(dataTransfer.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group aspect-square bg-gray-50 rounded-lg overflow-hidden border border-gray-200';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeFile(${index})" 
                            class="absolute top-1 right-1 bg-black text-white p-1 rounded-full opacity-80 hover:opacity-100 transition shadow-md">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                `;
                previewContainer.appendChild(div);
                lucide.createIcons();
            }
            reader.readAsDataURL(file);
        });
    }

    window.removeFile = function(index) {
        const newDataTransfer = new DataTransfer();
        Array.from(dataTransfer.files).forEach((file, i) => {
            if (i !== index) newDataTransfer.items.add(file);
        });
        dataTransfer = newDataTransfer;
        galleryInput.files = dataTransfer.files;
        renderGalleryPreviews();
    }
    
    lucide.createIcons();
</script>
</body>
</html>