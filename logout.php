<?php
// Oturumu başlat
session_start();

// Tüm oturum değişkenlerini temizle
$_SESSION = array();

// Oturumu sonlandır
session_destroy();

// Kullanıcıyı login sayfasına yönlendir
header("Location: login.php?logout=success");
exit();
?>