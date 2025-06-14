<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // YENİ SAKİN EKLEME
    if ($_POST['action'] == 'add') {
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $daire_id = $_POST['daire_id'];
        $telefon = trim($_POST['telefon']);
        $email = trim($_POST['email']);
        $ev_sahibi_mi = isset($_POST['ev_sahibi_mi']) ? 1 : 0;

        if (empty($ad) || empty($soyad) || empty($daire_id)) {
            header('Location: sakinler.php?error=empty');
            exit();
        }

        try {
            $sql = "INSERT INTO sakinler (Ad, Soyad, DaireID, Telefon, Email, EvSahibiMi) 
                    VALUES (:ad, :soyad, :daire_id, :telefon, :email, :ev_sahibi_mi)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':ad' => $ad, ':soyad' => $soyad, ':daire_id' => $daire_id, ':telefon' => $telefon, ':email' => $email, ':ev_sahibi_mi' => $ev_sahibi_mi]);
            header('Location: sakinler.php?status=added');
            exit();
        } catch (PDOException $e) {
            die("Sakin ekleme hatası: " . $e->getMessage());
        }
    }

    // SAKİN GÜNCELLEME
    if ($_POST['action'] == 'update') {
        $sakin_id = $_POST['sakin_id'];
        $ad = trim($_POST['ad']);
        $soyad = trim($_POST['soyad']);
        $daire_id = $_POST['daire_id'];
        $telefon = trim($_POST['telefon']);
        $email = trim($_POST['email']);
        $ev_sahibi_mi = isset($_POST['ev_sahibi_mi']) ? 1 : 0;

        if (empty($sakin_id) || empty($ad) || empty($soyad) || empty($daire_id)) {
            header('Location: sakinler.php?error=emptyupdate');
            exit();
        }

        try {
            $sql = "UPDATE sakinler SET Ad = :ad, Soyad = :soyad, DaireID = :daire_id, Telefon = :telefon, Email = :email, EvSahibiMi = :ev_sahibi_mi 
                    WHERE SakinID = :sakin_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':ad' => $ad,
                ':soyad' => $soyad,
                ':daire_id' => $daire_id,
                ':telefon' => $telefon,
                ':email' => $email,
                ':ev_sahibi_mi' => $ev_sahibi_mi,
                ':sakin_id' => $sakin_id
            ]);
            header('Location: sakinler.php?status=updated');
            exit();
        } catch (PDOException $e) {
            die("Sakin güncelleme hatası: " . $e->getMessage());
        }
    }

    // SAKİN SİLME
    if ($_POST['action'] == 'delete') {
        $sakin_id = $_POST['sakin_id'];

        if (empty($sakin_id)) {
            header('Location: sakinler.php?error=nosakinid');
            exit();
        }

        try {
            $sql = "DELETE FROM sakinler WHERE SakinID = :sakin_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':sakin_id' => $sakin_id]);
            header('Location: sakinler.php?status=deleted');
            exit();
        } catch (PDOException $e) {
            header('Location: sakinler.php?error=deletefailed');
            exit();
        }
    }
} else {
    // Doğrudan erişim engeli
    header('Location: sakinler.php');
    exit();
}
