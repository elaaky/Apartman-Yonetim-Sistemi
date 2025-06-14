<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // YENİ YÖNETİCİ EKLEME
    if ($_POST['action'] == 'add') {
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $kullanici_adi = trim($_POST['kullanici_adi']);
        $email = trim($_POST['email']);
        $telefon = trim($_POST['telefon']);
        $sifre = $_POST['sifre'];

        if (empty($ad) || empty($soyad) || empty($kullanici_adi) || empty($sifre)) {
            header('Location: yoneticiler.php?error=empty');
            exit();
        }

        // Şifreyi güvenli bir şekilde hash'le
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);

        try {
            // sp_YoneticiEkle prosedürünü kullanıyoruz
            $sql = "CALL sp_YoneticiEkle(:ad, :soyad, :kullanici_adi, :sifre, :email, :telefon)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':ad' => $ad,
                ':soyad' => $soyad,
                ':kullanici_adi' => $kullanici_adi,
                ':sifre' => $hashed_sifre, // Hash'lenmiş şifreyi gönder
                ':email' => $email,
                ':telefon' => $telefon
            ]);
            header('Location: yoneticiler.php?status=added');
            exit();
        } catch (PDOException $e) {
            // Kullanıcı adı veya email zaten varsa veritabanı unique kısıtlaması hatası verir.
            if ($e->getCode() == 23000) {
                header('Location: yoneticiler.php?error=userexists');
            } else {
                die("Yönetici ekleme hatası: " . $e->getMessage());
            }
        }
    }

    // YÖNETİCİ GÜNCELLEME
    if ($_POST['action'] == 'update') {
        $yonetici_id = $_POST['yonetici_id'];
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $kullanici_adi = trim($_POST['kullanici_adi']);
        $email = trim($_POST['email']);
        $telefon = trim($_POST['telefon']);
        $aktif_mi = isset($_POST['aktif_mi']) ? 1 : 0;

        if (empty($yonetici_id) || empty($ad) || empty($soyad) || empty($kullanici_adi)) {
            header('Location: yoneticiler.php?error=emptyupdate');
            exit();
        }

        // Kendi hesabını pasif yapmasını engelle
        if ($yonetici_id == $_SESSION['yonetici_id'] && $aktif_mi == 0) {
            header('Location: yoneticiler.php?error=selfdeactivate');
            exit();
        }

        try {
            // sp_YoneticiGuncelle prosedürünü kullanıyoruz
            $sql = "CALL sp_YoneticiGuncelle(:id, :ad, :soyad, :email, :telefon, :aktif_mi)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $yonetici_id,
                ':ad' => $ad,
                ':soyad' => $soyad,
                ':email' => $email,
                ':telefon' => $telefon,
                ':aktif_mi' => $aktif_mi
            ]);
            header('Location: yoneticiler.php?status=updated');
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                header('Location: yoneticiler.php?error=userexists');
            } else {
                die("Yönetici güncelleme hatası: " . $e->getMessage());
            }
        }
    }

    // YÖNETİCİ SİLME
    if ($_POST['action'] == 'delete') {
        $yonetici_id = $_POST['yonetici_id'];

        // Kendi kendini silmeyi engelle
        if (empty($yonetici_id) || $yonetici_id == $_SESSION['yonetici_id']) {
            header('Location: yoneticiler.php?error=selfdelete');
            exit();
        }

        try {
            // Stored Procedure 'sp_YoneticiSil' :id parametresini bekliyor.
            $sql = "CALL sp_YoneticiSil(:id)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $yonetici_id, PDO::PARAM_INT);
            $stmt->execute();

            header('Location: yoneticiler.php?status=deleted');
            exit();
        } catch (PDOException $e) {
            // Bu yönetici başka tablolarda (giderler, duyurular) kullanılıyorsa,
            // yabancı anahtar kısıtlaması nedeniyle silme işlemi başarısız olur.
            header('Location: yoneticiler.php?error=deletefailed_inuse');
            exit();
        }
    }
}
