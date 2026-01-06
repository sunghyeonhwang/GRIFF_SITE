<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<style>
    /* [기본 설정] */
    body {
        background-color: #ffffff;
        color: #1a1a1a;
        overflow-x: hidden;
    }

    /* 폰트 유틸리티 */
    .font-urw { font-family: 'URWDIN', sans-serif; }
    .font-free { font-family: 'Freesentation', sans-serif; }

    /* 섹션 공통 여백 */
    .about-section {
        padding: 120px 6vw;
        position: relative;
        border-bottom: 1px solid #e5e5e5;
    }

    /* 컨텐츠 폭 제한 (1400px) */
    .section-inner {
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
    }
    
    /* 섹션 서브 타이틀 (한글) */
    .sub-title {
        font-family: 'Freesentation', sans-serif;
        font-size: 1.1rem;
        color: #888;
        margin-top: 0.25rem; /* 타이틀과 가깝게 */
        margin-bottom: 2.5rem;
        padding-left:0.25rem;
        font-weight: 500;
        display: block;
    }

    /* [1. HERO SECTION] */
    .hero-section {
        position: relative;
        width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        color: white; 
    }

    .hero-video-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
    }
    .hero-video-bg video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 0 5vw;
    }

    .hero-title {
        font-family: 'URWDIN', sans-serif;
        font-size: 8.5vw;
        line-height: 1;
        font-weight: 700;
        text-transform: uppercase;
        color: #fff;
        margin-bottom: 3rem;
        white-space: nowrap;
    }

    .highlight-wrapper {
        position: relative;
        display: inline-block;
        z-index: 1;
    }
    .highlight-wrapper::after {
        content: '';
        position: absolute;
        bottom: 5%;
        left: -1%;
        width: 102%;
        height: 35%;
        background-color: #FAEB15;
        z-index: -1;
        opacity: 0.9;
    }

    .hero-desc-row {
        display: flex;
        justify-content: flex-end;
        padding-right: 2vw;
    }
    .hero-desc {
        font-family: 'Freesentation', sans-serif;
        font-size: 1.25rem;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.9);
        max-width: 600px;
        word-break: keep-all;
    }


    /* [2. CORE VALUES] - Hover Effect Updated */
    .core-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 2rem;
    }
    .core-item {
        background: #f9f9f9;
        padding: 4rem 2.5rem;
        border: 1px solid #eee;
        position: relative;
        transition: all 0.4s ease; /* 배경색 전환 포함 */
        overflow: hidden;
    }
    
    /* [Hover Interaction: Black BG + Colored Text] */
    .core-item:hover {
        transform: translateY(-10px);
        background: #111; /* 블랙 배경 */
        border-color: var(--glow-color); /* 테두리 컬러 */
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    
    /* 상단 컬러바 */
    .core-item::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 6px;
        background: var(--glow-color);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    .core-item:hover::before { transform: scaleX(1); }

    /* 텍스트 컬러 변화 */
    .core-head { 
        font-size: 1.1rem; color: #999; margin-bottom: 2rem; font-weight: 700; letter-spacing: 0.05em; 
        transition: color 0.3s;
    }
    .core-item:hover .core-head { color: var(--glow-color); } /* 헤드 컬러 변경 */

    .core-slogan { 
        font-size: 2.4rem; line-height: 1.1; font-weight: 700; color: #1a1a1a; margin-bottom: 1.5rem; min-height: 120px;
        transition: color 0.3s;
    }
    .core-item:hover .core-slogan { color: #fff; } /* 슬로건 화이트 */

    .core-desc { font-size: 1.2rem; color: #555; line-height: 1.6; transition: color 0.3s; }
    .core-item:hover .core-desc { color: #ccc; } /* 설명글 밝은 회색 */


    /* [3. COMPANY GUIDE] */
    .guide-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5rem;
        align-items: stretch;
    }
    .guide-img {
        width: 100%;
        height: 100%;
        min-height: 600px;
        border-radius: 4px;
        overflow: hidden;
    }
    .guide-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
    }
    .guide-img:hover img { transform: scale(1.05); }

    .info-list {
        border-top: 2px solid #1a1a1a;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .info-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        padding: 2rem 0;
        border-bottom: 1px solid #e5e5e5;
        align-items: flex-start;
    }
    .info-label h4 { font-size: 1.25rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.2rem; }
    .info-label span { font-size: 0.85rem; color: #999; text-transform: uppercase; font-weight: 600; }
    .info-content p { font-size: 1.15rem; color: #333; margin-bottom: 0.3rem; font-weight: 500; }
    .info-content span { font-size: 0.95rem; color: #777; display: block; }


    /* [4. CLIENT] */
    .client-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1px;
        background: #eee;
        border: 1px solid #eee;
        margin-top: 2rem;
    }
    .client-item {
        background: #fff;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        transition: background 0.3s;
    }
    .client-item:hover { background: #fafafa; }
    .client-item img {
        max-width: 80%;
        max-height: 60%;
        width: auto; 
        height: auto;
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.3s;
    }
    .client-item:hover img {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.05);
    }


    /* [5. LOCATION] */
    .loc-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 2rem;
    }
    .loc-map {
        width: 100%;
        height: 450px;
        background: #f0f0f0;
        margin-top: 1.5rem;
        border: 1px solid #ddd;
    }
    /* 구글맵 임베드 설정 */
    .loc-map iframe {
        width: 100%;
        height: 100%;
        border: 0;
        filter: grayscale(100%); /* 흑백 필터 */
    }

    /* Mobile */
    @media (max-width: 1024px) {
        .hero-title { font-size: 14vw; margin-bottom: 2rem; white-space: normal; line-height: 0.9; }
        .hero-desc-row { justify-content: flex-start; padding-right: 0; }
        .core-grid { grid-template-columns: 1fr; }
        .guide-grid { grid-template-columns: 1fr; gap: 3rem; }
        .guide-img { min-height: 300px; }
        .client-grid { grid-template-columns: repeat(2, 1fr); }
        .loc-container { grid-template-columns: 1fr; gap: 3rem; }
    }
</style>

<section class="hero-section">
    <div class="hero-video-bg">
        <video src="https://unrealsummit16.cafe24.com/2025/challange25/unrealchallange25_movie_hq.webm" autoplay loop muted playsinline></video>
        <div class="hero-overlay"></div>
    </div>

    <div class="hero-content">
        <div class="hero-title split-line">
            CRAZY ENOUGH<br>
            <span class="highlight-wrapper">BE CREATIVE.</span>
        </div>
        
        <div class="hero-desc-row fade-up">
            <p class="hero-desc">
                2016년, 시작한 그리프는 변화를 두려워하지 않는 창의적인 회사입니다.<br class="hidden md:block">
                미친 듯이 상상하고, 진심으로 도전하며, 늘 새로운 방법으로 세상을 바라봅니다. <br class="hidden md:block">
                그리프의 여정은 단순한 마케팅이 아닌, 대담한 이야기와 혁신을 만들어가는 과정입니다.
            </p>
        </div>
    </div>
</section>

<section class="about-section font-urw bg-white">
    <div class="section-inner">
        <h2 class="text-5xl font-bold mb-1 text-[#1a1a1a] fade-up">CORE VALUES</h2>
        <span class="sub-title fade-up">핵심가치</span>
        
        <div class="core-grid">
            <div class="core-item fade-up" style="--glow-color: #22d3ee;">
                <div class="core-head">MISSION</div>
                <div class="core-slogan">Be BOLD<br>STAY CREATIVE.</div>
                <div class="font-free core-desc">미친 상상력과 대담한 창의력으로</div>
            </div>
            <div class="core-item fade-up" style="--glow-color: #c084fc;">
                <div class="core-head">VISION</div>
                <div class="core-slogan">BREAK BOUNDARIES,<br>CREATE BOLDLY.</div>
                <div class="font-free core-desc">경계를 깨고, 대담하게<br>특별한 것을 창조합니다.</div>
            </div>
            <div class="core-item fade-up" style="--glow-color: #a3e635;">
                <div class="core-head">CORE</div>
                <div class="core-slogan">CREATE<br>AMAZING THINGS.</div>
                <div class="font-free core-desc">세상에 없던 경험을 만듭니다.</div>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="section-inner">
        <h2 class="font-urw text-5xl font-bold text-[#1a1a1a] fade-up mb-1">COMPANY GUIDE</h2>
        <span class="sub-title fade-up">회사 정보</span>
        
        <div class="guide-grid">
            <div class="guide-img fade-up">
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=1200&auto=format&fit=crop" alt="Griff Office">
            </div>

            <div class="info-list font-free fade-up">
                <div class="info-row">
                    <div class="info-label"><h4>회사명</h4><span>Company Name</span></div>
                    <div class="info-content"><p>주식회사 그리프</p><span>GRIFF Inc.</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><h4>소재지</h4><span>Location</span></div>
                    <div class="info-content">
                        <div class="mb-6">
                            <p class="text-neutral-500 text-sm mb-1">[본사 Head Office]</p>
                            <p>경기도 하남시 미사대로 540, A동 711호</p>
                            <span>A-711, 540, Misa-daero, Hanam-si, Gyeonggi-do</span>
                        </div>
                        <div>
                            <p class="text-neutral-500 text-sm mb-1">[스튜디오 Studio]</p>
                            <p>서울시 성동구 아차산로17길 49, 생각공장데시앙플렉스 1109호</p>
                            <span>#1109, 49 Achasan-ro 17-gil, Seongdong-gu, Seoul</span>
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><h4>전화번호</h4><span>Tel</span></div>
                    <div class="info-content"><p>02.326.3701</p><span>+82.2.326.3701</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><h4>설립일</h4><span>Establishment</span></div>
                    <div class="info-content"><p>2016.3.21</p><span>Mar 21, 2016</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="section-inner">
        <h2 class="font-urw text-5xl font-bold text-[#1a1a1a] fade-up">CLIENT</h2>
        <span class="sub-title fade-up">주요 거래처</span>
        
        <div class="client-grid fade-up">
            <?php for($i=1; $i<=12; $i++): 
                $num = sprintf("%02d", $i); // 01, 02... 12
            ?>
            <div class="client-item">
                <img src="/img/inc/griff_client_logo_<?=$num?>.svg" alt="Client <?=$i?>" onerror="this.style.display='none'">
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="about-section" style="border-bottom: none;">
    <div class="section-inner">
        <h2 class="font-urw text-5xl font-bold text-[#1a1a1a] fade-up">LOCATION</h2>
        <span class="sub-title fade-up">오시는 길</span>

        <div class="loc-container">
            <div class="loc-box fade-up">
                <h3 class="font-urw text-2xl font-bold text-[#1a1a1a]">HEAD OFFICE</h3>
                <p class="font-free text-gray-600">경기도 하남시 미사대로 540, A동 711호</p>
                <div class="loc-map">
                    <iframe src="https://maps.google.com/maps?q=경기도 하남시 미사대로 540&output=embed" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <div class="loc-box fade-up">
                <h3 class="font-urw text-2xl font-bold text-[#1a1a1a]">STUDIO</h3>
                <p class="font-free text-gray-600">서울시 성동구 아차산로17길 49, 생각공장데시앙플렉스 1109호</p>
                <div class="loc-map">
                    <iframe src="https://maps.google.com/maps?q=서울시 성동구 아차산로17길 49&output=embed" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/SplitText.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        gsap.registerPlugin(ScrollTrigger, SplitText);

        // 1. Hero Title Intro
        const heroSplit = new SplitText(".hero-title", { type: "chars, lines" });
        gsap.from(heroSplit.chars, {
            y: 100,
            opacity: 0,
            rotationX: -90,
            duration: 1.2,
            stagger: 0.02,
            ease: "back.out(1.7)",
            delay: 0.2
        });

        // 2. Common Fade Up
        const fadeUps = document.querySelectorAll(".fade-up");
        fadeUps.forEach((el) => {
            gsap.from(el, {
                y: 50,
                opacity: 0,
                duration: 1,
                ease: "power3.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                }
            });
        });
    });
</script>

<?php 
if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php";
?>