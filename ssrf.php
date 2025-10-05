<?php
// 第一层重定向
$url = $_REQUEST['url'];
header('Location: https://ssrf-virid.vercel.app//final.php?path=' . urlencode($url));
?>
