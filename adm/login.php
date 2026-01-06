<?php
// 에러 확인 설정 (개발 완료 후엔 주석 처리 권장)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 세션 설정 (쿠키 수명 연장 등 필요 시 여기서 설정)
session_start();

// DB 연결
require_once '../inc/db_connect.php';

// 이미 로그인 상태면 대시보드로 이동
if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit;
}

$error = '';

// [추가] 쿠키에 저장된 아이디가 있는지 확인
$saved_id = isset($_COOKIE['saved_admin_id']) ? $_COOKIE['saved_admin_id'] : '';

// 로그인 처리
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember = isset($_POST['remember']); // 체크박스 여부 확인

    if($username && $password){
        try {
            // 1. 아이디 조회
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
            $stmt->execute(array(':username' => $username));
            $admin = $stmt->fetch();

            // 2. 비밀번호 확인
            if($admin && password_verify($password, $admin['password'])){
                // 로그인 성공: 세션 생성
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                
                // [추가] Remember Me (아이디 기억하기) 처리
                if ($remember) {
                    // 30일(86400초 * 30) 동안 쿠키 저장
                    setcookie('saved_admin_id', $username, time() + (86400 * 30), "/");
                } else {
                    // 체크 해제 시 쿠키 삭제 (시간을 과거로 설정)
                    setcookie('saved_admin_id', '', time() - 3600, "/");
                }

                // 대시보드로 이동
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "아이디 또는 비밀번호가 일치하지 않습니다.";
            }
        } catch(PDOException $e) {
            $error = "DB 에러: " . $e->getMessage();
        }
    } else {
        $error = "아이디와 비밀번호를 모두 입력해주세요.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GRIFF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">

    <div class="bg-white p-10 rounded-xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="mb-8 flex items-center justify-center">
            <img src="/img/inc/logo_cms.svg" alt="GRIFF CMS" class="w-36 h-auto">
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-500 text-sm p-3 rounded-lg mb-6 text-center font-medium">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Admin ID</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($saved_id); ?>"
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-all placeholder-gray-400"
                       placeholder="Enter your ID" required>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" 
                       class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-all placeholder-gray-400"
                       placeholder="Enter your password" required>
            </div>

            <div class="flex items-center justify-between mb-8">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black" 
                           <?php echo $saved_id ? 'checked' : ''; ?>>
                    <span class="ml-2 text-sm text-gray-500 hover:text-gray-900">Remember Me</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-black text-white font-bold py-3.5 rounded-lg hover:bg-gray-800 transition-colors shadow-lg transform active:scale-95 duration-150">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-50 pt-6">
            <p class="text-xs text-gray-400">© 2026 GRIFF Inc. All rights reserved.</p>
        </div>
    </div>

</body>
</html>