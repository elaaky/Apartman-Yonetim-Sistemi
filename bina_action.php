<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // YENİ BİNA EKLEME
    if ($_POST['action'] == 'add') {
        $bina_adi = trim($_POST['bina_adi']);
        $adres = trim($_POST['adres']);

        if (empty($bina_adi)) {
            header('Location: binalar.php?error=empty');
            exit();
        }

        try {
            $sql = "INSERT INTO binalar (BinaAdi, Adres) VALUES (:bina_adi, :adres)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':bina_adi' => $bina_adi, ':adres' => $adres]);
            header('Location: binalar.php?status=added');
            exit();
        } catch (PDOException $e) {
            die("Bina ekleme hatası: " . $e->getMessage());
        }
    }

    // BİNA GÜNCELLEME
    if ($_POST['action'] == 'update') {
        $bina_id = $_POST['bina_id'];
        $bina_adi = trim($_POST['bina_adi']);
        $adres = trim($_POST['adres']);

        if (empty($bina_id) || empty($bina_adi)) {
            header('Location: binalar.php?error=emptyupdate');
            exit();
        }

        try {
            $sql = "UPDATE binalar SET BinaAdi = :bina_adi, Adres = :adres WHERE BinaID = :bina_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':bina_adi' => $bina_adi, ':adres' => $adres, ':bina_id' => $bina_id]);
            header('Location: binalar.php?status=updated');
            exit();
        } catch (PDOException $e) {
            die("Bina güncelleme hatası: " . $e->getMessage());
        }
    }

    // BİNA SİLME
    if ($_POST['action'] == 'delete') {
        $bina_id = $_POST['bina_id'];
        if (empty($bina_id)) {
            header('Location: binalar.php?error=nobinaid');
            exit();
        }

        try {
            // ON DELETE CASCADE sayesinde, bu binayı sildiğimizde
            // veritabanı otomatik olarak bu binaya ait tüm daireleri, sakinleri ve aidatları da silecektir.
            // Bu, SQL şemasını kurarken verdiğimiz önemli bir karardı.
            $sql = "DELETE FROM binalar WHERE BinaID = :bina_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':bina_id' => $bina_id]);
            header('Location: binalar.php?status=deleted');
            exit();
        } catch (PDOException $e) {
            // Normalde ON DELETE CASCADE sayesinde buraya düşmemesi lazım.
            // Ama bir sorun olursa diye hata yönetimi ekliyoruz.
            header('Location: binalar.php?error=deletefailed');
            exit();
        }
    }
}
