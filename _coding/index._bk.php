<?php 
// [에러 확인용]
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. 공통 헤더 (DB 연결 및 HTML 시작 포함)
if (file_exists('inc/header.php')) {
    include 'inc/header.php';
} else {
    die("Error: inc/header.php Not Found");
}

// 2. 데이터 조회 (conn 객체는 header.php에서 연결됨)
if (!isset($conn) || $conn->connect_error) {
    die("DB Connection Error");
}

$sql_visual = "SELECT * FROM main_visuals WHERE is_visible = 1 ORDER BY id DESC LIMIT 1";
$res_visual = $conn->query($sql_visual);
$visual = ($res_visual && $res_visual->num_rows > 0) ? $res_visual->fetch_assoc() : null;
if (!$visual) { $visual = ['video_url' => 'https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4', 'text_1' => 'Be', 'text_2' => 'BOLD', 'text_3' => 'BE', 'text_4' => 'CREATIVE.']; }

$sql_projects = "SELECT * FROM projects WHERE status = 'published' AND created_at >= '2024-01-01' ORDER BY sort_order ASC LIMIT 6";
$res_projects = $conn->query($sql_projects);
$sql_count = "SELECT COUNT(*) as cnt FROM projects WHERE status = 'published' AND created_at >= '2024-01-01'";
$res_count = $conn->query($sql_count);
$total_projects = ($res_count && $res_count->num_rows > 0) ? $res_count->fetch_assoc()['cnt'] : 0;
?>

<style>
    .indicator-dot { transition: all 0.3s ease; height: 6px; background-color: #d4d4d4; }
    .indicator-dot.active { height: 24px; background-color: #1a1a1a; }
    
    /* 3번 텍스트(BE) 아웃라인 효과 */
    .text-stroke-din {
        -webkit-text-stroke: 1.5px white; 
        color: transparent; 
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

    <section id="main-visual-trigger" class="relative w-full h-[300vh] bg-[#FACC15]">
        <div id="sticky-wrapper" class="sticky top-0 w-full h-screen flex flex-col items-center justify-center overflow-hidden">
            <div id="video-container" class="relative w-[85%] h-[80vh] bg-black rounded-[40px] overflow-hidden shadow-2xl z-0 will-change-transform origin-center">
                <div class="absolute inset-0 w-full h-[120%] -top-[10%]" data-scroll data-scroll-speed="-0.1">
                    <video src="<?= htmlspecialchars($visual['video_url']) ?>" autoplay loop muted playsinline class="w-full h-full object-cover opacity-70"></video>
                    <div class="absolute inset-0 bg-black/30"></div>
                </div>
                <div class="absolute bottom-10 w-full text-center z-20">
                    <span class="text-[#FACC15] text-sm font-eng tracking-[0.3em] animate-bounce whitespace-nowrap inline-block">SCROLL DOWN</span>
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
                <?php if($res_projects && $res_projects->num_rows > 0): ?>
                    <?php 
                    $idx = 0;
                    while($row = $res_projects->fetch_assoc()): 
                        $idx++;
                        $responsive_class = ($idx > 9) ? "hidden lg:block" : (($idx > 6) ? "hidden md:block" : "");
                        $col_span = (rand(1, 10) <= 3) ? 'md:col-span-2' : 'col-span-1';
                        $aspect = ($col_span === 'md:col-span-2') ? ((rand(1, 10) <= 6) ? 'aspect-video' : 'aspect-square') : ((rand(1, 10) <= 5) ? 'aspect-[4/5]' : 'aspect-square');
                    ?>
                    <a href="/project_view.php?id=<?= $row['id'] ?>" class="group relative block w-full h-full bg-gray-100 overflow-hidden project-item opacity-0 translate-y-20 <?= $col_span ?> <?= $aspect ?> <?= $responsive_class ?>">
                        <?php 
                            $thumb = $row['thumbnail_path']; if($thumb && strpos($thumb, '/') !== 0) $thumb = '/'.$thumb;
                        ?>
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
                    <?php endwhile; ?>
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
                $services = [['01. EVENT / PROMOTION', '기획부터 현장까지...'], ['02. DESIGN', '브랜드의 시작부터...'], ['03. FILM / MEDIA', '광고부터 4K 라이브까지...'], ['04. STUDIO', '24시간 이용 가능한...']];
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
            <?php for($i=0; $i<20; $i++): for($n=1; $n<=11; $n++): $num=sprintf("%02d", $n); ?>
                <div class="flex items-center justify-center opacity-40 hover:opacity-100 transition-opacity duration-500 px-8">
                    <img src="img/inc/griff_client_logo_<?= $num ?>.svg" alt="Logo" class="h-[100px] w-auto grayscale hover:grayscale-0 transition-all">
                </div>
            <?php endfor; endfor; ?>
        </div>
    </section>

    <section id="contact-section" class="relative w-full z-20">
        <div class="absolute inset-0 flex flex-col md:flex-row">
            <div class="w-full md:w-1/2 h-[500px] md:h-full bg-[#DEE3E4]"></div>
            <div class="w-full md:w-1/2 h-[500px] md:h-full bg-[#FAF1EB]"></div>
        </div>
        <div class="relative w-full max-w-[1400px] mx-auto pl-6 pr-6 md:pl-12 md:pr-12">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <a href="/recruit_list.php" class="group relative flex flex-col justify-between py-16 pr-8 pl-0 h-[500px] bg-transparent overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="font-eng text-[20px] md:text-[40px] text-neutral-900 font-bold leading-[0.9] mb-6 antialiased group-hover:text-[#0D9788] transition-colors duration-300">RECRUIT<span class="text-[#0D9788]">.</span></h2>
                        <p class="font-kor text-[20px] md:text-[24px] text-neutral-600 font-medium leading-relaxed">그리프는 이런 분을 모시고 싶습니다</p>
                    </div>
                    <div class="relative z-10 mt-12">
                        <span class="block font-eng text-neutral-400 text-xs mb-3">지원하기</span>
                        <div class="inline-flex items-center gap-2 text-neutral-900 font-eng text-lg font-bold border-b border-neutral-900 pb-1 group-hover:border-[#0D9788] group-hover:text-[#0D9788] transition-colors">Apply Here <span class="group-hover:translate-x-2 transition-transform duration-300">→</span></div>
                    </div>
                </a>
                <a href="/inquiry_list.php" class="group relative flex flex-col justify-between py-16 pl-10 pr-0 h-[500px] bg-transparent overflow-hidden">
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
        .to("#main-text-wrapper", { color: "#FACC15", "--text-color": "#FACC15", duration: 1, ease: "power1.inOut" }, 0)
        .to(["#text-script", "#text-outline-font", "#text-outline-css", "#text-solid"], 
            { color: "#FACC15", webkitTextStrokeColor: "#FACC15", duration: 1 }, 0);
            
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
            { xPercent: 0, ease: "none", duration: 1100, repeat: -1 }
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