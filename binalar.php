<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

try {
    $stmt = $db->query("SELECT * FROM binalar ORDER BY BinaAdi ASC");
    $binalar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Yeni Bina Ekleme Formu -->
    <div class="col-md-4 mb-4">
        <h3>Yeni Bina Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="bina_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="bina_adi" class="form-label">Bina Adı</label>
                        <input type="text" class="form-control" id="bina_adi" name="bina_adi" placeholder="Örn: C Blok" required>
                    </div>
                    <div class="mb-3">
                        <label for="adres" class="form-label">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Bina Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Binalar Listesi -->
    <div class="col-md-8">
        <h3>Mevcut Binalar</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr><th>Bina Adı</th><th>Adres</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php if (count($binalar) > 0): foreach ($binalar as $bina): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bina['BinaAdi']); ?></td>
                        <td><?php echo htmlspecialchars($bina['Adres']); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editBinaModal"
                                data-bina-id="<?php echo $bina['BinaID']; ?>"
                                data-bina-adi="<?php echo htmlspecialchars($bina['BinaAdi']); ?>"
                                data-adres="<?php echo htmlspecialchars($bina['Adres']); ?>">
                                Düzenle
                            </button>
                            <button onclick="confirmDelete(<?php echo $bina['BinaID']; ?>)" class="btn btn-sm btn-danger">Sil</button>
                            <form id="delete-form-<?php echo $bina['BinaID']; ?>" action="bina_action.php" method="POST" class="d-none">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="bina_id" value="<?php echo $bina['BinaID']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" class="text-center">Kayıtlı bina bulunmamaktadır.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bina Düzenleme Modal'ı -->
<div class="modal fade" id="editBinaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Bina Bilgilerini Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="bina_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="bina_id" id="edit_bina_id">
                    <div class="mb-3"><label for="edit_bina_adi" class="form-label">Bina Adı</label><input type="text" class="form-control" id="edit_bina_adi" name="bina_adi" required></div>
                    <div class="mb-3"><label for="edit_adres" class="form-label">Adres</label><textarea class="form-control" id="edit_adres" name="adres" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(binaId) {
    if (confirm("Bu binayı silmek istediğinizden emin misiniz? Bu binaya ait tüm daireler ve sakinler de silinecektir! Bu işlem geri alınamaz!")) {
        document.getElementById('delete-form-' + binaId).submit();
    }
}
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editBinaModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        editModal.querySelector('#edit_bina_id').value = button.getAttribute('data-bina-id');
        editModal.querySelector('#edit_bina_adi').value = button.getAttribute('data-bina-adi');
        editModal.querySelector('#edit_adres').value = button.getAttribute('data-adres');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>