<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // Yeni Duyuru Ekleme
    if ($_POST['action'] == 'add') {
        $yonetici_id = $_SESSION['yonetici_id'];
        $baslik = trim($_POST['baslik']);
        $icerik = trim($_POST['icerik']);

        if (empty($baslik) || empty($icerik)) {
            header('Location: duyurular.php?error=empty');
            exit();
        }

        try {
            // sp_DuyuruEkle prosedürünü kullanıyoruz (önceden oluşturmuştuk)
            $sql = "CALL sp_DuyuruEkle(:yonetici_id, :baslik, :icerik)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':yonetici_id' => $yonetici_id, ':baslik' => $baslik, ':icerik' => $icerik]);
            header('Location: duyurular.php?status=added');
            exit();
        } catch (PDOException $e) {
            die("Duyuru ekleme hatası: " . $e->getMessage());
        }
    }
    
    // Duyuru Silme
    if ($_POST['action'] == 'delete') {
        $duyuru_id = $_POST['duyuru_id'];
        if (empty($duyuru_id)) {
            header('Location: duyurular.php?error=nodeleteid');
            exit();
        }
        try {
            // sp_DuyuruSil prosedürünü kullanıyoruz
            $sql = "CALL sp_DuyuruSil(:duyuru_id)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':duyuru_id' => $duyuru_id]);
            header('Location: duyurular.php?status=deleted');
            exit();
        } catch (PDOException $e) {
            die("Duyuru silme hatası: " . $e->getMessage());
        }
    }
}
?>