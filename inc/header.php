<?php
// DB 연결 및 설정 로드
include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/front_db_connect.php';

$config = [];
if (isset($pdo)) {
    $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY id DESC LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($conn)) {
    $res = $conn->query("SELECT * FROM site_settings ORDER BY id DESC LIMIT 1");
    $config = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : [];
}

// SEO 및 기본 설정
$site_title = !empty($config['seo_title']) ? $config['seo_title'] : 'GRIFF';
$meta_desc  = !empty($config['seo_description']) ? $config['seo_description'] : 'Creative Studio';
$keywords   = !empty($config['seo_keywords']) ? $config['seo_keywords'] : 'design, studio, creative';
$og_title = !empty($config['og_title']) ? $config['og_title'] : $site_title;
$og_desc  = !empty($config['og_desc']) ? $config['og_desc'] : $meta_desc;
$og_image = !empty($config['og_image']) ? $config['og_image'] : '';

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];
$og_image_url = $og_image ? $domain . $og_image : ''; 
$current_url = $domain . $_SERVER['REQUEST_URI'];

// 메뉴 데이터 정의 (이름, 링크, 시작컬러, 끝컬러)
$menus = [
    ['ABOUT', '/about.php', 'blue-600', 'violet-600'],
    ['PROJECT', '/project_list.php', 'fuchsia-600', 'pink-600'],
    ['SERVICE', '/service.php', 'orange-500', 'rose-600'],
    ['RECRUIT', '/recruit/recruit_list.php', 'emerald-500', 'teal-600'],
    ['CONTACT', '/contact/contact.php', 'indigo-600', 'cyan-500']
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_desc); ?>">
    <?php if($og_image_url): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image_url); ?>">
    <?php endif; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Meow+Script&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/studio-freight/lenis@1.0.29/bundled/lenis.min.js"></script>
    
    <link rel="stylesheet" href="/assets/font.css">
    <link rel="stylesheet" href="/assets/css/griff.css">

    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    eng: ['"URWDIN"', 'sans-serif'], 
                    kor: ['"Freesentation"', 'sans-serif'],
                    script: ['"Meow Script"', 'cursive'], 
                },
                colors: { neutral: { 200: '#e5e5e5', 900: '#1a1a1a' } }
            }
        },
        safelist: [
            'from-blue-600', 'to-violet-600',
            'from-fuchsia-600', 'to-pink-600',
            'from-orange-500', 'to-rose-600',
            'from-emerald-500', 'to-teal-600',
            'from-indigo-600', 'to-cyan-500',
            'from-[#FACC15]', 'to-[#F7E731]'
        ]
    }
    </script>
</head>
<body class="min-h-[200vh] font-eng bg-[#F9F9F9] text-[#1a1a1a]">

    <div id="mobile-overlay" class="fixed inset-0 bg-white z-[60] flex flex-col justify-center items-center opacity-0 invisible md:hidden">
        
        <button id="mobile-close-btn" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center z-50">
            <svg class="w-8 h-8 stroke-neutral-900 stroke-[2px]" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="flex flex-col items-center justify-center w-full h-full pb-10">
            
            <div class="mb-6 mobile-nav-item">
                <img src="/img/inc/logo_no_sub.svg" alt="GRIFF" class="h-10 w-auto block select-none">
            </div>

            <div class="w-8 h-[2px] bg-neutral-200 mb-8 mobile-nav-item"></div>

            <div class="flex flex-col gap-5 text-center">
                <?php foreach($menus as $m): ?>
                <a href="<?=$m[1]?>" class="mobile-nav-item group relative inline-block">
                    <span class="block font-eng text-[40px] font-bold text-neutral-900 leading-none tracking-tight group-hover:opacity-0 transition-opacity duration-300">
                        <?=$m[0]?>
                    </span>
                    <span class="absolute inset-0 font-eng text-[40px] font-bold leading-none tracking-tight bg-gradient-to-r from-<?=$m[2]?> to-<?=$m[3]?> bg-clip-text text-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        <?=$m[0]?>
                    </span>
                </a>
                <?php endforeach; ?>
                
                <a href="/studio/studio_intro.php" class="mobile-nav-item group relative inline-block">
                    <span class="block font-eng text-[40px] font-bold text-neutral-900 leading-none tracking-tight group-hover:opacity-0 transition-opacity duration-300">
                        STUDIO
                    </span>
                    <span class="absolute inset-0 font-eng text-[40px] font-bold leading-none tracking-tight bg-gradient-to-r from-[#FACC15] to-[#F7E731] bg-clip-text text-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                        STUDIO
                    </span>
                </a>
            </div>

            <div class="mt-12 flex flex-col gap-3 items-center">
                 <a href="/studio/studio_booking.php?mode=book" class="mobile-nav-item font-kor text-[17px] font-medium text-neutral-400 hover:text-neutral-900 transition-colors">예약하기</a>
                 <a href="/studio/studio_check.php" class="mobile-nav-item font-kor text-[17px] font-medium text-neutral-400 hover:text-neutral-900 transition-colors">예약확인 / 취소</a>
            </div>
        </div>
    </div>


    <nav id="navbar" class="fixed top-6 left-1/2 -translate-x-1/2 bg-white border border-neutral-200 overflow-hidden z-50 origin-top shadow-sm w-[340px] h-[64px]"
         style="border-radius: 9999px;">
        
        <div class="relative w-full h-[64px] flex items-center justify-between px-6 z-20 bg-white">
            <div id="logoIcon" class="group flex items-center justify-center shrink-0 cursor-pointer hover:scale-105 transition-transform">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none">
                    <path class="fill-[#C3C3C3] group-hover:fill-[#F7E731] transition-colors duration-300" 
                          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                    <circle cx="12" cy="12" r="3" fill="white"/>
                    <path d="M12 5v2M12 17v2M5 12h2M17 12h2" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            
            <div class="center-absolute">
                <a href="/">
                    <img src="/img/inc/logo_no_sub.svg" alt="GRIFF" class="h-6 w-auto block select-none">
                </a>
            </div>

            <div class="relative w-8 h-8 flex items-center justify-center shrink-0 cursor-pointer" id="toggleBtn">
                <div id="hamburger" class="absolute inset-0 flex flex-col items-end justify-center gap-[6px]">
                    <span class="w-6 h-[2px] bg-neutral-900 rounded-full"></span>
                    <span class="w-6 h-[2px] bg-neutral-900 rounded-full"></span>
                </div>
                <div id="closeIcon" class="absolute inset-0 flex items-center justify-center opacity-0 scale-50 hidden md:flex">
                    <svg class="w-7 h-7 stroke-neutral-900 stroke-[2px]" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <div id="desktop-content" class="absolute top-[80px] left-0 w-full px-8 pb-8 opacity-0 invisible hidden md:flex flex-col h-[calc(100%-80px)]">
            <div class="grid grid-cols-12 gap-6 w-full h-full">
                
                <div class="col-span-4 flex flex-col justify-start pt-2 border-r border-gray-100 pr-6">
                    <span class="font-eng text-[11px] font-bold text-neutral-400 tracking-widest mb-5">MENU</span>
                    <div class="flex flex-col gap-3.5">
                        <?php foreach($menus as $m): ?>
                        <a href="<?=$m[1]?>" class="relative block w-max group nav-item-wrapper">
                            <span class="font-eng nav-text-layer absolute inset-0 bg-gradient-to-r from-<?=$m[2]?> to-<?=$m[3]?> bg-clip-text text-transparent opacity-0 group-hover:opacity-100 font-bold text-[32px] leading-tight select-none tracking-tight transition-opacity duration-300"><?=$m[0]?></span>
                            <span class="font-eng nav-text-layer text-neutral-900 group-hover:opacity-0 font-bold text-[32px] leading-tight select-none block tracking-tight transition-opacity duration-300"><?=$m[0]?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-span-4 flex flex-col justify-start pt-2 pl-2">
                    <span class="font-eng text-[11px] font-bold text-neutral-400 tracking-widest mb-5">EXPLORE</span>
                    <div class="flex flex-col gap-4">
                        <a href="/studio/studio_intro.php" class="group flex items-start gap-1 relative w-max mb-1">
                            <div class="relative">
                                <span class="font-eng nav-text-layer absolute inset-0 bg-gradient-to-r from-[#FACC15] to-[#F7E731] bg-clip-text text-transparent opacity-0 group-hover:opacity-100 font-bold text-[32px] leading-none tracking-tight">STUDIO</span>
                                <span class="font-eng nav-text-layer text-neutral-900 group-hover:opacity-0 font-bold text-[32px] leading-none tracking-tight block">STUDIO</span>
                            </div>
                            <div class="w-1.5 h-1.5 bg-[#F7E731] rounded-full mt-2 ml-1"></div>
                        </a>
                        <a href="/studio/studio_intro.php" class="group block"><span class="font-kor text-[18px] font-medium text-neutral-900 group-hover:text-neutral-500 transition-colors">그리프 스튜디오</span></a>
                        <a href="/studio/studio_booking.php?mode=book" class="group block"><span class="font-kor text-[18px] font-medium text-neutral-900 group-hover:text-neutral-500 transition-colors">예약하기</span></a>
                        <a href="/studio/studio_check.php" class="group block"><span class="font-kor text-[18px] font-medium text-neutral-900 group-hover:text-neutral-500 transition-colors">예약 확인/취소</span></a>
                    </div>
                    
                    <div class="flex items-center gap-3 mt-4">
                        <?php if(!empty($config['youtube_url'])): ?>
                        <a href="<?=$config['youtube_url']?>" target="_blank" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-neutral-900 hover:border-neutral-900 group/icon transition-all duration-300">
                            <svg class="w-4 h-4 fill-neutral-900 group-hover/icon:fill-white transition-colors" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if(!empty($config['instagram_url'])): ?>
                        <a href="<?=$config['instagram_url']?>" target="_blank" class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center hover:bg-neutral-900 hover:border-neutral-900 group/icon transition-all duration-300">
                            <svg class="w-4 h-4 fill-neutral-900 group-hover/icon:fill-white transition-colors" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-span-4 h-full flex items-start justify-end pt-0">
                    <div class="relative w-full aspect-[9/10] rounded-xl overflow-hidden bg-black group shadow-sm">
                        <video src="<?= !empty($config['video_url']) ? $config['video_url'] : 'https://unrealsummit16.cafe24.com/2025/ufest25_12113.mp4' ?>" 
                               autoplay loop muted playsinline 
                               class="w-full h-full object-cover opacity-90 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700">
                        </video>
                        <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                            <div class="font-eng text-white text-[11px] font-semibold bg-black/30 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/10 tracking-wide">Brand Film</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-4 flex items-end justify-between text-xs text-neutral-500 font-medium border-t border-gray-100">
                <span class="font-eng tracking-wide"><?= htmlspecialchars($config['footer_copyright'] ?? '© GRIFF Inc.') ?></span>
                <div class="flex gap-6">
                    <span class="font-kr tracking-wide">회사 소개서</span>
                    <a href="#" class="flex items-center gap-1.5 text-neutral-400 hover:text-neutral-600">
                        <span class="font-eng tracking-wide">Download</span> 
                        <div class="w-1.5 h-1.5 bg-[#F7E731] rounded-full"></div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navbar = document.getElementById('navbar');
            const toggleBtn = document.getElementById('toggleBtn');
            const logoIcon = document.getElementById('logoIcon');
            const hamburger = document.getElementById('hamburger');
            const closeIcon = document.getElementById('closeIcon');
            const desktopContent = document.getElementById('desktop-content');
            
            // 모바일 전용 요소
            const mobileOverlay = document.getElementById('mobile-overlay');
            const mobileCloseBtn = document.getElementById('mobile-close-btn');
            const mobileItems = document.querySelectorAll('.mobile-nav-item');
            
            let isExpanded = false;
            let isAnimating = false;
            const EASE = "expo.inOut"; 
            const DURATION = 1.0; 

            // ============================
            // 1. PC 애니메이션 타임라인
            // ============================
            let pcTl = gsap.timeline({ paused: true });
            
            // PC: Navbar 확장
            pcTl.to(navbar, { width: 920, height: 460, borderRadius: 32, duration: DURATION, ease: EASE }, 0);
            
            // PC: 햄버거 -> X 아이콘
            pcTl.to(hamburger, { opacity: 0, scale: 0.5, rotation: 90, duration: 0.4, ease: "power2.in" }, 0)
                .to(closeIcon, { opacity: 1, scale: 1, rotation: 0, duration: 0.4, ease: "back.out(1.7)" }, 0.2);
            
            // PC: 내부 컨텐츠 등장
            pcTl.to(desktopContent, { autoAlpha: 1, duration: 0.6, ease: "power2.out" }, 0.4);
            pcTl.from(".nav-item-wrapper", { y: 15, opacity: 0, stagger: 0.05, duration: 0.5, ease: "power2.out" }, 0.45);
            pcTl.from("#desktop-content .col-span-4:nth-child(2) > div > *", { y: 15, opacity: 0, stagger: 0.05, duration: 0.5, ease: "power2.out" }, 0.55);
            pcTl.from(".aspect-\\[9\\/10\\]", { scale: 0.95, opacity: 0, duration: 0.7, ease: "power2.out" }, 0.6);

            // ============================
            // 2. 모바일 애니메이션 타임라인
            // ============================
            let mobileTl = gsap.timeline({ paused: true });
            
            // 모바일: 전체화면 오버레이 등장
            mobileTl.to(mobileOverlay, { autoAlpha: 1, duration: 0.4, ease: "power2.out" });
            // 모바일: 각 항목 순차 등장
            mobileTl.from(mobileItems, { y: 20, opacity: 0, stagger: 0.08, duration: 0.5, ease: "power2.out" }, 0.1);


            // 토글 함수
            function toggleMenu() {
                if (isAnimating) return;
                
                const isMobile = window.innerWidth < 768;

                if (!isExpanded) {
                    // 메뉴 열기
                    isAnimating = true;
                    isExpanded = true;
                    
                    if (isMobile) {
                        mobileTl.timeScale(1).play().then(() => isAnimating = false);
                    } else {
                        pcTl.timeScale(1).play().then(() => isAnimating = false);
                    }

                } else {
                    // 메뉴 닫기
                    closeMenu();
                }
            }

            function closeMenu() {
                if (isExpanded && !isAnimating) {
                    isAnimating = true;
                    isExpanded = false;
                    
                    const isMobile = window.innerWidth < 768;

                    if (isMobile) {
                        mobileTl.timeScale(1.5).reverse().then(() => isAnimating = false);
                    } else {
                        pcTl.timeScale(1.4).reverse().then(() => isAnimating = false);
                    }
                }
            }

            // 이벤트 리스너
            toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleMenu(); });
            mobileCloseBtn.addEventListener('click', (e) => { e.stopPropagation(); closeMenu(); });
            
            logoIcon.addEventListener('click', (e) => { e.stopPropagation(); if (isExpanded) closeMenu(); });
            document.addEventListener('click', (e) => { 
                // PC에서 메뉴 밖 클릭시 닫기
                if (isExpanded && window.innerWidth >= 768 && !navbar.contains(e.target)) {
                    closeMenu();
                }
            });
        });
    </script>
    
    <main id="main-content" class="relative z-10">