<?php 
// 1. 에러 확인
ini_set('display_errors', 0); // 실서비스 시 0
error_reporting(E_ALL);

// 2. DB 연결 (PDO 방식)
require_once 'inc/db_connect.php'; 

// 3. 데이터 조회 (관리자에서 설정한 값 불러오기)
try {
    // A. 메인 비주얼 설정
    $visual = $pdo->query("SELECT * FROM main_visuals WHERE id = 1")->fetch();
    if (!$visual) { 
        // 데이터 없을 시 기본값
        $visual = [
            'video_url' => 'https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4',
            'text_1' => 'Be', 'text_2' => 'BOLD', 'text_3' => 'BE', 'text_4' => 'CREATIVE.',
            'bg_color' => '#FACC15', 'scroll_text_color' => '#FFFFFF'
        ]; 
    }

    // B. SEO 설정 (헤더에서 사용할 변수 미리 정의)
    $meta = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();
    if ($meta) {
        $page_title = $meta['og_title'];
        $page_desc = $meta['og_desc'];
        $page_image = $meta['og_image'];
    }

    // C. 클라이언트 리스트
    $clients = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();

    // D. 프로젝트 리스트 (기존 로직 유지하되 PDO로 변경)
    $sql_projects = "SELECT * FROM projects WHERE status = 'published' AND created_at >= '2024-01-01' ORDER BY sort_order ASC LIMIT 6";
    $projects = $pdo->query($sql_projects)->fetchAll();

    $cnt_stmt = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'published' AND created_at >= '2024-01-01'");
    $total_projects = $cnt_stmt->fetchColumn();

} catch (Exception $e) {
    // DB 에러 시 조용히 넘어가거나 기본값 유지
}

// 4. 헤더 로드
include 'inc/header.php'; 
?>

<style>
    .indicator-dot { transition: all 0.3s ease; height: 6px; background-color: #d4d4d4; }
    .indicator-dot.active { height: 24px; background-color: #1a1a1a; }
    
    .text-stroke-din {
        -webkit-text-stroke: 1.5px white; 
        color: transparent; 
    }
    
    /* [동적 스타일] 관리자 설정 배경색 적용 */
    #main-visual-trigger {
        background-color: <?= $visual['bg_color'] ?>;
        transition: background-color 0.5s ease;
    }
    
    /* 스크롤 다운 텍스트 색상 (배경색과 동일하게 시작) */
    .scroll-down-text {
        color: <?= $visual['bg_color'] ?>;
    }
</style>

<div id="scroll-indicator" class="fixed right-6 top-1/2 -translate-y-1/2 z-50 flex flex-col gap-3 p-2 bg-white/50 backdrop-blur-md rounded-full opacity-0 pointer-events-none transition-opacity duration-500">
    <a href="#main-visual-trigger" class="indicator-dot w-1.5 rounded-full block" data-target="#main-visual-trigger" title="Home"></a>
    <a href="#project-section" class="indicator-dot w-1.5 rounded-full block" data-target="#project-section" title="Projects"></a>
    <a href="#service-section" class="indicator-dot w-1.5 rounded-full block" data-target="#service-section" title="Service"></a>
    <a href="#client-section" class="indicator-dot w-1.5 rounded-full block" data-target="#client-section" title="Clients"></a>
    <a href="#contact-section" class="indicator-dot w-1.5 rounded-full block" data-target="#contact-section" title="Contact"></a>
    <a href="#studio-section" class="indicator-dot w-1.5 rounded-full block" data-target="#studio-section" title="Studio"></a>
</div>

<section id="main-visual-trigger" class="relative w-full h-[300vh]">
    <div id="sticky-wrapper" class="sticky top-0 w-full h-screen flex flex-col items-center justify-center overflow-hidden">
        
        <div id="video-container" class="relative w-[85%] h-[80vh] bg-black rounded-[40px] overflow-hidden shadow-2xl z-0 will-change-transform origin-center">
            <div class="absolute inset-0 w-full h-[120%] -top-[10%]" data-scroll data-scroll-speed="-0.1">
                <video src="<?= htmlspecialchars($visual['video_url']) ?>" autoplay loop muted playsinline class="w-full h-full object-cover opacity-70"></video>
                <div class="absolute inset-0 bg-black/30"></div>
            </div>
            <div class="absolute bottom-10 w-full text-center z-20">
                <span class="scroll-down-text text-sm font-eng tracking-[0.3em] animate-bounce whitespace-nowrap inline-block">SCROLL DOWN</span>
            </div>
        </div>
        
        <div id="main-text-wrapper" class="absolute inset-0 flex flex-col items-center justify-center z-10 pointer-events-none select-none leading-[0.95] pb-24" style="color: transparent; --text-color: #fff;">
            
            <div class="flex items-baseline gap-2 md:gap-4 translate-y-4 opacity-0 animate-fade-up">
                <span class="font-script text-[50px] md:text-[90px] lg:text-[120px] text-white transition-colors duration-500" id="text-script">
                    <?= htmlspecialchars($visual['text_1']) ?>
                </span>
                <span class="font-outline text-[50px] md:text-[80px] lg:text-[110px] tracking-tighter text-white transition-colors duration-500" id="text-outline-font">
                    <?= htmlspecialchars($visual['text_2']) ?>
                </span>
            </div>
            
            <div class="flex items-baseline gap-2 md:gap-4 -mt-1 md:-mt-4 lg:-mt-6 opacity-0 animate-fade-up" style="animation-delay: 0.2s;">
                <span class="font-eng font-bold text-stroke-din text-[50px] md:text-[80px] lg:text-[110px] tracking-tighter transition-colors duration-500" id="text-outline-css">
                    <?= htmlspecialchars($visual['text_3']) ?>
                </span>
                <span class="font-eng font-bold text-[50px] md:text-[80px] lg:text-[110px] tracking-tighter text-white transition-colors duration-500" id="text-solid">
                    <?= htmlspecialchars($visual['text_4']) ?>
                </span>
            </div>
        </div>
    </div>
</section>

<section id="project-section" class="relative z-20 bg-white pt-32 -mt-[40vh] pb-32 border-t border-neutral-200">
    <div class="w-full max-w-[1400px] mx-auto pl-6 pr-6 md:pl-12 md:pr-12">
        <div class="flex flex-col md:flex-row justify-between items-end mb-16">
            <div class="overflow-hidden">
                <h2 class="font-eng text-[20px] md:text-[40px] font-bold leading-tight py-2 text-neutral-900 reveal-text translate-y-[100%]">
                    PROJECTS<span class="text-[#FACC15] ml-2">.</span>
                </h2>
            </div>
        </div>

        <div id="project-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 grid-flow-dense">
            <?php if(count($projects) > 0): ?>
                <?php 
                $idx = 0;
                foreach($projects as $row): 
                    $idx++;
                    $responsive_class = ($idx > 9) ? "hidden lg:block" : (($idx > 6) ? "hidden md:block" : "");
                    $col_span = (rand(1, 10) <= 3) ? 'md:col-span-2' : 'col-span-1';
                    $aspect = ($col_span === 'md:col-span-2') ? ((rand(1, 10) <= 6) ? 'aspect-video' : 'aspect-square') : ((rand(1, 10) <= 5) ? 'aspect-[4/5]' : 'aspect-square');
                    
                    $thumb = $row['thumbnail_path']; 
                    if($thumb && strpos($thumb, '/') !== 0) $thumb = '/'.$thumb;
                ?>
                <a href="/project_view.php?id=<?= $row['id'] ?>" class="group relative block w-full h-full bg-gray-100 overflow-hidden project-item opacity-0 translate-y-20 <?= $col_span ?> <?= $aspect ?> <?= $responsive_class ?>">
                    <?php if(!empty($thumb) && file_exists($_SERVER['DOCUMENT_ROOT'] . $thumb)): ?>
                        <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400 font-eng text-sm bg-neutral-200">NO IMAGE</div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/60 transition-colors duration-500 p-6 flex flex-col justify-end">
                        <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                            <span class="font-eng text-[10px] font-bold text-white/80 mb-2 block uppercase tracking-widest"><?= htmlspecialchars($row['category']) ?></span>
                            <h3 class="font-eng text-base md:text-xl font-bold text-white leading-tight mb-2 line-clamp-2"><?= htmlspecialchars($row['title']) ?></h3>
                            <p class="font-kor text-white/70 text-[11px] truncate"><?= !empty($row['client_name']) ? "Client. " . htmlspecialchars($row['client_name']) : "" ?></p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-32 text-center text-neutral-400 font-kor">등록된 프로젝트가 없습니다.</div>
            <?php endif; ?>
        </div>

        <?php if($total_projects > 6): ?>
        <div class="w-full flex justify-center mt-20">
            <button id="btn-load-more" data-offset="6" class="inline-block px-10 pt-[18px] pb-[14px] border border-neutral-300 rounded-full font-eng text-sm font-bold text-neutral-900 hover:bg-neutral-900 hover:text-white hover:border-neutral-900 transition-all duration-300">
                + VIEW MORE
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<section id="service-section" class="py-32 bg-[#FAF7EB] relative z-20">
    <div class="w-full max-w-[1400px] mx-auto pl-6 pr-6 md:pl-12 md:pr-12 grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">
        <div class="lg:col-span-4 sticky top-32">
            <h2 class="font-eng text-[20px] md:text-[40px] font-bold leading-[0.9] text-neutral-900 mb-6">SERVICE<span class="text-[#F86F18]">.</span></h2>
            <p class="font-kor text-[20px] md:text-[24px] leading-snug font-medium text-neutral-900 break-keep">기획·디자인·제작·운영을<br class="hidden md:block"> 하나의 흐름으로 완성합니다</p>
        </div>
        <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-[10px]">
            <?php 
            $services = [['01. EVENT / PROMOTION', '이벤트/프로모션이 가진 한계와 문제를 가장 빠른 방법으로 해결로 이끌어갑니다.'], ['02. DESIGN', '우리는 크리에이티브로 목적에 충실한 디자인을 만듭니다. 디테일을 놓치지 않는 섬세함과 애정을 담아, 각 프로젝트에 생명을 불어넣습니다.'], ['03. FILM / MEDIA', '4K 고화질로 시네마틱하게 담아냅니다. 섬세한 연출과 감각적인 촬영으로 메시지를 전달합니다.'], ['04. STUDIO', '24시간 고화질 라이브 스트림부터 시네마틱 유튜브 촬영까지. 그리프 스튜디오는 전문
장비로 완벽한 퀄리티를 보장합니다. ']];
            foreach($services as $svc): ?>
            <div class="service-item relative pt-8 pb-8 px-6 -mx-6 transition-all duration-300 group hover:bg-neutral-900 hover:-translate-y-4 rounded-none">
                <div class="service-line absolute top-0 left-0 h-[1px] bg-neutral-900 w-0 group-hover:bg-white transition-colors"></div>
                <h3 class="font-eng text-2xl font-bold mb-4 text-neutral-900 group-hover:text-white transition-colors"><?= $svc[0] ?></h3>
                <p class="font-kor text-neutral-600 leading-relaxed break-keep group-hover:text-white/90 transition-colors"><?= $svc[1] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="client-section" class="pt-24 pb-36 overflow-hidden bg-white relative z-20">
    <div class="w-full max-w-[1400px] mx-auto pl-6 pr-6 md:pl-12 md:pr-12 mb-12">
        <span class="font-eng text-[20px] md:text-[40px] font-bold leading-[0.9] text-neutral-900">CLIENTS<span class="text-[#1CF1DF]">.</span></span>
    </div>
    <div class="flex gap-16 w-max marquee-track">
        <?php 
        // 클라이언트 데이터가 있으면 반복
        if (count($clients) > 0) {
            // 마퀴 효과가 자연스럽게 보이도록 데이터 개수에 따라 반복 횟수 조정
            $repeat = count($clients) < 10 ? 10 : 3;
            for($i=0; $i < $repeat; $i++): 
                foreach($clients as $c): 
        ?>
            <div class="flex items-center justify-center opacity-40 hover:opacity-100 transition-opacity duration-500 px-8">
                <img src="<?= htmlspecialchars($c['logo_path']) ?>" alt="<?= htmlspecialchars($c['title']) ?>" class="h-[80px] md:h-[100px] w-auto grayscale hover:grayscale-0 transition-all object-contain">
            </div>
        <?php 
                endforeach; 
            endfor; 
        } else {
            // 데이터 없을 때 빈 영역 표시 (레이아웃 깨짐 방지)
            echo '<div class="px-12 py-10 text-gray-300 font-eng">No Clients Data</div>';
        }
        ?>
    </div>
</section>

<section id="contact-section" class="relative w-full z-20">
    <div class="absolute inset-0 flex flex-col md:flex-row">
        <div class="w-full md:w-1/2 h-[500px] md:h-full bg-[#DEE3E4]"></div>
        <div class="w-full md:w-1/2 h-[500px] md:h-full bg-[#FAF1EB]"></div>
    </div>
    <div class="relative w-full max-w-[1400px] mx-auto pl-6 pr-6 md:pl-12 md:pr-12">
        <div class="grid grid-cols-1 md:grid-cols-2">
            <a href="/recruit/recruit_list.php" class="group relative flex flex-col justify-between py-16 pr-8 pl-0 h-[500px] bg-transparent overflow-hidden">
                <div class="relative z-10">
                    <h2 class="font-eng text-[20px] md:text-[40px] text-neutral-900 font-bold leading-[0.9] mb-6 antialiased group-hover:text-[#0D9788] transition-colors duration-300">RECRUIT<span class="text-[#0D9788]">.</span></h2>
                    <p class="font-kor text-[20px] md:text-[24px] text-neutral-600 font-medium leading-relaxed">그리프는 이런 분을 모시고 싶습니다</p>
                </div>
                <div class="relative z-10 mt-12">
                    <span class="block font-eng text-neutral-400 text-xs mb-3">지원하기</span>
                    <div class="inline-flex items-center gap-2 text-neutral-900 font-eng text-lg font-bold border-b border-neutral-900 pb-1 group-hover:border-[#0D9788] group-hover:text-[#0D9788] transition-colors">Apply Here <span class="group-hover:translate-x-2 transition-transform duration-300">→</span></div>
                </div>
            </a>
            <a href="/contact/contact.php" class="group relative flex flex-col justify-between py-16 pl-10 pr-0 h-[500px] bg-transparent overflow-hidden">
                <div class="relative z-10">
                    <h2 class="font-eng text-[20px] md:text-[40px] text-neutral-900 font-bold leading-[0.9] mb-6 antialiased group-hover:text-[#F86F18] transition-colors duration-300">CONTACT US<span class="text-[#F86F18]">.</span></h2>
                    <p class="font-kor text-[20px] md:text-[24px] text-neutral-600 font-medium leading-relaxed">프로젝트 이야기를 들려주세요.</p>
                </div>
                <div class="relative z-10 mt-12">
                    <span class="block font-eng text-neutral-400 text-xs mb-3">문의하기</span>
                    <div class="inline-flex items-center gap-2 text-neutral-900 font-eng text-lg font-bold border-b border-neutral-900 pb-1 group-hover:border-[#F86F18] group-hover:text-[#F86F18] transition-colors">Get in Touch <span class="group-hover:translate-x-2 transition-transform duration-300">→</span></div>
                </div>
            </a>
        </div>
    </div>
</section>

<?php 
if (file_exists('inc/studio_shortcut.php')) {
    include 'inc/studio_shortcut.php';
}
?>

<script>
    const lenis = new Lenis({ duration: 1.2, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), smooth: true });
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);

    gsap.registerPlugin(ScrollTrigger);
    gsap.to("body", { opacity: 1, duration: 0.5 });

    // [중요] DB에서 가져온 색상 변수 적용
    const dynamicScrollColor = "<?= $visual['scroll_text_color'] ?>"; 

    const mainTl = gsap.timeline({
        scrollTrigger: {
            trigger: "#main-visual-trigger", 
            start: "top top",
            end: "bottom bottom", 
            scrub: 0, 
            pin: "#sticky-wrapper",
            pinSpacing: false,
        }
    });

    mainTl.to("#video-container", { width: "100%", height: "100vh", borderRadius: "0px", duration: 1, ease: "power2.inOut" }, 0)
    .to("#main-text-wrapper", { color: dynamicScrollColor, "--text-color": dynamicScrollColor, duration: 1, ease: "power1.inOut" }, 0)
    .to(["#text-script", "#text-outline-font", "#text-outline-css", "#text-solid"], 
        { color: dynamicScrollColor, webkitTextStrokeColor: dynamicScrollColor, duration: 1 }, 0);
        
    mainTl.to({}, { duration: 10 }); 
    mainTl.to("#main-text-wrapper", { opacity: 0.2, duration: 1, ease: "none" });

    gsap.to(".reveal-text", { y: 0, duration: 1, ease: "power3.out", scrollTrigger: { trigger: "#next-section", start: "top 70%" } });
    gsap.to(".project-item:not(.hidden)", { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power3.out", scrollTrigger: { trigger: "#project-section", start: "top 60%" } });
    
    gsap.utils.toArray('.service-line').forEach(line => {
        gsap.to(line, { width: "100%", duration: 1.5, ease: "power3.inOut", scrollTrigger: { trigger: line.parentElement, start: "top 85%" } });
    });

    gsap.utils.toArray('[data-scroll]').forEach(el => {
        gsap.to(el, { y: () => (1 - (el.getAttribute('data-scroll-speed') || 0)) * 100, ease: "none", scrollTrigger: { trigger: el.parentElement, start: "top bottom", end: "bottom top", scrub: 0 } });
    });

    gsap.fromTo(".marquee-track", 
        { xPercent: -50 }, 
        { xPercent: 0, ease: "none", duration: 880, repeat: -1 } /* 속도 조절 1100 -> 60 */
    );

    // [인디케이터 로직]
    const indicator = document.getElementById('scroll-indicator');
    const dots = document.querySelectorAll('.indicator-dot');

    ScrollTrigger.create({
        trigger: "#project-section",
        start: "top center",
        onEnter: () => gsap.to(indicator, { opacity: 1, pointerEvents: "auto", duration: 0.5 }),
        onLeaveBack: () => gsap.to(indicator, { opacity: 0, pointerEvents: "none", duration: 0.5 })
    });

    dots.forEach(dot => {
        const targetId = dot.getAttribute('data-target');
        if (targetId) {
            ScrollTrigger.create({
                trigger: targetId,
                start: "top center",
                end: "bottom center",
                toggleClass: { targets: dot, className: "active" }
            });
        }
    });

    // [AJAX Load More]
    const loadMoreBtn = document.getElementById('btn-load-more');
    const projectGrid = document.getElementById('project-grid');

    if(loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            if (this.getAttribute('data-status') === 'list') {
                window.location.href = '/project_list.php';
                return;
            }

            const offset = parseInt(this.getAttribute('data-offset'));
            const originalText = this.innerText;
            
            this.innerText = "LOADING...";
            this.disabled = true;

            fetch(`inc/get_more_project.php?offset=${offset}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(data => {
                    if(data.trim() === "") {
                        loadMoreBtn.innerText = "VIEW ALL PROJECTS";
                        loadMoreBtn.disabled = false;
                        
                        loadMoreBtn.classList.remove('border-neutral-300', 'text-neutral-900', 'hover:bg-neutral-900', 'hover:text-white', 'hover:border-neutral-900');
                        loadMoreBtn.classList.add('bg-[#FACC15]', 'border-[#FACC15]', 'text-black', 'hover:bg-black', 'hover:text-[#FACC15]', 'hover:border-black');
                        
                        loadMoreBtn.setAttribute('data-status', 'list');
                    } else {
                        projectGrid.insertAdjacentHTML('beforeend', data);
                        loadMoreBtn.setAttribute('data-offset', offset + 6);
                        loadMoreBtn.innerText = originalText;
                        loadMoreBtn.disabled = false;

                        if (typeof gsap !== 'undefined') {
                            gsap.fromTo(".project-item.new-item", 
                                { y: 50, opacity: 0 }, 
                                { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power3.out" }
                            );
                        }
                        
                        document.querySelectorAll('.project-item.new-item').forEach(el => el.classList.remove('new-item'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadMoreBtn.innerText = originalText;
                    loadMoreBtn.disabled = false;
                });
        });
    }
</script>

<?php include 'inc/footer.php'; ?>