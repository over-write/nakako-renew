<?php
define('NAKAKO_CONTACT', true);
require_once dirname(__DIR__) . '/_lib.php';
session_start();

// POSTされた場合はバリデーション処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        header('Location: /contact/');
        exit;
    }
    csrf_regenerate();

    $input  = sanitize_input($_POST);
    $errors = validate_contact($input);

    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        $_SESSION['contact_input']  = $input;
        header('Location: /renew/contact/error/');
        exit;
    }

    // バリデーション通過 → セッションに保存してGETリダイレクト（PRGパターン）
    $_SESSION['contact_data']  = $input;
    $_SESSION['contact_token'] = bin2hex(random_bytes(32));
    header('Location: /renew/contact/confirm/');
    exit;
}

// GETアクセス：セッションデータがなければ入力フォームへ
if (empty($_SESSION['contact_data'])) {
    header('Location: /contact/');
    exit;
}

$data  = $_SESSION['contact_data'];
$token = $_SESSION['contact_token'] ?? '';
$topic = topic_label($data['topic']);

$title      = '入力内容の確認';
$body_class = 'page-contact-confirm';
require_once dirname(__DIR__) . '/_parts/header.php';
?>

<main id="main" class="main main--sub main--contact">
  <header class="page-hero page-hero--blue page-hero--short page-hero--contact">
    <div class="page-hero__inner page-hero__inner--breadcrumb">
      <nav class="breadcrumb" aria-label="パンくずリスト">
        <ol class="breadcrumb__list">
          <li><a href="/">トップ</a></li>
          <li><a href="/contact/">お問い合わせ</a></li>
          <li aria-current="page">入力内容の確認</li>
        </ol>
      </nav>
      <h1 class="page-hero__title">入力内容の確認</h1>
      <p class="page-hero__en">Contact Us</p>
    </div>
  </header>

  <section class="contact-lead">
    <div class="contact-lead__inner">
      <h2 class="page-title page-title--blue">送信内容のご確認</h2>
      <p>以下の内容で送信します。内容にお間違いがなければ「送信する」を押してください。<br>修正される場合は「修正する」からお問い合わせフォームに戻ってください。</p>
    </div>
  </section>

  <div class="contact-form contact-form--static">
    <section class="form-section">
      <h3 class="title--outline">お客様情報</h3>
      <dl class="contact-summary">
        <dt>貴社名</dt>
        <dd><?= h($data['company']) ?></dd>
        <dt>部署名・役職名</dt>
        <dd><?= h($data['dept']) ?></dd>
        <dt>ご担当者名</dt>
        <dd><?= h($data['name']) ?></dd>
        <dt>メールアドレス</dt>
        <dd><?= h($data['email']) ?></dd>
        <dt>郵便番号</dt>
        <dd><?= h($data['zip']) ?></dd>
        <dt>ご住所（都道府県）</dt>
        <dd><?= h($data['pref']) ?></dd>
        <dt>ご住所</dt>
        <dd><?= h($data['address']) ?></dd>
        <dt>TEL</dt>
        <dd><?= h($data['tel']) ?></dd>
        <dt>FAX</dt>
        <dd><?= h($data['fax']) ?></dd>
      </dl>
    </section>

    <section class="form-section">
      <h3 class="title--outline">粉体受託加工に関する内容</h3>
      <dl class="contact-summary">
        <dt>お問い合わせ内容</dt>
        <dd><?= h($topic) ?></dd>
      </dl>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">原料情報</h2>
      <dl class="contact-summary">
        <dt>原料名</dt>
        <dd><?= h($data['material_name']) ?></dd>
        <dt>粒度・形状</dt>
        <dd><?= h($data['material_size']) ?></dd>
        <dt>入荷形態</dt>
        <dd><?= h($data['material_in']) ?></dd>
        <dt>その他</dt>
        <dd><?= h($data['material_other']) ?></dd>
      </dl>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">製品情報</h2>
      <dl class="contact-summary">
        <dt>製品名</dt>
        <dd><?= h($data['product_name']) ?></dd>
        <dt>製品企画</dt>
        <dd><?= h($data['product_plan']) ?></dd>
        <dt>分析方法</dt>
        <dd><?= h($data['product_analysis']) ?></dd>
        <dt>出荷荷姿</dt>
        <dd><?= h($data['product_ship']) ?></dd>
        <dt>製品用途</dt>
        <dd><?= h($data['product_useage']) ?></dd>
        <dt>その他</dt>
        <dd><?= h($data['product_other']) ?></dd>
      </dl>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">その他</h2>
      <dl class="contact-summary">
        <dt>数量・継続性</dt>
        <dd><?= h($data['other_qty']) ?></dd>
        <dt>加工時期</dt>
        <dd><?= h($data['other_period']) ?></dd>
        <dt>その他</dt>
        <dd><?= h($data['other_note']) ?></dd>
      </dl>
    </section>

    <section class="form-section">
      <h3 class="title--outline">個人情報保護法への同意</h3>
      <p class="contact-summary__note">個人情報保護法に同意する</p>
    </section>

    <div class="form-actions contact-confirm__actions">
      <a class="btn btn--outline" href="/contact/">
        <span class="material-symbols-rounded">keyboard_arrow_left</span>
        修正する
      </a>
      <form class="contact-confirm__send" method="post" action="/renew/contact/send.php">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <button type="submit" class="btn btn--submit">
          <span class="btn__text">この内容で送信する</span>
          <span class="material-symbols-rounded">keyboard_arrow_right</span>
        </button>
      </form>
    </div>
  </div>
</main>

<?php require_once dirname(__DIR__) . '/_parts/footer.php'; ?>
