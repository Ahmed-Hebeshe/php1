<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $content = @file_get_contents($url);
    echo $content ?: "⚠️ Failed to fetch content.";
} else {
    echo '<form method="get">
        <input name="url" placeholder="Enter URL to proxy" style="width:300px;">
        <button type="submit">Go</button>
    </form>';
}
