<?php
session_start();

function safe_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $url);
}

function get_proxied_page($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] ?? "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
        CURLOPT_HTTPHEADER => [
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-US,en;q=0.9",
            "Referer: https://google.com"
        ],
        CURLOPT_COOKIEFILE => __DIR__ . "/cookies.txt",
        CURLOPT_COOKIEJAR => __DIR__ . "/cookies.txt",
        CURLOPT_ENCODING => "",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $output = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return $output ?: "<h2>❌ Error:</h2><pre>$error</pre>";
}

function render_ui() {
    $history = $_SESSION['history'] ?? [];
    $history_list = '';
    foreach (array_reverse($history) as $h) {
        $url_escaped = htmlspecialchars($h);
        $history_list .= "<li><a href='?url=$url_escaped' target='_blank'>$url_escaped</a></li>";
    }

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Advanced Proxy</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
  <style>
    body { background:#0e0e0e; color:#eee; font-family:'Inter', sans-serif; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:30px; }
    h1 { font-size:32px; margin-bottom:10px; }
    input { width:500px; padding:12px; border-radius:10px; border:none; margin-bottom:10px; font-size:16px; }
    button { padding:12px 25px; border:none; border-radius:10px; background:#00cc88; color:white; font-size:16px; cursor:pointer; }
    ul { margin-top:20px; padding-left:0; list-style:none; }
    li { margin:5px 0; }
    a { color:#4fc3f7; text-decoration:none; }
    a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <h1>🔐 Double Helix Proxy Portal</h1>
  <form method="get">
    <input name="url" placeholder="https://example.com" required>
    <br>
    <button type="submit">Go Securely</button>
  </form>
  <h3>🕓 Recent URLs</h3>
  <ul>$history_list</ul>
</body>
</html>
HTML;
}

if (isset($_GET['url']) && safe_url($_GET['url'])) {
    $_SESSION['history'][] = $_GET['url'];
    $_SESSION['history'] = array_slice(array_unique($_SESSION['history']), -10); // Keep last 10 unique
    echo get_proxied_page($_GET['url']);
} else {
    render_ui();
}
