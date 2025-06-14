<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // YENİ GİDER EKLEME
    if ($_POST['action'] == 'add') {
        $yonetici_id = $_SESSION['yonetici_id'];
        $gider_turu = trim($_POST['gider_turu']);
        $tutar = $_POST['tutar'];
        $tarih = $_POST['tarih'];
        $aciklama = trim($_POST['aciklama']);

        if (empty($gider_turu) || empty($tutar) || empty($tarih)) {
            header('Location: giderler.php?error=empty');
            exit();
        }

        try {
            $sql = "INSERT INTO giderler (YoneticiID, GiderTuru, Tutar, Tarih, Aciklama) 
                    VALUES (:yonetici_id, :gider_turu, :tutar, :tarih, :aciklama)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':yonetici_id' => $yonetici_id,
                ':gider_turu' => $gider_turu,
                ':tutar' => $tutar,
                ':tarih' => $tarih,
                ':aciklama' => $aciklama
            ]);
            header('Location: giderler.php?status=added');
            exit();
        } catch (PDOException $e) {
            die("Gider ekleme hatası: " . $e->getMessage());
        }
    }
    
    // GİDER GÜNCELLEME
    if ($_POST['action'] == 'update') {
        $gider_id = $_POST['gider_id'];
        $gider_turu = trim($_POST['gider_turu']);
        $tutar = $_POST['tutar'];
        $tarih = $_POST['tarih'];
        $aciklama = trim($_POST['aciklama']);

        if (empty($gider_id) || empty($gider_turu) || empty($tutar) || empty($tarih)) {
            header('Location: giderler.php?error=emptyupdate');
            exit();
        }

        try {
            $sql = "UPDATE giderler SET GiderTuru = :gider_turu, Tutar = :tutar, Tarih = :tarih, Aciklama = :aciklama 
                    WHERE GiderID = :gider_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':gider_turu' => $gider_turu,
                ':tutar' => $tutar,
                ':tarih' => $tarih,
                ':aciklama' => $aciklama,
                ':gider_id' => $gider_id
            ]);
            header('Location: giderler.php?status=updated');
            exit();
        } catch (PDOException $e) {
            die("Gider güncelleme hatası: " . $e->getMessage());
        }
    }

    // GİDER SİLME
    if ($_POST['action'] == 'delete') {
        $gider_id = $_POST['gider_id'];

        if (empty($gider_id)) {
            header('Location: giderler.php?error=nogiderid');
            exit();
        }

        try {
            $sql = "DELETE FROM giderler WHERE GiderID = :gider_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':gider_id' => $gider_id]);
            header('Location: giderler.php?status=deleted');
            exit();
        } catch (PDOException $e) {
            header('Location: giderler.php?error=deletefailed');
            exit();
        }
    }

} else {
    // Doğrudan erişim engeli
    header('Location: giderler.php');
    exit();
}
?>