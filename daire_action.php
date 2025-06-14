<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

// Formdan gelen verileri kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // Ekleme işlemi
    if ($_POST['action'] == 'add') {
        $bina_id = $_POST['bina_id'];
        $daire_no = $_POST['daire_no'];
        $kat = $_POST['kat'];

        // Alanların boş olup olmadığını kontrol et
        if (empty($bina_id) || empty($daire_no)) {
            // Hata mesajı ile geri yönlendir (gelecekte eklenebilir)
            header('Location: daireler.php?error=empty');
            exit();
        }

        try {
            $sql = "INSERT INTO daireler (BinaID, DaireNo, Kat) VALUES (:bina_id, :daire_no, :kat)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':bina_id', $bina_id);
            $stmt->bindParam(':daire_no', $daire_no);
            $stmt->bindParam(':kat', $kat);
            $stmt->execute();

            // Başarılı ekleme sonrası daireler sayfasına yönlendir
            header('Location: daireler.php?status=added');
            exit();
        } catch (PDOException $e) {
            die("Ekleme hatası: " . $e->getMessage());
        }
    }
    
    // Gelecekte güncelleme ve silme işlemleri buraya eklenecek
    // if ($_POST['action'] == 'update') { ... }
    // if ($_POST['action'] == 'delete') { ... }
} else {
    // Doğrudan erişim engeli
    header('Location: daireler.php');
    exit();
}
// Silme işlemi
if ($_POST['action'] == 'delete') {
    $daire_id = $_POST['daire_id'];

    if (empty($daire_id)) {
        header('Location: daireler.php?error=nodeleteid');
        exit();
    }

    try {
        $sql = "DELETE FROM daireler WHERE DaireID = :daire_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':daire_id', $daire_id);
        $stmt->execute();

        header('Location: daireler.php?status=deleted');
        exit();
    } catch (PDOException $e) {
        // İlişkili kayıt varsa (örn: sakin), bu hata verir.
        // Gerçek bir projede bu durum daha zarif yönetilmelidir.
        header('Location: daireler.php?error=deletefailed');
        exit();
    }
}
// Güncelleme işlemi
if ($_POST['action'] == 'update') {
    $daire_id = $_POST['daire_id'];
    $bina_id = $_POST['bina_id'];
    $daire_no = $_POST['daire_no'];
    $kat = $_POST['kat'];

    if (empty($daire_id) || empty($bina_id) || empty($daire_no)) {
        header('Location: daireler.php?error=emptyupdate');
        exit();
    }

    try {
        $sql = "UPDATE daireler SET BinaID = :bina_id, DaireNo = :daire_no, Kat = :kat WHERE DaireID = :daire_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':bina_id', $bina_id);
        $stmt->bindParam(':daire_no', $daire_no);
        $stmt->bindParam(':kat', $kat);
        $stmt->bindParam(':daire_id', $daire_id);
        $stmt->execute();

        header('Location: daireler.php?status=updated');
        exit();

    } catch (PDOException $e) {
        die("Güncelleme hatası: " . $e->getMessage());
    }
}
?>