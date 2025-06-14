<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // TOPLU AİDAT BORCU ATAMA
    if ($_POST['action'] == 'toplu_ata') {
        $tutar = $_POST['tutar'];
        $donem = date('F Y');
        $son_odeme_tarihi = date('Y-m-t'); // Ayın son günü

        if (empty($tutar)) {
            header('Location: aidatlar.php?error=tutar_bos');
            exit();
        }

        try {
            // Tüm daireleri çek
            $daireler_stmt = $db->query("SELECT DaireID FROM daireler");
            $daireler = $daireler_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Her daire için aidat borcu oluştur
            $sql = "INSERT INTO aidatlar (DaireID, Tutar, Donem, SonOdemeTarihi) VALUES (:daire_id, :tutar, :donem, :son_odeme_tarihi)";
            $stmt = $db->prepare($sql);

            foreach ($daireler as $daire) {
                // O daire için o dönemde zaten aidat var mı kontrol et
                $check_sql = "SELECT COUNT(*) FROM aidatlar WHERE DaireID = ? AND Donem = ?";
                $check_stmt = $db->prepare($check_sql);
                $check_stmt->execute([$daire['DaireID'], $donem]);
                if ($check_stmt->fetchColumn() == 0) {
                    $stmt->execute([
                        ':daire_id' => $daire['DaireID'],
                        ':tutar' => $tutar,
                        ':donem' => $donem,
                        ':son_odeme_tarihi' => $son_odeme_tarihi
                    ]);
                }
            }
            header('Location: aidatlar.php?status=toplu_atandi');
            exit();
        } catch (PDOException $e) {
            die("Toplu aidat atama hatası: " . $e->getMessage());
        }
    }

    // ÖDEME YAPMA
    if ($_POST['action'] == 'odeme_yap') {
        $aidat_id = $_POST['aidat_id'];
        try {
            // Burada sp_AidatOde prosedürünü kullanıyoruz!
            $stmt = $db->prepare("CALL sp_AidatOde(:aidat_id)");
            $stmt->bindParam(':aidat_id', $aidat_id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: aidatlar.php?status=odendi');
            exit();
        } catch (PDOException $e) {
            die("Ödeme hatası: " . $e->getMessage());
        }
    }

    // ÖDEME İPTAL ETME
    if ($_POST['action'] == 'odeme_iptal') {
        $aidat_id = $_POST['aidat_id'];
        try {
            $sql = "UPDATE aidatlar SET OdendiMi = 0, OdemeTarihi = NULL WHERE AidatID = :aidat_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':aidat_id', $aidat_id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: aidatlar.php?status=iptal_edildi');
            exit();
        } catch (PDOException $e) {
            die("Ödeme iptal hatası: " . $e->getMessage());
        }
    }

    // MANUEL AİDAT EKLEME (Bu kısmı en sona ekledim)
    if ($_POST['action'] == 'manuel_ekle') {
        $daire_id = $_POST['daire_id'];
        $tutar = $_POST['tutar'];
        $donem = $_POST['donem'];
        $son_odeme_tarihi = date('Y-m-t', strtotime('last day of ' . $donem));

        try {
            $sql = "INSERT INTO aidatlar (DaireID, Tutar, Donem, SonOdemeTarihi) VALUES (:daire_id, :tutar, :donem, :son_odeme_tarihi)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':daire_id' => $daire_id, ':tutar' => $tutar, ':donem' => $donem, ':son_odeme_tarihi' => $son_odeme_tarihi]);
            header('Location: aidatlar.php?status=manuel_eklendi');
            exit();
        } catch (PDOException $e) {
            die("Manuel ekleme hatası: " . $e->getMessage());
        }
    }
} else {
    header('Location: aidatlar.php');
    exit();
}
