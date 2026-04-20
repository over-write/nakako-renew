<?php
define('NAKAKO_CONTACT', true);
require_once dirname(__DIR__) . '/_lib.php';
session_start();

// 送信完了後のセッション残滓をクリア
unset($_SESSION['contact_data'], $_SESSION['contact_token']);

$title      = '送信完了';
$body_class = 'page-contact-thanks';
require_once dirname(__DIR__) . '/_parts/header.php';
?>

<main id="main" class="main main--sub main--contact">
  <header class="page-hero page-hero--blue page-hero--short page-hero--contact">
    <div class="page-hero__inner page-hero__inner--breadcrumb">
      <nav class="breadcrumb" aria-label="パンくずリスト">
        <ol class="breadcrumb__list">
          <li><a href="/">トップ</a></li>
          <li><a href="/contact/">お問い合わせ</a></li>
          <li aria-current="page">送信完了</li>
        </ol>
      </nav>
      <h1 class="page-hero__title">送信完了</h1>
      <p class="page-hero__en">Contact Us</p>
    </div>
  </header>

  <section class="contact-lead">
    <div class="contact-lead__inner">
      <h2 class="page-title page-title--blue">お問い合わせを受け付けました</h2>
      <p>この度は粉体受託加工にお問い合わせいただき、誠にありがとうございます。</p>
      <p>内容を確認のうえ、担当より折り返しご連絡いたします。今しばらくお待ちください。</p>
      <p class="contact-thanks__note">※自動返信メールをお送りしている場合は、受信フォルダをご確認ください。メールが届かない場合は、迷惑メールフォルダもあわせてご確認ください。</p>
    </div>
  </section>

  <div class="contact-form contact-form--static">
    <div class="form-actions contact-thanks__actions">
      <a class="btn btn--outline" href="/">
        トップページへ
        <span class="material-symbols-rounded">keyboard_arrow_right</span>
      </a>
    </div>
  </div>
</main>

<?php require_once dirname(__DIR__) . '/_parts/footer.php'; ?>
