<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [1] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// [2] 현재 공고 조회
$sql = "SELECT * FROM recruits WHERE id = " . $conn->real_escape_string($id);
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "<script>alert('존재하지 않는 공고입니다.'); location.href='/recruit_list.php';</script>";
    exit;
}

// [3] 이전/다음 글 조회
$sql_prev = "SELECT id, title FROM recruits WHERE status = 'open' AND is_hidden = 0 AND id < $id ORDER BY id DESC LIMIT 1";
$res_prev = $conn->query($sql_prev);
$prev_row = ($res_prev && $res_prev->num_rows > 0) ? $res_prev->fetch_assoc() : null;

$sql_next = "SELECT id, title FROM recruits WHERE status = 'open' AND is_hidden = 0 AND id > $id ORDER BY id ASC LIMIT 1";
$res_next = $conn->query($sql_next);
$next_row = ($res_next && $res_next->num_rows > 0) ? $res_next->fetch_assoc() : null;

// [4] 데이터 매핑
$title = htmlspecialchars($row['title']);
$skill = !empty($row['tech_stack']) ? htmlspecialchars($row['tech_stack']) : 'General'; 
$status = isset($row['status']) ? $row['status'] : 'open'; 
$content = $row['content']; 

$location = !empty($row['location']) ? htmlspecialchars($row['location']) : '서울 성동구';
$type_kr = !empty($row['job_type']) ? htmlspecialchars($row['job_type']) : '정규직';
$deadline_txt = !empty($row['deadline']) ? date("Y.m.d", strtotime($row['deadline'])) : "상시 채용";
$salary = (!empty($row['salary'])) ? htmlspecialchars($row['salary']) : '협의';

$type_eng = "FULL TIME";
if (strpos($type_kr, '정규직') !== false) $type_eng = "FULL TIME";
elseif (strpos($type_kr, '계약직') !== false) $type_eng = "CONTRACT";
elseif (strpos($type_kr, '인턴') !== false) $type_eng = "INTERNSHIP";
elseif (strpos($type_kr, '프리랜서') !== false) $type_eng = "FREELANCE";
elseif (strpos($type_kr, '아르바이트') !== false) $type_eng = "PART TIME";
?>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }

    /* 초기 상태 숨김 */
    .fade-up-init { opacity: 0; transform: translateY(30px); }

    /* 본문 스타일링 */
    .jd-content { 
        color: #374151; 
        line-height: 1.8; 
        font-family: 'Freesentation', sans-serif;
        font-size: 1.15rem;
    }
    .jd-content h1, .jd-content h2, .jd-content h3 {
        color: #111; font-weight: 700; margin-top: 3rem; margin-bottom: 1.2rem; line-height: 1.3;
    }
    .jd-content h1 { font-size: 2.2rem; }
    .jd-content h2 { font-size: 1.8rem; }
    .jd-content h3 { font-size: 1.5rem; }
    .jd-content p { margin-bottom: 1.5rem; }
    .jd-content ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1.5rem; }
    .jd-content ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1.5rem; }
    .jd-content li { margin-bottom: 0.5rem; }
    .jd-content strong { font-weight: 700; color: #000; }
    /* 링크 컬러도 포인트 컬러로 변경 */
    .jd-content a { color: #2DC49A; text-decoration: underline; }
    .jd-content img { max-width: 100%; height: auto; border-radius: 12px; margin: 2rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }

    /* 우측 테이블 스타일 */
    .summary-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1.1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-label { font-weight: 700; color: #1a1a1a; font-size: 0.95rem; }
    .summary-value { color: #6b7280; font-size: 0.95rem; text-align: right; }
    
    /* 네비게이션 버튼 스타일 */
    .nav-btn {
        display: flex; align-items: center; gap: 1rem;
        padding: 2rem 1.5rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .nav-btn:hover { 
        background-color: #1a1a1a; 
        border-color: #1a1a1a;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .nav-icon {
        width: 40px; height: 40px; 
        border-radius: 50%; 
        background: #f3f4f6; 
        display: flex; align-items: center; justify-content: center;
        color: #6b7280;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    .nav-btn:hover .nav-icon { background: #FAEB15; color: #000; }
    .nav-label { 
        font-family: 'URWDIN', sans-serif; font-size: 0.75rem; color: #9ca3af; font-weight: 700; 
        margin-bottom: 0.3rem; letter-spacing: 0.05em; transition: color 0.3s;
    }
    .nav-btn:hover .nav-label { color: #FAEB15; }
    .nav-title { 
        font-family: 'Freesentation', sans-serif; font-size: 1.1rem; color: #1a1a1a; font-weight: 700; 
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.3s;
    }
    .nav-btn:hover .nav-title { color: #fff; }

    /* 목록으로 돌아가기 버튼 스타일 */
    .list-btn {
        display: inline-flex; align-items: center; gap: 0.75rem;
        padding: 0.8rem 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 100px;
        font-family: 'URWDIN', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        color: #6b7280;
        background: #fff;
        transition: all 0.3s ease;
    }
    .list-btn:hover {
        background: #1a1a1a;
        color: #fff;
        border-color: #1a1a1a;
        transform: translateY(-2px);
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-[100px] lg:pb-[200px] min-h-screen">

    <div class="flex flex-col lg:flex-row justify-between items-end pb-12 mb-12 gap-8 border-b border-neutral-200 fade-up-init">
        <div class="w-full lg:w-auto">
            <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
                CAREERS<span class="text-[#2DC49A]">.</span> </h1>
            <p class="font-kor text-neutral-800 mt-4 text-base md:text-lg font-medium">
                그리프와 함께 새로운 가치를 만들어갈 동료를 찾습니다.
            </p>
        </div>
    </div>

    <div class="mb-16 fade-up-init">
        <div class="flex items-center gap-6 mb-4 font-eng text-sm font-bold tracking-wide">
            <?php if($status == 'open'): ?>
                <span class="text-black border-b-[3px] border-[#FAEB15] pb-1">RECRUITING</span>
            <?php else: ?>
                <span class="text-neutral-400 border-b-[3px] border-neutral-200 pb-1">CLOSED</span>
            <?php endif; ?>
            <span class="text-neutral-500 border-b border-neutral-300 pb-1 uppercase"><?= $type_eng ?></span>
        </div>
        <h2 class="font-kor text-4xl md:text-5xl font-bold text-[#1a1a1a] leading-tight mb-2">
            <?= $title ?>
        </h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-[30px] items-start">
        
        <div class="lg:col-span-8 fade-up-init">
            <div class="jd-content mb-24">
                <?= $content ?>
            </div>

            <div class="flex justify-center mb-16">
                <a href="/recruit/recruit_list.php" class="list-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <span>BACK TO LIST</span>
                </a>
            </div>

            <div class="border-t border-neutral-200 pt-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if($prev_row): ?>
                    <a href="/recruit/recruit_view.php?id=<?= $prev_row['id'] ?>" class="nav-btn rounded-xl">
                        <div class="nav-icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></div>
                        <div class="overflow-hidden">
                            <p class="nav-label">PREV RECRUIT</p>
                            <p class="nav-title"><?= htmlspecialchars($prev_row['title']) ?></p>
                        </div>
                    </a>
                    <?php else: ?>
                    <div class="nav-btn rounded-xl opacity-50 cursor-default hover:bg-white hover:border-neutral-200 hover:transform-none hover:shadow-none">
                        <div class="nav-icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></div>
                        <div><p class="nav-label">PREV RECRUIT</p><p class="nav-title text-neutral-400">이전 공고가 없습니다.</p></div>
                    </div>
                    <?php endif; ?>

                    <?php if($next_row): ?>
                    <a href="/recruit_view.php?id=<?= $next_row['id'] ?>" class="nav-btn rounded-xl justify-between flex-row-reverse text-right">
                        <div class="nav-icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></div>
                        <div class="overflow-hidden">
                            <p class="nav-label">NEXT RECRUIT</p>
                            <p class="nav-title"><?= htmlspecialchars($next_row['title']) ?></p>
                        </div>
                    </a>
                    <?php else: ?>
                    <div class="nav-btn rounded-xl justify-between flex-row-reverse text-right opacity-50 cursor-default hover:bg-white hover:border-neutral-200 hover:transform-none hover:shadow-none">
                        <div class="nav-icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></div>
                        <div><p class="nav-label">NEXT RECRUIT</p><p class="nav-title text-neutral-400">다음 공고가 없습니다.</p></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 lg:sticky lg:top-[150px] fade-up-init">
            <div class="bg-white rounded-[2rem] p-6 md:p-8 border border-neutral-200 shadow-xl shadow-neutral-100 mb-8 font-kor">
                <div class="mb-6 pb-2 border-b-2 border-black">
                    <h3 class="font-eng font-bold text-lg">JOB SUMMARY</h3>
                </div>
                <div class="summary-item"><span class="summary-label">기술 스택 (Skill)</span><span class="summary-value font-eng font-bold text-black"><?= $skill ?></span></div>
                <div class="summary-item"><span class="summary-label">급여</span><span class="summary-value font-bold"><?= $salary ?></span></div>
                <div class="summary-item"><span class="summary-label">근무지</span><span class="summary-value"><?= $location ?></span></div>
                <div class="summary-item"><span class="summary-label">고용형태</span><span class="summary-value"><?= $type_kr ?></span></div>
                <div class="summary-item"><span class="summary-label">마감일</span><span class="summary-value"><?= $deadline_txt ?></span></div>
                <div class="pt-6 text-xs text-neutral-400 leading-relaxed">* 포트폴리오 제출 필수<br>(PDF 형식 또는 URL)<br>* 허위 사실 기재 시 채용이 취소될 수 있습니다.</div>
            </div>
            
            <?php if($status == 'open'): ?>
            <a href="/recruit/recruit_apply.php?id=<?= $id ?>" class="group w-full bg-black text-white font-eng font-bold text-lg py-4 rounded-2xl flex items-center justify-center gap-2 transition-all duration-300 hover:bg-[#2DC49A] hover:shadow-lg hover:-translate-y-1">
                <span>APPLY NOW</span>
                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            <?php else: ?>
            <button disabled class="w-full bg-neutral-300 text-white font-eng font-bold text-lg py-4 rounded-2xl cursor-not-allowed">CLOSED</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power2.out", delay: 0.1 });
    });
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>