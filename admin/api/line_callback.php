<?php
require_once 'config.php';

$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

if (empty($code) || $state !== ($_SESSION['line_state'] ?? '')) {
    die("Invalid state or missing code.");
}

$channel_id = $_ENV['LINE_CHANNEL_ID'];
$channel_secret = $_ENV['LINE_CHANNEL_SECRET'];
$callback_url = $_ENV['LINE_CALLBACK_URL'];

// 1. Exchange code for access token
$ch = curl_init("https://api.line.me/oauth2/v2.1/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $callback_url,
    'client_id' => $channel_id,
    'client_secret' => $channel_secret
]));
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['access_token'])) {
    die("Failed to get access token: " . ($data['error_description'] ?? 'Unknown error'));
}

$access_token = $data['access_token'];

// 2. Get User Profile
$ch = curl_init("https://api.line.me/v2/profile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
$response = curl_exec($ch);
curl_close($ch);

$profile = json_decode($response, true);
if (!isset($profile['userId'])) {
    die("Failed to get profile.");
}

$line_id = $profile['userId'];
$display_name = $profile['displayName'];
$picture_url = $profile['pictureUrl'] ?? '';

// 3. Check/Add to admins table
$stmt = $pdo->prepare("SELECT * FROM admins WHERE line_id = ?");
$stmt->execute([$line_id]);
$admin = $stmt->fetch();

if (!$admin) {
    // For initial setup, we allow the first user to become admin.
    // Or you might want to restrict this.
    $stmt = $pdo->prepare("INSERT INTO admins (line_id, display_name, picture_url) VALUES (?, ?, ?)");
    $stmt->execute([$line_id, $display_name, $picture_url]);
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE line_id = ?");
    $stmt->execute([$line_id]);
    $admin = $stmt->fetch();
}

// 4. Set Session
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_name'] = $admin['display_name'];
$_SESSION['admin_picture'] = $admin['picture_url'];

header("Location: ../index.php");
exit;
