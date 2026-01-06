<section id="studio-section" class="relative h-[80vh] overflow-hidden flex items-center justify-center z-20 mt-0 bg-black">
    
    <div class="absolute inset-0 w-full h-full z-0">
        <video src="https://unrealsummit16.cafe24.com/2025/challange25/unrealchallange25_movie_hq.webm" autoplay loop muted playsinline class="w-full h-full object-cover"></video>
        <div class="absolute inset-0 bg-black/20"></div>
    </div>

    <div class="relative z-10 text-center flex flex-col items-center">
        <img src="img/inc/griff_studio_logo.svg" alt="GRIFF STUDIO" class="animate-studio h-[90px] md:h-[150px] w-auto mb-8 opacity-0 translate-y-8">
        
        <a href="/studio/studio_intro.php" id="studio-expand-btn" class="animate-studio group relative flex flex-col items-center justify-center opacity-0 translate-y-8 cursor-pointer py-2">
            
            <div class="text-wrapper relative h-[30px] flex items-center justify-center px-1">
                
                <span class="text-short h-full flex items-center text-white font-eng font-bold text-lg md:text-xl leading-none whitespace-nowrap transition-colors duration-300 group-hover:text-[#F7E731]">
                    자세히 보기
                </span>
                
                <span class="text-long absolute left-1/2 top-0 -translate-x-1/2 h-full flex items-center text-white font-eng font-bold text-lg md:text-xl leading-none whitespace-nowrap opacity-0 pointer-events-none transition-colors duration-300 group-hover:text-[#F7E731]">
                    스튜디오 보기 | 예약하기
                </span>
            </div>

            <div class="line w-full h-[2px] bg-white mt-1 group-hover:bg-[#F7E731] transition-colors duration-300 origin-center"></div>
        </a>
    </div>

</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            
            // 1. 초기 등장 애니메이션
            gsap.to(".animate-studio", {
                scrollTrigger: {
                    trigger: "#studio-section",
                    start: "top 75%",
                },
                y: 0,
                opacity: 1,
                duration: 1,
                stagger: 0.2,
                ease: "power3.out"
            });

            // 2. 버튼 너비 확장 인터랙션
            const btn = document.getElementById('studio-expand-btn');
            const wrapper = btn.querySelector('.text-wrapper');
            const shortText = btn.querySelector('.text-short');
            const longText = btn.querySelector('.text-long');

            btn.addEventListener('mouseenter', () => {
                const currentShortWidth = shortText.offsetWidth;
                const currentLongWidth = longText.offsetWidth;

                // 텍스트 교체 (위/아래 방향 통일)
                gsap.to(shortText, { 
                    y: -15, // 이동 거리 살짝 축소
                    opacity: 0, 
                    duration: 0.3, 
                    ease: "power2.in" 
                });

                gsap.fromTo(longText, 
                    { y: 15 }, // 아래에서 시작
                    { y: 0, opacity: 1, duration: 0.4, delay: 0.1, ease: "power2.out" } // 0으로 와야 정확히 h-full 중앙
                );

                gsap.fromTo(wrapper, 
                    { width: currentShortWidth }, 
                    { width: currentLongWidth, duration: 0.5, ease: "elastic.out(1, 0.7)" }
                );
            });

            btn.addEventListener('mouseleave', () => {
                const currentShortWidth = shortText.offsetWidth;
                
                gsap.to(shortText, { 
                    y: 0, 
                    opacity: 1, 
                    duration: 0.4, 
                    delay: 0.1, 
                    ease: "power2.out" 
                });

                gsap.to(longText, { 
                    y: 15, 
                    opacity: 0, 
                    duration: 0.3, 
                    ease: "power2.in" 
                });

                gsap.to(wrapper, { 
                    width: currentShortWidth, 
                    duration: 0.5, 
                    ease: "power3.out",
                    onComplete: () => {
                        gsap.set(wrapper, { clearProps: "width" });
                    }
                });
            });

        } else {
            document.querySelectorAll('.animate-studio').forEach(el => {
                el.style.opacity = 1;
                el.style.transform = 'translateY(0)';
            });
        }
    });
</script>