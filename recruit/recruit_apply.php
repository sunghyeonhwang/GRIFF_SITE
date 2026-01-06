<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";

// [1] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

// [2] 공고 정보 조회
$recruit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$job_title = "채용 공고"; 
$category = 'RECRUIT';
$location = '서울 성동구';
$type_kr = '정규직';
$deadline_txt = '상시 채용';
$salary = '협의';

if ($recruit_id > 0) {
    // 공고 테이블명: recruits (또는 recruit_jobs)
    $sql = "SELECT * FROM recruits WHERE id = " . $conn->real_escape_string($recruit_id);
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        $job_title = htmlspecialchars($row['title']);
        $category = !empty($row['tech_stack']) ? htmlspecialchars($row['tech_stack']) : 'RECRUIT'; 
        $location = !empty($row['location']) ? htmlspecialchars($row['location']) : '서울 성동구';
        $type_kr = !empty($row['job_type']) ? htmlspecialchars($row['job_type']) : '정규직';
        $deadline_txt = !empty($row['deadline']) ? date("Y.m.d", strtotime($row['deadline'])) : "상시 채용";
        $salary = (!empty($row['salary'])) ? htmlspecialchars($row['salary']) : '협의';
    } else {
        echo "<script>alert('존재하지 않는 공고입니다.'); location.href='/recruit/recruit_list.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/recruit/recruit_list.php';</script>";
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .fade-up-init { opacity: 0; transform: translateY(30px); }
    .input-label { display: block; font-family: 'Freesentation', sans-serif; font-weight: 700; margin-bottom: 0.8rem; color: #1a1a1a; font-size: 1.1rem; }
    .input-field { width: 100%; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.2rem 1.5rem; font-family: 'Freesentation', sans-serif; font-size: 1.05rem; color: #1a1a1a; transition: all 0.3s; }
    .input-field:focus { border-color: #2DC49A; outline: none; box-shadow: 0 0 0 4px rgba(45, 196, 154, 0.1); }
    .file-box { position: relative; border: 2px dashed #e5e7eb; border-radius: 1rem; padding: 2.5rem; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa; }
    .file-box:hover { border-color: #2DC49A; background: #f0fdf9; }
    .file-name { margin-top: 0.8rem; font-size: 0.95rem; color: #2DC49A; font-weight: 700; }
    .section-title { font-family: 'URWDIN', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1a1a1a; border-bottom: 2px solid #1a1a1a; padding-bottom: 1rem; margin-bottom: 2rem; }
    .summary-item { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 0; border-bottom: 1px solid #f3f4f6; }
    .summary-item:last-child { border-bottom: none; }
    .summary-label { font-weight: 700; color: #1a1a1a; font-size: 0.95rem; flex-shrink: 0; }
    .summary-value { color: #6b7280; font-size: 0.95rem; text-align: right; word-break: keep-all; padding-left: 1rem; }
    .list-btn { display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.8rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 100px; font-family: 'URWDIN', sans-serif; font-weight: 700; font-size: 0.9rem; color: #6b7280; background: #fff; transition: all 0.3s ease; cursor: pointer; }
    .list-btn:hover { background: #1a1a1a; color: #fff; border-color: #1a1a1a; transform: translateY(-2px); }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-[100px] lg:pb-[200px] min-h-screen">

    <div class="mb-20 fade-up-init">
        <h1 class="font-eng text-4xl md:text-5xl font-bold mb-4">APPLICATION<span class="text-[#2DC49A]">.</span></h1>
        <p class="font-kor text-xl text-neutral-500">
            지원 공고 : <span class="text-black font-bold border-b border-black pb-1"><?= $job_title ?></span>
        </p>
    </div>

    <form id="applyForm" action="/recruitrecruit_apply_ok.php" method="POST" enctype="multipart/form-data" class="fade-up-init">
        <input type="hidden" name="recruit_id" value="<?= $recruit_id ?>">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-[30px] mb-12">
            
            <div class="lg:col-span-8">
                <div class="mb-16">
                    <h3 class="section-title">01. PROFILE</h3>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-4">
                            <label class="input-label">증명사진 <span class="text-[#2DC49A]">*</span></label>
                            <p class="text-xs text-neutral-400 mb-2 font-eng">JPG/PNG (3:4 Ratio), Max 5MB</p>
                            <div class="file-box h-[200px] flex flex-col justify-center items-center" onclick="document.getElementById('photo_file').click()">
                                <input type="file" name="photo_file" id="photo_file" class="hidden" accept="image/png, image/jpeg" required data-max-size="5" onchange="checkFile(this, 'photo-name')">
                                <svg class="w-8 h-8 text-neutral-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-neutral-400 text-sm">Upload Photo</span>
                                <div id="photo-name" class="file-name text-xs break-all"></div>
                            </div>
                        </div>
                        <div class="md:col-span-8 space-y-6">
                            <div><label class="input-label">이름 <span class="text-[#2DC49A]">*</span></label><input type="text" name="name" class="input-field" required placeholder="성함을 입력해주세요"></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><label class="input-label">연락처 <span class="text-[#2DC49A]">*</span></label><input type="text" name="phone" class="input-field" required placeholder="010-0000-0000"></div>
                                <div><label class="input-label">이메일 <span class="text-[#2DC49A]">*</span></label><input type="email" name="email" class="input-field" required placeholder="example@email.com"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-16">
                    <h3 class="section-title">02. ESSAY</h3>
                    <div class="mb-10"><label class="input-label">자기소개 <span class="text-[#2DC49A]">*</span></label><textarea name="cover_letter" class="input-field h-48 resize-none" required placeholder="자신을 자유롭게 소개해 주세요."></textarea></div>
                    <div class="mb-10"><label class="input-label">지원동기 <span class="text-[#2DC49A]">*</span></label><textarea name="motivation" class="input-field h-48 resize-none" required placeholder="그리프에 지원하게 된 동기를 적어주세요."></textarea></div>
                </div>

                <div class="mb-0">
                    <h3 class="section-title">03. ATTACHMENTS</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="input-label">이력서 <span class="text-[#2DC49A]">*</span></label>
                            <p class="text-xs text-neutral-400 mb-2 font-eng">PDF/Word (Max 10MB)</p>
                            <div class="file-box" onclick="document.getElementById('resume_file').click()">
                                <input type="file" name="resume_file" id="resume_file" class="hidden" required accept=".pdf,.doc,.docx" data-max-size="10" onchange="checkFile(this, 'resume-name')">
                                <svg class="w-6 h-6 mx-auto text-neutral-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-neutral-400 text-sm">Upload Resume</span>
                                <div id="resume-name" class="file-name"></div>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">포트폴리오 <span class="text-[#2DC49A]">*</span></label>
                            <p class="text-xs text-neutral-400 mb-2 font-eng">PDF/ZIP (Max 50MB)</p>
                            <div class="file-box" onclick="document.getElementById('portfolio_file').click()">
                                <input type="file" name="portfolio_file" id="portfolio_file" class="hidden" required accept=".pdf,.zip,.pptx" data-max-size="50" onchange="checkFile(this, 'pf-name')">
                                <svg class="w-6 h-6 mx-auto text-neutral-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="text-neutral-400 text-sm">Upload Portfolio</span>
                                <div id="pf-name" class="file-name"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 h-full relative">
                <div class="sticky top-[150px]">
                    <div class="bg-white rounded-[2rem] p-6 md:p-8 border border-neutral-200 shadow-xl shadow-neutral-100 mb-8 font-kor">
                        <div class="mb-6 pb-2 border-b-2 border-black"><h3 class="font-eng font-bold text-lg">JOB SUMMARY</h3></div>
                        
                        <div class="summary-item"><span class="summary-label">지원 공고</span><span class="summary-value font-bold text-black"><?= $job_title ?></span></div>
                        <div class="summary-item"><span class="summary-label">기술 스택</span><span class="summary-value font-eng font-bold text-black"><?= $category ?></span></div>
                        <div class="summary-item"><span class="summary-label">급여</span><span class="summary-value font-bold"><?= $salary ?></span></div>
                        <div class="summary-item"><span class="summary-label">근무지</span><span class="summary-value"><?= $location ?></span></div>
                        <div class="summary-item"><span class="summary-label">고용형태</span><span class="summary-value"><?= $type_kr ?></span></div>
                        <div class="summary-item"><span class="summary-label">마감일</span><span class="summary-value"><?= $deadline_txt ?></span></div>
                    </div>
                    
                    <button type="button" onclick="handleApply()" class="group w-full bg-black text-white font-eng font-bold text-xl py-5 rounded-2xl hover:bg-[#2DC49A] transition-colors shadow-lg flex items-center justify-center gap-2">
                        <span>SUBMIT APPLICATION</span>
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </div>

        </div> 
        
        <div class="max-w-[1400px] mx-auto mt-20 border-t border-neutral-200 pt-12">
            <div class="flex flex-col items-center">
                <label class="flex items-start gap-3 cursor-pointer p-6 bg-neutral-50 rounded-xl mb-12 border border-transparent hover:border-neutral-200 transition-colors max-w-2xl w-full">
                    <input type="checkbox" id="privacyCheck" required class="mt-1 w-5 h-5 text-black rounded focus:ring-black">
                    <span class="font-kor text-sm text-neutral-600 leading-relaxed"><strong>[필수] 개인정보 수집 및 이용 동의</strong><br>채용 전형 진행을 위해 입사지원자의 성명, 연락처, 이메일, 학력/경력 사항 등을 수집하며, 수집된 정보는 채용 목적 이외의 용도로 사용되지 않습니다. (보존기간: 채용 종료 후 3년)</span>
                </label>

                <a href="/recruit/recruit_list.php" class="list-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <span>BACK TO LIST</span>
                </a>
            </div>
        </div>

    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => { 
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power2.out", delay: 0.1 }); 
    });

    function checkFile(input, targetId) {
        const target = document.getElementById(targetId);
        const file = input.files[0];
        const maxSizeMB = parseInt(input.getAttribute('data-max-size')) || 10; 
        const maxSizeBytes = maxSizeMB * 1024 * 1024;
        clearError(input);

        if (file) {
            if (file.size > maxSizeBytes) {
                Swal.fire({ icon: 'warning', title: '용량 초과', text: `파일 크기는 ${maxSizeMB}MB를 초과할 수 없습니다.`, confirmButtonColor: '#2DC49A' });
                input.value = ""; target.innerText = "";
                const box = input.closest('.file-box');
                box.style.borderColor = "#e5e7eb"; box.style.backgroundColor = "#fafafa";
                return;
            }
            target.innerText = file.name;
            const box = input.closest('.file-box');
            box.style.borderColor = "#2DC49A"; box.style.backgroundColor = "#f0fdf9";
        } else {
            target.innerText = "";
            const box = input.closest('.file-box');
            box.style.borderColor = "#e5e7eb"; box.style.backgroundColor = "#fafafa";
        }
    }

    function showError(element, message) {
        let targetBox = element;
        if (element.type === 'file') targetBox = element.closest('.file-box');
        if (element.type === 'checkbox') targetBox = element.closest('label');

        targetBox.style.borderColor = "#EF4444"; 
        targetBox.style.backgroundColor = "#FEF2F2"; 

        let parent = targetBox.parentElement;
        if (!parent.querySelector('.error-msg')) {
            const msg = document.createElement('p');
            msg.className = 'error-msg text-[#EF4444] text-xs mt-2 font-kor font-medium fade-up-init';
            msg.innerText = `* ${message}`;
            msg.style.opacity = 1; msg.style.transform = 'translateY(0)';
            if(element.type === 'checkbox') { targetBox.parentElement.appendChild(msg); } else { targetBox.after(msg); }
        }
    }

    function clearError(element) {
        let targetBox = element;
        if (element.type === 'file') targetBox = element.closest('.file-box');
        if (element.type === 'checkbox') targetBox = element.closest('label');

        targetBox.style.borderColor = "#e5e7eb"; 
        targetBox.style.backgroundColor = "#fff"; 
        if (element.type === 'file') targetBox.style.backgroundColor = "#fafafa";
        if (element.type === 'checkbox') targetBox.style.backgroundColor = "#fafafa";

        let parent = targetBox.parentElement;
        if(element.type === 'checkbox') parent = targetBox.parentElement;
        const existingMsg = parent.querySelector('.error-msg');
        if (existingMsg) { existingMsg.remove(); }
    }

    document.addEventListener('input', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            if (e.target.value.trim() !== '') { clearError(e.target); }
        }
    });

    function handleApply() {
        const form = document.getElementById('applyForm');
        let hasError = false;
        let firstErrorInput = null;

        const requiredInputs = form.querySelectorAll('input[type="text"], input[type="email"], textarea');
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                showError(input, "필수 입력 항목입니다."); hasError = true;
                if (!firstErrorInput) firstErrorInput = input;
            } else { clearError(input); }
        });

        const fileInputs = form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            if (!input.value) {
                showError(input, "파일을 업로드해주세요."); hasError = true;
                if (!firstErrorInput) firstErrorInput = input;
            } else {
                const parent = input.closest('.file-box').parentElement;
                const msg = parent.querySelector('.error-msg');
                if(msg) msg.remove();
            }
        });

        const privacyCheck = document.getElementById('privacyCheck');
        if (!privacyCheck.checked) {
            showError(privacyCheck, "개인정보 수집 및 이용에 동의해야 합니다."); hasError = true;
            if (!firstErrorInput) firstErrorInput = privacyCheck;
        } else { clearError(privacyCheck); }

        if (hasError) {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            Toast.fire({ icon: 'error', title: '입력하지 않은 항목이 있습니다.' });
            if (firstErrorInput) {
                firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if(firstErrorInput.type !== 'file' && firstErrorInput.type !== 'checkbox') { firstErrorInput.focus(); }
            }
            return;
        }

        Swal.fire({
            title: '지원서를 제출하시겠습니까?', text: "제출 후에는 수정이 불가능합니다.", icon: 'question',
            showCancelButton: true, confirmButtonColor: '#2DC49A', cancelButtonColor: '#d33',
            confirmButtonText: '제출하기', cancelButtonText: '취소'
        }).then((result) => { if (result.isConfirmed) { form.submit(); } });
    }
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>