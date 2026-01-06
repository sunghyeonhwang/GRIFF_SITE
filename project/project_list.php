<?php
// 1. 에러 리포팅
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. 공통 헤더 로드
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) {
    require_once "$root/inc/header.php";
} else {
    die("<h1>Error: 헤더 로드 실패</h1>");
}

// 3. DB 연결 확인
if (!isset($conn)) {
    die("<h1>Error: DB 연결 실패. 헤더 파일을 확인해주세요.</h1>");
}

// 4. 파라미터 처리
$selected_cat = isset($_GET['cat']) ? $_GET['cat'] : 'all';
$selected_year = isset($_GET['year']) ? $_GET['year'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 9; 
$offset = ($page - 1) * $limit;

// 5. 데이터 준비
$sql_cats = "SELECT DISTINCT category FROM projects WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category ASC";
$res_cats = $conn->query($sql_cats);
$categories = [];
if ($res_cats) { while ($row = $res_cats->fetch_assoc()) $categories[] = $row['category']; }

$sql_years = "SELECT DISTINCT YEAR(created_at) as yr FROM projects WHERE status = 'published' ORDER BY yr DESC";
$res_years = $conn->query($sql_years);
$years = [];
if ($res_years) { while ($row = $res_years->fetch_assoc()) $years[] = $row['yr']; }

// 6. 프로젝트 쿼리
$where_clause = " WHERE status = 'published'";
if ($selected_cat !== 'all') {
    $where_clause .= " AND category = '" . $conn->real_escape_string($selected_cat) . "'";
}
if ($selected_year !== 'all') {
    $where_clause .= " AND YEAR(created_at) = '" . $conn->real_escape_string($selected_year) . "'";
}

$sql_count = "SELECT COUNT(*) as cnt FROM projects" . $where_clause;
$res_count = $conn->query($sql_count);
$total_items = ($res_count && $row = $res_count->fetch_assoc()) ? $row['cnt'] : 0;
$total_pages = ceil($total_items / $limit);

$sql_data = "SELECT * FROM projects" . $where_clause;
$sql_data .= " ORDER BY created_at DESC, sort_order ASC";
$sql_data .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($sql_data);
?>

<style>
    .filter-link {
        color: #9CA3AF;
        font-weight: 500;
        transition: all 0.3s ease;
        padding-bottom: 2px;
        border-bottom: 2px solid transparent;
        cursor: pointer;
    }
    .filter-link:hover { color: #1a1a1a; }
    .filter-link.active {
        color: #1a1a1a;
        font-weight: 700;
        border-bottom-color: #1a1a1a;
    }
    .filter-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #9CA3AF;
        letter-spacing: 0.05em;
        margin-right: 1.5rem;
        display: inline-block;
        width: 60px;
        text-align: right;
    }
    @media (max-width: 1024px) {
        .filter-label { text-align: left; width: auto; margin-right: 1rem; }
    }
    .page-link {
        width: 40px; height: 40px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        font-family: 'URWDIN', sans-serif;
        font-size: 14px;
        color: #9CA3AF;
        transition: all 0.3s;
    }
    .page-link:hover { color: #1a1a1a; background-color: #f3f4f6; }
    .page-link.active { background-color: #1a1a1a; color: #fff; font-weight: bold; }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-24 min-h-screen">
    
    <div class="flex flex-col lg:flex-row justify-between items-end pb-12 mb-16 gap-8 border-b border-neutral-200">
        <div class="w-full lg:w-auto">
            <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
                PROJECTS<span class="text-[#FACC15]">.</span>
            </h1>
            <p class="font-kor text-neutral-800 mt-3 text-sm md:text-base font-medium">
                그리프의 다양한 프로젝트를 확인해보세요.
            </p>
        </div>

        <div class="w-full lg:w-auto flex flex-col items-start lg:items-end gap-3 font-eng text-sm">
            <div class="flex items-center">
                <span class="filter-label text-neutral-400">YEAR</span>
                <div class="flex gap-4 md:gap-6">
                    <a href="?cat=<?= urlencode($selected_cat) ?>&year=all" class="filter-link uppercase <?= ($selected_year === 'all') ? 'active' : '' ?>">ALL</a>
                    <?php foreach ($years as $yr): ?>
                        <a href="?cat=<?= urlencode($selected_cat) ?>&year=<?= $yr ?>" class="filter-link <?= ($selected_year == $yr) ? 'active' : '' ?>"><?= $yr ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex items-center">
                <span class="filter-label text-neutral-400">CATEGORY</span>
                <div class="flex gap-4 md:gap-6">
                    <a href="?cat=all&year=<?= urlencode($selected_year) ?>" class="filter-link uppercase <?= ($selected_cat === 'all') ? 'active' : '' ?>">ALL</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?cat=<?= urlencode($cat) ?>&year=<?= urlencode($selected_year) ?>" class="filter-link uppercase <?= ($selected_cat === $cat) ? 'active' : '' ?>"><?= htmlspecialchars($cat) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-16">
            <?php 
            while ($row = $result->fetch_assoc()): 
                $thumb = $row['thumbnail_path'];
                if ($thumb && strpos($thumb, '/') !== 0) $thumb = '/' . $thumb;
                $client = !empty($row['client_name']) ? "Client. " . htmlspecialchars($row['client_name']) : "";
            ?>
            <a href="/project_view.php?id=<?= $row['id'] ?>" class="group block w-full cursor-pointer project-item opacity-0 translate-y-10">
                <div class="relative w-full aspect-[16/10] bg-neutral-100 overflow-hidden mb-5 rounded-lg border border-neutral-100 shadow-sm group-hover:shadow-md transition-all duration-300">
                    <?php if (!empty($thumb) && file_exists($_SERVER['DOCUMENT_ROOT'] . $thumb)): ?>
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 font-eng text-sm bg-neutral-100"><span>NO IMAGE</span></div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-500"></div>
                </div>
                <div class="flex flex-col gap-1 pr-4">
                    <span class="font-eng text-[11px] font-bold text-[#FACC15] uppercase tracking-widest block">
                        <?= htmlspecialchars($row['category']) ?>
                    </span>
                    <h3 class="font-eng text-2xl font-bold text-neutral-900 leading-tight group-hover:text-neutral-600 transition-colors duration-300 break-keep line-clamp-2">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>
                    <p class="font-kor text-sm text-neutral-400 mt-1 truncate font-medium">
                        <?= $client ?>
                    </p>
                </div>
            </a>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-24 mb-[150px]">
            <?php 
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a href="?page='.$i.'&cat='.urlencode($selected_cat).'&year='.urlencode($selected_year).'" class="page-link '.$active.'">'.$i.'</a>';
            }
            ?>
        </div>
        <?php else: ?>
            <div class="mb-[150px]"></div>
        <?php endif; ?>

    <?php else: ?>
        <div class="w-full py-40 text-center mb-[150px]">
            <p class="font-kor text-neutral-400 text-xl mb-6">조건에 맞는 프로젝트가 없습니다.</p>
            <a href="/project_list.php" class="inline-block px-6 py-2 border border-neutral-300 rounded-full font-eng text-sm text-neutral-600 hover:bg-neutral-900 hover:text-white transition-colors">Reset Filters</a>
        </div>
    <?php endif; ?>

</div>

<div class="w-full max-w-[1200px] mx-auto mb-20 px-6 md:px-0">
    <a href="/inquiry_list.php" class="group block w-full bg-[#FFFEED] rounded-[24px] p-10 md:p-12 hover:bg-[#FAEB15] transition-colors duration-300">
        <div class="flex flex-col md:flex-row justify-between items-end gap-8">
            <div>
                <h2 class="font-eng text-3xl md:text-4xl font-bold text-neutral-900 mb-2 group-hover:text-black transition-colors duration-300">
                    CONTACT US<span class="text-[#F86F18]">.</span>
                </h2>
                <p class="font-kor text-base md:text-lg text-neutral-500 font-medium group-hover:text-neutral-800 transition-colors">
                    새로운 프로젝트 이야기를 들려주세요.
                </p>
            </div>
            
            <div class="md:mb-1">
                <div class="inline-flex items-center gap-2 text-neutral-900 font-eng text-lg font-bold border-b border-neutral-900 pb-0.5 pt-[2px] group-hover:border-black group-hover:text-black transition-colors">
                    Get in Touch <span class="group-hover:translate-x-1 transition-transform duration-300">→</span>
                </div>
            </div>
        </div>
    </a>
</div>

<?php 
// 스튜디오 숏컷 Include
if (file_exists("$root/inc/studio_shortcut.php")) {
    include "$root/inc/studio_shortcut.php";
}

// 공통 푸터 로드
if (file_exists("$root/inc/footer.php")) {
    include "$root/inc/footer.php";
}
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof gsap !== 'undefined') {
            gsap.to("body", { opacity: 1, duration: 0.5 });
            gsap.to(".project-item", { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power3.out" });

            if (typeof ScrollTrigger !== 'undefined') {
                gsap.utils.toArray('[data-scroll]').forEach(el => {
                    gsap.to(el, {
                        y: () => (1 - (el.getAttribute('data-scroll-speed') || 0)) * 100,
                        ease: "none",
                        scrollTrigger: {
                            trigger: el.parentElement,
                            start: "top bottom",
                            end: "bottom top",
                            scrub: 0
                        }
                    });
                });
            }
        } else {
            document.body.style.opacity = 1;
        }
    });
</script>