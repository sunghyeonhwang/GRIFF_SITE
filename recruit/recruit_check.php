<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .fade-up-init { opacity: 0; transform: translateY(30px); }
    
    /* 입력 필드 스타일 (지원서 페이지와 통일) */
    .input-label { display: block; font-family: 'Freesentation', sans-serif; font-weight: 700; margin-bottom: 0.6rem; color: #1a1a1a; font-size: 1rem; text-align: left; margin-left: 0.5rem; }
    .input-field { width: 100%; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.2rem 1.5rem; font-family: 'Freesentation', sans-serif; font-size: 1.05rem; color: #1a1a1a; transition: all 0.3s ease; }
    .input-field:focus { background-color: #fff; border-color: #2DC49A; outline: none; box-shadow: 0 0 0 4px rgba(45, 196, 154, 0.1); }
    
    /* 버튼 스타일 */
    .btn-submit { width: 100%; background-color: #1a1a1a; color: #fff; font-family: 'URWDIN', sans-serif; font-weight: 700; font-size: 1.1rem; padding: 1.2rem; border-radius: 1rem; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-submit:hover { background-color: #2DC49A; transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(45, 196, 154, 0.3); }
    .btn-submit svg { transition: transform 0.3s; }
    .btn-submit:hover svg { transform: translateX(5px); }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[550px] mx-auto px-6 pt-40 md:pt-48 pb-32 text-center min-h-[85vh] flex flex-col justify-center">
    
    <div class="mb-12 fade-up-init">
        <span class="font-eng text-[#2DC49A] font-bold tracking-[0.2em] text-xs mb-3 block">MY APPLICATION</span>
        <h1 class="font-eng text-4xl md:text-5xl font-bold mb-5 leading-none text-neutral-900">
            CHECK<br>STATUS<span class="text-[#2DC49A]">.</span>
        </h1>
        <p class="font-kor text-lg text-neutral-500 font-medium leading-relaxed break-keep">
            지원 시 입력하셨던 정보로<br>본인 확인 후 지원서를 수정할 수 있습니다.
        </p>
    </div>

    <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-2xl shadow-neutral-100 border border-neutral-100 fade-up-init relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#FAEB15]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-[#2DC49A]/10 rounded-full blur-3xl pointer-events-none"></div>

        <form action="recruit_edit.php" method="POST" class="space-y-6 relative z-10">
            <div>
                <label class="input-label">이메일</label>
                <input type="email" name="email" class="input-field" required placeholder="example@email.com">
            </div>
            <div>
                <label class="input-label">연락처</label>
                <input type="text" name="phone" class="input-field" required placeholder="010-0000-0000">
            </div>
            
            <div class="pt-4">
                <button type="submit" class="btn-submit">
                    <span>SEARCH APPLICATION</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l7 7m7-7H3"></path></svg>
                </button>
            </div>
        </form>
    </div>

    <div class="mt-12 fade-up-init">
        <a href="/recruit_list.php" class="group inline-flex items-center gap-2 text-neutral-400 hover:text-black font-eng font-bold text-sm transition-colors py-2 px-4 rounded-full hover:bg-neutral-100">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            BACK TO RECRUIT LIST
        </a>
    </div>

</div>

<script>
    // GSAP 등장 애니메이션
    document.addEventListener("DOMContentLoaded", () => { 
        gsap.to(".fade-up-init", { 
            y: 0, 
            opacity: 1, 
            duration: 1, 
            stagger: 0.15, 
            ease: "power3.out" 
        }); 
    });
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>