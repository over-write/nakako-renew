<?php
if (!defined('NAKAKO_CONTACT')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

const MAIL_TO        = 'sakoda@overwrite.work';
const MAIL_FROM      = 'noreply@nakako.co.jp';
const MAIL_FROM_NAME = '株式会社ナカコー お問い合わせフォーム';

const TOPIC_LABELS = [
    'funsai' => '粉砕',
    'bunkyu' => '分級',
    'kongo'  => '混合',
    'zouryu' => '造粒',
    'other'  => 'その他お見積りの有無等',
];

const FORM_FIELDS = [
    'company', 'dept', 'name', 'email', 'zip', 'pref', 'address', 'tel', 'fax',
    'topic',
    'material_name', 'material_size', 'material_in', 'material_other',
    'product_name', 'product_plan', 'product_analysis', 'product_ship', 'product_useage', 'product_other',
    'other_qty', 'other_period', 'other_note',
];

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_regenerate(): void {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function topic_label(string $value): string {
    return TOPIC_LABELS[$value] ?? '';
}

function sanitize_input(array $post): array {
    $data = [];
    foreach (FORM_FIELDS as $field) {
        $data[$field] = trim($post[$field] ?? '');
    }
    $data['privacy'] = (isset($post['privacy']) && $post['privacy'] === '1') ? '1' : '';
    return $data;
}

function validate_contact(array $data): array {
    $errors = [];

    if ($data['company'] === '') {
        $errors['company'] = '必須項目です。入力してください。';
    }
    if ($data['dept'] === '') {
        $errors['dept'] = '必須項目です。入力してください。';
    }
    if ($data['name'] === '') {
        $errors['name'] = '必須項目です。入力してください。';
    }
    if ($data['email'] === '') {
        $errors['email'] = '必須項目です。入力してください。';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'メールアドレスの形式が正しくありません。';
    }
    if ($data['zip'] === '') {
        $errors['zip'] = '必須項目です。入力してください。';
    } elseif (!preg_match('/^\d{7}$/', $data['zip'])) {
        $errors['zip'] = '郵便番号は7桁の半角数字で入力してください。';
    }
    if ($data['pref'] === '') {
        $errors['pref'] = '必須項目です。入力してください。';
    }
    if ($data['address'] === '') {
        $errors['address'] = '必須項目です。入力してください。';
    }
    if ($data['tel'] === '') {
        $errors['tel'] = '必須項目です。入力してください。';
    } elseif (!preg_match('/^\d+$/', $data['tel'])) {
        $errors['tel'] = '電話番号は半角数字で入力してください。';
    }
    if ($data['fax'] !== '' && !preg_match('/^\d+$/', $data['fax'])) {
        $errors['fax'] = 'FAXは半角数字で入力してください。';
    }
    if ($data['topic'] === '' || !array_key_exists($data['topic'], TOPIC_LABELS)) {
        $errors['topic'] = 'お問い合わせ内容を選択してください。';
    }
    if ($data['privacy'] !== '1') {
        $errors['privacy'] = '必須項目です。チェックを入れてください。';
    }

    return $errors;
}

const LOG_FILE = __DIR__ . '/_logs/mail.log';

function write_mail_log(string $type, string $to, string $subject, bool $result): void {
    $log_dir = dirname(LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0700, true);
    }
    $status = $result ? 'OK' : 'FAIL';
    $line   = implode("\t", [
        date('Y-m-d H:i:s'),
        $status,
        $type,
        $to,
        $subject,
    ]) . "\n";
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

function smtp_send(string $to, string $from_addr, string $from_name, string $subject, string $body, array $extra_headers = []): bool {
    mb_internal_encoding('UTF-8');

    $host = getenv('SMTP_HOST') ?: 'localhost';
    $port = (int)(getenv('SMTP_PORT') ?: 25);

    $fp = @fsockopen($host, $port, $errno, $errstr, 5);
    if (!$fp) {
        error_log("SMTP connect failed [{$host}:{$port}]: {$errstr} ({$errno})");
        return false;
    }
    stream_set_timeout($fp, 5);

    $w = fn(string $s) => fputs($fp, $s . "\r\n");
    $r = fn(): string => (string) fgets($fp, 512);

    $r(); // 220 greeting
    $w('EHLO localhost');
    do { $line = $r(); } while ($line && ($line[3] ?? '') === '-');

    $w("MAIL FROM:<{$from_addr}>");
    $r();
    $w("RCPT TO:<{$to}>");
    $r();

    foreach ($extra_headers as $rcpt) {
        $w("RCPT TO:<{$rcpt}>");
        $r();
    }

    $w('DATA');
    $r(); // 354

    $enc_subject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
    $enc_from    = mb_encode_mimeheader($from_name, 'UTF-8', 'B', "\r\n") . " <{$from_addr}>";

    $header_lines = array_merge([
        "From: {$enc_from}",
        "To: {$to}",
        "Subject: {$enc_subject}",
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'X-Mailer: PHP/smtp',
    ], array_map(fn($k, $v) => "{$k}: {$v}", array_keys($extra_headers), $extra_headers));

    $message = implode("\r\n", $header_lines) . "\r\n\r\n" . chunk_split(base64_encode($body));

    // dot stuffing
    $lines   = explode("\r\n", $message);
    $stuffed = implode("\r\n", array_map(fn($l) => str_starts_with($l, '.') ? '.' . $l : $l, $lines));
    fputs($fp, $stuffed . "\r\n");

    $w('.');
    $result = $r(); // 250

    $w('QUIT');
    fclose($fp);

    return strncmp((string) $result, '250', 3) === 0;
}

function send_contact_mail(array $data): bool {
    mb_internal_encoding('UTF-8');

    $topic = topic_label($data['topic']);

    $company_body = implode("\n", [
        '以下の内容でお問い合わせが届きました。',
        '',
        '■ お客様情報',
        '・貴社名: '           . $data['company'],
        '・部署名・役職名: '   . $data['dept'],
        '・ご担当者名: '       . $data['name'],
        '・メールアドレス: '   . $data['email'],
        '・郵便番号: '         . $data['zip'],
        '・ご住所（都道府県）: ' . $data['pref'],
        '・ご住所: '           . $data['address'],
        '・TEL: '              . $data['tel'],
        '・FAX: '              . $data['fax'],
        '',
        '■ 粉体受託加工に関する内容',
        '・お問い合わせ内容: ' . $topic,
        '',
        '■ 原料情報',
        '・原料名: '     . $data['material_name'],
        '・粒度・形状: ' . $data['material_size'],
        '・入荷形態: '   . $data['material_in'],
        '・その他: '     . $data['material_other'],
        '',
        '■ 製品情報',
        '・製品名: '   . $data['product_name'],
        '・製品企画: ' . $data['product_plan'],
        '・分析方法: ' . $data['product_analysis'],
        '・出荷荷姿: ' . $data['product_ship'],
        '・製品用途: ' . $data['product_useage'],
        '・その他: '   . $data['product_other'],
        '',
        '■ その他',
        '・数量・継続性: ' . $data['other_qty'],
        '・加工時期: '    . $data['other_period'],
        '・その他: '      . $data['other_note'],
    ]);

    $company_subject = '【お問い合わせ】粉体受託加工に関するお問い合わせ';
    $company_result  = smtp_send(MAIL_TO, MAIL_FROM, MAIL_FROM_NAME, $company_subject, $company_body, ['Reply-To' => $data['email']]);
    write_mail_log('company', MAIL_TO, $company_subject, $company_result);

    $user_body = implode("\n", [
        $data['name'] . ' 様',
        '',
        'この度は粉体受託加工にお問い合わせいただき、誠にありがとうございます。',
        '以下の内容でお問い合わせを受け付けました。',
        '内容を確認のうえ、担当より折り返しご連絡いたします。今しばらくお待ちください。',
        '',
        '■ お問い合わせ内容',
        '・お問い合わせ内容: ' . $topic,
        '',
        '■ お客様情報',
        '・貴社名: '         . $data['company'],
        '・部署名・役職名: ' . $data['dept'],
        '・ご担当者名: '     . $data['name'],
        '・メールアドレス: ' . $data['email'],
        '',
        '──────────────────────────',
        '株式会社ナカコー',
        '──────────────────────────',
        '※このメールはシステムからの自動送信です。',
    ]);

    $user_subject = 'お問い合わせを受け付けました【株式会社ナカコー】';
    $user_result  = smtp_send($data['email'], MAIL_FROM, '株式会社ナカコー', $user_subject, $user_body);
    write_mail_log('autoReply', $data['email'], $user_subject, $user_result);

    return $company_result && $user_result;
}
