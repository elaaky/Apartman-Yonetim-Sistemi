<?php
session_start();
if (!isset($_SESSION['yonetici_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'includes/db_connect.php';

try {
    $stmt = $db->query("SELECT YoneticiID, Ad, Soyad, KullaniciAdi, Email, Telefon, AktifMi FROM yöneticiler ORDER BY Ad ASC");
    $yoneticiler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Yeni Yönetici Ekleme Formu -->
    <div class="col-md-4 mb-4">
        <h3>Yeni Yönetici Ekle</h3>
        <div class="card">
            <div class="card-body">
                <form action="yonetici_action.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label for="ad" class="form-label">Ad</label><input type="text" class="form-control" id="ad" name="ad" required></div>
                    <div class="mb-3"><label for="soyad" class="form-label">Soyad</label><input type="text" class="form-control" id="soyad" name="soyad" required></div>
                    <div class="mb-3"><label for="kullanici_adi" class="form-label">Kullanıcı Adı</label><input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required></div>
                    <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
                    <div class="mb-3"><label for="telefon" class="form-label">Telefon</label><input type="text" class="form-control" id="telefon" name="telefon"></div>
                    <div class="mb-3"><label for="sifre" class="form-label">Şifre</label><input type="password" class="form-control" id="sifre" name="sifre" required></div>
                    <button type="submit" class="btn btn-primary">Yönetici Ekle</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mevcut Yöneticiler Listesi -->
    <div class="col-md-8">
        <h3>Mevcut Yöneticiler</h3>
        <table class="table table-striped table-hover">
            <thead>
                <tr><th>Ad Soyad</th><th>Kullanıcı Adı</th><th>Email</th><th>Durum</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($yoneticiler as $yonetici): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($yonetici['Ad'] . ' ' . $yonetici['Soyad']); ?></td>
                        <td><?php echo htmlspecialchars($yonetici['KullaniciAdi']); ?></td>
                        <td><?php echo htmlspecialchars($yonetici['Email']); ?></td>
                        <td><?php echo $yonetici['AktifMi'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Pasif</span>'; ?></td>
                        <td>
    <!-- Kendi kendini silmeyi veya pasif yapmayı engellemek için kontrol -->
    <?php if ($yonetici['YoneticiID'] != $_SESSION['yonetici_id']): ?>
        <button type="button" class="btn btn-sm btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editYoneticiModal"
            data-yonetici-id="<?php echo $yonetici['YoneticiID']; ?>"
            data-ad="<?php echo htmlspecialchars($yonetici['Ad']); ?>"
            data-soyad="<?php echo htmlspecialchars($yonetici['Soyad']); ?>"
            data-kullanici-adi="<?php echo htmlspecialchars($yonetici['KullaniciAdi']); ?>"
            data-email="<?php echo htmlspecialchars($yonetici['Email']); ?>"
            data-telefon="<?php echo htmlspecialchars($yonetici['Telefon']); ?>"
            data-aktif-mi="<?php echo $yonetici['AktifMi']; ?>">
            Düzenle
        </button>
        
        <!-- DÜZELTİLMİŞ SİLME BUTONU VE FORMU -->
        <button onclick="confirmDelete(<?php echo $yonetici['YoneticiID']; ?>)" class="btn btn-sm btn-danger">Sil</button>
        <form id="delete-form-<?php echo $yonetici['YoneticiID']; ?>" action="yonetici_action.php" method="POST" style="display: none;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="yonetici_id" value="<?php echo $yonetici['YoneticiID']; ?>">
        </form>
        
    <?php else: ?>
        <span class="badge bg-info">Mevcut Oturum</span>
    <?php endif; ?>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Yönetici Düzenleme Modal'ı -->
<div class="modal fade" id="editYoneticiModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Yönetici Bilgilerini Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form action="yonetici_action.php" method="POST"><div class="modal-body">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="yonetici_id" id="edit_yonetici_id">
    <div class="mb-3"><label for="edit_ad" class="form-label">Ad</label><input type="text" class="form-control" id="edit_ad" name="ad" required></div>
    <div class="mb-3"><label for="edit_soyad" class="form-label">Soyad</label><input type="text" class="form-control" id="edit_soyad" name="soyad" required></div>
    <div class="mb-3"><label for="edit_kullanici_adi" class="form-label">Kullanıcı Adı</label><input type="text" class="form-control" id="edit_kullanici_adi" name="kullanici_adi" required></div>
    <div class="mb-3"><label for="edit_email" class="form-label">Email</label><input type="email" class="form-control" id="edit_email" name="email"></div>
    <div class="mb-3"><label for="edit_telefon" class="form-label">Telefon</label><input type="text" class="form-control" id="edit_telefon" name="telefon"></div>
    <div class="mb-3 form-check"><input type="checkbox" class="form-check-input" id="edit_aktif_mi" name="aktif_mi" value="1"><label class="form-check-label" for="edit_aktif_mi">Hesap Aktif</label></div>
    <p class="text-muted small">Not: Güvenlik nedeniyle şifre bu ekrandan değiştirilemez.</p>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Kaydet</button></div></form>
</div></div></div>

<script>
function confirmDelete(yoneticiId) {
    if (confirm("Bu yöneticiyi kalıcı olarak silmek istediğinizden emin misiniz?")) {
        document.getElementById('delete-form-' + yoneticiId).submit();
    }
}
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editYoneticiModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var yoneticiId = button.getAttribute('data-yonetici-id');
        var ad = button.getAttribute('data-ad');
        var soyad = button.getAttribute('data-soyad');
        var kullanici_adi = button.getAttribute('data-kullanici-adi');
        var email = button.getAttribute('data-email');
        var telefon = button.getAttribute('data-telefon');
        var aktif_mi = button.getAttribute('data-aktif-mi');

        editModal.querySelector('#edit_yonetici_id').value = yoneticiId;
        editModal.querySelector('#edit_ad').value = ad;
        editModal.querySelector('#edit_soyad').value = soyad;
        editModal.querySelector('#edit_kullanici_adi').value = kullanici_adi;
        editModal.querySelector('#edit_email').value = email;
        editModal.querySelector('#edit_telefon').value = telefon;
        editModal.querySelector('#edit_aktif_mi').checked = (aktif_mi == 1);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>