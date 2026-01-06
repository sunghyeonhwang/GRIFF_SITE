<?php
// [1] 에러 리포팅 켜기 (디버깅용)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = $_SERVER['DOCUMENT_ROOT'];

// [2] DB 연결
if (file_exists("$root/inc/front_db_connect.php")) {
    include "$root/inc/front_db_connect.php";
} else {
    include "$root/inc/db_connect.php";
}

// [3] POST 데이터 수신
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

if (empty($email) || empty($phone)) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='/recruit/recruit_check.php';</script>";
    exit;
}

// [4] 지원자 및 공고 정보 조회 (안전장치 추가)
// ★ 중요: applicants 테이블의 공고 ID 컬럼이 'recruit_id'인지 'job_id'인지 확인 필요
// 일단 recruit_apply_ok.php에서 'recruit_id'로 저장하도록 했으므로 recruit_id를 사용합니다.
// 만약 에러가 난다면 'a.recruit_id'를 'a.job_id'로 바꿔보세요.
$sql = "SELECT a.*, 
               r.title as job_title, 
               r.tech_stack, 
               r.salary, 
               r.location, 
               r.job_type, 
               r.deadline 
        FROM applicants a 
        LEFT JOIN recruits r ON a.recruit_id = r.id 
        WHERE a.email = ? AND a.phone = ? 
        ORDER BY a.id DESC LIMIT 1";

// SQL 준비 (여기서 에러가 나면 500이 뜸 -> 예외처리 추가)
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // 쿼리 준비 실패 시 (테이블명이나 컬럼명이 틀린 경우)
    die("<h1>System Error</h1><p>SQL Query Failed: " . $conn->error . "</p><p>관리자에게 'recruit_edit.php 쿼리 오류'라고 알려주세요.</p>");
}

$stmt->bind_param("ss", $email, $phone);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "<script>alert('일치하는 지원 내역이 없습니다.\\n이메일과 연락처를 다시 확인해주세요.'); location.href='/recruit/recruit_check.php';</script>";
    exit;
}

// 공고 정보 변수 매핑 (NULL 처리)
$job_title_disp = !empty($row['job_title']) ? htmlspecialchars($row['job_title']) : '상시 채용 / 인재풀';
$tech_stack_disp = !empty($row['tech_stack']) ? htmlspecialchars($row['tech_stack']) : '-';
$salary_disp = !empty($row['salary']) ? htmlspecialchars($row['salary']) : '협의';
$location_disp = !empty($row['location']) ? htmlspecialchars($row['location']) : '-';
$job_type_disp = !empty($row['job_type']) ? htmlspecialchars($row['job_type']) : '-';
$deadline_disp = !empty($row['deadline']) ? date("Y.m.d", strtotime($row['deadline'])) : '상시 채용';

// 헤더 로드
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
    .fade-up-init { opacity: 0; transform: translateY(30px); }
    
    /* 입력 필드 */
    .input-label { display: block; font-family: 'Freesentation', sans-serif; font-weight: 700; margin-bottom: 0.8rem; color: #1a1a1a; font-size: 1.1rem; }
    .input-field { width: 100%; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.2rem 1.5rem; font-family: 'Freesentation', sans-serif; font-size: 1.05rem; color: #1a1a1a; transition: all 0.3s; }
    .input-field:focus { border-color: #2DC49A; outline: none; box-shadow: 0 0 0 4px rgba(45, 196, 154, 0.1); }
    .input-readonly { background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed; border-color: #e5e7eb; }
    
    /* 파일 박스 */
    .file-box { position: relative; border: 2px dashed #e5e7eb; border-radius: 1rem; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 140px; }
    .file-box:hover { border-color: #2DC49A; background: #f0fdf9; }
    .file-name { margin-top: 0.5rem; font-size: 0.85rem; color: #2DC49A; font-weight: 700; word-break: break-all; }
    .file-existing-badge { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: #1a1a1a; background-color: #e5e7eb; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 0.5rem; font-weight: 700; z-index: 10; position: relative; }
    
    /* 섹션 타이틀 */
    .section-title { font-family: 'URWDIN', sans-serif; font-size: 1.8rem; font-weight: 700; color: #1a1a1a; border-bottom: 2px solid #1a1a1a; padding-bottom: 1rem; margin-bottom: 2rem; }
    
    /* 요약 카드 스타일 */
    .summary-item { display: flex; justify-content: space-between; align-items: center; padding: 1.1rem 0; border-bottom: 1px solid #f3f4f6; }
    .summary-item:last-child { border-bottom: none; }
    .summary-label { font-weight: 700; color: #1a1a1a; font-size: 0.95rem; flex-shrink: 0; font-family: 'Freesentation', sans-serif; }
    .summary-value { color: #6b7280; font-size: 0.95rem; text-align: right; word-break: keep-all; padding-left: 1rem; font-family: 'Freesentation', sans-serif; }

    /* 수정 버튼 (메인 액션) */
    .btn-modify { width: 100%; background-color: #1a1a1a; color: #fff; font-family: 'URWDIN', sans-serif; font-weight: 700; font-size: 1.2rem; padding: 1.2rem; border-radius: 1rem; transition: all 0.3s; box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
    .btn-modify:hover { background-color: #2DC49A; transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(45, 196, 154, 0.3); }
    
    /* 취소 버튼 (필 버튼 스타일) */
    .btn-pill-cancel { display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 2.5rem; border: 1px solid #EF4444; border-radius: 9999px; color: #EF4444; font-family: 'Freesentation', sans-serif; font-weight: 700; font-size: 0.95rem; background: #fff; transition: all 0.3s ease; cursor: pointer; }
    .btn-pill-cancel:hover { background: #EF4444; color: #fff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2); }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[1400px] mx-auto px-6 md:px-12 pt-32 md:pt-48 pb-[100px] min-h-screen">

    <div class="mb-20 fade-up-init">
        <h1 class="font-eng text-4xl md:text-5xl font-bold mb-4">EDIT APPLICATION<span class="text-[#2DC49A]">.</span></h1>
        <p class="font-kor text-xl text-neutral-500">
            제출하신 지원서를 수정하거나 취소할 수 있습니다.
        </p>
    </div>

    <form id="editForm" action="recruit_edit_ok.php" method="POST" enctype="multipart/form-data" class="fade-up-init">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="mode" id="formMode" value="update">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-[30px] mb-12">
            
            <div class="lg:col-span-8">
                
                <div class="mb-16">
                    <h3 class="section-title">01. PROFILE</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="input-label">이름</label>
                            <input type="text" name="name" class="input-field" value="<?= htmlspecialchars($row['name']) ?>" required>
                        </div>
                        <div class="opacity-70">
                            <label class="input-label">이메일 <span class="text-xs font-normal text-gray-400 ml-1">(수정불가)</span></label>
                            <input type="text" class="input-field input-readonly" value="<?= htmlspecialchars($row['email']) ?>" readonly>
                        </div>
                        <div class="opacity-70 md:col-span-2">
                            <label class="input-label">연락처 <span class="text-xs font-normal text-gray-400 ml-1">(수정불가)</span></label>
                            <input type="text" class="input-field input-readonly" value="<?= htmlspecialchars($row['phone']) ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-16">
                    <h3 class="section-title">02. ESSAY</h3>
                    <div class="mb-10">
                        <label class="input-label">자기소개</label>
                        <textarea name="cover_letter" class="input-field h-60 resize-none leading-relaxed" required><?= htmlspecialchars($row['cover_letter']) ?></textarea>
                    </div>
                    <div>
                        <label class="input-label">지원동기</label>
                        <textarea name="motivation" class="input-field h-60 resize-none leading-relaxed" required><?= htmlspecialchars($row['motivation']) ?></textarea>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="section-title">03. ATTACHMENTS</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div>
                            <label class="input-label">증명사진</label>
                            <div class="file-box" onclick="document.getElementById('photo_file').click()">
                                <?php if($row['profile_image']): ?>
                                    <a href="<?= $row['profile_image'] ?>" target="_blank" onclick="event.stopPropagation()" class="file-existing-badge hover:bg-gray-300 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        이미지 확인
                                    </a>
                                <?php endif; ?>
                                <input type="file" name="photo_file" id="photo_file" class="hidden" accept="image/png, image/jpeg" onchange="checkFile(this, 'photo-name')">
                                <svg class="w-8 h-8 text-neutral-300 mb-2 mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-neutral-400 text-sm font-medium">Click to Change</span>
                                <div id="photo-name" class="file-name text-xs"></div>
                            </div>
                        </div>

                        <div>
                            <label class="input-label">이력서</label>
                            <div class="file-box" onclick="document.getElementById('resume_file').click()">
                                <?php if($row['resume_path']): ?>
                                    <a href="<?= $row['resume_path'] ?>" target="_blank" onclick="event.stopPropagation()" class="file-existing-badge hover:bg-gray-300 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        다운로드
                                    </a>
                                <?php endif; ?>
                                <input type="file" name="resume_file" id="resume_file" class="hidden" accept=".pdf,.doc,.docx" onchange="checkFile(this, 'resume-name')">
                                <svg class="w-8 h-8 text-neutral-300 mb-2 mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-neutral-400 text-sm font-medium">Click to Change</span>
                                <div id="resume-name" class="file-name text-xs"></div>
                            </div>
                        </div>

                        <div>
                            <label class="input-label">포트폴리오</label>
                            <div class="file-box" onclick="document.getElementById('portfolio_file').click()">
                                <?php if($row['portfolio_path']): ?>
                                    <a href="<?= $row['portfolio_path'] ?>" target="_blank" onclick="event.stopPropagation()" class="file-existing-badge hover:bg-gray-300 transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        다운로드
                                    </a>
                                <?php endif; ?>
                                <input type="file" name="portfolio_file" id="portfolio_file" class="hidden" accept=".pdf,.zip,.pptx" onchange="checkFile(this, 'pf-name')">
                                <svg class="w-8 h-8 text-neutral-300 mb-2 mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <span class="text-neutral-400 text-sm font-medium">Click to Change</span>
                                <div id="pf-name" class="file-name text-xs"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-20 border-t border-gray-100 pt-10 text-center lg:text-left">
                    <button type="button" onclick="deleteApp()" class="btn-pill-cancel group">
                        <svg class="w-4 h-4 mr-2 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        지원 취소 (데이터 삭제)
                    </button>
                </div>

            </div>

            <div class="lg:col-span-4 h-full relative">
                <div class="sticky top-[150px]">
                    
                    <div class="bg-white rounded-[2rem] p-6 md:p-8 border border-neutral-200 shadow-xl shadow-neutral-100 mb-6 font-kor">
                        <div class="mb-6 pb-2 border-b-2 border-black"><h3 class="font-eng font-bold text-lg">JOB SUMMARY</h3></div>
                        
                        <div class="summary-item">
                            <span class="summary-label">지원 공고</span>
                            <span class="summary-value font-bold text-black"><?= $job_title_disp ?></span>
                        </div>
                        <div class="summary-item"><span class="summary-label">기술 스택</span><span class="summary-value font-eng font-bold text-black"><?= $tech_stack_disp ?></span></div>
                        <div class="summary-item"><span class="summary-label">급여</span><span class="summary-value font-bold"><?= $salary_disp ?></span></div>
                        <div class="summary-item"><span class="summary-label">근무지</span><span class="summary-value"><?= $location_disp ?></span></div>
                        <div class="summary-item"><span class="summary-label">고용형태</span><span class="summary-value"><?= $job_type_disp ?></span></div>
                        <div class="summary-item"><span class="summary-label">마감일</span><span class="summary-value"><?= $deadline_disp ?></span></div>
                    </div>
                    
                    <button type="button" onclick="updateApp()" class="btn-modify group">
                        <span>MODIFY APPLICATION</span>
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>

                </div>
            </div>

        </div> 
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => { 
        gsap.to(".fade-up-init", { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: "power2.out", delay: 0.1 }); 
    });

    // 파일 선택 시 이름 표시
    function checkFile(input, targetId) {
        const target = document.getElementById(targetId);
        const file = input.files[0];
        if (file) {
            target.innerText = file.name;
            const box = input.closest('.file-box');
            box.style.borderColor = "#2DC49A"; 
            box.style.backgroundColor = "#f0fdf9";
        } else {
            target.innerText = "";
            const box = input.closest('.file-box');
            box.style.borderColor = "#e5e7eb"; 
            box.style.backgroundColor = "#fafafa";
        }
    }

    const form = document.getElementById('editForm');
    const modeInput = document.getElementById('formMode');

    // 수정하기
    function updateApp() {
        modeInput.value = 'update';
        Swal.fire({
            title: '지원서를 수정하시겠습니까?',
            text: '수정된 내용은 즉시 반영됩니다.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a1a1a',
            confirmButtonText: '수정 완료',
            cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }

    // 삭제하기
    function deleteApp() {
        modeInput.value = 'delete';
        Swal.fire({
            title: '정말 지원을 취소하시겠습니까?',
            text: "취소 후에는 모든 데이터가 영구 삭제되며 복구할 수 없습니다.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: '네, 지원을 취소합니다',
            cancelButtonText: '돌아가기'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    }
</script>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>