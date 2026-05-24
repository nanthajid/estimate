<?php
require_once 'config.php';

$channel_id = $_ENV['LINE_CHANNEL_ID'] ?? '';
$callback_url = $_ENV['LINE_CALLBACK_URL'] ?? '';

if (empty($channel_id) || empty($callback_url)) {
    die("LINE Configuration missing in .env");
}

$state = bin2hex(random_bytes(16));
$_SESSION['line_state'] = $state;

$url = "https://access.line.me/oauth2/v2.1/authorize?" . http_build_query([
    'response_type' => 'code',
    'client_id' => $channel_id,
    'redirect_uri' => $callback_url,
    'state' => $state,
    'scope' => 'profile openid',
    'nonce' => bin2hex(random_bytes(16))
]);

header("Location: $url");
exit;
