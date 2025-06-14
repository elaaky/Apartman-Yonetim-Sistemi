<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

// URL'den daire ID'sini al
if (!isset($_GET['daire_id']) || !is_numeric($_GET['daire_id'])) {
    header('Location: daireler.php');
    exit();
}
$daire_id = $_GET['daire_id'];

// Veritabanından verileri çek
try {
    // Daire bilgilerini çek
    $sql_daire = "SELECT d.*, b.BinaAdi FROM daireler d JOIN binalar b ON d.BinaID = b.BinaID WHERE d.DaireID = :daire_id";
    $stmt_daire = $db->prepare($sql_daire);
    $stmt_daire->execute([':daire_id' => $daire_id]);
    $daire = $stmt_daire->fetch(PDO::FETCH_ASSOC);

    if (!$daire) {
        die("Daire bulunamadı.");
    }

    // Daireye ait tüm aidat kayıtlarını çek
    $sql_aidatlar = "SELECT * FROM aidatlar WHERE DaireID = :daire_id ORDER BY Donem DESC";
    $stmt_aidatlar = $db->prepare($sql_aidatlar);
    $stmt_aidatlar->execute([':daire_id' => $daire_id]);
    $aidatlar = $stmt_aidatlar->fetchAll(PDO::FETCH_ASSOC);

    // Dairenin toplam borcunu hesapla (daha önce yazdığımız fonksiyonu kullanalım!)
    $sql_borc = "SELECT fn_DaireToplamBorc(:daire_id) as toplam_borc";
    $stmt_borc = $db->prepare($sql_borc);
    $stmt_borc->execute([':daire_id' => $daire_id]);
    $toplam_borc = $stmt_borc->fetchColumn();

} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daire Borç Dökümü</h1>
        <a href="sakinler.php" class="btn btn-sm btn-outline-secondary">Geri Dön</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h3>Daire Bilgileri</h3>
            <p><strong>Bina:</strong> <?php echo htmlspecialchars($daire['BinaAdi']); ?></p>
            <p><strong>Daire No:</strong> <?php echo htmlspecialchars($daire['DaireNo']); ?></p>
        </div>
        <div class="col-md-6 text-md-end">
            <h3>Bakiye Durumu</h3>
            <?php if ($toplam_borc > 0): ?>
                <h4 class="text-danger">Toplam Borç: <?php echo number_format($toplam_borc, 2, ',', '.'); ?> TL</h4>
            <?php else: ?>
                <h4 class="text-success">Borcu Yoktur</h4>
            <?php endif; ?>
        </div>
    </div>

    <hr>

    <h3>Aidat Geçmişi</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Dönem</th>
                <th>Tutar</th>
                <th>Durum</th>
                <th>Ödeme Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($aidatlar) > 0): ?>
                <?php foreach ($aidatlar as $aidat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($aidat['Donem']); ?></td>
                        <td><?php echo number_format($aidat['Tutar'], 2, ',', '.'); ?> TL</td>
                        <td>
                            <?php if ($aidat['OdendiMi']): ?>
                                <span class="badge bg-success">Ödendi</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Ödenmedi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $aidat['OdemeTarihi'] ? date('d.m.Y', strtotime($aidat['OdemeTarihi'])) : '-'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Bu daireye ait aidat kaydı bulunmamaktadır.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php require_once 'includes/footer.php'; ?>