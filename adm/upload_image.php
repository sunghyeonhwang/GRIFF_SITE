<?php
/*********************************************
 * TinyMCE 이미지 업로드 처리기 (수정됨)
 *********************************************/

// 에러 표시 (디버깅용)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// [1] 저장될 경로 설정 (상대 경로 및 절대 경로)
// 주의: 이 폴더가 실제로 존재하고 쓰기 권한(777)이 있어야 합니다.
$imageFolder = "../img/uploads/"; 

// [2] CORS 헤더 설정 (모든 도메인 허용으로 변경하여 403 에러 방지)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// [3] 파일 업로드 처리
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Preflight 요청 처리
    header("HTTP/1.1 200 OK");
    return;
}

if (isset($_FILES['file']['name'])) {
    if (!$_FILES['file']['error']) {
        // 파일명 중복 방지를 위한 랜덤 이름 생성
        $name = md5(rand(100, 200));
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = $name . '_' . time() . '.' . $ext;
        
        $target_file = $imageFolder . $filename;
        
        // 절대 경로 확인 (서버 내부 이동용)
        // ../img/uploads/ 가 admin 폴더 상위에 있으므로 경로를 잘 잡아야 합니다.
        // realpath나 $_SERVER['DOCUMENT_ROOT']를 사용하는 것이 더 안전할 수 있습니다.
        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/img/uploads/' . $filename;

        // 폴더가 없으면 생성 시도
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/img/uploads/')) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/img/uploads/', 0777, true);
        }

        // 파일 이동
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $absolute_path)) {
            // 성공 시: 에디터가 이해할 수 있는 JSON 형태로 이미지 웹 경로 반환
            // 중요: 웹 브라우저에서 접근 가능한 경로여야 합니다.
            echo json_encode(['location' => '/img/uploads/' . $filename]);
        } else {
            // 이동 실패 (권한 문제 등)
            header("HTTP/1.1 500 Server Error");
            echo json_encode(['error' => 'Failed to move uploaded file. Check folder permissions (777).']);
        }
    } else {
        // 업로드 에러
        header("HTTP/1.1 500 Server Error");
        echo json_encode(['error' => 'Upload error code: ' . $_FILES['file']['error']]);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'No file uploaded.']);
}
?>