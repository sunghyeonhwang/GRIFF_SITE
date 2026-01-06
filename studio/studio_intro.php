<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [1] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

// [2] 장비 목록 조회 (DB)
$equip_sql = "SELECT name FROM studio_equipment ORDER BY id ASC";
$equip_result = $conn->query($equip_sql);

// [3] 갤러리 이미지 데이터 (20장)
$gallery_images = [
    'https://images.unsplash.com/photo-1598899134739-24c46f58b8c0?w=1200&q=80', 
    'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=1200&q=80',
    'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=1200&q=80', 
    'https://images.unsplash.com/photo-1533488765986-dfa2a9939acd?w=1200&q=80',
    'https://images.unsplash.com/photo-1527525443983-6e60c75fff46?w=1200&q=80', 
    'https://images.unsplash.com/photo-1536240478700-b869070f9279?w=1200&q=80',
    'https://images.unsplash.com/photo-1478720568477-152d9b164e63?w=1200&q=80', 
    'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=1200&q=80', 
    'https://images.unsplash.com/photo-1605810230434-7631ac76ec81?w=1200&q=80', 
    'https://images.unsplash.com/photo-1552168324-d612d77725e3?w=1200&q=80', 
    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80',
    'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=1200&q=80', 
    'https://images.unsplash.com/photo-1571221703623-6447c234a49c?w=1200&q=80',
    'https://images.unsplash.com/photo-1518176258769-e389c81319c4?w=1200&q=80', 
    'https://images.unsplash.com/photo-1516280440614-6697288d5d38?w=1200&q=80', 
    'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?w=1200&q=80', 
    'https://images.unsplash.com/photo-1559053547-ffb8b2e3e67e?w=1200&q=80',
    'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=1200&q=80',
    'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80',
    'https://images.unsplash.com/photo-1533174072545-e8d4aa97edf9?w=1200&q=80'
];
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }

    /* 초기 상태 숨김 (GSAP) */
    .fade-up-init { opacity: 0; transform: translateY(30px); }

    /* STUDIO 점 그라디언트 */
    .dot-gradient {
        background: linear-gradient(to right, #FFFB00, #FFD400);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;
    }

    /* 풀스크린 비디오 배경 스타일 */
    .hero-wrapper { position: relative; width: 100%; height: 100vh; min-height: 800px; overflow: hidden; display: flex; align-items: center; }
    .hero-video-bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -20; }
    .hero-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: -15; }

    /* 스크롤 다운 인디케이터 */
    .scroll-indicator { position: absolute; bottom: 3rem; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 0.5rem; z-index: 10; cursor: pointer; opacity: 0.7; transition: opacity 0.3s ease; }
    .scroll-indicator:hover { opacity: 1; }
    .scroll-text { font-family: 'URWDIN', sans-serif; font-size: 0.75rem; letter-spacing: 0.2em; color: #fff; font-weight: 700; }
    .scroll-arrow { width: 24px; height: 24px; color: #fff; animation: bounce 2s infinite; }
    @keyframes bounce { 0%, 20%, 50%, 80%, 100% {transform: translateY(0);} 40% {transform: translateY(-10px);} 60% {transform: translateY(-5px);} }

    /* 카드 스타일 */
    .feature-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 1.5rem; padding: 2.5rem; transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); height: 100%; display: flex; flex-direction: column; }
    .feature-card:hover { transform: translateY(-5px); border-color: #FFD400; } 
    .icon-box { width: 3.5rem; height: 3.5rem; background: #f3f4f6; border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; color: #1a1a1a; transition: all 0.3s; }
    .feature-card:hover .icon-box { background: #FFD400; color: #000; }

    /* 탭 스타일 */
    .pricing-tab-btn { padding: 0.8rem 2.5rem; border-radius: 100px; font-family: 'URWDIN', sans-serif; font-weight: 700; font-size: 1.1rem; color: #9ca3af; background: #f3f4f6; transition: all 0.3s ease; cursor: pointer; border: 2px solid transparent; }
    .pricing-tab-btn.active, .pricing-tab-btn:hover { background: #000; color: #FFD400; border-color: #000; box-shadow: none; }

    /* 가격표 카드 스타일 */
    .price-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 1.5rem; padding: 2.5rem 2rem; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
    .price-card:hover { border-color: #FFD400; box-shadow: none; transform: translateY(-5px); }
    .price-card.featured { border-color: #FFD400; border-width: 2px; }
    .price-name { font-family: 'URWDIN', sans-serif; font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; color: #1a1a1a; }
    .price-amount { font-family: 'URWDIN', sans-serif; font-size: 2.2rem; font-weight: 700; color: #1a1a1a; margin: 1rem 0 0.5rem; }
    .price-sub { font-size: 0.9rem; color: #9ca3af; font-weight: 400; margin-bottom: 1.5rem; }
    .price-features li { display: flex; align-items: start; gap: 0.5rem; text-align: left; font-size: 0.95rem; color: #4b5563; margin-bottom: 0.6rem; font-family: 'Freesentation', sans-serif; line-height: 1.4; }
    .price-features svg { flex-shrink: 0; margin-top: 0.2rem; color: #FFD400; }
    .price-features li strong { color: #000; font-weight: 700; }

    /* 예약 버튼 호버 효과 */
    .booking-btn { display: block; width: 100%; py-3; rounded-xl; font-family: 'URWDIN', sans-serif; font-weight: 700; font-size: 0.9rem; transition: all 0.3s ease; margin-top: auto; text-align: center; padding: 0.8rem 0; border: 1px solid #e5e7eb; }
    .booking-btn:hover { background-color: #FFD400; color: #000; border-color: #FFD400; }
    .booking-btn.featured-btn { background-color: #000; color: #fff; border-color: #000; }
    .booking-btn.featured-btn:hover { background-color: #FFD400; color: #000; border-color: #FFD400; }

    /* 탭 콘텐츠 전환용 */
    .tab-content { display: none; animation: fadeIn 0.5s ease; }
    .tab-content.active { display: grid; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* 옵션 버튼 스타일 */
    .option-btn { font-family: 'Freesentation', sans-serif; font-size: 0.95rem; color: #6b7280; border-bottom: 1px solid #6b7280; cursor: pointer; transition: all 0.3s; padding-bottom: 2px; }
    .option-btn:hover { color: #000; border-color: #000; }

    /* MASONRY GALLERY 스타일 */
    .masonry-wrapper { width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; padding: 0; background: #000; }
    .masonry-container { column-count: 1; column-gap: 0; width: 100%; }
    @media (min-width: 640px) { .masonry-container { column-count: 2; } }
    @media (min-width: 1024px) { .masonry-container { column-count: 4; } }

    .masonry-item { break-inside: avoid; margin: 0; padding: 0; width: 100%; display: block; overflow: hidden; cursor: pointer; position: relative; }
    .masonry-item img { width: 100%; height: auto; display: block; border-radius: 0 !important; filter: grayscale(100%); transition: all 0.5s ease; }
    .masonry-item:hover img { filter: grayscale(0%); transform: scale(1.05); }
    
    /* 줌 아이콘 오버레이 (호버시) */
    .masonry-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.3s;
        display: flex; align-items: center; justify-content: center; z-index: 20;
    }
    .masonry-item:hover .masonry-overlay { opacity: 1; }
    .masonry-overlay svg { color: #fff; width: 40px; height: 40px; drop-shadow: 0 2px 5px rgba(0,0,0,0.5); }

    /* ★ 모달 (Lightbox) 스타일 */
    .gallery-modal {
        position: fixed; inset: 0; z-index: 9999;
        background-color: rgba(0,0,0,0.95);
        display: none; opacity: 0; transition: opacity 0.3s ease;
        justify-content: center; align-items: center;
        flex-direction: column;
    }
    .gallery-modal.open { display: flex; opacity: 1; }
    
    .modal-img-wrapper { position: relative; max-width: 90vw; max-height: 85vh; }
    .modal-img { max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 4px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
    
    .modal-close { position: absolute; top: 30px; right: 40px; color: #fff; cursor: pointer; transition: transform 0.3s; z-index: 100; }
    .modal-close:hover { transform: rotate(90deg); color: #FFD400; }
    
    .modal-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 60px; height: 60px; border-radius: 50%;
        background: rgba(255,255,255,0.1); color: #fff;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .modal-nav:hover { background: #fff; color: #000; border-color: #fff; }
    .modal-prev { left: 40px; }
    .modal-next { right: 40px; }
    
    .modal-counter {
        margin-top: 20px; color: #888; font-family: 'URWDIN', sans-serif; font-size: 1.2rem; font-weight: 700; letter-spacing: 2px;
    }
    .modal-counter span { color: #fff; }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="hero-wrapper mb-24">
    <video class="hero-video-bg" autoplay muted loop playsinline>
        <source src="https://unrealsummit16.cafe24.com/2025/challange25/unrealengine_a_hq.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>

    <div class="w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 fade-up-init">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            <div class="flex justify-start items-center">
                <img src="/img/inc/griff_studio_logo.svg" alt="Griff Studio Logo" class="w-full max-w-[400px] md:max-w-[500px] h-auto opacity-90">
            </div>
            <div class="text-white text-left">
                <h2 class="font-kor text-3xl md:text-4xl font-bold leading-snug mb-8 text-neutral-100">
                    성수동에 위치한 그리프 스튜디오는<br>온라인 콘텐츠 제작에 최적화된<br>전용 공간입니다.
                </h2>
                <div class="w-12 h-1 bg-[#FFD400] mb-8"></div>
                <p class="font-kor text-lg md:text-xl font-medium leading-relaxed mb-6 text-neutral-200">
                    전문 촬영 장비와 안정적인 송출 시스템을 갖추고 있어<br>실시간 라이브 중계는 물론, 고품질 영상 제작까지 가능합니다.
                </p>
                <p class="font-kor text-base md:text-lg font-light text-neutral-400">
                    기획부터 촬영, 송출까지 원스톱으로 지원하는 그리프 스튜디오에서<br>더 효율적이고 완성도 높은 콘텐츠 제작을 경험해 보세요.
                </p>
            </div>
        </div>
    </div>

    <div class="scroll-indicator" onclick="scrollToContent()">
        <span class="scroll-text">SCROLL DOWN</span>
        <svg class="scroll-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
    </div>
</div>

<div id="main-content" class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pb-24">

    <div class="mb-24 fade-up-init text-left">
        <h1 class="font-eng text-[60px] md:text-[100px] font-bold leading-none text-black">
            STUDIO<span class="dot-gradient">.</span>
        </h1>
    </div>

    <div class="mb-32">
        <div class="flex flex-col items-start mb-12 fade-up-init">
            <h3 class="font-eng text-3xl font-bold leading-none mb-1">FEATURES</h3>
            <span class="font-kor text-lg text-neutral-400 font-bold">주요 특징</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 fade-up-init">
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">4K 녹화 시스템</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">4K Recording / Stream</span>
                <p class="font-kor text-neutral-600 leading-relaxed text-sm">4K 고화질 촬영과 안정적인 라이브 송출을 동시에 운영할 수 있습니다. 끊김 없는 송출, 선명한 영상 품질, 안정적인 녹화 운영을 지원합니다.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">콘솔룸</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">Console Room</span>
                <p class="font-kor text-neutral-600 leading-relaxed text-sm">별도의 콘솔룸에서 엔지니어뿐 아니라 추가 인원이 함께 모니터링하며 운영 상황을 공유할 수 있어, 현장 대응과 안정성이 높습니다.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">모니터링</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">Monitoring</span>
                <p class="font-kor text-neutral-600 leading-relaxed text-sm">전용 모니터링 공간에서 촬영 영상을 실시간으로 체크하여 퀄리티를 즉시 보완하고 재촬영 리스크를 줄일 수 있습니다.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">스트리밍 운영</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">Streaming Operation</span>
                <p class="font-kor text-neutral-600 leading-relaxed text-sm">방송국 출신 PD와 엔지니어가 전 과정을 책임 운영합니다. 현장 변수에도 빠르게 대응하며 사고 없는 라이브를 지원합니다.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">편의 시설</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">Amenities</span>
                <p class="font-kor text-neutral-600 leading-relaxed text-sm">스튜디오 이용 중 편하게 머무르실 수 있도록 스페셜티 커피와 최고급 커피머신, 드립 장비를 준비해 두었습니다.</p>
            </div>
            <div class="feature-card">
                <div class="icon-box"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg></div>
                <h4 class="font-kor font-bold text-xl mb-1">주차</h4>
                <span class="font-eng text-xs text-neutral-400 font-bold mb-4 block uppercase">Parking</span>
                <div class="font-kor text-neutral-600 text-sm leading-relaxed">
                    성수동에서 보기 드문 넉넉한 주차 공간.<br>
                    <span class="block mt-2 font-medium text-black">• 차량 1대: 무료 / 2대 이상: 유료 주차 (패키지 선택시 무료)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-32 fade-up-init">
        <div class="text-left mb-12">
            <h3 class="font-eng text-3xl font-bold leading-none mb-1">PRICING PLAN</h3>
            <span class="font-kor text-lg text-neutral-400 font-bold">이용 요금</span>
        </div>
        
        <div class="flex flex-col items-center gap-6 mb-12">
            <div class="flex gap-3 bg-white p-1 rounded-full border border-neutral-200">
                <button onclick="switchTab('1d')" id="btn-1d" class="pricing-tab-btn active">1 DAY (8H)</button>
                <button onclick="switchTab('4h')" id="btn-4h" class="pricing-tab-btn">4 HOURS</button>
            </div>
            
            <button onclick="showOptionModal()" class="option-btn flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                옵션 단가표 보기
            </button>
        </div>
        
        <div id="tab-1d" class="tab-content active grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-100 rounded-full text-xs font-bold text-neutral-500 mb-4 font-eng">RENTAL ONLY</span>
                    <h4 class="price-name">START</h4>
                    <div class="price-amount">₩420,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관 Only (8시간)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 조명 장비 포함</li>
                    </ul>
                    <p class="text-xs text-neutral-400 mt-4">* 장시간 촬영 할인 적용</p>
                </div>
                <a href="/studio/studio_booking.php?package=1D_START" class="booking-btn">BOOKING</a>
            </div>
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-100 rounded-full text-xs font-bold text-neutral-500 mb-4 font-eng">1 CAM</span>
                    <h4 class="price-name">BASIC</h4>
                    <div class="price-amount">₩900,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>카메라 1대 (오퍼레이터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 오디오 + 시스템 운영</li>
                    </ul>
                    <p class="text-xs text-neutral-400 mt-4">* 장시간 촬영 할인 적용</p>
                </div>
                <a href="/studio/studio_booking.php?package=1D_BASIC" class="booking-btn">BOOKING</a>
            </div>
            <div class="price-card featured">
                <div>
                    <span class="inline-block px-3 py-1 bg-[#FFD400] rounded-full text-xs font-bold text-black mb-4 font-eng">BEST CHOICE</span>
                    <h4 class="price-name">MULTI</h4>
                    <div class="price-amount">₩1,300,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>카메라 2대 (오퍼레이터 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 오디오 + 시스템 운영</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>화면 전환 및 연출 제공</strong></li>
                    </ul>
                    <p class="text-xs text-neutral-400 mt-4">* 장시간 촬영 할인 적용</p>
                </div>
                <a href="/studio/studio_booking.php?package=1D_MULTI" class="booking-btn featured-btn">BOOKING</a>
            </div>
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-900 text-white rounded-full text-xs font-bold mb-4 font-eng">3 CAM + PRO</span>
                    <h4 class="price-name">PRO</h4>
                    <div class="price-amount">₩1,800,000</div>
                   <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>카메라 3대 (오퍼레이터 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>오디오 콘솔 (엔지니어 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>화면 전환 및 연출 제공</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>현장 PD 포함</strong></li>
                    </ul>
                    <!-- <p class="text-xs text-neutral-400 mt-4">* 장시간 촬영 할인 적용</p> -->
                </div>
                <a href="/studio/studio_booking.php?package=1D_PRO" class="booking-btn">BOOKING</a>
            </div>
        </div>

        <div id="tab-4h" class="tab-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-100 rounded-full text-xs font-bold text-neutral-500 mb-4 font-eng">RENTAL ONLY</span>
                    <h4 class="price-name">START</h4>
                    <div class="price-amount">₩240,000</div>
                   <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관 Only Only (4시간)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 조명 장비 포함</li>
                    </ul>
                </div>
                <a href="/studio/studio_booking.php?package=4H_START" class="booking-btn">BOOKING</a>
            </div>
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-100 rounded-full text-xs font-bold text-neutral-500 mb-4 font-eng">1 CAM</span>
                    <h4 class="price-name">BASIC</h4>
                    <div class="price-amount">₩700,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>카메라 1대 (오퍼레이터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 오디오 + 시스템 운영</li>
                    </ul>
                </div>
                <a href="/studio/studio_booking.php?package=4H_BASIC" class="booking-btn">BOOKING</a>
            </div>
            <div class="price-card featured">
                <div>
                    <span class="inline-block px-3 py-1 bg-[#FFD400] rounded-full text-xs font-bold text-black mb-4 font-eng">BEST CHOICE</span>
                    <h4 class="price-name">MULTI</h4>
                    <div class="price-amount">₩1,000,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>카메라 2대 (오퍼레이터 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>기본 오디오 + 시스템 운영</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>화면 전환 및 연출 제공</strong></li>
                    </ul>
                </div>
                <a href="/studio/studio_booking.php?package=4H_MULTI" class="booking-btn featured-btn">BOOKING</a>
            </div>
            <div class="price-card">
                <div>
                    <span class="inline-block px-3 py-1 bg-neutral-900 text-white rounded-full text-xs font-bold mb-4 font-eng">3 CAM + PRO</span>
                    <h4 class="price-name">PRO</h4>
                    <div class="price-amount">₩1,400,000</div>
                    <p class="price-sub">&nbsp;</p>
                    <ul class="price-features">
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>공간 대관</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>라이브 시스템 (테크니컬 디렉터 포함)</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>카메라 3대 (오퍼레이터 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>오디오 콘솔 (엔지니어 포함)</strong></li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>화면 전환 및 연출 제공</li>
                        <li><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><strong>현장 PD 포함</strong></li>
                    </ul>
                </div>
                <a href="/studio/studio_booking.php?package=4H_PRO" class="booking-btn">BOOKING</a>
            </div>
        </div>

        <div class="mt-8 text-right font-kor text-neutral-400 text-sm leading-relaxed">
            <p>· 상기 금액은 VAT 별도입니다.</p>
            <p>· 모든 패키지에는 라이브 세팅, 리허설, 녹화본 제공이 포함됩니다.</p>
            <p>· 멀티 플랫폼 송출, 자막, 하이라이트 편집은 별도 문의 바랍니다.</p>
        </div>
    </div>

    <div class="mb-32 fade-up-init">
        <div class="flex flex-col items-start mb-12">
            <h3 class="font-eng text-3xl font-bold leading-none mb-1">EQUIPMENT LIST</h3>
            <span class="font-kor text-lg text-neutral-400 font-bold">보유 장비</span>
        </div>

        <div class="rounded-[2rem] overflow-hidden bg-black">
            <div class="relative h-[300px] md:h-[400px]">
                <img src="https://images.unsplash.com/photo-1587583770025-32851bad462e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80" 
                     alt="Cinema Equipment" 
                     class="absolute top-0 left-0 w-full h-full object-cover opacity-80 hover:scale-105 transition-transform duration-700">
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-transparent via-black/20 to-black"></div>
            </div>

            <div class="p-10 md:p-16 text-white">
                <p class="font-kor text-neutral-400 mb-12 leading-relaxed text-lg">
                    최고의 퀄리티를 위해 <span class="text-white font-bold">시네마 라인 카메라</span>와 <span class="text-white font-bold">전문 조명 시스템</span>을 운용하고 있습니다.
                </p>
                
                <?php if ($equip_result && $equip_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6 font-eng text-lg">
                    <?php while($equip = $equip_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-4 group border-b border-neutral-800 pb-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FFD400] group-hover:scale-150 transition-transform"></span>
                        <span class="group-hover:text-[#FFD400] transition-colors"><?= htmlspecialchars($equip['name']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                    <p class="text-neutral-500 font-kor">등록된 장비 정보가 없습니다.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<div class="mb-0 fade-up-init w-full"> 
    <div class="flex flex-col items-start mb-12 px-6 md:px-12 max-w-[1400px] mx-auto">
        <h3 class="font-eng text-3xl font-bold leading-none mb-1">GALLERY</h3>
        <span class="font-kor text-lg text-neutral-400 font-bold">스튜디오 전경</span>
    </div>

    <div class="masonry-wrapper">
        <div class="masonry-container">
            <?php foreach($gallery_images as $index => $img): ?>
                <div class="masonry-item" onclick="openModal(<?= $index ?>)">
                    <img src="<?= $img ?>" alt="Studio Gallery">
                    <div class="masonry-overlay">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="galleryModal" class="gallery-modal">
    <div class="modal-close" onclick="closeModal()">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </div>
    
    <div class="modal-nav modal-prev" onclick="changeImage(-1)">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </div>
    
    <div class="modal-img-wrapper">
        <img id="modalImage" class="modal-img" src="" alt="Gallery Full">
    </div>
    
    <div class="modal-nav modal-next" onclick="changeImage(1)">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </div>

    <div class="modal-counter" id="modalCounter">
        <span id="currentIndex">1</span> / <span id="totalIndex">20</span>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        gsap.to(".fade-up-init", {
            y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power2.out", delay: 0.1
        });
    });

    // 탭 전환
    function switchTab(tabId) {
        document.querySelectorAll('.pricing-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById('btn-' + tabId).classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    // 옵션 모달
    function showOptionModal() {
        Swal.fire({
            title: '<strong>엔지니어 및 추가 옵션</strong>',
            html: `
                <div class="text-left font-kor text-sm">
                    <table class="w-full text-left border-collapse mb-4">
                        <thead>
                            <tr class="border-b border-neutral-300">
                                <th class="py-2 text-black">항목</th>
                                <th class="py-2 text-black text-right">4시간 (Half)</th>
                                <th class="py-2 text-black text-right">1일 (Full)</th>
                            </tr>
                        </thead>
                        <tbody class="text-neutral-600">
                            <tr class="border-b border-neutral-100"><td class="py-2">테크니컬 디렉터(TD)</td><td class="py-2 text-right">250,000</td><td class="py-2 text-right">400,000</td></tr>
                            <tr class="border-b border-neutral-100"><td class="py-2">카메라 오퍼레이터</td><td class="py-2 text-right">200,000</td><td class="py-2 text-right">320,000</td></tr>
                            <tr class="border-b border-neutral-100"><td class="py-2">오디오 엔지니어</td><td class="py-2 text-right">150,000</td><td class="py-2 text-right">240,000</td></tr>
                            <tr><td class="py-2">현장 PD</td><td class="py-2 text-right">250,000</td><td class="py-2 text-right">400,000</td></tr>
                        </tbody>
                    </table>
                    <div class="bg-neutral-100 p-3 rounded-lg text-xs"><strong>시간 초과 (Overtime):</strong><br>평일 5~6만원 / 주말 6~7만원 (시간당)</div>
                </div>
            `,
            showCloseButton: true, confirmButtonText: '닫기', confirmButtonColor: '#1a1a1a', width: '600px'
        });
    }

    // 스크롤 다운
    function scrollToContent() {
        const mainContent = document.getElementById('main-content');
        if(mainContent) { window.scrollTo({ top: mainContent.offsetTop - 100, behavior: 'smooth' }); }
    }

    // ★ [MODAL SCRIPT]
    const galleryImages = <?= json_encode($gallery_images) ?>;
    let currentImgIndex = 0;
    const modal = document.getElementById('galleryModal');
    const modalImg = document.getElementById('modalImage');
    const currentIndexEl = document.getElementById('currentIndex');
    const totalIndexEl = document.getElementById('totalIndex');

    function openModal(index) {
        currentImgIndex = index;
        updateModalImage();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden'; // 스크롤 막기
        
        // 키보드 이벤트 등록
        document.addEventListener('keydown', handleKeydown);
    }

    function closeModal() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleKeydown);
    }

    function changeImage(direction) {
        currentImgIndex += direction;
        // 순환 구조
        if (currentImgIndex < 0) currentImgIndex = galleryImages.length - 1;
        if (currentImgIndex >= galleryImages.length) currentImgIndex = 0;
        updateModalImage();
    }

    function updateModalImage() {
        modalImg.src = galleryImages[currentImgIndex];
        currentIndexEl.innerText = currentImgIndex + 1;
        totalIndexEl.innerText = galleryImages.length;
    }

    function handleKeydown(e) {
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }

    // 모달 배경 클릭 시 닫기
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>