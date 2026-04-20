<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> | 株式会社ナカコー</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/contact.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,300,0,0" />
</head>
<body class="<?= h($body_class) ?>">

<a class="skip-link" href="#main">本文へスキップ</a>
<header class="site-header" id="site-header">
  <div class="site-header__bar">
    <div class="site-header__inner">
      <a class="site-header__logo" href="/">
        <img src="/assets/img/logo.svg" alt="株式会社ナカコー">
      </a>
      <button type="button" class="site-header__menu-btn" id="menu-toggle" aria-controls="global-nav" aria-expanded="false" aria-label="メニューを開く">
        <span class="site-header__menu-icon" aria-hidden="true"></span>
      </button>
      <div class="site-header__nav-wrap" id="global-nav">
        <button type="button" class="site-header__close" id="menu-close" aria-label="メニューを閉じる">
          <span aria-hidden="true">×</span>
        </button>
        <nav class="gnav" aria-label="グローバルナビゲーション">
          <ul class="gnav__list">
            <li class="gnav__item">
              <a class="gnav__link" href="/about/">私たちについて</a>
            </li>
            <li class="gnav__item gnav__item--has-sub">
              <a class="gnav__link gnav__link--parent" href="/powder-processing/">
                粉体受託加工
                <span class="material-symbols-rounded">arrow_circle_down</span>
              </a>
              <div class="gnav__sub-wrap">
                <ul class="gnav__sub">
                  <li><a class="gnav__sub-link" href="/powder-processing/#about">粉体受託加工について<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#funsai">粉砕加工<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#kongo">混合加工<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#zouryu">造粒加工<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#teion">低温粉砕加工・冷凍粉砕加工<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#bunkyu">分級加工・乾燥加工・その他<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                  <li><a class="gnav__sub-link" href="/powder-processing/#setsubi">粉体加工設備一覧<span class="material-symbols-rounded">keyboard_arrow_right</span></a></li>
                </ul>
              </div>
            </li>
            <li class="gnav__item">
              <a class="gnav__link" href="/steelmaking-auxiliary-materials/">製鋼副資材</a>
            </li>
            <li class="gnav__item">
              <a class="gnav__link" href="/greening-materials/">環境緑化資材</a>
            </li>
          </ul>
        </nav>
        <a class="btn btn--header-contact btn--header-active" href="/contact/">
          <span class="material-symbols-rounded">mail</span>
          お問い合わせ
        </a>
      </div>
    </div>
  </div>
</header>
