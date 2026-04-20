<?php
define('NAKAKO_CONTACT', true);
require_once __DIR__ . '/_lib.php';
session_start();

// POST以外はトップへ
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact/');
    exit;
}

// CSRFトークン検証
$token = $_POST['token'] ?? '';
if (
    empty($_SESSION['contact_token']) ||
    empty($token) ||
    !hash_equals($_SESSION['contact_token'], $token)
) {
    header('Location: /contact/');
    exit;
}

// セッションデータ検証
if (empty($_SESSION['contact_data'])) {
    header('Location: /contact/');
    exit;
}

$data = $_SESSION['contact_data'];

// メール送信
send_contact_mail($data);

// セッションクリア
unset($_SESSION['contact_data'], $_SESSION['contact_token']);

header('Location: /contact/thanks/');
exit;
