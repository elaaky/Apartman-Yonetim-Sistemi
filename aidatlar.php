<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

// Veritabanından aidatları çek
try {
    // Ödenmemiş Aidatlar
    $sql_odenmemis = "SELECT a.*, d.DaireNo, b.BinaAdi, s.Ad, s.Soyad
                      FROM aidatlar a
                      JOIN daireler d ON a.DaireID = d.DaireID
                      JOIN binalar b ON d.BinaID = b.BinaID
                      LEFT JOIN (SELECT * FROM sakinler WHERE EvSahibiMi = 1) s ON d.DaireID = s.DaireID
                      WHERE a.OdendiMi = 0
                      ORDER BY a.SonOdemeTarihi ASC";
    $stmt_odenmemis = $db->query($sql_odenmemis);
    $odenmemis_aidatlar = $stmt_odenmemis->fetchAll(PDO::FETCH_ASSOC);
    
    // Ödenmiş Aidatlar
    $sql_odenmis = "SELECT a.*, d.DaireNo, b.BinaAdi, s.Ad, s.Soyad
                    FROM aidatlar a
                    JOIN daireler d ON a.DaireID = d.DaireID
                    JOIN binalar b ON d.BinaID = b.BinaID
                    LEFT JOIN (SELECT * FROM sakinler WHERE EvSahibiMi = 1) s ON d.DaireID = s.DaireID
                    WHERE a.OdendiMi = 1
                    ORDER BY a.OdemeTarihi DESC LIMIT 50"; // Son 50 ödemeyi göster
    $stmt_odenmis = $db->query($sql_odenmis);
    $odenmis_aidatlar = $stmt_odenmis->fetchAll(PDO::FETCH_ASSOC);

    // Daire listesini çek (manuel ekleme için)
    $sql_daireler = "SELECT d.DaireID, d.DaireNo, b.BinaAdi FROM daireler d JOIN binalar b ON d.BinaID = b.BinaID ORDER BY b.BinaAdi, d.DaireNo";
    $stmt_daireler = $db->query($sql_daireler);
    $daireler = $stmt_daireler->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Kontrol Paneli (Toplu Aidat Atama vs.) -->
    <div class="col-md-4 mb-4">
        <h3>Aidat İşlemleri</h3>
        <div class="card">
            <div class="card-body">
                <h5>Toplu Aidat Ata</h5>
                <p class="text-muted small">Tüm dairelere mevcut ay için toplu aidat borcu atar. (Örn: <?php echo date('F Y'); ?>)</p>
                <form action="aidat_action.php" method="POST" onsubmit="return confirm('Tüm dairelere aidat atamak istediğinizden emin misiniz?');">
                    <input type="hidden" name="action" value="toplu_ata">
                    <div class="mb-3">
                        <label for="toplu_tutar" class="form-label">Aylık Aidat Tutarı</label>
                        <input type="number" step="0.01" class="form-control" id="toplu_tutar" name="tutar" placeholder="Örn: 500.00" required>
                    </div>
                    <button type="submit" class="btn btn-success">Tümüne Borç Ata</button>
                </form>
                <hr>
                <h5>Manuel Borç Ekle</h5>
                <form action="aidat_action.php" method="POST">
                    <input type="hidden" name="action" value="manuel_ekle">
                    <div class="mb-3">
                        <label for="daire_id" class="form-label">Daire Seç</label>
                        <select class="form-select" id="daire_id" name="daire_id" required>
                            <option value="">Seçin...</option>
                            <?php foreach($daireler as $daire): ?>
                                <option value="<?php echo $daire['DaireID']; ?>"><?php echo htmlspecialchars($daire['BinaAdi'] . ' - Daire ' . $daire['DaireNo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="manuel_tutar" class="form-label">Borç Tutarı</label>
                        <input type="number" step="0.01" class="form-control" id="manuel_tutar" name="tutar" required>
                    </div>
                     <div class="mb-3">
                        <label for="donem" class="form-label">Dönem (Ay Yıl)</label>
                        <input type="text" class="form-control" id="donem" name="donem" value="<?php echo date('F Y'); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Borç Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Aidat Listeleri (Sekmeli yapı) -->
    <div class="col-md-8">
        <h3>Aidat Durumu</h3>
        <ul class="nav nav-tabs" id="aidatTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="odenmemis-tab" data-bs-toggle="tab" data-bs-target="#odenmemis" type="button" role="tab">Ödenmemiş Borçlar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="odenmis-tab" data-bs-toggle="tab" data-bs-target="#odenmis" type="button" role="tab">Son Ödemeler</button>
            </li>
        </ul>
        <div class="tab-content" id="aidatTabContent">
            <!-- Ödenmemiş Aidatlar Paneli -->
            <div class="tab-pane fade show active" id="odenmemis" role="tabpanel">
                <table class="table table-hover mt-3">
                    <thead><tr><th>Daire</th><th>Ev Sahibi</th><th>Dönem</th><th>Tutar</th><th>Son Ödeme</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach($odenmemis_aidatlar as $aidat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($aidat['BinaAdi'] . ' - ' . $aidat['DaireNo']); ?></td>
                            <td><?php echo htmlspecialchars($aidat['Ad'] . ' ' . $aidat['Soyad']); ?></td>
                            <td><?php echo htmlspecialchars($aidat['Donem']); ?></td>
                            <td><?php echo number_format($aidat['Tutar'], 2); ?> TL</td>
                            <td><?php echo date('d.m.Y', strtotime($aidat['SonOdemeTarihi'])); ?></td>
                            <td>
                                <form action="aidat_action.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="odeme_yap">
                                    <input type="hidden" name="aidat_id" value="<?php echo $aidat['AidatID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">Ödendi İşaretle</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($odenmemis_aidatlar) == 0): ?>
                        <tr><td colspan="6" class="text-center">Ödenmemiş borç bulunmamaktadır.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Ödenmiş Aidatlar Paneli -->
            <div class="tab-pane fade" id="odenmis" role="tabpanel">
                <table class="table table-hover mt-3">
                     <thead><tr><th>Daire</th><th>Ev Sahibi</th><th>Dönem</th><th>Tutar</th><th>Ödeme Tarihi</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach($odenmis_aidatlar as $aidat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($aidat['BinaAdi'] . ' - ' . $aidat['DaireNo']); ?></td>
                            <td><?php echo htmlspecialchars($aidat['Ad'] . ' ' . $aidat['Soyad']); ?></td>
                            <td><?php echo htmlspecialchars($aidat['Donem']); ?></td>
                            <td><?php echo number_format($aidat['Tutar'], 2); ?> TL</td>
                            <td><?php echo date('d.m.Y', strtotime($aidat['OdemeTarihi'])); ?></td>
                             <td>
                                <form action="aidat_action.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="odeme_iptal">
                                    <input type="hidden" name="aidat_id" value="<?php echo $aidat['AidatID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary">İptal Et</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                         <?php if(count($odenmis_aidatlar) == 0): ?>
                        <tr><td colspan="6" class="text-center">Kayıtlı ödeme bulunmamaktadır.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>