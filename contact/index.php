<?php
define('NAKAKO_CONTACT', true);
require_once __DIR__ . '/_lib.php';
session_start();

// 確認画面から「修正する」で戻った場合にセッションから値を復元
$saved = $_SESSION['contact_data'] ?? [];

function val_index(string $name): string {
    global $saved;
    return h($saved[$name] ?? '');
}

function selected_index(string $name, string $value): string {
    global $saved;
    return ($saved[$name] ?? '') === $value ? ' selected' : '';
}

$title      = 'お問い合わせ';
$body_class = 'page-contact';
require_once __DIR__ . '/_parts/header.php';
?>

<main id="main" class="main main--sub main--contact">
  <header class="page-hero page-hero--blue page-hero--short page-hero--contact">
    <div class="page-hero__inner page-hero__inner--breadcrumb">
      <nav class="breadcrumb" aria-label="パンくずリスト">
        <ol class="breadcrumb__list">
          <li><a href="/">トップ</a></li>
          <li aria-current="page">お問い合わせ</li>
        </ol>
      </nav>
      <h1 class="page-hero__title">お問い合わせ</h1>
      <p class="page-hero__en">Contact Us</p>
    </div>
  </header>

  <section class="contact-lead">
    <div class="contact-lead__inner">
      <h2 class="page-title page-title--blue">粉体受託加工へのお問い合わせ</h2>
      <p>こちらは、粉体受託加工へのお問い合わせとなります。<br>それ以外のお問い合わせについては、各ページに記載されている事業部・工場へ直接お問い合わせください。</p>
      <p>粉体受託加工へのお問い合わせの方は、下記の入力フォームに必要な情報をご入力ください。</p>
    </div>
  </section>

  <form class="contact-form" action="/renew/contact/confirm/" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <section class="form-section">
      <h3 class="title--outline">お客様情報</h3>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="company">貴社名<span class="req">必須</span></label>
          <input class="form-field__input" type="text" id="company" name="company" value="<?= val_index('company') ?>" placeholder="例）株式会社ナカコー" required autocomplete="organization">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="dept">部署名・役職名<span class="req">必須</span></label>
          <input class="form-field__input" type="text" id="dept" name="dept" value="<?= val_index('dept') ?>" placeholder="例）営業企画部" required>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="name">ご担当者名<span class="req">必須</span></label>
          <input class="form-field__input" type="text" id="name" name="name" value="<?= val_index('name') ?>" placeholder="例）田中 太郎" required autocomplete="name">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="email">メールアドレス<span class="req">必須</span></label>
          <input class="form-field__input" type="email" id="email" name="email" value="<?= val_index('email') ?>" placeholder="例）nakako-digital@nakako.co.jp" required autocomplete="email">
        </div>
        <div class="form-field form-field--inline">
          <label class="form-field__label" for="zip">郵便番号<span class="req">必須</span></label>
          <div class="form-field__row">
            <input class="form-field__input form-field__input--short" type="text" id="zip" name="zip" value="<?= val_index('zip') ?>" inputmode="numeric" placeholder="6510083" required>
            <button class="form-field__btn" type="button">住所検索</button>
            <span class="form-field__hint">（ハイフンなし）</span>
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="pref">ご住所（都道府県）<span class="req">必須</span></label>
          <div class="form-field__row">
            <input class="form-field__input form-field__input--short" type="text" id="pref" name="pref" value="<?= val_index('pref') ?>" placeholder="自動入力されます" required>
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="address">ご住所<span class="req">必須</span></label>
          <input class="form-field__input" type="text" id="address" name="address" value="<?= val_index('address') ?>" placeholder="自動入力されます（番地以降はご入力ください）" required autocomplete="street-address">
        </div>
        <div class="form-field form-field--inline">
          <label class="form-field__label" for="tel">TEL<span class="req">必須</span></label>
          <div class="form-field__row">
            <input class="form-field__input form-field__input--short" type="tel" id="tel" name="tel" value="<?= val_index('tel') ?>" placeholder="例）0794439378" required autocomplete="tel">
            <span class="form-field__hint">（ハイフンなし）</span>
          </div>
        </div>
        <div class="form-field form-field--inline">
          <label class="form-field__label" for="fax">FAX</label>
          <div class="form-field__row">
            <input class="form-field__input form-field__input--short" type="text" id="fax" name="fax" value="<?= val_index('fax') ?>" placeholder="例）0794439388" inputmode="numeric">
            <span class="form-field__hint">（ハイフンなし）</span>
          </div>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h3 class="title--outline">粉体受託加工に関する内容</h3>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="topic">お問い合わせ内容<span class="req">必須</span></label>
          <select class="form-field__input form-field__select" id="topic" name="topic" required>
            <option value="">選択してください</option>
            <option value="funsai"<?= selected_index('topic', 'funsai') ?>>粉砕</option>
            <option value="bunkyu"<?= selected_index('topic', 'bunkyu') ?>>分級</option>
            <option value="kongo"<?= selected_index('topic', 'kongo') ?>>混合</option>
            <option value="zouryu"<?= selected_index('topic', 'zouryu') ?>>造粒</option>
            <option value="other"<?= selected_index('topic', 'other') ?>>その他お見積りの有無等</option>
          </select>
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">原料情報</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="m-name">原料名</label>
          <input class="form-field__input" type="text" id="m-name" name="material_name" value="<?= val_index('material_name') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-size">粒度・形状</label>
          <input class="form-field__input" type="text" id="m-size" name="material_size" value="<?= val_index('material_size') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-in">入荷形態</label>
          <input class="form-field__input" type="text" id="m-in" name="material_in" value="<?= val_index('material_in') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-other">その他</label>
          <input class="form-field__input" type="text" id="m-other" name="material_other" value="<?= val_index('material_other') ?>">
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">製品情報</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="p-name">製品名</label>
          <input class="form-field__input" type="text" id="p-name" name="product_name" value="<?= val_index('product_name') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-plan">製品企画</label>
          <input class="form-field__input" type="text" id="p-plan" name="product_plan" value="<?= val_index('product_plan') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-analysis">分析方法</label>
          <input class="form-field__input" type="text" id="p-analysis" name="product_analysis" value="<?= val_index('product_analysis') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-ship">出荷荷姿</label>
          <input class="form-field__input" type="text" id="p-ship" name="product_ship" value="<?= val_index('product_ship') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-useage">製品用途</label>
          <input class="form-field__input" type="text" id="p-useage" name="product_useage" value="<?= val_index('product_useage') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-other">その他</label>
          <input class="form-field__input" type="text" id="p-other" name="product_other" value="<?= val_index('product_other') ?>">
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">その他</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="o-qty">数量・継続性</label>
          <input class="form-field__input" type="text" id="o-qty" name="other_qty" value="<?= val_index('other_qty') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="o-period">加工時期</label>
          <input class="form-field__input" type="text" id="o-period" name="other_period" value="<?= val_index('other_period') ?>">
        </div>
        <div class="form-field">
          <label class="form-field__label" for="o-other">その他</label>
          <input class="form-field__input" type="text" id="o-other" name="other_note" value="<?= val_index('other_note') ?>">
        </div>
      </div>
    </section>

    <section class="form-section">
      <h3 class="title--outline">個人情報保護法への同意</h3>
      <p><a href="/privacy/" target="_blank">個人情報保護法</a>をご覧いただいた上、同意いただける場合はチェックを付けてください。</p>
      <div class="form-field--check">
        <label class="form-check">
          <input type="checkbox" name="privacy" value="1"<?= !empty($saved['privacy']) ? ' checked' : '' ?> required>
          <span class="form-check__content">
            <span class="material-symbols-rounded">check</span>個人情報保護法に同意する
          </span>
        </label>
      </div>
    </section>

    <div class="form-actions">
      <button type="submit" class="btn btn--submit">
        <span class="btn__text">確認画面へ</span>
        <span class="material-symbols-rounded">keyboard_arrow_right</span>
      </button>
    </div>
  </form>
</main>

<?php require_once __DIR__ . '/_parts/footer.php'; ?>
