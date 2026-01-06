<?php
// 1. 에러 리포팅
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. 공통 헤더 로드
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* [폰트 정의] */
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }

    /* [초기 상태: 숨김] */
    .fade-up-init { opacity: 0; transform: translateY(30px); }

    /* [인터랙티브 인풋 스타일] */
    .input-field {
        width: 100%;
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem; 
        padding: 1.2rem 1.5rem; 
        font-family: 'Freesentation', sans-serif;
        font-size: 1.1rem; 
        color: #1a1a1a;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .input-field:focus {
        background-color: #ffffff;
        /* [수정] 포커스 시 포인트 컬러 적용 */
        border-color: #0D4097; 
        box-shadow: 0 4px 25px rgba(13, 64, 151, 0.08);
        outline: none;
        transform: translateY(-2px);
    }
    .input-field::placeholder { color: #9ca3af; font-size: 1rem; }
    
    /* [아이콘 박스 인터랙션] */
    .info-card { transition: transform 0.3s ease; }
    .info-card:hover .icon-box {
        background-color: #1a1a1a;
        /* [수정] 아이콘 호버 시 포인트 컬러 (노란색 -> 네이비 or 유지? 요청하신건 '점 컬러'지만 통일감을 위해 네이비 추천 or 노란 배경이 있으니 텍스트는 노란색 유지도 가능. 일단 요청대로 네이비 포인트로 통일) */
        color: #fff; 
        background-color: #0D4097;
        transform: rotate(-10deg) scale(1.1);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .info-card:hover .info-title { color: #0D4097; }
    
    /* [버튼 인터랙션] */
    .submit-btn { position: relative; overflow: hidden; transition: all 0.3s ease; }
    .submit-btn:hover .btn-arrow { transform: translateX(6px); }
    .submit-btn:hover {
        /* [수정] 버튼 호버 컬러 변경 */
        background-color: #0D4097 !important;
        box-shadow: none !important;
        color: #fff; 
    }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-24 min-h-screen">

    <div class="flex flex-col lg:flex-row justify-between items-end pb-12 mb-16 gap-8 border-b border-neutral-200 fade-up-init">
        <div class="w-full lg:w-auto">
            <h1 class="font-eng text-[40px] md:text-[60px] font-bold leading-tight text-black">
                CONTACT<span class="text-[#0D4097]">.</span>
            </h1>
            <p class="font-kor text-neutral-800 mt-4 text-base md:text-lg font-medium">
                그리프는 단순히 서비스를 제공하는 것이 아닌, 고객의 고민과 진심으로 마주하는 것부터 시작합니다.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-y-12 lg:gap-x-[15px] items-start">
        
        <div class="lg:col-span-4 lg:sticky lg:top-[150px]">
            <div class="fade-up-init">
                <h2 class="font-eng text-4xl font-bold mb-4 text-neutral-900">Get in Touch</h2>
                <p class="font-kor text-neutral-500 mb-12 leading-relaxed text-lg">
                    프로젝트에 대해 궁금하신 점이나 견적 문의가 있으시면<br>언제든지 편하게 연락주세요.
                </p>
            </div>

            <div class="space-y-12">
                <div class="info-card flex items-start gap-6 group cursor-default fade-up-init">
                    <div class="icon-box w-14 h-14 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-900 transition-all duration-300 shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <span class="font-eng text-xs font-bold text-neutral-400 uppercase tracking-widest mb-1 block">Address</span>
                        <p class="font-kor text-xl font-bold text-neutral-800 info-title transition-colors leading-snug">경기도 하남시 미사대로 540<br>한강미사 2차 A동 711호</p>
                    </div>
                </div>

                <a href="mailto:info@griff.co.kr" class="info-card flex items-start gap-6 group fade-up-init">
                    <div class="icon-box w-14 h-14 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-900 transition-all duration-300 shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="font-eng text-xs font-bold text-neutral-400 uppercase tracking-wider mb-1 block">Email</span>
                        <p class="font-eng text-2xl font-bold text-neutral-800 info-title transition-colors">info@griff.co.kr</p>
                    </div>
                </a>

                <a href="tel:02-326-3701" class="info-card flex items-start gap-6 group fade-up-init">
                    <div class="icon-box w-14 h-14 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-900 transition-all duration-300 shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <div>
                        <span class="font-eng text-xs font-bold text-neutral-400 uppercase tracking-wider mb-1 block">Phone</span>
                        <p class="font-eng text-2xl font-bold text-neutral-800 info-title transition-colors">+82.2.326.3701</p>
                    </div>
                </a>
            </div>

            <div class="mt-14 p-8 bg-gray-100 rounded-3xl border border-gray-200 font-kor fade-up-init w-full max-w-sm">
                <h3 class="font-bold text-neutral-900 mb-4 text-lg">응답 시간</h3>
                <div class="flex justify-between text-neutral-600 text-base mb-2 border-b border-gray-300 pb-2">
                    <span>평일</span>
                    <span class="font-eng font-bold">10:00 - 18:00</span>
                </div>
                <div class="flex justify-between text-neutral-400 text-base">
                    <span>주말 및 공휴일</span>
                    <span>휴무</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8 bg-white rounded-[2rem] p-10 md:p-16 border border-neutral-200 shadow-2xl shadow-neutral-200/50 fade-up-init w-full">
            <h3 class="font-eng text-3xl font-bold mb-12 text-neutral-900 flex items-center gap-3">
                Send Message <span class="w-2.5 h-2.5 bg-[#0D4097] rounded-full inline-block animate-pulse"></span>
            </h3>
            
            <form id="contactForm" action="contact_ok.php" method="POST" class="space-y-10">
                <div>
                    <label class="block font-kor text-sm font-bold text-neutral-900 mb-3 pl-1">이름 <span class="text-[#0D4097]">*</span></label>
                    <input type="text" name="name" class="input-field" placeholder="성함을 입력해주세요" required>
                </div>

                <div>
                    <label class="block font-kor text-sm font-bold text-neutral-900 mb-3 pl-1">이메일 <span class="text-[#0D4097]">*</span></label>
                    <input type="email" name="email" class="input-field" placeholder="example@email.com" required>
                </div>

                <div>
                    <label class="block font-kor text-sm font-bold text-neutral-900 mb-3 pl-1">연락처 <span class="text-[#0D4097]">*</span></label>
                    <input type="text" name="phone" class="input-field" placeholder="010-0000-0000" required>
                </div>

                <div>
                    <label class="block font-kor text-sm font-bold text-neutral-900 mb-3 pl-1">예산 범위</label>
                    <input type="text" name="budget" class="input-field" placeholder="대략적인 예산을 입력해주세요">
                </div>

                <div>
                    <label class="block font-kor text-sm font-bold text-neutral-900 mb-3 pl-1">프로젝트 상세 내용 <span class="text-[#0D4097]">*</span></label>
                    <textarea name="message" class="input-field resize-none h-64" placeholder="프로젝트의 목적, 일정 등 상세 내용을 자유롭게 기재해주세요." required></textarea>
                </div>

                <div class="py-2">
                    <div class="g-recaptcha" data-sitekey="6Ldo0j0sAAAAAHfsPHFz2-X2w6dRXX-8Ow7bwMWr"></div>
                </div>

                <div class="space-y-4 pt-4">
                    <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-100 text-xs text-neutral-500 font-kor leading-relaxed h-auto">
                        <strong>[개인정보 수집 및 이용 동의]</strong><br>
                        1. 수집 목적: 문의 응대 및 프로젝트 상담<br>
                        2. 수집 항목: 이름, 이메일, 연락처, 예산 범위, 프로젝트 내용<br>
                        3. 보유 기간: 문의 처리 완료 후 3년 (법적 의무 보유기간 제외)<br>
                        * 귀하는 개인정보 수집 및 이용에 대한 동의를 거부할 권리가 있으며, 동의 거부 시 문의 접수가 제한될 수 있습니다.
                    </div>
                    
                    <label class="flex items-center gap-3 cursor-pointer group p-1">
                        <input type="checkbox" id="privacyCheck" required class="w-5 h-5 text-black rounded border-gray-300 focus:ring-black">
                        <span class="text-sm text-neutral-800 font-bold font-kor group-hover:text-black transition-colors">개인정보 수집 및 이용에 동의합니다.</span>
                    </label>
                </div>

                <button type="button" onclick="handleContact()" class="submit-btn w-full bg-neutral-900 text-white font-eng font-bold text-xl py-5 rounded-xl flex items-center justify-center gap-3 mt-8">
                    <span class="pt-[2px]">Send Message</span>
                    <svg class="btn-arrow w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power2.out", delay: 0.1 });
    });

    // 1. 에러 표시 함수
    function showError(element, message) {
        let targetBox = element;
        if (element.type === 'checkbox') {
            targetBox = element.closest('label');
        }

        targetBox.style.borderColor = "#EF4444"; // Red
        targetBox.style.backgroundColor = "#FEF2F2"; 

        let parent = targetBox.parentElement;
        if (!parent.querySelector('.error-msg')) {
            const msg = document.createElement('p');
            msg.className = 'error-msg text-[#EF4444] text-xs mt-2 font-kor font-medium fade-up-init pl-1';
            msg.innerText = `* ${message}`;
            msg.style.opacity = 1; 
            msg.style.transform = 'translateY(0)';
            
            if(element.type === 'checkbox') {
                targetBox.parentElement.appendChild(msg);
            } else {
                targetBox.after(msg);
            }
        }
    }

    // 2. 에러 초기화 함수
    function clearError(element) {
        let targetBox = element;
        if (element.type === 'checkbox') targetBox = element.closest('label');

        targetBox.style.borderColor = "#e5e7eb"; 
        
        if (element.type === 'checkbox') {
            targetBox.style.backgroundColor = "transparent";
        } else {
            targetBox.style.backgroundColor = "#f9fafb"; 
        }

        let parent = targetBox.parentElement;
        if(element.type === 'checkbox') parent = targetBox.parentElement;
        
        const existingMsg = parent.querySelector('.error-msg');
        if (existingMsg) existingMsg.remove();
    }

    // 3. 입력 시 에러 즉시 제거
    document.addEventListener('input', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (e.target.value.trim() !== '') { clearError(e.target); }
        }
        if (e.target.type === 'checkbox') {
            if(e.target.checked) clearError(e.target);
        }
    });

    // 4. 폼 검증 및 전송 핸들러
    function handleContact() {
        const form = document.getElementById('contactForm');
        let hasError = false;
        let firstErrorInput = null;

        const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
        requiredInputs.forEach(input => {
            if (input.type === 'checkbox') return;

            if (!input.value.trim()) {
                showError(input, "필수 입력 항목입니다.");
                hasError = true;
                if (!firstErrorInput) firstErrorInput = input;
            } else {
                clearError(input);
            }
        });

        // 리캡차 검사
        if (typeof grecaptcha !== 'undefined') {
            const response = grecaptcha.getResponse();
            if (response.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: '자동등록방지 확인',
                    text: '로봇이 아님을 확인해주세요.',
                    confirmButtonColor: '#1a1a1a'
                });
                return;
            }
        }

        // 개인정보 동의 검사
        const privacyCheck = document.getElementById('privacyCheck');
        if (!privacyCheck.checked) {
            showError(privacyCheck, "개인정보 수집 및 이용에 동의해야 합니다.");
            hasError = true;
            if (!firstErrorInput) firstErrorInput = privacyCheck;
        } else {
            clearError(privacyCheck);
        }

        if (hasError) {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: 'error', title: '입력하지 않은 항목이 있습니다.' });

            if (firstErrorInput) {
                firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if(firstErrorInput.type !== 'checkbox') {
                    firstErrorInput.focus();
                }
            }
            return;
        }

        // [수정] SweetAlert 확인 버튼 컬러 #0D4097
        Swal.fire({
            title: '문의를 전송하시겠습니까?',
            text: "담당자가 확인 후 빠르게 연락드리겠습니다.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0D4097', // 포인트 컬러
            cancelButtonColor: '#d33',
            confirmButtonText: '전송하기',
            cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>

<?php 
if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php";
?>