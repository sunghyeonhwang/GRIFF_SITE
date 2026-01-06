<?php
require_once '../inc/db_connect.php';

// 1. 현재 리스트와 동일한 필터 로직 적용
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$job_filter = isset($_GET['recruit_id']) ? $_GET['recruit_id'] : 'All';

$sql = "SELECT a.*, r.title as job_title 
        FROM applicants a 
        LEFT JOIN recruits r ON a.recruit_id = r.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (a.name LIKE :search OR a.email LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($job_filter && $job_filter !== 'All') {
    $sql .= " AND a.recruit_id = :rid";
    $params[':rid'] = $job_filter;
}
$sql .= " ORDER BY a.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. CSV 헤더 설정
$filename = "applicants_list_" . date('Ymd') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 3. 파일 생성 및 한글 깨짐 방지 (BOM 추가)
$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // Excel에서 한글 깨짐 방지용 BOM

// 4. 컬럼 제목 (헤더)
fputcsv($output, ['ID', 'Name', 'Job Title', 'Email', 'Phone', 'Status', 'Applied Date', 'Resume Link', 'Portfolio Link']);

// 5. 데이터 입력
foreach ($rows as $row) {
    // 상태값 한글 변환 (선택사항)
    $status_text = $row['status']; // 필요하면 switch case로 한글 변환 가능
    
    // 다운로드 링크 생성 (도메인 포함)
    $site_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $resume_link = $row['resume_path'] ? $site_url . $row['resume_path'] : '';
    $port_link = $row['portfolio_path'] ? $site_url . $row['portfolio_path'] : '';

    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['job_title'],
        $row['email'],
        $row['phone'],
        $status_text,
        $row['applied_at'],
        $resume_link,
        $port_link
    ]);
}

fclose($output);
exit;
?>