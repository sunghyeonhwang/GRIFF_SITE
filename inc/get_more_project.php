<?php
// inc/get_more_project.php

// 1. DB 연결 (같은 폴더 내 front_db_connect.php 포함)
if (file_exists(__DIR__ . '/front_db_connect.php')) {
    include __DIR__ . '/front_db_connect.php';
} else {
    die("DB Connect Error");
}

if (!isset($conn)) {
    die("DB Connection Failed");
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 6;
$limit = 6;

// [수정] 2024년 1월 1일 이후 프로젝트 모두 조회 (더보기 버튼 클릭 시)
$sql = "SELECT * FROM projects WHERE status = 'published' AND created_at >= '2024-01-01' ORDER BY sort_order ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        $thumb = $row['thumbnail_path'];
        if($thumb && strpos($thumb, '/') !== 0) $thumb = '/'.$thumb;
        
        $img_tag = "";
        if(!empty($thumb) && file_exists($_SERVER['DOCUMENT_ROOT'] . $thumb)) {
            $img_tag = '<img src="'.htmlspecialchars($thumb).'" alt="'.htmlspecialchars($row['title']).'" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">';
        } else {
            $img_tag = '<div class="w-full h-full flex items-center justify-center text-gray-400 font-eng text-sm bg-neutral-200">NO IMAGE</div>';
        }

        $client_name = $row['client_name'] ?? $row['client name'] ?? '';
        $client_html = !empty($client_name) ? "Client. " . htmlspecialchars($client_name) : "";

        $col_span_class = (rand(1, 10) <= 3) ? 'md:col-span-2' : 'col-span-1';
        if ($col_span_class === 'md:col-span-2') {
            $aspect_class = (rand(1, 10) <= 6) ? 'aspect-video' : 'aspect-square';
        } else {
            $aspect_class = (rand(1, 10) <= 5) ? 'aspect-[4/5]' : 'aspect-square';
        }

        echo '
        <a href="/project_view.php?id='.$row['id'].'" 
           class="group relative block w-full h-full bg-gray-100 overflow-hidden project-item new-item opacity-0 translate-y-20 '.$col_span_class.' '.$aspect_class.'">
            
            '.$img_tag.'

            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/60 transition-colors duration-500 p-6 flex flex-col justify-end">
                <div class="translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                    <span class="font-eng text-[10px] font-bold text-white/80 mb-2 block uppercase tracking-widest">
                        '.htmlspecialchars($row['category']).'
                    </span>
                    <h3 class="font-eng text-base md:text-xl font-bold text-white leading-tight mb-2 line-clamp-2">
                        '.htmlspecialchars($row['title']).'
                    </h3>
                    <p class="font-kor text-white/70 text-[11px] truncate">
                        '.$client_html.'
                    </p>
                </div>
            </div>
        </a>';
    }
}
?>