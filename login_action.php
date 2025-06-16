<?php
// Oturumu başlat
session_start();

// Veritabanı bağlantısını dahil et
require_once 'includes/db_connect.php';

// Formdan gelen verileri al
// POST metodu ile gönderilip gönderilmediğini kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    // Kullanıcı adının boş olup olmadığını kontrol et
    if (empty($kullanici_adi) || empty($sifre)) {
        // Hata mesajı ile login sayfasına geri yönlendir
        header("Location: login.php?error=emptyfields");
        exit();
    }

    try {
        // SQL Injection'a karşı korumalı sorgu hazırla
        $sql = "SELECT * FROM Yöneticiler WHERE KullaniciAdi = :kullanici_adi";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':kullanici_adi', $kullanici_adi);

        // Sorguyu çalıştır
        $stmt->execute();

        // Kullanıcıyı bulduysak
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ŞİFRE KONTROLÜ
            // Normalde burada password_verify() kullanılmalı. 
            // Şimdilik veritabanındaki düz metin ile karşılaştırıyoruz.
            if (password_verify($sifre, $user['Sifre'])) {
                // Şifre doğru, oturum değişkenlerini ayarla
                $_SESSION['yonetici_id'] = $user['YoneticiID'];
                $_SESSION['yonetici_adsoyad'] = $user['Ad'] . ' ' . $user['Soyad'];

                // Başarılı giriş, ana panele yönlendir
                header("Location: index.php?login=success");
                exit();
            } else {
                // Şifre yanlış
                header("Location: login.php?error=wrongpassword");
                exit();
            }
        } else {
            // Kullanıcı bulunamadı
            header("Location: login.php?error=nouser");
            exit();
        }
    } catch (PDOException $e) {
        die("Sorgu hatası: " . $e->getMessage());
    }
} else {
    // Doğrudan bu sayfaya erişilirse, ana sayfaya yönlendir
    header("Location: index.php");
    exit();
}