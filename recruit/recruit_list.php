<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [DB 연결]
include "$root/inc/front_db_connect.php";

// [쿼리 수정] 
// 1. is_hidden 컬럼이 있다고 하셨으니 조건 추가 (없으면 에러날 수 있으니 확인 필요)
// 2. status = 'open' 인 것만 조회
$sql = "SELECT * FROM recruits WHERE status = 'open' AND is_hidden = 0 ORDER BY id DESC";
$result = $conn->query($sql);
?>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    
    /* [초기 상태: 숨김 (GSAP to 애니메이션용)] */
    .fade-up-init, .scroll-fade-item {
        opacity: 0;
        transform: translateY(30px);
    }

    /* 리스트 카드 스타일 */
    .recruit-card {
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: 1px solid #e5e7eb;
        background: #fff;
    }
    .recruit-card:hover {
        transform: translateY(-5px);
        border-color: #1a1a1a;
        /* 그림자 제거 */
        box-shadow: none; 
    }

    /* Culture & Welfare 스타일 */
    .culture-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 2.5rem;
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .culture-card:hover {
        background: #FAEB15;
        border-color: #FAEB15;
        transform: translateY(-5px);
        box-shadow: none; 
    }
    
    .welfare-card {
        background: #262626;
        border: 1px solid #333;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100%;
    }
    .welfare-card:hover {
        background: #333;
        border-color: #555;
        transform: translateY(-5px);
        box-shadow: none; 
    }
    .welfare-icon {
        width: 60px; height: 60px;
        background: #FAEB15;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.2rem;
        color: #000;
    }

    /* 뱃지 스타일 */
    .d-day-badge {
        background: #FAEB15; color: #000; font-weight: 700; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-family: 'URWDIN', sans-serif;
    }
    .always-badge {
        background: #1a1a1a; color: #fff; font-weight: 700; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-family: 'URWDIN', sans-serif;
    }
    .closed-badge {
        background: #e5e7eb; color: #9ca3af; font-weight: 700; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-family: 'URWDIN', sans-serif;
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-32 min-h-screen">
    
    <div class="flex flex-col lg:flex-row justify-between items-end pb-12 mb-12 gap-8 border-b border-neutral-200 fade-up-init">
        <div class="w-full lg:w-auto">
            <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
                CAREERS<span class="text-[#22d3ee]">.</span>
            </h1>
            <p class="font-kor text-neutral-800 mt-4 text-base md:text-lg font-medium">
                그리프와 함께 새로운 가치를 만들어갈 동료를 찾습니다.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-32">
        <?php if($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                // [수정 1] 컬럼 매핑 및 데이터 처리
                // category 컬럼이 없다면 tech_stack을 사용하거나 'RECRUIT' 고정
                $category = !empty($row['tech_stack']) ? $row['tech_stack'] : 'RECRUIT'; 
                $location = !empty($row['location']) ? $row['location'] : '서울 성동구';
                
                // job_type이 있다면 쓰고, 없으면 type 확인, 없으면 기본값
                $type_kr = !empty($row['job_type']) ? $row['job_type'] : '정규직';
                
                // [수정 2] 본문 컬럼명 contents -> content 로 수정
                $content_preview = strip_tags($row['content']); 

                // [수정 3] 고용형태 영문 변환 로직
                $type_eng = "FULL TIME";
                if (strpos($type_kr, '정규직') !== false) $type_eng = "FULL TIME";
                elseif (strpos($type_kr, '계약직') !== false) $type_eng = "CONTRACT";
                elseif (strpos($type_kr, '인턴') !== false) $type_eng = "INTERNSHIP";
                elseif (strpos($type_kr, '프리랜서') !== false) $type_eng = "FREELANCE";
                elseif (strpos($type_kr, '아르바이트') !== false || strpos($type_kr, '파트타임') !== false) $type_eng = "PART TIME";

                // [D-Day 계산]
                $deadline_txt = "상시 채용";
                $badge_class = "always-badge";
                $d_day_str = "ALWAYS";

                if($row['deadline']) {
                    $today = new DateTime();
                    $deadline = new DateTime($row['deadline']);
                    $interval = $today->diff($deadline);
                    $days = $interval->days;
                    
                    if($today > $deadline) {
                        $d_day_str = "CLOSED";
                        $badge_class = "closed-badge";
                        $deadline_txt = "마감됨";
                    } elseif ($days == 0) {
                        $d_day_str = "D-Day";
                        $badge_class = "d-day-badge";
                        $deadline_txt = date("Y.m.d", strtotime($row['deadline']))." 마감";
                    } else {
                        $d_day_str = "D-".$days;
                        $badge_class = "d-day-badge";
                        $deadline_txt = date("Y.m.d", strtotime($row['deadline']))." 마감";
                    }
                }
            ?>
            <a href="/recruit/recruit_view.php?id=<?= $row['id'] ?>" class="recruit-card p-10 rounded-[2rem] block group fade-up-init relative overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <span class="<?= $badge_class ?>"><?= $d_day_str ?></span>
                    <span class="font-eng text-lg font-bold text-black uppercase tracking-wide group-hover:text-neutral-600 transition-colors"><?= htmlspecialchars($type_eng) ?></span>
                </div>
                
                <div class="mb-10">
                    <h3 class="font-kor text-3xl md:text-4xl font-bold text-neutral-900 mb-3 leading-snug w-fit relative inline-block">
                        <?= htmlspecialchars($row['title']) ?>
                        <span class="absolute left-0 bottom-0 w-full h-[2px] bg-black scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                    </h3>
                    
                    <p class="font-kor text-neutral-500 text-base md:text-lg mt-2 line-clamp-2 max-w-5xl leading-relaxed">
                        <?= $content_preview ?>
                    </p>
                </div>

                <div class="flex justify-between items-end border-t border-neutral-100 pt-8">
                    <div class="font-kor text-base text-neutral-600 space-y-1">
                        <p class="font-medium"><?= htmlspecialchars($location) ?> · <?= htmlspecialchars($type_kr) ?></p>
                        <p class="text-neutral-400"><?= $deadline_txt ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-black flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                        <svg class="w-6 h-6 text-[#FAEB15]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="py-32 text-center border border-dashed border-neutral-300 rounded-3xl">
                <p class="font-kor text-xl text-neutral-400">현재 진행중인 채용 공고가 없습니다.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-32">
        <div class="text-center mb-8 scroll-fade-item">
            <h2 class="font-kor text-4xl md:text-5xl font-bold text-neutral-900 mb-4">그리프는 이런 분을 모시고 싶습니다</h2>
            <p class="font-eng text-neutral-400 font-bold tracking-widest">OUR CULTURE</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="culture-card scroll-fade-item">
                <h3 class="font-kor text-2xl font-bold mb-3 text-black">자율적인 워크 스타일</h3>
                <p class="font-kor text-neutral-500 leading-relaxed">
                    출퇴근 시간과 근무 방식의 유연성을 존중합니다. 성과 중심의 업무 환경에서 자신만의 방식으로 몰입하여 일할 수 있습니다.
                </p>
            </div>
            <div class="culture-card scroll-fade-item">
                <h3 class="font-kor text-2xl font-bold mb-3 text-black">수평적 소통</h3>
                <p class="font-kor text-neutral-500 leading-relaxed">
                    직급이 아닌 역할로 소통합니다. 모든 구성원의 의견이 동등하게 존중되며, 빠르고 합리적인 의사결정이 가능한 문화를 지향합니다.
                </p>
            </div>
            <div class="culture-card scroll-fade-item">
                <h3 class="font-kor text-2xl font-bold mb-3 text-black">창의성과 도전</h3>
                <p class="font-kor text-neutral-500 leading-relaxed">
                    새로운 시도를 장려하고 실패를 배움의 기회로 삼습니다. 혁신적인 아이디어가 실제 프로젝트로 실현될 수 있는 환경입니다.
                </p>
            </div>
            <div class="culture-card scroll-fade-item">
                <h3 class="font-kor text-2xl font-bold mb-3 text-black">성장 지향</h3>
                <p class="font-kor text-neutral-500 leading-relaxed">
                    개인의 성장이 곧 회사의 성장입니다. 도서 구입, 컨퍼런스 참가, 스터디 그룹 등 다양한 성장 기회를 아낌없이 지원합니다.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="w-full bg-[#1a1a1a] py-32 rounded-t-[30px]">
    <div class="w-full max-w-[1400px] mx-auto px-6 md:px-12">
        <div class="text-center mb-16 scroll-fade-item">
            <h2 class="font-kor text-4xl md:text-5xl font-bold text-white mb-4">그리프는 이렇게 일합니다</h2>
            <p class="font-eng text-[#FAEB15] font-bold tracking-widest">BENEFITS & WELFARE</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">경쟁력 있는 연봉</h4>
                <p class="font-kor text-sm text-neutral-400">업계 최고 수준 대우</p>
            </div>
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">휴가 제도</h4>
                <p class="font-kor text-sm text-neutral-400">리프레시, 생일 휴가</p>
            </div>
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">건강 지원</h4>
                <p class="font-kor text-sm text-neutral-400">4대보험, 건강검진</p>
            </div>
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">교육 지원</h4>
                <p class="font-kor text-sm text-neutral-400">도서, 강의, 세미나</p>
            </div>
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">최신 장비</h4>
                <p class="font-kor text-sm text-neutral-400">맥북, 듀얼 모니터</p>
            </div>
            <div class="welfare-card scroll-fade-item">
                <div class="welfare-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                <h4 class="font-kor font-bold text-lg mb-1 text-white">사무 환경</h4>
                <p class="font-kor text-sm text-neutral-400">간식, 음료 무제한</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        gsap.registerPlugin(ScrollTrigger);

        // 상단 리스트 등장
        gsap.to(".fade-up-init", {
            y: 0,
            opacity: 1,
            duration: 0.8,
            stagger: 0.1,
            ease: "power2.out",
            delay: 0.1
        });

        // 하단 섹션 스크롤 등장
        gsap.utils.toArray('.scroll-fade-item').forEach(item => {
            gsap.to(item, {
                scrollTrigger: {
                    trigger: item,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                },
                y: 0,
                opacity: 1,
                duration: 0.8,
                ease: "power2.out"
            });
        });
    });
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>