<?php
define('NAKAKO_CONTACT', true);
require_once dirname(__DIR__) . '/_lib.php';
session_start();

// セッションにエラーがなければ入力フォームへ
if (empty($_SESSION['contact_errors'])) {
    header('Location: /contact/');
    exit;
}

$errors = $_SESSION['contact_errors'];
$input  = $_SESSION['contact_input'] ?? [];
unset($_SESSION['contact_errors'], $_SESSION['contact_input']);

// エラーがある場合のフィールドクラス・メッセージ出力ヘルパー
function field_error(array $errors, string $name): string {
    return isset($errors[$name]) ? '<p class="form-field__error" id="' . $name . '-error">' . h($errors[$name]) . '</p>' : '<p class="form-field__error"></p>';
}

function field_class(array $errors, string $name, string $base = 'form-field__input'): string {
    return $base . (isset($errors[$name]) ? ' ' . $base . '--error' : '');
}

function wrapper_class(array $errors, string $name, string $base = 'form-field'): string {
    return $base . (isset($errors[$name]) ? ' ' . $base . '--error' : '');
}

function val(array $input, string $name): string {
    return h($input[$name] ?? '');
}

$title      = '入力エラー';
$body_class = 'page-contact-error';
require_once dirname(__DIR__) . '/_parts/header.php';
?>

<main id="main" class="main main--sub main--contact">
  <header class="page-hero page-hero--blue page-hero--short page-hero--contact">
    <div class="page-hero__inner page-hero__inner--breadcrumb">
      <nav class="breadcrumb" aria-label="パンくずリスト">
        <ol class="breadcrumb__list">
          <li><a href="/">トップ</a></li>
          <li><a href="/contact/">お問い合わせ</a></li>
          <li aria-current="page">入力エラー</li>
        </ol>
      </nav>
      <h1 class="page-hero__title">入力エラー</h1>
      <p class="page-hero__en">Contact Us</p>
    </div>
  </header>

  <section class="contact-lead">
    <div class="contact-lead__inner">
      <h2 class="page-title page-title--blue">粉体受託加工へのお問い合わせ</h2>
      <p>こちらは、粉体受託加工へのお問い合わせとなります。<br>それ以外のお問い合わせについては、各ページに記載されている事業部・工場へ直接お問い合わせください。</p>
      <div class="contact-lead__error">
        <p>入力内容に不備があります。エラーが表示されている項目をご確認のうえ、再度ご入力ください。</p>
      </div>
    </div>
  </section>

  <form class="contact-form" action="/renew/contact/confirm/" method="post" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <section class="form-section">
      <h3 class="title--outline">お客様情報</h3>
      <div class="form-grid">

        <div class="<?= wrapper_class($errors, 'company') ?>">
          <label class="form-field__label" for="company">貴社名<span class="req">必須</span></label>
          <div class="form-field__body">
            <input class="<?= field_class($errors, 'company') ?>" type="text" id="company" name="company" value="<?= val($input, 'company') ?>" placeholder="例）株式会社ナカコー" required autocomplete="organization"<?= isset($errors['company']) ? ' aria-invalid="true" aria-describedby="company-error"' : '' ?>>
            <?= field_error($errors, 'company') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'dept') ?>">
          <label class="form-field__label" for="dept">部署名・役職名<span class="req">必須</span></label>
          <div class="form-field__body">
            <input class="<?= field_class($errors, 'dept') ?>" type="text" id="dept" name="dept" value="<?= val($input, 'dept') ?>" placeholder="例）営業企画部" required<?= isset($errors['dept']) ? ' aria-invalid="true" aria-describedby="dept-error"' : '' ?>>
            <?= field_error($errors, 'dept') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'name') ?>">
          <label class="form-field__label" for="name">ご担当者名<span class="req">必須</span></label>
          <div class="form-field__body">
            <input class="<?= field_class($errors, 'name') ?>" type="text" id="name" name="name" value="<?= val($input, 'name') ?>" placeholder="例）田中 太郎" required autocomplete="name"<?= isset($errors['name']) ? ' aria-invalid="true" aria-describedby="name-error"' : '' ?>>
            <?= field_error($errors, 'name') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'email') ?>">
          <label class="form-field__label" for="email">メールアドレス<span class="req">必須</span></label>
          <div class="form-field__body">
            <input class="<?= field_class($errors, 'email') ?>" type="email" id="email" name="email" value="<?= val($input, 'email') ?>" placeholder="例）nakako-digital@nakako.co.jp" required autocomplete="email"<?= isset($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '' ?>>
            <?= field_error($errors, 'email') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'zip') ?> form-field--inline">
          <label class="form-field__label" for="zip">郵便番号<span class="req">必須</span></label>
          <div class="form-field__body">
            <div class="form-field__row">
              <input class="<?= field_class($errors, 'zip') ?> form-field__input--short" type="text" id="zip" name="zip" value="<?= val($input, 'zip') ?>" inputmode="numeric" placeholder="6510083" required<?= isset($errors['zip']) ? ' aria-invalid="true" aria-describedby="zip-error"' : '' ?>>
              <button class="form-field__btn" type="button">住所検索</button>
              <span class="form-field__hint">（ハイフンなし）</span>
            </div>
            <?= field_error($errors, 'zip') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'pref') ?>">
          <label class="form-field__label" for="pref">ご住所（都道府県）<span class="req">必須</span></label>
          <div class="form-field__body">
            <div class="form-field__row">
              <input class="<?= field_class($errors, 'pref') ?> form-field__input--short" type="text" id="pref" name="pref" value="<?= val($input, 'pref') ?>" placeholder="自動入力されます" required<?= isset($errors['pref']) ? ' aria-invalid="true" aria-describedby="pref-error"' : '' ?>>
            </div>
            <?= field_error($errors, 'pref') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'address') ?>">
          <label class="form-field__label" for="address">ご住所<span class="req">必須</span></label>
          <div class="form-field__body">
            <input class="<?= field_class($errors, 'address') ?>" type="text" id="address" name="address" value="<?= val($input, 'address') ?>" placeholder="自動入力されます（番地以降はご入力ください）" required autocomplete="street-address"<?= isset($errors['address']) ? ' aria-invalid="true" aria-describedby="address-error"' : '' ?>>
            <?= field_error($errors, 'address') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'tel') ?> form-field--inline">
          <label class="form-field__label" for="tel">TEL<span class="req">必須</span></label>
          <div class="form-field__body">
            <div class="form-field__row">
              <input class="<?= field_class($errors, 'tel') ?> form-field__input--short" type="tel" id="tel" name="tel" value="<?= val($input, 'tel') ?>" placeholder="例）0794439378" required autocomplete="tel"<?= isset($errors['tel']) ? ' aria-invalid="true" aria-describedby="tel-error"' : '' ?>>
              <span class="form-field__hint">（ハイフンなし）</span>
            </div>
            <?= field_error($errors, 'tel') ?>
          </div>
        </div>

        <div class="<?= wrapper_class($errors, 'fax') ?> form-field--inline">
          <label class="form-field__label" for="fax">FAX</label>
          <div class="form-field__body">
            <div class="form-field__row">
              <input class="<?= field_class($errors, 'fax') ?> form-field__input--short" type="text" id="fax" name="fax" value="<?= val($input, 'fax') ?>" placeholder="例）0794439388" inputmode="numeric"<?= isset($errors['fax']) ? ' aria-invalid="true" aria-describedby="fax-error"' : '' ?>>
              <span class="form-field__hint">（ハイフンなし）</span>
            </div>
            <?= field_error($errors, 'fax') ?>
          </div>
        </div>

      </div>
    </section>

    <section class="form-section">
      <h3 class="title--outline">粉体受託加工に関する内容</h3>
      <div class="form-grid">
        <div class="<?= wrapper_class($errors, 'topic') ?>">
          <label class="form-field__label" for="topic">お問い合わせ内容<span class="req">必須</span></label>
          <div class="form-field__body">
            <select class="<?= field_class($errors, 'topic') ?> form-field__select" id="topic" name="topic" required<?= isset($errors['topic']) ? ' aria-invalid="true" aria-describedby="topic-error"' : '' ?>>
              <option value="">選択してください</option>
              <?php foreach (TOPIC_LABELS as $value => $label): ?>
              <option value="<?= h($value) ?>"<?= ($input['topic'] ?? '') === $value ? ' selected' : '' ?>><?= h($label) ?></option>
              <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'topic') ?>
          </div>
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">原料情報</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="m-name">原料名</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="m-name" name="material_name" value="<?= val($input, 'material_name') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-size">粒度・形状</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="m-size" name="material_size" value="<?= val($input, 'material_size') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-in">入荷形態</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="m-in" name="material_in" value="<?= val($input, 'material_in') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="m-other">その他</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="m-other" name="material_other" value="<?= val($input, 'material_other') ?>">
          </div>
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">製品情報</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="p-name">製品名</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-name" name="product_name" value="<?= val($input, 'product_name') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-plan">製品企画</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-plan" name="product_plan" value="<?= val($input, 'product_plan') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-analysis">分析方法</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-analysis" name="product_analysis" value="<?= val($input, 'product_analysis') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-ship">出荷荷姿</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-ship" name="product_ship" value="<?= val($input, 'product_ship') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-useage">製品用途</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-useage" name="product_useage" value="<?= val($input, 'product_useage') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="p-other">その他</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="p-other" name="product_other" value="<?= val($input, 'product_other') ?>">
          </div>
        </div>
      </div>
    </section>

    <section class="form-section form-section--bar">
      <h2 class="form-section__title-bar">その他</h2>
      <div class="form-grid">
        <div class="form-field">
          <label class="form-field__label" for="o-qty">数量・継続性</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="o-qty" name="other_qty" value="<?= val($input, 'other_qty') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="o-period">加工時期</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="o-period" name="other_period" value="<?= val($input, 'other_period') ?>">
          </div>
        </div>
        <div class="form-field">
          <label class="form-field__label" for="o-other">その他</label>
          <div class="form-field__body">
            <input class="form-field__input" type="text" id="o-other" name="other_note" value="<?= val($input, 'other_note') ?>">
          </div>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h3 class="title--outline">個人情報保護法への同意</h3>
      <p><a href="/privacy/" target="_blank">個人情報保護法</a>をご覧いただいた上、同意いただける場合はチェックを付けてください。</p>
      <div class="<?= wrapper_class($errors, 'privacy', 'form-field--check') ?>">
        <label class="form-check">
          <input type="checkbox" name="privacy" value="1" required<?= isset($errors['privacy']) ? ' aria-invalid="true" aria-describedby="privacy-error"' : '' ?>>
          <span class="form-check__content">
            <span class="material-symbols-rounded">check</span>個人情報保護法に同意する
          </span>
        </label>
        <?php if (isset($errors['privacy'])): ?>
        <p class="form-field__error" id="privacy-error"><?= h($errors['privacy']) ?></p>
        <?php endif; ?>
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

<?php require_once dirname(__DIR__) . '/_parts/footer.php'; ?>
