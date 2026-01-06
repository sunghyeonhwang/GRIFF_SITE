<?php
require_once '../inc/db_connect.php';

if (!isset($_GET['id'])) exit('No ID provided');
$id = (int)$_GET['id'];

// 지원자 정보 조회
$stmt = $pdo->prepare("SELECT a.*, r.title as job_title FROM applicants a LEFT JOIN recruits r ON a.recruit_id = r.id WHERE a.id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) exit('Applicant not found');

// 파일 존재 여부 확인 및 경로 수집
$files = [];
$root = $_SERVER['DOCUMENT_ROOT'];

// 1. 이력서 확인
if (!empty($app['resume_path']) && file_exists($root . $app['resume_path'])) {
    // 저장될 파일명: [이름]_Resume_원래파일명
    $key_name = $app['name'] . '_Resume_' . basename($app['resume_path']);
    $files[$key_name] = $root . $app['resume_path'];
}

// 2. 포트폴리오 확인
if (!empty($app['portfolio_path']) && file_exists($root . $app['portfolio_path'])) {
    // 저장될 파일명: [이름]_Portfolio_원래파일명
    $key_name = $app['name'] . '_Portfolio_' . basename($app['portfolio_path']);
    $files[$key_name] = $root . $app['portfolio_path'];
}

// 파일이 없을 경우
if (empty($files)) {
    echo "<script>alert('다운로드할 첨부파일이 없습니다.'); history.back();</script>";
    exit;
}

// ---------------------------------------------------------
// ★ CASE A: 파일이 1개일 때 -> 압축 없이 바로 다운로드
// ---------------------------------------------------------
if (count($files) === 1) {
    // 배열의 첫 번째 파일 가져오기
    $download_name = key($files); // 위에서 만든 key_name
    $file_path = reset($files);   // 실제 서버 경로

    if (file_exists($file_path)) {
        // MIME 타입 감지
        $mime = mime_content_type($file_path);
        
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $download_name . '"'); // 다운로드 창 뜸
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }
}

// ---------------------------------------------------------
// ★ CASE B: 파일이 여러 개일 때 -> ZIP 압축 다운로드
// ---------------------------------------------------------
// 파일명 정리 (공백/특수문자 제거)
$clean_name = preg_replace('/[^a-zA-Z0-9가-힣]/u', '', $app['name']); 
$clean_job = preg_replace('/[^a-zA-Z0-9]/', '', $app['job_title']);
$date = date('Y-m-d');
$zip_filename = "{$clean_name}_{$clean_job}_{$date}.zip";

$zip = new ZipArchive();
$temp_zip = tempnam(sys_get_temp_dir(), 'Zip');

if ($zip->open($temp_zip, ZipArchive::CREATE) !== TRUE) {
    exit("Could not open archive");
}

foreach ($files as $localName => $realPath) {
    $zip->addFile($realPath, $localName);
}

$zip->close();

if (file_exists($temp_zip)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($temp_zip));
    
    readfile($temp_zip);
    unlink($temp_zip); // 임시 파일 삭제
    exit;
}
?>