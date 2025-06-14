<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// Veritabanından tüm daireleri ve bağlı oldukları binaları çekme
try {
    $sql = "SELECT d.*, b.BinaAdi FROM daireler d JOIN binalar b ON d.BinaID = b.BinaID ORDER BY b.BinaAdi, d.DaireNo";
    $stmt = $db->query($sql);
    $daireler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Yeni daire eklemek için binaları çek
    $binalar_stmt = $db->query("SELECT * FROM binalar ORDER BY BinaAdi");
    $binalar = $binalar_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}
?>

<div class="row">
    <!-- Daire Ekleme Formu -->
    <div class="col-md-4">
        <h3>Yeni Daire Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="daire_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="bina_id" class="form-label">Bina</label>
                        <select class="form-select" id="bina_id" name="bina_id" required>
                            <option value="">Bina Seçin...</option>
                            <?php foreach ($binalar as $bina): ?>
                                <option value="<?php echo $bina['BinaID']; ?>"><?php echo htmlspecialchars($bina['BinaAdi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="daire_no" class="form-label">Daire Numarası</label>
                        <input type="text" class="form-control" id="daire_no" name="daire_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="kat" class="form-label">Kat</label>
                        <input type="number" class="form-control" id="kat" name="kat">
                    </div>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Daireler Listesi -->
    <div class="col-md-8">
        <h3>Mevcut Daireler</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Bina Adı</th>
                    <th>Daire No</th>
                    <th>Kat</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($daireler) > 0): ?>
                    <?php foreach ($daireler as $daire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($daire['BinaAdi']); ?></td>
                            <td><?php echo htmlspecialchars($daire['DaireNo']); ?></td>
                            <td><?php echo htmlspecialchars($daire['Kat']); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editDaireModal"
                                    data-daire-id="<?php echo $daire['DaireID']; ?>"
                                    data-bina-id="<?php echo $daire['BinaID']; ?>"
                                    data-daire-no="<?php echo htmlspecialchars($daire['DaireNo']); ?>"
                                    data-kat="<?php echo htmlspecialchars($daire['Kat']); ?>">
                                    Düzenle
                                </a>

                                <!-- Silme butonu artık bir JavaScript fonksiyonunu çağırıyor -->
                                <button onclick="confirmDelete(<?php echo $daire['DaireID']; ?>)" class="btn btn-sm btn-danger">Sil</button>

                                <!-- Her satır için gizli bir silme formu -->
                                <form id="delete-form-<?php echo $daire['DaireID']; ?>" action="daire_action.php" method="POST" style="display: none;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="daire_id" value="<?php echo $daire['DaireID']; ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Henüz kayıtlı daire bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Daire Düzenleme Modal'ı -->
<div class="modal fade" id="editDaireModal" tabindex="-1" aria-labelledby="editDaireModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDaireModalLabel">Daire Bilgilerini Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="daire_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="daire_id" id="edit_daire_id">

                    <div class="mb-3">
                        <label for="edit_bina_id" class="form-label">Bina</label>
                        <select class="form-select" id="edit_bina_id" name="bina_id" required>
                            <?php foreach ($binalar as $bina): ?>
                                <option value="<?php echo $bina['BinaID']; ?>"><?php echo htmlspecialchars($bina['BinaAdi']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_daire_no" class="form-label">Daire Numarası</label>
                        <input type="text" class="form-control" id="edit_daire_no" name="daire_no" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kat" class="form-label">Kat</label>
                        <input type="number" class="form-control" id="edit_kat" name="kat">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function confirmDelete(daireId) {
        // ... (mevcut silme kodu burada kalacak)
        if (confirm("Bu daireyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!")) {
            document.getElementById('delete-form-' + daireId).submit();
        }
    }

    // Modal tetiklendiğinde çalışacak kod
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = document.getElementById('editDaireModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            // Butonu tetikleyen elementi al
            var button = event.relatedTarget;

            // data-* niteliklerinden veriyi çek
            var daireId = button.getAttribute('data-daire-id');
            var binaId = button.getAttribute('data-bina-id');
            var daireNo = button.getAttribute('data-daire-no');
            var kat = button.getAttribute('data-kat');

            // Modal'ın içindeki form elemanlarını seç
            var modalTitle = editModal.querySelector('.modal-title');
            var inputDaireId = editModal.querySelector('#edit_daire_id');
            var selectBinaId = editModal.querySelector('#edit_bina_id');
            var inputDaireNo = editModal.querySelector('#edit_daire_no');
            var inputKat = editModal.querySelector('#edit_kat');

            // Form elemanlarını gelen veri ile doldur
            modalTitle.textContent = 'Daire Düzenle: ' + daireNo;
            inputDaireId.value = daireId;
            selectBinaId.value = binaId;
            inputDaireNo.value = daireNo;
            inputKat.value = kat;
        });
    });
</script>
<?php require_once 'includes/footer.php'; ?>