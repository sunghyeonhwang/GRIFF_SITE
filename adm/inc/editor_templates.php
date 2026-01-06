<?php
// -------------------------------------------------------------------
// TinyMCE 에디터용 HTML 템플릿 모음 (총 5종)
// -------------------------------------------------------------------

$editor_templates = [];
//===================================================================
// [기본] 블로기스타일
// ===================================================================
$editor_templates[] = [
    'title' => '블로그 스타일 ',
    'description' => '블로그 스타일 (inner-wrap 제거됨)',
    'content' => <<<HTML
<div class="custom-project-layout">
    <header class="hero-header text-center">
        <span class="cafe-location">TOKYO CAFE GUIDE Vol.1</span>
        <h1 class="hero-title">제목을 입력하세요</h1>
        <p class="hero-subtitle">부제목을 입력하세요</p>
        <div class="hero-image-frame">
            <img src="https://placehold.co/1000x500/eee/999?text=Image+1:+Main+Header+(1000x500)" alt="메인 이미지">
        </div>
    </header>
    <section class="section-padding intro-text text-center">
        <p>여기에 인트로 텍스트를 입력하세요. 첫 글자는 자동으로 크게 변환됩니다. 도쿄는 끊임없이 움직이는 도시입니다. 수많은 인파와 빌딩 숲 사이에서 때로는 잠시 멈춤 버튼을 누를 공간이 필요합니다.</p>
    </section>
    <section class="section-padding cafe-section">
        <div class="grid-feature">
            <div class="feature-main-img">
                <img src="https://placehold.co/600x500/eee/999?text=Image+2:+Section+Main+(600x500)" alt="섹션 이미지">
            </div>
            <div>
                <h2>01. 소제목 입력</h2>
                <span class="cafe-location">Location Name</span>
                <p class="cafe-description mb-4" style="text-align: left;">내용을 입력하세요. 미니멀리즘의 극치를 보여주는 공간입니다.</p>
                <!-- <div class="feature-detail-img">
                    <img src="https://placehold.co/400x250/eee/999?text=Image+3:+Detail+(400x250)" alt="상세 이미지">
                </div> -->
            </div>
        </div>
    </section>
    <section class="section-padding cafe-section">
            <div class="gallery-text text-center mb-4">
            <h2>02. 소제목 입력</h2>
            <span class="cafe-location">Location Name</span>
            <p class="cafe-description intro-text">내용을 입력하세요. 기찻길 옆 작은 2층 건물 전체가 카페입니다.</p>
        </div>
        <div class="grid-gallery">
            <div class="gallery-img-frame">
                <img src="https://placehold.co/500x400/eee/999?text=Image+4:+Gallery+Left+(500x400)" alt="갤러리 좌">
            </div>
            <div class="gallery-img-frame">
                <img src="https://placehold.co/500x400/eee/999?text=Image+5:+Gallery+Right+(500x400)" alt="갤러리 우">
            </div>
        </div>
    </section>
    
    <div class="interlude-banner">
        <img src="https://placehold.co/1600x400/eee/999?text=Image+6:+Wide+Banner+(1600x400)" alt="배너 이미지">
    </div>

    <section class="section-padding cafe-section">
        <div class="collage-text-area text-center">
            <h2>03. 소제목 입력</h2>
            <span class="cafe-location">Location Name</span>
            <p class="cafe-description intro-text">내용을 입력하세요. 시부야의 번잡함에서 조금 벗어난 언덕길에 위치한 현대적인 카페입니다.</p>
        </div>
        <div class="grid-collage">
            <div class="collage-tall">
                <img src="https://placehold.co/500x600/eee/999?text=Image+7:+Vertical+Tall+(500x600)" alt="세로 이미지">
            </div>
            <div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/eee/999?text=Image+8:+Stack+Top+(500x285)" alt="스택 상단">
                </div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/eee/999?text=Image+9:+Stack+Bottom+(500x285)" alt="스택 하단">
                </div>
            </div>
        </div>
    </section>
</div>
HTML
];
// ===================================================================
// [템플릿 1] 매거진 스타일 (Magazine)
// ===================================================================
$editor_templates[] = [
    'title' => '01. 매거진 스타일 (Magazine)',
    'description' => '감성적인 텍스트와 다양한 이미지 배치가 어우러진 잡지 스타일입니다.',
    'content' => <<<HTML
<div class="custom-project-layout">
    <header class="hero-header text-center">
        <span class="cafe-location">Lifestyle / Space</span>
        <h1 class="hero-title">공간이 건네는 위로:<br>도쿄의 숨겨진 틈새를 찾아서</h1>
        <p class="hero-subtitle">번잡한 도시의 소음 뒤에 숨겨진, 고요하고 아늑한 시간을 선물하는 세 곳의 공간을 소개합니다.</p>
        <div class="hero-image-frame">
            <img src="https://placehold.co/1000x500/eee/999?text=Main+Visual+(1000x500)" alt="메인 이미지">
        </div>
    </header>
    
    <section class="section-padding intro-text text-center">
        <p>도쿄는 끊임없이 움직이는 거대한 유기체와 같습니다. 수많은 인파와 빽빽한 빌딩 숲 사이에서 우리는 때로 잠시 '멈춤' 버튼을 누를 공간이 절실해집니다. 제가 이번 여행에서 발견한 이 장소들은 단순한 카페나 상업 공간 이상의 의미를 가집니다. 주인의 확고한 철학이 담긴 인테리어, 창밖으로 보이는 계절의 미세한 변화, 그리고 정성스럽게 내려진 커피 한 잔의 여유가 있습니다. 마치 오래된 잡지를 한 장씩 넘기듯, 편안한 마음으로 도쿄의 감성적인 틈새들을 만나보시길 바랍니다.</p>
    </section>

    <section class="section-padding cafe-section">
        <div class="grid-feature">
            <div class="feature-main-img">
                <img src="https://placehold.co/600x500/eee/999?text=Feature+Image+(600x500)" alt="특징 이미지">
            </div>
            <div>
                <h2>01. 빛과 그림자의 조화</h2>
                <span class="cafe-location">Omotesando, Tokyo</span>
                <p class="cafe-description mb-4" style="text-align: left;">
                    이곳의 가장 큰 특징은 시간의 흐름에 따라 변화하는 빛의 설계입니다. 오전에는 부드러운 자연광이 공간 깊숙이 스며들어 따뜻함을 주고, 오후가 되면 길게 늘어지는 그림자가 공간에 입체감을 더합니다. 미니멀리즘의 극치를 보여주는 가구 배치와 여백의 미는 방문객으로 하여금 오로지 '지금 이 순간'에 집중하게 만듭니다.
                </p>
                <div class="feature-detail-img">
                    <img src="https://placehold.co/400x250/eee/999?text=Detail+Shot+(400x250)" alt="상세 이미지">
                </div>
            </div>
        </div>
    </section>

    <div class="interlude-banner">
        <img src="https://placehold.co/1600x400/333/fff?text=Mood+Break+Banner+(1600x400)" alt="분위기 배너">
    </div>

    <section class="section-padding cafe-section">
        <div class="collage-text-area text-center">
            <h2>02. 오래된 것들의 가치</h2>
            <span class="cafe-location">Nakameguro, Tokyo</span>
            <p class="cafe-description intro-text">
                두 번째로 소개할 곳은 50년 된 목조 주택을 개조한 공간입니다. 낡은 나무 바닥이 내는 기분 좋은 삐걱거림, 손때 묻은 빈티지 가구들이 어우러져 마치 시간이 멈춘 듯한 착각을 불러일으킵니다. 화려함보다는 익숙함과 편안함을 추구하는 이곳의 철학은 바쁜 현대인들에게 진정한 휴식이 무엇인지를 다시금 생각하게 합니다.
            </p>
        </div>
        <div class="grid-collage">
            <div class="collage-tall">
                <img src="https://placehold.co/500x600/eee/999?text=Vertical+Mood+(500x600)" alt="세로 긴 이미지">
            </div>
            <div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/eee/999?text=Atmosphere+1+(500x285)" alt="분위기 1">
                </div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/eee/999?text=Atmosphere+2+(500x285)" alt="분위기 2">
                </div>
            </div>
        </div>
    </section>
</div>
HTML
];

// ===================================================================
// [템플릿 2] 미니멀 케이스 스터디 (Minimal Project)
// ===================================================================
$editor_templates[] = [
    'title' => '02. 미니멀 케이스 스터디 (Project)',
    'description' => '프로젝트의 목표와 해결 과정을 논리적으로 보여주는 깔끔한 레이아웃입니다.',
    'content' => <<<HTML
<div class="custom-project-layout">
    <header class="hero-header text-center" style="padding-bottom: 20px !important;">
        <span class="cafe-location">BRAND IDENTITY / UX DESIGN</span>
        <h1 class="hero-title" style="margin-bottom: 10px;">Rebranding Project: ORIGIN</h1>
        <div style="width: 50px; height: 2px; background: #333; margin: 20px auto;"></div>
        <p class="hero-subtitle" style="font-style: normal; font-size: 1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
            기존 브랜드가 가진 고유의 가치는 보존하면서, 디지털 환경에 최적화된 새로운 시각적 언어를 구축하는 것을 목표로 했습니다.
        </p>
    </header>

    <div class="interlude-banner" style="margin-top: 40px !important;">
        <img src="https://placehold.co/1600x600/222/fff?text=Project+Hero+Image+(1600x600)" alt="프로젝트 대표 이미지">
    </div>

    <section class="section-padding cafe-section">
        <div class="grid-gallery">
            <div>
                <h3 style="font-size: 1.5rem; margin-bottom: 15px;">The Challenge</h3>
                <p class="cafe-description" style="text-align: left;">
                    클라이언트는 오프라인 시장에서의 강력한 인지도를 가지고 있었으나, 모바일 중심의 디지털 환경에서는 그 매력을 충분히 전달하지 못하고 있었습니다. 복잡한 로고 시스템과 일관성 없는 컬러 팔레트는 사용자 경험을 저해하는 주요 요인이었습니다. 우리의 과제는 브랜드의 핵심 유산을 해치지 않으면서도, MZ세대에게 어필할 수 있는 현대적이고 유연한 아이덴티티를 정립하는 것이었습니다.
                </p>
            </div>
            <div>
                <h3 style="font-size: 1.5rem; margin-bottom: 15px;">The Solution</h3>
                <p class="cafe-description" style="text-align: left;">
                    우리는 '본질(Origin)'이라는 키워드에 집중했습니다. 불필요한 장식을 걷어내고 가장 기본적인 조형 요소만을 남겨 로고를 재설계했습니다. 또한, 디지털 스크린에서의 가독성을 최우선으로 고려한 타이포그래피 시스템을 도입하고, 다크 모드와 라이트 모드에 모두 대응할 수 있는 확장성 있는 컬러 시스템을 구축했습니다. 결과적으로 온-오프라인을 아우르는 일관된 브랜드 경험을 완성했습니다.
                </p>
            </div>
        </div>
    </section>

    <section class="section-padding cafe-section">
        <div class="collage-text-area text-center">
            <h2>Design System Details</h2>
            <p class="cafe-description intro-text" style="max-width: 600px; margin: 0 auto;">
                단순한 시각적 개선을 넘어, 브랜드가 지속 가능하게 성장할 수 있는 체계적인 디자인 가이드를 수립했습니다.
            </p>
        </div>
        <div class="grid-feature">
            <div class="feature-main-img">
                <img src="https://placehold.co/600x600/eee/999?text=Mockup+View+(600x600)" alt="디테일 컷">
            </div>
            <div style="text-align: left;">
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">01. Modular Grid System</h3>
                <p class="cafe-description" style="text-align: left; margin-bottom: 30px;">
                    다양한 매체 환경에 유연하게 대응할 수 있는 모듈형 그리드 시스템을 개발했습니다. 이는 웹사이트, 앱, 인쇄물 등 어떤 포맷에서도 브랜드의 일관성을 유지해줍니다.
                </p>
                <h3 style="font-size: 1.2rem; margin-bottom: 10px;">02. Typography & Color</h3>
                <p class="cafe-description" style="text-align: left;">
                    가독성이 뛰어난 산세리프 서체와 브랜드의 신뢰감을 상징하는 딥 블루 컬러를 메인으로 선정했습니다. 보조 컬러들은 기능적인 역할(강조, 알림 등)을 명확히 수행하도록 설계되었습니다.
                </p>
            </div>
        </div>
    </section>

    <div class="interlude-banner">
        <img src="https://placehold.co/1600x600/333/fff?text=Final+Result+Showcase+(1600x600)" alt="최종 결과물">
    </div>
</div>
HTML
];

// ===================================================================
// [템플릿 3] 시네마틱 비주얼 (Visual)
// ===================================================================
$editor_templates[] = [
    'title' => '03. 시네마틱 비주얼 (Visual)',
    'description' => '텍스트는 최소화하고 압도적인 이미지로 승부하는 영상/사진 포트폴리오용입니다.',
    'content' => <<<HTML
<div class="custom-project-layout">
    <div class="interlude-banner" style="margin-top: 0 !important; height: 600px !important;">
        <img src="https://placehold.co/1600x600/000/fff?text=Cinematic+Title+Shot" alt="시네마틱 메인">
    </div>

    <section class="section-padding intro-text text-center">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">"The Sound of Silence"</h2>
        <p style="color: #666; max-width: 600px; margin: 0 auto; line-height: 1.8;">
            도심 속 고요함을 주제로 한 이번 필름 프로젝트는, 소음이 제거된 도시의 풍경이 얼마나 낯설고도 아름다운지를 탐구합니다. 대사를 배제하고 오로지 앰비언스 사운드와 영상미만으로 서사를 이끌어가는 실험적인 방식을 채택했습니다.
        </p>
    </section>

    <section class="section-padding cafe-section">
        <div style="display: flex; flex-direction: column; gap: 40px;">
            <div class="hero-image-frame" style="height: 500px;">
                <img src="https://placehold.co/1000x500/111/666?text=Scene+01:+Opening" alt="장면 1">
            </div>
            <div class="grid-gallery">
                <div class="gallery-img-frame">
                    <img src="https://placehold.co/500x400/222/777?text=Scene+02:+Close+up" alt="장면 2">
                </div>
                <div class="gallery-img-frame">
                    <img src="https://placehold.co/500x400/222/777?text=Scene+03:+Emotion" alt="장면 3">
                </div>
            </div>
            <div class="hero-image-frame" style="height: 500px;">
                <img src="https://placehold.co/1000x500/111/666?text=Scene+04:+Highlight+Shot" alt="장면 4">
            </div>
        </div>
    </section>

    <section class="section-padding cafe-section text-center" style="background-color: #f0f0f0; margin-top: 60px;">
        <h3 style="font-size: 1.2rem; margin-bottom: 20px;">BEHIND THE SCENE</h3>
        <p class="cafe-description" style="max-width: 600px; margin: 0 auto 40px auto; text-align: center !important;">
            새벽 4시의 차가운 공기와 스태프들의 열정이 만들어낸 현장의 기록들입니다.
        </p>
        <div class="grid-collage">
            <div class="collage-tall">
                <img src="https://placehold.co/500x600/ccc/555?text=Director+Note" alt="비하인드 1">
            </div>
            <div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/ccc/555?text=Camera+Set" alt="비하인드 2">
                </div>
                <div class="collage-stack-img">
                    <img src="https://placehold.co/500x285/ccc/555?text=Staff+Meeting" alt="비하인드 3">
                </div>
            </div>
        </div>
    </section>
</div>
HTML
];

// ===================================================================
// [템플릿 4] 비하인드 & 프로세스 (Process) - 제작 과정, 단계별 설명(Step by Step)에 최적화
// ===================================================================
$editor_templates[] = [
    'title' => '04. 비하인드 & 프로세스 (Process)',
    'description' => '작업 과정과 기술적 디테일을 단계별로 보여주기 좋은 레이아웃입니다.',
    'content' => <<<HTML
<div class="custom-project-layout">
    <header class="hero-header text-center">
        <span class="cafe-location">WORKFLOW & BEHIND</span>
        <h1 class="hero-title">Making Film: Production Process</h1>
        <p class="hero-subtitle">아이디어 기획부터 최종 렌더링까지, 프로젝트가 완성되는 치열한 과정을 공개합니다.</p>
        <div class="hero-image-frame">
            <img src="https://placehold.co/1000x500/333/999?text=Process+Main+Image" alt="프로세스 메인">
        </div>
    </header>

    <section class="section-padding cafe-section">
        <div class="collage-text-area text-center">
            <h2>The Workflow</h2>
            <p class="cafe-description intro-text">우리는 효율적이고 창의적인 결과물을 위해 체계적인 4단계 파이프라인을 구축했습니다.</p>
        </div>

        <div class="grid-gallery" style="margin-bottom: 40px;">
            <div>
                <div class="gallery-img-frame" style="height: 300px; margin-bottom: 15px;">
                    <img src="https://placehold.co/500x300/eee/999?text=STEP+01:+Planning" alt="기획 단계">
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px;">01. Pre-Production</h3>
                <p class="cafe-description" style="text-align: left; font-size: 0.9rem;">
                    철저한 레퍼런스 분석과 스토리보드 작업을 통해 영상의 전체적인 톤앤매너를 결정합니다. 이 단계에서 모든 샷의 구도를 확정합니다.
                </p>
            </div>
            <div>
                <div class="gallery-img-frame" style="height: 300px; margin-bottom: 15px;">
                    <img src="https://placehold.co/500x300/eee/999?text=STEP+02:+Shooting" alt="촬영 단계">
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px;">02. Production</h3>
                <p class="cafe-description" style="text-align: left; font-size: 0.9rem;">
                    아리 알렉사(ARRI Alexa) 카메라와 아나모픽 렌즈를 사용하여 시네마틱한 룩을 구현했습니다. 조명은 자연광을 모사하여 리얼리티를 살렸습니다.
                </p>
            </div>
        </div>

        <div class="grid-gallery">
            <div>
                <div class="gallery-img-frame" style="height: 300px; margin-bottom: 15px;">
                    <img src="https://placehold.co/500x300/eee/999?text=STEP+03:+Editing" alt="편집 단계">
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px;">03. Post-Production</h3>
                <p class="cafe-description" style="text-align: left; font-size: 0.9rem;">
                    컷 편집과 함께 VFX 작업을 병행합니다. 다빈치 리졸브를 활용한 컬러 그레이딩으로 독보적인 색감을 완성합니다.
                </p>
            </div>
            <div>
                <div class="gallery-img-frame" style="height: 300px; margin-bottom: 15px;">
                    <img src="https://placehold.co/500x300/eee/999?text=STEP+04:+Sound" alt="사운드 단계">
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 5px;">04. Sound Design</h3>
                <p class="cafe-description" style="text-align: left; font-size: 0.9rem;">
                    영상에 생명력을 불어넣는 마지막 단계입니다. 현장음을 레이어링하고, 감정을 고조시키는 오리지널 스코어를 작곡하여 입힙니다.
                </p>
            </div>
        </div>
    </section>

    <div class="inner-wrap" style="background-color: #f0f0f0; margin-top: 60px; padding: 40px;">
        <h3 style="font-size: 1.2rem; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">Technical Specifications</h3>
        <ul style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <li><strong>Camera:</strong> ARRI Alexa Mini LF</li>
            <li><strong>Lens:</strong> Cooke Anamorphic /i SF</li>
            <li><strong>Resolution:</strong> 4K ProRes 4444</li>
            <li><strong>Software:</strong> Premiere Pro, After Effects, DaVinci Resolve</li>
        </ul>
    </div>
</div>
HTML
];

// ===================================================================
// [템플릿 5] 인터뷰 & 에디토리얼 (Interview) - 인물 중심, Q&A 형식, 긴 글을 읽기 좋게 배치
// ===================================================================
$editor_templates[] = [
    'title' => '05. 인터뷰 & 에디토리얼 (Interview)',
    'description' => '인물 인터뷰나 Q&A 콘텐츠에 적합한 텍스트 중심의 감각적인 레이아웃입니다.',
    'content' => <<<HTML
<div class="custom-project-layout">
    <section class="section-padding cafe-section">
        <div class="grid-feature">
            <div class="feature-main-img" style="height: 600px;">
                <img src="https://placehold.co/600x800/222/fff?text=Portrait+Shot+(600x800)" alt="인물 사진">
            </div>
            
            <div style="padding-left: 20px;">
                <span class="cafe-location" style="color: #888; letter-spacing: 2px;">DIRECTOR INTERVIEW</span>
                <h1 style="font-size: 2.5rem; margin-bottom: 30px; line-height: 1.2;">"좋은 디자인은<br>설명하지 않아도<br>느껴지는 것이다."</h1>
                <p class="cafe-description" style="text-align: left; font-style: italic; color: #666; border-left: 3px solid #000; padding-left: 15px;">
                    크리에이티브 디렉터 <strong>김그리프</strong>는 지난 10년간 묵묵히 시각 언어의 본질을 탐구해왔다. 그가 생각하는 '지속 가능한 디자인'에 대한 이야기를 들어보았다.
                </p>
            </div>
        </div>
    </section>

    <section class="section-padding cafe-section" style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 40px;">
            <h3 style="font-size: 1.2rem; color: #000; margin-bottom: 10px;">Q. 이번 프로젝트에서 가장 중점을 둔 부분은 무엇인가요?</h3>
            <p class="cafe-description" style="text-align: left; line-height: 1.8;">
                가장 큰 고민은 '균형'이었습니다. 브랜드가 가진 전통적인 가치를 지키면서도, 새로운 세대에게 신선하게 다가갈 수 있는 접점을 찾는 것이 중요했죠. 우리는 화려한 기교보다는 기본에 충실한 타이포그래피와 여백을 활용해 그 해답을 찾았습니다. 덜어낼수록 본질은 더 명확해지니까요.
            </p>
        </div>

        <div style="margin-bottom: 40px;">
            <h3 style="font-size: 1.2rem; color: #000; margin-bottom: 10px;">Q. 영감은 주로 어디서 얻으시는지 궁금합니다.</h3>
            <p class="cafe-description" style="text-align: left; line-height: 1.8;">
                아이러니하게도 디자인과 관련 없는 곳에서 영감을 많이 받습니다. 주말 아침의 한적한 카페, 오래된 서점의 책 냄새, 혹은 길가에 핀 이름 모를 꽃의 색감 같은 것들이요. 일상 속의 작은 관찰들이 모여 결국 큰 아이디어의 씨앗이 됩니다.
            </p>
        </div>

        <div style="background-color: #f9f9f9; padding: 40px; margin: 40px 0; text-align: center;">
            <p style="font-family: 'Playfair Display', serif; font-size: 1.5rem; line-height: 1.6; color: #333;">
                "결국 디자인은 사람을 향해야 합니다.<br>우리가 만드는 모든 결과물이 누군가의 삶에<br>긍정적인 파동을 일으키길 바랍니다."
            </p>
        </div>

        <div style="margin-bottom: 40px;">
            <h3 style="font-size: 1.2rem; color: #000; margin-bottom: 10px;">Q. 앞으로의 계획이 있다면?</h3>
            <p class="cafe-description" style="text-align: left; line-height: 1.8;">
                장르의 경계를 허무는 작업을 계속하고 싶습니다. 영상과 그래픽, 공간과 디지털을 넘나들며 그리프 스튜디오만의 고유한 서사를 만들어가는 것이 목표입니다. 다음 달에 공개될 인터랙티브 전시 프로젝트도 그 일환이니 많은 기대 부탁드립니다.
            </p>
        </div>
    </section>

    <section class="section-padding cafe-section">
        <div class="grid-gallery">
            <div class="gallery-img-frame">
                <img src="https://placehold.co/500x400/eee/999?text=Work+Process+1" alt="작업 과정 1">
            </div>
            <div class="gallery-img-frame">
                <img src="https://placehold.co/500x400/eee/999?text=Work+Process+2" alt="작업 과정 2">
            </div>
        </div>
    </section>
</div>
HTML
];
?>