<?php
$root = $_SERVER['DOCUMENT_ROOT'];
if (file_exists("$root/inc/header.php")) require_once "$root/inc/header.php";
?>

<style>
    .font-eng { font-family: 'URWDIN', sans-serif; }
    .font-kor { font-family: 'Freesentation', sans-serif; }
</style>

<div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-[1400px] h-[100px] bg-[#FAEB15] -z-10 rounded-b-[30px]"></div>

<div class="relative z-10 w-full max-w-[500px] mx-auto px-6 pt-48 pb-32 min-h-screen flex flex-col items-center">
    
    <div class="bg-white w-full rounded-[2rem] p-8 md:p-10 border border-neutral-200 shadow-xl">
        <h2 class="font-eng text-3xl font-bold mb-2 text-center">CHECK BOOKING</h2>
        <p class="font-kor text-neutral-500 text-center mb-8">예약 시 입력한 정보를 입력해주세요.</p>

        <form action="studio_booking_list.php" method="POST" class="space-y-6">
            <div>
                <label class="block font-kor text-sm font-bold mb-2 ml-1">이메일</label>
                <input type="email" name="client_email" class="w-full bg-neutral-50 border border-neutral-200 rounded-xl px-4 py-3 font-kor focus:outline-none focus:border-[#FFD400]" placeholder="sample@email.com" required>
            </div>
            <div>
                <label class="block font-kor text-sm font-bold mb-2 ml-1">연락처</label>
                <input type="text" name="client_phone" class="w-full bg-neutral-50 border border-neutral-200 rounded-xl px-4 py-3 font-kor focus:outline-none focus:border-[#FFD400]" placeholder="010-0000-0000" required>
            </div>
            
            <button type="submit" class="w-full bg-black text-white font-eng font-bold text-lg py-4 rounded-xl hover:bg-[#FFD400] hover:text-black transition-colors">
                SEARCH
            </button>
        </form>
    </div>

</div>

<?php if (file_exists("$root/inc/footer.php")) require_once "$root/inc/footer.php"; ?>