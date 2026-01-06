<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<style>
    /* [1. 전체 컨테이너] */
    .horizontal-container {
        width: 100%;
        height: 100vh;
        overflow: hidden;
        background-color: #000; 
        display: flex;
        align-items: center;
        position: relative;
    }

    /* [2. 트랙] */
    .horizontal-track {
        display: flex;
        flex-wrap: nowrap;
        height: 100%;
        align-items: center;
        width: max-content;
    }

    /* [3. 섹션 공통 스타일] */
    .service-section {
        display: flex;
        align-items: center;
        height: 100vh;
        width: 100vw; /* Snap을 위한 너비 고정 */
        padding-left: 10vw;
        padding-right: 5vw;
        position: relative;
        flex-shrink: 0;
        box-sizing: border-box;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* [배경 컬러 테마] */
    .bg-event { background: linear-gradient(135deg, #020202 0%, #051515 100%); }
    .bg-design { background: linear-gradient(135deg, #020202 0%, #120515 100%); }
    .bg-film { background: linear-gradient(135deg, #020202 0%, #0a1505 100%); }
    .bg-studio { background: linear-gradient(135deg, #020202 0%, #150a05 100%); }

    /* 배경 숫자 */
    .bg-number {
        position: absolute;
        top: 5vh;
        left: 2vw;
        font-family: 'URWDIN', sans-serif;
        font-size: 25vh;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.03);
        z-index: 0;
        pointer-events: none;
    }

    /* [4. 텍스트 영역] */
    .text-group {
        width: 32vw; 
        margin-right: 4vw; 
        z-index: 2;
        flex-shrink: 0;
    }

    /* 타이틀 */
    .service-title {
        font-family: 'URWDIN', sans-serif;
        font-size: 7vh; 
        font-weight: 700;
        line-height: 1.1;
        color: #ddd;
        text-transform: uppercase;
        margin-bottom: 2rem;
    }
    .service-title span.dot { color: #DC2626; }

    /* 본문 */
    .service-desc {
        font-family: 'Freesentation', sans-serif;
        font-size: 1.15rem;
        color: #888;
        line-height: 1.8;
        word-break: keep-all;
    }
    .service-desc strong {
        display: block;
        font-size: 1.4rem;
        color: white;
        margin-bottom: 0.8rem;
    }

    /* [5. 벤토 그리드] */
    .bento-container {
        display: grid;
        grid-template-columns: repeat(3, 16vw); 
        grid-template-rows: 25vh 25vh; 
        gap: 1.2vw;
        z-index: 2;
    }

    .bento-item {
        background: #222;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }

    .bento-item img, .bento-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .bento-item:hover img, .bento-item:hover video { transform: scale(1.05); }

    /* Grid Variations */
    .grid-type-a .bento-item:nth-child(1) { grid-column: span 2; grid-row: span 2; }
    .grid-type-b .bento-item:nth-child(2) { grid-row: span 2; }
    .grid-type-c .bento-item:nth-child(4) { grid-column: span 2; }

    /* Colors */
    .txt-cyan { color: #22d3ee; }
    .txt-purple { color: #c084fc; }
    .txt-lime { color: #a3e635; }
    
    /* [수정] STUDIO Color Updated */
    .txt-studio { color: #FAEB15; } 

    /* [6. 스크롤 다운 인디케이터] */
    .scroll-down-indicator {
        position: absolute;
        bottom: 5vh;
        right: 10vw; 
        display: flex;
        align-items: center;
        gap: 1rem;
        color: rgba(255,255,255,0.8);
        font-family: 'URWDIN', sans-serif;
        font-size: 0.9rem;
        letter-spacing: 0.1em;
        z-index: 10;
        animation: fadeBounce 2s infinite;
    }
    .scroll-line {
        width: 60px;
        height: 1px;
        background: rgba(255,255,255,0.6);
        position: relative;
    }
    .scroll-line::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: #fff;
        border-radius: 50%;
    }

    @keyframes fadeBounce {
        0%, 100% { opacity: 0.5; transform: translateX(0); }
        50% { opacity: 1; transform: translateX(10px); }
    }
    
    /* Intro Video Background */
    .intro-video-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }
    .intro-video-bg video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .intro-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5); 
        z-index: 1;
    }
</style>

<div class="horizontal-container">
    <div class="horizontal-track">

        <div class="service-section" style="width: 100vw; justify-content: center; padding: 0; position: relative;">
            <div class="intro-video-bg">
                <video src="https://unrealsummit16.cafe24.com/2025/challange25/unrealchallange25_movie_hq.webm" autoplay loop muted playsinline></video>
                <div class="intro-overlay"></div>
            </div>
            <div class="text-left pl-[10vw] relative z-10">
                <h1 class="service-title" style="font-size: 15vh; color: white; margin-bottom: 1rem;">
                    SERVICE<span class="dot">.</span>
                </h1>
                <p class="service-desc text-white text-2xl font-bold" style="max-width: 600px;">
                   그리프는 단순히 서비스를 제공하는 것이 아닌, 고객의 고민과 진심으로 마주하는 것부터 시작합니다. 브랜드의 본질을 깊이 이해하고, 기대를 뛰어넘는 가치를 창출하기 위해 끊임없이 탐구합니다. 특별한 결과물을 만듭니다.
                </p>
            </div>
            <div class="scroll-down-indicator">
                <span>SCROLL DOWN</span>
                <div class="scroll-line"></div>
            </div>
        </div>

        <div class="service-section bg-event">
            <span class="bg-number">01</span>
            <div class="text-group">
                <h2 class="service-title split-me">
                    <span class="txt-cyan">EVENT</span> & <br><span class="txt-cyan">PROMOTION</span>
                </h2>
                <div class="service-desc split-line">
                    <strong class="txt-cyan">결과가 말해줍니다.</strong>
                    많은 프로모션 프로젝트를 진행하며, 수많은 노하우와 실적을 쌓아왔습니다. 이벤트/프로모션이 가진 한계와 문제를 가장 빠른 방법으로 해결로 이끌어갑니다.
                </div>
            </div>
            
            <div class="bento-container grid-type-a">
                <div class="bento-item">
                    <video src="https://unrealsummit16.cafe24.com/2025/challange25/unrealchallange25_movie_hq.webm" autoplay loop muted playsinline></video>
                </div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1475721027767-4d529c14cbd2?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=500&q=80"></div>
            </div>
        </div>

        <div class="service-section bg-design">
            <span class="bg-number">02</span>
            <div class="text-group">
                <h2 class="service-title split-me">
                    <span class="txt-purple">DESIGN</span><br>ON/OFFLINE
                </h2>
                <div class="service-desc split-line">
                    <strong class="txt-purple">상상을 현실화 합니다.</strong>
                    우리는 크리에이티브로 목적에 충실한 디자인을 만듭니다. 디테일을 놓치지 않는 섬세함과 애정을 담아, 각 프로젝트에 생명을 불어넣습니다. 좋은 디자인은 늘 강력한 무기입니다.
                </div>
            </div>
            
            <div class="bento-container grid-type-b">
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1626785774583-16191f2720ff?w=600&q=80"></div>
                <div class="bento-item">
                    <video src="https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4" autoplay loop muted playsinline></video>
                </div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1558655146-d09347e0c766?w=600&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1572044162444-ad60f128bdea?w=600&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1600607686527-6fb886090705?w=600&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?w=600&q=80"></div>
            </div>
        </div>

        <div class="service-section bg-film">
            <span class="bg-number">03</span>
            <div class="text-group">
                <h2 class="service-title split-me">
                    <span class="txt-lime">FILM</span> & <br><span class="txt-lime">LIVE</span>
                </h2>
                <div class="service-desc split-line">
                    <strong class="txt-lime">4K 라이브 스트림까지</strong>
                    광고, 홍보영상, 인터뷰부터 행사영상, 스케치영상, 라이브스트림까지. 그리프 영상팀은 다양한 장르를 4K 고화질로 시네마틱하게 담아냅니다. 섬세한 연출과 감각적인 촬영으로 메시지를 전달합니다.
                </div>
            </div>
            
            <div class="bento-container grid-type-c">
                <div class="bento-item"><video src="https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4" autoplay loop muted playsinline></video></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1478720568477-152d9b164e63?w=500&q=80"></div>
                <div class="bento-item">
                    <video src="https://unrealsummit16.cafe24.com/2025/challange25/unrealchallange25_movie_hq.webm" autoplay loop muted playsinline></video>
                </div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1574717024653-61fd2cf4d44c?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1518930182868-b3941cb91560?w=500&q=80"></div>
            </div>
        </div>

        <div class="service-section bg-studio">
            <span class="bg-number">04</span>
            <div class="text-group">
                <h2 class="service-title split-me">
                    GRIFF <br><span class="txt-studio">STUDIO</span>
                </h2>
                <div class="service-desc split-line">
                    <strong class="txt-studio">24시간, 언제나 이용가능</strong>
                    24시간 고화질 라이브 스트림부터 시네마틱 유튜브 촬영까지. 그리프 스튜디오는 전문 장비로 완벽한 퀄리티를 보장합니다. 성수동에 위치해 뛰어난 접근성과 깔끔한 인테리어로 최적의 촬영 환경을 제공합니다.
                </div>
            </div>
            
            <div class="bento-container grid-type-a">
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1534120247760-c44c3e4a62f1?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1527529482837-4698179dc6ce?w=500&q=80"></div>
                <div class="bento-item"><img src="https://images.unsplash.com/photo-1471341971474-27c524463543?w=500&q=80"></div>
                <div class="bento-item">
                    <video src="https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4" autoplay loop muted playsinline></video>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/DrawSVGPlugin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollSmoother.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollToPlugin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/SplitText.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", (event) => {
        gsap.registerPlugin(DrawSVGPlugin, ScrollTrigger, ScrollSmoother, ScrollToPlugin, SplitText);

        const container = document.querySelector(".horizontal-container");
        const track = document.querySelector(".horizontal-track");
        const sections = gsap.utils.toArray(".service-section");

        function initScroll() {
            let trackWidth = track.scrollWidth;
            let windowWidth = window.innerWidth;
            let scrollAmount = trackWidth - windowWidth; 

            if(window.currentScrollTween) window.currentScrollTween.kill();

            window.currentScrollTween = gsap.to(track, {
                x: -scrollAmount,
                ease: "none",
                scrollTrigger: {
                    trigger: container,
                    pin: true,
                    scrub: 1,
                    // 마지막 섹션 후 500px 더 스크롤해야 핀이 풀리고 푸터로 이동
                    end: () => "+=" + (scrollAmount + 500), 
                    invalidateOnRefresh: true,
                    // [Snap] Sticky Effect
                    snap: {
                        snapTo: 1 / (sections.length - 1), 
                        duration: {min: 0.2, max: 0.4}, 
                        delay: 0.1, 
                        ease: "power1.inOut" 
                    }
                }
            });
            
            return window.currentScrollTween;
        }

        let scrollTween = initScroll();

        // 2. Title Animation
        const charTitles = document.querySelectorAll(".split-me");
        const splitChar = new SplitText(charTitles, { type: "chars" });
        
        splitChar.chars.forEach((char) => {
            gsap.from(char, {
                yPercent: gsap.utils.random(-150, 150),
                rotation: gsap.utils.random(-30, 30),
                opacity: 0,
                scale: 0.5,
                duration: 1.2,
                ease: "back.out(1.5)",
                scrollTrigger: {
                    trigger: char,
                    containerAnimation: scrollTween,
                    start: "left 95%",
                    toggleActions: "play none none reverse",
                }
            });
        });

        // 3. Description Animation
        const lineDescs = document.querySelectorAll(".split-line");
        const splitLine = new SplitText(lineDescs, { type: "lines" });

        splitLine.lines.forEach((line) => {
            gsap.from(line, {
                y: 50,          
                opacity: 0,     
                duration: 1,
                ease: "power3.out", 
                scrollTrigger: {
                    trigger: line,
                    containerAnimation: scrollTween,
                    start: "left 85%", 
                    stagger: 0.1,      
                    toggleActions: "play none none reverse"
                }
            });
        });

        // 4. Bento Grid Animation
        const bentoContainers = document.querySelectorAll(".bento-container");
        
        bentoContainers.forEach((grid) => {
            const items = grid.querySelectorAll(".bento-item");
            
            gsap.from(items, {
                y: 100,         
                opacity: 0,
                scale: 0.8,
                duration: 0.8,
                ease: "back.out(1.2)",
                stagger: {
                    amount: 0.6, 
                    from: "random" 
                },
                scrollTrigger: {
                    trigger: grid,
                    containerAnimation: scrollTween,
                    start: "left 90%", 
                    toggleActions: "play none none reverse"
                }
            });
        });
    });
</script>

<?php 
if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php";
?>