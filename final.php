<?php
// 第二层重定向
$path = $_REQUEST['path'];
header('Location: file://' . $path);
?>
