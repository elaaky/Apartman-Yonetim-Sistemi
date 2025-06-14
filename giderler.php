<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

// Veritabanından giderleri çek
try {
    $sql = "SELECT g.*, CONCAT(y.Ad, ' ', y.Soyad) as YoneticiAdi 
            FROM giderler g 
            LEFT JOIN yöneticiler y ON g.YoneticiID = y.YoneticiID 
            ORDER BY g.Tarih DESC";
    $stmt = $db->query($sql);
    $giderler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Raporlama için özet veriler
    $toplam_gelir = $db->query("SELECT SUM(Tutar) FROM aidatlar WHERE OdendiMi = 1")->fetchColumn() ?? 0;
    $toplam_gider = $db->query("SELECT SUM(Tutar) FROM giderler")->fetchColumn() ?? 0;
    $kasa_durumu = $toplam_gelir - $toplam_gider;
} catch (PDOException $e) {
    die("Gider verileri çekilirken hata oluştu: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Toplam Gelir (Aidat)</h5>
                <p class="card-text fs-4"><?php echo number_format($toplam_gelir, 2, ',', '.'); ?> TL</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Toplam Gider</h5>
                <p class="card-text fs-4"><?php echo number_format($toplam_gider, 2, ',', '.'); ?> TL</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-dark bg-warning">
            <div class="card-body">
                <h5 class="card-title">Kasa Durumu</h5>
                <p class="card-text fs-4"><?php echo number_format($kasa_durumu, 2, ',', '.'); ?> TL</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <!-- Yeni Gider Ekleme Formu -->
    <div class="col-md-4 mb-4">
        <h3>Yeni Gider Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="gider_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label for="gider_turu" class="form-label">Gider Türü</label><input type="text" class="form-control" id="gider_turu" name="gider_turu" placeholder="Örn: Elektrik Faturası" required></div>
                    <div class="mb-3"><label for="tutar" class="form-label">Tutar (TL)</label><input type="number" step="0.01" class="form-control" id="tutar" name="tutar" required></div>
                    <div class="mb-3"><label for="tarih" class="form-label">Gider Tarihi</label><input type="date" class="form-control" id="tarih" name="tarih" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="mb-3"><label for="aciklama" class="form-label">Açıklama (Opsiyonel)</label><textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea></div>
                    <button type="submit" class="btn btn-primary">Gider Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Giderler Listesi -->
    <div class="col-md-8">
        <h3>Yapılan Giderler</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Gider Türü</th>
                    <th>Tutar</th>
                    <th>Açıklama</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($giderler) > 0): ?>
                    <?php foreach ($giderler as $gider): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($gider['Tarih'])); ?></td>
                            <td><?php echo htmlspecialchars($gider['GiderTuru']); ?></td>
                            <td><?php echo number_format($gider['Tutar'], 2, ',', '.'); ?> TL</td>
                            <td><?php echo htmlspecialchars($gider['Aciklama']); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editGiderModal"
                                    data-gider-id="<?php echo $gider['GiderID']; ?>"
                                    data-gider-turu="<?php echo htmlspecialchars($gider['GiderTuru']); ?>"
                                    data-tutar="<?php echo $gider['Tutar']; ?>"
                                    data-tarih="<?php echo $gider['Tarih']; ?>"
                                    data-aciklama="<?php echo htmlspecialchars($gider['Aciklama']); ?>">
                                    Düzenle
                                </button>
                                <button onclick="confirmDelete(<?php echo $gider['GiderID']; ?>)" class="btn btn-sm btn-danger">Sil</button>
                                <form id="delete-form-<?php echo $gider['GiderID']; ?>" action="gider_action.php" method="POST" class="d-none">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="gider_id" value="<?php echo $gider['GiderID']; ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Henüz kayıtlı gider bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Gider Düzenleme Modal'ı -->
<div class="modal fade" id="editGiderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gider Bilgilerini Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="gider_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="gider_id" id="edit_gider_id">
                    <div class="mb-3"><label for="edit_gider_turu" class="form-label">Gider Türü</label><input type="text" class="form-control" id="edit_gider_turu" name="gider_turu" required></div>
                    <div class="mb-3"><label for="edit_tutar" class="form-label">Tutar (TL)</label><input type="number" step="0.01" class="form-control" id="edit_tutar" name="tutar" required></div>
                    <div class="mb-3"><label for="edit_tarih" class="form-label">Gider Tarihi</label><input type="date" class="form-control" id="edit_tarih" name="tarih" required></div>
                    <div class="mb-3"><label for="edit_aciklama" class="form-label">Açıklama</label><textarea class="form-control" id="edit_aciklama" name="aciklama" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(giderId) {
        if (confirm("Bu gider kaydını silmek istediğinizden emin misiniz?")) {
            document.getElementById('delete-form-' + giderId).submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var editModal = document.getElementById('editGiderModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var giderId = button.getAttribute('data-gider-id');
            var giderTuru = button.getAttribute('data-gider-turu');
            var tutar = button.getAttribute('data-tutar');
            var tarih = button.getAttribute('data-tarih');
            var aciklama = button.getAttribute('data-aciklama');

            editModal.querySelector('#edit_gider_id').value = giderId;
            editModal.querySelector('#edit_gider_turu').value = giderTuru;
            editModal.querySelector('#edit_tutar').value = tutar;
            editModal.querySelector('#edit_tarih').value = tarih;
            editModal.querySelector('#edit_aciklama').value = aciklama;
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>