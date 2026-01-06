<?php
// [설정 데이터 로드]
// header.php에서 $config가 이미 로드되었을 수 있지만, 
// 독립적으로 footer만 불릴 경우를 대비해 체크합니다.
if (!isset($config)) {
    // DB 연결이 안 된 상태라면 연결 시도
    if (!isset($conn) && !isset($pdo)) {
        include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/front_db_connect.php';
    }

    $config = [];
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM site_settings LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (isset($conn)) {
        $res = $conn->query("SELECT * FROM site_settings LIMIT 1");
        $config = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : [];
    }
}

// [기본값 설정] DB값이 없으면 기본 텍스트 사용
$copyright   = !empty($config['footer_copyright']) ? $config['footer_copyright'] : "© 2026 GRIFF Inc. All rights reserved.";
$email       = !empty($config['contact_email']) ? $config['contact_email'] : "";
$insta_url   = !empty($config['instagram_url']) ? $config['instagram_url'] : "#";
$youtube_url = !empty($config['youtube_url']) ? $config['youtube_url'] : "#";
?>

</main> <footer class="bg-neutral-900 text-white pt-20 pb-12 px-6 md:px-12 border-t border-neutral-800">
    <div class="max-w-[1400px] mx-auto mb-20">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-start">
            
            <div class="lg:col-span-3">
                <img src="/img/inc/logo.svg" alt="GRIFF" class="h-12 brightness-0 invert"> 
            </div>
            
            <div class="lg:col-span-6 flex flex-col md:flex-row gap-16 md:gap-32">
                
                <div>
                    <h4 class="font-eng text-xs font-bold text-neutral-500 mb-6 tracking-widest hidden md:block">SITEMAP</h4>
                    <ul class="font-eng space-y-4 text-xl font-bold tracking-wide">
                        <li><a href="/about.php" class="hover:text-[#F7E731] transition-colors">ABOUT</a></li>
                        <li><a href="/project_list.php" class="hover:text-[#F7E731] transition-colors">PROJECT</a></li>
                        <li><a href="/service.php" class="hover:text-[#F7E731] transition-colors">SERVICE</a></li>
                        <li><a href="/recruit/recruit_list.php" class="hover:text-[#F7E731] transition-colors">RECRUIT</a></li>
                        <li><a href="/contact/contact.php" class="hover:text-[#F7E731] transition-colors">CONTACT</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-eng text-xs font-bold text-neutral-500 mb-6 tracking-widest hidden md:block">STUDIO</h4>
                    <h5 class="font-eng text-xl font-bold mb-4">STUDIO<span class="text-[#FACC15]">.</span></h5>
                    <ul class="font-kor space-y-3 text-lg text-neutral-400 font-medium">
                        <li><a href="/studio/studio_intro.php" class="hover:text-white transition-colors">그리프 스튜디오</a></li>
                        <li><a href="/studio/studio_booking.php" class="hover:text-white transition-colors">예약하기</a></li>
                        <li><a href="/studio/studio_check.php" class="hover:text-white transition-colors">예약 확인/취소</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="lg:col-span-3 flex flex-col items-start">
                <h4 class="font-eng text-xs font-bold text-neutral-500 mb-6 tracking-widest hidden md:block">SOCIAL</h4>
                <div class="flex gap-5">
                    <a href="<?php echo htmlspecialchars($insta_url); ?>" target="_blank" class="transition-opacity hover:opacity-70">
                        <img src="/img/inc/insta.svg" alt="Instagram" class="w-7 h-7">
                    </a>
                    
                    <a href="<?php echo htmlspecialchars($youtube_url); ?>" target="_blank" class="transition-opacity hover:opacity-70">
                        <img src="/img/inc/utube.svg" alt="YouTube" class="w-7 h-7">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1400px] mx-auto border-t border-neutral-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-neutral-600 font-eng">
        <p><?php echo htmlspecialchars($copyright); ?></p>
        <p><?php echo htmlspecialchars($email); ?></p>
    </div>
</footer>