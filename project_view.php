<?php
// 1. 에러 리포팅 & DB 연결
error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = $_SERVER['DOCUMENT_ROOT'];

// 공통 헤더 로드
if (file_exists("$root/inc/header.php")) {
    require_once "$root/inc/header.php";
} else {
    die("<h1>Error: 헤더 로드 실패 (inc/header.php)</h1>");
}

// DB 연결 확인
if (!isset($conn)) {
    die("<h1>Error: DB 연결 실패</h1>");
}

// 2. 프로젝트 ID 확인 및 데이터 조회
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/project_list.php';</script>";
    exit;
}

// [메인 프로젝트 정보 조회]
$sql = "SELECT * FROM projects WHERE id = $id AND status = 'published'";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    echo "<script>alert('존재하지 않거나 비공개된 프로젝트입니다.'); location.href='/project_list.php';</script>";
    exit;
}
$row = $result->fetch_assoc();

// [갤러리 이미지 조회]
$gallery_images = [];
$js_gallery_data = []; 

$sql_gallery = "SELECT * FROM project_images WHERE project_id = $id ORDER BY sort_order ASC";
$res_gallery = $conn->query($sql_gallery);
if ($res_gallery && $res_gallery->num_rows > 0) {
    while($img_row = $res_gallery->fetch_assoc()) {
        $gallery_images[] = $img_row;
        
        $g_path = $img_row['image_path'];
        if($g_path && strpos($g_path, '/') !== 0) $g_path = '/'.$g_path;
        $js_gallery_data[] = $g_path;
    }
}

// [크레딧 & 링크 JSON 디코딩]
$credits = !empty($row['credits']) ? json_decode($row['credits'], true) : [];
$links = !empty($row['related_links']) ? json_decode($row['related_links'], true) : [];

// [이전/다음 프로젝트 조회]
$sql_prev = "SELECT id, title, thumbnail_path FROM projects WHERE status = 'published' AND id > $id ORDER BY id ASC LIMIT 1";
$res_prev = $conn->query($sql_prev);
$prev_project = ($res_prev && $res_prev->num_rows > 0) ? $res_prev->fetch_assoc() : null;

$sql_next = "SELECT id, title, thumbnail_path FROM projects WHERE status = 'published' AND id < $id ORDER BY id DESC LIMIT 1";
$res_next = $conn->query($sql_next);
$next_project = ($res_next && $res_next->num_rows > 0) ? $res_next->fetch_assoc() : null;

// 날짜 포맷팅
$date_str = date("Y.m", strtotime($row['created_at']));

// [영상 URL 파싱 함수]
function getVideoEmbedUrl($url) {
    $url = trim((string)$url);
    if ($url === '') return '';

    if (stripos($url, 'youtube.com/embed/') !== false) {
        $sep = (strpos($url, '?') !== false) ? '&' : '?';
        return $url . $sep . "rel=0&modestbranding=1&playsinline=1";
    }

    $videoId = '';
    $parts = @parse_url($url);
    if ($parts && !empty($parts['host'])) {
        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';

        if (strpos($host, 'youtu.be') !== false) {
            $videoId = ltrim($path, '/');
        }
        if ($videoId === '' && strpos($host, 'youtube.com') !== false) {
            parse_str($parts['query'] ?? '', $qs);
            if (!empty($qs['v'])) $videoId = $qs['v'];
        }
        if ($videoId === '' && preg_match('~/(shorts|live|embed)/([^/?]+)~i', $path, $m)) {
            $videoId = $m[2];
        }
    }
    if ($videoId === '' && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([^"&?/\\s]{11})~i', $url, $m)) {
        $videoId = $m[1];
    }

    if ($videoId !== '' && preg_match('~^[a-zA-Z0-9_-]{11}$~', $videoId)) {
        return "https://www.youtube.com/embed/{$videoId}?rel=0&modestbranding=1&playsinline=1";
    }

    if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $matches)) {
        return "https://player.vimeo.com/video/" . $matches[1];
    }
    return '';
}

// [다중 컬럼 지원]
$video_url = "";
$candidates = [
    $row['youtube_url'] ?? '',
    $row['vimeo_url'] ?? '',
    $row['video_url'] ?? '',
    $row['main_video_url'] ?? '',
    $row['video_link'] ?? '',
];

foreach ($candidates as $cand) {
    $embed = getVideoEmbedUrl($cand);
    if ($embed !== '') { $video_url = $embed; break; }
}
?>

<style>
    /* 에디터 스타일 복구 */
    .view-editor-content { color: #333; line-height: 1.8; font-family: 'Freesentation', sans-serif; }
    .view-editor-content p { margin-bottom: 1.5em; word-break: keep-all; }
    .view-editor-content strong, .view-editor-content b { font-weight: 700; color: #1a1a1a; }
    .view-editor-content img { max-width: 100%; height: auto; border-radius: 12px; margin: 2em 0; }
    .view-editor-content h1, .view-editor-content h2 { font-weight: bold; margin-top: 2em; margin-bottom: 1em; }
    .view-editor-content ul { list-style: disc; padding-left: 1.5em; margin-bottom: 1.5em; }
</style>

<div class="yellow-bg absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[320px] bg-[#FAEB15] -z-10 rounded-b-[50px]"></div>

<div class="h-[100px] md:h-[150px]"></div>

<div class="relative w-full max-w-[1400px] mx-auto px-6 md:px-12">

    <div class="w-full mb-24 animate-fade-up">
        <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
            <a href="/project_list.php" class="cursor-pointer hover:opacity-70 transition-opacity">
                PROJECTS<span class="text-white">.</span>
            </a>
        </h1>
        <p class="font-kor text-neutral-900 mt-3 text-sm md:text-base font-medium">
            그리프의 다양한 프로젝트를 확인해보세요.
        </p>
    </div>

    <div class="flex flex-col lg:flex-row items-center justify-between gap-8 pb-10 border-b border-black/10 mb-4 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="w-full lg:w-7/12">
            <h2 class="font-eng text-3xl md:text-4xl font-bold text-neutral-900 leading-[1.1] break-keep pt-4">
                <?= htmlspecialchars($row['title']) ?>
            </h2>
        </div>
        <div class="w-full lg:w-5/12 flex flex-col items-start lg:items-end text-left lg:text-right gap-4">
            <div class="flex flex-wrap items-center justify-start lg:justify-end gap-x-6 gap-y-2 text-sm md:text-base font-medium text-neutral-800">
                <div class="flex items-center gap-2">
                    <span class="font-eng font-bold opacity-50 text-xs">CATEGORY</span>
                    <span class="font-eng font-bold uppercase"><?= htmlspecialchars($row['category']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-eng font-bold opacity-50 text-xs">CLIENT</span>
                    <span class="font-kor font-bold"><?= !empty($row['client_name']) ? htmlspecialchars($row['client_name']) : '-' ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-eng font-bold opacity-50 text-xs">DATE</span>
                    <span class="font-eng font-bold"><?= $date_str ?></span>
                </div>
            </div>
            <?php if(!empty($links)): ?>
            <div class="flex gap-4">
                <?php foreach($links as $link): if(!empty($link['url'])): ?>
                    <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank" class="inline-flex items-center gap-1 font-eng text-sm font-bold text-black border-b border-black/30 hover:border-black transition-colors pb-0.5">
                        <?= !empty($link['name']) ? htmlspecialchars($link['name']) : 'Visit Link' ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                    </a>
                <?php endif; endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($video_url)): // 영상 주소가 있을 때만 이 영역을 출력 ?>
<div id="video-section-wrapper" class="w-full max-w-[1400px] px-6 md:px-12 mx-auto mb-24 relative z-20 transition-all duration-700 ease-in-out animate-fade-up" style="animation-delay: 0.2s;">
    <div id="video-bg-box" class="w-full bg-[#202020] py-10 md:py-20 flex items-center justify-center shadow-2xl rounded-[30px] md:rounded-[50px] relative transition-all duration-700 overflow-hidden">
        <div id="video-inner-container" class="relative w-[90%] max-w-[1280px] aspect-video bg-black overflow-hidden shadow-lg border border-neutral-800 mx-auto rounded-xl transition-all duration-700">
            <iframe src="<?= htmlspecialchars($video_url, ENT_QUOTES, 'UTF-8') ?>" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
        <button id="btn-video-toggle" onclick="toggleVideoWidth()" class="absolute bottom-6 right-6 md:bottom-10 md:right-10 z-50 p-3 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-full text-white transition-all duration-300 group border border-white/10" title="전체 화면 보기">
            <svg id="icon-expand" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="block group-hover:scale-110 transition-transform"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
            <svg id="icon-compress" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden group-hover:scale-110 transition-transform"><path d="M4 14h6v6M20 10h-6V4M14 10l7-7M10 14l-7 7"/></svg>
        </button>
    </div>
</div>
<?php endif; ?>

<div class="relative w-full max-w-[1400px] mx-auto px-6 md:px-12 pb-20">
    <div class="mb-24 animate-fade-up" style="animation-delay: 0.3s;">
        <h3 class="font-eng text-2xl md:text-3xl font-bold text-neutral-900 mb-8 flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-[#FACC15]"></span> OVERVIEW
        </h3>
        <div class="view-editor-content text-lg md:text-xl text-neutral-800 leading-relaxed text-justify w-full mb-16">
            <?php
            $content = $row['content'] ?? $row['description'] ?? $row['overview_text'] ?? '';
            echo $content;
            ?>
        </div>
        <?php if(!empty($credits)): ?>
        <div class="bg-neutral-50 p-8 md:p-10 rounded-[30px] border border-neutral-100">
            <h4 class="font-eng text-xs font-bold text-neutral-400 mb-8 tracking-widest border-b border-neutral-200 pb-4">CREDITS</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-8 gap-y-6">
                <?php foreach($credits as $credit): ?>
                <div class="flex flex-col">
                    <span class="font-eng font-bold text-neutral-900 uppercase text-sm mb-1"><?= htmlspecialchars($credit['role'] ?? '') ?></span>
                    <span class="font-kor text-neutral-500 font-medium text-sm"><?= htmlspecialchars($credit['name'] ?? '') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($gallery_images)): ?>
    <div class="mb-32 animate-fade-up" style="animation-delay: 0.4s;">
        <h3 class="font-eng text-2xl md:text-3xl font-bold text-neutral-900 mb-10 flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-[#FACC15]"></span> PROJECT GALLERY
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($gallery_images as $idx => $img): ?>
                <div onclick="openLightbox(<?= $idx ?>)" class="gallery-item group relative overflow-hidden rounded-[20px] shadow-sm hover:shadow-md transition-all duration-300 opacity-0 translate-y-10 cursor-pointer">
                    <?php 
                        $g_path = $img['image_path'];
                        if($g_path && strpos($g_path, '/') !== 0) $g_path = '/'.$g_path;
                    ?>
                    <img src="<?= htmlspecialchars($g_path) ?>" alt="Gallery Image" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-300 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="border-t border-neutral-200 pt-16 nav-section">
        <div class="flex justify-between items-center mb-8">
            <a href="/project_list.php" class="inline-flex items-center gap-2 font-eng text-sm font-bold text-neutral-400 hover:text-black transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                BACK TO LIST
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
            <?php if($prev_project): ?>
            <a href="/project_view.php?id=<?= $prev_project['id'] ?>" class="group block relative w-full h-[250px] md:h-[300px] rounded-[30px] overflow-hidden bg-black shadow-lg hover:shadow-xl transition-shadow nav-item">
                <?php $prev_thumb = !empty($prev_project['thumbnail_path']) ? $prev_project['thumbnail_path'] : ''; ?>
                <div class="absolute inset-0 w-full h-full">
                    <?php if($prev_thumb): ?>
                    <img src="<?= $prev_thumb ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-40 group-hover:scale-105 transition-all duration-700 ease-out">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                </div>
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-6 z-10">
                    <span class="font-eng text-[#FACC15] text-xs font-bold tracking-widest mb-3 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500">PREV PROJECT</span>
                    <h2 class="font-eng text-2xl md:text-3xl font-bold text-white leading-none group-hover:text-[#FACC15] transition-colors"><?= htmlspecialchars($prev_project['title']) ?></h2>
                </div>
            </a>
            <?php else: ?>
            <div class="hidden md:block"></div> 
            <?php endif; ?>
            <?php if($next_project): ?>
            <a href="/project_view.php?id=<?= $next_project['id'] ?>" class="group block relative w-full h-[250px] md:h-[300px] rounded-[30px] overflow-hidden bg-black shadow-lg hover:shadow-xl transition-shadow nav-item">
                <?php $next_thumb = !empty($next_project['thumbnail_path']) ? $next_project['thumbnail_path'] : ''; ?>
                <div class="absolute inset-0 w-full h-full">
                    <?php if($next_thumb): ?>
                    <img src="<?= $next_thumb ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-40 group-hover:scale-105 transition-all duration-700 ease-out">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                </div>
                <div class="absolute inset-0 flex flex-col justify-center items-center text-center p-6 z-10">
                    <span class="font-eng text-[#FACC15] text-xs font-bold tracking-widest mb-3 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-500">NEXT PROJECT</span>
                    <h2 class="font-eng text-2xl md:text-3xl font-bold text-white leading-none group-hover:text-[#FACC15] transition-colors"><?= htmlspecialchars($next_project['title']) ?></h2>
                </div>
            </a>
            <?php else: ?>
            <div class="flex items-center justify-center h-[250px] md:h-[300px] border-2 border-dashed border-neutral-200 rounded-[30px] bg-neutral-50 text-neutral-400 font-kor nav-item">마지막 프로젝트입니다.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="lightbox-modal" class="fixed inset-0 z-[100] bg-black/95 hidden opacity-0 transition-opacity duration-300 flex items-center justify-center backdrop-blur-sm">
    <button onclick="closeLightbox()" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors z-[110]">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <button onclick="changeLightboxImage(-1)" class="absolute left-4 md:left-8 text-white/50 hover:text-[#FACC15] transition-colors z-[110] p-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button onclick="changeLightboxImage(1)" class="absolute right-4 md:right-8 text-white/50 hover:text-[#FACC15] transition-colors z-[110] p-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </button>
    <div class="relative w-full h-full max-w-7xl max-h-screen p-4 md:p-12 flex items-center justify-center">
        <img id="lightbox-img" src="" alt="Full Image" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 font-eng text-sm tracking-widest bg-black/50 px-4 py-1 rounded-full border border-white/10">
            <span id="lightbox-current">1</span> / <span id="lightbox-total">1</span>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<script>
    // [PHP -> JS 데이터 전달]
    const galleryData = <?php echo json_encode($js_gallery_data); ?>;
    let currentLightboxIndex = 0;

    document.addEventListener("DOMContentLoaded", function() {
        gsap.registerPlugin(ScrollTrigger);

        // 1. 상단 요소 순차 등장
        const fadeElements = gsap.utils.toArray('.animate-fade-up');
        if (fadeElements.length > 0) {
            gsap.from(fadeElements, {
                y: 60, opacity: 0, duration: 1.2, stagger: 0.15, ease: "power3.out", clearProps: "all"
            });
        }

        // 2. 노란색 박스 패럴랙스
        gsap.to(".yellow-bg", {
            yPercent: 30, ease: "none",
            scrollTrigger: { trigger: "body", start: "top top", end: "bottom top", scrub: true }
        });

        // 3. 갤러리 이미지 폭포수 등장
        const galleryItems = gsap.utils.toArray('.gallery-item');
        if (galleryItems.length > 0) {
            ScrollTrigger.batch(galleryItems, {
                start: "top 85%",
                onEnter: batch => gsap.to(batch, {
                    opacity: 1, y: 0, stagger: 0.15, duration: 1, ease: "power3.out", overwrite: true
                }),
                once: true
            });
        }

        // 4. 하단 네비게이션 등장
        const navItems = gsap.utils.toArray('.nav-item');
        if (navItems.length > 0) {
            ScrollTrigger.batch(navItems, {
                start: "top 90%",
                onEnter: batch => gsap.from(batch, {
                    opacity: 0, y: 40, stagger: 0.2, duration: 1, ease: "power2.out"
                }),
                once: true
            });
        }

        // 5. 키보드 이벤트 (Lightbox)
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('lightbox-modal');
            if (modal.classList.contains('hidden')) return;

            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') changeLightboxImage(-1);
            if (e.key === 'ArrowRight') changeLightboxImage(1);
        });
    });

    // [영상 너비 토글 함수]
    function toggleVideoWidth() {
        const wrapper = document.getElementById('video-section-wrapper');
        const bgBox = document.getElementById('video-bg-box');
        const innerContainer = document.getElementById('video-inner-container');
        const iconExpand = document.getElementById('icon-expand');
        const iconCompress = document.getElementById('icon-compress');
        
        const isDefault = wrapper.classList.contains('max-w-[1400px]');

        if (isDefault) {
            // [EXPAND]
            wrapper.classList.remove('max-w-[1400px]', 'px-6', 'md:px-12');
            bgBox.classList.remove('rounded-[30px]', 'md:rounded-[50px]');
            
            innerContainer.classList.remove('w-[90%]', 'max-w-[1280px]', 'rounded-xl');
            innerContainer.classList.add('w-full', 'max-w-[1600px]', 'rounded-none');
            
            iconExpand.classList.add('hidden');
            iconExpand.classList.remove('block');
            iconCompress.classList.remove('hidden');
            iconCompress.classList.add('block');
        } else {
            // [CONTRACT]
            wrapper.classList.add('max-w-[1400px]', 'px-6', 'md:px-12');
            bgBox.classList.add('rounded-[30px]', 'md:rounded-[50px]');
            
            innerContainer.classList.add('w-[90%]', 'max-w-[1280px]', 'rounded-xl');
            innerContainer.classList.remove('w-full', 'max-w-[1600px]', 'rounded-none');

            iconCompress.classList.add('hidden');
            iconCompress.classList.remove('block');
            iconExpand.classList.remove('hidden');
            iconExpand.classList.add('block');
        }
    }

    // [Lightbox 기능]
    function openLightbox(index) {
        if (!galleryData || galleryData.length === 0) return;
        
        currentLightboxIndex = index;
        updateLightboxContent();

        const modal = document.getElementById('lightbox-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const modal = document.getElementById('lightbox-modal');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }

    function changeLightboxImage(direction) {
        if (!galleryData || galleryData.length === 0) return;

        currentLightboxIndex += direction;

        if (currentLightboxIndex < 0) currentLightboxIndex = galleryData.length - 1;
        if (currentLightboxIndex >= galleryData.length) currentLightboxIndex = 0;

        updateLightboxContent();
    }

    function updateLightboxContent() {
        const img = document.getElementById('lightbox-img');
        const current = document.getElementById('lightbox-current');
        const total = document.getElementById('lightbox-total');

        img.src = galleryData[currentLightboxIndex];
        current.textContent = currentLightboxIndex + 1;
        total.textContent = galleryData.length;
    }
</script>

<?php 
if (file_exists("$root/inc/studio_shortcut.php")) {
    include "$root/inc/studio_shortcut.php";
}

if (file_exists("$root/inc/footer.php")) {
    include "$root/inc/footer.php";
}
?>