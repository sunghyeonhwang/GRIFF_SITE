<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<style>
    /* 1. 배경 그라데이션 (좌->우) */
    .horizontal-container {
        width: 100%;
        height: 100vh;
        overflow: hidden;
        /* Deep Dark Gradient */
        background: linear-gradient(90deg, #050505 0%, #1a1a1a 50%, #000000 100%);
        display: flex;
        align-items: center;
    }

    /* 트랙 */
    .horizontal-track {
        display: flex;
        flex-wrap: nowrap;
        height: 100%;
        align-items: center;
        padding: 0 10vw;
        width: max-content;
    }

    /* 섹션 레이아웃 */
    .service-section {
        display: flex;
        align-items: center;
        gap: 5rem;
        padding-right: 15vw;
        flex-shrink: 0;
    }

    /* 텍스트 그룹 */
    .text-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 35vw; /* 텍스트 영역 너비 제한 */
        z-index: 2;
    }

    /* 2. 타이틀 크기 축소 (13vh -> 9vh) */
    .service-title {
        font-family: 'URWDIN', sans-serif;
        font-size: 9vh; 
        font-weight: 700;
        line-height: 1;
        color: #333;
        text-transform: uppercase;
        margin-bottom: 2rem;
        white-space: nowrap;
    }

    /* 형광색 키워드 */
    .highlight-text { color: white; }

    /* 본문 텍스트 */
    .service-desc {
        font-family: 'Freesentation', sans-serif;
        font-size: 1.2rem;
        color: #999;
        line-height: 1.7;
        word-break: keep-all;
    }
    
    .service-desc strong {
        display: block;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: white;
    }

    /* 4. 랜덤 미디어 그리드 (6개 아이템 기준) */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3열 */
        grid-template-rows: repeat(2, 120px); /* 2행 (높이 고정) */
        gap: 10px;
        width: 40vw; /* 그리드 전체 너비 */
        flex-shrink: 0;
    }

    /* 그리드 아이템 공통 */
    .grid-item {
        background: #222;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }

    .grid-item img, .grid-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .grid-item:hover img, .grid-item:hover video { transform: scale(1.1); }

    /* [랜덤성 부여] CSS Grid Span 활용 */
    /* 첫 번째 아이템은 크게 (2x2) */
    .grid-item:nth-child(1) { grid-column: span 2; grid-row: span 2; }
    /* 나머지는 1x1 자동 배치 */
</style>

<div class="horizontal-container">
    <div class="horizontal-track">

        <div class="service-section">
            <div class="text-content">
                <h1 class="service-title split-me">
                    <span class="text-white">SERVICE</span><span class="text-red-600">.</span>
                </h1>
                <div class="service-desc split-desc text-white text-2xl font-bold">
                    그리프가 제공하는<br>모든 크리에이티브 솔루션
                </div>
            </div>
        </div>

        <div class="service-section">
            <div class="text-content">
                <h2 class="service-title split-me">
                    <span class="highlight-text text-cyan-400">EVENT</span> / <span class="highlight-text text-cyan-400">PROMOTION</span>
                </h2>
                <div class="service-desc split-desc">
                    <strong class="text-cyan-400">결과가 말해줍니다.</strong>
                    업계와 분야를 불문하고 다양한 장르의 프로젝트에 참여하면서, 고객이 요구하는 바를 배우고 수많은 노하우와 실적을 쌓아왔습니다. 디자인과 관련된 모든 문제를 가장 빠른 방법으로 해결로 이끌어갑니다.
                </div>
            </div>
            
            <div class="media-grid">
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1475721027767-4d529c14cbd2?w=400&q=80" alt=""></div>
            </div>
        </div>

        <div class="service-section">
            <div class="text-content">
                <h2 class="service-title split-me">
                    <span class="highlight-text text-purple-400">DESIGN</span> CREATIVE
                </h2>
                <div class="service-desc split-desc">
                    <strong class="text-purple-400">상상을 현실화 합니다.</strong>
                    우리는 크리에이티브로 목적에 충실한 디자인을 만듭니다. 디테일을 놓치지 않는 섬세함과 애정을 담아, 각 프로젝트에 생명을 불어넣습니다. 머릿속 아이디어가 손끝을 타고 그래픽으로 펼쳐집니다.
                </div>
            </div>
            <div class="media-grid">
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1626785774583-16191f2720ff?w=800&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1558655146-d09347e0c766?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1572044162444-ad60f128bdea?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1600607686527-6fb886090705?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?w=400&q=80" alt=""></div>
            </div>
        </div>

        <div class="service-section">
            <div class="text-content">
                <h2 class="service-title split-me">
                    <span class="highlight-text text-lime-400">FILM</span> / <span class="highlight-text text-lime-400">LIVE</span>
                </h2>
                <div class="service-desc split-desc">
                    <strong class="text-lime-400">4K 라이브 스트림까지</strong>
                    그리프 영상팀은 다양한 장르를 4K 고화질로 시네마틱하게 담아냅니다. 섬세한 연출과 감각적인 촬영으로 브랜드의 메시지를 생생하게 전달하며, 단순한 기록을 넘어 감동을 전하는 영상을 만듭니다.
                </div>
            </div>
            <div class="media-grid">
                <div class="grid-item"><video src="https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4" autoplay loop muted playsinline></video></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1478720568477-152d9b164e63?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1536240478700-b869070f9279?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1574717024653-61fd2cf4d44c?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1518930182868-b3941cb91560?w=400&q=80" alt=""></div>
            </div>
        </div>

        <div class="service-section">
            <div class="text-content">
                <h2 class="service-title split-me">
                    GRIFF <span class="highlight-text text-orange-400">STUDIO</span>
                </h2>
                <div class="service-desc split-desc">
                    <strong class="text-orange-400">24시간, 언제나 이용가능</strong>
                    그리프 스튜디오는 전문 장비로 완벽한 퀄리티를 보장합니다. 성수동에 위치해 뛰어난 접근성과 깔끔한 인테리어로 최적의 촬영 환경을 제공합니다.
                </div>
            </div>
            <div class="media-grid">
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1534120247760-c44c3e4a62f1?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1527529482837-4698179dc6ce?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1471341971474-27c524463543?w=400&q=80" alt=""></div>
                <div class="grid-item"><img src="https://images.unsplash.com/photo-1621619856624-42fd193a0661?w=400&q=80" alt=""></div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://assets.codepen.io/16327/SplitText3.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        gsap.registerPlugin(ScrollTrigger, SplitText);

        const container = document.querySelector(".horizontal-container");
        const track = document.querySelector(".horizontal-track");

        // 1. 가로 스크롤 설정
        let trackWidth = track.scrollWidth;
        let windowWidth = window.innerWidth;
        let scrollAmount = trackWidth - windowWidth;

        let scrollTween = gsap.to(track, {
            x: -scrollAmount,
            ease: "none",
            scrollTrigger: {
                trigger: container,
                pin: true,
                scrub: 1,
                end: "+=6000", // 이동 거리 조금 늘림
                invalidateOnRefresh: true,
            }
        });

        // 2. 타이틀 (알파벳) 애니메이션
        const titles = document.querySelectorAll(".split-me");
        const splitTitle = new SplitText(titles, { type: "chars, words" });
        
        splitTitle.chars.forEach((char) => {
            const randomY = gsap.utils.random(-200, 200);
            const randomRot = gsap.utils.random(-40, 40);

            gsap.from(char, {
                yPercent: randomY,
                rotation: randomRot,
                opacity: 0,
                scale: 0.5,
                duration: 1,
                ease: "back.out(1.5)",
                scrollTrigger: {
                    trigger: char,
                    containerAnimation: scrollTween,
                    start: "left 95%",
                    toggleActions: "play none none reverse",
                }
            });
        });

        // 3. [추가] 한글 본문 애니메이션 (SplitText: Words)
        const descs = document.querySelectorAll(".split-desc");
        const splitDesc = new SplitText(descs, { type: "lines, words" }); // 줄, 단어 단위 분할

        splitDesc.words.forEach((word) => {
            gsap.from(word, {
                y: 30,          // 아래에서
                opacity: 0,     // 투명하게
                duration: 0.8,
                ease: "power3.out",
                scrollTrigger: {
                    trigger: word,
                    containerAnimation: scrollTween,
                    start: "left 90%", // 타이틀보다 살짝 늦게 등장
                    toggleActions: "play none none reverse",
                }
            });
        });

        // 4. [추가] 미디어 그리드 아이템 애니메이션
        const gridItems = document.querySelectorAll(".grid-item");
        gridItems.forEach((item, index) => {
            // 랜덤성 추가: 홀수/짝수 인덱스에 따라 등장 방향 다르게
            const fromY = index % 2 === 0 ? 50 : -50; 
            
            gsap.from(item, {
                scale: 0.5,
                opacity: 0,
                y: fromY, // 위아래 교차 등장
                rotation: gsap.utils.random(-5, 5), // 살짝 회전하며 등장
                duration: 1,
                ease: "back.out(1.2)",
                scrollTrigger: {
                    trigger: item,
                    containerAnimation: scrollTween,
                    start: "left 100%", // 화면 끝에 닿자마자 시작
                    stagger: 0.1, // 순차 등장
                    toggleActions: "play none none reverse"
                }
            });
        });

    });
</script>

<?php 
if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php";
?>