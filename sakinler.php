<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

// Veritabanından verileri çek
try {
    // Mevcut sakinleri listele
    $sql_sakinler = "SELECT s.*, d.DaireNo, b.BinaAdi 
                     FROM sakinler s
                     LEFT JOIN daireler d ON s.DaireID = d.DaireID
                     LEFT JOIN binalar b ON d.BinaID = b.BinaID
                     ORDER BY s.Ad, s.Soyad";
    $stmt_sakinler = $db->query($sql_sakinler);
    $sakinler = $stmt_sakinler->fetchAll(PDO::FETCH_ASSOC);

    // Daire seçimi için daire listesini çek
    $sql_daireler = "SELECT d.DaireID, d.DaireNo, b.BinaAdi 
                     FROM daireler d
                     JOIN binalar b ON d.BinaID = b.BinaID
                     ORDER BY b.BinaAdi, d.DaireNo";
    $stmt_daireler = $db->query($sql_daireler);
    $daireler = $stmt_daireler->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Yeni Sakin Ekleme Formu -->
    <div class="col-md-4 mb-4">
        <h3>Yeni Sakin Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="sakin_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label for="ad" class="form-label">Ad</label><input type="text" class="form-control" id="ad" name="ad" required></div>
                    <div class="mb-3"><label for="soyad" class="form-label">Soyad</label><input type="text" class="form-control" id="soyad" name="soyad" required></div>
                    <div class="mb-3"><label for="daire_id" class="form-label">Oturduğu Daire</label><select class="form-select" id="daire_id" name="daire_id" required>
                            <option value="">Daire Seçin...</option><?php foreach ($daireler as $daire): ?><option value="<?php echo $daire['DaireID']; ?>"><?php echo htmlspecialchars($daire['BinaAdi'] . ' - Daire ' . $daire['DaireNo']); ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label for="telefon" class="form-label">Telefon</label><input type="text" class="form-control" id="telefon" name="telefon"></div>
                    <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
                    <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" id="ev_sahibi_mi" name="ev_sahibi_mi" value="1" checked><label class="form-check-label" for="ev_sahibi_mi">Ev Sahibi</label></div>
                    <button type="submit" class="btn btn-primary">Sakin Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Sakinler Listesi -->
    <div class="col-md-8">
        <h3>Mevcut Sakinler</h3>
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Oturduğu Daire</th>
                    <th>Telefon</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sakinler) > 0): ?>
                    <?php foreach ($sakinler as $sakin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sakin['Ad'] . ' ' . $sakin['Soyad']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($sakin['BinaAdi'] . ' - Daire ' . $sakin['DaireNo']); ?>
                                <!-- RAPOR LİNKİ EKLENDİ -->
                                <a href="daire_detay.php?daire_id=<?php echo $sakin['DaireID']; ?>" class="btn btn-sm btn-outline-info py-0 px-1" title="Daire Borç Dökümü">
                                    📋
                                    a>
                            </td>
                            <td><?php echo htmlspecialchars($sakin['Telefon']); ?></td>
                            <td><?php echo $sakin['EvSahibiMi'] ? '<span class="badge bg-success">Ev Sahibi</span>' : '<span class="badge bg-info">Kiracı</span>'; ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editSakinModal"
                                    data-sakin-id="<?php echo $sakin['SakinID']; ?>"
                                    data-ad="<?php echo htmlspecialchars($sakin['Ad']); ?>"
                                    data-soyad="<?php echo htmlspecialchars($sakin['Soyad']); ?>"
                                    data-daire-id="<?php echo $sakin['DaireID']; ?>"
                                    data-telefon="<?php echo htmlspecialchars($sakin['Telefon']); ?>"
                                    data-email="<?php echo htmlspecialchars($sakin['Email']); ?>"
                                    data-ev-sahibi-mi="<?php echo $sakin['EvSahibiMi']; ?>">
                                    Düzenle
                                </button>
                                <button onclick="confirmDelete(<?php echo $sakin['SakinID']; ?>)" class="btn btn-sm btn-danger">Sil</button>
                                <form id="delete-form-<?php echo $sakin['SakinID']; ?>" action="sakin_action.php" method="POST" class="d-none">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="sakin_id" value="<?php echo $sakin['SakinID']; ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Henüz kayıtlı sakin bulunmamaktadır.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Sakin Düzenleme Modal'ı -->
<div class="modal fade" id="editSakinModal" tabindex="-1" aria-labelledby="editSakinModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSakinModalLabel">Sakin Bilgilerini Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="sakin_action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="sakin_id" id="edit_sakin_id">
                    <div class="mb-3"><label for="edit_ad" class="form-label">Ad</label><input type="text" class="form-control" id="edit_ad" name="ad" required></div>
                    <div class="mb-3"><label for="edit_soyad" class="form-label">Soyad</label><input type="text" class="form-control" id="edit_soyad" name="soyad" required></div>
                    <div class="mb-3"><label for="edit_daire_id" class="form-label">Oturduğu Daire</label><select class="form-select" id="edit_daire_id" name="daire_id" required>
                            <option value="">Daire Seçin...</option><?php foreach ($daireler as $daire): ?><option value="<?php echo $daire['DaireID']; ?>"><?php echo htmlspecialchars($daire['BinaAdi'] . ' - Daire ' . $daire['DaireNo']); ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="mb-3"><label for="edit_telefon" class="form-label">Telefon</label><input type="text" class="form-control" id="edit_telefon" name="telefon"></div>
                    <div class="mb-3"><label for="edit_email" class="form-label">Email</label><input type="email" class="form-control" id="edit_email" name="email"></div>
                    <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" id="edit_ev_sahibi_mi" name="ev_sahibi_mi" value="1"><label class="form-check-label" for="edit_ev_sahibi_mi">Ev Sahibi</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(sakinId) {
        if (confirm("Bu sakini silmek istediğinizden emin misiniz?")) {
            document.getElementById('delete-form-' + sakinId).submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var editModal = document.getElementById('editSakinModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var sakinId = button.getAttribute('data-sakin-id');
            var ad = button.getAttribute('data-ad');
            var soyad = button.getAttribute('data-soyad');
            var daireId = button.getAttribute('data-daire-id');
            var telefon = button.getAttribute('data-telefon');
            var email = button.getAttribute('data-email');
            var evSahibiMi = button.getAttribute('data-ev-sahibi-mi');

            var modalTitle = editModal.querySelector('.modal-title');
            var inputSakinId = editModal.querySelector('#edit_sakin_id');
            var inputAd = editModal.querySelector('#edit_ad');
            var inputSoyad = editModal.querySelector('#edit_soyad');
            var selectDaireId = editModal.querySelector('#edit_daire_id');
            var inputTelefon = editModal.querySelector('#edit_telefon');
            var inputEmail = editModal.querySelector('#edit_email');
            var checkEvSahibiMi = editModal.querySelector('#edit_ev_sahibi_mi');

            modalTitle.textContent = 'Düzenle: ' + ad + ' ' + soyad;
            inputSakinId.value = sakinId;
            inputAd.value = ad;
            inputSoyad.value = soyad;
            selectDaireId.value = daireId;
            inputTelefon.value = telefon;
            inputEmail.value = email;
            checkEvSahibiMi.checked = (evSahibiMi == 1);
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>